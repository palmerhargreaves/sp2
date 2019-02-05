<?php

/**
 * Description of RealBudgetCalculator
 *
 * @author Сергей
 */
class RealBudgetCalculator
{
    /**
     * Dealer
     *
     * @var Dealer
     */
    protected $dealer;
    /**
     * Year
     *
     * @var int
     */
    protected $year;
    protected $real_budget = array();
    protected $real_company_budget = array();
    protected $plan_budget = array();

    const LAST_QUARTER = 4;
    const FIRST_QUARTER = 1;
    const MIN_DAYS = 20;

    private $_models = array();
    private $_activities = array();
    private $_calc_models = array();

    private $_company_type = null;
    private $_plan_budget_summ = 0;

    private $_company_budget = array('completed' => 0, 'wait' => 0, 'completed_over' => 0, 'to');
    /**
     * @var ActivityCompanyCalculator
     */
    private $_company_calculator = null;

    private static $_company_plan_budget = array();

    function __construct(Dealer $dealer, $year, $company_type = null)
    {
        $this->dealer = $dealer;
        $this->year = $year;
        $this->_company_type = $company_type;
    }

    function calculate()
    {
        $this->real_budget = array(1 => 0, 2 => 0, 3 => 0, 4 => 0);
        $this->real_company_budget = array();

        $this->total_budget = 0;
        $this->plan_budget = $this->getPlanBudget();

        $query = AgreementModelTable::getInstance()
            ->createQuery('am')
            ->select('am.id as mId, am.cost, r.accept_date as rAcceptDate, am.activity_id as mActivityId, ama.type_company_id')
            ->leftJoin('am.Report r')
            ->leftJoin('am.Activity ama')
            ->where('am.dealer_id = ? and (year(am.created_at) = ? or year(am.updated_at) = ?) and am.status = ? and r.status = ? and am.is_deleted = ?',
                array
                (
                    $this->dealer->getId(),
                    $this->year,
                    $this->year,
                    'accepted',
                    'accepted',
                    false
                )
            )->orderBy('am.id');

        if (!is_null($this->_company_type)) {
            $this->_company_calculator = ActivityCompanyCalculator::createCalculator($this->_company_type, array(
                'dealer' => $this->dealer,
                'year' => $this->year,
                '_company_type' => $this->_company_type
            ));
        }


        if (!is_null($this->_company_type)) {
            $query->leftJoin('am.Activity a WITH a.type_company_id = ?', $this->_company_type);
        }

        $this->calcRealBudgetByItems($query->execute(array(), Doctrine_Core::HYDRATE_ARRAY));

        $query = AgreementModelTable::getInstance()
            ->createQuery('am')
            ->select('am.id as mId, am.cost, r.accept_date as rAcceptDate, am.activity_id as mActivityId, ama.type_company_id')
            ->leftJoin('am.Report r')
            ->leftJoin('am.Activity ama')
            ->where('am.dealer_id = ? and year(am.updated_at) = ? and quarter(am.updated_at) = ? and am.status = ? and r.status = ? and am.is_deleted = ?',
                array
                (
                    $this->dealer->getId(),
                    ($this->year + 1),
                    self::FIRST_QUARTER,
                    'accepted',
                    'accepted',
                    false
                )
            )->orderBy('am.id ASC');

        if (!is_null($this->_company_type)) {
            $query->leftJoin('am.Activity a WITH a.type_company_id = ?', $this->_company_type);
        }

        $this->calcRealBudgetByItems($query->execute(array(), Doctrine_Core::HYDRATE_ARRAY));
        //$this->calcCompanyTypePercent();

        return $this->real_budget;
    }

    public function getCompanyBudgetStatus() {
        return $this->_company_calculator->getCompanyBudgetStatus();
    }

    private function calcRealBudgetByItems($items) {
        $activities = array();

        $total = 0;
        foreach ($items as $real_row) {
            if (array_key_exists($real_row['mId'], $this->_calc_models)) {
                continue;
            }

            $this->_calc_models[$real_row['mId']] = $real_row['mId'];
            $entry = utils::getModelDateFromLogEntryWithYear($real_row['mId']);

            if ($entry) {
                $nDate = $entry;
            } else {
                $nDate = D::calcQuarterData($real_row['rAcceptDate']);
            }

            $year = D::getYear($nDate);
            $q = D::getQuarter($nDate);

            if ($this->year != $year) {
                continue;
            }

            if(!array_key_exists($real_row['mActivityId'], $activities)) {
                $activities[$real_row['mActivityId']] = ActivityTable::getInstance()->find($real_row['mActivityId']);
                $this->_activities[$real_row['mActivityId']] = Activity::STATISTIC_COMPLETED;
            }

            $realSum = $real_row['cost'];
            $this->addToTotalBudget($realSum);

            $this->addToRealBudget($q, $realSum, $real_row);
            if ($real_row['Activity']['type_company_id'] != 0) {
                if ($this->_company_calculator) {
                    $this->_company_calculator->addToBudget($q, $realSum, $real_row);
                }
                //$this->addToRealCompanyBudget($q, $realSum, $real_row);
            }
            $total++;

            $activity = $activities[$real_row['mActivityId']];
            if (!$activity->isActivityStatisticComplete($this->dealer, $nDate)) {
                $this->_activities[$real_row['mActivityId']] = Activity::STATISTIC_NOT_COMPLETED;
                continue;
            }

            $this->_activities[$real_row['mActivityId']] = Activity::STATISTIC_COMPLETED;
        }
    }

    public function getCalcModelsList() {
        return $this->_models;
    }

    public function getActivitiesList() {
        return $this->_activities;
    }

    public function getTotalBudget() {
        return $this->total_budget;
    }

    protected function addToTotalBudget($sum) {
        $this->total_budget += $sum;
    }

    protected function addToRealBudget($quarter, $sum, $row = null)
    {
        $new_sum = $this->real_budget[$quarter] + $sum;
        if ($quarter < 4 && $new_sum > $this->plan_budget[$quarter] && $this->plan_budget[$quarter] != 0) {
            $this->addToRealBudget($quarter + 1, $new_sum - $this->plan_budget[$quarter], $row);
            $new_sum = $this->plan_budget[$quarter];
        }

        if (!is_null($row)) {
            $this->_models[$quarter][] = $row;
        }

        $this->real_budget[$quarter] = $new_sum;
    }

    public function getPlanBudget()
    {
        $budget = array(1 => 0, 2 => 0, 3 => 0, 4 => 0);

        $query = BudgetTable::getInstance()
            ->createQuery()
            ->select('quarter, plan')
            ->where('dealer_id=? and year=?', array($this->dealer->getId(), $this->year));

        foreach ($query->execute(array(), Doctrine_Core::HYDRATE_ARRAY) as $budget_row) {
            $budget[$budget_row['quarter']] = $budget_row['plan'];
            $this->_plan_budget_summ += $budget_row['plan'];
        }

        return $budget;
    }

    public function getTotalPlanBudget() {
        return $this->_plan_budget_summ;
    }


}
