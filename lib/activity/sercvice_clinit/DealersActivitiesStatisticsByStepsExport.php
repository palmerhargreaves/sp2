<?php
/**
 * Created by PhpStorm.
 * User: kostig51
 * Date: 25.11.2017
 * Time: 23:07
 */

class DealersActivitiesStatisticsByStepsExport
{
    private $_activity_id = 0;
    private $_quarter = 0;
    private $_steps_ids = array();

    public function __construct ( $activity_id, $quarter, $steps = null )
    {
        $this->_activity_id = $activity_id;
        $this->_quarter = $quarter;
        $this->_steps_ids = $steps;
    }

    protected static function conceptDate ( $concept_id, &$concept_dates )
    {
        if (!array_key_exists($concept_id, $concept_dates)) {
            $concept_date = AgreementModelSettingsTable::getInstance()->createQuery()->where('model_id = ?', $concept_id)->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

            $concept_dates[ $concept_id ] = '';
            if ($concept_date) {
                $concept_dates[ $concept_id ] = $concept_date[ 'certificate_date_to' ];
            }
        }
    }

    public function makeExport ()
    {
        if (!is_null($this->_steps_ids) && !empty($this->_steps_ids)) {
            $steps_ids[] = $this->_steps_ids;
        }

        $quarter = $this->_quarter;

        $quarters = !empty($quarter) ? array( $quarter ) : array( 1, 2, 3, 4 );

        //Получаем шаг(и) по активности
        $steps = empty($steps_ids) || is_null($steps_ids)
            ? ActivityExtendedStatisticStepsTable::getInstance()->createQuery()->where('activity_id = ?', $this->_activity_id)->orderBy('position ASC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY)
            : ActivityExtendedStatisticStepsTable::getInstance()->createQuery()->where('activity_id = ?', $this->_activity_id)
                ->andWhereIn('id', $this->_steps_ids)->orderBy('position ASC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $dealers = array();
        foreach (DealerTable::getInstance()->createQuery()->select('id, name, number')->where('status = ?', true)->orderBy('id ASC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY) as $dealer) {
            $dealers[ $dealer[ 'id' ] ] = $dealer;
        }

        $fields = array();
        $fields_headers = array();
        $concept_dates = array();

        $column = 4;
        $fields_position_in_cell = array();

        foreach ($steps as $step) {
            //Получаем список разделов привязанных к шагам
            $fields_items = ActivityExtendedStatisticFieldsTable::getInstance()->createQuery()->where('step_id = ?', $step[ 'id' ])->andWhere('value_type != ?', 'text')->orderBy('position ASC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            $sections = array();
            $sections_ids = array();
            $sections_fields = array();

            foreach ($fields_items as $field_item) {
                $sections[ $field_item[ 'parent_id' ] ][ 'fields_ids' ][] = $field_item[ 'id' ];
                $sections_ids[ $field_item[ 'parent_id' ] ] = $field_item[ 'parent_id' ];
            }

            $sections_list = ActivityExtendedStatisticSectionsTable::getInstance()->createQuery()->whereIn('id', $sections_ids)->orderBy('position ASC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
            foreach ($sections_list as $section) {
                $section_fields = $sections[ $section[ 'id' ] ];

                $section_fields_items = ActivityExtendedStatisticFieldsTable::getInstance()->createQuery()->whereIn('id', $section_fields[ 'fields_ids' ])->orderBy('position ASC')->execute();
                foreach ($section_fields_items as $section_field) {
                    $sections_fields[] = $section_field;
                }
            }

            foreach ($quarters as $quarter) {
                foreach ($dealers as $dealer_id => $dealer) {
                    foreach ($sections_fields as $field_item) {
                        $value = null;

                        //Заполняем список полей (заголовки)
                        if (!array_key_exists($field_item->getId(), $fields_headers)) {
                            $fields_headers[ $field_item->getId() ] = $field_item->getHeader();
                        }

                        //Заполняем таблицу по полям для корректного расположения на сетке
                        if (!array_key_exists($field_item->getId(), $fields_position_in_cell)) {
                            $fields_position_in_cell[ $field_item->getId() ] = $column++;
                        }

                        //Проверка на обычное поле или расчитываемое
                        if ($field_item->isCalcField()) {
                            $values = $field_item->calculateValueByStep($dealer[ 'id' ], $this->_activity_id, $step[ 'id' ], $quarter);

                            foreach ($values as $concept_id => $value) {
                                self::conceptDate($concept_id, $concept_dates);

                                $fields[ $quarter ][ $dealer[ 'id' ] ][ $concept_id ][ $field_item->getId() ] = array( 'field' => $field_item->getId(), 'value' => $value, 'is_calc' => true );
                            }
                        } else if ($field_item->getValueType() == 'file') {
                            $field_datas = ActivityExtendedStatisticFieldsDataTable::getInstance()->createQuery()
                                ->where('dealer_id = ? and activity_id = ? and field_id = ? and step_id = ? and quarter = ?', array($dealer['id'], $this->_activity_id, $field_item->getId(), $step['id'], $quarter))->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                            if (empty($field_datas)) {
                                $field_datas = ActivityExtendedStatisticStepValuesTable::getInstance()->createQuery()
                                    ->where('dealer_id = ? and activity_id = ? and field_id = ? and step_id = ? and quarter = ?', array($dealer['id'], $this->_activity_id, $field_item->getId(), $step['id'], $quarter))->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
                            }

                            //Если нет по шагу и кварталу, берем общие данные
                            if (count($field_datas) == 0) {
                                $field_datas = ActivityExtendedStatisticFieldsDataTable::getInstance()->createQuery()
                                    ->where('dealer_id = ? and activity_id = ? and field_id = ? and step_id = ? and quarter = ?', array($dealer['id'], $this->_activity_id, $field_item->getId(), 0, 0))->orderBy('id DESC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
                            }

                            foreach ($field_datas as $field_data) {
                                self::conceptDate($field_data['concept_id'], $concept_dates);

                                $fields[$quarter][$dealer['id']][$field_data['concept_id']][$field_item->getId()] = array('field' => $field_item->getId(), 'value' => $field_data['value'], 'is_calc' => false);
                            }
                        }
                        else {
                            $field_datas = ActivityExtendedStatisticFieldsDataTable::getInstance()->createQuery()
                                ->where('dealer_id = ? and activity_id = ? and field_id = ? and step_id = ? and quarter = ?', array( $dealer[ 'id' ], $this->_activity_id, $field_item->getId(), $step[ 'id' ], $quarter ))->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
                            if (empty($field_datas)) {
                                $field_datas = ActivityExtendedStatisticStepValuesTable::getInstance()->createQuery()
                                    ->where('dealer_id = ? and activity_id = ? and field_id = ? and step_id = ? and quarter = ?', array( $dealer[ 'id' ], $this->_activity_id, $field_item->getId(), $step[ 'id' ], $quarter ))->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
                            }

                            foreach ($field_datas as $field_data) {
                                self::conceptDate($field_data[ 'concept_id' ], $concept_dates);

                                $fields[ $quarter ][ $dealer[ 'id' ] ][ $field_data[ 'concept_id' ] ][ $field_item->getId() ] = array( 'field' => $field_item->getId(), 'value' => $field_data[ 'value' ], 'is_calc' => false );
                            }
                        }
                    }
                }
            }
        }

        $pExcel = new PHPExcel();

        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle('Расширенная статистика');


        $headers = array();
        $headers[] = 'Номер';
        $headers[] = 'Дилер';
        $headers[] = 'Концепция';
        $headers[] = 'Срок действия сертификата';

        $headers = array_merge($headers, $fields_headers);
        $aSheet->fromArray($headers, null, 'A1');

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
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        $header_quarter_font = array(
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

        $last_letter = PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);

        $aSheet->getRowDimension('1')->setRowHeight(45);
        $aSheet->getStyle('A1:' . $last_letter . '1')->getAlignment()->setWrapText(true);

        $aSheet->getStyle('A1:' . $last_letter . '1')->applyFromArray($header_font);
        $aSheet->getStyle('A1:A1')->applyFromArray($header_font_with_underline);

        $cellIterator = $aSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        $aSheet->getStyle('A1:' . $last_letter . '1')->getAlignment()->setWrapText(true);

        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $aSheet->getColumnDimension($cell->getColumn())->setWidth(25);
        }

        $aSheet->getColumnDimension('A')->setWidth(15);
        $aSheet->getColumnDimension('B')->setWidth(35);
        $aSheet->freezePane('C3');

        $row = 2;

        foreach ($fields as $quarter => $quarter_data) {
            $aSheet->setCellValueByColumnAndRow(0, $row, sprintf('Квартал: %s', $quarter));
            $aSheet->getStyle('A' . $row . ':' . $last_letter . $row)->applyFromArray($header_quarter_font);

            $row++;
            foreach ($quarter_data as $dealer_id => $dealer_data) {
                foreach ($dealer_data as $concept_id => $concept_data) {
                    if (count($concept_data) > 3) {
                        $column = 0;

                        $aSheet->setCellValueByColumnAndRow($column++, $row, sprintf('%s', substr($dealers[$dealer_id]['number'], -3)));
                        $aSheet->setCellValueByColumnAndRow($column++, $row, sprintf('%s', $dealers[$dealer_id]['name']));
                        $aSheet->setCellValueByColumnAndRow($column++, $row, $concept_id);
                        $aSheet->setCellValueByColumnAndRow($column++, $row, $concept_id != 0 ? date("d-m-Y", strtotime($concept_dates[$concept_id])) : "");

                        foreach ($concept_data as $field_id => $field_data) {
                            if (strpos($field_data['value'], ":") !== FALSE) {
                                $field_data['value'] = str_replace(":", ". ", $field_data['value']) . '';
                            }
                            $aSheet->setCellValueByColumnAndRow($fields_position_in_cell[$field_id], $row, $field_data['value']);
                        }

                        $row++;
                    }
                }
            }

            $row++;
        }

        $file_name = 'sc_general_steps.xlsx';

        $objWriter = PHPExcel_IOFactory::createWriter($pExcel, "Excel2007");
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/' . $file_name);

        return array( 'success' => count($fields) > 0, 'file_url' => '/uploads/' . $file_name );
    }
}
