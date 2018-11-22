<?php

include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Cell.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Writer/Excel5.php');

/**
 * activity actions.
 *
 * @package    Servicepool2.0
 * @subpackage activity
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class activityActions extends BaseActivityActions
{
    const FILTER_NAMESPACE = 'stats';

    function executeIndex(sfWebRequest $request)
    {

        $this->outputActivity($request);

        $this->getUser()->setAttribute('current_q', 0, self::FILTER_Q_NAMESPACE);
        $current_q = $request->getParameter('current_q');

        if (!is_null($current_q)) {
            sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));

            //$this->outputModelsQuarters($request);
            $this->outputFilterByYear();
            $this->outputFilterByQuarter();

            $this->redirect(url_for('@agreement_module_models?activity=' . $request->getParameter('activity')));
        }

        $this->activity->markAsViewed($this->getUser()->getAuthUser());
    }

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    function executeFinished(sfWebRequest $request)
    {
        $this->year = D::getBudgetYear($request);

        $user = $this->getUser();
        $show_hidden = $user->isAdmin() || $user->isImporter() || $user->isManager();

        $query = ActivityTable::getInstance()
            ->createQuery('a')
            ->select('a.id, a.start_date, a.end_date, a.custom_date, a.name, a.brief, a.importance, v.id is_viewed')
            ->leftJoin('a.UserViews v WITH v.user_id=?', $this->getUser()->getAuthUser()->getId())
            ->where('finished=?', true)
            ->orderBy('a.position ASC');

        if (!$show_hidden)
            $query->andWhere('a.hide=?', false);

        $this->activities = $query->execute();
    }

    function executeStatistic(sfWebRequest $request)
    {

        $this->outputModelsQuarters($request);
        $this->outputFilterByYear();
        $this->outputFilterByQuarter();

        $this->activity = $this->getActivity($request);

        //$this->checkAllowToEdit();
        $this->preCheckStatisticStatus($request);
    }

    function executeStatisticOne(sfWebRequest $request)
    {
        $this->activity = $this->getActivity($request);

        $this->outputModelsQuarters($request);
        $this->outputFilterByYear();
        $this->outputFilterByQuarter();

        $user = $this->getUser()->getAuthUser();

        $userDealer = $user->getDealerUsers()->getFirst();
        if ($userDealer) {
            $dealer = DealerTable::getInstance()->createQuery('d')->where('id = ?', $userDealer->getDealerId())->fetchOne();
        }

        if (!$dealer) {
            return sfView::ERROR;
        }

        $models = AgreementModelTable::getInstance()
            ->createQuery('am')
            ->select('am.created_at as amUpdatedAt, amr.created_at as amrUpdatedAt, am.status as amStatus, amr.status as amrStatus')
            ->leftJoin('am.Report amr')
            ->where('activity_id = ? and dealer_id = ?', array($this->activity->getId(), $dealer->getId()))
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $activityStatisticPeriods = $this->activity->getActivityStatisticPeriodsInfo();

        $quarter = 0;
        foreach ($models as $model) {
            $year = intval(D::getYear($model['amUpdatedAt']));
            $q = intval(D::getQuarter($model['amUpdatedAt']));

            if (count($activityStatisticPeriods) && isset($activityStatisticPeriods[$year]) && !in_array($q, $activityStatisticPeriods[$year])) {
                continue;
            }

            $quarter = D::getQuarter(D::calcQuarterData($model['amUpdatedAt']));
        }

        $this->current_q = $quarter != 0 ? $quarter : $request->getParameter('current_q', $this->current_q != 0 ? $this->current_q : D::getQuarter(time()) );

        $this->preCheckStatisticStatus($request);

        $this->setTemplate('statistic');
    }

    private function checkAllowToEdit()
    {
        $this->allow_to_edit_fields = $this->allow_to_edit = true;
        $this->allow_to_cancel = false;
        $this->disable_importer = false;

        if ($this->current_q != 0) {
            /*Если статистика не заполнена и нет данных в БД, разрешаем заполнение статистики и ее сохранение*/
            if ( ActivityFieldsValuesTable::getInstance()->createQuery('afv')
                    ->innerJoin('afv.ActivityFields af')
                    ->where('afv.dealer_id = ? and afv.q = ? and afv.year = ?', array($this->getUser()->getAuthUser()->getDealer()->getId(), $this->current_q, $this->current_year))
                    ->andWhere('af.activity_id = ?', $this->activity->getId())
                    ->count() == 0
            ) {
                $this->allow_to_cancel = false;
                $this->allow_to_edit_fields = $this->allow_to_edit = true;
            } else {
                $q = 'q' . $this->current_q;
                $stat_item = ActivityDealerStaticticStatusTable::getInstance()->createQuery()
                    ->select('ignore_q1_statistic, ignore_q2_statistic, ignore_q3_statistic, ignore_q4_statistic')
                    ->where('dealer_id = ? and activity_id = ? and stat_type = ? and ' . $q . ' != ? and year = ?',
                        array
                        (
                            $this->getUser()->getAuthUser()->getDealer()->getId(),
                            $this->activity->getId(),
                            Activity::ACTIVITY_STATISTIC_TYPE_SIMPLE,
                            0,
                            $this->year
                        )
                    )
                    ->fetchOne();

                if ($stat_item && !$stat_item->getIgnoreStatisticStatus($this->current_q)) {
                    $this->allow_to_edit_fields = $this->allow_to_edit = false;
                }

                if ($this->getUser()->getAuthUser()->isSuperAdmin() && !$this->allow_to_edit_fields) {
                    $this->allow_to_cancel = $this->allow_to_edit = true;
                }

            }
        }
    }

    public function executeCancelStatisticData(sfWebRequest $request)
    {
        $q = 'q' . $request->getParameter('quarter');
        $activity = $request->getParameter('activity');
        $year = $request->getParameter('year');

        if (is_null($year)) {
            $year = D::getYear(date('Y-m-d'));
        }

        $stat_item = ActivityDealerStaticticStatusTable::getInstance()
            ->createQuery()
            ->where('dealer_id = ? and activity_id = ? and stat_type = ? and ' . $q . ' != ? and year = ?',
                array
                (
                    $this->getUser()->getAuthUser()->getDealer()->getId(),
                    $activity,
                    Activity::ACTIVITY_STATISTIC_TYPE_SIMPLE,
                    0,
                    $year
                )
            )
            ->fetchOne();

        if ($stat_item) {
            $stat_item->setComplete(false);
            $stat_item->setIgnoreStatisticStatus(true, $request->getParameter('quarter'));
            $stat_item->save();
        }

        return $this->sendJson(array('success' => true));
    }

    function executeExtendedStatistic(sfWebRequest $request)
    {
        $this->preCheckStatisticStatus($request);

        $this->outputModelsQuarters($request);
        $this->outputFilterByYear();
        $this->outputFilterByQuarter();

        $this->activity = $this->getActivity($request);

        $this->bindedConcept = ActivityExtendedStatisticFieldsTable::getConceptInfoByUserActivity($this->getUser(), $this->activity, $this->current_year, $this->current_q);

        if ($this->activity->hasStatisticByBlocks()) {
            $this->setTemplate('extendedStatisticByBlocks');
        } else if ($this->activity->isActivityStatisticHasSteps()) {
            $this->setTemplate('extendedStatisticBySteps');
        }
    }

    function executeChangeStats(sfWebRequest $request)
    {
        $fields = $request->getParameter('data');

        foreach ($fields as $field) {
            $row = ActivityFieldsValuesTable::getInstance()->find($field['id']);

            if ($row) {
                $row->setVal($field['value']);
                $row->setUpdatedAt(date('Y-m-d H:i:s'));
                $row->save();
            }
        }

        return $this->sendJson(array('success' => true));
    }

    function executeCheckAllowToEditCancelStatData(sfWebRequest $request)
    {
        return $this->sendJson($this->checkAllowToEditExtendedStat($request));
    }

    private function checkAllowToEditExtendedStat(sfWebRequest $request)
    {
        $item_exit = ActivityDealerStaticticStatusTable::getInstance()->createQuery()->where('dealer_id = ? and concept_id = ?',
            array
            (
                $this->getUser()->getAuthUser()->getDealer()->getId(),
                $request->getParameter('concept_id'),
            )
        )->fetchOne();

        $allow_to_edit = true;

        if ($item_exit) {
            if (is_null($this->current_q)) {
                $this->current_q = $this->getUser()->getAttribute('current_q', 0, self::FILTER_Q_NAMESPACE);
            }

            $allow_to_edit = !$item_exit->getIgnoreStatisticStatus($this->current_q) ? false : true;
            $allow_to_cancel = $this->getUser()->getAuthUser()->isSuperAdmin() && !$allow_to_edit;
        } else {
            $allow_to_cancel = false;
        }

        return array('allow_to_edit' => $allow_to_edit, 'allow_to_cancel' => $allow_to_cancel);
    }

    function executeChangeExtendedStats(sfRequest $request)
    {
        $this->activity = $this->getActivity($request);

        $result = ActivityExtendedStatisticFields::saveData($request, $this->getUser(), $_FILES, $this->activity);

        return $this->sendJson($result, 'activity_extended_statistic.onSaveDataCompleted');
    }

    function executeChangeExtendedStatsToImporter(sfWebRequest $request)
    {
        $this->activity = $this->getActivity($request);
        $result = ActivityExtendedStatisticFields::saveData($request, $this->getUser(), $_FILES, $this->activity);

        return $this->sendJson($result, 'activity_extended_statistic.onSaveDataCompleted');
    }

    function executeCancelExtendedStatisticData(sfWebRequest $request)
    {
        $stat_item = ActivityDealerStaticticStatusTable::getInstance()->createQuery()->where('dealer_id = ? and concept_id = ?',
            array
            (
                $this->getUser()->getAuthUser()->getDealer()->getId(),
                $request->getParameter('concept_id'),
            )
        )->fetchOne();

        if ($stat_item) {
            if (is_null($this->current_q)) {
                $this->current_q = $this->getUser()->getAttribute('current_q', 0, self::FILTER_Q_NAMESPACE);
            }

            $stat_item->setIgnoreStatisticStatus(true, $this->current_q);
            $stat_item->save();

            //Если передаем при отмене индекс шага, отменяем принятые данные
            $step_status_id = intval($request->getParameter('step_status_id'));
            if (!empty($step_status_id) && $step_status_id > 0) {
                $step_status = ActivityExtendedStatisticStepStatusTable::getInstance()->createQuery()->where('id = ?', $step_status_id)->fetchOne();
                if ($step_status) {
                    $step_status->setStatus(false);
                    $step_status->save();
                }
            }

            return $this->sendJson(array('success' => true, 'step_id' => intval($request->getParameter('step_id', 0))));
        }

        return $this->sendJson(array('success' => false));
    }

    function executeStatisticInfo(sfWebRequest $request)
    {
        $this->outputActivityFilter($request);
        $this->outputActivityQuarterFilter($request);

        $this->builder = new ActivityStatisticFieldsBuilder(
            array
            (
                'year' => date('Y'),
                'quarter' => $this->activityQuarter
            ),
            $this->activity,
            $this->getUser());
    }

    public function executeStatisticInfoExport(sfWebRequest $request) {
        $this->outputActivityFilter($request);
        $this->outputActivityQuarterFilter($request);

        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle(Utils::trim_text('Экспорт данных', 30));

        $builder = new ActivityStatisticFieldsBuilder(
            array
            (
                'year' => date('Y'),
                'quarter' => $this->activityQuarter
            ),
            $this->activity,
            $this->getUser());
        $stat = $builder->getStat();

        $headers = array();
        $headers[] = "№";
        $headers[] = "Дилер";

        $parent_headers = array();
        $parent_fields_list = array();

        foreach ($stat['fields'] as $field_item) {
            $parent = $field_item->getActivityVideoRecordsStatisticsHeaders();
            if ($parent) {
                if (!array_key_exists($parent->getId(), $parent_headers)) {
                    $parent_headers[$parent->getId()] = $parent->getHeader();
                }

                $parent_fields_list[$parent->getId()][] = $field_item->getId();
            }
        }

        $column = 2;
        $row = 1;
        foreach ($parent_headers as $key => $header) {
            $aSheet->mergeCells(ExcelUtils::mergeCell($column, $column + (count($parent_fields_list[$key]) - 1), $row));

            $aSheet->setCellValueByColumnAndRow($column, $row, $header);
            $column += count($parent_fields_list[$key]);
        }

        foreach ($stat['fields'] as $field_item) {
            $headers[] = $field_item->getName();
        }

        $headers[] = "Дата заполнения";
        $headers[] = "Дата обновления данных";

        $aSheet->fromArray($headers, null, 'A2');

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

        $parent_header_font = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '10',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $header_font_with_underline = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '10',
                'bold' => true,
                'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $header_font_without_underline = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '9',
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $normalFont = array(
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

        $last_letter = PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);

        $aSheet->getRowDimension('1')->setRowHeight(25);
        $aSheet->getRowDimension('2')->setRowHeight(35);

        $aSheet->getStyle('A1:' . $last_letter . '1')->applyFromArray($parent_header_font);
        $aSheet->getStyle('A1:'.$last_letter.'1')->getAlignment()->setWrapText(true);

        $aSheet->getStyle('A2:' . $last_letter . '2')->applyFromArray($header_font);
        $aSheet->getStyle('A2:'.$last_letter.'2')->getAlignment()->setWrapText(true);

        $cellIterator = $aSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $aSheet->getColumnDimension($cell->getColumn())->setWidth(25);
        }

        $aSheet->getColumnDimension('A')->setWidth(10);

        $row = 2;

        $column = 0;
        foreach ($stat[ 'dealers' ] as $qKey => $quarters) {
            if ($qKey != 0) {
                $row += 2;

                $aSheet->setCellValueByColumnAndRow($column, $row, sprintf('Квартал: %s', $qKey));
                $aSheet->getStyle('A'.$row.':' . 'A'.$row++)->applyFromArray($header_font_with_underline);
            }

            foreach ($quarters as $dKey => $dealers) {
                $dealer = $dealers[ 'dealer' ];

                $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer->getShortNumber());
                $aSheet->getStyle('A0:' . 'A'.$row)->applyFromArray($header_font_without_underline);

                $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer->getName());
                $aSheet->getStyle('A0:' . 'A'.$row)->applyFromArray($header_font_without_underline);

                foreach ($parent_fields_list as $pKey => $parent_fields) {
                    foreach ($stat['fields'] as $field) {
                        if (in_array($field->getId(), $parent_fields)) {
                            $value = '';
                            if (isset($dealers['values']['item'][$field->getId()])) {
                                $item = $dealers['values']['item'][$field->getId()];
                                $itemField = ActivityFieldsTable::getInstance()->createQuery()->select('type, content')->where('id = ?', $item['field_id'])->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

                                $value = $item['val'];
                                if ($itemField['content'] == "price") {
                                    $value = number_format(floatval($value), 0, '.', ' ') . ' руб.';
                                } else if ($itemField['type'] == ActivityVideoRecordsStatisticsHeadersFields::FIELD_TYPE_FILE) {
                                    $value = $item['val'];
                                }
                            }

                            $aSheet->setCellValueByColumnAndRow($column++, $row, $value);
                        }
                    }
                }

                $aSheet->setCellValueByColumnAndRow($column++, $row, $dealers[ 'update_date' ]);
                $aSheet->setCellValueByColumnAndRow($column++, $row, $dealers[ 'last_update_date' ]);

                $aSheet->getStyle('A'.$row.':' . $last_letter.$row)->applyFromArray($normalFont);

                $column = 0;
                $row++;
            }
        }

        $aSheet->freezePane('C3');

        $file_name = Utils::makeSlugs($this->activity->getName()).'.xlsx';

        $objWriter = PHPExcel_IOFactory::createWriter($pExcel, "Excel2007");
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/'.$file_name);

        return $this->sendJson(array("success" => true, 'file_name' => $file_name));
    }

    function executeBindToConcept(sfWebRequest $request)
    {
        $this->outputModelsQuarters($request);
        $this->outputFilterByYear();
        $this->outputFilterByQuarter();

        $this->concept = $request->getParameter('concept');
        $this->activity = ActivityTable::getInstance()->find($request->getParameter('activity'));

        /*$this->bindedConcept = ActivityExtendedStatisticFieldsTable::getConceptInfoByUserActivity($this->getUser());
        if ($this->bindedConcept) {
            $this->active_concept = $this->bindedConcept->getConceptId();
        }*/
    }

    function getActivityFilter()
    {
        $default = $this->getUser()->getAttribute('activity', 0, self::FILTER_NAMESPACE);
        $activity = $this->getRequestParameter('activity', $default);

        // set to default quarter
        if ($default != $activity) {
            $this->getUser()->setAttribute('activityQuarter', 0, self::FILTER_NAMESPACE);
        }

        $this->getUser()->setAttribute('activity', $activity, self::FILTER_NAMESPACE);

        return $activity;
    }

    function getActivityQuarterFilter()
    {
        $default = $this->getUser()->getAttribute('activityQuarter', 0, self::FILTER_NAMESPACE);
        $q = $this->getRequestParameter('activityQuarter', $default);

        $this->getUser()->setAttribute('activityQuarter', $q, self::FILTER_NAMESPACE);

        return $q;
    }

    function outputActivityFilter(sfWebRequest $request)
    {
        $this->year = D::getBudgetYear($request);

        $this->activity = $this->getActivityFilter();
        if ($this->activity != 0)
            $this->activity = ActivityTable::getInstance()->find($this->activity);
        else
            $this->activity = null;

    }

    function outputActivityQuarterFilter()
    {
        $this->activityQuarter = $this->getActivityQuarterFilter();
    }

    function executeGetHolidaysDays(sfWebRequest $request)
    {
        $currDate = date("Y-m", $request->getParameter('currentDate'));
        $days = array();
        $currDay = 0;

        $dates = CalendarTable::getInstance()->createQuery()->where('start_date LIKE ?', $currDate . '%')->execute();
        foreach ($dates as $date) {
            $firstDay = date("d", strtotime($date->getStartDate()));
            $endDay = date("d", strtotime($date->getEndDate()));

            $currDay = intval($firstDay);
            $days[] = date("Ymd", strtotime($date->getStartDate()));
            $dayIndex = 1;

            while ($currDay < $endDay) {
                $currDay++;
                $days[] = date("Ymd", strtotime('+' . $dayIndex . ' days ' . $date->getStartDate()));

                $dayIndex++;
            }
        }

        return $this->sendJson(array('days' => $days));
    }

    /**
     * Show activities list for Service Clinic
     * @param sfWebRequest $request
     */
    public function executeServiceClinicStatsShow(sfWebRequest $request)
    {
        $builder = new ActivityExtendedStatisticsBuilder();
        $builder->buildActivitiesStats();

        $this->stats = $builder->getActivitiesStats();
    }

    /**
     * Export data with selected params
     * @param sfWebRequest $request
     * @return NONE
     */
    public function executeServiceClinicStatsExport(sfWebRequest $request)
    {
        $url = ActivityExtendedStatisticsBuilder::makeExportFile($request);
        echo $url;

        return sfView::NONE;
    }

    public function executeDownloadFile(sfWebRequest $request)
    {
        $id = $request->getParameter('id');

        $file = ActivityFileTable::getInstance()->find($id);
        if ($file) {
            $filePath = sfConfig::get('app_activities_upload_path') . '/file/' . $file->getFile();
            if (!F::downloadFile($filePath, $file->getFile())) {
                $this->getResponse()->setContentType('application/json');
                $this->getResponse()->setContent(json_encode(array('success' => false, 'message' => '���� �� ������')));
            }
        }

        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent(json_encode(array('success' => false, 'message' => '���� �� ������')));

        return sfView::NONE;
    }

    public function executeDownloadFileField(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        $type = $request->getParameter('type');

        if (!empty($type) && $type == 'extended') {
            $field_value = ActivityExtendedStatisticFieldsDataTable::getInstance()->find($id);
            $func_value_name = 'getValue';
        } else {
            $field_value = ActivityFieldsValuesTable::getInstance()->find($id);
            $func_value_name = 'getVal';
        }

        if ($field_value) {
            $filePath = sfConfig::get('app_activities_upload_path') . '/module/statistics/' . $field_value->$func_value_name();
            $file_name = $field_value->$func_value_name();

            if (!F::downloadFile($filePath, $file_name)) {
                $this->getResponse()->setContentType('application/json');
                $this->getResponse()->setContent(json_encode(array('success' => false, 'message' => '���� �� ������')));
            }
        }

        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent(json_encode(array('success' => false, 'message' => '���� �� ������')));

        return sfView::NONE;
    }

    public function executeActivityVideoRecordStatistic(sfWebRequest $request)
    {
        $this->activity = $this->getActivity($request);
    }

    public function executeOnAddNewGroupFields(sfWebRequest $request)
    {
        $activity = $this->getActivity($request);
        $fields = ActivityFieldsTable::getInstance()
            ->createQuery()
            ->where('parent_header_id = ? and group_id = ? and owner = ?', array($request->getParameter('header_id'), $request->getParameter('group_id'), 0))
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $hash_id = md5(time());
        foreach ($fields as $field) {
            unset($field['id']);

            $newField = new ActivityFields();

            $field['owner'] = $this->getUser()->getAuthUser()->getDealer()->getId();
            $field['hash_id'] = $hash_id;

            $newField->setArray($field);
            $newField->save();
        }

        $this->activity = $activity;
    }

    public function executeOnSaveSimpleActivityStatistic(sfWebRequest $request)
    {
        return $this->sendJson($this->saveVideoRecordStatisticData($request, false), 'activity_simple_statistic.onSaveDataCompleted');
    }

    public function executeOnSaveVideoRecordStatisticData(sfWebRequest $request)
    {
        return $this->sendJson($this->saveVideoRecordStatisticData($request, false), 'activity_video_record_statistic.onSaveDataCompleted');
    }

    public function executeOnSaveImporterVideoRecordStatisticData(sfWebRequest $request)
    {
        return $this->sendJson($this->saveVideoRecordStatisticData($request, true), 'activity_video_record_statistic.onSaveImporterDataCompleted');
    }

    /**
     * Проверка статистики активности пользователем
     * @param sfWebRequest $request
     * @throws ActionDoesNotMatchModuleException
     * @throws sfStopException
     */
    public function executeStatisticPreCheckByUser(sfWebRequest $request) {

        $this->outputFilterByYear();
        $this->outputFilterByQuarter();

        $this->outputModelsQuarters($request);

        //Переключение пользователя на дилиера с письма
        if ($this->getUser()->isManager() || $this->getUser()->isImporter()) {
            $dealer = DealerTable::getInstance()->find($request->getParameter('dealer'));
            $this->forward404Unless($dealer);


            $dealer_user = DealerUserTable::getInstance()->findOneByUserId($this->getUser()->getAuthUser()->getId());

            if (!$dealer_user) {
                $dealer_user = new DealerUser();
                $dealer_user->setUser($this->getUser()->getAuthUser());
                $dealer_user->setManager(true);
            }

            $dealer_user->setDealer($dealer);
            $dealer_user->save();
        }

        $this->preCheckStatisticStatus($request);

        $this->setTemplate('statistic');
    }

    /**
     * Принять данные по статистике от делира
     * @param sfWebRequest $request
     * @return array
     */
    public function executeAcceptStatisticDataByUser(sfWebRequest $request) {
        $this->getActivityAndQuarterAndYear($request);

        return $this->sendJson(array('success' => ActivityStatisticCheckFactory::getInstance($this->activity)->accept($this->getUser()->getAuthUser(), $this->current_q, $this->current_year)));
    }

    /**
     * Отклонить данные по статистике от дилера
     * @param sfWebRequest $request
     * @return array
     */
    public function executeCancelStatisticDataByUser(sfWebRequest $request) {
        $this->getActivityAndQuarterAndYear($request);

        return $this->sendJson(array('success' => ActivityStatisticCheckFactory::getInstance($this->activity)->cancel($this->getUser()->getAuthUser(), $this->current_q, $this->current_year)));
    }

    private function getActivityAndQuarterAndYear(sfWebRequest $request) {
        $this->outputFilterByYear();
        $this->outputFilterByQuarter();

        $this->activity = $this->getActivity($request);
    }

    /**
     * Проверка статистики активности на возможность согалосвоания / отклонения для администраторов
     * Возможность редактироваия данных для дилеров если статистика активности не на проверке у администрации
     * @param sfWebRequest $request
     */
    private function preCheckStatisticStatus(sfWebRequest $request) {
        $this->getActivityAndQuarterAndYear($request);

        $this->pre_check_statistic = ActivityStatisticPreCheckAbstract::CHECK_STATUS_NONE;

        //Делаем проверку на доступную статистику в активности и проверку на обязательную проверку администрацией
        $this->pre_check_statistic = ActivityStatisticCheckFactory::getInstance($this->activity)->status($this->getUser()->getAuthUser(), $this->current_q, $this->current_year);

        //Проверка на корректность заполненных данных, только для администрации
        if ($this->pre_check_statistic == ActivityStatisticPreCheckAbstract::CHECK_STATUS_IN_PROGRESS) {

            //Запрещаем любые изменения данных в статистике, только возможность согласовать и отклонить статистику
            $this->allow_to_edit = false;
            $this->allow_to_edit_fields = false;
            $this->allow_to_cancel = false;

            $this->disable_importer = true;
        } else {
            $this->checkAllowToEdit();

            if ($this->activity->isVideoRecordStatisticsActive()) {
                $statistic = $this->activity->getActivityVideoStatistics()->getFirst();

                if ($statistic && $statistic->getNotUsingImporter()) {
                    $this->allow_to_edit = true;
                    $this->allow_to_edit_fields = true;
                    $this->allow_to_cancel = false;
                    $this->disable_importer = true;
                }
            }
        }
    }

    private function saveVideoRecordStatisticData(sfWebRequest $request, $to_importer = false)
    {
        $this->activity = $this->getActivity($request);

        $result = ActivityStatisticCheckFactory::getInstance($this->activity)->save($request, $this->getUser(), $_FILES, $to_importer, $this->activity);
        //$result = ActivityFields::saveData($request, $this->getUser(), $_FILES, $to_importer, $this->activity);

        $result['hide_data'] = $to_importer;

        return $result;
    }

    public function executeOnDeleteVideoRecordField(sfWebRequest $request)
    {
        $field = ActivityFieldsTable::getInstance()->createQuery()->where('id = ? and owner = ?', array($request->getParameter('field_id'), $this->getUser()->getAuthUser()->getDealer()->getId()))->fetchOne();
        $fields_by_hash = ActivityFieldsTable::getInstance()->createQuery()->where('hash_id = ?', $field->getHashId())->execute();

        foreach ($fields_by_hash as $field) {
            $field_data = ActivityFieldsValuesTable::getInstance()->createQuery()->where('field_id = ? and dealer_id = ?', array($field->getId(), $this->getUser()->getAuthUser()->getDealer()->getId()))->fetchOne();
            if ($field_data) {
                $field_data->delete();
            }

            $field->delete();
        }
        $this->activity = $this->getActivity($request);

        $this->outputModelsQuarters($request);
        $this->outputFilterByYear();
        $this->outputFilterByQuarter();

        //$this->checkAllowToEdit();
        $this->preCheckStatisticStatus($request);
    }

    /*Activity efficiency*/
    public function executeEfficiency(sfWebRequest $request)
    {
        $this->activity = $this->getActivity($request);

        $efficiency = new ActivityCalculateEfficiencyUtils($this->activity, $this->getUser()->getAuthUser());
        $this->efficiency_result = $efficiency->getResult();

        $this->outputModelsQuarters($request);
        $this->outputFilterByYear();
        $this->outputFilterByQuarter();
    }

    public function executeEfficiencyInfo(sfWebRequest $request)
    {
        $this->outputActivityFilter($request);
        $this->outputActivityQuarterFilter($request);

        $this->builder = new ActivitiesEfficiencyDealersStatistic(
            array
            (
                'year' => date('Y'),
                'quarter' => $this->activityQuarter
            ),
            $this->activity,
            $this->getUser()->getAuthUser());
    }

    public function executeCheckActivityEfficiency(sfWebRequest $request)
    {
        $activity = $this->getActivity($request);

        $efficiency = new ActivityCalculateEfficiencyUtils($activity, $this->getUser()->getAuthUser());
        $efficiency_result = $efficiency->getResult();

        $is_effective = true;
        foreach ($efficiency_result as $key => $data) {
            if ($data['formula']->isEfficiencyFormula()) {
                if (is_null($data['value']) || $data['value'] <= 0 || empty($data['value'])) {
                    $is_effective = false;
                    break;
                }
            }
        }

        return $this->sendJson(array('is_effective' => $is_effective));
    }

    public function executeExportActivityEfficiencyData(sfWebRequest $request)
    {
        $this->activity = $this->getActivity($request);

        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle(Utils::trim_text('Экспорт данных', 30));

        $builder = new ActivitiesEfficiencyDealersStatistic(
            array
            (
                'year' => date('Y'),
                'quarter' => $this->activityQuarter
            ),
            $this->activity,
            $this->getUser()->getAuthUser());

        $builder->build();
        $results = $builder->getResults();

        $headers = array();
        $headers[] = "Дилер";

        foreach ($results['formulas'] as $formula) {
            $headers[] = $formula->getName();
        }

        $headers[] = "Эффективность";

        $boldLeftFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
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
        $center = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
        );

        $aSheet->getStyle('A1:G1')->applyFromArray($boldLeftFont);
        $aSheet->getStyle('A4:M4')->applyFromArray($boldFont);
        $aSheet->getStyle('B:M')->applyFromArray($center);

        $column = 0;
        $tCount = 1;
        foreach ($headers as $head) {
            $aSheet->setCellValueByColumnAndRow($column++, 1, $head);
            $tCount++;
        }

        $aSheet->getRowDimension('1')->setRowHeight(35);
        $aSheet->getStyle('A1:M1')->getAlignment()->setWrapText(true);

        $cellIterator = $aSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $aSheet->getColumnDimension($cell->getColumn())->setWidth(35);
        }

        $fillColor = 'ececec';
        $row = 2;

        foreach ($results['results'] as $key => $result_data) {
            $column = 0;
            $aSheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($boldLeftFont);
            $aSheet->getRowDimension($row)->setRowHeight(25);

            $dealer = DealerTable::getInstance()->find($key);

            if ($row % 2 == 0) {
                $aSheet->getStyle('A' . $row . ':M' . $row)
                    ->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB($fillColor);
            }

            $aSheet->setCellValueByColumnAndRow($column++, $row, sprintf('[%s] %s', $dealer->getShortNumber(), $dealer->getName()));
            $efficiency = false;
            $efficiency_ind = 0;
            foreach ($results['formulas'] as $formula) {
                if ($efficiency_ind == 0) {
                    $efficiency = $result_data[$formula->getId()] > 0 ? true : false;
                    $efficiency_ind++;
                }

                $aSheet->setCellValueByColumnAndRow($column++, $row, Utils::numberFormat($result_data[$formula->getId()]));
            }

            if ($efficiency) {
                Utils::drawExcelImage('efficiency/hand_up.png', 'F' . $row, $pExcel, 50);
            } else {
                Utils::drawExcelImage('efficiency/hand_down.png', 'F' . $row, $pExcel, 50);
            }

            $row++;
        }

        $save_file_name = 'activity_efficiency_data.xls';
        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/' . $save_file_name);

        return $this->sendJson(array('success' => true, 'file_url' => sfConfig::get('site_url') . '/uploads/' . $save_file_name));
    }

    public function executeDealersWorkStatistics(sfWebRequest $request) {
        $this->quarters = range(1, 4);

        //Заполняем список активностей с учетом типа статистики привязанной к активности
        $activities_list = array('simple' => array('label' => 'Обычная статистика', 'activities' => array()), 'service_clinic' => array('label' => 'Service Clinic', 'activities' => array()));
        $activities_ids = array_map(function ( $item ) {
            return $item[ 'activity_id' ];
        }, ActivityExtendedStatisticStepsTable::getInstance()->createQuery()->select('activity_id')->groupBy('activity_id')->execute(array(), Doctrine_Core::HYDRATE_ARRAY));

        $service_clinic_activities = ActivityTable::getInstance()->createQuery()->whereIn('id', $activities_ids)->orderBy('position ASC')->execute();
        foreach ($service_clinic_activities as $activity) {
            $activities_list['service_clinic']['activities'][] = $activity;
        }

        //Обычный тип статистики
        $activities_ids = array_map(function($item) {
            return $item['activity_id'];
        }, ActivityFieldsTable::getInstance()->createQuery()->select('activity_id')->groupBy('activity_id')->execute(array(), Doctrine_Core::HYDRATE_ARRAY));

        $simple_activities_list = ActivityTable::getInstance()->createQuery()->whereIn('id', $activities_ids)->andWhere('finished = ?', false)->orderBy('position ASC')->execute();
        foreach ($simple_activities_list as $activity) {
            $activities_list['simple']['activities'][] = $activity;
        }

        $this->consolidated_information_activities = $activities_list;

    }

    public function executeDealersExportWorkStatistics(sfWebRequest $request) {
        $year = $request->getParameter('year');
        $quarter = $request->getParameter('quarter');
        $mandatory_activity = $request->getParameter('mandatory_activity');
        $unload_type = $request->getParameter('unload_type');

        if ($unload_type == 'by-year') {
            $dealer_statistics_calc = new DealersStatisticsCalculateByYear($quarter, $mandatory_activity, $year);
            $dealer_statistics_calc->start();

            $file_url = $dealer_statistics_calc->getData();
        } else {
            $dealers_statistics_calc = new DealersStatisticsCalculate($quarter, $mandatory_activity, $year);
            $dealers_statistics_calc->start();

            $file_url = $dealers_statistics_calc->getData();
        }

        return $this->sendJson(array('success' => !empty($file_url) ? true : false, 'file_url' => $file_url));
    }

    /**
     * Экспорт данных по сервисным акциям по выбранной активности
     * @param sfWebRequest $request
     * @return string
     */
    public function executeDealersExportServicesDialogsStatistics(sfWebRequest $request) {
        $export_data = new DealersServicesActivitiesExport($request->getParameter('activity'));
        $result = $export_data->makeExport();

        return $this->sendJson(array('success' => !empty($result) ? true : false, 'file_url' => $result));
    }

    /**
     * Получить список шагов активности
     * @param sfWebRequest $request
     * @return array
     */
    public function executeActivityStepsList(sfWebRequest $request) {
        $steps = array();
        foreach (ActivityExtendedStatisticStepsTable::getInstance()->createQuery()->where('activity_id = ?', $request->getParameter('activity_id'))->orderBy('position ASC')->execute() as $step) {
            $steps[$step->getId()] = $step->getHeader();
        }

        return $this->sendJson(array('steps' => $steps));
    }

    /**
     * Экспорт данных по статистике по шагам
     * @param sfWebRequest $request
     */
    public function executeDealersExportServicesStepsStatistics(sfWebRequest  $request) {
        $export_data = new DealersActivitiesStatisticsByStepsExport($request->getParameter('activity'), $request->getParameter('q'), $request->getParameter('step'));
        $result = $export_data->makeExport();

        return $this->sendJson($result);
    }

    public function executeChangeStatusByUser(sfWebRequest $request) {
        $activity = $this->getActivity($request);
        $by_year = date('Y', strtotime($activity->getStartDate()));
        $by_quarter = $request->getParameter('quarter');

        $user = $this->getUser()->getAuthUser();

        $userDealer = $user->getDealerUsers()->getFirst();
        if ($activity && $userDealer) {
            if (ActivitiesStatusByUsersTable::getInstance()->createQuery()->where('activity_id = ? and dealer_id = ? and by_year = ? and by_quarter = ?',
                array
                (
                    $activity->getId(),
                    $userDealer->getDealerId(),
                    $by_year,
                    $by_quarter
                )
            )->count() == 0) {
                $item = new ActivitiesStatusByUsers();
                $item->setArray(array(
                    'user_id' => $user->getId(),
                    'activity_id' => $activity->getId(),
                    'dealer_id' => $userDealer->getDealerId(),
                    'by_year' => $by_year,
                    'by_quarter' => $by_quarter
                ));
                $item->save();
            } else {

                ActivitiesStatusByUsersTable::getInstance()->createQuery()->where('activity_id = ? and dealer_id = ? and by_year = ? and by_quarter = ?',
                    array
                    (
                        $activity->getId(),
                        $userDealer->getDealerId(),
                        $by_year,
                        $by_quarter
                    )
                )->delete()->execute();
            }
        }

        $this->redirect('/');
    }

    /**
     * Настройка параметров активности
     * @param sfWebRequest $request
     */
    public function executeSettings(sfWebRequest $request) {
        $this->outputActivity($request);

        $this->year = date('Y', strtotime($this->activity->getStartDate()));
    }

    /**
     * Созранить блок информации для дилера
     * @param sfWebRequest $request
     * @return string
     */
    public function executeSaveDealerInformationBlock(sfWebRequest $request) {
        $activity_id = $request->getParameter('activity_id');
        $dealer_id = $request->getParameter('dealer_id');
        $concept_id = $request->getParameter('concept_id', 0);
        $text = $request->getParameter('text');

        $information_block = ActivityDealerInformationBlocksTable::getInstance()->createQuery()->where('activity_id = ? and dealer_id = ? and concept_id = ?', array($activity_id, $dealer_id, $concept_id))->fetchOne();
        if (!$information_block) {

            $information_block = new ActivityDealerInformationBlocks();
            $information_block->setArray(array(
                'activity_id' => $activity_id,
                'dealer_id' => $dealer_id,
                'concept_id' => $concept_id
            ));
        }

        $information_block->setDescription($text);
        $information_block->save();

        return $this->sendJson(array('success' => true, 'text' => $text));
    }

    /**
     * Обработка события смены концепции
     * @param sfWebRequest $request
     */
    public function executeOnSpecialAgreementChangeConceptBindTargetAndStatistic(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $activity = $this->getActivity($request);
        $concept_id = $request->getParameter('concept_id');

        return $this->sendJson(array(
            'concept_target' => get_partial('special_agreement_concept_target', array('activity' => $activity, 'concept_id' => $concept_id)),
            'concept_statistic' => get_partial('special_agreement_concept_statistic', array('activity' => $activity, 'concept_id' => $concept_id))
        ));
    }
}
