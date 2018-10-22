<?php

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 10.10.2018
 * Time: 9:53
 */
class ActivityConsolidatedInformation
{
    private $_dealers = array();
    private $_quarters_list = array();

    private $_year = 0;

    private $_activity = null;
    private $_activity_company = null;

    private $_regional_manager = null;

    public function __construct(Activity $activity, sfWebRequest $request = null)
    {
        $this->_year = date('Y');

        $this->_activity = $activity;

        $this->_dealers = array(
            'count' => 0,
            'service_action_count' => 0,
            'models_count' => 0,
            'statistic_completed_count' => 0
        );

        $this->getQuarters();
        $this->filterData($request);
    }

    public function getQuarters()
    {
        $period = ActivityStatisticPeriodsTable::getInstance()->createQuery()->where('activity_id = ?', $this->_activity->getId())->fetchOne();
        if ($period) {
            $quarters = $period->getQuarters();
            $this->_quarters_list = explode(":", $quarters);
        }

        return $this->_quarters_list;
    }

    /**
     * Фильтр данных
     * @param sfWebRequest $request
     */
    public function filterData(sfWebRequest $request = null)
    {
        $activity_id = $this->_activity->getId();
        $query = DealerTable::getInstance()->createQuery()->select('id')->where('status = ?', array(true));

        if (!is_null($request)) {
            //Сохраняем список кварталов
            $this->_quarters_list = $request->getParameter('quarters', array());

            //Получаем данные по рег. менеджеру
            $regional_manager_id = $request->getParameter('regional_manager', -1);
            if ($regional_manager_id != -1) {
                $query->andWhere('regional_manager_id = ?', $regional_manager_id);

                $this->_regional_manager = NaturalPersonTable::getInstance()->find($regional_manager_id);
            }
        }
        $dealers_list = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        //Кампания активности
        $this->_activity_company = ActivityCompanyTypeTable::getInstance()->find($this->_activity->getTypeCompanyId());

        //Общее количество дилеров
        $this->_dealers['count'] = count($dealers_list);

        //Получаем список индексов доступных дилеров
        $dealers_ids = array_map(function ($item) {
            return $item['id'];
        }, $dealers_list);

        if (!empty($dealers_ids)) {
            //Получаем количество дилеров создавших заявку в активности
            $models_count = AgreementModelTable::getInstance()->createQuery()->where('activity_id = ?', $activity_id)->andWhereIn('dealer_id', $dealers_ids)->groupBy('dealer_id')->count();

            //Получаем количество дилеров участвующих в акции, если не формы нет берем количестов дилеров по созданным заявкам
            if (DealersServiceDataTable::getInstance()->createQuery('sd')->innerJoin('sd.Dialog d')->andWhere('d.activity_id = ?', $activity_id)->count() > 0) {
                $service_action_count = DealersServiceDataTable::getInstance()->createQuery('sd')
                    ->innerJoin('sd.Dialog d')
                    ->andWhereIn('sd.dealer_id', $dealers_ids)
                    ->andWhere('d.activity_id = ?', $activity_id)
                    ->andWhere('sd.status = ?', 'accepted')
                    ->count();
            } else {
                $service_action_count = $models_count;
            }

            $this->_dealers['service_action_count'] = $service_action_count;
            $this->_dealers['models_count'] = $models_count;


            //Получаем информацию по количеству заполненных статистик с учетом кварталов активности
            $query = ActivityDealerStaticticStatusTable::getInstance()->createQuery()
                ->where('activity_id = ?', $activity_id)
                ->andWhereIn('dealer_id', $dealers_ids);

            $query_columns = array();
            $query_params = array();
            foreach ($this->_quarters_list as $quarter) {
                $query_columns[] = 'ignore_q' . $quarter . '_statistic = ?';
                $query_params[] = 0;
            }

            if (!empty($query_columns)) {
                $query_columns = implode(' and ', $query_columns);

                $query->andWhere('(' . $query_columns . ')', $query_params);
            }

            $this->_dealers['statistic_completed_count'] = $query->count();
        }

    }

    public function getActivity()
    {
        return $this->_activity;
    }

    public function getCompany()
    {
        return $this->_activity_company;
    }

    public function getExportQuartersList()
    {
        return $this->_quarters_list;
    }

    public function getManager()
    {
        return $this->_regional_manager;
    }

    /**
     * Получить сводную информацию по дилерам
     */
    public function getDealersInformation()
    {
        return $this->_dealers;
    }
}
