<?php

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 15.12.2016
 * Time: 10:46
 */
class ServiceClinicStatisticUtils
{
    /**
     * @var array
     */
    private $filter = array();

    /**
     * @var array
     */
    private $_activities = array();

    /**
     * @var array
     */
    private $_years = array();

    /**
     * @var array
     */
    private $_models = array();

    /**
     * @var array
     */
    private $_stats = array();

    public function __construct($filter = array())
    {
        $this->filter = $filter;

        $this->initStats();

        $this->loadActivities();
        $this->loadConcepts();

        $this->initFilter();
    }

    /**
     * @return array
     */
    public function getActivitiesList()
    {
        return $this->_activities;
    }

    /**
     * return array
     */
    public function getYearsList()
    {
        return $this->_years;
    }

    public function export()
    {
        $concepts_models_stats = arraY
        (
            'total_concepts' => 0,
            'total_concepts_completed' => 0,
            'total_concepts_in_work' => 0,
            'total_models' => 0,
            'total_models_completed' => 0,
            'total_models_in_work' => 0

        );

        $export_result = array();

        $checked_ids = array();
        $concepts_ids = array_keys($this->_stats['models']);

        $logs_list = Utils::getModelDateFromLogEntryWithYear($concepts_ids, false);

        $activity = $this->_activities->getFirst();
        //Check for dates when concept was completed
        foreach ($logs_list as $log_item) {
            if (in_array($log_item['object_id'], $checked_ids)) {
                continue;
            }

            $checked_ids[] = $log_item['object_id'];
            $concept_completed_date = D::calcQuarterData($log_item['created_at']);

            $year = D::getYear($concept_completed_date);
            $q = D::getQuarter($concept_completed_date);

            if (isset($this->_stats['models'][$log_item['object_id']])) {
                foreach ($this->_stats['models'][$log_item['object_id']] as $model_id => $models) {
                    if (!empty($models['concept_data'])) {
                        $concept_data = $models['concept_data'];

                        $statistic_status = $activity->checkServiceClinicStatisticFillByConcept($concept_data['concept']);
                        if (!empty($statistic_status) && isset($statistic_status[$log_item['object_id']])) {
                            $concept_data['statistic_completed'] = $statistic_status[$log_item['object_id']];
                        }

                        $concept_data['year'] = $year;
                        $concept_data['q'] = $q;

                        $this->_stats['models'][$log_item['object_id']][$model_id]['concept_data'] = $concept_data;
                    }
                }
            }
        }

        /** */
        foreach (array_diff($concepts_ids, $checked_ids) as $key => $concept_id) {
            if (isset($this->_stats['models'][$concept_id])) {
                foreach ($this->_stats['models'][$concept_id] as $model_id => $models) {
                    if (!empty($models['concept_data'])) {
                        $concept_data = $models['concept_data'];

                        $concept_completed_date = D::calcQuarterData($concept_data['concept']['c_created_at']);

                        $year = D::getYear($concept_completed_date);
                        $q = D::getQuarter($concept_completed_date);

                        $statistic_status = $activity->checkServiceClinicStatisticFillByConcept($concept_data['concept']);
                        if (!empty($statistic_status) && isset($statistic_status[$concept_id])) {
                            $concept_data['statistic_completed'] = $statistic_status[$concept_id];
                        }

                        $concept_data['year'] = $year;
                        $concept_data['q'] = $q;

                        $this->_stats['models'][$concept_id][$model_id]['concept_data'] = $concept_data;
                    }
                }
            }
        }

        $cps_ids = array();

        //Filter data by year / quarter
        foreach ($this->_stats['models'] as $key => $models) {
            foreach ($models as $model_id => $model) {
                $year = $model['year'];
                if (isset($model['concept_data']['year'])) {
                    $year = $model['concept_data']['year'];
                }

                if ($this->filter['year'] != $year) {
                    continue;
                }

                if ($this->filter['q'] != -1 && $this->filter['q'] != $model['concept_data']['q']) {
                    continue;
                }

                /** Calc total concepts count */
                if (!in_array($model['concept_data']['concept_id'], $cps_ids)) {
                    $concepts_models_stats['total_concepts']++;
                    $cps_ids[] = $model['concept_data']['concept_id'];

                    if ($model['concept_data']['concept_completed']) {
                        $concepts_models_stats['total_concepts_completed']++;
                    } else {
                        $concepts_models_stats['total_concepts_in_work']++;
                    }
                }

                /** Calc total models count */
                $concepts_models_stats['total_models']++;

                if ($model['model_completed']) {
                    $concepts_models_stats['total_models_completed']++;
                } else {
                    $concepts_models_stats['total_models_in_work']++;
                }

                $export_result[$model['model']['m_dealer_id']][] = $model;
            }
        }

        asort($export_result);

        $this->_stats['models'] = $export_result;
        $this->_stats['concepts_models_stats'] = $concepts_models_stats;

        return $this->_stats;
    }

    private function loadActivities()
    {
        $query = ActivityTable::getInstance()->createQuery()->where('allow_extended_statistic = ?', true)->orderBy('id ASC');
        if (isset($this->filter['activity_id']) && $this->filter['activity_id'] != -1) {
            $query->andWhere('id = ?', $this->filter['activity_id']);
        }

        $this->_activities = $query->execute();
    }

    private function loadConcepts() {
        foreach ($this->_activities as $activity) {
            $query = AgreementModelTable::getInstance()
                ->createQuery('c')
                ->select('c.id as c_id, c.created_at c_created_at, c.status c_status, c.concept_id, c.dealer_id c_dealer_id, r.status r_status, c.created_at c_created_at')
                ->leftJoin('c.Report r')
                ->where('c.activity_id = ? and c.model_type_id = ?', array($activity->getId(), AgreementModel::CONCEPT_TYPE_ID))
                ->orderBy('c.id ASC');

            $concepts = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            foreach ($concepts as $concept) {
                $models = $query = AgreementModelTable::getInstance()
                    ->createQuery('am')
                    ->select('am.id as m_id, am.created_at m_created_at, am.status m_status, am.concept_id, am.dealer_id m_dealer_id, r.status r_status')
                    ->leftJoin('am.Report r')
                    ->where('am.concept_id = ?', $concept['c_id'])
                    ->orderBy('am.id ASC')
                    ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                $q = D::getQuarter($concept['c_created_at']);
                $year = D::getYear($concept['c_created_at']);

                //Check if model has bind to concept and concept already exists
                $concept_completed = $concept['c_status'] == 'accepted' && $concept['r_status'] == 'accepted';

                $concept_data = array(
                    'concept_id' => $concept['c_id'],
                    'concept_completed' => $concept_completed,
                    'statistic_completed' => false,
                    'concept' => $concept,
                    'q' => -1,
                );

                if (count($models) > 0) {
                    foreach ($models as $model) {
                        //Get model year
                        $model_completed = $model['m_status'] == 'accepted' && isset($model['Report']) && $model['r_status'] == 'accepted';

                        $this->_stats['models'][$model['concept_id']][$model['m_id']] = array(
                            'model' => $model,
                            'concept_data' => $concept_data,
                            'year' => $year,
                            'in_quarter' => $q,
                            'model_completed' => $model_completed,
                        );
                    }
                } else {
                    $this->_stats['models'][$concept['c_id']][$concept['c_id']] = array(
                        'model' => null,
                        'concept_data' => $concept_data,
                        'year' => $year,
                        'in_quarter' => $q,
                        'model_completed' => false,
                    );
                }

                if (!in_array($year, $this->_years)) {
                    $this->_years[] = $year;
                }
            }
        }

    }

    private function loadModels()
    {
        foreach ($this->_activities as $activity) {
            $query = AgreementModelTable::getInstance()
                ->createQuery('am')
                ->select('am.id as m_id, am.created_at m_created_at, am.status m_status, am.concept_id, am.dealer_id m_dealer_id,
                                r.status r_status, c.id c_id,
                                c.status c_status, c.dealer_id c_dealer_id,
                                cr.status cr_status')
                ->leftJoin('am.Report r')
                ->leftJoin('am.Concept c')
                ->leftJoin('c.Report cr')
                ->where('activity_id = ? and concept_id != 0', $activity->getId())
                ->andWhere('am.model_type_id != ?', AgreementModel::CONCEPT_TYPE_ID)
                ->orderBy('am.id ASC');

            $models = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
            foreach ($models as $model) {
                $model_completed = false;
                $concept_completed = false;

                //Get model year
                if ($model['m_status'] == 'accepted' && isset($model['Report']) && $model['r_status'] == 'accepted') {
                    $model_completed = true;
                }

                $q = D::getQuarter($model['m_created_at']);
                $year = D::getYear($model['m_created_at']);

                //Check if model has bind to concept and concept already exists
                if ($model['concept_id'] != 0 && !is_null($model['Concept'])) {
                    $concept = $model['Concept'];

                    $concept_completed = $model['c_status'] == 'accepted' && isset($model['Concept']) && $model['cr_status'] == 'accepted';
                    if ($concept_completed)
                    {
                        $this->_stats['models_with_concept']++;
                        $concept_data = array(
                            'concept_completed' => $concept_completed,
                            'statistic_completed' => false,
                            'concept' => $concept,
                            'q' => -1,
                        );

                        $this->_stats['models'][$model['concept_id']][$model['m_id']] = array(
                            'model' => $model,
                            'concept_data' => $concept_data,
                            'year' => $year,
                            'in_quarter' => $q,
                            'model_completed' => $model_completed,
                        );
                    }
                }

                if ($model_completed && !$concept_completed) {
                    //$year = D::getYear($model_date);
                    $this->_stats['models_completed']++;
                } else {
                    $this->_stats['model_in_work']++;
                }

                if (!in_array($year, $this->_years)) {
                    $this->_years[] = $year;
                }
            }
        }
    }

    private function initStats()
    {
        $this->_stats = array(
            'models' => array(),
            'models_with_concept' => 0,
            'models_concept_completed' => 0,
            'models_concept_in_work' => 0,
            'models_only' => 0,
            'model_in_work' => 0,
            'models_completed' => 0
        );
    }

    /**
     * @param $concept_id
     */
    private function getConcept($concept_id)
    {
        return AgreementModelTable::getInstance()->createQuery('c')
            ->select('c.id c_id, dealer_id, c.status c_status, r.status r_status')
            ->leftJoin('c.Report r')
            ->where('id = ?', $concept_id)
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
    }

    private function initFilter()
    {
        if (isset($this->filter['year']) && $this->filter['year'] == -1) {
            $this->filter['year'] = date('Y');
        }
    }
}
