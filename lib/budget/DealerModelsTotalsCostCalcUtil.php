<?php

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 12.11.2018
 * Time: 10:30
 */
class DealerModelsTotalsCostCalcUtil
{
    private $_dealers_list = array();
    private $_year = 0;

    public function __construct()
    {
        $this->buildDealersList();

        $this->_year = date('Y');
    }

    /**
     * Собриаем информацию по дилерам
     */
    public function buildData()
    {
        $dealers_total_cash_by_models = array();

        //Получаем список ключей активностей
        $activities_ids = array_map(function ($item) {
            return $item['id'];
        }, $this->getActivitiesList());

        $work_activity = 0;

        $year = date('Y');
        $quarter = D::getQuarter(time());
        $last_update_quarter = ActivityAcceptStatsUpdatesTable::getInstance()->createQuery()->where('year = ?', $this->_year)->fetchOne();
        if (!$last_update_quarter) {
            $last_update_quarter = new ActivityAcceptStatsUpdatesTable();
            $last_update_quarter->setYear(date('Y'));
            $last_update_quarter->setQuarter(1);
            $last_update_quarter->setActivities(serialize($activities_ids));
            $last_update_quarter->save();
        } else {
            $quarter = $last_update_quarter->getQuarter();
            $last_activities_ids = $last_update_quarter->getActivities();

            if (empty($last_activities_ids)) {
                $work_activity = array_shift($activities_ids);
                $last_update_quarter->setActivities(serialize($activities_ids));

                $quarter++;
            } else {
                $last_activities_ids = unserialize($last_activities_ids);
                $work_activity = array_shift($last_activities_ids);

                $last_update_quarter->setActivities(!empty($last_activities_ids) ? serialize($last_activities_ids) : '');
            }

            $last_update_quarter->setQuarter($quarter);
            if ($quarter > 4) {
                $last_update_quarter->setQuarter(1);
                $quarter = 1;
            }
            $last_update_quarter->save();
        }

        //Учитываем квартал
        if (!array_key_exists($quarter, $dealers_total_cash_by_models)) {
            $dealers_total_cash_by_models[$quarter] = array();
        }

        //Проходим по всем дилерам для получения общей суммы учтенных заявок за выбранный период (квартал и год)
        foreach ($this->_dealers_list as $dealer_id) {
            $dealer_data = array();

            $query = AgreementModelTable::getInstance()->createQuery('am')->select('id, cost, model_category_id')->orderBy('am.id DESC');

            //Фильтр по активности
            $query->andWhere('am.activity_id = ?', $work_activity);

            //Фильтр по дилеру
            $query->andWhere('am.dealer_id = ?', $dealer_id);

            //Удаленные заявки не выбираем
            $query->andWhere('is_deleted = ?', false);

            //Только выполненные заявки
            $query->innerJoin('am.Report r')
                ->andWhere('am.status = ? and r.status = ?', array('accepted', 'accepted'));

            $models_list_with_cost = array();
            $models_ids = array();
            $models_list = array();

            $models = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
            foreach ($models as $model) {
                $models_ids[] = $model['id'];

                $models_list_with_cost[$model['id']] = $model;
            }
            unset($models);

            if (!empty($models_ids)) {
                $models_dates = Utils::getModelDateFromLogEntryWithYear($models_ids);

                unset($models_ids);
                foreach ($models_dates as $model_date_item) {
                    //Проверка на дубликат заявки
                    if (!in_array($model_date_item['object_id'], $models_list)) {
                        $models_list[] = $model_date_item['object_id'];

                        //Получаем корректную дату с учетом всех календарных дней
                        $model_date = date('Y-m-d H:i:s', D::calcQuarterData($model_date_item['created_at']));

                        //Получаем квартал / год
                        $model_quarter = D::getQuarter($model_date);
                        $model_year = D::getYear($model_date);

                        //Проверка на квартал / год
                        if ($model_quarter == $quarter && $model_year == $year) {
                            if (array_key_exists($model_date_item['object_id'], $models_list_with_cost)) {
                                $model = $models_list_with_cost[$model_date_item['object_id']];

                                //Проверяем на наличие категории заявки
                                if (!array_key_exists($model['model_category_id'], $dealer_data)) {
                                    $dealer_data[$model['model_category_id']] = array('cost' => 0, 'models' => 0);
                                }

                                $dealer_data[$model['model_category_id']]['cost'] += $model['cost'];
                                $dealer_data[$model['model_category_id']]['models']++;
                            }
                        }
                    }
                }
                unset($models_list);
                unset($models_list_with_cost);

                if (!empty($dealer_data)) {
                    foreach ($dealer_data as $model_category_id => $data) {
                        //Сохраняем данные с суммой больше 0
                        if ($data['cost'] > 0) {
                            $item = DealerModelsTotalCostTable::getInstance()->createQuery()
                                ->where('dealer_id = ? and activity_id = ? and category_id = ? and year = ? and quarter = ?',
                                    array(
                                        $dealer_id,
                                        $work_activity,
                                        $model_category_id,
                                        $year,
                                        $quarter
                                    ))->fetchOne();
                            if (!$item) {
                                $new_item = new DealerModelsTotalCost();
                                $new_item->setArray(array(
                                    'dealer_id' => $dealer_id,
                                    'activity_id' => $work_activity,
                                    'category_id' => $model_category_id,
                                    'year' => $year,
                                    'quarter' => $quarter,
                                    'cost' => $data['cost'],
                                    'models_count' => $data['models']
                                ));
                                $new_item->save();
                            } else {
                                $item->setArray(array(
                                    'cost' => $data['cost'],
                                    'models_count' => $data['models']
                                ));
                                $item->save();
                            }
                        }
                    }
                }

                unset($dealer_data);
            }
        }


        echo "done";
    }

    private function buildDealersList()
    {
        //Проходим по всем активным дилерам и собираем информацию по общей сумме заявок за выбранный период
        $active_dealers_ids = array_map(function ($item) {
            return $item['id'];
        }, DealerTable::getActiveDealersList()
            ->where('status = ?', true)
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY));

        $this->_dealers_list = $active_dealers_ids;
    }

    private function getActivitiesList()
    {
        return ActivityTable::getInstance()->createQuery()->select('id')->where('finished = ?', false)->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
    }
}
