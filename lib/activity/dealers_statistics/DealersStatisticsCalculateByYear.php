<?php

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 20.06.2017
 * Time: 12:41
 */
class DealersStatisticsCalculateByYear
{
    private $_quarter = 0;
    private $_year = null;
    private $_consider_next_quarter = false;

    private $_statistic_aggregator = null;
    private $_dealers_ids = array();
    private $_dealers_numbers = array();

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

        foreach (DealerTable::getActiveDealersList()->select('id, number')->execute(array(), Doctrine_Core::HYDRATE_ARRAY) as $dealer) {
            $this->_dealers_ids[] = $dealer['id'];
            $this->_dealers_numbers[$dealer['id']] = $dealer['number'];
        }
    }

    public function start()
    {
        $query = AgreementModelTable::getInstance()->createQuery('am')
            ->select('id, id as mId, activity_id, dealer_id, cost, status, step1, step2, report_id, model_category_id, model_type_id, created_at, am.updated_at am_updated_at, a.type_company_id, r.status as r_status, a.mandatory_activity as req_activity')
            ->where('(year(created_at) = ? or year(updated_at) = ?)', array($this->_year, $this->_year))
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
        for ($q_ind = 1; $q_ind <= 4; $q_ind++) {
            foreach ($models as $model) {
                if (($model['status'] == 'accepted' && $model['r_status'] == 'accepted') || ($model['status'] == 'accepted' && $model['r_status'] == 'wait')) {
                    $completed_models_ids[] = $model['id'];
                    $temp_models[$model['id']] = $model;
                } else {
                    //Сравниваем полученный квартал заявки с кварталом в фильтре
                    $date = D::calcQuarterData($model['created_at']);
                    $model_q = D::getQuarter($date);

                    if ($model_q == $q_ind) {
                        $model['model_q'] = $q_ind;
                        $model['model_year'] = D::getYear($date);
                        $models_list_by_quarter[$model['dealer_id']][] = $model;
                    }
                }
            }
        }

        //Проходим по логам и получаем квартал выполнения заявки
        $completed_models = Utils::getModelDateFromLogEntryWithYear($completed_models_ids);
        $completed_models_ids = array();

        for ($q_ind = 1; $q_ind <= 4; $q_ind++) {
            foreach ($completed_models as $completed_model) {
                if (!array_key_exists($completed_model['object_id'], $completed_models_ids)) {
                    $completed_models_ids[$completed_model['object_id']] = $completed_model['object_id'];

                    //Сравниваем полученный квартал заявки с кварталом в фильтре
                    $accept_date = D::calcQuarterData($completed_model['created_at']);
                    if (array_key_exists($completed_model['object_id'], $temp_models)) {
                        $model = $temp_models[$completed_model['object_id']];
                        $model['accepted_date'] = $accept_date;
                        $model['model_q'] = D::getQuarter($accept_date);
                        $model['model_year'] = D::getYear($accept_date);

                        $models_list_by_quarter[$temp_models[$completed_model['object_id']]['dealer_id']][] = $model;
                    }
                }
            }
        }

        //Определяем обязательные активности по кварталу и году
        $mandatory_activities_list_in_year_result = array();
        $mandatory_activities_list_in_year = MandatoryActivityQuartersTable::getInstance()->createQuery()->where('year = ?', $this->_year)->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        foreach ($mandatory_activities_list_in_year as $mandatory) {
            $mandatory_activities_list_in_year_result[$mandatory['activity_id']] = explode(':', $mandatory['quarters']);
        }


        $company_types = ActivityCompanyTypeTable::getInstance()->createQuery()->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        $dealers = array();

        $current_year = $this->_year;

        /** @var Dealer $dealer */
        $it = 2;
        foreach ($models_list_by_quarter as $dealer_id => $dealer_models) {
            $activities_statistic = array();
            $total_mailing_plan = 0;
            $total_dealer_mailings = 0;

            if (!in_array($dealer_id, $dealers_work_with_models)) {
                $dealers_work_with_models[] = $dealer_id;
            }

            if (!array_key_exists($dealer_id, $dealers)) {
                $dealers[$dealer_id] = $dealer_id;

                $companies_statistics_by_q = array();
                $activities = array();

                $dealer = DealerTable::getInstance()->createQuery()->select('name, number')->where('id = ?', $dealer_id)->fetchOne();

                $real_budget = new RealBudgetCalculator($dealer, $this->_year);
                $real_budget_data = $real_budget->getPlanBudget();
                $fact_budget_data = array(1 => 0, 2 => 0, 3 => 0, 4 => 0);

                //Calc real budget
                $total_real_budget = 0;
                for ($q_ind = 1; $q_ind <= 4; $q_ind++) {
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
                        )),
                    );

                    $companies_statistics_by_q[$company_type['id']]['quarters'] = array(
                        1 => array('plan' => 0, 'fact' => 0),
                        2 => array('plan' => 0, 'fact' => 0),
                        3 => array('plan' => 0, 'fact' => 0),
                        4 => array('plan' => 0, 'fact' => 0)
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
                        $fact_budget += $model['cost'];

                        if (!is_null($model['report_id']) && $model['r_status'] == 'accepted') {
                            $fact_budget_data[$model['model_q']] += $model['cost'];
                            //var_dump($model['activity_id'] . '--' . $model['id'] . '---' . $model['model_q'] . '---' . $model['cost'] . '--' . $fact_budget_data[$model['model_q']] . '--' . $dealer_id);

                            $companies_statistics[$model['Activity']['type_company_id']]['calc']->addToQBudgetOneQuarter($model['model_q'], $model['cost'], $model);
                        }

                        continue;
                    }

                    if ($model['model_q'] == $this->_quarter) {
                        if (!array_key_exists($model['activity_id'], $activities)) {
                            $activities[$model['activity_id']] = ActivityTable::getInstance()->find($model['activity_id']);

                            /** @var Activity $activity */
                            $activity = $activities[$model['activity_id']];
                            $activities_statistic[$model['activity_id']]['name'] = $activity->getName();

                            $activities_statistic[$model['activity_id']]['status'] = $activity->getStatus($dealer_id, $this->_year, $this->_quarter, false) == ActivityModuleDescriptor::STATUS_ACCEPTED ? true : false;
                        }
                    }

                    if ($model['status'] == 'accepted') {
                        $fact_budget += $model['cost'];

                        if (!is_null($model['report_id']) && $model['r_status'] == 'accepted') {
                            $this->addQuarterFactCash($fact_budget_data, $real_budget_data, $model['model_q'], $model['cost']);
                            //var_dump($model['activity_id'] . '--' . $model['id'] . '---' . $model['model_q'] . '---' . $model['cost'] . '--' . $fact_budget_data[$model['model_q']] . '--' . $dealer_id);

                            $companies_statistics[$model['Activity']['type_company_id']]['calc']->addToQBudgetOneQuarter($model['model_q'], $model['cost'], $model);
                        }
                    }
                }

                //Выбираем только одну из компаний)

                //Вычисляем для компаний Сервисные акции и Имеджевая рекламая
                foreach ($company_types as $company_type) {
                    $companies_q_result = $companies_statistics[$company_type['id']]['calc']->getStatisticByQuarters();

                    for ($q_ind = 1; $q_ind <= 4; $q_ind++) {
                        $q_data = $companies_q_result[$q_ind][$company_type['id']];

                        $companies_statistics[$company_type['id']]['company_plan'] += $q_data['plan_company_budget'];
                        $companies_statistics[$company_type['id']]['company_fact'] += $q_data['total_cash'];

                        $companies_statistics_by_q[$company_type['id']]['quarters'][$q_ind]['plan'] = $q_data['plan_company_budget'];
                        $companies_statistics_by_q[$company_type['id']]['quarters'][$q_ind]['fact'] = $q_data['total_cash'];

                        if ($companies_statistics[$company_type['id']]['company_plan'] > 0) {
                            $companies_statistics[$company_type['id']]['fact_plan_company_percent'] = $companies_statistics[$company_type['id']]['company_fact'] * 100 / $companies_statistics[$company_type['id']]['company_plan'];
                        }
                    }
                }

                //Calc fact / plan percent
                $fact_plan_percent = 0;
                if ($total_real_budget > 0) {
                    $fact_plan_percent = $fact_budget * 100 / $total_real_budget;
                }

                //Calc mailing plans
                $dealerPlan = DealerPlansTable::getInstance()->createQuery()
                    ->select('MONTH(added_date) as month, SUM(plan1 + plan2) as plan')
                    ->groupBy('MONTH(added_date)')
                    ->where('dealer_id = ? and quarter(added_date) = ? and year(added_date) = ?', array($this->_dealers_numbers[$dealer_id], $this->_quarter, $this->_year))
                    ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                foreach($dealerPlan as $plan) {
                    $total_mailing_plan +=  $plan['plan'];
                }

                $mailings = MailingListTable::getInstance()->createQuery()
                    ->select('MONTH(added_date) as month, count(*) as count')
                    ->where('dealer_id = ? and quarter(added_date) = ? and year(added_date) = ?', array($this->_dealers_numbers[$dealer_id], $this->_quarter, $this->_year))
                    ->groupBy('MONTH(added_date)')
                    ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                foreach($mailings as $mailing) {
                    $total_dealer_mailings += $mailing['count'];
                }

                $this->_statistic_aggregator[$dealer_id] = array(
                    'dealer_data' => array('dealer_name' => $dealer->getName(), 'dealer_number' => $dealer->getShortNumber(), 'regional_manager' => $dealer->getPkwManagerName()),
                    'dealer_statistic' => array(
                        'plan_budget_data' => $real_budget_data,
                        'fact_budget_data' => $fact_budget_data,
                        'activities' => $activities_statistic,
                        'plan' => $total_real_budget,
                        'fact' => $fact_budget,
                        'fact_plan_percent' => $fact_plan_percent,
                        'companies_statistics' => $companies_statistics,
                        'companies_statistics_by_q' => $companies_statistics_by_q,
                        'email_percent' => $total_mailing_plan > 0 ? $total_dealer_mailings / ($total_mailing_plan / 100) : 0
                    )
                );
            }

            //if ($it++ > 3) { break; }
        }

        //Work with dealer who don`t have any created models
        $dealers_without_models = array_diff($this->_dealers_ids, $dealers_work_with_models);
        foreach ($dealers_without_models as $dealer_id) {
            $activities_statistic = array();
            $total_mailing_plan = 0;
            $total_dealer_mailings = 0;

            if (!array_key_exists($dealer_id, $dealers)) {
                $dealers[$dealer_id] = $dealer_id;

                $companies_statistics_by_q = array();

                $dealer = DealerTable::getInstance()->createQuery()->select('name, number')->where('id = ?', $dealer_id)->fetchOne();

                $real_budget = new RealBudgetCalculator($dealer, $this->_year);
                $real_budget_data = $real_budget->getPlanBudget();
                $fact_budget_data = array(1 => 0, 2 => 0, 3 => 0, 4 => 0);

                $fact_plan_percent = 0;

                //Calc real budget
                $total_real_budget = 0;
                for ($q_ind = 1; $q_ind <= 4; $q_ind++) {
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
                        )),
                    );

                    $companies_statistics_by_q[$company_type['id']]['quarters'] = array(
                        1 => array('plan' => 0, 'fact' => 0),
                        2 => array('plan' => 0, 'fact' => 0),
                        3 => array('plan' => 0, 'fact' => 0),
                        4 => array('plan' => 0, 'fact' => 0)
                    );
                }

                //Выбираем только одну из компаний)
                //Вычисляем для компаний Сервисные акции и Имеджевая рекламая
                foreach ($company_types as $company_type) {
                    $companies_q_result = $companies_statistics[$company_type['id']]['calc']->getStatisticByQuarters();

                    for ($q_ind = 1; $q_ind <= 4; $q_ind++) {
                        $q_data = $companies_q_result[$q_ind][$company_type['id']];

                        $companies_statistics[$company_type['id']]['company_plan'] += $q_data['plan_company_budget'];
                        $companies_statistics[$company_type['id']]['company_fact'] += $q_data['total_cash'];

                        $companies_statistics_by_q[$company_type['id']]['quarters'][$q_ind]['plan'] = $q_data['plan_company_budget'];
                        $companies_statistics_by_q[$company_type['id']]['quarters'][$q_ind]['fact'] = $q_data['total_cash'];

                        if ($companies_statistics[$company_type['id']]['company_plan'] > 0) {
                            $companies_statistics[$company_type['id']]['fact_plan_company_percent'] = $companies_statistics[$company_type['id']]['company_fact'] * 100 / $companies_statistics[$company_type['id']]['company_plan'];
                        }
                    }
                }

                //Calc fact / plan percent
                if ($total_real_budget > 0) {
                    $fact_plan_percent = $fact_budget * 100 / $total_real_budget;
                }

                //Calc mailing plans
                $dealerPlan = DealerPlansTable::getInstance()->createQuery()
                    ->select('MONTH(added_date) as month, SUM(plan1 + plan2) as plan')
                    ->groupBy('MONTH(added_date)')
                    ->where('dealer_id = ? and quarter(added_date) = ? and year(added_date) = ?', array($this->_dealers_numbers[$dealer_id], $this->_quarter, $this->_year))
                    ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                foreach($dealerPlan as $plan) {
                    $total_mailing_plan +=  $plan['plan'];
                }

                $mailings = MailingListTable::getInstance()->createQuery()
                    ->select('MONTH(added_date) as month, count(*) as count')
                    ->where('dealer_id = ? and quarter(added_date) = ? and year(added_date) = ?', array($this->_dealers_numbers[$dealer_id], $this->_quarter, $this->_year))
                    ->groupBy('MONTH(added_date)')
                    ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                foreach($mailings as $mailing) {
                    $total_dealer_mailings += $mailing['count'];
                }

                $this->_statistic_aggregator[$dealer_id] = array(
                    'dealer_data' => array('id' => $dealer->getId(), 'dealer_name' => $dealer->getName(), 'dealer_number' => $dealer->getShortNumber(), 'regional_manager' => $dealer->getPkwManagerName()),
                    'dealer_statistic' => array(
                        'plan_budget_data' => $real_budget_data,
                        'fact_budget_data' => $fact_budget_data,
                        'activities' => $activities_statistic,
                        'plan' => $total_real_budget,
                        'fact' => $fact_budget,
                        'fact_plan_percent' => $fact_plan_percent,
                        'companies_statistics' => $companies_statistics,
                        'companies_statistics_by_q' => $companies_statistics_by_q,
                        'email_percent' => $total_mailing_plan > 0 ? $total_dealer_mailings / ($total_mailing_plan / 100) : 0
                    )
                );
            }
        }

        return $this->_statistic_aggregator;
    }

    /**
     * @param $fact_budget_data
     * @param $real_budget_data
     * @param $quarter
     * @param $cost
     * @internal param $cash
     * @internal param $fact_q_plan
     */
    private function addQuarterFactCash(&$fact_budget_data, $real_budget_data, $quarter, $cost) {
        $fact_budget_data[$quarter] += $cost;

        //If q less than 4, move cash by quarters
        /*if ($quarter < 4) {
            //If cash what we have is greater than real budget, set cash to real budget
            if ($fact_budget_data[$quarter] > $real_budget_data[$quarter]) {
                $prev_sum = $fact_budget_data[$quarter] - $real_budget_data[$quarter];
                $fact_budget_data[$quarter] = $real_budget_data[$quarter];

                $this->addQuarterFactCash($fact_budget_data, $real_budget_data, $quarter + 1, $prev_sum);
            }
        }*/
    }

    public function getData() {
        $start_from = 8;

        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle('Выгрузка (В разрезе года)');

        $q_label = '';
        for ($q_ind = 1; $q_ind <= $this->_quarter; $q_ind++) {
            $q_label .= $q_ind != $this->_quarter ? $q_ind . '+' : $q_ind;
        }

        $q_label = '(' . $q_label . ')';

        $headers = array('Региональный менеджер PKW', 'Название дилера', 'номер дилера',
            'План ' . $this->_year . 'г.',
            'План 1 квартал',
            'План 2 квартал',
            'План 3 квартал',
            'План 4 квартал');

        for($q_ind = 1; $q_ind <= $this->_quarter; $q_ind++) {
            $headers[] = sprintf('Факт %s', $q_ind);

            $start_from++;
        }

        $headers[] = sprintf("Факт %s / План %s", $q_label, $q_label);
        $headers[] = sprintf("Факт / План по сервисным акциям %s", $q_label, $q_label);
        $headers[] = sprintf("Факт / План по имиджевым акциям %s", $q_label, $q_label);

        $start_from += 3;

        $fact_quarters = array();
        for($q_ind = 1; $q_ind <= $this->_quarter; $q_ind++) {
            $fact_quarters[] = $q_ind;
        }
        $headers[] = sprintf("Факт (%s)", implode('+', $fact_quarters));
        $headers[] = "Выполнено активностей";

        $start_from += 2;

            /*'Факт '.$this->_quarter.' кв.',
            'Факт/план '.$this->_quarter.' квартал',
            'Факт по сервисным акциям '.$this->_quarter.' кв.',
            'Факт/план по сервисным ациям '.$this->_quarter.' кв.',
            'Факт по имиджевым акциям '.$this->_quarter.' кв.',
            'Факт/план по имиджевым ациям '.$this->_quarter.' кв.',
            'К-во активностей',
            '% по E-mail за квартал'
        );*/

        /*$headers[] = 'К-во активностей';
        $headers[] = '% по E-mail за квартал';*/

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

        $right_bold_underline = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '9',
                'bold' => true,
                'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $small_font = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '9',
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $bold_small_font = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '12',
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $bold_small_font_left = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '10',
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $aSheet->getRowDimension('1')->setRowHeight(15);
        $aSheet->getStyle('A1:M1')->getAlignment()->setWrapText(true);

        $last_letter = PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);

        $aSheet->getStyle('A3:' . $last_letter . '3')->applyFromArray($header_font);
        $aSheet->getStyle('A3:A3')->applyFromArray($header_font_with_underline);

        $dealers_activities_list = array();
        $dealers_activities_list_data = array();
        $position_start = $start_from;
        $position_inc = 1;
        $index = 0;

        $total_completed_activities = array();

        foreach ($this->_statistic_aggregator as $dealer_id => $result_data) {
            $total_completed_activities[$dealer_id] = array('activities_completed' => 0);

            if (count($result_data['dealer_statistic']['activities']) > 0) {
                foreach ($result_data['dealer_statistic']['activities'] as $activity_id => $activity) {

                    if (!array_key_exists($activity_id, $dealers_activities_list)) {
                        //Если принудительно выполнена активность, фиксируем это
                        if (ActivitiesStatusByUsersTable::getInstance()->createQuery()
                                ->where('activity_id = ? and dealer_id = ?', array($activity_id, $dealer_id))
                                ->andWhere('by_year = ? and by_quarter = ?', array($this->_year, $this->_quarter))
                                ->count() > 0) {
                            $result_data['dealer_statistic']['activities'][$activity_id]['status'] = true;
                        }

                        $dealers_activities_list[$activity_id] = array('data' => $activity,
                            'position_start' => $position_start,
                            'position' => $position_inc,
                        );

                        $dealers_activities_list_data[$index++] = array('data' => $activity, 'position' => $position_inc, 'activity_id' => $activity_id);

                        $position_start += $position_inc;
                    }

                    //Подсчитываем общее количество выполненных активностей
                    if ($result_data['dealer_statistic']['activities'][$activity_id]['status']) {
                        $total_completed_activities[$dealer_id]['activities_completed']++;
                    }
                }
            }
        }

        $activities_count = count($dealers_activities_list);
        $column = $high_column_count = $start_from;

        $row = 3;
        for($activity_index = 0; $activity_index < $activities_count; $activity_index++)
        {
            $activity_data = $dealers_activities_list_data[$activity_index];

            $aSheet->setCellValueByColumnAndRow($column++, $row, $activity_data['data']['name']);
            $high_column_count++;
        }

        $high_column = $aSheet->getHighestColumn();

        //Если нет данных то просто отдаем пустое значение
        if (empty($high_column)) {
            return '';
        }

        $aSheet->getStyle($last_letter . $row . ':' . $high_column . $row)->applyFromArray($header_font);
        $aSheet->getStyle($last_letter.$row.':'.$high_column.$row)->getAlignment()->setWrapText(true);

        $cellIterator = $aSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $aSheet->getColumnDimension($cell->getColumn())->setWidth(25);
        }

        $fillColor = 'f0fffe';

        $aSheet->getColumnDimension('A')->setWidth(30);
        $aSheet->getColumnDimension('B')->setWidth(40);
        $aSheet->getColumnDimension('C')->setWidth(10);

        $aSheet->freezePane('B4');

        $company_types = ActivityCompanyTypeTable::getInstance()->createQuery()->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        $row = 4;

        foreach ($this->_statistic_aggregator as $dealer_id => $result_data) {
            $column = 0;

            $aSheet->setCellValueByColumnAndRow($column++, $row, $result_data['dealer_data']['regional_manager']);
            $aSheet->setCellValueByColumnAndRow($column++, $row, $result_data['dealer_data']['dealer_name']);
            $aSheet->setCellValueByColumnAndRow($column++, $row, $result_data['dealer_data']['dealer_number']);

            $aSheet->setCellValueByColumnAndRow($column++, $row, Utils::format_number($result_data['dealer_statistic']['plan']));

            for($plan_q = 1; $plan_q <= 4; $plan_q++) {
                $aSheet->setCellValueByColumnAndRow($column++, $row, Utils::format_number($result_data['dealer_statistic']['plan_budget_data'][$plan_q]));
            }

            $fact_cash = 0;
            $plan_cash = 0;
            for($fact_plan_q = 1; $fact_plan_q <= $this->_quarter; $fact_plan_q++) {
                $aSheet->setCellValueByColumnAndRow($column++, $row, Utils::format_number($result_data['dealer_statistic']['fact_budget_data'][$fact_plan_q]));

                $fact_cash += $result_data['dealer_statistic']['fact_budget_data'][$fact_plan_q];
                $plan_cash += $result_data['dealer_statistic']['plan_budget_data'][$fact_plan_q];
            }

            $aSheet->setCellValueByColumnAndRow($column++, $row, ceil(Utils::format_number($plan_cash > 0 ? $fact_cash * 100 / $plan_cash : 0)) . '%');

            foreach ($company_types as $company_type) {
                $company_plan_cash = 0;
                $company_fact_cash = 0;

                for($company_plan_q = 1; $company_plan_q <= $this->_quarter; $company_plan_q++) {
                    $company_plan_cash += $result_data['dealer_statistic']['companies_statistics_by_q'][$company_type['id']]['quarters'][$company_plan_q]['plan'];
                    $company_fact_cash += $result_data['dealer_statistic']['companies_statistics_by_q'][$company_type['id']]['quarters'][$company_plan_q]['fact'];
                }

                $aSheet->setCellValueByColumnAndRow($column++, $row,
                    ceil(Utils::format_number($company_plan_cash > 0 ? $company_fact_cash * 100 / $company_plan_cash : 0)).'%'
                );
            }

            $aSheet->setCellValueByColumnAndRow($column++, $row, Utils::format_number($fact_cash));

            $aSheet->setCellValueByColumnAndRow($column, $row, $total_completed_activities[$dealer_id]['activities_completed']);

            /*$aSheet->setCellValueByColumnAndRow($column++, $row, count($result_data['dealer_statistic']['activities']));
            $aSheet->setCellValueByColumnAndRow($column++, $row, ceil($result_data['dealer_statistic']['email_percent']).'%');*/

            $aSheet->getStyle('A3'.':X3')->getAlignment()->setWrapText(true);

            $aSheet->getStyle('A' . $row . ':A' . $row)->applyFromArray($leftFontWithUnderline);
            $aSheet->getStyle('B' . $row . ':'. $last_letter . $row)->applyFromArray($boldFont);


            if (count($result_data['dealer_statistic']['activities']) > 0) {
                foreach ($result_data['dealer_statistic']['activities'] as $activity_id => $activity) {
                    $column = $dealers_activities_list[$activity_id]['position_start'];

                    $aSheet->setCellValueByColumnAndRow($column++, $row, $result_data['dealer_statistic']['activities'][$activity_id]['status'] ? 'Да' : 'Нет');
                }

                for($column_ind = $start_from; $column_ind < $high_column_count; $column_ind++) {
                    $value = $aSheet->getCellByColumnAndRow($column_ind, $row)->getValue();
                    if (is_null($value)) {
                        $aSheet->setCellValueByColumnAndRow($column_ind, $row, 'Нет');
                    }
                }
            } else {
                for($column_ind = $start_from; $column_ind < $high_column_count; $column_ind++) {
                    $value = $aSheet->getCellByColumnAndRow($column_ind, $row)->getValue();
                    if (is_null($value)) {
                        $aSheet->setCellValueByColumnAndRow($column_ind, $row, 'Нет');
                    }
                }
            }

            $row++;
        }

        $file_name = '/uploads/statistics_y('.$this->_year.').xlsx';

        $objWriter = PHPExcel_IOFactory::createWriter($pExcel, "Excel2007");
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/statistics_y('.$this->_year.').xlsx');

        return $file_name;
    }
}
