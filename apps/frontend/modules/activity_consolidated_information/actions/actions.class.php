<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 08.10.2018
 * Time: 14:26
 */

class activity_consolidated_informationActions extends BaseActivityActions {

    //Общая инцормация по дилерам
    private $consolidated_information_by_dealers = null;

    public function executeIndex(sfWebRequest $request) {
        $this->getConsolidatedInformation($request);

    }

    public function executeFilterData(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $this->getConsolidatedInformation($request);

        return $this->sendJson(array('content' => get_partial('dealers_information', array('consolidated_information' => $this->consolidated_information))));
    }

    public function executeOnChangeManager(sfWebRequest $request) {
        $dealers_list = array();

        $regional_manager_id = $request->getParameter('manager_id');

        //Получаем список дилеров по типу, если запрос по менеджеру то фиотруем по менеджеру
        $query = DealerTable::getActiveDealersList()->andWhere('dealer_type = ? or dealer_type = ?', array(Dealer::TYPE_PKW, Dealer::TYPE_NFZ_PKW))->orderBy('number ASC');
        if ($regional_manager_id != 999) {
            $query->andWhere('regional_manager_id = ?', $regional_manager_id);
        }
        $dealers = $query->execute();

        $dealers_list_by_type = array();
        foreach ($dealers as $dealer) {
            $dealers_list_by_type[$dealer->getDealerTypeLabel()][] = $dealer;
        }

        foreach ($dealers_list_by_type as $label => $dealers) {
            $dealers_list[] = array('options' => $label, 'label' => $label);
            foreach ($dealers as $dealer) {
                $dealers_list[] = array('name' => $dealer->getNameAndNumber(), 'value' => $dealer->getId(), 'checked' => false);
            }
        }

        return $this->sendJson(array('dealers_list' => $dealers_list));
    }

    /**
     * Экспорт информации по активностям / кварталам / рег. менеджерам / дилерам
     */
    public function executeExportConsolidatedInformationByDealer(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $css = implode('', array(
            file_get_contents(sfConfig::get('app_root_dir').'www/pdf/css/fonts.css'),
            file_get_contents(sfConfig::get('app_root_dir').'www/pdf/css/css.css')
        ));

        $pages = array();
        $data = $this->getConsolidatedInformationByDealers($request);
        $managers_dealers_data = $data['managers_dealers_data'];

        $exists_activities = array();
        $by_manager = 0;

        foreach ($managers_dealers_data as $manager_id => $manager_data) {
            $by_manager = $manager_id;

            //Проверяем наличие кварталов в выгрузке
            for($quarter = 1; $quarter <= 4; $quarter++) {
                if (array_key_exists($quarter, $manager_data)) {
                    foreach ($manager_data[$quarter]["dealers"] as $dealer_id => $dealer_data) {
                        if (!array_key_exists($dealer_id, $pages)) {
                            $pages[$dealer_id] = array();
                        }

                        if (!array_key_exists($quarter, $pages[$dealer_id])) {
                            $pages[$dealer_id][$quarter] = array('budget' => array(), 'statistic' => array(), 'graphs' => array());
                        }

                        //Первая страница выгрузки (основная информация по дилеру)
                        $result = $this->generateDealerBudgetPage(array('manager' => $manager_data["manager"], 'dealer_budget' => $dealer_data['budget'], 'activities' => $dealer_data['activities'], 'dealer_id' => $dealer_id, 'not_work_with_activity' => $dealer_data['not_work_with_activity'], 'dealer' => $dealer_data["dealer"], 'css' => $css, 'quarter' => $quarter));
                        $pages[$dealer_id][$quarter]['budget'] = array_merge($pages[$dealer_id][$quarter]['budget'], $result);

                        //Вторая страница выгрузки (информация по статистики дилера)
                        if (!empty($dealer_data['completed_statistics'])) {
                            $result = $this->generateDealerStatisticPages(array('manager' => $manager_data["manager"], 'completed_statistics' => $dealer_data['completed_statistics'], 'dealer_id' => $dealer_id, 'dealer' => $dealer_data["dealer"], 'css' => $css, 'quarter' => $quarter));

                            $pages[$dealer_id][$quarter]['statistic'] = array_merge($pages[$dealer_id][$quarter]['statistic'], $result);
                        }

                        //Вторая страница (простая статистка)
                        if (!empty($dealer_data['completed_simple_statistics'])) {
                            $result = $this->generateDealerSimpleStatisticPages(array('manager' => $manager_data["manager"], 'completed_simple_statistics' => $dealer_data['completed_simple_statistics'], 'dealer_id' => $dealer_id, 'dealer' => $dealer_data["dealer"], 'css' => $css, 'quarter' => $quarter));

                            $pages[$dealer_id][$quarter]['statistic'] = array_merge($pages[$dealer_id][$quarter]['statistic'], $result);
                        }

                        //Третья страница - Графика
                        //Только для кварталов с заполненной статистикой
                        if (array_key_exists($dealer_id, $data['dealer_total_models_cost_by_categories']) && array_key_exists($quarter, $data['dealer_total_models_cost_by_categories'][$dealer_id])) {
                            foreach ($data['dealer_total_models_cost_by_categories'][$dealer_id][$quarter] as $activity_id => $dealers_data) {
                                if (!array_key_exists($activity_id, $exists_activities)) {
                                    $exists_activities[$activity_id] = ActivityTable::getInstance()->find($activity_id);
                                }

                                $result = $this->generateGraphPage(array(
                                    'dealers_total_cost' => $data['dealers_total_cost'][$quarter][$activity_id],
                                    'dealer_total_models_cost_by_categories' => $dealers_data,
                                    'manager' => $manager_data["manager"],
                                    'dealer_id' => $dealer_id,
                                    'dealer' => $dealer_data["dealer"],
                                    'activity_data' => array('activity_name' => $exists_activities[$activity_id]->getName(), 'company_name' => $exists_activities[$activity_id]->getCompanyType()->getName()),
                                    'tick_values' => $data['graph_tick_values'][$quarter][$activity_id],
                                    'css' => $css,
                                    'activity_id' => $activity_id,
                                    'quarter' => $quarter));

                                $pages[$dealer_id][$quarter]['graphs'] = array_merge($pages[$dealer_id][$quarter]['graphs'], $result);
                            }
                        }
                    }
                }
            }
        }

        $this->convertDealersHtmlPagesToPdf($pages, $by_manager);

        return $this->sendJson(array('success' => true, 'url' => sfConfig::get('app_site_url').'pdf/gen_files/dealers_consolidate_information.pdf'));
    }

    /**
     * Создаем страницу пдф на основе созданных html
     * @param $pages
     * @param $manager_id
     * @param string $page_size
     * @return string
     */
    private function convertDealersHtmlPagesToPdf($pages, $manager_id, $page_size = '1024px') {
        $generated_png = array();

        //Создаем на основе сгенерированной картинки пдф файл
        $output_file_name = 'dealers_consolidate_information';
        $save_to = sfConfig::get('app_root_dir').'www/pdf/gen_files/'.$output_file_name.'.pdf';
        @unlink($save_to);

        foreach ($pages as $dealer_id => $dealer_data) {


            foreach ($dealer_data as $quarter => $pages) {
                //Для страниц бюджета
                foreach ($pages['budget'] as $page) {
                    $file_data = pathinfo($page);
                    $file_name = $file_data['filename'];

                    exec('phantomjs '.sfConfig::get('app_root_dir').'www/js/pdf/rasterize.js ' . $page .' '. sfConfig::get('app_root_dir') . 'www/pdf/data/dealers/budget/' . $file_name . '.png '.$page_size);
                    $generated_png[] =  sfConfig::get('app_root_dir') . 'www/pdf/data/dealers/budget/' . $file_name . '.png';
                }

                //Для страниц статистики
                foreach ($pages['statistic'] as $page) {
                    $file_data = pathinfo($page);
                    $file_name = $file_data['filename'];

                    exec('phantomjs '.sfConfig::get('app_root_dir').'www/js/pdf/rasterize.js ' . $page .' '. sfConfig::get('app_root_dir') . 'www/pdf/data/dealers/statistics/' . $file_name . '.png '.$page_size);
                    $generated_png[] =  sfConfig::get('app_root_dir') . 'www/pdf/data/dealers/statistics/' . $file_name . '.png';
                }

                //Для страниц графики
                foreach ($pages['graphs'] as $page) {
                    $file_data = pathinfo($page);
                    $file_name = $file_data['filename'];

                    exec('phantomjs '.sfConfig::get('app_root_dir').'www/js/pdf/rasterize.js ' . $page .' '. sfConfig::get('app_root_dir') . 'www/pdf/data/dealers/graphs/' . $file_name . '.png '.$page_size);
                    $generated_png[] =  sfConfig::get('app_root_dir') . 'www/pdf/data/dealers/graphs/' . $file_name . '.png';
                }
            }
        }

        //Генерация пдф с картинок
        exec('convert '.implode(' ', $generated_png).' '.$save_to);
    }

    /**
     * Генерация графической части выгрузки
     * @param $params
     * @return array
     * @internal param $param
     */
    private function generateGraphPage($params) {
        $header = get_partial('activity_template_page_dealer_budget_header', array('information' => $params));
        $header = str_replace('<style></style>', '<style>'.$params['css'].'</style>', $header);

        $page1_html = array(
            $header,
            get_partial('activity_template_page_dealer_graph_body', array('data' => $params)),
            get_partial('activity_template_page_dealer_bottom', array())
        );
        $file_name = sfConfig::get('app_root_dir').'www/pdf/data/dealers/graphs/activity_consolidated_information_dealer_graph_page_'.$params['quarter'].'_'.$params['activity_id'].'_'.$params['dealer_id'].'.html';
        file_put_contents($file_name, implode('<br/>', $page1_html));

        return array($file_name);
    }

    /**
     * Генерация бюджетной страницы для дилера
     * @internal param $dealer
     * @internal param $css
     */
    private function generateDealerBudgetPage($params) {
        $header = get_partial('activity_template_page_dealer_budget_header', array('information' => $params));
        $header = str_replace('<style></style>', '<style>'.$params['css'].'</style>', $header);

        $page1_html = array(
            $header,
            get_partial('activity_template_page_dealer_budget_body', array('information' => $params)),
            get_partial('activity_template_page_dealer_bottom', array())
        );
        $file_name = sfConfig::get('app_root_dir').'www/pdf/data/dealers/budget/activity_consolidated_information_dealer_budget_page_'.$params['quarter'].'_'.$params['dealer_id'].'.html';
        file_put_contents($file_name, implode('<br/>', $page1_html));

        return array($file_name);
    }

    /**
     * Генерация старицы статистики по дилеру
     * @param $params
     * @return array
     */
    private function generateDealerStatisticPages($params) {
        $header = get_partial('activity_template_page_dealer_budget_header', array('information' => $params));
        $header = str_replace('<style></style>', '<style>'.$params['css'].'</style>', $header);

        //Получаем список доступных шагов
        $activity_statistic_steps_list_ids = array();
        foreach ($params['completed_statistics'] as $concept_id => $statistic_params) {
            foreach (ActivityExtendedStatisticStepsTable::getInstance()->createQuery()->where('activity_id = ?', $statistic_params['activity_id'])->orderBy('position ASC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY) as $step) {
                $activity_statistic_steps_list_ids[$step['id']] = $step['header'];
            }
        }

        $activities = array();

        $pages = array();

        //Делаем проход по всем концепциям привязанным к статистике и делаем выборку заполненных данных
        $activity_statistic_completed_fields_list = array();
        foreach ($params['completed_statistics'] as $concept_id => $statistic_params) {
            $activity_statistic_steps_list = ActivityExtendedStatisticStepsTable::getInstance()->createQuery()->where('activity_id = ?', $statistic_params['activity_id'])->orderBy('position ASC')->execute();

            //Получаем список активностей и название привязанной кампании
            if (!array_key_exists($statistic_params['activity_id'], $activities)) {
                $activity = ActivityTable::getInstance()->find($statistic_params['activity_id']);
                if ($activity) {
                    $activities[$activity->getId()] = array('activity_name' => $activity->getName(), 'company_name' => $activity->getCompanyType()->getName());
                }
            }

            $page = 1;
            $total_fields = 0;
            foreach ($activity_statistic_steps_list as $step) {
                //Присваиваем номер странички (начиниаем с первой)
                if (!array_key_exists($page, $activity_statistic_completed_fields_list)) {
                    $activity_statistic_completed_fields_list[$page] = array();
                }

                //Создаем ключ концепции
                if (!array_key_exists($concept_id, $activity_statistic_completed_fields_list[$page])) {
                    $activity_statistic_completed_fields_list[$page][$concept_id] = array();
                }

                //Создаем ключ шага привязанного к концепции
                if (!array_key_exists($step->getId(), $activity_statistic_completed_fields_list[$page][$concept_id])) {
                    $activity_statistic_completed_fields_list[$page][$concept_id][$step->getId()] = array();
                }

                $sections = $step->getSectionsList();
                foreach ($sections as $section_id => $section) {
                    $fields = $section[ 'fields' ];

                    //Инициализируем данные по разделу (название раздела и массив полей)
                    if (!array_key_exists($section_id, $activity_statistic_completed_fields_list[$page][$concept_id][$step->getId()])) {
                        $activity_statistic_completed_fields_list[$page][$concept_id][$step->getId()][$section_id] = array('section_header' => $section['data'], 'fields' => array());
                    }

                    foreach ($fields as $field) {
                        $fieldValue = $field->getStepFieldUserValue(null, $params['dealer_id'], $concept_id, $statistic_params['year'], $statistic_params['quarter']);

                        if (empty($activity_statistic_completed_fields_list[$page][$concept_id][$step->getId()][$section_id]['section_header'])) {
                            $activity_statistic_completed_fields_list[$page][$concept_id][$step->getId()][$section_id]['section_header'] = $section['data'];
                        }

                        //Получаем значение заполненных полей в зависимости от типа
                        if ($field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_DATE) {
                            $value = explode("-", $fieldValue->getValue());
                            $value = implode('-', array(isset($value[ 0 ]) && !empty($value[ 0 ]) ? $value[ 0 ] : '', isset($value[ 1 ]) && !empty($value[ 1 ]) ? $value[ 1 ] : ''));
                        } else if ($field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_MONEY) {
                            $value = explode(':', $fieldValue->getValue());
                            $value = implode(' ', array(isset($value[ 0 ]) && !empty($value[ 0 ]) ? $value[ 0 ] : '', isset($value[ 1 ]) && !empty($value[ 1 ]) ? $value[ 1 ] : ''));
                        } else if ($field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_CALC) {
                            $value = $field->calculateValue($params['dealer_id'], '', $concept_id);
                        } else if ($field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_FILE) {
                            $value = $fieldValue->getValue();
                        } else  {
                            $value = $fieldValue->getValue();
                        }

                        //Присваиваем данные поля
                        $activity_statistic_completed_fields_list[$page][$concept_id][$step->getId()][$section_id]['fields'][] = array(
                            'header' => $field->getHeader(),
                            'value' => $value,
                        );

                        //Если количестов полей на одной страничке превышает допустимое количество, переходим на др. страницу
                        if ($total_fields++ > ActivityConsolidatedInformationByDealers::FIELDS_PER_PAGE) {
                            $total_fields = 0;
                            $page++;
                        }
                    }
                }
            }

            //Делаем проходы по полученным данным, создаем файлы
            foreach ($activity_statistic_completed_fields_list as $page => $page_data) {
                foreach ($page_data as $concept_id => $concept_data) {
                    $page1_html = array(
                        $header,
                        get_partial('activity_template_page_dealer_statistic_body',
                            array(
                                'information' => $params,
                                'steps_list' => $activity_statistic_steps_list_ids,
                                'statistic_data' => $concept_data,
                                'statistic_params' => $statistic_params,
                                'activity_id' => $statistic_params['activity_id'],
                                'activities_list' => $activities
                            )),
                        get_partial('activity_template_page_dealer_bottom', array())
                    );

                    $file_name = sfConfig::get('app_root_dir').'www/pdf/data/dealers/statistics/activity_consolidated_information_dealer_statistic_page_'.$page.'_'.$concept_id."_".$params['quarter'].'_'.$params['dealer_id'].'.html';
                    file_put_contents($file_name, implode('<br/>', $page1_html));

                    $pages[] = $file_name;
                }
            }
        }

        return $pages;
    }

    /**
     * Генерация старицы простой статистики по дилеру
     * @param $params
     * @return array
     */
    private function generateDealerSimpleStatisticPages($params) {
        $header = get_partial('activity_template_page_dealer_budget_header', array('information' => $params));
        $header = str_replace('<style></style>', '<style>'.$params['css'].'</style>', $header);

        $year = date('Y');
        $activities = array();

        $pages = array();

        //Делаем проход по всем концепциям привязанным к статистике и делаем выборку заполненных данных
        $activity_statistic_completed_fields_list = array();
        foreach ($params['completed_simple_statistics'] as $activity_id => $statistic_params) {
            //Получаем список активностей и название привязанной кампании
            if (!array_key_exists($statistic_params['activity_id'], $activities)) {
                $activity = ActivityTable::getInstance()->find($statistic_params['activity_id']);
                if ($activity) {
                    $activities[$activity->getId()] = array('activity_name' => $activity->getName(), 'company_name' => $activity->getCompanyType()->getName());
                }
            }

            $page = 1;
            $total_fields = 0;
            $fields = ActivityFieldsTable::getInstance()->createQuery()->select('*')->where('activity_id = ?', $activity->getId())->orderBy('id ASC')->execute();

            foreach ($fields as $field) {
                $value = '';
                $field_data = $field->getValue($params['dealer_id'], $params['quarter'], $year);

                if (!empty($field_data)) {
                    $value = $field_data['val'];
                }

                //Присваиваем данные поля
                $activity_statistic_completed_fields_list[$page]['fields'][] = array(
                    'header' => $field->getName(),
                    'value' => $value,
                );

                //Если количестов полей на одной страничке превышает допустимое количество, переходим на др. страницу
                if ($total_fields++ > ActivityConsolidatedInformationByDealers::FIELDS_PER_PAGE) {
                    $total_fields = 0;
                    $page++;
                }
            }

            //Делаем проходы по полученным данным, создаем файлы
            foreach ($activity_statistic_completed_fields_list as $page => $page_data) {

                $page1_html = array(
                    $header,
                    get_partial('activity_template_page_dealer_simple_statistic_body',
                        array(
                            'information' => $params,
                            'statistic_data' => $page_data['fields'],
                            'statistic_params' => $statistic_params,
                            'activity_id' => $statistic_params['activity_id'],
                            'activities_list' => $activities
                        )),
                    get_partial('activity_template_page_dealer_bottom', array())
                );

                $file_name = sfConfig::get('app_root_dir').'www/pdf/data/dealers/statistics/activity_consolidated_information_dealer_simple_statistic_page_'.$page.'_'.$activity_id.'_'.$params['quarter'].'_'.$params['dealer_id'].'.html';
                file_put_contents($file_name, implode('<br/>', $page1_html));

                $pages[] = $file_name;
            }
        }

        return $pages;
    }

    /**
     * Создаем пдф файл
     * @param sfWebRequest $request
     * @return mixed
     */
    public function executeExportConsolidatedInformation(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $this->getConsolidatedInformation($request);

        $css = implode('', array(
            file_get_contents(sfConfig::get('app_root_dir').'www/pdf/css/fonts.css'),
            file_get_contents(sfConfig::get('app_root_dir').'www/pdf/css/css.css')
            ));

        //Первая страница
        $this->generateFirstPage($css, 1);

        //Страницы статистики
        $this->generateStatisticPage($css, 2);

        //Страница данных по дилерам
        $this->generateDealersPage($css, 3);

        $output_file_name = sprintf('consolidated_information_%s.pdf',$this->activity->getId());
        return $this->sendJson(array('success' => $this->convertHtmlToPdf($output_file_name), 'url' => sfConfig::get('app_site_url').'pdf/gen_files/'.$output_file_name));
    }

    /**
     * @param $output_file_name
     * @param string $page_size
     * @return array
     */
    private function convertHtmlToPdf($output_file_name, $page_size = '1024px') {
        $page_name = 'activity_consolidated_information_';

        $page_index = 1;
        $images_files_list = array();
        //Генерация картинок с html
        while(1) {
            $file_name = $page_name.$page_index++;
            $file = 'http://dm.vw-servicepool.ru/pdf/data/activity/'.$file_name.'.html';
            $local_file = sfConfig::get('app_root_dir') . 'www/pdf/data/activity/'.$file_name.'.html';

            if (!file_exists($local_file)) {
                break;
            }

            exec('phantomjs '.sfConfig::get('app_root_dir').'www/js/pdf/rasterize.js ' . $file .' '. sfConfig::get('app_root_dir') . 'www/pdf/data/activity/' . $file_name . '.png '.$page_size);
            $images_files_list[] = sfConfig::get('app_root_dir') . 'www/pdf/data/activity/' . $file_name . '.png';
        }

        $save_to = sfConfig::get('app_root_dir').'www/pdf/gen_files/'.$output_file_name;
        @unlink($save_to);

        //Генерация пдф с картинок
        exec('convert '.implode(' ', $images_files_list).' '.$save_to);
        if (file_exists($save_to)) {
            return array('success' => true);
        }

        return array('success' => false);
    }

    private function generateFirstPage($css, $page_index) {
        $header = get_partial('activity_template_page1_header', array('consolidated_information' => $this->consolidated_information));
        $header = str_replace('<style></style>', '<style>'.$css.'</style>', $header);
        $page1_html = array(
            $header,
            get_partial('activity_template_page1_body', array('consolidated_information' => $this->consolidated_information)),
            get_partial('activity_template_page1_bottom', array())
        );
        $file_name = sfConfig::get('app_root_dir').'www/pdf/data/activity/activity_consolidated_information_'.$page_index.'.html';
        file_put_contents($file_name, implode('<br/>', $page1_html));
    }

    private function generateStatisticPage($css, $page_index) {
        $header = get_partial('activity_template_page2_header', array('consolidated_information' => $this->consolidated_information));
        $header = str_replace('<style></style>', '<style>'.$css.'</style>', $header);

        $page2_html = array(
            $header,
            get_partial('activity_template_page2_body', array('consolidated_information' => $this->consolidated_information)),
            get_partial('activity_template_page2_bottom', array())
        );

        $file_name = sfConfig::get('app_root_dir').'www/pdf/data/activity/activity_consolidated_information_'.$page_index.'.html';
        file_put_contents($file_name, implode('<br/>', $page2_html));
    }

    private function generateDealersPage($css, $page_index) {
        $header = get_partial('activity_template_page3_header', array('consolidated_information' => $this->consolidated_information));
        $header = str_replace('<style></style>', '<style>'.$css.'</style>', $header);

        $pages = $this->consolidated_information->getDealersPages();

        //Генерация первой странички
        $page3_html = array(
            $header,
            get_partial('activity_template_page3_body', array('consolidated_information' => $this->consolidated_information, 'page' => $pages[1])),
        );
        $file_name = sfConfig::get('app_root_dir').'www/pdf/data/activity/activity_consolidated_information_'.$page_index++.'.html';
        file_put_contents($file_name, implode('<br/>', $page3_html));

        $dealer_header = get_partial('activity_template_page3_dealer_header', array());
        $dealer_header = str_replace('<style></style>', '<style>'.$css.'</style>', $dealer_header);

        //Генерация остальных страниц, кроме последней
        for($index = 2; $index < count($pages); $index++) {
            $dealer_page_html = array(
                $dealer_header,
                get_partial('activity_template_page3_body', array('consolidated_information' => $this->consolidated_information, 'page' => $pages[$index])),
                get_partial('activity_template_page3_dealer_bottom', array())
            );
            $file_name = sfConfig::get('app_root_dir').'www/pdf/data/activity/activity_consolidated_information_'.$page_index++.'.html';
            file_put_contents($file_name, implode('<br/>', $dealer_page_html));
        }

        //Генерация последней странички
        $pages_count = count($pages);
        $dealer_last_page_html = array(
            $dealer_header,
            get_partial('activity_template_page3_body', array('consolidated_information' => $this->consolidated_information, 'page' => $pages[$pages_count])),
            get_partial('activity_template_page3_bottom', array())
        );
        $file_name = sfConfig::get('app_root_dir').'www/pdf/data/activity/activity_consolidated_information_'.$page_index.'.html';
        file_put_contents($file_name, implode('<br/>', $dealer_last_page_html));
    }

    private function getConsolidatedInformation(sfWebRequest $request) {
        $this->outputActivity($request);

        $this->consolidated_information = new ActivityConsolidatedInformation($this->activity, $request);
    }

    private function getConsolidatedInformationByDealers($request) {
        $this->consolidated_information_by_dealers = new ActivityConsolidatedInformationByDealers($request);

        return $this->consolidated_information_by_dealers->getData();
    }

}
