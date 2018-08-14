<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 21.07.2016
 * Time: 15:03
 */

/**
 * Get company type, all activities types, get plan budget
 * Class ActivitiesCompanyTypesBuilder
 */
class ActivitiesCompanyTypesBuilder
{
    private $_company_types = array();
    private $_company_dealers_statistic = array();

    private $_request = null;
    private $_user = null;
    private $_filters = array();

    private $_dealer = null;

    private $year = 0;

    function __construct($user, sfWebRequest $request, $filters)
    {
        $this->_user = $user;
        $this->_request = $request;
        $this->_filters = $filters;

        $this->year = D::getBudgetYear($this->_request);

        $this->_filters['request'] = $request;
        $this->_filters['year'] = $this->year;

        $this->getCompanyTypes();
    }

    /**
     * Init company types
     */
    private function getCompanyTypes() {
        if (isset($this->_filters['filter_activities_by_company']) && !empty($this->_filters['filter_activities_by_company'])) {
            $items = ActivityCompanyTypeTable::getInstance()->createQuery()->where('id = ?', $this->_filters['filter_activities_by_company'])->orderBy('id ASC')->execute();
        } else {
            $items = ActivityCompanyTypeTable::getInstance()->createQuery()->orderBy('id ASC')->execute();
        }

        foreach ($items as $item) {
            $this->_company_types[$item->getId()] = array
            (
                'company_type_item' => $item,
                'activities' => array
                (
                    'not_finished' => array(),
                    'finished' => array(),
                    'dealer_statistic' => null
                ),
                'budget_plan' => array(),
                'year' => 0
            );
        }
    }

    /**
     * Build data
     */
    public function build($is_xhr = false, $first_load = false)
    {
        foreach ($this->_company_types as $key => $company) {
            if (empty($this->_filters['filter_by_tab'])) {
                $this->getNotFinishedActivities($key);
            } else if ($this->_filters['filter_by_tab'] == 'finished') {
                $this->getFinishedActivities($key);
            } else if ($this->_filters['filter_by_tab'] == 'activities') {
                $this->getDealerStatistics($key);
            }

            if (!$is_xhr || $first_load) {
                $this->getCompanyBudgetStatus($key);
            }

            $this->_company_types[$key]['year'] = $this->_request->getParameter('year');
        }
    }

    /**
     * Build only dealer statistic
     */
    public function buildOnlyForStatistic()
    {
        foreach ($this->_company_types as $key => $company) {
            $this->getDealerStatistics($key);
            $this->getCompanyBudgetStatus($key);

            $this->_company_types[$key]['year'] = $this->_request->getParameter('year');
        }
    }

    public function getData() {
        return $this->_company_types;
    }

    public function getDealersStatistic() {
        return $this->_company_dealers_statistic;
    }

    private function getCompanyBudgetStatus($key) {
        $dealerId = DealerUserTable::getInstance()->createQuery()->select('dealer_id')->where('user_id = ?', $this->_user->getAuthUser()->getId())->fetchOne();
        if ($dealerId) {
            $dealer = DealerTable::getInstance()->find($dealerId->getDealerId());

            $calc = new RealBudgetCalculator($dealer, $this->year, $key);
            $calc->calculate();

            $this->_company_types[$key]['budget_plan'] = $calc->getCompanyBudgetStatus();
        }
    }

    private function makeActivitiesQuery($company_type, $is_finished = false) {
        $query = ActivityTable::getInstance()
            ->createQuery('a')
            ->select('a.id, a.start_date, a.end_date, a.custom_date, a.name, a.brief, a.importance, v.id is_viewed')
            ->where('a.type_company_id = ?', $company_type)
            ->leftJoin('a.UserViews v WITH v.user_id=?', $this->_user->getAuthUser()->getId());

        $query_sub_q = array();
        $query_arr = array();
        if ($this->_filters['filter_by_owned'] == 1) {
            $query_sub_q[] = 'own_activity = ?';
            $query_arr[] = 1;
        }

        if ($this->_filters['filter_by_required'] == 1) {
            $query_sub_q[] = 'required_activity = ?';
            $query_arr[] = 1;
        }

        if (count($query_sub_q) > 0) {
            $query->andWhere('(' . implode(' or ', $query_sub_q) . ')', $query_arr);
        }

        $query->andWhere('(a.own_activity = ? or a.required_activity = ?)',
            array
            (
                $this->_filters['filter_by_owned'],
                $this->_filters['filter_by_required']
            )
        );

        //Для завершенных активностей, делаем сортировку по возрастанию по номеру
        if (!$is_finished) {
            $query->orderBy($this->_filters['filter_by_sort']['sort_field'] . ' ' . $this->_filters['filter_by_sort']['sort_direction']);
        } else {
            $query->orderBy('id DESC');
        }

        return $query;
    }

    private function getNotFinishedActivities($company_type) {
        $query = $this->makeActivitiesQuery($company_type);

        if ($this->year && !D::isSpecialFirstQuarter($this->_request)) {
            $query->andWhere('(year(a.start_date) <= ? and year(a.end_date) >= ?)', array($this->year, $this->year))
                ->andWhere('(a.finished = ? or (allow_extended_statistic = ? and a.finished = ?))', array(false, true, false));
        } else {
            $query->andWhere('a.finished = ?', false);
        }

        $query->andWhere('(a.hide = ? or a.hide = ?)', array(false, ($this->_user->isAdmin() || $this->_user->getAuthUser()->isDesigner()) ? true : false));
        //$query->andWhere('(a.hide = ?)', array(false));
        ActivityTable::checkActivity($this->_user, $query, $company_type, $this->_filters);

        //$query->andWhere('(a.hide = ? or a.hide = ?)', array(false, ($this->_user->isAdmin() || $this->_user->getAuthUser()->isDesigner()) ? true : false));
        $activities = $query->execute();
        foreach ($activities as $activity) {
            $this->_company_types[$company_type]['activities']['not_finished'][] = $activity;
        }
    }

    private function getFinishedActivities($company_type) {
        $user = $this->_user;
        $show_hidden = $user->isAdmin() || $user->isImporter() || $user->isManager()/* || $user->isDealerUser()*/;

        $query = $this->makeActivitiesQuery($company_type, true);
        //$query->andWhere('finished = ? and year(a.start_date) <= ? and year(a.end_date) >= ?', array(true, $this->year, $this->year));
        $query->andWhere('finished = ?', array(true));

        if (!$show_hidden) {
            $query->andWhere('a.hide=?', false);
        }

        $this->_filters['finished'] = true;
        ActivityTable::checkActivity($user, $query, $company_type, $this->_filters);

        $activities = $query->execute();
        foreach ($activities as $activity) {
            $this->_company_types[$company_type]['activities']['finished'][] = $activity;
        }
    }

    public function getDealer() {
        return $this->_dealer;
    }

    public function getYear() {
        return $this->year;
    }

    public function getDealerStatistics($company_type = null)
    {
        if (isset($this->_filters['filter_by_dealer']) && !empty($this->_filters['filter_by_dealer'])) {
            $dealerId = $this->_filters['filter_by_dealer'];
        } else {
            $user_dealer = DealerUserTable::getInstance()->createQuery()->select('dealer_id')->where('user_id = ?', $this->_user->getAuthUser()->getId())->fetchOne();
            if ($user_dealer) {
                $dealerId = $user_dealer->getDealerId();
            }
        }

        if (!empty($dealerId)) {
            $this->_dealer = $dealer = DealerTable::getInstance()->find($dealerId);

            foreach (ActivityCompanyTypeTable::getInstance()->createQuery()->select()->execute() as $company) {
                $builder = new AgreementDealerStatisticBuilder($this->year, $dealer, $company->getId(), $this->_filters);
                $builder->build();

                $this->_company_types[$company->getId()]['activities']['dealer_statistic'] = array
                (
                    'builder' => $builder,
                    'company_type' => $company,
                );
            }

            $this->_temp_result = array();
            foreach ($this->_company_types as $key => $builder_data) {
                $builder = $builder_data['activities']['dealer_statistic']['builder'];
                $company_type = $builder_data['activities']['dealer_statistic']['company_type'];

                foreach ($builder->getStat() as $quarter => $companies) {
                    foreach ($companies as $key => $stat) {
                        if (!isset($stat['activities'])) { continue; }

                        $this->_temp_result[$quarter]['companies'][$key] = array
                        (
                            'stat' => $stat,
                            'company_type' => $company_type->getName(),
                            'company_statistic' => $stat['company_statistic']['data'],
                            'company_statistic_by_quarters' => $builder->getCompanyStatistic(),
                            'company_statistic_by_months' => $builder->getStatisticByMonths()
                        );
                    }
                }
            }

            $this->_company_dealers_statistic = $this->_temp_result;
        }
    }
}
