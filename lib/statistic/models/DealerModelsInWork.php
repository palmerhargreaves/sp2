<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 29.11.2016
 * Time: 16:36
 */

class DealerModelsInWork extends DealerModelsAbstract implements ModelsStatisticInterface
{
    /**
     * DealerModelsInWork constructor.
     * @param $request
     */
    public function __construct($request)
    {
        parent::__construct($request);

    }

    /**
     * @return mixed
     */
    public function getModelsList()
    {
        return $this->_result['models'];
    }

    public function makeBaseQuery($filter_by_activity = true)
    {
        $query = parent::makeBaseQuery($filter_by_activity);

        /*By status*/
        $query->leftJoin('am.Report r');

        /*Filter by quarter*/
        //if (isset($this->_filter['quarter'])) {
            //$query->andWhere('quarter(created_at) = ?', $this->_filter['quarter']);
        //}

        if (isset($this->_filter['year'])) {
            $query->andWhere('year(created_at) = ?', $this->_filter['year']);
        }

        return $query;
    }

    /**
     * @return mixed
     */
    public function getTotalAmountByModels()
    {
        return $this->_result['total_amount'];
    }

    /**
     * Make query and get list of completed models and reports
     */
    protected function getData() {
        $query = $this->makeBaseQuery();

        /*Check models by selected status in filter*/
        if (isset($this->_filter['model_status']) && $this->_filter['model_status'] != 'all') {
            if ($this->_filter['model_status'] == 'declined') {
                $query->andWhere('am.status = ?', 'declined');
            } else if ($this->_filter['model_status'] == 'wait') {
                $query->andWhere('(am.status = ? or am.status = ?)', array('wait', 'wait_specialist'));
            } else if ($this->_filter['model_status'] == 'no_report') {
                $query->andWhere('am.status = ? and am.report_id IS NULL', 'accepted');
            } else if ($this->_filter['model_status'] == 'wait_report') {
                $query->andWhere('(r.status = ? or r.status = ?)', array('wait', 'wait_specialist'));
            } else if ($this->_filter['model_status'] == 'blocked') {
                $query->andWhere('am.is_blocked = ? and am.allow_use_blocked = ?', array(true, false));
            }
        }

        $by_quarter = -1;
        $by_year = D::getYear(D::calcQuarterData(time()));
        if (isset($this->_filter['quarter'])) {
            $by_quarter = $this->_filter['quarter'];
        }

        $activities_list_ids = array();

        $models_list = $query->execute();
        foreach ($models_list as $model) {
            if ($model->getStatus() == 'accepted' && $model->getReport() && $model->getReport()->getStatus() == 'accepted') {
                continue;
            }

            if (!array_key_exists($model->getActivityId(), $activities_list_ids) && isset($this->_params['user'])) {
                $quartersModels = new ActivityQuartersModelsAndStatistics($this->_params['user']->getAuthUser(), $model->getActivity());

                $qData = $quartersModels->getData();
                $qList = array();
                foreach ($qData as $q => $qItem) {
                    $qList = array_keys($qItem);
                }

                $currentQ = D::getQuarter(D::calcQuarterData(time()));
                if (!in_array($currentQ, $qList) && !empty($qList)) {
                    $currentQ = max($qList);
                }

                $activities_list_ids[$model->getActivityId()] = $currentQ;
            }

            $model_cls = '';
            if ($model->isModelBlocked()) {
                $model_cls = 'bc_red';
            } else if ($model->getStatus() == 'declined') {
                $model_cls = 'bc_orange';
            } else if ($model->getStatus() == 'wait' || $model->getStatus() == 'wait_specialist') {
                $model_cls = 'bc_blue';
            } else if (!$model->getReport()) {
                $model_cls = 'bc_orange';
            } else if ($model->getStatus() == 'accepted' && $model->getReport()
                && ($model->getReport()->getStatus() == 'wait' || $model->getReport()->getStatus() == 'wait_specialist')) {
                $model_cls = 'bc_blue';
            }

            $date = $model->getModelQuarterDate();

            $modelQuarter = D::getQuarter($date);
            $modelYear = D::getYear($date);

            if ($by_quarter != -1) {

                if ($modelYear != $by_year) {
                    continue;
                }

                /*Если текущий квартал не равен кварталу фильтра, выводим только заявки выполненный по кварталу фильтра или заблокированные */
                if ($currentQ != $by_quarter) {
                    if ($by_quarter == $modelQuarter && ($model->isModelCompleted() || $model->isModelBlocked())) {
                        $this->_result['models'][] = array('model' => $model, 'model_cls' => $model_cls, 'quarter' => $modelQuarter);
                    } else if ($by_quarter == $modelQuarter) {
                        $this->_result['models'][] = array('model' => $model, 'model_cls' => $model_cls, 'quarter' => $modelQuarter);
                    }
                } /*Проверка на текущий квартал и если заявка не выполнена */
                else if ($by_quarter == $modelQuarter || (!$model->isModelCompleted() && !$model->isModelBlocked())) {
                    $this->_result['models'][] = array('model' => $model, 'model_cls' => $model_cls, 'quarter' => $modelQuarter);
                }

                $this->_result['total_amount'] += $model->getCost();
            }
            //$this->_result['models'][] = array('model' => $model, 'model_cls' => $model_cls, 'quarter' => D::getQuarter($model->getCreatedAt()));
        }
    }

}
