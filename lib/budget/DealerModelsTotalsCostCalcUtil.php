<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 12.11.2018
 * Time: 10:30
 */

class DealerModelsTotalsCostCalcUtil {
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
    public function buildData() {
        $dealers_total_cash_by_models = array();

        $quarter = D::getQuarter(time());
        $last_update_quarter = ActivityAcceptStatsUpdatesTable::getInstance()->createQuery()->where('year = ?', $this->_year)->fetchOne();
        if (!$last_update_quarter) {
            $last_update_quarter = new ActivityAcceptStatsUpdatesTable();
            $last_update_quarter->setYear(date('Y'));
            $last_update_quarter->setQuarter(1);
            $last_update_quarter->save();
        } else {
            $quarter = $last_update_quarter->getQuarter();
            $quarter++;

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
            if (!array_key_exists($dealer_id, $dealers_total_cash_by_models[$quarter])) {
                $dealers_total_cash_by_models[$quarter][$dealer_id] = array('total_models' => 0, 'total_models_cost' => 0);
            }

            $models_list = AgreementModelTable::getInstance()->createQuery('am')
                ->select('id, cost, dealer_id')
                ->innerJoin('am.Report r')
                ->andWhere('am.dealer_id = ?', $dealer_id)
                //Удаленные заявки не выбираем
                ->andWhere('is_deleted = ?', false)
                ->andWhere('(am.status = ? and r.status = ?)', array('accepted', 'accepted'))
                //->andWhere('(year(created_at) = ? or year(created_at) = ?)', array($this->_year, $this->_year - 1))
                ->andWhere('(year(created_at) = ?)', array($this->_year))
                ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            //Получаемя список заявок с привязкой к индексу заявки и стоимости заявки
            $models_list_with_cost = array();
            foreach ($models_list as $model) {
                $models_list_with_cost[$model['id']] = $model['cost'];
            }

            //Получаем индексы заявок для получения точной даты выполнения заявки
            $models_ids = array_map(function($item) {
                return $item['id'];
            }, $models_list);

            //Список выполненных заявок с корректными датами
            $models_dates = Utils::getModelDateFromLogEntryWithYear($models_ids);
            foreach ($models_dates as $model_item) {
                if (array_key_exists($model_item['object_id'], $models_list_with_cost)) {
                    //Дата выполнения заявки
                    $model_date = date('Y-m-d H:i:s', D::calcQuarterData($model_item['created_at']));

                    //Получаем квартал выполнения заявки и год
                    $model_quarter = D::getQuarter($model_date);
                    $model_year = D::getYear($model_date);

                    //Делаем проверку на квартал и год выполнения
                    if ($model_quarter == $quarter && $model_year == $this->_year) {
                        $dealers_total_cash_by_models[$quarter][$dealer_id]['total_models_cost'] += $models_list_with_cost[$model_item['object_id']]['cost'];
                        $dealers_total_cash_by_models[$quarter][$dealer_id]['total_models']++;
                    }
                }
            }
        }


        var_dump('done');
        exit;
    }

    private function buildDealersList() {
        //Проходим по всем активным дилерам и собираем информацию по общей сумме заявок за выбранный период
        $active_dealers_ids = array_map(function ($item) {
            return $item['id'];
        }, DealerTable::getInstance()->createQuery()
            ->select('id')
            ->where('status = ?', true)
            ->andWhere('(dealer_type = ? or dealer_type = ?)', array(Dealer::TYPE_PKW, Dealer::TYPE_NFZ_PKW))
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY));

        $this->_dealers_list = $active_dealers_ids;
    }
}
