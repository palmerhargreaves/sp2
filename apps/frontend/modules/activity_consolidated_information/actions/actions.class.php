<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 08.10.2018
 * Time: 14:26
 */

require_once sfConfig::get('sf_lib_dir').'/vendor/autoload.php';

ini_set("magic_quotes_runtime", 0);

class activity_consolidated_informationActions extends BaseActivityActions {

    //Общая информация по активности
    private $consolidated_information = null;

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
        $query = DealerTable::getInstance()->createQuery()->where('dealer_type = ? or dealer_type = ?', array(Dealer::TYPE_PKW, Dealer::TYPE_NFZ_PKW))->orderBy('number ASC');
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

        $managers_dealers_data =$this->getConsolidatedInformationByDealers($request);
        foreach ($managers_dealers_data as $manager_id => $manager_data) {
            //Проверяем наличие кварталов в выгрузке
            for($quarter = 1; $quarter <= 4; $quarter++) {
                if (array_key_exists($quarter, $manager_data)) {
                    foreach ($manager_data[$quarter]["dealers"] as $dealer_id => $dealer_data) {
                        //Первая страница выгрузки (основная информация по дилеру)
                        //$this->generateDealerBudgetPage(array('manager' => $manager_data["manager"], 'dealer_budget' => $dealer_data['budget'], 'activities' => $dealer_data['activities'], 'dealer_id' => $dealer_id, 'not_work_with_activity' => $dealer_data['not_work_with_activity'], 'dealer' => $dealer_data["dealer"], 'css' => $css, 'quarter' => $quarter));

                        //Вторая страница выгрузки (информация по статистики дилера)
                        if (!is_null($dealer_data['completed_statistics'])) {
                            $this->generateDealerStatisticPages(array('manager' => $manager_data["manager"], 'completed_statistics' => $dealer_data['completed_statistics'], 'dealer_id' => $dealer_id, 'dealer' => $dealer_data["dealer"], 'css' => $css, 'quarter' => $quarter));
                        }
                    }
                }
            }
        }

        return $this->sendJson(array('success' => false));
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
        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/data/dealers/activity_consolidated_information_dealer_budget_page_'.$params['quarter'].'_'.$params['dealer_id'].'.html';
        file_put_contents($file_name, implode('<br/>', $page1_html));
    }

    /**
     * Генерация старицы статистики по дилеру
     * @param $params
     */
    private function generateDealerStatisticPages($params) {
        $header = get_partial('activity_template_page_dealer_budget_header', array('information' => $params));
        $header = str_replace('<style></style>', '<style>'.$params['css'].'</style>', $header);

        //Получаем список доступных шагов
        $activity_statistic_steps_list = array();
        foreach ($params['completed_statistics'] as $concept_id => $statistic_params) {
            foreach (ActivityExtendedStatisticStepsTable::getInstance()->createQuery()->where('activity_id = ?', $statistic_params['activity_id'])->orderBy('position ASC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY) as $step) {
                $activity_statistic_steps_list[$step['id']] = $step['header'];
            }
        }

        //Делаем проход по всем концепциям привязанным к статистике и делаем выборку заполненных данных
        $activity_statistic_completed_fields_list = array();
        foreach ($params['completed_statistics'] as $concept_id => $statistic_params) {
            $activity_statistic_steps_list = ActivityExtendedStatisticStepsTable::getInstance()->createQuery()->where('activity_id = ?', $statistic_params['activity_id'])->orderBy('position ASC')->execute();

            $page = 1;
            $total_fields = 0;
            $step = 1;
            foreach ($activity_statistic_steps_list as $step) {
                //Присваиваем нормер странички (начиниаем с первой)
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

                        //Обычное текстовое поле пропускаем
                        if ($field->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_TEXT) {
                            continue;
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
                $step++;
            }

            //Делаем проходы по полученным данным, создаем файлы
            foreach ($activity_statistic_completed_fields_list as $page => $page_data) {
                foreach ($page_data as $concept_id => $concept_data) {
                    foreach ($concept_data as $step_id => $step_data) {
                        $page1_html = array(
                            $header,
                            get_partial('activity_template_page_dealer_statistic_body', array('information' => $params, 'step_header' => array_key_exists($step_id, $activity_statistic_steps_list) ? $activity_statistic_steps_list[$step_id] : '',  'statistic_data' => $step_data,  'statistic_params' => $statistic_params)),
                            get_partial('activity_template_page_dealer_bottom', array())
                        );

                        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/data/dealers/activity_consolidated_information_dealer_statistic_page_'.$page.'_'.$concept_id."_".$params['quarter'].'_'.$params['dealer_id'].'.html';
                        file_put_contents($file_name, implode('<br/>', $page1_html));
                    }
                }
            }

        }



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

        return $this->sendJson(array('success' => $this->convertHtmlToPdf(), 'url' => sfConfig::get('app_site_url').'pdf/output.pdf'));
    }

    private function convertHtmlToPdf($page_size = '1024px') {
        $page_name = 'activity_consolidated_information_';

        $page_index = 1;
        $images_files_list = array();
        //Генерация картинок с html
        while(1) {
            $file_name = $page_name.$page_index++;
            $file = 'http://dm.vw-servicepool.ru/js/pdf/data/'.$file_name.'.html';
            $local_file = sfConfig::get('app_root_dir') . 'www/js/pdf/data/'.$file_name.'.html';

            if (!file_exists($local_file)) {
                break;
            }

            exec('phantomjs '.sfConfig::get('app_root_dir').'www/js/pdf/rasterize.js ' . $file .' '. sfConfig::get('app_root_dir') . 'www/js/pdf/data/' . $file_name . '.png '.$page_size);
            $images_files_list[] = sfConfig::get('app_root_dir') . 'www/js/pdf/data/' . $file_name . '.png';
        }

        $save_to = sfConfig::get('app_root_dir').'www/pdf/output.pdf';
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
        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/data/activity_consolidated_information_'.$page_index.'.html';
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

        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/data/activity_consolidated_information_'.$page_index.'.html';
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
        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/data/activity_consolidated_information_'.$page_index++.'.html';
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
            $file_name = sfConfig::get('app_root_dir').'www/js/pdf/data/activity_consolidated_information_'.$page_index++.'.html';
            file_put_contents($file_name, implode('<br/>', $dealer_page_html));
        }

        //Генерация последней странички
        $pages_count = count($pages);
        $dealer_last_page_html = array(
            $dealer_header,
            get_partial('activity_template_page3_body', array('consolidated_information' => $this->consolidated_information, 'page' => $pages[$pages_count])),
            get_partial('activity_template_page3_bottom', array())
        );
        $file_name = sfConfig::get('app_root_dir').'www/js/pdf/data/activity_consolidated_information_'.$page_index.'.html';
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
