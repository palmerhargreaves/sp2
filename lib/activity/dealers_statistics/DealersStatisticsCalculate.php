<?php

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 20.06.2017
 * Time: 12:41
 */
class DealersStatisticsCalculate
{
    const ACTIVITY_MODEL_HIGH_PRIORITY = 999;
    const ACTIVITY_MODEL_NORMAL_PRIORITY = 499;
    const ACTIVITY_MODEL_LOW_PRIORITY = 99;

    private $_quarter = 0;
    private $_year = null;
    private $_consider_next_quarter = false;

    private $_statistic_aggregator = null;
    private $_dealers_ids = array();

    public function __construct($quarter, $mandatory_activity, $year = null, $consider_next_quarter = false)
    {
        $this->_quarter = $quarter;
        $this->_mandatory_activity = $mandatory_activity;
        $this->_consider_next_quarter = $consider_next_quarter;

        $this->_year = date('Y');
        if (!is_null($year)) {
            $this->_year = $year;
        }

        $this->init();
    }

    private function init()
    {
        $this->_statistic_aggregator = array();

        foreach (DealerTable::getActiveDealersList()->select('id')->execute(array(), Doctrine_Core::HYDRATE_ARRAY) as $dealer) {
            $this->_dealers_ids[] = $dealer['id'];
        }
    }

    public function start()
    {
        $query = AgreementModelTable::getInstance()->createQuery('am')
            ->select('id, id as mId, activity_id, dealer_id, cost, status, step1, step2, report_id, model_category_id, model_type_id, created_at, am.updated_at am_updated_at, a.type_company_id, r.status as r_status, a.mandatory_activity as req_activity')
            ->where('(year(am.created_at) = ? or year(am.updated_at) = ?)', array($this->_year, $this->_year))
            ->andWhereIn('dealer_id', $this->_dealers_ids)
            ->innerJoin('am.Activity a')
            ->leftJoin('am.Report r')
            ->orderBy('dealer_id ASC');

        /*if ($this->_mandatory_activity) {
            $query->andWhere('a.mandatory_activity = ?', true);
        }*/

        $models = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $temp_models = array();
        $completed_models_ids = array();
        $dealers_work_with_models = array();

        //Делаем первый проход для получения списка невыполненных заявок по кварталам
        //И получаем список индексов выполненных заявок для прохода по логам и получения корректной даты воплнения заявки

        foreach ($models as $model) {
            if (($model['status'] == 'accepted' && $model['r_status'] == 'accepted') || ($model['status'] == 'accepted' && $model['r_status'] == 'wait')) {
                $completed_models_ids[] = $model['id'];
                $temp_models[$model['id']] = $model;
            } else {
                //Сравниваем полученный квартал заявки с кварталом в фильтре
                $date = D::calcQuarterData($model['created_at']);
                $model_q = D::getQuarter($date);

                for ($q_ind = 1; $q_ind <= $this->_quarter; $q_ind++) {
                    if ($model_q == $q_ind) {
                        $model['model_q'] = $q_ind;
                        $model['model_year'] = D::getYear($date);
                        $models_list_by_quarter[$model['dealer_id']][] = $model;
                    }
                }
            }
        }

        //Переносим невыполненные заявки в последний квартал в выборке
        foreach ($models_list_by_quarter as &$models) {
            foreach ($models as &$model) {
                if ($model['status'] != 'accepted' || $model['r_status'] != 'accepted') {
                    $model['model_q'] = $this->_quarter;
                }
            }
        }

        //Проходим по логам и получаем квартал выполнения заявки
        $completed_models = Utils::getModelDateFromLogEntryWithYear($completed_models_ids);
        $completed_models_ids = array();

        for ($q_ind = 1; $q_ind <= $this->_quarter; $q_ind++) {
            foreach ($completed_models as $completed_model) {
                $accept_date = D::calcQuarterData($completed_model['created_at']);

                //Если заявки нет в списке выполненных, получаем дату ее выполнения
                if (!array_key_exists($completed_model['object_id'], $completed_models_ids)) {
                    $completed_models_ids[$completed_model['object_id']] = $completed_model['object_id'];

                    //Сравниваем полученный квартал заявки с кварталом в фильтре
                    $model = $temp_models[$completed_model['object_id']];
                    $model['accepted_date'] = $accept_date;
                    $model['model_q'] = D::getQuarter($accept_date);
                    $model['model_year'] = D::getYear($accept_date);

                    $models_list_by_quarter[$temp_models[$completed_model['object_id']]['dealer_id']][] = $model;
                }
            }
        }

        $company_types = ActivityCompanyTypeTable::getInstance()->createQuery()->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        $dealers = array();

        $current_year = $this->_year;

        //Определяем обязательные активности по кварталу и году
        $mandatory_activities_list_in_year_result = array();
        $mandatory_activities_list_in_year = MandatoryActivityQuartersTable::getInstance()->createQuery()->where('year = ?', $this->_year)->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        foreach ($mandatory_activities_list_in_year as $mandatory) {
            $mandatory_activities_list_in_year_result[$mandatory['activity_id']] = explode(':', $mandatory['quarters']);
        }

        /** @var Dealer $dealer */
        foreach ($models_list_by_quarter as $dealer_id => $dealer_models) {
            $activities_statistic = array();

            if (!in_array($dealer_id, $dealers_work_with_models)) {
                $dealers_work_with_models[] = $dealer_id;
            }

            if (!array_key_exists($dealer_id, $dealers)) {
                $activities = array();

                $dealers[$dealer_id] = $dealer_id;
                $dealer = DealerTable::getInstance()->createQuery()->select('name, number')->where('id = ?', $dealer_id)->fetchOne();

                $real_budget = new RealBudgetCalculator($dealer, $this->_year);
                $real_budget_data = $real_budget->getPlanBudget();

                //Calc real budget
                $total_real_budget = 0;
                for ($q_ind = 1; $q_ind <= $this->_quarter; $q_ind++) {
                    $total_real_budget += $real_budget_data[$q_ind];
                }

                foreach ($company_types as $company_type) {
                    $companies_statistics[$company_type['id']] = array(
                        'company_plan' => 0,
                        'company_fact' => 0,
                        'fact_plan_company_percent' => 0,
                        'calc' => ActivityCompanyCalculator::createCalculator($company_type['id'], array(
                            'dealer' => $dealer,
                            'year' => $this->_year,
                            '_company_type' => $company_type['id']
                        ))
                    );
                }

                //Calc fact budget
                $fact_budget = 0;
                foreach ($dealer_models as $model) {
                    if ($model['model_year'] != $current_year) {
                        continue;
                    }

                    if ($this->_mandatory_activity && (!$model['req_activity'] || !in_array($this->_quarter, $mandatory_activities_list_in_year_result[$model['activity_id']]))) {
                        //Добавляем суммы заявк в факт даже есть активность не обязательная
                        if ($model['status'] == 'accepted') {
                            if (!is_null($model['report_id']) && $model['r_status'] == 'accepted') {
                                if ($model['model_q'] <= $this->_quarter) {
                                    $fact_budget += $model['cost'];
                                }

                                $companies_statistics[$model['Activity']['type_company_id']]['calc']->addToQBudgetOneQuarter($model['model_q'], $model['cost'], $model);
                            }
                        }

                        continue;
                    }

                    if ($model['model_q'] == $this->_quarter) {
                        if (!array_key_exists($model['activity_id'], $activities)) {
                            ///Find activity and initialize data
                            $activities[$model['activity_id']] = ActivityTable::getInstance()->find($model['activity_id']);

                            //Activity models priority count
                            //For high we ge status of complete model
                            $activities_statistic[$model['activity_id']]['max_model_status'] = array();
                            $activities_statistic[$model['activity_id']]['with_high_priority'] = 0;
                            $activities_statistic[$model['activity_id']]['with_medium_priority'] = 0;
                            $activities_statistic[$model['activity_id']]['with_low_priority'] = 0;
                            $activities_statistic[$model['activity_id']]['with_medium_scenario_record_priority'] = 0;

                            $activities_statistic[$model['activity_id']]['with_low_priority_status'] = array('model' => false, 'report' => false);
                            $activities_statistic[$model['activity_id']]['with_medium_scenario_record_priority_status'] = array('scenario' => false, 'record' => false, 'report' => false);

                            /** @var Activity $activity */
                            $activity = $activities[$model['activity_id']];
                            $activities_statistic[$model['activity_id']]['name'] = $activity->getName();

                            $activities_statistic[$model['activity_id']]['status'] = $activity->getStatusByQuarter($dealer_id, $this->_quarter, false, $this->_year) == ActivityModuleDescriptor::STATUS_ACCEPTED ? true : false;

                            if ($activity->isActivityStatisticComplete($dealer, null, true, $this->_year, $this->_quarter, array('check_by_quarter' => true))) {
                                $activities_statistic[$model['activity_id']]['statistic_status'] = true;
                            } else {
                                $activities_statistic[$model['activity_id']]['statistic_status'] = false;
                            }

                            //Model statuses
                            $activities_statistic[$model['activity_id']]['statuses']['model_completed'] = false;
                            $activities_statistic[$model['activity_id']]['statuses']['scenario_completed'] = false;
                            $activities_statistic[$model['activity_id']]['statuses']['record_completed'] = false;
                            $activities_statistic[$model['activity_id']]['statuses']['report_completed'] = false;
                            $activities_statistic[$model['activity_id']]['statuses']['have_scenario_record'] = false;
                        }

                        //Проверка на выпонение завки
                        if (($model['step1'] != 'wait' && $model['step1'] != 'none') || ($model['step2'] != 'wait' && $model['step2'] != 'none')) {

                            if ($model['step1'] == 'accepted') {
                                $activities_statistic[$model['activity_id']]['statuses']['scenario_completed'] = true;
                            }

                            if ($model['step2'] == 'accepted') {
                                $activities_statistic[$model['activity_id']]['statuses']['record_completed'] = true;
                            }

                            $activities_statistic[$model['activity_id']]['statuses']['have_scenario_record'] = true;
                        }

                        if (!is_null($model['report_id'])) {
                            $report = AgreementModelReportTable::getInstance()->find($model['report_id']);
                            if ($report && $report->getStatus() == 'accepted') {
                                $activities_statistic[$model['activity_id']]['statuses']['report_completed'] = true;
                            }
                        }

                        if ($model['status'] == 'accepted') {
                            $activities_statistic[$model['activity_id']]['statuses']['model_completed'] = true;
                        }

                        //First check for high priority than for medium and last others for low
                        if ($activities_statistic[$model['activity_id']]['statuses']['have_scenario_record'] && $activities_statistic[$model['activity_id']]['statuses']['model_completed'] && $activities_statistic[$model['activity_id']]['statuses']['report_completed']) {
                            $activities_statistic[$model['activity_id']]['with_high_priority']++;
                            $activities_statistic[$model['activity_id']]['max_model_status'][$activities_statistic[$model['activity_id']]['with_high_priority']] = $activities_statistic[$model['activity_id']]['statuses'];

                        } else if ($activities_statistic[$model['activity_id']]['statuses']['model_completed'] && $activities_statistic[$model['activity_id']]['statuses']['report_completed']) {
                            $activities_statistic[$model['activity_id']]['with_medium_priority']++;

                            $activities_statistic[$model['activity_id']]['max_model_status'][$activities_statistic[$model['activity_id']]['with_medium_priority']] = $activities_statistic[$model['activity_id']]['statuses'];

                        } else if ($activities_statistic[$model['activity_id']]['statuses']['have_scenario_record']) {
                            $activities_statistic[$model['activity_id']]['with_medium_scenario_record_priority']++;

                            //Если выполнены оба сценария (сценарий / запись)
                            if ($activities_statistic[$model['activity_id']]['statuses']['scenario_completed'] && $activities_statistic[$model['activity_id']]['statuses']['record_completed']) {
                                $activities_statistic[$model['activity_id']]['with_medium_scenario_record_priority_status'] = array('scenario' => true, 'record' => true);
                            }

                            //Если выполнены только сценарий или запись, при условии что сценарий выполняется первым
                            if ($activities_statistic[$model['activity_id']]['statuses']['scenario_completed'] && !$activities_statistic[$model['activity_id']]['with_medium_scenario_record_priority_status']['scenario']) {
                                $activities_statistic[$model['activity_id']]['with_medium_scenario_record_priority_status']['scenario'] = true;

                                if ($activities_statistic[$model['activity_id']]['statuses']['record_completed'] && !$activities_statistic[$model['activity_id']]['with_medium_scenario_record_priority_status']['record']) {
                                    $activities_statistic[$model['activity_id']]['with_medium_scenario_record_priority_status']['record'] = true;
                                }
                            }

                            $activities_statistic[$model['activity_id']]['max_model_status'][$activities_statistic[$model['activity_id']]['with_medium_scenario_record_priority']] = $activities_statistic[$model['activity_id']]['statuses'];

                        } else {
                            $activities_statistic[$model['activity_id']]['with_low_priority']++;

                            //Только для выполненных заявок без отчета
                            if ($activities_statistic[$model['activity_id']]['statuses']['model_completed']) {
                                $activities_statistic[$model['activity_id']]['with_low_priority_status']['model'] = true;

                                $activities_statistic[$model['activity_id']]['max_model_status'][$activities_statistic[$model['activity_id']]['with_low_priority']] = $activities_statistic[$model['activity_id']]['statuses'];
                            }
                        }
                    }

                    if ($model['status'] == 'accepted') {
                        if (!is_null($model['report_id']) && $model['r_status'] == 'accepted') {
                            if ($model['model_q'] <= $this->_quarter) {
                                $fact_budget += $model['cost'];
                            }

                            $companies_statistics[$model['Activity']['type_company_id']]['calc']->addToQBudgetOneQuarter($model['model_q'], $model['cost'], $model);
                        }
                    }
                }

                //Выбираем только одну из компаний)

                //Вычисляем для компаний Сервисные акции и Имеджевая рекламая
                foreach ($company_types as $company_type) {
                    $companies_q_result = $companies_statistics[$company_type['id']]['calc']->getStatisticByQuarters();

                    for ($q_ind = 1; $q_ind <= $this->_quarter; $q_ind++) {
                        $q_data = $companies_q_result[$q_ind][$company_type['id']];

                        $companies_statistics[$company_type['id']]['company_plan'] += $q_data['plan_company_budget'];
                        $companies_statistics[$company_type['id']]['company_fact'] += $q_data['total_cash'];

                        if ($companies_statistics[$company_type['id']]['company_plan'] > 0) {
                            $companies_statistics[$company_type['id']]['fact_plan_company_percent'] = $companies_statistics[$company_type['id']]['company_fact'] * 100 / $companies_statistics[$company_type['id']]['company_plan'];
                        }
                    }
                }

                //Calc fact / plan percent
                if ($total_real_budget > 0) {
                    $fact_plan_percent = $fact_budget * 100 / $total_real_budget;
                }

                $this->_statistic_aggregator[$dealer_id] = array(
                    'dealer_data' => array('dealer_name' => $dealer->getName(), 'dealer_number' => $dealer->getShortNumber(), 'regional_manager' => $dealer->getPkwManagerName()),
                    'dealer_statistic' => array(
                        'activities' => $activities_statistic,
                        'plan' => $total_real_budget,
                        'fact' => $fact_budget,
                        'fact_plan_percent' => $fact_plan_percent,
                        'companies_statistics' => $companies_statistics
                    )
                );
            }
        }

        //Work with dealer who don`t have any created models
        $dealers_without_models = array_diff($this->_dealers_ids, $dealers_work_with_models);

        foreach ($dealers_without_models as $dealer_id) {
            $activities_statistic = array();

            if (!array_key_exists($dealer_id, $dealers)) {
                $dealers[$dealer_id] = $dealer_id;
                $dealer = DealerTable::getInstance()->createQuery()->select('name, number')->where('id = ?', $dealer_id)->fetchOne();

                $real_budget = new RealBudgetCalculator($dealer, $this->_year);
                $real_budget_data = $real_budget->getPlanBudget();

                //Calc real budget
                $total_real_budget = 0;

                $fact_plan_percent = 0;
                $fact_budget = 0;

                for ($q_ind = 1; $q_ind <= $this->_quarter; $q_ind++) {
                    $total_real_budget += $real_budget_data[$q_ind];
                }

                foreach ($company_types as $company_type) {
                    $companies_statistics[$company_type['id']] = array(
                        'company_plan' => 0,
                        'company_fact' => 0,
                        'fact_plan_company_percent' => 0,
                        'calc' => ActivityCompanyCalculator::createCalculator($company_type['id'], array(
                            'dealer' => $dealer,
                            'year' => $this->_year,
                            '_company_type' => $company_type['id']
                        ))
                    );
                }

                //Выбираем только одну из компаний)
                //Вычисляем для компаний Сервисные акции и Имеджевая рекламая
                foreach ($company_types as $company_type) {
                    $companies_q_result = $companies_statistics[$company_type['id']]['calc']->getStatisticByQuarters();

                    for ($q_ind = 1; $q_ind <= $this->_quarter; $q_ind++) {
                        $q_data = $companies_q_result[$q_ind][$company_type['id']];

                        $companies_statistics[$company_type['id']]['company_plan'] += $q_data['plan_company_budget'];
                        $companies_statistics[$company_type['id']]['company_fact'] += $q_data['total_cash'];

                        if ($companies_statistics[$company_type['id']]['company_plan'] > 0) {
                            $companies_statistics[$company_type['id']]['fact_plan_company_percent'] = $companies_statistics[$company_type['id']]['company_fact'] * 100 / $companies_statistics[$company_type['id']]['company_plan'];
                        }
                    }
                }

                //Calc fact / plan percent
                if ($total_real_budget > 0) {
                    $fact_plan_percent = $fact_budget * 100 / $total_real_budget;
                }

                $this->_statistic_aggregator[$dealer_id] = array(
                    'dealer_data' => array('dealer_name' => $dealer->getName(), 'dealer_number' => $dealer->getShortNumber(), 'regional_manager' => $dealer->getPkwManagerName()),
                    'dealer_statistic' => array(
                        'activities' => $activities_statistic,
                        'plan' => $total_real_budget,
                        'fact' => $fact_budget,
                        'fact_plan_percent' => $fact_plan_percent,
                        'companies_statistics' => $companies_statistics
                    )
                );
            }
        }
    }

    public function getData()
    {
        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle('Выгрузка (Сумма кварталов)');

        $q_label = '';
        for ($q_ind = 1; $q_ind <= $this->_quarter; $q_ind++) {
            $q_label .= $q_ind != $this->_quarter ? $q_ind . '+' : $q_ind;
        }

        $q_label = '(' . $q_label . ' квартал)';

        $headers = array('Региональный менеджер PKW', 'Название дилера', 'номер дилера',
            'План' . $q_label,
            'Факт' . $q_label,
            'Факт/план',
            'План по сервису' . $q_label,
            'Факт по сервису' . $q_label,
            'Факт/План по сервису',
            'План по имиджу' . $q_label,
            'Факт по имиджу' . $q_label,
            'Факт/План по имиджу');

        $aSheet->fromArray($headers, null, 'A3');

        $column = 0;
        $aSheet->setCellValueByColumnAndRow($column, 1, sprintf('%s квартал %s', $this->_quarter, $this->_year));

        $leftFont = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '12',
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $leftFontWithUnderline = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '12',
                'bold' => false,
                'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $header_font = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '8',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $header_font_with_underline = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '8',
                'bold' => true,
                'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $boldFont = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '12',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );
        $center = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '10',
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );
        $right = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '10',
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $right_bold = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '9',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $bold_small_font = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '9',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $bold_normal_font = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '12',
                'bold' => true,
                'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $aSheet->getRowDimension('1')->setRowHeight(15);
        $aSheet->getStyle('A1:M1')->getAlignment()->setWrapText(true);

        $last_letter = PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);

        $aSheet->getStyle('A3:' . $last_letter . '3')->applyFromArray($header_font);
        $aSheet->getStyle('A3:A3')->applyFromArray($header_font_with_underline);

        $fillColor = 'f0fffe';

        $dealers_activities_list = array();
        $dealers_activities_list_data = array();
        $position_start = 12;
        $index = 0;

        foreach ($this->_statistic_aggregator as $dealer_id => $result_data) {
            if (count($result_data['dealer_statistic']['activities']) > 0) {
                foreach ($result_data['dealer_statistic']['activities'] as $activity_id => $activity) {
                    $inc = 0;

                    $position_inc = 7;
                    $header_with_scenario = false;
                    $header_with_model_complete = false;
                    $header_with_scenario_model_complete = false;
                    $header_with_model_in_process = false;

                    if ($activity['with_high_priority'] != 0) {
                        $header_with_scenario_model_complete = true;
                    } else if ($activity['with_medium_priority'] != 0) {
                        $header_with_model_complete = true;
                    } else if ($activity['with_medium_scenario_record_priority'] != 0) {
                        $header_with_scenario = true;
                    } else if ($activity['with_low_priority'] != 0) {
                        $header_with_model_in_process = true;
                    }

                    if (!array_key_exists($activity_id, $dealers_activities_list)) {
                        $dealers_activities_list[$activity_id] = array('data' => $activity,
                            'position_start' => $position_start,
                            'position' => $position_inc,
                            'inc' => $inc,
                            'with_scenario' => $header_with_scenario,
                            'with_model_complete' => $header_with_model_complete,
                            'with_scenario_model_complete' => $header_with_scenario_model_complete,
                            'with_model_in_process' => $header_with_model_in_process,
                        );

                        $dealers_activities_list_data[$index++] = array('data' => $activity, 'position' => $position_inc, 'activity_id' => $activity_id);
                        $position_start += $position_inc;
                    }
                }
            }
        }

        $row = 2;
        $activity_row_header = 3;
        $column = count($headers);
        $column_activity_header = count($headers);

        $from_column = 'M';
        $first_activity = true;

        $next_activity_ind = 0;
        $activities_count = count($dealers_activities_list);

        $high_column = $aSheet->getHighestColumn();
        $high_column_count = 0;

        $activity_headers_full = array('К активности приступил дилер', 'Согласован макет', 'Согласован сценарий', 'Согласована запись', 'Согласован отчет', 'Статистика', 'Активность выполнена');

        for ($activity_index = 0; $activity_index < $activities_count; $activity_index++) {
            $activity_data = $dealers_activities_list_data[$activity_index];
            $activity = $dealers_activities_list[$activity_data['activity_id']];

            if ($first_activity) {
                $aSheet->setCellValueByColumnAndRow($column++, $row, $activity_data['data']['name']);
                $first_activity = false;
            }

            for ($pos = 1; $pos < $activity['position']; $pos++) {
                $aSheet->setCellValueByColumnAndRow($column++, $row, $activity_data['data']['name']);
            }

            $activity_headers = $activity_headers_full;

            $high_column = $aSheet->getHighestColumn();

            foreach ($activity_headers as $activity_header) {
                $aSheet->setCellValueByColumnAndRow($column_activity_header++, $activity_row_header, $activity_header);
                //$high_column_count++;
            }

            $aSheet->getStyle($from_column . $activity_row_header . ':' . $high_column . $activity_row_header)->applyFromArray($bold_small_font);
            $aSheet->getStyle($from_column . $row . ':' . $high_column . $row)->applyFromArray($bold_small_font);

            $aSheet->mergeCells($from_column . $row . ':' . $high_column . $row);

            $next_activity_ind++;
            if ($next_activity_ind < $activities_count) {
                $next_activity = $dealers_activities_list_data[$next_activity_ind];

                $aSheet->setCellValueByColumnAndRow($column++, $row, $next_activity['data']['name']);
                $high_column = $aSheet->getHighestColumn();

                $from_column = $high_column;
                //$high_column_count++;
            }
        }

        $high_column_count = (count($activity_headers_full) * $activities_count) + 11;

        $cellIterator = $aSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $aSheet->getColumnDimension($cell->getColumn())->setWidth(25);
        }

        $aSheet->getColumnDimension('A')->setWidth(30);
        $aSheet->getColumnDimension('B')->setWidth(40);
        $aSheet->getColumnDimension('C')->setWidth(10);
        $aSheet->getColumnDimension('D')->setWidth(20);
        $aSheet->getColumnDimension('E')->setWidth(20);
        $aSheet->getColumnDimension('F')->setWidth(15);
        $aSheet->getColumnDimension('G')->setWidth(20);
        $aSheet->getColumnDimension('I')->setWidth(15);

        $aSheet->freezePane('B4');

        $row = 4;

        foreach ($this->_statistic_aggregator as $dealer_id => $result_data) {
            $column = 0;

            $aSheet->setCellValueByColumnAndRow($column++, $row, $result_data['dealer_data']['regional_manager']);
            $aSheet->setCellValueByColumnAndRow($column++, $row, $result_data['dealer_data']['dealer_name']);
            $aSheet->setCellValueByColumnAndRow($column++, $row, $result_data['dealer_data']['dealer_number']);

            $aSheet->setCellValueByColumnAndRow($column++, $row, Utils::format_number($result_data['dealer_statistic']['plan']));
            $aSheet->setCellValueByColumnAndRow($column++, $row, Utils::format_number($result_data['dealer_statistic']['fact']));
            $aSheet->setCellValueByColumnAndRow($column++, $row, ceil($result_data['dealer_statistic']['fact_plan_percent']) . '%');

            foreach ($result_data['dealer_statistic']['companies_statistics'] as $company_id => $company_statistic) {
                $aSheet->setCellValueByColumnAndRow($column++, $row, Utils::format_number($company_statistic['company_plan']));
                $aSheet->setCellValueByColumnAndRow($column++, $row, Utils::format_number($company_statistic['company_fact']));

                $aSheet->setCellValueByColumnAndRow($column++, $row, ceil($company_statistic['fact_plan_company_percent']) . '%');
            }

            if (count($result_data['dealer_statistic']['activities']) > 0) {
                foreach ($result_data['dealer_statistic']['activities'] as $activity_id => $activity) {
                    $column = $dealers_activities_list[$activity_id]['position_start'];

                    $data = $result_data['dealer_statistic']['activities'][$activity_id];

                    $model_status = $data['max_model_status'];
                    $data_status_index = -1;
                    if (isset($model_status[$data['with_high_priority']])) {
                        $data_status_index = $data['with_high_priority'];
                    } else if (isset($model_status[$data['with_medium_priority']])) {
                        $data_status_index = $data['with_medium_priority'];
                    } else if (isset($model_status[$data['with_medium_scenario_record_priority']])) {
                        $data_status_index = $data['with_medium_scenario_record_priority'];
                    } else if (isset($model_status[$data['with_low_priority']])) {
                        $data_status_index = $data['with_low_priority'];
                    }

                    $model_completed = $data_status_index == -1 ? false : $model_status[$data_status_index]['model_completed'];
                    $scenario_completed = $data_status_index == -1 ? false : $model_status[$data_status_index]['scenario_completed'];
                    $record_completed = $data_status_index == -1 ? false : $model_status[$data_status_index]['record_completed'];
                    $report_completed = $data_status_index == -1 ? false : $model_status[$data_status_index]['report_completed'];

                    $aSheet->setCellValueByColumnAndRow($column++, $row, $data_status_index != -1 ? 'Да' : 'Нет');
                    $aSheet->setCellValueByColumnAndRow($column++, $row, $model_completed ? 'Да' : 'Нет');
                    $aSheet->setCellValueByColumnAndRow($column++, $row, $scenario_completed ? 'Да' : 'Нет');
                    $aSheet->setCellValueByColumnAndRow($column++, $row, $record_completed ? 'Да' : 'Нет');
                    $aSheet->setCellValueByColumnAndRow($column++, $row, $report_completed ? 'Да' : 'Нет');
                    $aSheet->setCellValueByColumnAndRow($column++, $row, $data['statistic_status'] ? 'Да' : 'Нет');
                    $aSheet->setCellValueByColumnAndRow($column++, $row, $data['status'] ? 'Да' : 'Нет');
                }

                for ($column_ind = 12; $column_ind <= $high_column_count; $column_ind++) {
                    $value = $aSheet->getCellByColumnAndRow($column_ind, $row)->getValue();
                    if (is_null($value)) {
                        $aSheet->setCellValueByColumnAndRow($column_ind, $row, 'Нет');
                    }
                }

                //$row = $activity_row;
            } else {
                for ($column_ind = 12; $column_ind <= $high_column_count; $column_ind++) {
                    $value = $aSheet->getCellByColumnAndRow($column_ind, $row)->getValue();
                    if (is_null($value)) {
                        $aSheet->setCellValueByColumnAndRow($column_ind, $row, 'Нет');
                    }
                }
            }

            $aSheet->getStyle('A' . $row . ':A' . $row)->applyFromArray($leftFontWithUnderline);
            $aSheet->getStyle('B' . $row . ':E' . $row)->applyFromArray($boldFont);
            $aSheet->getStyle('F' . $row . ':F' . $row)->applyFromArray($leftFont);
            $aSheet->getStyle('G' . $row . ':L' . $row)->applyFromArray($boldFont);
            $aSheet->getStyle('I' . $row . ':I' . $row)->applyFromArray($leftFont);
            $aSheet->getStyle('L' . $row . ':L' . $row)->applyFromArray($leftFont);

            $row++;
        }

        $file_name = '/uploads/statistics_q(' . $this->_year . ').xlsx';

        //$objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter = PHPExcel_IOFactory::createWriter($pExcel, "Excel2007");
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/statistics_q(' . $this->_year . ').xlsx');

        return $file_name;
    }
}
