<?php

/**
 * Description of AgreementActivityStatusStatisticBuilder
 *
 * @author Сергей
 */
class AgreementActivityStatusStatisticBuilder
{
    const CONCEPT_ID = 10;

    protected $dealers = array();
    protected $managers = array();
    protected $activities_stat = array();
    protected $dealers_statistic = array();
    protected $quarter;
    protected $year;
    protected $total_stat = array();

    function __construct($year = false, $quarter = false)
    {
        $this->quarter = $quarter;
        $this->year = $year ?: intval(date('Y'));
    }

    function build()
    {
        $this->loadActivitiesStat();
        $this->loadDealers();

        $this->buildDealersStat();
        $this->loadPlanBudget();
        $this->loadRealBudget();
        $this->calcBudgetPercent();
        $this->loadAcceptedActivitiesForYear();

        if ($this->quarter) {
            $this->loadAcceptedActivitiesForQuarter();
        }

        $this->buildTotalStat();

        return $this->managers;
    }

    function start($buildForQuarters = false)
    {
        $rawDb = new RawDb();
        $statsFields = array('average_percent' => 'average_percent', 'accepted_required' => 'accepted_required');

        $managers = $this->build();
        if(!$buildForQuarters) {
            $activities = $this->getActivitiesStat();
            $total = $this->getTotalStat();

            $totalDealerStats = $rawDb->getRow(DealerActivitiesStatsDataTable::getRawTableName(),
                array(
                    'year' => $this->year
                ));

            if (count($totalDealerStats) == 0) {
                foreach ($statsFields as $key => $field) {
                    if (isset($total[$field])) {
                        $data = array(
                            'value' => $total[$field],
                            'field_name' => $field,
                            'year' => $this->year
                        );

                        $rawDb->insertRow(DealerActivitiesStatsDataTable::getRawTableName(), $data, false);
                    }
                }
            } else {
                foreach ($statsFields as $key => $field) {
                    if (isset($total[$field])) {
                        $dealerStatsItem = $rawDb->getRow(DealerActivitiesStatsDataTable::getRawTableName(),
                            array(
                                'year' => $this->year,
                                'field_name' => $field
                            ));

                        if (!$dealerStatsItem) {
                            $data = array(
                                'value' => $total[$field],
                                'field_name' => $field,
                                'year' => $this->year
                            );

                            $rawDb->insertRow(DealerActivitiesStatsDataTable::getRawTableName(), $data, false);
                        } else {
                            $rawDb->updateRow(DealerActivitiesStatsDataTable::getRawTableName(),
                                array(
                                    'value' => $total[ $field ]
                                ),
                                array(
                                    'id' => $dealerStatsItem[ 'id' ]
                                )
                            );
                        }
                    }
                }
            }
        }

        foreach ($managers as $key => $manager) {
            $key = $key == 0 ? 99999 : $key;

            $managerItem = $rawDb->getRow(DealerActivitiesStatsManagersTable::getRawTableName(),
                array(
                    'manager_id' => $key,
                    'year' => $this->year
                )
            );

            if (empty($managerItem)) {
                $managerItem = $rawDb->insertRow(DealerActivitiesStatsManagersTable::getRawTableName(),
                    array
                    (
                        'manager_id' => $key != 0 ? $key : 99999,
                        'year' => $this->year,
                    ),
                    true
                );
            }

            foreach ($manager['dealers'] as $n => $dealer) {
                $dealer_stat = $this->getDealerStat($dealer);
                $managerDealerStats = $rawDb->getRow(DealerActivitiesStatsTable::getRawTableName(),
                    array
                    (
                        'manager_id' => $managerItem['id'],
                        'dealer_id' => $dealer->getId()
                    )
                );

                if (!$managerDealerStats) {
                    $managerDealerStats = $rawDb->insertRow(DealerActivitiesStatsTable::getRawTableName(),
                        array
                        (
                            'manager_id' => $managerItem['id'],
                            'dealer_id' => $dealer->getId(),
                            'percent_of_budget' => $dealer_stat['budget']['percent'],
                            'models_completed' => $dealer_stat['models_accepted'],
                            'models_completed_cost' => $dealer_stat['models_accepted_total_cost'],
                            'activities_completed' => $dealer_stat['year_accepted'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ),
                        true);
                } else {
                    $rawDb->updateRow(DealerActivitiesStatsTable::getRawTableName(),
                        array(
                            'manager_id' => $managerItem['id'],
                            'percent_of_budget' => $dealer_stat['budget']['percent'],
                            'models_completed' => $dealer_stat['models_accepted'],
                            'models_completed_cost' => $dealer_stat['models_accepted_total_cost'],
                            'activities_completed' => $dealer_stat['year_accepted'],
                            'updated_at' => date('Y-m-d H:i:s'),
                        ),
                        array(
                            'id' => $managerDealerStats['id']
                        ),
                        false
                    );
                }

                if ($buildForQuarters) {
                    $quartersStats = $this->getAcceptedModelsByQuarter($dealer->getId());
                    $quarterModelsCost = $this->getAcceptedModelsCostByQuarter($dealer->getId());
                    $quartersActivitiesStats = $this->getCompletedActivitiesByQuarter($dealer->getId());

                    $qStats = array_merge($quartersStats, $quartersActivitiesStats, $quarterModelsCost);
                    foreach ($qStats as $qKey => $qStat) {
                        $rawDb->updateRow(DealerActivitiesStatsTable::getRawTableName(),
                            array(
                                $qKey => $qStat,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ),
                            array(
                                'id' => $managerDealerStats['id']
                            ),
                            false
                        );
                    }
                } else {

                    foreach ($activities as $key => $activity_row) {
                        $activityStat = $rawDb->getRow(DealerActivitiesStatsDataTable::getRawTableName(),
                            array
                            (
                                'dealer_stat_id' => $managerDealerStats['id'],
                                'activity_id' => $activity_row['activity']->getId()
                            )
                        );

                        $complete_by_quarters = array();
                        if (isset($this->dealers_statistic[$dealer->getId()]) && isset($this->dealers_statistic[$dealer->getId()][$activity_row['activity']->getId()])) {
                            foreach ($this->dealers_statistic[$dealer->getId()][$activity_row['activity']->getId()] as $q_index => $q_data) {
                                $complete_by_quarters['q'.$q_index] = $q_data;
                            }
                        }

                        if (empty($activityStat)) {
                            $data = array(
                                'dealer_stat_id' => $managerDealerStats['id'],
                                'activity_id' => $activity_row['activity']->getId(),
                                'status' => $dealer_stat['statuses'][$activity_row['activity']->getId()],
                                'total_completed' => $total['accepted'][$activity_row['activity']->getId()],
                                'year' => $this->year,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            );

                            $rawDb->insertRow(DealerActivitiesStatsDataTable::getRawTableName(),
                                array_merge($data, $complete_by_quarters),
                                false
                            );

                        } else {
                            $data = array(
                                'status' => $dealer_stat['statuses'][$activity_row['activity']->getId()],
                                'total_completed' => $total['accepted'][$activity_row['activity']->getId()],
                                'updated_at' => date('Y-m-d H:i:s'),
                            );

                            $rawDb->updateRow(DealerActivitiesStatsDataTable::getRawTableName(),
                                array_merge($data, $complete_by_quarters),
                                array(
                                    'id' => $activityStat['id']
                                ));
                        }
                    }
                }
            }

        }
    }

    function getManagers()
    {
        return $this->managers;
    }

    function getTotalStat()
    {
        return $this->total_stat;
    }

    /**
     * Returns statistic by dealer
     *
     * @param Dealer $dealer
     */
    function getDealerStat(Dealer $dealer)
    {
        return isset($this->dealers[$dealer->getId()])
            ? $this->dealers[$dealer->getId()]
            : $this->createDealerBlankStat($dealer);
    }

    function getActivitiesStat()
    {
        return $this->activities_stat;
    }

    protected function buildTotalStat()
    {
        $this->total_stat = array(
            'average_percent' => 0,
            'dealers_percent' => array(),
            'accepted_required' => 0,
            'models_in_work' => array(),
            'models_complete' => array(),
            'accepted' => array()
        );

        foreach ($this->activities_stat as $id => $activity)
            $this->total_stat['accepted'][$id] = 0;

        $this->calcAveragePercent();
        $this->calcTotalAccepted();

        $this->calcAcceptedRequired();
    }

    protected function buildDealersStat()
    {
        foreach ($this->dealers as $dealer_row) {
            $dealer = $dealer_row['dealer'];
            $ok_count = 0;
            foreach ($this->activities_stat as $activity_id => $activity) {
                $status = $this->calcStatus($activity, $dealer->getId());
                $this->dealers[$dealer->getId()]['statuses'][$activity_id] = $status;

                if ($status == 'ok')
                    $ok_count++;
            }
            $this->dealers[$dealer->getId()]['quarter_accepted_required'] = ($ok_count > 3 ? 3 : $ok_count) / 3 * 100;
        }
    }

    protected function calcAveragePercent()
    {
        $total = 0;

        foreach ($this->dealers as $dealer_key => $stat) {
            if (isset($stat['budget'])) {
                $total += $stat['budget']['percent'];
                $this->total_stat['dealers_percent'][$dealer_key] = $stat['budget']['percent'];
            }
        }

        $this->total_stat['average_percent'] = $total / count($this->dealers);
    }

    protected function calcTotalAccepted()
    {
        foreach ($this->dealers as $stat) {
            foreach ($this->activities_stat as $id => $activity) {
                if (isset($stat['statuses']) && $stat['statuses'][$id] == 'ok')
                    $this->total_stat['accepted'][$id]++;
            }
        }
    }

    protected function calcAcceptedRequired()
    {
        $sum = 0;
        foreach ($this->dealers as $stat)
            $sum += $stat['quarter_accepted_required'];

        $this->total_stat['accepted_required'] = $sum / count($this->dealers);

    }

    protected function loadPlanBudget()
    {
        if (!$this->dealers) {
            return;
        }

        if ($this->quarter) {
            for ($i = 1; $i <= $this->quarter; $i++) {
                $query = BudgetTable::getInstance()
                    ->createQuery()
                    ->select('dealer_id, sum(plan) sum')
                    ->where('year=?', $this->year)
                    ->andWhereIn('dealer_id', array_keys($this->dealers))
                    ->groupBy('dealer_id');

                $query->andWhere('quarter=?', $i);

                $all_budgets = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                foreach ($all_budgets as $budget) {
                    $this->dealers[$budget['dealer_id']]['budget']['plan'] += $budget['sum'];
                }
            }
        } else {
            $query = BudgetTable::getInstance()
                ->createQuery()
                ->select('dealer_id, sum(plan) sum')
                ->where('year=?', $this->year)
                ->andWhereIn('dealer_id', array_keys($this->dealers))
                ->groupBy('dealer_id');

            $all_budgets = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            foreach ($all_budgets as $budget) {
                $this->dealers[$budget['dealer_id']]['budget']['plan'] = $budget['sum'];
            }
        }

    }

    protected function loadRealBudget()
    {
        if (!$this->dealers)
            return;

        if ($this->quarter) {
            for ($i = 1; $i <= $this->quarter; $i++) {
                $query = RealTotalBudgetTable::getInstance()
                    ->createQuery()
                    ->select('dealer_id, sum(sum) sum')
                    ->where('year=?', $this->year)
                    ->andWhereIn('dealer_id', array_keys($this->dealers))
                    ->groupBy('dealer_id');

                $query->andWhere('quarter=?', $i);

                $all_budgets = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                foreach ($all_budgets as $budget) {
                    $this->dealers[$budget['dealer_id']]['budget']['fact'] += $budget['sum'];

                }
            }
        } else {
            $query = RealTotalBudgetTable::getInstance()
                ->createQuery()
                ->select('dealer_id, sum(sum) sum')
                ->where('year=?', $this->year)
                ->andWhereIn('dealer_id', array_keys($this->dealers))
                ->groupBy('dealer_id');

            $all_budgets = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            foreach ($all_budgets as $budget) {
                $this->dealers[$budget['dealer_id']]['budget']['fact'] = $budget['sum'];
            }
        }
    }

    protected function calcBudgetPercent()
    {
        foreach ($this->dealers as $id => $dealer) {
            $plan = floatval($dealer['budget']['plan']);
            $fact = floatval($dealer['budget']['fact']);

            if ($plan) {
                $this->dealers[$id]['budget']['percent'] = $fact / $plan * 100;
            }

            /*if($fact)
              $this->dealers[$id]['budget']['percent'] = $plan / $fact * 100;*/
        }
    }

    protected function calcStatus($activity_stat_row, $dealer_id)
    {
        if (isset($activity_stat_row['done_dealers'][$dealer_id]))
            return 'ok';

        if (isset($activity_stat_row['in_work_dealers'][$dealer_id]))
            return 'wait';

        return 'none';
    }

    protected function loadAcceptedActivitiesForYear()
    {
        $start_date = mktime(0, 0, 0, 1, 1, $this->year);
        $end_date = strtotime('+1 year', $start_date);

        $items = $this->getQueryForAcceptedActivities($start_date, $end_date);

        $temp = array();
        foreach ($items as $row) {
            if (!array_key_exists($row->activity_id, $this->activities_stat)) {
                continue;
            }

            if (!isset($temp[$row->dealer_id])) {
                $temp[$row->dealer_id] = array();
            }

            if (!in_array($row->activity_id, $temp[$row->dealer_id])) {
                $temp[$row->dealer_id][] = $row->activity_id;

                //$this->dealers[$row->dealer_id]['year_accepted'] = $row->activities;
                if (isset($this->dealers[$row->dealer_id])) {
                    $this->dealers[$row->dealer_id]['year_accepted']++;
                }
            }

            if (isset($this->dealers[$row->dealer_id])) {
                $this->dealers[$row->dealer_id]['models_accepted']++;
                $this->dealers[$row->dealer_id]['models_accepted_total_cost'] += $row->getCost();

            }
        }
    }

    public function getAcceptedModelsByQuarter($dealerId)
    {
        $result = array('q1' => 0, 'q2' => 0, 'q3' => 0, 'q4' => 0);

        $models = $this->getAcceptedModelsListByDealerId($dealerId);

        for ($q = 1; $q <= 4; $q++) {
            $result['q' . $q] = isset($models[$q]) ? count($models[$q]) : 0;
        }

        return $result;
    }

    public function getAcceptedModelsCostByQuarter($dealerId)
    {
        $result = array('models_completed_cost_q1' => 0, 'models_completed_cost_q2' => 0, 'models_completed_cost_q3' => 0, 'models_completed_cost_q4' => 0);

        $models_list = $this->getAcceptedModelsListByDealerId($dealerId);

        foreach ($models_list as $q => $models) {
            foreach ($models as $model) {
                $result[ 'models_completed_cost_q' . $q ] += $model[ 'cost' ];
            }
        }

        return $result;
    }

    public function getCompletedActivitiesByQuarter($dealerId)
    {
        $result = array('q_activity1' => 0, 'q_activity2' => 0, 'q_activity3' => 0, 'q_activity4' => 0);
        $temp = array();

        //Получаем список заявок за год
        $models_list = $this->getAcceptedModelsListByDealerId($dealerId);

        //Проходим по кварталам и фиксируем поличество выполненных активностей
        for ($q = 1; $q <= 4; $q++) {
            if (!isset($temp[$dealerId])) {
                $temp[$dealerId] = array();
            }

            //Если есть в квартале выполненные заявки пишем данные в массив
            if (isset($models_list[$q])) {
                $models = $models_list[$q];
                foreach ($models as $model) {
                    if (!array_key_exists($model[ 'activity_id' ], $this->activities_stat)) {
                        continue;
                    }

                    if (!in_array($model[ 'activity_id' ], $temp[ $dealerId ])) {
                        $temp[ $dealerId ][] = $model[ 'activity_id' ];

                        $result[ 'q_activity' . $q ]++;
                    }
                }
            }
        }

        return $result;
    }

    private function getAcceptedModelsListByDealerId($dealerId, $q = null)
    {
        $start_date = mktime(0, 0, 0, !is_null($q) ? ($q - 1) * 3 + 1 : 1, 1, $this->year);
        $end_date = strtotime(!is_null($q) ? '+3 months' : '+1 year', $start_date);

        $result = array();
        $models = $this->getQueryForAcceptedActivities($start_date, $end_date, $dealerId, true);

        foreach ($models as $model) {
            if (!array_key_exists($model['activity_id'], $this->activities_stat)) {
                continue;
            }

            if ($model) {
                $result[utils::eqModelDateFromLogEntryWithYearAndGetQuarter($model['id'], $this->year, $q)][] = $model;
            }
        }

        return $result;
    }

    protected function loadAcceptedActivitiesForQuarter()
    {
        $start_date = mktime(0, 0, 0, ($this->quarter - 1) * 3 + 1, 1, $this->year);
        $end_date = strtotime('+3 months', $start_date);

        $items = $this->getQueryForAcceptedActivities($start_date, $end_date);

        $temp = array();
        foreach ($items as $row) {
            if (!in_array($row->activity_id, $temp[$row->dealer_id])) {
                $temp[$row->dealer_id][] = $row->activity_id;
                $this->dealers[$row->dealer_id]['quarter_accepted']++;
            }
        }
    }

    protected function getQueryForAcceptedActivities($start_date, $end_date, $dealer_id = null, $result_as_array = false)
    {
        $result = array();
        $query = Doctrine_Query::create()
            ->select('ada.*')
            ->from('AgreementModel ada')
            ->innerJoin('ada.Report r')
            ->where('r.accept_date >= ? and r.accept_date < ?', array(D::toDb($start_date, true), D::toDb($end_date, true)))
            ->andWhere('ada.model_type_id != ?', self::CONCEPT_ID)
            ->orderBy('ada.dealer_id ASC');

        if (!is_null($dealer_id)) {
            $query->andWhere('ada.dealer_id = ?', $dealer_id);
        }

        $models = !$result_as_array ? $query->execute() : $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);;
        foreach ($models as $model) {
            if (utils::eqModelDateFromLogEntryWithYear(!$result_as_array ? $model->getId() : $model['id'], $this->year)) {
                $result[] = $model;
            }
        }

        return $result;
    }


    protected function loadActivitiesStat()
    {
        $activity_stat = new AgreementActivityStatisticBuilder(null, null, true);
        $activity_stat->setQuarterFilter($this->year, $this->quarter);
        $activity_stat->buildInWork();
        $activity_stat->buildDone();

        $this->activities_stat = $activity_stat->getStat();
        $this->dealers_statistic = $activity_stat->getDealerStat();
    }

    protected function loadDealers()
    {
        $this->dealers = array();
        $this->managers = array();

        $all_dealers = DealerTable::getVwDealersQuery()
            ->leftJoin('d.RegionalManager rm')
            ->orderBy('rm.firstname, rm.surname')
            ->execute();

        foreach ($all_dealers as $dealer) {
            $manager = $dealer->getRegionalManager();
            $manager_id = $manager ? $manager->getId() : 0;

            if (!isset($this->managers[$manager_id])) {
                $this->managers[$manager_id] = array(
                    'manager' => $manager ? $manager->getFirstName() . ' ' . $manager->getSurname() : 'Без менеджера',
                    'dealers' => array()
                );
            }

            $this->managers[$manager_id]['dealers'][] = $dealer;
            $this->dealers[$dealer->getId()] = $this->createDealerBlankStat($dealer);
        }
    }

    protected function createDealerBlankStat(Dealer $dealer)
    {
        $stat = array(
            'dealer' => $dealer,
            'statuses' => array(),
            'year_accepted' => 0,
            'models_accepted' => 0,
            'models_accepted_total_cost' => 0,
            'quarter_accepted' => 0,
            'quarter_accepted_required' => 0,
            'budget' => array(
                'plan' => 0,
                'fact' => 0,
                'percent' => 0
            )
        );

        foreach ($this->activities_stat as $id => $activity)
            $stat['statuses'][$id] = 'none';

        return $stat;
    }

}
