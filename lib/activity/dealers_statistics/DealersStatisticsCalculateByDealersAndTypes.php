<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 28.07.2017
 * Time: 13:18
 */

class DealersStatisticsCalculateByDealersAndTypes {
    private $_quarter = null;
    private $_year = null;
    private $_category_or_type = null;
    private $_category_or_type_id = '';
    private $_data_type = '';
    private $_extended_category_info = false;

    private $_statistic_aggregator = null;
    private $_dealers_ids = array();

    const DATA_TYPE_AMOUNTS = 'amounts';
    const DATA_TYPE_COUNTS = 'counts';

    public function __construct($year, $quarter, $mandatory_activity, $category_or_type, $data_type, $extended_category_info = false)
    {
        $this->_year = $year;
        $this->_quarter = $quarter;
        $this->_mandatory_activity = $mandatory_activity;
        $this->_category_or_type = $category_or_type;
        $this->_data_type = $data_type;
        $this->_extended_category_info = $extended_category_info;

        $this->init();
    }

    private function init()
    {
        $this->_statistic_aggregator = array();

        foreach (DealerTable::getDealersList()->select('id')->execute(array(), Doctrine_Core::HYDRATE_ARRAY) as $dealer) {
            $this->_dealers_ids[] = $dealer['id'];
        }
    }

    public function start() {
        $query = AgreementModelTable::getInstance()->createQuery('am')
            ->select('id, id as mId, activity_id, dealer_id, cost, status, step1, step2, report_id, model_category_id, model_type_id, created_at, am.updated_at am_updated_at, a.type_company_id, r.status as r_status, a.mandatory_activity as req_activity')
            ->where('(year(created_at) = ? or year(updated_at) = ?)', array($this->_year, $this->_year))
            ->andWhereIn('dealer_id', $this->_dealers_ids)
            ->andWhere('am.status = ? and r.status = ?', array('accepted', 'accepted'))
            ->innerJoin('am.Activity a')
            ->innerJoin('am.Report r')
            ->orderBy('dealer_id ASC');

        if (!empty($this->_category_or_type)) {
            $this->_category_or_type_id = 'model_category_id';
            if ($this->_category_or_type == 'types') {
                $query->andWhere('am.model_category_id = ?', 11);
                $this->_category_or_type_id = 'model_type_id';
            } else {
                $query->andWhere('am.model_category_id != ?', 11);
            }
        }

        $models = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $temp_models = array();
        $completed_models_ids = array();

        //Делаем первый проход для получения списка невыполненных заявок по кварталам
        //И получаем список индексов выполненных заявок для прохода по логам и получения корректной даты воплнения заявки

        foreach ($models as $model) {
            if ($model['status'] == 'accepted' && $model['r_status'] == 'accepted') {
                $completed_models_ids[] = $model['id'];
                $temp_models[$model['id']] = $model;
            }
        }

        //Проходим по логам и получаем квартал выполнения заявки
        $completed_models = Utils::getModelDateFromLogEntryWithYear($completed_models_ids);
        $completed_models_ids = array();
        $models_list_by_quarter = array();

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

                //Делаем доп. проверку на год
                if ($model['model_year'] == $this->_year) {
                    if (!empty($this->_quarter)) {
                        if ($this->_quarter == $model['model_q']) {
                            $models_list_by_quarter[$temp_models[$completed_model['object_id']]['dealer_id']][] = $model;

                            //Информация в разрезе категории
                            if ($this->_extended_category_info) {
                                $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['model_type_id']][$model['dealer_id']] = 0;
                            } else {
                                $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['dealer_id']] = 0;
                            }
                        }
                    } else {
                        $models_list_by_quarter[$temp_models[$completed_model['object_id']]['dealer_id']][] = $model;

                        //Информация в разрезе категории
                        if ($this->_extended_category_info) {
                            $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['model_type_id']][$model['dealer_id']] = 0;
                        } else{
                            $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['dealer_id']] = 0;
                        }
                    }
                }
            }
        }

        $dealers = array();
        $dealers_work_with_models = array();

        /** @var Dealer $dealer */
        foreach ($models_list_by_quarter as $dealer_id => $dealer_models) {
            if (!in_array($dealer_id, $dealers_work_with_models)) {
                $dealers_work_with_models[] = $dealer_id;
            }

            if (!array_key_exists($dealer_id, $dealers)) {
                $dealers[$dealer_id] = $dealer_id;

                //Calc fact budget
                foreach ($dealer_models as $model) {

                    if ($model['model_year'] != $this->_year) {
                        continue;
                    }

                    if ($this->_mandatory_activity && !$model['req_activity']) {
                        //Добавляем суммы заявк в факт даже есть активность не обязательная
                        if (!empty($this->_quarter)) {
                            if ($model['model_q'] == $this->_quarter) {
                                if ($this->_data_type == self::DATA_TYPE_COUNTS) {
                                    //Информация в разрезе категории
                                    if ($this->_extended_category_info) {
                                        $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['model_type_id']][$model['dealer_id']]++;
                                    } else {
                                        $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['dealer_id']]++;
                                    }
                                } else {
                                    //Информация в разрезе категории
                                    if ($this->_extended_category_info) {
                                        $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['model_type_id']][$model['dealer_id']] += $model['cost'];
                                    } else {
                                        $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['dealer_id']] += $model['cost'];
                                    }
                                }
                            }
                        } else {
                            if ($this->_data_type == self::DATA_TYPE_COUNTS) {
                                //Информация в разрезе категории
                                if ($this->_extended_category_info) {
                                    $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['model_type_id']][$model['dealer_id']]++;
                                } else {
                                    $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['dealer_id']]++;
                                }
                            } else {
                                //Информация в разрезе категории
                                if ($this->_extended_category_info) {
                                    $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['model_type_id']][$model['dealer_id']] += $model['cost'];;
                                } else {
                                    $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['dealer_id']] += $model['cost'];
                                }
                            }
                        }

                        continue;
                    }

                    //Делаем проверку на квартал, если выбран сравнивам квартал заявки с выбранным кварталом
                    if (!empty($this->_quarter)) {
                        if ($model['model_q'] == $this->_quarter) {
                            if ($this->_data_type == self::DATA_TYPE_COUNTS) {
                                //Информация в разрезе категории
                                if ($this->_extended_category_info) {
                                    $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['model_type_id']][$model['dealer_id']]++;
                                } else {
                                    $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['dealer_id']]++;
                                }
                            } else {
                                //Информация в разрезе категории
                                if ($this->_extended_category_info) {
                                    $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['model_type_id']][$model['dealer_id']] += $model['cost'];;
                                } else {
                                    $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['dealer_id']] += $model['cost'];
                                }
                            }
                        }
                    } else {
                        if ($this->_data_type == self::DATA_TYPE_COUNTS) {
                            if ($this->_extended_category_info) {
                                $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['model_type_id']][$model['dealer_id']]++;
                            } else {
                                $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['dealer_id']]++;
                            }
                        } else {
                            //Информация в разрезе категории
                            if ($this->_extended_category_info) {
                                $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['model_type_id']][$model['dealer_id']] += $model['cost'];;
                            } else {
                                $this->_statistic_aggregator[$model[$this->_category_or_type_id]][$model['dealer_id']] += $model['cost'];
                            }
                        }
                    }
                }
            }
        }
    }

    public function getData() {
        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle('Выгрузка (Общая)');

        $headers = array('Категория');

        $aSheet->fromArray($headers, null, 'A3');

        $column = 0;

        if (!empty($this->_quarter)) {
            $aSheet->setCellValueByColumnAndRow($column, 1, sprintf('%s квартал %s (%s)', $this->_quarter, $this->_year, $this->_category_or_type == 'categories' ? 'По категории' : 'По типу'));
        } else {
            $aSheet->setCellValueByColumnAndRow($column, 1, sprintf('%s (%s)', $this->_year, $this->_category_or_type == 'categories' ? 'По категории' : 'По типу'));
        }


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

        $aSheet->getRowDimension('1')->setRowHeight(15);
        $aSheet->getStyle('A1:M1')->getAlignment()->setWrapText(true);

        $available_dealers = array();
        $available_types = array();
        $available_category_types = array();

        foreach ($this->_statistic_aggregator as $key_id => $result_data) {
            //Get model category / type name

            if (!array_key_exists($key_id, $available_types)) {
                if ($this->isCategoryType()) {
                    $category = AgreementModelCategoriesTable::getInstance()->createQuery()->select('name')->where('id = ? and is_blank = ?', array($key_id, false))->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
                    $name = $category['name'];

                    //Расширенная информация по категории
                    if ($this->_extended_category_info) {
                        foreach ($result_data as $type_id => $data) {
                            $type = AgreementModelTypeTable::getInstance()->createQuery()->select('name')->where('id = ?', $type_id)->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

                            $available_category_types[$key_id][$type_id] = $type['name'];
                        }
                    }
                } else {
                    $type = AgreementModelTypeTable::getInstance()->createQuery()->select('name')->where('id = ?', $key_id)->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
                    $name = $type['name'];
                }

                $available_types[$key_id] = $name;
            }

            //Расширенная информация по категории
            if ($this->_extended_category_info && $this->isCategoryType()) {
                foreach ($result_data as $ind => $result_item) {
                    foreach ($result_item as $dealer_id => $dealer_data) {
                        $dealers[$dealer_id] = $dealer_data;
                    }
                }
            } else {
                $dealers = $result_data;
            }

            foreach ($dealers as $dealer_id => $dealer_data) {
                if (!array_key_exists($dealer_id, $available_dealers)) {
                    $dealer = DealerTable::getInstance()->createQuery()->select('name, number')->where('id = ?', $dealer_id)->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

                    $available_dealers[$dealer_id] = array('name' => $dealer['name'], 'number' => $dealer['number']);
                }
            }
        }

        asort($available_types);
        $column = 1;
        $row = 3;
        $dealers_columns_schema = array();
        foreach ($available_dealers as $dealer_id => $dealer_data) {
            $dealers_columns_schema[$dealer_id] = $column;

            $dealer_name_number = $dealer_data['name'].'('.substr($dealer_data['number'], -3).')';

            $aSheet->setCellValueByColumnAndRow($column++, $row, $dealer_name_number);
            $headers[] = $dealer_name_number;
        }

        $last_letter = PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);

        $aSheet->getStyle('A3:' . $last_letter . '3')->applyFromArray($header_font);
        $aSheet->getStyle('A3:A3')->applyFromArray($header_font_with_underline);

        $cellIterator = $aSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        $aSheet->getRowDimension('3')->setRowHeight(25);

        $aSheet->getStyle('A3:'.$last_letter.'3')->getAlignment()->setWrapText(true);

        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $aSheet->getColumnDimension($cell->getColumn())->setWidth(25);
        }

        $aSheet->getColumnDimension('A')->setWidth(35);
        $aSheet->freezePane('B3');

        $row = 4;
        $column = 0;

        //Только для Категорий (расширенная информация)
        if ($this->_extended_category_info && $this->isCategoryType()) {

            foreach ($available_types as $category_id => $category_name) {
                $aSheet->setCellValueByColumnAndRow($column, $row, $category_name);
                $aSheet->getStyle('A'.$row.':A' . $row)->applyFromArray($bold_normal_font);
                $row++;

                foreach ($available_category_types[$category_id] as $type_id => $type_name) {
                    $aSheet->setCellValueByColumnAndRow($column, $row, $type_name);

                    $aSheet->getStyle('A'.$row.':A' . $row)->applyFromArray($right_bold);

                    foreach ($this->_statistic_aggregator[$category_id][$type_id] as $dealer_id => $data) {
                        if (array_key_exists($dealer_id, $dealers_columns_schema)) {
                            $aSheet->setCellValueByColumnAndRow($dealers_columns_schema[$dealer_id], $row, $data);
                            $aSheet->getStyle($dealers_columns_schema[$dealer_id] . $row)
                                ->getNumberFormat()
                                ->setFormatCode('#,##0.00');
                        }
                    }

                    $row++;
                }
            }

        } else {
            foreach ($available_types as $type_id => $type_name) {
                $aSheet->setCellValueByColumnAndRow($column, $row, $type_name);

                foreach ($this->_statistic_aggregator[$type_id] as $dealer_id => $data) {
                    if (array_key_exists($dealer_id, $dealers_columns_schema)) {
                        $aSheet->setCellValueByColumnAndRow($dealers_columns_schema[$dealer_id], $row, $data);
                        $aSheet->getStyle($dealers_columns_schema[$dealer_id] . $row)
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00');
                    }
                }

                $row++;
            }
        }

        $file_name = '/uploads/statistics_dealers_and_types.xlsx';

        //$objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter = PHPExcel_IOFactory::createWriter($pExcel, "Excel2007");
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/statistics_dealers_and_types.xlsx');

        return $file_name;
    }

    public function getCategoryTypeId() {
        return $this->_category_or_type_id;
    }

    /**
     * Проверка на Категорию
     * @return bool
     */
    public function isCategoryType() {
        return $this->getCategoryTypeId() == "model_category_id";
    }
}
