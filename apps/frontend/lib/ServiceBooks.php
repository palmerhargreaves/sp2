<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 01.10.2018
 * Time: 12:13
 */

include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/IOFactory.php');

class ServiceBooks {

    /**
     * Получить данные по сервисным книжкам для дилера
     * @param $dealerNumber
     * @return int
     */
    public static function getDealerServiceBookData($dealerNumber) {
        $file_name = sfConfig::get('app_uploads_path').'/services_books.xlsx';
        try {
            $inputFileType = PHPExcel_IOFactory::identify($file_name);
            $fileReader = PHPExcel_IOFactory::createReader($inputFileType);
            $pExcel = $fileReader->load($file_name);
        } catch (Exception $e) {
            return -1;
        }

        $sheet = $pExcel->getSheet(0);
        $highRow = $sheet->getHighestRow();
        $highCol = $sheet->getHighestColumn();

        $service_books_data = array();
        for($row = 2; $row <= $highRow; $row++) {
            $rowData = array_values($sheet->rangeToArray('A'.$row.':'.$highCol.$row, null, true, false));

            if (!empty($rowData) && isset($rowData[0])) {
                $service_books_data[$rowData[0][0]] = array(
                    'count_of_services_books' => $rowData[0][2],
                    'count_of_services_books_for_tuareg' => $rowData[0][3],
                    'count_of_stickers_for_first_part' => $rowData[0][4],
                    'count_of_buklets_michlen' => $rowData[0][5]
                );
            }
        }

        return array_key_exists($dealerNumber, $service_books_data) ? $service_books_data[$dealerNumber] : -1;
    }
}
