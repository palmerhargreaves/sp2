<?php

/**
 * MailingList
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class MailingList extends BaseMailingList
{
    const EXCEPTION_DATE = 1;
    const EXCEPTION_PHONE = 2;
    const EXCEPTION_EMAIL = 3;
    const EXCEPTION_FILE = 3;

    private static $_year = null;
    private static $_quarter = null;

    /**
     * @param $file
     * @param $total_result
     * @param $dealer
     * @param null $year
     * @param null $quarter
     * @return array|bool|Exception
     * @throws Exception
     */
    public static function readDealerFile($file, &$total_result, $dealer, $year = null, $quarter = null)
    {
        self::$_year = $year;
        self::$_quarter = $quarter;

        if (!empty($file)) {
            $uploadfile = '../apps/frontend/modules/mailing/load_files/' . date('Y_m_d') . '-' . basename($file['data_file']['name']);
            if (isset($file['data_file']) && isset($file['data_file']['name']))
                $path_file = pathinfo($file['data_file']['name']);

            if ((($file['data_file']['type'] == 'text/csv' || $file['data_file']['type'] == 'application/vnd.ms-excel') && $path_file['extension'] == 'csv') && move_uploaded_file($file['data_file']['tmp_name'], $uploadfile)) {
                setlocale(LC_ALL, 'ru_RU.UTF-8');
                $handle = fopen($uploadfile, 'r');
                $array = array();
//                $total_file_records = array();
                $row = 0;
                while ($data = fgetcsv($handle, 4096, ";")) {
                    if ($row != 0 && !self::emptyData($data)) {
                        try {
                            $data_item = self::checkData($data, $total_result, $dealer);
                        } catch (Exception $e) {
                            if ($e->getMessage() == self::EXCEPTION_DATE) {
                                $total_result['date_error'] = "Необходимо написать дату в правильном формате: 09.10.2017<br>" .
                                    "<a href=\"http://dm.vw-servicepool.ru/dealer_file.csv\">Скачать образец файла</a>.<br>";
                                throw new Exception(self::EXCEPTION_DATE);
                            }
                            if ($e->getMessage() == self::EXCEPTION_FILE) {
                                $total_result['file_error'] = "Вам необходимо оформить файл с адресами корректно, проверить наличие и порядок расположения всех столбцов и повторно загрузить его на портал.<br>" .
                                    "<a href=\"http://dm.vw-servicepool.ru/dealer_file.csv\">Скачать образец файла</a>.<br>";
                            }
                        }
                        array_push($array, $data_item);
                    }
                    ++$row;
                }
                fclose($handle);
                self::totalFileRecordsDuplicate($array, $total_result, $dealer);
                return $array;
            } else {
//                echo '<pre>'. print_r(123, 1) .'</pre>'; die();
//                $total_result['file_error'] = 'Необходимо загрузить файл в формате .csv.';
                throw new Exception(self::EXCEPTION_FILE);
            }
        } else {
            throw new Exception('File is empty!');
        }
    }

    /**
     * Проверка данных и сбор ошибок
     * @param $data
     * @return bool
     * @throws Exception
     */
    public static function checkData($data, &$total_result, $dealer)
    {
//        echo "<pre>" . print_r($data, 1) . "</pre>"; die();
        if (!empty($data) && count($data) >= 10) {
            $tmp = array();

            $has_error = false;
            foreach ($data as $key => $item) {
                if ($key == 0) {
                    $tmp['number'] = $dealer->getNumber();
                } elseif ($key == 1) {
                    $tmp['firstname'] = self::decodeString($item);
                } elseif ($key == 2) {
                    $tmp['lastname'] = self::decodeString($item);
                } elseif ($key == 3) {
                    $tmp['middlename'] = self::decodeString($item);
                } elseif ($key == 4) {
                    if (empty($item)) {
                        $tmp['gender'] = null;
                        $has_error = true;
                        self::logger('../log/mailing-errors.log', date('Y-m-d H:i:s') . " dealer number - " . $dealer->getNumber() . " - GENDER field EMPTY. He was not added to the database\n", FILE_APPEND);
                    } else {
                        $tmp['gender'] = self::decodeString($item);
                    }
                } elseif ($key == 5) {
                    $tmp['phone'] = trim(str_replace(" ", "", $item));
                } elseif ($key == 6) {
                    if (empty($item)) {
                        $tmp['vin'] = null;
                        $has_error = true;
                        self::logger('../log/mailing-errors.log', date('Y-m-d H:i:s') . " dealer number - " . $dealer->getNumber() . " - VIN field EMPTY. He was not added to the database\n", FILE_APPEND);
                    } else {
                        $tmp['vin'] = trim(str_replace(" ", "", $item));
                    }
                } elseif ($key == 7) {
                    if (!empty($item) && filter_var(trim($item), FILTER_VALIDATE_EMAIL)) { // Проверяем на правильность email
                        $tmp['email'] = trim(str_replace(" ", "", $item));
                    } else {
                        $tmp['email'] = null;
                        $has_error = true;
                        self::logger('../log/mailing-errors.log', date('Y-m-d H:i:s') . " dealer number - " . $dealer->getNumber() . " - Email address '" . $item . "' He was not added to the database\n", FILE_APPEND);
//                        throw new Exception(self::EXCEPTION_FILE);
                    }
                } elseif ($key == 8) {
                    try {
                        $date = new DateTime(trim($item));
                        if ($date)
                            $tmp['last_visit_date'] = $date->format('Y-m-d');
                    } catch (Exception $e) {
                        throw new Exception(self::EXCEPTION_DATE);
                    }
                } elseif ($key == 9) {
                    $date = new DateTime();
                    if ($date->format('d') == 31)
                        $date->modify('-1 day');

                    $date->modify('-1 month');

                    $tmp['added_date'] = $date->format('Y-m-d');
                }
            }

            //Если есть незаполненные поля в строке, учитываем
            if ($has_error) {
                $total_result['total_incorrect']++;
            }

            if (!empty($tmp['email']) && self::checkDuplicate($tmp['email'], $dealer)) { // Проверяем на дубликаты в базе
                $total_result['total_duplicate']++;
                self::logger('../log/mailing-errors.log', date('Y-m-d H:i:s') . " dealer number - " . $dealer->getNumber() . " - Email address '" . $tmp['email'] . "' duplicate\n", FILE_APPEND);
            } else {
                $total_result['total_unique']++;
            }
            return $tmp;
//            return false;
        } else {
//            echo "<pre>" . print_r($data, 1) . "</pre>"; die();
            throw new Exception(self::EXCEPTION_FILE);
        }
    }

    /**
     * @param $data
     * @return bool
     */
    static function emptyData($data)
    {
        foreach ($data as $i) {
            if (!empty($i))
                return false;
        }
        return true;
    }

    /**
     * @param $string
     * @return string
     */
    public static function decodeString($string)
    {
        return html_entity_decode(htmlentities(trim($string), ENT_QUOTES, "Windows-1251"), ENT_QUOTES, "utf-8");
    }

    /**
     * Метод добавляет и удаляет данные из базы так же изменяет статистику для вывода в результатах загрузки файла.
     * @param $item
     * @param $result - link to $total_result
     */
    public function validateItem($item, &$result, $dealer = null)
    {
        if ($this->addClient($item, $dealer)) {
            $result['total_added']++;
        } else {
            $result['total_incorrect']++;
        }
    }

    /**
     * @param $email
     * @param $dealer
     * @return bool
     */
    public static function checkDuplicate($email, $dealer)
    {
        $date = new DateTime();
        $quarter = self::getQuarter(date('m'));

        return MailingListTable::getInstance()
            ->createQuery()
            ->where('email = ?', $email)
            ->andWhere('dealer_id = ?', $dealer->getNumber())
            ->andWhere('YEAR(added_date) = ?', !is_null(self::$_year) ? self::$_year : $date->format('Y'))
//            ->andWhere('MONTH(added_date) = ?', $date->format('m'))
            ->andWhere('QUARTER(added_date) = ?', !is_null(self::$_quarter) ? self::$_quarter : $quarter)
            ->count() > 0 ? true : false;
    }

    /**
     * @param $records
     * @param $total_result
     */
    public static function totalFileRecordsDuplicate($records, &$total_result, $dealer)
    {
        $total = array();
        $count = 0;
        $total_result['total_on_file'] = count($records);
        foreach ($records as $record)
            $total[] = $record['email'];

        foreach (array_count_values($total) as $d_count) {
            if ($d_count > 1)
                $total_result['total_duplicate_on_file']++;
        }
    }

    /**
     * @param $dealer_number
     * @param $total_result
     * @param null $year
     * @param null $quarter
     * @return float
     */
    public static function checkDuplicatePerecnt($dealer_number, $total_result, $year = null, $quarter = null)
    {
        $quarter = !is_null($quarter) ? $quarter : self::getQuarter(date('m'));

        $percent = MailingListTable::getInstance()->createQuery()
                ->select()
                ->where('dealer_id = ?', $dealer_number)
                ->andWhere('QUARTER(added_date) = ?', $quarter)
                ->andWhere('YEAR(added_date) = ?', !is_null($year) ? $year : date('Y'))
                ->execute()->count() / 100;

        return round($total_result['total_duplicate'] / $percent);
    }

    /**
     * @param $clients
     * @param $dealer
     */
    public static function addAllTrueClients($clients, $dealer, &$total_result)
    {
//                echo '<pre>'. print_r($clients, 1) .'</pre>'; die();
        foreach ($clients as $client) {
            if (self::addClient($client, $dealer)) {
                $total_result['total_added']++;
            } else {
            }
        }
    }

    /**
     * @param $clinet
     * @param null $dealer
     * @return bool
     */
    public static function addClient($clinet, $dealer = null)
    {
        if (!empty($clinet['email'])) {
            $model = new MailingList();
            $model->setDealerId($dealer->getNumber());
            $model->setFirstName($clinet['firstname']);
            $model->setLastName($clinet['lastname']);
            $model->setMiddleName($clinet['middlename']);
            $model->setGender($clinet['gender']);
            $model->setPhone($clinet['phone']);
            $model->setEmail($clinet['email']);
            $model->setVin($clinet['vin']);
            $model->setLastVisitDate($clinet['last_visit_date']);
            $model->setLastUploadData($clinet['added_date']);
            $model->setAddedDate($clinet['added_date']);
            $model->save();
            return true;
        }
        return false;
    }


    /**
     * Удаление записей дилера за текущий месяц
     * @param $dealer_id - номер дилера
     */
    public static function deleteMailings($dealer_id, $params)
    {
        $mailings = MailingListTable::getInstance()->createQuery()
            ->delete()
            ->where('dealer_id = ' . $dealer_id . ' AND YEAR(added_date) IN (' . $params['year'] . ') AND MONTH(added_date) IN (' . $params['month'] . ')')
            ->execute();
    }

    /**
     * Возвращает текущий квартал
     * @return int
     */
    public static function getQuarter($month = null)
    {
        $quarter = 1;
        $date = new DateTime();
        if ($month) {
            $date = new DateTime(date('Y-' . $month . '-d'));
            $date->modify('-1 month');
        }

        if ($date->format('m') == 4 || $date->format('m') == 5 || $date->format('m') == 6)
            $quarter = 2;
        if ($date->format('m') == 7 || $date->format('m') == 8 || $date->format('m') == 9)
            $quarter = 3;
        if ($date->format('m') == 10 || $date->format('m') == 11 || $date->format('m') == 12)
            $quarter = 4;

        return $quarter;
    }


    /**
     * @param $filename - полный путь до файла
     * @param $data - сообщение для лога
     */
    public static function logger($filename, $data)
    {
        if (filesize($filename) > 5048576)
            unlink($filename);

        file_put_contents($filename, $data, FILE_APPEND);
    }


    /**
     * Экспорт статистики в XLS
     * @param mailings $
     */
    public static function exportStatToXls($data)
    {
        $pExcel = new PHPExcel();
        $pExcel->setActiveSheetIndex(0);
        $aSheet = $pExcel->getActiveSheet();

        $aSheet->setTitle('Cтатистика по емейлам');

        $boldFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '12',
                'bold' => true
            )
        );
        $center = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            ),
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '9',
                'bold' => false
            )
        );

        $left = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
        );

        $right = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
        );

        $column = 0;
        $row = 2;

        $aSheet->setCellValue("A1", 'Номер');
        $aSheet->getStyle('A1')->applyFromArray($boldFont);
        $aSheet->setCellValue("B1", 'Клиент');
        $aSheet->getStyle('B1')->applyFromArray($boldFont);
        $aSheet->setCellValue("C1", 'Телефон');
        $aSheet->getStyle('C1')->applyFromArray($boldFont);
        $aSheet->setCellValue("D1", 'Email');
        $aSheet->getStyle('D1')->applyFromArray($boldFont);
        $aSheet->setCellValue("E1", 'Дата посещения');
        $aSheet->getStyle('E1')->applyFromArray($boldFont);
        $aSheet->setCellValue("F1", 'Дата выгрузки');
        $aSheet->getStyle('F1')->applyFromArray($boldFont);
        $aSheet->setCellValue("G1", 'Дата загрузки');
        $aSheet->getStyle('G1')->applyFromArray($boldFont);
        $aSheet->setCellValue("H1", 'VIN номер');
        $aSheet->getStyle('H1')->applyFromArray($boldFont);
        $aSheet->setCellValue("I1", 'Пол');
        $aSheet->getStyle('I1')->applyFromArray($boldFont);
        $aSheet->setCellValue("J1", 'Название дилера');
        $aSheet->getStyle('J1')->applyFromArray($boldFont);


//        $aSheet->getStyle('2:' . (count($data) + 1))->applyFromArray($center);
//        $aSheet->getStyle('A1:B' . (count($data) + 1))->applyFromArray($boldFont);

        $cellIterator = $aSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        foreach ($cellIterator as $cell) {
            $aSheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        }

        sfProjectConfiguration::getActive()->loadHelpers('Date');
        foreach ($data as $d_key => $client) {
            $name = trim(str_replace('===', '', $client->getFirstName() . ' ' . $client->getLastName() . ' ' . $client->getMiddleName()));
            $phone = trim(str_replace('===', '', $client->getPhone()));
            $email = $client->getEmail() == "===" ? "" : strtolower(trim($client->getEmail()));
            $last_v_date = trim(str_replace('===', '', $client->getLastVisitDate()));
            $last_up_date = trim(str_replace('===', '', $client->getLastUploadData()));
            $gender = $client->getGender() == "===" ? "" : strtolower(trim($client->getGender()));

            $Dealer = DealerTable::getInstance()->findOneByNumber($client->getDealerId());
//            echo '<pre>'. print_r($Dealer->name, 1) .'</pre>'; die();

            $aSheet->setCellValueByColumnAndRow(0, $row, $d_key + 1);
            $aSheet->setCellValueByColumnAndRow(1, $row, $name);
            $aSheet->setCellValueByColumnAndRow(2, $row, $phone);
            $aSheet->setCellValueByColumnAndRow(3, $row, $email);
            $aSheet->setCellValueByColumnAndRow(4, $row, format_date($last_v_date, 'd MMMM yyyy', 'ru'));
            $aSheet->setCellValueByColumnAndRow(5, $row, format_date($last_up_date, 'd MMMM yyyy', 'ru'));
            $aSheet->setCellValueByColumnAndRow(6, $row, format_date($client->getAddedDate(), 'd MMMM yyyy', 'ru'));
            $aSheet->setCellValueByColumnAndRow(7, $row, $client->getVin());
            $aSheet->setCellValueByColumnAndRow(8, $row, $gender);
            $aSheet->setCellValueByColumnAndRow(9, $row, $Dealer['name']);
            $row++;
        }

        // Выводим HTTP-заголовки
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=dealer_mail_stat.xls");


        // Выводим содержимое файла
        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter->save('php://output');
        die();
    }

    /**
     * @param int $month
     * @param int $year
     */
    public static function exportStatToXlsAll($month = 1, $mailing_year = null)
    {
        $year = date('Y');
        if (!is_null($mailing_year)) {
            $year = $mailing_year;
        }

        ini_set('memory_limit', '4095M');
        set_time_limit(0);

        $pExcel = new PHPExcel();
        $Dealers = MailingListTable::getInstance()->createQuery()->select()
            ->groupBy('dealer_id')->execute();

        $index = 0;
        foreach ($Dealers as $dealer) {
            if (self::getCountDataFromDealer($dealer->getDealerId(), $year, $month)) {
                $generalDealer = DealerTable::getInstance()->createQuery()->select('number, name')->where('number = ?', $dealer->getDealerId())->execute()->toArray();
                $title = substr($dealer->getDealerId(), -3, 3) . '-' . mb_substr($generalDealer[0]['name'], 0, 25, 'utf-8');
                $pExcel->createSheet($index);
                $pExcel->setActiveSheetIndex($index);
                $aSheet = $pExcel->getActiveSheet();
                $aSheet->setTitle($title);

                $aSheet = self::setSheetHeader($aSheet);
                self::setSheetData($aSheet, $dealer->getDealerId(), $year, $month);

                ++$index;
            }
        }

        // Выводим HTTP-заголовки
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=dealer_mail_stat.xls");

        // Выводим содержимое файла
        $objWriter = new PHPExcel_Writer_Excel5($pExcel);
        $objWriter->save('php://output');
    }

    /**
     * @param $aSheet
     * @return mixed
     */
    private static function setSheetHeader($aSheet)
    {
        $boldFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '12',
                'bold' => true
            )
        );

        $aSheet->setCellValue("A1", 'Номер');
        $aSheet->getStyle('A1')->applyFromArray($boldFont);
        $aSheet->setCellValue("B1", 'Клиент');
        $aSheet->getStyle('B1')->applyFromArray($boldFont);
        $aSheet->setCellValue("C1", 'Пол');
        $aSheet->getStyle('C1')->applyFromArray($boldFont);
        $aSheet->setCellValue("D1", 'Телефон');
        $aSheet->getStyle('D1')->applyFromArray($boldFont);
        $aSheet->setCellValue("E1", 'Email');
        $aSheet->getStyle('E1')->applyFromArray($boldFont);
        $aSheet->setCellValue("F1", 'Дата посещения');
        $aSheet->getStyle('F1')->applyFromArray($boldFont);
        $aSheet->setCellValue("G1", 'Дата выгрузки');
        $aSheet->getStyle('G1')->applyFromArray($boldFont);
        $aSheet->setCellValue("H1", 'Дата выгрузки');
        $aSheet->getStyle('H1')->applyFromArray($boldFont);
        $aSheet->setCellValue("I1", 'VIN номер');
        $aSheet->getStyle('I1')->applyFromArray($boldFont);
        $aSheet->setCellValue("J1", 'Модель автомобиля');
        $aSheet->getStyle('J1')->applyFromArray($boldFont);

        $cellIterator = $aSheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        foreach ($cellIterator as $cell) {
            $aSheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        }

        return $aSheet;
    }

    /**
     * @param $dealer_id
     * @param $year
     * @param $month
     * @return bool
     */
    private static function getCountDataFromDealer($dealer_id, $year, $month)
    {
        $data = MailingListTable::getInstance()
            ->createQuery()->select()
            ->where('dealer_id = ?', $dealer_id)
            ->andWhere('YEAR(added_date) = ?', $year)
            ->andWhere('MONTH(added_date) = ?', $month);
        return $data->count() > 0 ? true : false;
    }

    /**
     * @param $aSheet
     * @param $data
     */
    private static function setSheetData($aSheet, $dealer_id, $year, $month)
    {
        $row = 2;
        sfProjectConfiguration::getActive()->loadHelpers('Date');
        $data = MailingListTable::getInstance()->createQuery()->select()
            ->where('dealer_id = ?', $dealer_id)
            ->andWhere('YEAR(added_date) = ?', $year)
            ->andWhere('MONTH(added_date) = ?', $month)
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        foreach ($data as $d_key => $client) {
            $name = trim(str_replace('===', '', $client['fist_name'] . ' ' . $client['last_name']));
            $phone = trim(str_replace('===', '', $client['phone']));
            $email = trim(str_replace('===', '', $client['email']));
            $gender = trim(str_replace('===', '', $client['gender']));
            $last_v_date = trim(str_replace('===', '', $client['last_visit_date']));
            $last_up_date = trim(str_replace('===', '', $client['last_upload_data']));

            $aSheet->setCellValueByColumnAndRow(0, $row, $d_key + 1);
            $aSheet->setCellValueByColumnAndRow(1, $row, $name);
            $aSheet->setCellValueByColumnAndRow(2, $row, $gender);
            $aSheet->setCellValueByColumnAndRow(3, $row, $phone);
            $aSheet->setCellValueByColumnAndRow(4, $row, $email);
            $aSheet->setCellValueByColumnAndRow(5, $row, $last_v_date);
            $aSheet->setCellValueByColumnAndRow(6, $row, $last_up_date);
            $aSheet->setCellValueByColumnAndRow(7, $row, format_date($client['added_date'], 'd MMMM yyyy', 'ru'));
            $aSheet->setCellValueByColumnAndRow(8, $row, $client['vin']);
            $aSheet->setCellValueByColumnAndRow(9, $row, $client['model']);
            $row++;
        }

        return $aSheet;
    }
}
