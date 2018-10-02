<?php

include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Cell.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Writer/Excel5.php');

/**
 * agreement_activity actions.
 *
 * @package    Servicepool2.0
 * @subpackage agreement_activity
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class agreement_activityActions extends ActionsWithJsonForm
{
    const FILTER_EXPORT_NAMESPACE = 'activity_export_filter';

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    function executeIndex(sfWebRequest $request)
    {
        $this->outputPrev($request);
        $this->outputActivities(false);

        $this->is_finished = false;
        $this->setTemplate('activities');
    }

    function executeFinishedActivities(sfWebRequest $request)
    {
        $this->outputPrev($request);
        $this->outputActivities(true);

        $this->years_range = array_filter(D::getYearsRangeList(null, null, 0));

        $this->is_finished = true;
        $this->setTemplate('activities');
    }

    function executeActivity(sfWebRequest $request)
    {
        $this->outputPrev($request);

        $activity = ActivityTable::getInstance()->find($request->getParameter('id'));
        $this->dealer_filter = $this->getDealerFilter();
        $this->start_date_filter = $this->getStartDateFilter();
        $this->end_date_filter = $this->getEndDateFilter();
        $this->view_data_filter = $this->getViewDataFilter();

        $this->forward404Unless($activity);

        $builder = new AgreementActivityModelsStatisticBuilder($activity, $this->dealer_filter, null, $this->start_date_filter, $this->end_date_filter);
        $builder->build();

        $this->builder = $builder;
        $this->activityId = $request->getParameter('id');

        $this->outputDeclineReasons();
        $this->outputDeclineReportReasons();
        $this->outputSpecialistGroups();
    }

    /**
     * Export activity data to Excel
     * @param sfWebRequest $request
     */
    function executeExportActivity(sfWebRequest $request)
    {
        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle('Дилеры');

        $headers = array('#', '№ дилера', 'Название дилера', 'Тип рекламы', 'Согласовал макет', 'Согласовал отчет', 'Сумма');
        $column = 0;
        $row = 0;

        //настройки для шрифтов
        $baseFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        );

        $boldFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $rightFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $smallRightFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '8',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $left = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $aSheet->getStyle('A1:G1')->applyFromArray($boldFont);
        $aSheet->getStyle('B:G')->applyFromArray($left);

        $column = 0;
        $tCount = 1;
        foreach ($headers as $head) {
            $aSheet->setCellValueByColumnAndRow($column++, 1, $head);
            $tCount++;
        }

        $aSheet->getColumnDimension('A')->setWidth(3);
        $aSheet->getColumnDimension('B')->setWidth(10);
        $aSheet->getColumnDimension('C')->setWidth(40);
        $aSheet->getColumnDimension('D')->setWidth(15);
        $aSheet->getColumnDimension('E')->setWidth(15);
        $aSheet->getColumnDimension('F')->setWidth(15);
        $aSheet->getColumnDimension('G')->setWidth(20);

        $aSheet->getRowDimension('1')->setRowHeight(35);

        $aSheet->getStyle('A1:G1')->getAlignment()->setWrapText(true);

        $activity = ActivityTable::getInstance()->find($request->getParameter('activity_id'));
        $dealer = $request->getParameter('dealer');
        $modelWorkStatus = $request->getParameter('model_work_status');
        $quarter = $request->getParameter('quarter');

        $this->forward404Unless($activity);

        $this->dealer_filter = $this->getDealerFilter();
        $this->start_date_filter = $this->getStartDateFilter();
        $this->end_date_filter = $this->getEndDateFilter();

        $builder = new AgreementActivityModelsStatisticBuilder($activity, $this->dealer_filter, null, $this->start_date_filter, $this->end_date_filter, $quarter);
        $builder->build();

        $row = 2;

        $statsResult = $builder->getStat();
        $extendedStats = $statsResult['extended'];

        foreach ($extendedStats as $q => $data) {
            foreach ($data as $year => $dealers) {
                foreach ($dealers as $id => $dealer) {
                    if ($dealer['all'] > 0) {
                        $column = 1;

                        if ($dealer['done'] && $dealer['all'] > 0) {
                            $icon = 'ok-icon-active.png';
                            $fillColor = 'D6FDD6';
                        } else {
                            if ($dealer['accepted_models'] > 0) {
                                $icon = 'ok-icon.png';
                                $fillColor = 'e6f0f2';
                            } else {
                                $icon = 'error-icon.png';
                                $fillColor = 'FBCBC6';
                            }
                        }

                        $this->drawExcelImage($icon, 'A' . $row, $pExcel, 3, 5);

                        $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer['dealer']->getShortNumber());
                        $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer['dealer']->getName());

                        $aSheet->getStyle('A' . $row . ':G' . $row)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB($fillColor);

                        $aSheet->getRowDimension($row)->setRowHeight(20);

                        $totalSumm = 0;
                        $originalRow = $row;

                        foreach ($dealer['models'] as $n => $model) {
                            $row++;
                            $tempColumn = $column - 1;

                            $aSheet->setCellValueByColumnAndRow($tempColumn++, $row, $model->getId());
                            $aSheet->getStyle('C' . $row . ':C' . $row)->applyFromArray($smallRightFont);

                            $aSheet->setCellValueByColumnAndRow($tempColumn, $row, $model->getModelType()->getName());
                            $aSheet->getStyle('D' . $row . ':D' . $row)->applyFromArray($baseFont);

                            if ($model->getCssStatus() == 'ok') {
                                $this->drawExcelImage('accepted.png', 'E' . $row, $pExcel, 50);
                            } else {
                                $this->drawExcelImage('not_accepted.png', 'E' . $row, $pExcel, 50);
                            }

                            if ($model->getReportCssStatus() == 'ok') {
                                $this->drawExcelImage('accepted.png', 'F' . $row, $pExcel, 50);
                            } else {
                                $this->drawExcelImage('not_accepted.png', 'F' . $row, $pExcel, 50);
                            }

                            $aSheet->setCellValueByColumnAndRow($tempColumn + 3, $row, $this->formatPrice($model->getCost()));
                            $aSheet->getStyle('G' . $row . ':G' . $row)->applyFromArray($rightFont);

                            $totalSumm += $model->getCost();
                        }

                        $aSheet->setCellValueByColumnAndRow($column + 3, $originalRow, $this->formatPrice($totalSumm));
                        $aSheet->getStyle('G' . $originalRow . ':G' . $originalRow)->applyFromArray($boldFont);

                        $row++;
                    }
                }
            }
        }

        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/dealers.xls');

        return $this->sendJson(array('url' => 'http://dm.vw-servicepool.ru/uploads/dealers.xls', 'success' => true));
    }

    private function drawExcelImage($icon, $coordinates, $pExcel, $offsetX = 3, $offsetY = 3)
    {
        $imageModelStatus = new PHPExcel_Worksheet_Drawing();

        $imageModelStatus->setPath(sfConfig::get('app_images_path') . '/' . $icon);
        $imageModelStatus->setName('work_status');
        $imageModelStatus->setDescription('work_status');
        $imageModelStatus->setHeight(16);
        $imageModelStatus->setWidth(16);

        $imageModelStatus->setOffsetX($offsetX);
        $imageModelStatus->setOffsetY($offsetY);

        $imageModelStatus->setWorksheet($pExcel->getActiveSheet());
        $imageModelStatus->setCoordinates($coordinates);
    }

    private function formatPrice($price)
    {
        return number_format($price, 2, '.', ' ') . ' руб.';
    }

    function executeNoWorkDealers(sfWebRequest $request)
    {
        $activity = ActivityTable::getInstance()->find($request->getParameter('id'));
        $this->forward404Unless($activity);

        //$builder = new AgreementActivityStatisticBuilder($activity, null, true);
        $builder = new AgreementActivitiesStatisticBuilder($activity, null, true, $this->getUser()->getAuthUser());
        $stat = $builder->buildNoWork();

        $this->activity = $activity;
        $this->status = sfConfig::get('app_no_work');

        $this->outputDealers($stat[$activity->getId()]['no_work_dealers']);
    }

    function executeInWorkDealers(sfWebRequest $request)
    {
        $activity = ActivityTable::getInstance()->find($request->getParameter('id'));
        $this->forward404Unless($activity);

        //$builder = new AgreementActivityStatisticBuilder($activity, null, true);
        $builder = new AgreementActivitiesStatisticBuilder($activity, null, true, $this->getUser()->getAuthUser());

        $stat = $builder->buildInWork();

        $this->activity = $activity;
        $this->status = sfConfig::get('app_in_work');

        $this->outputDealers($stat[$activity->getId()]['in_work_dealers']);
    }

    function executeDoneDealers(sfWebRequest $request)
    {
        $activity = ActivityTable::getInstance()->find($request->getParameter('id'));
        $this->forward404Unless($activity);

        //$builder = new AgreementActivityStatisticBuilder($activity, null, true);
        $builder = new AgreementActivitiesStatisticBuilder($activity, null, true, $this->getUser()->getAuthUser());
        $stat = $builder->buildDone();

        $this->activity = $activity;
        $this->status = sfConfig::get('app_done');
        $this->outputDealers($stat[$activity->getId()]['done_dealers']);
    }

    function executeActivitiesStatus(sfWebRequest $request)
    {
        $this->dealer = $request->getParameter('dealer', '');
        $quarter = $request->getParameter('quarter', D::getQuarter(time()));
        $year = $request->getParameter('year', D::getYear(time()));
        $this->quarter = $quarter;
        $this->activities_filter = $request->getParameter('activities_filter', '');

        $this->outputPrev($request);

        $completed_activities_list_by_user = ActivitiesStatusByUsersTable::getCompletedActivitiesList($this->year, $quarter);

        $this->activities = array();
        $query = DealerActivitiesStatsDataTable::getInstance()
            ->createQuery('ds')
            ->select('ds.*, a.name as activityName, a.id as activityId, manager_stat.manager_id, a.mandatory_activity')
            ->leftJoin('ds.Activity a')
            ->innerJoin('ds.DealerActivitiesStats manager_stat')
            ->where('ds.year = ?', $this->year)
            ->groupBy('ds.activity_id')
            ->orderBy('activityId DESC');

        if (!empty($this->activities_filter)) {
            if ($this->activities_filter == 'simple') {
                $query->andWhere('a.mandatory_activity = ?', false);
            } else if ($this->activities_filter == 'mandatory') {
                $query->andWhere('a.mandatory_activity = ?', true);
            }
        }

        $temp_result = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        if ($this->getUser()->isRegionalManager()) {
            foreach ($temp_result as $temp_item) {
                foreach ($temp_item['DealerActivitiesStats'] as $key => $temp_item_data) {
                    if ($temp_item_data['manager_id'] = $this->getUser()->getAuthUser()->getNaturalPersonId()) {
                        $this->activities[] = $temp_item;
                    }
                }
            }
        }
        else {
            $this->activities = $temp_result;
        }

        $activeActivities = array();
        foreach ($this->activities as $activity_row) {
            $activeActivities[$activity_row['activityId']] = $activity_row['activityId'];
        }

        //Get mandatory activities list data
        $this->activities_mandatory_list = array();

        $activities_temp_list = ActivityTable::getInstance()->createQuery()->select('mandatory_activity')->whereIn('id', array_values($activeActivities))->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        foreach ($activities_temp_list as $activity_item) {
            $this->activities_mandatory_list[$activity_item['id']] = $activity_item['mandatory_activity'];
        }

        $query = DealerActivitiesStatsManagersTable::getInstance()
            ->createQuery('dam')
            ->where('year = ?',
                array
                (
                    $this->year,
                )
            )
            ->orderBy('manager_id ASC');

        if ($this->getUser()->isRegionalManager()) {
            $query->andWhere('manager_id = ?', $this->getUser()->getAuthUser()->getNaturalPersonId());
        }
        $this->managers = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $this->workStats = array();
        $this->manager_dealers_activities_work_statuses = array();
        $this->total_activities_completed = array();
        $this->dealers_statistics = array();
        $this->dealers_statistics_activities = array();

        $dealers_list_ids = array();
        $manager_dealers_list = array();
        $dealer_manager = array();

        //Делаем проверку на выбор по году / кварталу
        $quarters = array($this->quarter => $this->quarter);
        if (empty($this->quarter)) {
            $quarters = array();
            for($q_i = 1; $q_i <= 4; $q_i++) {
                $quarters[$q_i] = $q_i;
            }
        }

        //Средний процент выполнения бюджета по региональному менеджеру привязанных к нему дилеров
        $this->avg_percent_of_budget_for_regional_manager = array();
        $this->completed_models_cost_by_year = array();
        $this->completed_models_count_by_year = array();
        $this->completed_models_count_by_quarter = array();
        $this->total_avg_models_completed_cost_manager_by_quarter = array();

        foreach ($quarters as $q_key => $q) {
            $this->completed_models_count_by_quarter[$q_key] = array();
        }

        foreach ($this->managers as $manager) {
            $dealerStats = DealerActivitiesStatsTable::getInstance()
                ->createQuery('das')
                ->select('das.id, d.name, das.percent_of_budget, das.models_completed, das.models_completed_cost, das.activities_completed, das.manager_id, das.dealer_id, das.q1, das.q2, das.q3, das.q4, das.q_activity1, das.q_activity2, das.q_activity3, das.q_activity4, das.models_completed_cost_q1, das.models_completed_cost_q2, das.models_completed_cost_q3, das.models_completed_cost_q4')
                ->innerJoin('das.DealerStat d')
                ->leftJoin('das.ManagerStat ms')
                ->where('manager_id = ?', $manager['id'])
                ->andWhere('d.regional_manager_id = ?', $manager['manager_id'])
                ->orderBy('das.manager_id ASC')
                ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            //Проверка пользователя что он является региональным менеджером
            if (NaturalPersonTable::getInstance()->createQuery()->where('id = ? and (regional_manager_id != 0 or regional_manager_nfz_id != 0)', $manager['manager_id'])->count() == 0) {
                continue;
            }

            foreach ($dealerStats as $stat) {
                $this->dealers_statistics[$manager['manager_id']][] = $stat;
                $dealers_list_ids[$stat['id']] = $stat['dealer_id'];

                $this->avg_percent_of_budget_for_regional_manager[$manager['id']][] = $stat['percent_of_budget'];
                $this->completed_models_cost_by_year[$manager['id']][] = $stat['models_completed_cost'];

                //Общее количество выполненных заявок за год
                if (!array_key_exists($manager['id'], $this->completed_models_count_by_year)) {
                    $this->completed_models_count_by_year[$manager['id']] = 0;
                }
                $this->completed_models_count_by_year[$manager['id']] += $stat['models_completed'];

                //Сохраняем обющее количестов дилеров у менеджера
                if (!in_array($stat['dealer_id'], $manager_dealers_list)) {
                    $manager_dealers_list[$manager['id']][] = $stat['dealer_id'];
                }

                //Фиксируем для каждого дилера, своего менеджера
                $dealer_manager[$stat['dealer_id']] =  $manager['id'];

                //Учитываем среднюю сумму по кварталам по выполненным заявкам
                if (!array_key_exists($manager['id'], $this->total_avg_models_completed_cost_manager_by_quarter)) {
                    $this->total_avg_models_completed_cost_manager_by_quarter[$manager['id']] = 0;
                }

                foreach ($quarters as $q_key => $q) {
                    $this->total_avg_models_completed_cost_manager_by_quarter[$manager['id']] += $stat['models_completed_cost_q'.$q];
                }

                //Общее количетсов заявок выполненных за квартал
                foreach ($quarters as $q_key => $q) {
                    if (!array_key_exists($manager['id'], $this->completed_models_count_by_quarter[$q_key])) {
                        $this->completed_models_count_by_quarter[$q_key][$manager['id']] = 0;
                    }
                    $this->completed_models_count_by_quarter[$q_key][$manager['id']] += $stat['q'.$q];
                }
            }
        }

        $dealers_statistic_ids = array();
        foreach ($this->dealers_statistics as $manager_key => $dealer_statistics) {
            foreach ($dealer_statistics as $dealer_stat_items) {
                $dealers_statistic_ids[] = $dealer_stat_items['id'];
            }
        }

        $dealersActivities = DealerActivitiesStatsDataTable::getInstance()
            ->createQuery('as')
             ->select('activity_id, status, total_completed, dealer_stat_id, id, as.q1, as.q2, as.q3, as.q4, year, ds.dealer_id as dealer_id, ds.models_completed as models_completed, ds.models_completed_cost as models_completed_cost, ds.activities_completed as activities_completed')
            ->innerJoin('as.DealerActivitiesStats ds')
            ->leftJoin('as.Activity a')
            ->whereIn('as.dealer_stat_id', $dealers_statistic_ids)
            ->andWhere('as.year = ?', $this->year)
            ->orderBy('a.id DESC')
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        //Получаем среднее значение выполненных активностей по каждому рег. менеджеру
        $this->total_avg_activities_completed_for_manager_by_year = array();
        $this->total_avg_activities_completed_for_manager_by_quarter = array();

        $this->total_activities_completed_for_manager_by_year = array();
        $this->total_activities_in_work_for_manager_by_year = array();

        $dealer_stats_data_check = array();
        //
        foreach ($dealersActivities as $item) {
            if (!in_array($item['activity_id'], $activeActivities)) {
                continue;
            }

            if (!isset($this->workStats[$item['activity_id']])) {
                $this->workStats[$item['activity_id']] = array('in_work' => 0, 'completed' => 0);
            }

            if (!array_key_exists($dealer_manager[$item['dealer_id']], $this->manager_dealers_activities_work_statuses)) {
                $this->manager_dealers_activities_work_statuses[$dealer_manager[$item['dealer_id']]] = array();
            }

            if (!array_key_exists($item['activity_id'], $this->manager_dealers_activities_work_statuses[$dealer_manager[$item['dealer_id']]])) {
                $this->manager_dealers_activities_work_statuses[$dealer_manager[$item['dealer_id']]][$item['activity_id']] = array('in_work' => 0, 'completed' => 0);
            }

            //Выполнение активностей за квартал
            $item['activity_complete'] = 'none';

            //Проверка на принудительное выполнение активности пользователем
            if (!empty($completed_activities_list_by_user) && array_key_exists($item['activity_id'], $completed_activities_list_by_user)) {
                foreach ($completed_activities_list_by_user[$item['activity_id']] as $complete_item) {
                    if ($complete_item['dealer_id'] == $dealers_list_ids[$item['dealer_stat_id']] && $complete_item['activity_id'] == $item['activity_id']) {
                        $item['activity_complete'] = 'ok';
                    }
                }
            }

            if (!array_key_exists($item['dealer_stat_id'], $this->total_activities_completed)) {
                $this->total_activities_completed[$item['dealer_stat_id']] = 0;
            }

            //Если выбран период за весь год, проверяем статус выполнения активности за год, иначе проходим по всем кварталам
            //Учитываем общее выполнение по активности, без учета квартала
            if (!array_key_exists($dealer_manager[$item['dealer_id']], $this->total_avg_activities_completed_for_manager_by_year)) {
                $this->total_avg_activities_completed_for_manager_by_year[$dealer_manager[$item['dealer_id']]] = 0;
            }

            //Количество
            if (!array_key_exists($dealer_manager[$item['dealer_id']], $this->total_activities_completed_for_manager_by_year)) {
                $this->total_activities_completed_for_manager_by_year[$dealer_manager[$item['dealer_id']]] = 0;
            }

            if (!array_key_exists($dealer_manager[$item['dealer_id']], $this->total_activities_in_work_for_manager_by_year)) {
                $this->total_activities_in_work_for_manager_by_year[$dealer_manager[$item['dealer_id']]] = 0;
            }

            if ($item['status'] == 'ok') {
                //Фиксируем количество выполненных активностей по менеджерам за год
                if (array_key_exists($item['dealer_id'], $dealer_manager)) {
                    $this->total_avg_activities_completed_for_manager_by_year[$dealer_manager[$item['dealer_id']]]++;
                }
            }

            //Для года фиксируем выполнение по любому из кварталов
            if (count($quarters) > 1) {
                if ($item['status'] == 'ok') {
                    $item['activity_complete'] = 'ok';

                    $this->total_activities_completed[$item['dealer_stat_id']]++;
                }
            } else {
                foreach($quarters as $q_index => $q_data) {
                    //Учитывем выполнение активности по кварталу
                    if ($item['q' . $q_index] == 1) {
                        $this->workStats[$item['activity_id']]['completed']++;
                        $item['activity_complete'] = 'ok';

                        $this->total_activities_completed[$item['dealer_stat_id']]++;

                        //Фиксируем количество выполненных активностей по менеджерам
                        if (array_key_exists($item['dealer_id'], $dealer_manager)) {
                            if (!array_key_exists($dealer_manager[$item['dealer_id']], $this->total_avg_activities_completed_for_manager_by_quarter)) {
                                $this->total_avg_activities_completed_for_manager_by_quarter[$dealer_manager[$item['dealer_id']]] = 0;
                            }
                            $this->total_avg_activities_completed_for_manager_by_quarter[$dealer_manager[$item['dealer_id']]]++;
                        }

                        //var_Dump($this->total_activities_completed[$item['dealer_stat_id']].'--'.$item['activity_id']);
                    } else if ($item['q' . $q_index] == 2) {
                        $item['activity_complete'] = 'wait';
                        $this->workStats[$item['activity_id']]['in_work']++;
                    }
                }
            }

            //Делаем проход по всем кварталам для полчения статуса выполнения активности по дилерам
            foreach($quarters as $q_index => $q_data) {
                if ($item['q' . $q_index] == 1) {
                    $this->manager_dealers_activities_work_statuses[$dealer_manager[$item['dealer_id']]][$item['activity_id']]['completed']++;
                } else if ($item['q' . $q_index] == 2) {
                    $this->manager_dealers_activities_work_statuses[$dealer_manager[$item['dealer_id']]][$item['activity_id']]['in_work']++;
                }
            }

            $this->dealers_statistics_activities[$item['dealer_stat_id']][] = $item;
        }

        //Вычисляем средние значеиния для года / квартала
        $this->avg_completed_activities_for_manager_by_year = array();
        foreach ($this->total_avg_activities_completed_for_manager_by_year as $manager_id => $activities) {
            $this->avg_completed_activities_for_manager_by_year[$manager_id] = $manager_dealers_list[$manager_id] > 0 ? round($activities / count($manager_dealers_list[$manager_id]), 0) : 0;
        }

        $this->avg_completed_activities_for_manager_by_quarter = array();
        foreach ($this->total_avg_activities_completed_for_manager_by_quarter as $manager_id => $activities) {
            $this->avg_completed_activities_for_manager_by_quarter[$manager_id] = $manager_dealers_list[$manager_id] > 0 ? round($activities / count($manager_dealers_list[$manager_id]), 0) : 0;
        }

        $this->totalItems = DealerActivitiesStatsDataTable::getInstance()
            ->createQuery()
            ->select('value, field_name')
            ->where('year = ?', $this->year)
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $this->total = array();
        foreach ($this->totalItems as $item) {
            $this->total[$item['field_name']] = $item['value'];
        }

        $this->user = $this->getUser();
        //$this->builder = new AgreementActivityStatusStatisticBuilder($this->year, $quarter);

    }

    function executeFake()
    {

    }

    protected function outputActivities($finished)
    {
        //$builder = new AgreementActivityStatisticBuilder(null, $finished);
        $builder = new AgreementActivitiesStatisticBuilder(null, $finished, true, $this->getUser()->getAuthUser());
//        $builder->setYearFilter($this->year);
        $builder->setYearFilter($this->year);

        $this->activities = $builder->build();
    }

    function outputDeclineReasons()
    {
        $this->decline_reasons = AgreementDeclineReasonTable::getInstance()->createQuery()->execute();
    }

    function outputDeclineReportReasons()
    {
        $this->decline_report_reasons = AgreementDeclineReportReasonTable::getInstance()->createQuery()->execute();
    }

    function outputSpecialistGroups()
    {
        $this->specialist_groups = UserGroupTable::getInstance()
            ->createQuery('g')
            ->distinct()
            ->select('g.*')
            ->innerJoin('g.Roles r WITH r.role=?', 'specialist')
            ->innerJoin('g.Users u WITH u.active=?', true)
            ->execute();
    }

    function outputDealers($dealers)
    {
        $this->dealers = $dealers;
        $this->setTemplate('dealers');
    }

    function outputPrev(sfWebRequest $request)
    {
        $this->year = D::getBudgetYear($request);

        $this->budgetYears = D::getBudgetYears($request, false);
    }

    private function getDealerFilter()
    {
        $default = $this->getUser()->getAttribute('dealer', null, self::FILTER_EXPORT_NAMESPACE);
        $dealer = $this->getRequestParameter('dealer', $default);
        $this->getUser()->setAttribute('dealer', $dealer, self::FILTER_EXPORT_NAMESPACE);

        if ($dealer != -1 && !is_null($dealer)) {
            $dealer = DealerTable::getInstance()->findOneById($dealer);
        }

        return $dealer;
    }

    private function getStartDateFilter()
    {
        $default = $this->getUser()->getAttribute('start_date', '', self::FILTER_EXPORT_NAMESPACE);
        $start_date = $this->getRequestParameter('start_date', $default);
        $this->getUser()->setAttribute('start_date', $start_date, self::FILTER_EXPORT_NAMESPACE);

        return preg_match('#^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$#', $start_date)
            ? D::fromRus($start_date)
            : false;
    }

    private function getEndDateFilter()
    {
        $default = $this->getUser()->getAttribute('end_date', '', self::FILTER_EXPORT_NAMESPACE);
        $end_date = $this->getRequestParameter('end_date', $default);
        $this->getUser()->setAttribute('end_date', $end_date, self::FILTER_EXPORT_NAMESPACE);

        return preg_match('#^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$#', $end_date)
            ? D::fromRus($end_date)
            : false;
    }

    private function getViewDataFilter()
    {
        $default = $this->getUser()->getAttribute('view_data', 'quarters', self::FILTER_EXPORT_NAMESPACE);
        $view_data = $this->getRequestParameter('view_data', $default);
        $this->getUser()->setAttribute('view_data', $view_data, self::FILTER_EXPORT_NAMESPACE);

        return $view_data;
    }
}
