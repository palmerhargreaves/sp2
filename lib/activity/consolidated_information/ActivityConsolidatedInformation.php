<?php

require_once (sfConfig::get('app_root_dir').'lib/jpgraph/jpgraph.php');
require_once (sfConfig::get('app_root_dir').'lib/jpgraph/jpgraph_pie.php');
require_once (sfConfig::get('app_root_dir').'lib/jpgraph/jpgraph_pie3d.php');


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
                    $activity_sections_with_fields[$section->getId()] = array('section_data' => $section, 'fields' => array(), 'graph_data' => null, 'graph_url' => '');
                }

                $activity_statistic_fields_list = ActivityExtendedStatisticFieldsTable::getInstance()->createQuery()->where('activity_id = ? and show_in_export = ?', array($activity_id, true))->execute();
                foreach ($activity_statistic_fields_list as $field_item) {
                    if (array_key_exists($field_item->getParentId(), $activity_sections_with_fields)) {
                        $field_color = self::randColors();

                        $activity_sections_with_fields[$field_item->getParentId()]['fields'][$field_item->getId()] = array(
                            'value' => 0,
                            'name' => $field_item->getHeader(),
                            'color_name' => $field_color['name'],
                            'color_value' => $field_color['value'],
                            'is_selected' => false
                        );
                    }
                }
            }

            if (!empty($this->_quarters_list)) {

                //Суммируем данные заполненные дилером
                $field_values_by_max = array();
                foreach ($activity_statistic_fields_list as $field) {
                    //Для вычисляемых полей делаем отдельный проход по всем дилерам для получения значений
                    if ($field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_CALC) {
                        $field_values = array('value' => array());

                        foreach ($dealers_ids as $dealer_id) {
                            $field_values['value'][] = $field->calculateValue($dealer_id);
                        }
                    } else {
                        $field_values = ActivityExtendedStatisticFieldsDataTable::getInstance()->createQuery()
                            ->where('field_id = ?', array($field->getId()))
                            ->andWhereIn('dealer_id', $dealers_ids)
                            ->andWhere('year = ?', array($this->_year))
                            ->andWhereIn('quarter', $this->_quarters_list)
                            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
                    }

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

                //Для диаграммы Воронка вычисляем проценты
                if (!empty($field_values_by_max)) {

                }

                //Прходим по данным и выделяем максимальное значение
                foreach ($activity_sections_with_fields as $key => $data) {

                    if ($data['section_data']->getGraphType() == self::GRAPH_TYPE_PIE) {

                        $field_id_to_select = 0;
                        $last_value = 0;
                        foreach ($data['fields'] as $field_key => $field_data) {
                            if ($field_data['value'] != 0) {
                                if ($last_value == 0) {
                                    $last_value = $field_data['value'];
                                }

                                if ($field_data['value'] >= $last_value) {
                                    $last_value = $field_data['value'];
                                    $field_id_to_select = $field_key;
                                }
                            }
                        }

                        if ($field_id_to_select != 0) {
                            $activity_sections_with_fields[$key]['fields'][$field_id_to_select]['is_selected'] = true;
                        }
                    }
                }

                //Создаем Pie диаграмму
                foreach ($activity_sections_with_fields as $key => $data) {

                    if ($data['section_data']->getGraphType() == self::GRAPH_TYPE_PIE) {
                        $graph_data = array();
                        $graph_colors = array();

                        $slice_selected = -1;
                        foreach ($data['fields'] as $field_key => $field_data) {
                            if ($field_data['value'] != 0) {
                                $graph_data[] = $field_data['value'];
                                $graph_colors[] = $field_data['color_value'];

                                if ($field_data['is_selected']) {
                                    $slice_selected++;
                                }
                            }
                        }

                        //Создаем диаграмму только если есть данные
                        if (!empty($graph_data)) {
                            $activity_sections_with_fields[$key]['graph_data'] = new PieGraph(400, 250);

                            $theme_class = new VividTheme;
                            $activity_sections_with_fields[$key]['graph_data']->SetTheme($theme_class);

                            $plot = new PiePlot3D($graph_data);
                            $activity_sections_with_fields[$key]['graph_data']->Add($plot);

                            $plot->ShowBorder();
                            $plot->SetColor('black');

                            $plot->ExplodeSlice($slice_selected);
                            $plot->SetSliceColors($graph_colors);

                            $plot->value->SetFont(VW_HEAD, FS_BOLD, 20);

                            $activity_sections_with_fields[$key]['graph_data']->Stroke(_IMG_HANDLER);

                            $file_name = $data['section_data']->getHeader() . '.png';
                            $path = sfConfig::get('app_root_dir') . 'www/pdf/images/';

                            $gen_file = new UniqueFileNameGenerator($path);
                            $gen_file_name = $gen_file->generate($file_name);

                            $activity_sections_with_fields[$key]['graph_data']->img->Stream($path . $gen_file_name);
                            $activity_sections_with_fields[$key]['graph_url'] = sfConfig::get('app_site_url') . DIRECTORY_SEPARATOR . 'pdf/images/' . $gen_file_name;
                        }
                    }
                }

                $this->_activity_statistic = array(
                    'statistic_data' => $activity_sections_with_fields,
                    'fields_values_by_max' => $field_values_by_max,
                );

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

    private static function randColors() {
        $colors = array(
            array('name' => 'amaranth', 'value' => '#E52B50'),
            array('name' => 'amber', 'value' => '#FFBF00'),
            array('name' => 'amethyst', 'value' => '#9966CC'),
            array('name' => 'apricot', 'value' => '#FBCEB1'),
            array('name' => 'aquamarine', 'value' => '#7FFFD4'),
            array('name' => 'azure', 'value' => '#007FFF'),
            array('name' => 'baby-blue', 'value' => '#89CFF0'),
            array('name' => 'beige', 'value' => '#F5F5DC'),
            array('name' => 'blue', 'value' => '#0000FF'),
            array('name' => 'blue-green', 'value' => '#0095B6'),
            array('name' => 'blue-violet', 'value' => '#8A2BE2'),
            array('name' => 'blush', 'value' => '#DE5D83'),
            array('name' => 'bronze', 'value' => '#CD7F32'),
            array('name' => 'brown', 'value' => '#964B00'),
            array('name' => 'burgundy', 'value' => '#800020'),
            array('name' => 'byzantium', 'value' => '#702963'),
            array('name' => 'carmine', 'value' => '#960018'),
            array('name' => 'cerise', 'value' => '#DE3163'),
            array('name' => 'cerulean', 'value' => '#007BA7'),
            array('name' => 'champagne', 'value' => '#F7E7CE'),
            array('name' => 'chartreuse-green', 'value' => '#7FFF00'),
            array('name' => 'chocolate', 'value' => '#7B3F00'),
            array('name' => 'cobalt-blue', 'value' => '#0047AB'),
            array('name' => 'coffee', 'value' => '#6F4E37'),
            array('name' => 'copper', 'value' => '#B87333'),
            array('name' => 'coral', 'value' => '#F88379'),
            array('name' => 'crimson', 'value' => '#DC143C'),
            array('name' => 'cyan', 'value' => '#00FFFF'),
            array('name' => 'desert-sand', 'value' => '#EDC9Af'),
            array('name' => 'electric-blue', 'value' => '#7DF9FF'),
            array('name' => 'emerald', 'value' => '#50C878'),
            array('name' => 'erin', 'value' => '#00FF3F'),
        );

        return $colors[mt_rand(0, count($colors) - 1)];
    }
}
