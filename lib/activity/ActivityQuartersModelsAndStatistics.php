<?php
use Fxp\Composer\AssetPlugin\Repository\Util;

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 16.04.2016
 * Time: 12:05
 */
class ActivityQuartersModelsAndStatistics
{
    private $_quarters_years = array();
    private $_cal_quarter = 0;
    private $_current_quarter = 0;

    private $_dealer = null;
    private $_activity = null;
    private $_user = null;

    const CONCEPT = 1;
    const MODEL = 2;
    const ALL_TYPES = 3;

    /**
     * ActivityQuartersModelsAndStatistics constructor.
     * @param User $user
     * @param Activity $activity
     */
    public function __construct($user, Activity $activity)
    {
        $this->_user = $user;

        $this->_dealer = $this->getDealer($user);
        $this->_activity = $activity;

        $this->_current_quarter = D::getQuarter(D::calcQuarterData(time()));
    }

    /**
     * Load data by dealer and activity
     */
    public function getData() {
        $quarters = $this->getDataModelsList(self::ALL_TYPES, true);
        $statistics_q_status = array();

        //Get list of models / concepts
        $concepts_result = $this->getModelsTotalCompleted($this->getDataModelsList(self::CONCEPT));
        $models_result = $this->getModelsTotalCompleted($this->getDataModelsList(self::MODEL));

        //Collecting data to check statistic complete
        if ($this->_dealer) {
            foreach ($quarters as $q_year => $y_data) {
                foreach ($y_data as $q_key => $q_data) {
                    if ($this->_activity->getActivityField()->count() > 0) {
                        $statistics_q_status[ $q_year ][ $q_data[ 'quarter' ] ] = $this->_activity->checkForSimpleStatisticComplete($this->_dealer->getId(), $q_data[ 'quarter' ], $q_data[ 'year' ]);
                    } elseif ($this->_activity->getAllowExtendedStatistic()) {
                        $statistics_q_status[ $q_year ][ $q_data[ 'quarter' ] ] = $this->_activity->checkForStatisticComplete($this->_dealer->getId(), $q_data[ 'quarter' ], $q_data[ 'year' ]);
                    }
                }
            }
        }

        //Fill result from list of quarters by checking if complete concepts and models
        $result = array();

        foreach ($quarters as $q_year => $y_data) {
            foreach ($y_data as $q_key => $q_data) {
                //Если для активности установлена галочка спец. согласование, делаем проверку на выполнение концепций по активности
                if ($this->_activity->getAllowSpecialAgreement()) {
                    if ($this->_activity->getIsConceptComplete()) {
                        if ((isset($concepts_result[$q_year][$q_data['quarter']]) && $concepts_result[$q_year][$q_data['quarter']]['data']['completed']) && (isset($models_result[$q_year][$q_data['quarter']]) && $models_result[$q_year][$q_data['quarter']]['data']['completed'])) {
                            $result[$q_year][$q_data['quarter']] = $concepts_result[$q_year][$q_data['quarter']];
                        }
                    }
                } else {
                    if ($this->_activity->getIsConceptComplete()) {
                        if ((isset($concepts_result[$q_year][$q_data['quarter']]) && $concepts_result[$q_year][$q_data['quarter']]['data']['completed']) && (isset($models_result[$q_year][$q_data['quarter']]) && $models_result[$q_year][$q_data['quarter']]['data']['completed'])) {
                            $result[$q_year][$q_data['quarter']] = $concepts_result[$q_year][$q_data['quarter']];
                        }
                    } //Check for activity statistic fill and exists
                    else if (isset($statistics_q_status[ $q_year ][ $q_data[ 'quarter' ] ]) && ( isset($concepts_result[ $q_year ][ $q_data[ 'quarter' ] ]) && $concepts_result[ $q_year ][ $q_data[ 'quarter' ] ][ 'data' ][ 'completed' ] ) && isset($models_result[ $q_year ][ $q_data[ 'quarter' ] ]) && $models_result[ $q_year ][ $q_data[ 'quarter' ] ][ 'data' ][ 'completed' ] && $statistics_q_status[ $q_year ][ $q_data[ 'quarter' ] ]) {
                        $result[ $q_year ][ $q_data[ 'quarter' ] ] = $concepts_result[ $q_year ][ $q_data[ 'quarter' ] ];
                    } //Check for complete concepts or models
                    else if (( isset($concepts_result[ $q_year ][ $q_data[ 'quarter' ] ]) && $concepts_result[ $q_year ][ $q_data[ 'quarter' ] ][ 'data' ][ 'completed' ] ) || ( isset($models_result[ $q_year ][ $q_data[ 'quarter' ] ]) && $models_result[ $q_year ][ $q_data[ 'quarter' ] ][ 'data' ][ 'completed' ] )) {
                        if (isset($concepts_result[ $q_year ][ $q_data[ 'quarter' ] ]) && $concepts_result[ $q_year ][ $q_data[ 'quarter' ] ][ 'data' ][ 'completed' ]) {
                            $result[ $q_year ][ $q_data[ 'quarter' ] ] = $concepts_result[ $q_year ][ $q_data[ 'quarter' ] ];
                        }

                        if (isset($models_result[ $q_year ][ $q_data[ 'quarter' ] ]) && $models_result[ $q_year ][ $q_data[ 'quarter' ] ][ 'data' ][ 'completed' ]) {
                            $result[ $q_year ][ $q_data[ 'quarter' ] ] = $models_result[ $q_year ][ $q_data[ 'quarter' ] ];
                        }
                    }
                }

                //Fill empty data for concept if not any rules are accepted
                if (!isset($result[$q_year][$q_data['quarter']])) {
                    if (isset($concepts_result[$q_year][$q_data['quarter']])) {
                        $result[$q_year][$q_data['quarter']] = $concepts_result[$q_year][$q_data['quarter']];
                        $result[$q_year][$q_data['quarter']]['data']['completed'] = $concepts_result[$q_year][$q_data['quarter']]['data']['completed'];
                    } //Fill empty data for models
                    else if (isset($models_result[$q_year][$q_data['quarter']]) || isset($concepts_result[$q_year][$q_data['quarter']])) {
                        $result[$q_year][$q_data['quarter']] = $models_result[$q_year][$q_data['quarter']];
                        $result[$q_year][$q_data['quarter']]['data']['completed'] = $models_result[$q_year][$q_data['quarter']]['data']['completed'];
                    }
                }
            }
        }

        /*uasort($result, function($a, $b) {
            return $a['data']['year'] > $b['data']['year'] ? 1 : -1;
        });

        $years_list = array();
        foreach ($result as $q => $q_data) {
            $years_list[$q_data['data']['year']][$q] = $result[$q];
        }

        $result = array();
        foreach ($years_list as $year => $year_data) {
            ksort($years_list[$year]);

            foreach ($years_list[$year] as $q => $q_data) {
                $result[$year][$q] = $q_data;
            }
        }*/
        return $result;
    }

    private function getModelsTotalCompleted($models)
    {
        $is_necessarily_activity = false;
        if (count($models) > 0) {
            $activity_id = $models->getFirst()->getActivityId();

            $is_necessarily_activity = ActivityModelsTypesNecessarilyTable::getInstance()->createQuery()->where('activity_id = ?', $activity_id)->count();
        }

        $result = array();
        foreach($models as $model) {
            if ($model->isModelCompleted()) {
                $acceptedDate = $model->getModelQuarterDate(null, $this->_user);

                $q = D::getQuarter($acceptedDate);
                $year = D::getYear($acceptedDate);

                //Коррекция по году и кварталу
                //list($year, $q) = Utils::correctYearAndQ($model->getCreatedAt(), $year, $q);

                if (!isset($result[$year][$q]['data'])) {
                    $result[$year][$q]['data'] = array
                    (
                        'year' => D::getYear($acceptedDate),
                        'total_completed' => 1,
                        'completed' => true,
                        'total_necessarily_models_complete' => $model->getIsNecessarilyModel() != 0 ? 1 : 0
                    );
                } else {
                    if ($model->getIsNecessarilyModel() != 0) {
                        $result[$year][$q]['data']['total_necessarily_models_complete']++;
                    } else {
                        $result[$year][$q]['data']['total_completed']++;
                        $result[$year][$q]['data']['completed'] = true;
                    }
                }
            } else {
                $calc_model_date = $model->getModelQuarterDate(null, $this->_user);
                $q = D::getQuarter($calc_model_date);
                $year = D::getYear($calc_model_date);

                //Коррекция по году и кварталу
                //list($year, $q) = Utils::correctYearAndQ($model->getCreatedAt(), $calcYear, $q);

                if (!isset($result[$year][$q]['data']['total_completed'])) {
                    $result[$year][$q]['data'] = array
                    (
                        'year' => $year,//D::getYear($calc_model_date),
                        'total_completed' => 0,
                        'completed' => false,
                        'total_necessarily_models_complete' => 0
                    );
                }
            }
        }

        //Обязательные заявки по активности
        if ($is_necessarily_activity > 0) {
            foreach ($result as $q_year => $q_data) {
                foreach ($q_data as $q_key => $data) {
                    if ($data['data']['total_necessarily_models_complete'] == 0 && $data['data']['total_completed'] == 0) {
                        $result[$year][$q]['data']['completed'] = false;
                    } else if ($data['data']['total_necessarily_models_complete'] > 0 && $data['data']['total_necessarily_models_complete'] == $is_necessarily_activity) {
                        $result[$year][$q]['data']['completed'] = true;
                    }
                }
            }
        }

        //Принудительное выполнение кварталов
        if ($this->_dealer) {
            foreach ($result as $q_year => $q_data) {
                foreach ($q_data as $q_key => $data) {
                    $forcibly_completed = ActivitiesStatusByUsersTable::checkActivityStatus($this->_activity->getId(), $this->_dealer->getId(), $q_year, $q_key);

                    $result[ $year ][ $q_key ][ 'data' ][ 'forcibly_completed' ] = false;
                    if ($forcibly_completed) {
                        $result[ $year ][ $q_key ][ 'data' ][ 'forcibly_completed' ] = true;
                        $result[ $year ][ $q_key ][ 'data' ][ 'completed' ] = true;
                    }
                }
            }
        }

        return $result;
    }

    private function getDataModelsList($model_type, $calc_q = false) {
        $query = AgreementModelTable::getInstance()
            ->createQuery()
            ->where('activity_id = ?', array($this->_activity->getId()));

        if ($this->_dealer) {
            $query->andWhere('dealer_id = ?', $this->_dealer->getId());
        }

        if ($model_type == self::CONCEPT) {
            $query->andWhere('model_type_id = ?', Activity::CONCEPT_MODEL_TYPE_ID);
        } else if($model_type == self::MODEL) {
            $query->andWhere('model_type_id != ?', Activity::CONCEPT_MODEL_TYPE_ID);
        }

        if ($calc_q) {
            $items = $query->execute();

            $qs = array();
            /** @var AgreementModel $item */
            foreach($items as $item) {
                $date = $item->getModelQuarterDate();

                $q = D::getQuarter($date);
                $year = D::getYear($date);

                //Коррекция по году и кварталу
                //list($year, $q) = Utils::correctYearAndQ($item->getCreatedAt(), $year, $q);

                $qs[$year][$q] = array('quarter' => $q, 'year' => $year);
            }
            return $qs;
        }

        return $query->execute();
    }

    private function getDealer($user) {
        $dealer = $this->getUserDealer($user);

        if (!$dealer) {
            $dealer_user = DealerUserTable::getInstance()->findOneByUserId($user->getId());

            if (!$dealer_user) {
                $dealer_user = new DealerUser();
                $dealer_user->setUser($user);
                $dealer_user->setManager(true);
            }

            $dealer_user->setDealer($dealer);
            $dealer_user->save();

            $dealer = $this->getUserDealer($user);;
        }

        return $dealer;
    }

    private function getUserDealer($user) {
        $dealer= null;

        $userDealer = $user->getDealerUsers()->getFirst();
        if ($userDealer) {
            $dealer = DealerTable::getInstance()->createQuery('d')->where('id = ?', $userDealer->getDealerId())->fetchOne();
        }

        return $dealer;
    }
}
