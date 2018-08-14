<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 13.04.2017
 * Time: 15:38
 */

class ActivityCompanyCalculator {
    protected $_company = null;

    protected $plan_budget = array();
    protected $plan_company_budget = array();
    protected $real_budget = array();

    protected $plan_budget_cash = 0;

    protected $_company_budget = array('completed' => 0, 'wait' => 0, 'completed_over' => 0, 'total_cash' => 0);
    protected $_completed_by_quarters = array();
    protected $_statistics_by_months = array();

    /**
     * ActivityCompanyCalculator constructor.
     * @param $params
     * @param $company
     */
    public function __construct($params, $company)
    {
        $this->_company = $company;

        foreach ($params as $key => $param_value) {
            $this->{$key} = $param_value;
        }

        $this->getPlanBudget();
        //$this->getRealBudget();

        $companies = ActivityCompanyTypeTable::getInstance()->createQuery()->execute();
        foreach ($companies as $company_type) {
            //Инициализация массива статистики по месяцам
            for($month_num = 1; $month_num <= 12; $month_num++) {
                $this->_statistics_by_months[$company_type->getId()]['months'][$month_num] = array('fact_cash' => 0,'cash' => 0, 'moved_cash' => 0, 'models' => 0, 'plan_company_cash' => 0, 'total_cash' => 0);
            }

            $prev_q = 0;
            for ($q = 1; $q <= 4; $q++) {
                $this->real_budget[$company_type->getId()][$q] = 0;

                $plan_budget = $company_type->getPercent() * $this->plan_budget[$q] / 100;
                $this->plan_company_budget[$company_type->getId()][$q] = $plan_budget;

                $this->_completed_by_quarters[$q][$company_type->getId()] = array
                (
                    'total_models' => 0,
                    'total_moved_models' => 0,
                    'total_moved_models_cash' => 0,
                    'activities_moved' => array(),
                    'total_cash' => 0,
                    'complete_percent' => 0,
                    'wait_percent' => 0,
                    'recomplete_percent' => 0,
                    'total_models_from_prev_quarter' => 0,
                    'models' => array(),
                    'plan_company_budget' => $this->plan_company_budget[$company_type->getId()][$q]
                );

                if ($prev_q != $q) {
                    $q_start = D::getFirstMonthOfQuarter($q);
                    for ($q_from = $q_start; $q_from < ($q_start + 3); $q_from++) {
                        $this->_statistics_by_months[$company_type->getId()]['months'][$q_from]['plan_company_cash'] = $this->plan_company_budget[$company_type->getId()][$q];
                    }

                    $prev_q = $q;
                }
            }
        }
    }

    /**
     * @param $company_type
     * @param $params
     * @return null
     */
    public static function createCalculator($company_type, $params) {
        $company = ActivityCompanyTypeTable::getInstance()->find($company_type);
        if ($company) {
            $cls = (implode('', array_map(function($item) {
                return ucfirst($item);
            }, explode('_', $company->getClassName())))).'Company';

            return new $cls($params, $company);
        }

        return null;
    }
    /**
     * Planned budget
     * @return array
     */
    protected function getPlanBudget() {
        $budget = array(1 => 0, 2 => 0, 3 => 0, 4 => 0);

        $query = BudgetTable::getInstance()
            ->createQuery()
            ->select('quarter, plan')
            ->where('dealer_id=? and year=?', array($this->dealer->getId(), $this->year));

        foreach ($query->execute(array(), Doctrine_Core::HYDRATE_ARRAY) as $budget_row) {
            $budget[$budget_row['quarter']] = $budget_row['plan'];
            $this->plan_budget_cash += $budget_row['plan'];
        }
        $this->plan_budget = $budget;

        return $budget;
    }

    /**
     * @param $quarter
     * @param $sum
     * @param null $row
     */
    public function addToBudget($quarter, $sum, $row = null, $month_num = null, $prev_q = false) {
        $new_sum = $this->real_budget[$row['Activity']['type_company_id']][$quarter] + $sum;

        if ($quarter < 4 && $new_sum > $this->plan_company_budget[$row['Activity']['type_company_id']][$quarter]) {
            $moved_cash = $new_sum -  $this->plan_company_budget[$row['Activity']['type_company_id']][$quarter];

            //Статистика по месяцу
            if (!is_null($month_num)) {
                $this->_statistics_by_months[$row['Activity']['type_company_id']]['months'][$month_num]['moved_cash'] += $moved_cash;
                $this->_statistics_by_months[$row['Activity']['type_company_id']]['months'][$month_num]['total_cash'] += $sum;
            }

            $this->_completed_by_quarters[$quarter][$row['Activity']['type_company_id']]['total_moved_models_cash'] += $moved_cash;

            $new_sum = $this->plan_company_budget[$row['Activity']['type_company_id']][$quarter];

            $this->_completed_by_quarters[$quarter][$row['Activity']['type_company_id']]['models'][$row['mId']] = array('model' => $row, 'next_quarter' => true);
            $this->_completed_by_quarters[$quarter][$row['Activity']['type_company_id']]['total_moved_models']++;

            $calc_result = $this->calculate($quarter, $this->_completed_by_quarters[$quarter][$row['Activity']['type_company_id']]['total_moved_models_cash']);

            $this->_completed_by_quarters[$quarter][$row['Activity']['type_company_id']]['total_moved_models_percent'] = $calc_result['moved_percent'];
            $this->_completed_by_quarters[$quarter][$row['Activity']['type_company_id']]['activities_moved'][] = $row['Activity']['id'];

            $this->_completed_by_quarters[$quarter + 1][$row['Activity']['type_company_id']]['total_models_from_prev_quarter']++;

            $this->addToBudget($quarter + 1, $moved_cash, $row, D::getFirstMonthOfQuarter($quarter + 1), true);
        } else {
            $this->_completed_by_quarters[$quarter][$row['Activity']['type_company_id']]['models'][$row['mId']] = array('model' => $row, 'next_quarter' => false);
        }

        $this->_completed_by_quarters[$quarter][$row['Activity']['type_company_id']]['total_models']++;
        $this->_completed_by_quarters[$quarter][$row['Activity']['type_company_id']]['total_cash'] = $new_sum;

        //Статистика по месяцам
        if (!is_null($month_num)) {
            $this->_statistics_by_months[$row['Activity']['type_company_id']]['months'][$month_num]['models']++;
            $this->_statistics_by_months[$row['Activity']['type_company_id']]['months'][$month_num]['cash'] += $sum;
            $this->_statistics_by_months[$row['Activity']['type_company_id']]['months'][$month_num]['total_cash'] += $sum;

            //Вычисляем фактический бюджет в разрезе месяцев
            $this->_statistics_by_months[$row['Activity']['type_company_id']]['months'][$month_num]['fact_cash'] += $sum;
        }

        $this->real_budget[$row['Activity']['type_company_id']][$quarter] = $new_sum;
    }

    public function addToQBudget($quarter, $sum, $row = null) {
        $new_sum = $this->real_budget[$row['Activity']['type_company_id']][$quarter] + $sum;

        if ($quarter < 4 && $new_sum > $this->plan_company_budget[$row['Activity']['type_company_id']][$quarter]) {
            $moved_cash = $new_sum -  $this->plan_company_budget[$row['Activity']['type_company_id']][$quarter];

            $this->addToQBudget($quarter + 1, $moved_cash, $row);

            $new_sum = $this->plan_company_budget[$row['Activity']['type_company_id']][$quarter];
        }

        $this->_completed_by_quarters[$quarter][$row['Activity']['type_company_id']]['total_cash'] = $new_sum;
        $this->real_budget[$row['Activity']['type_company_id']][$quarter] = $new_sum;
    }

    public function addToQBudgetOneQuarter($quarter, $sum, $row = null) {
        $new_sum = $this->real_budget[$row['Activity']['type_company_id']][$quarter] + $sum;

        $this->_completed_by_quarters[$quarter][$row['Activity']['type_company_id']]['total_cash'] = $new_sum;
        $this->real_budget[$row['Activity']['type_company_id']][$quarter] = $new_sum;
    }

    /**
     * @return array
     */
    public function getStatisticByQuarters() {
        return $this->_completed_by_quarters;
    }

    /**
     * @return array
     */
    public function getStatisticByMonths() {
        return $this->_statistics_by_months;
    }

    /**
     * @param null $by_quarter
     * @param int $moved_cache
     * @return array
     */
    public function calculate($by_quarter = null, $moved_cache = 0) {
        $current_q = is_null($by_quarter) ? D::getQuarter(D::calcQuarterData(time())) : $by_quarter;
        $quarter_budget = 0;

        if (isset($this->real_budget[$this->_company_type])) {
            $quarter_budget = $this->real_budget[$this->_company_type][$current_q];
        }

        if ($this->_company)
        {
            $plan_budget = $this->getCompanyPercent() * $this->plan_budget[$current_q] / 100;

            $plan_budget = $plan_budget == 0 ? 1 : $plan_budget;
            $company_completed_by_percent = round(($quarter_budget * 100) / $plan_budget, 0);

            $company_budget_recompleted = 0;
            $company_budget_completed = 0;
            $company_budget_wait = 100;

            if ($company_completed_by_percent > 0) {
                $company_budget_completed = $company_completed_by_percent;
                if ($company_budget_completed > 100) {
                    $company_budget_completed = 100;
                }

                $company_budget_wait = round(100 - $company_budget_completed, 0);
                if ($company_budget_wait < 0) {
                    $company_budget_wait = 0;
                }
            }

            //Вычисляем процент переноса суммы в сл.квартал в случае перевыполнения бюджета квартала
            $company_budget_moved_percent = 0;
            if ($moved_cache > 0 && $quarter_budget != 0) {
                $company_budget_moved_percent = round(($moved_cache * 100) / $quarter_budget, 0);
            }

            $this->_company_budget['total_plan_cash'] = $plan_budget;
            $this->_company_budget['total_cash'] = $quarter_budget;
            //$this->_company_budget['total_cash'] = round(($this->plan_budget[$current_q] * $this->_company->getPercent()) / 100, 0);
            $this->_company_budget['completed'] = $this->_completed_by_quarters[$current_q][$this->_company_type]['complete_percent'] = $company_budget_completed;
            $this->_company_budget['moved_percent'] = $company_budget_moved_percent;
            $this->_company_budget['wait'] = $this->_completed_by_quarters[$current_q][$this->_company_type]['wait_percent'] = $company_budget_wait;
            $this->_company_budget['completed_over'] = $this->_completed_by_quarters[$current_q][$this->_company_type]['recomplete_percent'] = $company_budget_recompleted;

            $this->_company_budget['wait_cash'] = $plan_budget - $quarter_budget;
            $this->_company_budget['complete_cash'] = $quarter_budget;
        }

        return $this->_company_budget;;
    }

    /**
     * @return array
     */
    public function getCompanyBudgetStatus() {
        return $this->calculate();
    }

    /**
     * Get company percent from main budget
     */
    protected function getCompanyPercent() {
        return 0;
    }
}
