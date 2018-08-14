<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 02.11.2017
 * Time: 15:49
 */

class DealersServicesActivitiesExport {
    private $_activity_id = 0;

    public function __construct($activity_id)
    {
        $this->_activity_id = $activity_id;
    }

    public function makeExport() {
        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();
        $aSheet->setTitle('Выгрузка сервисных акций');

        $headers = array('№ дилера', 'Дилер', 'Дата начала', 'Дата окончания', 'Статус');
        $aSheet->fromArray($headers, null, 'A1');

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
        $normal = array(
            'font' => array(
                'name' => 'Calibri',
                'size' => '10',
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );


        $aSheet->getRowDimension('1')->setRowHeight(15);
        $aSheet->getStyle('A1:M1')->getAlignment()->setWrapText(true);

        $last_letter = PHPExcel_Cell::stringFromColumnIndex(count($headers) - 1);

        $aSheet->getStyle('A1:' . $last_letter . '1')->applyFromArray($header_font);
        $aSheet->getStyle('A1:A1')->applyFromArray($header_font_with_underline);

        $cellIterator = $aSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $aSheet->getColumnDimension($cell->getColumn())->setWidth(25);
        }

        $aSheet->getColumnDimension('A')->setWidth(15);
        $aSheet->getColumnDimension('B')->setWidth(40);
        $aSheet->getColumnDimension('C')->setWidth(15);
        $aSheet->getColumnDimension('D')->setWidth(15);
        $aSheet->getColumnDimension('E')->setWidth(15);

        $row = 3;

        $activity = ActivityTable::getInstance()->createQuery()->where('id = ?', $this->_activity_id)->fetchOne();

        $service_data = DealerServicesDialogsTable::getInstance()->createQuery()->where('activity_id = ?', $this->_activity_id)->fetchOne();
        if (!$service_data ) {
            return '';
        }

        $services_data_list = DealersServiceDataTable::getInstance()->createQuery()->where('dialog_service_id = ?', $service_data->getId())->execute();

        foreach ($services_data_list as $result_data) {
            $aSheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($normal);

            $column = 0;

            $aSheet->setCellValueByColumnAndRow($column++, $row, $result_data->getDealer()->getShortNumber());
            $aSheet->setCellValueByColumnAndRow($column++, $row, $result_data->getDealer()->getName());

            $start_date = $result_data->getStartDate();
            if (!empty($start_date)) {
                $start_date = date('Y-m-d', strtotime($start_date));
                //$start_date = PHPExcel_Shared_Date::PHPToExcel(new DateTime($start_date));
            }

            $end_date = $result_data->getEndDate();
            if (!empty($end_date)) {
                $end_date = date('Y-m-d', strtotime($end_date));
                //$end_date = PHPExcel_Shared_Date::PHPToExcel(new DateTime($end_date));
            }

            $aSheet->setCellValueByColumnAndRow($column++, $row, !empty($start_date) ? $start_date : '');
            $aSheet->setCellValueByColumnAndRow($column++, $row, !empty($end_date) ? $end_date : '');

            $aSheet->getStyle('C' . $row . ':D' . $row)->applyFromArray($center);

            $aSheet->setCellValueByColumnAndRow($column, $row, $result_data->getStatus());
            if ($result_data->getStatus() != 'accepted') {
                $aSheet->getStyle('A' . $row . ':E' . $row)
                    ->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('f39e9e');
            }

            $row++;
        }

        $file = Utils::makeSlugs($activity->getName()).'_'.time();
        $file_name = '/uploads/'.$file.'.xlsx';

        $objWriter = PHPExcel_IOFactory::createWriter($pExcel, "Excel2007");
        $objWriter->save(sfConfig::get('sf_root_dir') . '/www/uploads/'.$file.'.xlsx');

        return $file_name;
    }
}
