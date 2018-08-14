<?php

/**
 * Description of ActivityExtendedStatisticsBuilder
 *
 *
 */
class ActivityExtendedStatisticsBuilder
{
    /** @var array */
    private $_stats = array();

    /** @var array */
    private $_statsDealers = array();

    /** @var array */
    private $_activitiesStats = array();

    /** @var array|null */
    private $_filter = null;

    function __construct($filter = null)
    {
        //$this->build();
        $this->_filter = !is_null($filter) ? $filter : array();

        $year_filter = array();
        if (isset($this->_filter['year']) && $this->_filter['year'] == -1) {
            $year_filter = array('year' => D::getYear(time()));
        }

        $this->_filter = array_merge($this->_filter, $year_filter);
    }

    /**
     *
     * @param $dealer
     * @param null $fieldType
     * @param int $limit
     * @param bool $asArray
     * @return mixed
     */
    private function getFieldsData($dealer, $fieldType = null, $limit = -1, $asArray = false)
    {
        $query = ActivityExtendedStatisticFieldsDataTable::getInstance()
            ->createQuery('f')
            ->select('created_at, concept_id')
            ->leftJoin('f.Field pf')
            //->where('f.value != ?', array(''))
            ->andWhere('f.dealer_id = ?', $dealer['id'])
            ->orderBy('pf.position ASC');

        if (!is_null($fieldType)) {
            $query->andWhere('pf.value_type = ?', $fieldType);
        }

        if (!empty($this->_filter) && isset($this->_filter['activity'])) {
            $query->andWhere('pf.activity_id = ?', $this->_filter['activity']);
        }

        /*if (!empty($this->_filter) && isset($this->_filter['quarter'])) {
            $query->andWhere('quarter(f.updated_at) = ?', $this->_filter['quarter']);
        }*/

        if ($asArray && $limit != -1) {
            return $query->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
        }

        return $query->execute();
    }

    private function isDealerRequiredFieldsFilled($dealer, $concept) {

        $query = ActivityExtendedStatisticFieldsDataTable::getInstance()
            ->createQuery('f')
            ->leftJoin('f.Field pf')
            //->where('f.value != ?', array(''))
            ->andWhere('f.dealer_id = ? and f.concept_id = ?', array($dealer['id'], $concept->getId()))
            ->andWhere('pf.required = ? and pf.value_type != ? and pf.value_type != ? and pf.value_type != ?', array(true, 'date', 'calc', 'text'))
            ->orderBy('pf.position ASC');

        if (!empty($this->_filter) && isset($this->_filter['activity'])) {
            $query->andWhere('pf.activity_id = ?', $this->_filter['activity']);
        }

        $fields = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        $all_filled = true;
        foreach ($fields as $field) {

            if (empty($field['value'])) {
                $all_filled = false;
            }
        }

        return $all_filled;
    }

    public function build()
    {
        $result = array();

        return $result;
        $conceptInd = 1;
        $dealers = DealerTable::getDealersList()->select('id, name, number')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $query = ActivityExtendedStatisticFieldsTable::getInstance()
            ->createQuery()
            ->select('id, header')
            ->orderBy('position ASC');

        if (!empty($this->_filter) && isset($this->_filter['activity'])) {
            $query->andWhere('activity_id = ?', $this->_filter['activity']);
        }

        $fields = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        foreach ($dealers as $dealer) {
            $field = $this->getFieldsData($dealer, 'date', 1, true);
            if (!$field) {
                continue;
            }

            $conceptQuery = AgreementModelTable::getInstance()
                ->createQuery('am')
                ->innerJoin('am.AgreementModelSettings ams')
                ->where('model_type_id = ?', 10)
                //->andWhere('ams.certificate_date_to >= ?', date('Y-m-d'))
                ->andWhere('am.dealer_id = ?', $dealer['id'])
                ->orderBy('ams.id ASC');

            if (!empty($this->_filter) && isset($this->_filter['activity'])) {
                $conceptQuery->andWhere('am.activity_id = ?', $this->_filter['activity']);
            }

            $concepts = $conceptQuery->execute();
            foreach ($concepts as $concept) {
                if (!$concept->isModelCompleted()) {
                    continue;
                }

                if (!$this->isDealerRequiredFieldsFilled($dealer, $concept)) {
                    continue;
                }

                /** @var  $concept_work_date */
                $concept_work_date = $concept->getModelQuarterDate();

                /** @var  $complete_quarter */
                $complete_quarter = D::getQuarter($concept_work_date);

                /** @var  $complete_year */
                $complete_year = D::getYear($concept_work_date);

                /** @var  $createdAt */
                $createdAt = explode(' ', $field['created_at']);

                /** @var  $date */
                $date = $concept->getAgreementModelSettings()->getCertificateDateTo();

                /** @var  $certificate_date_to_year */
                $certificate_date_to_year = D::getYear($date);

                /** @var  $complete_in_work_year */
                $complete_in_work_year = $complete_year == $certificate_date_to_year ? true : false;

                if (isset($this->_filter['quarter']) && $complete_quarter != $this->_filter['quarter'] && $this->_filter['quarter'] != -1) {
                    continue;
                }

                if (isset($this->_filter['year']) && $complete_year != $this->_filter['year']) {
                    continue;
                }

                foreach ($fields as $field_item) {
                    $conceptId = $concept ? 'concept-' . $concept->getId() : $conceptInd++;

                    $item = $this->queryFilledData($concept->getId(), $dealer['id'], $field_item['id']);
                    if ($item) {
                        $itemValue = $item->getField()->getValueType() == ActivityExtendedStatisticFields::FIELD_TYPE_CALC ?
                            $item->getField()->calculateValue($item->getDealerId(), null, $concept->getId()) :
                            $item->getValue();

                        $tempVal = $item->getValue();
                        if (empty($tempVal) && $item->getField()->getValueType() != ActivityExtendedStatisticFields::FIELD_TYPE_CALC) {
                            $itemValue = 0;
                        }

                        $result[$conceptId][$item->getFieldId()]['data'] =
                            array(
                                'value' => $itemValue,
                                'name' => $item->getField()->getHeader(),
                                'dealerId' => $dealer['id'],
                                'dealerName' => $item->getDealer()->getName(),
                                'dealerNumber' => $item->getDealer()->getNumber(),
                                'concept' => isset($date) ? $date : '',
                                'complete_year' => $complete_year,
                                'complete_quarter' => $complete_quarter,
                                'complete_in_work_year' => $complete_in_work_year
                            );
                    } else {
                        $result[$conceptId][$field_item['id']]['data'] =
                            array(
                                'value' => '',
                                'name' => $field_item['header'],
                                'dealerId' => $dealer['id'],
                                'dealerName' => $dealer['name'],
                                'dealerNumber' => $dealer['number'],
                                'concept' => isset($date) ? $date : '',
                                'complete_year' => $complete_year,
                                'complete_quarter' => $complete_quarter,
                                'complete_in_work_year' => $complete_in_work_year
                            );
                    }
                }
            }
        }

        $this->_stats = $result;
    }

    private function queryFilledData($conceptId, $dealerId, $field_item_id)
    {
        $query = ActivityExtendedStatisticFieldsDataTable::getInstance()
            ->createQuery('f')
            ->leftJoin('f.Field pf')
            ->andWhere('f.dealer_id = ? and f.concept_id = ?', array($dealerId, $conceptId))
            ->andWhere('field_id = ?', $field_item_id)
            ->orderBy('pf.position ASC');

        if (!empty($this->_filter) && isset($this->_filter['activity'])) {
            $query->andWhere('pf.activity_id = ?', $this->_filter['activity']);
        }

        /*if ($count) {
            $query->andWhere('pf.value_type != ?', 'date')
                ->andWhere('f.value != ?', '');

            return $query->count();
        }*/

        return $query->fetchOne();
    }

    public function buildDealerStats()
    {
        $result = array();

        $query = ActivityExtendedStatisticFieldsDataTable::getInstance()
            ->createQuery('f')
            ->leftJoin('f.Field pf')
            ->orderBy('pf.position ASC');

        if (!empty($this->_filter) && isset($this->_filter['activity'])) {
            $query->andWhere('f.activity_id = ?', $this->_filter['activity']);
        }

        $items = $query->execute();
        foreach ($items as $item) {
            $val = $item->getValue();

            if (!array_key_exists($item->getDealerId(), $result)) {
                $result[$item->getDealerId()] = array
                (
                    'totalFillValues' => 0,
                    'dealerName' => $item->getDealer()->getName(),
                    'dealerNumber' => $item->getDealer()->getNumber(),
                    'percentOfComplete' => 0
                );
            }

            $result[$item->getDealerId()]['totalFillValues'] = !empty($val) && $val != 0 ?
                $result[$item->getDealerId()]['totalFillValues'] += 1 :
                $result[$item->getDealerId()]['totalFillValues'];
        }


        $fieldsCount = ActivityExtendedStatisticFieldsTable::getInstance()->createQuery()->count();
        foreach ($result as $dealerId => $data) {
            $result[$dealerId]['percentOfComplete'] = round($data['totalFillValues'] * 100 / $fieldsCount, 0);
        }

        $this->_statsDealers = $result;
    }

    public function buildActivitiesStats()
    {
        $result = array();

        $activities = ActivityTable::getInstance()->createQuery()->where('allow_extended_statistic = ? and allow_certificate = ?',
            array
            (
                true,
                true
            )
        )
            ->orderBy('id ASC')
            ->execute();

        foreach ($activities as $activity) {
            $quarters = ActivityQuartersTable::getInstance()->createQuery()->where('activity_id = ?', $activity->getId())->execute();

            $result[$activity->getId()]['activity'] = array('name' => $activity->getName(), 'id' => $activity->getId());
            foreach ($quarters as $quarter) {
                $fieldData = ActivityExtendedStatisticFieldsDataTable::getInstance()
                    ->createQuery('fd')
                    ->leftJoin('fd.Field f')
                    ->where('f.activity_id = ? and f.value_type = ? and quarter(fd.updated_at) = ?',
                        array
                        (
                            $activity->getId(),
                            ActivityExtendedStatisticFields::FIELD_TYPE_DATE,
                            $quarter->getQuarter()->getQuarter()
                        )
                    )
                    ->orderBy('updated_at ASC')
                    ->execute();

                $tHaveConcept = 0;
                $tDontHaveConcept = 0;
                foreach ($fieldData as $data) {
                    if ($data->getConceptId() != 0) {
                        $tHaveConcept++;
                    } else {
                        $tDontHaveConcept++;
                    }
                }

                $result[$activity->getId()]['data'][$quarter->getQuarter()->getQuarter()] =
                    array(
                        'data' =>
                            array(
                                'haveConcept' => $tHaveConcept,
                                'dontHaveConcept' => $tDontHaveConcept
                            )
                    );
            }

        }

        $this->_activitiesStats = $result;
    }

    public static function makeExportFile(sfWebRequest $request)
    {
        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle('Расширенная статистика');


        $headers = array();
        $headers[] = 'Дилер (название и номер)';
        $headers[] = 'Срок действия сертификата';

        $fields = ActivityExtendedStatisticFieldsTable::getInstance()->createQuery()->where('activity_id = ?', $request->getParameter('activity'))->orderBy('position ASC')->execute();
        foreach ($fields as $field) {
            $headers[] = $field->getHeader();
        }

        $boldFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => true
            )
        );
        $center = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $left = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $column = 0;
        foreach ($headers as $head) {
            $aSheet->setCellValueByColumnAndRow($column++, 1, $head);
        }

        $aSheet->getRowDimension('1')->setRowHeight(35);
        $aSheet->getStyle('A1:M1')->getAlignment()->setWrapText(true);

        $cellIterator = $aSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);

        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $aSheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        }

        $aSheet->getStyle('2:' . count($headers))->applyFromArray($center);

        $aSheet->getStyle('A1:A' . count($headers))->applyFromArray($left);
        $aSheet->getStyle('A1:B' . count($headers))->applyFromArray($boldFont);

        $stats = new ActivityExtendedStatisticsBuilder(
            array
            (
                'year' => $request->getParameter('year'),
                'quarter' => $request->getParameter('quarter'),
                'activity' => $request->getParameter('activity'),
            )
        );
        $stats->build();

        $fillColorRed = 'fbcbcb';

        $aSheet->getStyle('A1:Z1')->applyFromArray($center);
        $aSheet->getStyle('A1:Z1')
            ->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('d5fbd8');

        $row = 2;

        $completed_by_quarters = $stats->getStats();
        foreach ($completed_by_quarters as $conceptId => $stat) {
            $column = 0;

            foreach ($fields as $field) {
                if ($column == 0) {
                    $dealerId = $stat[$field->getId()]['data']['dealerId'];

                    $dealer = DealerTable::getInstance()->find($dealerId);
                    $dealerD = sprintf('[%s] %s', $dealer->getNumber(), $dealer->getName());

                    $aSheet->setCellValueByColumnAndRow($column++, $row, $dealerD);
                    if (is_numeric($conceptId)) {
                        $conceptData = 'Нет';
                    } else {
                        $conceptData = $stat[$field->getId()]['data']['concept'];
                    }
                    $aSheet->setCellValueByColumnAndRow($column++, $row, $conceptData);
                    $aSheet->setCellValueByColumnAndRow($column++, $row, $stat[$field->getId()]['data']['value']);
                } else if (array_key_exists($field->getId(), $stat)) {
                    $aSheet->setCellValueByColumnAndRow($column++, $row, $stat[$field->getId()]['data']['value']);
                }

                if (!$stat[$field->getId()]['data']['complete_in_work_year']) {
                    $aSheet->getStyle('A' . $row . ':Z' . $row)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB($fillColorRed);
                }
            }

            $aSheet->getRowDimension($row)->setRowHeight(25);
            $row++;
        }
        $aSheet->freezePane('C2');

        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/extended_stats.xls');

        return 'http://dm.vw-servicepool.ru/uploads/extended_stats.xls';
    }

    public function getStats()
    {
        return $this->_stats;
    }

    public function getDealerStats()
    {
        return $this->_statsDealers;
    }

    public function getActivitiesStats()
    {
        return $this->_activitiesStats;
    }
}
