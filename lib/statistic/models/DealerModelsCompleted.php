<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 29.11.2016
 * Time: 16:36
 */

class DealerModelsCompleted extends DealerModelsAbstract implements ModelsStatisticInterface
{
    private $_models_list = array();
    private $_total_amount = 0;

    /**
     * DealerModelsCompleted constructor.
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
        $this->getDataByParamType($query);
    }

    /**
     * Make query with with checking what param type is get (array to get models or int to get cost)
     * @param $query
     * @param array $param_type
     */
    private function getDataByParamType($query, $param_type = array()) {
        $query->innerJoin('am.Report r')
            ->andWhere('am.status = ? and r.status = ?', array('accepted', 'accepted'));

        $modelsIds = array();
        $models_list = $query->execute();

        if (count($models_list) > 0) {
            foreach ($models_list as $model) {
                $modelsIds[$model->getId()] = $model;
            }

            $models_list = array();
            $models_dates = Utils::getModelDateFromLogEntryWithYear(array_keys($modelsIds));

            foreach ($models_dates as $model_date_item)
            {
                /*Check for duplicate of model by id*/
                if (!in_array($model_date_item['object_id'], $models_list)) {
                    //Save model id in models list
                    $models_list[] = $model_date_item['object_id'];

                    //Get correct date of model status
                    $model_date = date('Y-m-d H:i:s', D::calcQuarterData($model_date_item['created_at']));

                    //Get model quarter / year
                    $model_quarter = D::getQuarter($model_date);
                    $model_year = D::getYear($model_date);

                    //If model and quarter equals, then add model in final list and sum total models cost
                    if ($model_quarter == $this->_filter['quarter'] && $model_year == $this->_filter['year']) {
                        $this->_result['models'][] = array
                        (
                            'model' => $modelsIds[$model_date_item['object_id']],
                            'model_cls' => 'bc_green',
                            'quarter' => $model_quarter,
                        );
                        $this->_result['total_amount'] += $modelsIds[$model_date_item['object_id']]->getCost();
                    }
                }
            }
        }
    }
}
