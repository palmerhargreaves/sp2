<?php

require sfConfig::get('app_root_dir').'lib/vendor/autoload.php';

use SamChristy\PieChart\PieChartGD;

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 10.10.2018
 * Time: 9:53
 */
class ActivityConsolidatedInformation
{
    const GRAPH_TYPE_WATERFALL = 'waterfall';
    const GRAPH_TYPE_PIE = 'pie';

    private $_dealers = array();
    private $_quarters_list = array();

    private $_year = 0;

    private $_activity = null;
    private $_activity_company = null;
    private $_activity_statistic = null;

    private $_regional_manager = null;

    private $_dealers_completed_levels = array();
    private $_dealers_pages = array();

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
        $query = DealerTable::getInstance()->createQuery()->select('id, name, number')->where('status = ?', array(true))->orderBy('id ASC');

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
        $dealers_list = $query->execute();

        //Получаем список дилеров по индексу
        $dealers_list_by_index = array();
        foreach ($dealers_list as $dealer) {
            $dealers_list_by_index[$dealer->getId()] = array('id' => $dealer->getId(), 'name' => $dealer->getName(), 'number' => $dealer->getShortNumber());
        }

        //Кампания активности
        $this->_activity_company = ActivityCompanyTypeTable::getInstance()->find($this->_activity->getTypeCompanyId());

        //Общее количество дилеров
        $this->_dealers['count'] = count($dealers_list);

        //Получаем список индексов доступных дилеров
        $dealers_ids = array_map(function ($item) {
            return $item['id'];
        }, $dealers_list_by_index);

        //Список дилеров, отсортированных по максимольно выполненным условиям (участие в акции, заполнение статистики, добавление заявки)
        $this->_dealers_completed_levels = array(
            'level_1' => array(),
            'level_2' => array(),
            'level_3' => array(),
            'level_4' => array()
        );

        $levels_index = array(3 => 'level_1', 2 => 'level_2', 1 => 'level_3', 0 => 'level_4');

        if (!empty($dealers_ids)) {
            //Получаем количество дилеров создавших заявку в активности
            $models_count = AgreementModelTable::getInstance()->createQuery()->where('activity_id = ?', $activity_id)->andWhereIn('dealer_id', $dealers_ids)->groupBy('dealer_id')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            //Получаем количество дилеров участвующих в акции, если не формы нет берем количестов дилеров по созданным заявкам
            if (DealersServiceDataTable::getInstance()->createQuery('sd')->innerJoin('sd.Dialog d')->andWhere('d.activity_id = ?', $activity_id)->count() > 0) {
                $service_action_count = DealersServiceDataTable::getInstance()->createQuery('sd')
                    ->innerJoin('sd.Dialog d')
                    ->andWhereIn('sd.dealer_id', $dealers_ids)
                    ->andWhere('d.activity_id = ?', $activity_id)
                    ->andWhere('sd.status = ?', 'accepted')
                    ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
            } else {
                $service_action_count = $models_count;
            }

            $this->_dealers['service_action_count'] = count($service_action_count);
            $this->_dealers['models_count'] = count($models_count);

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

            $statistic_completed_count = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
            $this->_dealers['statistic_completed_count'] = count($statistic_completed_count);

            foreach ($dealers_ids as $dealer_id) {
                //Дилеры участвующие в акции
                $completed_level = 0;
                $completed_service_action = false;
                foreach ($service_action_count as $service_action) {
                    if ($dealer_id == $service_action['dealer_id']) {
                        $completed_level++;
                        $completed_service_action = true;
                    }
                }

                //Дилеры которые заполнили статистику
                $statistic_completed = false;
                foreach ($statistic_completed_count as $statistic_item) {
                    if ($dealer_id == $statistic_item['dealer_id']) {
                        $completed_level++;
                        $statistic_completed = true;
                    }
                }

                //Дилеры у которых есть созданная заявка
                $models_completed = false;
                foreach ($models_count as $model_item) {
                    if ($dealer_id == $model_item['dealer_id']) {
                        $completed_level++;
                        $models_completed = true;
                    }
                }

                $this->_dealers_completed_levels[$levels_index[$completed_level]][] = array(
                    'dealer' => $dealers_list_by_index[$dealer_id],
                    'service_action' => $completed_service_action,
                    'statistic_completed' => $statistic_completed,
                    'models_completed' => $models_completed
                );

                //Получаем данные по статистики активности
                //Получаем список полей которые должны выводиться в выгрузке, максимальное количестов полей - 5
                $activity_sections_with_fields = array();

                //Делаем проверку на возможность вывода блоков
                foreach (ActivityExtendedStatisticSectionsTable::getInstance()->createQuery()->where('activity_id = ? and graph_type != ?', array($activity_id, 'none'))->orderBy('id ASC')->execute() as $section) {
                    $activity_sections_with_fields[$section->getId()] = array('section_data' => $section, 'fields' => array(), 'graph_data' => array());
                }

                $activity_statistic_fields_list = ActivityExtendedStatisticFieldsTable::getInstance()->createQuery()->where('activity_id = ? and show_in_export = ?', array($activity_id, true))->execute();
                foreach ($activity_statistic_fields_list as $field_item) {
                    if (array_key_exists($field_item->getParentId(), $activity_sections_with_fields)) {
                        $activity_sections_with_fields[$field_item->getParentId()]['fields'][$field_item->getId()] = array('value' => 0, 'name' => $field_item->getHeader());
                    }
                }
            }

            //Суммируем данные заполненные дилером
            $field_values_by_max = array();
            foreach ($activity_statistic_fields_list as $field) {
                $field_values = ActivityExtendedStatisticFieldsDataTable::getInstance()->createQuery()
                    ->where('field_id = ?', array($field->getId()))
                    ->andWhereIn('dealer_id', $dealers_ids)
                    ->andWhere('year = ?', array($this->_year))
                    ->andWhereIn('quarter', $this->_quarters_list)
                    ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                if (!array_key_exists($field->getId(), $field_values_by_max)) {
                    $field_values_by_max[$field->getId()] = 0;
                }

                foreach ($field_values as $field_value) {
                    if (!empty($field_value['value'])) {
                        $activity_sections_with_fields[$field->getParentId()]['fields'][$field->getId()]['value'] += floatval($field_value['value']);
                    }

                    if ($activity_sections_with_fields[$field->getParentId()]['section_data']->getGraphType() == self::GRAPH_TYPE_WATERFALL) {
                        $field_values_by_max[$field->getId()] = $activity_sections_with_fields[$field->getParentId()]['fields'][$field->getId()]['value'];
                    }
                }
            }
            arsort($field_values_by_max);

            if (!empty($field_values_by_max)) {
                $graph = new PieChartGD(350, 250);
                $graph->setLegend(false);
                foreach (array_values($field_values_by_max) as $value) {
                    if ($value != 0) {
                        $graph->addSlice('', $value, '#22aacc');
                    }
                }

                $file_name = sfConfig::get('app_root_dir').'www/pdf/images/test.png';

                $graph->draw();
                $graph->savePNG($file_name);

            }

            $this->_activity_statistic = array(
                'statistic_data' => $activity_sections_with_fields,
                'fields_values_by_max' => $field_values_by_max,
            );
        }

        $page_index = 1;
        $page_items = 1;
        $per_page = 20;

        foreach ($this->_dealers_completed_levels as $level => $level_items) {
            foreach ($level_items as $level_item) {
                if (!array_key_exists($page_index, $this->_dealers_pages)) {
                    $this->_dealers_pages[$page_index] = array();
                    $this->_dealers_pages[$page_index][$level] = array();
                }

                $this->_dealers_pages[$page_index][$level][] = $level_item;

                if (ceil($page_items % $per_page) == 0) {
                    $page_index++;
                }
                $page_items++;
            }
        }
    }

    /**
     * Получить данные по заполненной статистике
     */
    public
    function getActivityStatistic()
    {
        return $this->_activity_statistic;
    }

    public
    function getDealersPages()
    {
        return $this->_dealers_pages;
    }

    public
    function getActivity()
    {
        return $this->_activity;
    }

    public
    function getCompany()
    {
        return $this->_activity_company;
    }

    public
    function getExportQuartersList()
    {
        return $this->_quarters_list;
    }

    public
    function getManager()
    {
        return $this->_regional_manager;
    }

    /**
     * Получить сводную информацию по дилерам
     */
    public
    function getDealersInformation()
    {
        return $this->_dealers;
    }

    public
    function getDealersCompletedByLevelsList()
    {
        return $this->_dealers_completed_levels;
    }
}
