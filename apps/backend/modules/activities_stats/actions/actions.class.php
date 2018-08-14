<?php

include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Cell.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Writer/Excel5.php');

/**
 * activities_stats actions.
 *
 * @package    Servicepool2.0
 * @subpackage deleted_models
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class activities_statsActions extends sfActions
{

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    function executeIndex(sfWebRequest $request)
    {
        $this->activities = ActivityTable::getInstance()
            ->createQuery('a')
            ->innerJoin('a.ActivityField af')
            ->orderBy('a.id DESC')
            ->execute();
    }

    function executeShow(sfWebRequest $request)
    {
        $this->setTemplate('index');
    }

    public function executeExportData(sfWebRequest $request)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));

        $activity = ActivityTable::getInstance()->find($request->getParameter('activityId'));
        $this->builder = new ActivityStatisticFieldsBuilder(
            array
            (
                'year' => date('Y'),
                'quarter' => -1
            ),
            $activity,
            $this->getUser());

        $stat = $this->builder->getStat();

        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle(Utils::trim_text($activity->getName(), 30));

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

        //ksort($stat['dealers']);

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

        $file_name = Utils::makeSlugs($activity->getName()).'.xlsx';

        $objWriter = PHPExcel_IOFactory::createWriter($pExcel, "Excel2007");
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/'.$file_name);

        $json = json_encode(array("success" => true, 'file_name' => $file_name));

        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent($json);

        return sfView::NONE;
    }
}
