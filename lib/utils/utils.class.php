<?php

/**
 * Utils class
 *
 */
class Utils
{
    static private $_log_models_list = array();

    static function trim_text($input, $length, $ellipses = true, $strip_html = true)
    {
        //strip tags, if desired
        if ($strip_html) {
            $input = strip_tags($input);
        }

        //no need to trim, already shorter than trim length
        if (strlen($input) <= $length) {
            return $input;
        }

        //find last space within length
        $last_space = strrpos(substr($input, 0, $length), ' ');
        $trimmed_text = substr($input, 0, $last_space ? $last_space : $length);

        //add ellipses (...)
        if ($ellipses) {
            $trimmed_text .= '...';
        }

        return $trimmed_text;
    }

    static function format_amount($cost, $decimal = 2)
    {
        return sprintf('%s %s', number_format($cost, $decimal, '.', ' '), 'руб.');
    }

    static function format_number($cost, $decimal = 2)
    {
        return number_format($cost, $decimal, '.', ' ');
    }

    static function oi_encode_token($input = null, $key = null)
    {
        if ($input && $key) {
            $encoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $input, MCRYPT_MODE_CBC, md5(md5($key))));

            return $encoded;
        }

        return null;
    }

    static function normalize($name)
    {
        $str = '';
        $name = mb_strtolower(Utils::toUtf8($name), 'UTF-8');

        for ($n = 0, $len = mb_strlen($name, 'UTF-8'); $n < $len; $n++) {
            $new_sym = $sym = mb_substr($name, $n, 1, 'UTF-8');
            if (!Utils::isSymEnabled($sym)) {
                $new_sym = Utils::symToTranslit($sym);
                if (!$new_sym)
                    $new_sym = '_';
            }

            $str .= $new_sym;
        }

        return $str;
    }

    static function isSymEnabled($sym)
    {
        $enabled = 'abcdefghijklmnopqrstuvwxyz0123456789';
        return mb_strpos($enabled, $sym, 0, 'UTF-8') !== false;
    }

    static function symToTranslit($sym)
    {
        static $translit = array(
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'yo',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sch',
            'ы' => 'yi',
            'э' => 'ye',
            'ю' => 'yu',
            'я' => 'ya'
        );

        return isset($translit[$sym]) ? $translit[$sym] : false;
    }

    static function toUtf8($name)
    {
        return mb_convert_encoding($name, 'UTF-8', 'UTF-8,CP1251,ASCII');
    }

    static function getRemoteFileSize($link)
    {
        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //not necessary unless the file redirects (like the PHP example we're using here)

        $data = curl_exec($ch);
        curl_close($ch);
        if ($data === false) {
            return 0;
        }

        $contentLength = 'unknown';
        $status = 'unknown';
        if (preg_match('/^HTTP\/1\.[01] (\d\d\d)/', $data, $matches)) {
            $status = (int)$matches[1];
        }

        if (preg_match('/Content-Length: (\d+)/', $data, $matches)) {
            $contentLength = (int)$matches[1];
        }

        return F::getSmartSize($contentLength);
    }

    static function eqModelDateFromLogEntryWithYear($modelId, $year, $quarter = 0)
    {
        $calcDate = self::getModelDateFromLogEntryWithYear($modelId);

        if (!is_null($calcDate)) {
            $modelYear = D::getYear($calcDate);

            if ($quarter != 0) {
                $modelQuarter = D::getQuarter($calcDate);

                return $modelYear == $year && $modelQuarter == $quarter;
            }

            return $modelYear == $year;
        }

        return true;
    }

    static function eqModelDateFromLogEntryWithYearAndGetQuarter($model_id, $quarter) {
        $logDate = self::getModelDateFromLogEntryWithYear($model_id);
        if (!is_null($logDate)) {
            return D::getQuarter($logDate);
        }

        return $quarter;
    }

    static function getModelDateFromLogEntryWithYear($modelId, $returnAsObject = false, $actions = null)
    {
        return !is_null($actions)
            ? self::getModelDateFromLogEntryByActions($modelId, $returnAsObject, $actions)
            : self::getModelDateFromLogEntry($modelId, $returnAsObject);
    }

    /**
     * @param $modelId
     * @param $returnAsObject
     * @param null $actions
     * @return false|null|string
     */
    static function getModelDateFromLogEntryByActions($modelId, $returnAsObject, $actions) {
        $query = self::getModelDateFromLogQuery();

        //Доп. проверка на действия над заявкой
        $query->andWhere('object_type = ? or object_type = ? or object_type = ? or object_type = ? or object_type = ?', array('agreement_report', 'agreement_model', 'agreement_concept_report', 'agreement_concept', 'agreement_special_concept_report_regional_manager'));
        $query->andWhereIn('action', $actions);

        $result = null;
        if (is_array($modelId)) {
            $query->andWhereIn('object_id', $modelId);

            $result = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        } else {
            $query->andWhere('object_id = ?', $modelId)
                ->limit(1);

            if ($returnAsObject) {
                $result = $query->fetchOne();
            } else {
                $data = $query->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
                if (!empty($data)) {
                    $result = date('Y-m-d H:i:s', D::calcQuarterData($data[ 'created_at' ]));
                }
            }
        }

        if (!$result) {
            return self::getModelDateFromLogEntry($modelId, $returnAsObject);
        }

        return $result;
    }

    static function getModelDateFromLogEntry($modelId, $returnAsObject) {
        $query = self::getModelDateFromLogQuery();

        $query->andWhere('private_user_id = ? and icon = ? and (object_type = ? or object_type = ? or object_type = ? or object_type = ? or object_type = ? or object_type = ?)', array(0, 'clip', 'agreement_report', 'agreement_model', 'agreement_concept_report', 'agreement_concept', 'agreement_concept_report_by_importer', 'agreement_special_concept_report_regional_manager'));

        if (is_array($modelId)) {
            $query->andWhereIn('object_id', $modelId);

            return $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        } else {
            $query->andWhere('object_id = ?', $modelId)
                ->limit(1);
        }

        if ($returnAsObject) {
            return $query->fetchOne();
        } else {
            $data = $query->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
            if (!empty($data)) {
                return date('Y-m-d H:i:s', D::calcQuarterData($data['created_at']));
            }
        }

        return null;
    }

    static function getModelDateFromLogQuery() {
        return $query = LogEntryTable::getInstance()
            ->createQuery()
            ->select('created_at, object_id, icon')
            ->orderBy('id DESC');;
    }

    static function checkModelsCompleted(Activity $activity, Dealer $dealer, $year, $quarter)
    {
        $complete = false;

        //Выбираем список заявок по активности и по дилеру
        //Делаем проверку по заявкам, год создания заявки или год согласования заявки
        $query = AgreementModelTable::getInstance()
            ->createQuery('am')
            ->select('id')
            ->leftJoin('am.Report r')
            ->where('am.activity_id = ? and am.dealer_id = ?', array($activity->getId(), $dealer->getId()))
            //->andWhere('year(r.updated_at) = ? and quarter(r.updated_at) = ?', array($year, $quarter))
            ->andWhere('(year(r.updated_at) = ? or year(am.created_at) = ?)', array($year, $year))
            ->andWhere('am.status = ? and r.status = ?', array('accepted', 'accepted'))
            ->orderBy('am.id ASC');

        //Спец. согласование по рег. менеджеру и импортеру
        if (!$activity->getAllowSpecialAgreement()) {
            $query->andWhere('model_type_id != ?', Activity::CONCEPT_MODEL_TYPE_ID);
        }

        $activityModelsComplete = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        foreach ($activityModelsComplete as $model) {
            $date = Utils::getModelDateFromLogEntryWithYear($model['id']);
            if (!is_null($date)) {
                if ($year == D::getYear($date) && $quarter == D::getQuarter($date)) {
                    $complete = true;
                }
            }
        }

        return $complete;
    }

    static function makeUrl($text, $formatted = true)
    {
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

        if (preg_match($reg_exUrl, $text, $matches)) {
            if ($formatted) {
                return preg_replace($reg_exUrl, "<a href='" . $matches[0] . "' target='_blank'>" . $matches[0] . "</a>", $text);
            } else {
                return $matches[0];
            }
        }

        return $text;
    }

    static function checkUrl($text)
    {
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

        return preg_match($reg_exUrl, $text, $matches);
    }

    static function numberFormat($number, $currency = 'руб.')
    {
        return sprintf('%s %s', number_format($number, 2, '.', ' '), $currency);
    }

    static function array_merge_custom()
    {
        $array = array();

        foreach (func_get_args() as $key => $args) {
            foreach ($args as $arg_key => $value) {
                $array[$arg_key] = $value;
            }
        }

        return $array;
    }

    static function getElapsedTime($st)
    {
        $mins = floor($st / 60);
        $hours = floor($mins / 60);
        $days = floor($hours / 24);

        return $days;
    }

    static function drawExcelImage($icon, $coordinates, $pExcel, $offsetX = 3, $offsetY = 3, $label = '')
    {
        $imageModelStatus = new PHPExcel_Worksheet_Drawing();

        $imageModelStatus->setPath(sfConfig::get('app_images_path') . '/' . $icon);
        $imageModelStatus->setName($label);
        $imageModelStatus->setDescription($label);
        $imageModelStatus->setHeight(16);
        $imageModelStatus->setWidth(16);

        $imageModelStatus->setOffsetX($offsetX);
        $imageModelStatus->setOffsetY($offsetY);

        $imageModelStatus->setWorksheet($pExcel->getActiveSheet());
        $imageModelStatus->setCoordinates($coordinates);
    }

    static function isImage($img)
    {
        $ext = pathinfo($img, PATHINFO_EXTENSION);

        return in_array($ext, array('gif', 'png', 'jpg', 'jpeg'));
    }

    static function makeThumbnailFromImage($img_path, $dest, $desired_width)
    {
        $file_ext = pathinfo($img_path, PATHINFO_EXTENSION);
        switch ($file_ext) {
            case 'jpeg':
            case 'jpg':
                $source_image = imagecreatefromjpeg($img_path);
                break;

            case 'png':
                $source_image = imagecreatefrompng($img_path);
                break;

            case 'gif':
                $source_image = imagecreatefromgif($img_path);
                break;
        }

        $width = imagesx($source_image);
        $height = imagesy($source_image);

        $desired_height = floor($height * ($desired_width / $width));
        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
        switch ($file_ext) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($virtual_image, $dest);
                break;

            case 'png':
                imagepng($virtual_image, $dest);
                break;

            case 'gif':
                imagegif($virtual_image, $dest);
                break;
        }
    }

    static function getYearsList($from, $plus_years = 10)
    {
        $gen_year = range($from, date('Y') + $plus_years);

        return array_merge(array_combine($gen_year, $gen_year));
    }

    static function getIndexFromGenList($value)
    {
        $gen_list = self::getYearsList(sfConfig::get('app_min_year_for_gen'), sfConfig::get('app_plus_years'));
        $gen_result = array_filter($gen_list, function ($item) use ($value) {
            return $item == $value;
        });

        if (!empty($gen_result)) {
            $keys = array_keys($gen_result);

            return $keys[0];
        }

        return 0;
    }

    static function formatIdxFromZero($idx)
    {
        return $idx < 10 ? '0' . $idx : $idx;
    }

    public static function getUploadedFilesList(sfWebRequest $request, $field, $uploaded_files_count = null)
    {
        $files = $request->getFiles();
        if (!is_array($files)) {
            return $files;
        }

        if (empty($files)) {
            return $files;
        }

        $uploaded_files = self::getUploadedFilesByField($files, $field, $uploaded_files_count);
        if (!empty($uploaded_files)) {
            return $uploaded_files;
        }

        $server_file = $request->getPostParameter('server_model_file');
        if (!$server_file || preg_match('#[\\\/]#', $server_file)) {
            return $files;
        }

        return $files;
    }

    static function getUploadedFilesByField($files, $file_field, $uploaded_files_count = null)
    {
        if (is_array($file_field)) {
            $fields = $file_field;
        } else {
            $fields = array($file_field);
        }

        $uploaded_files_result = array();
        foreach ($files as $key => $file) {
            if (isset($files[$key]['tmp_name']) && $files[$key]['tmp_name']) {
                $uploaded_files_result[$key] = $files[$key];
            }
        }

        foreach ($fields as $field) {
            if (isset($files[$field])) {
                $uploaded_files_result[$field] = $files[$field];
            }
        }

        return $uploaded_files_result;
    }

    /**
     * Форматирование параметров сообщения для передачи в чаты
     * @param $message
     * @return null
     */
    public static function formatMessageData($message, $return_as_null = true)
    {
        if ($return_as_null) {
            return null;
        }

        if (!$message || !$message->getMsgShow()) {
            return null;
        }

        //Список пользователей кторые получают сообщение по умолчанию
        $users_ids = array_map(function($item) {
                return $item['id'];
            },
            UserTable::getInstance()->createQuery()->select('id')->where('allow_to_receive_messages_in_chat = ?', true)->execute(array(), Doctrine_Core::HYDRATE_ARRAY)
        );

        $users_ids[] = 1;

        $discussion = $message->getDiscussion();
        $model = $discussion->getModels()->getFirst();

        if (!$model) {
            return null;
        }

        //Получаем список пользователей которые получают сообщение
        foreach ($discussion->getMessages() as $message_item) {
            if(!in_array($message_item->getUserId(), $users_ids)) {
                $users_ids[] = $message_item->getUserId();
            }
        }

        //Список пользователей сформированный с именами
        $chat_with = array();
        foreach ($users_ids as $chat_item) {
            $user_item = UserTable::getInstance()->find($chat_item);
            if ($user_item) {
                $chat_with[] = sprintf('%s %s', $user_item->getName(), $user_item->getSurname());
            }
        }

        return array
        (
            'message' => $message->getText(),
            'users_who_get_messages' => $users_ids,
            'send_user' => sprintf('%s %s', $message->getUser()->getName(), $message->getUser()->getSurname()),
            'message_time' => date('H:i', strtotime($message->getCreatedAt())),
            'users_names' => implode(' / ', $chat_with),
            'messages_list' => '',
            'messages_count' => 1,
            'message_type' => 'message',
            'discussion_id' => $discussion->getId(),
            'model_id' => $model ? $model->getId() : 0,
            'dealer_id' => $model->getDealerId(),
            'model_url' => "/activity/module/agreement/management/models/{$model->getId()}"
        );
    }

    public static function my_str_split($string)
    {
        $slen = strlen($string);
        for ($i = 0; $i < $slen; $i++) {
            $sArray[$i] = $string{$i};
        }
        return $sArray;
    }

    public static function noDiacritics($string)
    {
        //cyrylic transcription
        $cyrylicFrom = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $cyrylicTo = array('A', 'B', 'W', 'G', 'D', 'Ie', 'Io', 'Z', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'Ch', 'C', 'Tch', 'Sh', 'Shtch', '', 'Y', '', 'E', 'Iu', 'Ia', 'a', 'b', 'w', 'g', 'd', 'ie', 'io', 'z', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'ch', 'c', 'tch', 'sh', 'shtch', '', 'y', '', 'e', 'iu', 'ia');


        $from = array("Á", "À", "Â", "Ä", "A", "A", "Ã", "Å", "A", "Æ", "C", "C", "C", "C", "Ç", "D", "Ð", "Ð", "É", "È", "E", "Ê", "Ë", "E", "E", "E", "?", "G", "G", "G", "G", "á", "à", "â", "ä", "a", "a", "ã", "å", "a", "æ", "c", "c", "c", "c", "ç", "d", "d", "ð", "é", "è", "e", "ê", "ë", "e", "e", "e", "?", "g", "g", "g", "g", "H", "H", "I", "Í", "Ì", "I", "Î", "Ï", "I", "I", "?", "J", "K", "L", "L", "N", "N", "Ñ", "N", "Ó", "Ò", "Ô", "Ö", "Õ", "O", "Ø", "O", "Œ", "h", "h", "i", "í", "ì", "i", "î", "ï", "i", "i", "?", "j", "k", "l", "l", "n", "n", "ñ", "n", "ó", "ò", "ô", "ö", "õ", "o", "ø", "o", "œ", "R", "R", "S", "S", "Š", "S", "T", "T", "Þ", "Ú", "Ù", "Û", "Ü", "U", "U", "U", "U", "U", "U", "W", "Ý", "Y", "Ÿ", "Z", "Z", "Ž", "r", "r", "s", "s", "š", "s", "ß", "t", "t", "þ", "ú", "ù", "û", "ü", "u", "u", "u", "u", "u", "u", "w", "ý", "y", "ÿ", "z", "z", "ž");
        $to = array("A", "A", "A", "A", "A", "A", "A", "A", "A", "AE", "C", "C", "C", "C", "C", "D", "D", "D", "E", "E", "E", "E", "E", "E", "E", "E", "G", "G", "G", "G", "G", "a", "a", "a", "a", "a", "a", "a", "a", "a", "ae", "c", "c", "c", "c", "c", "d", "d", "d", "e", "e", "e", "e", "e", "e", "e", "e", "g", "g", "g", "g", "g", "H", "H", "I", "I", "I", "I", "I", "I", "I", "I", "IJ", "J", "K", "L", "L", "N", "N", "N", "N", "O", "O", "O", "O", "O", "O", "O", "O", "CE", "h", "h", "i", "i", "i", "i", "i", "i", "i", "i", "ij", "j", "k", "l", "l", "n", "n", "n", "n", "o", "o", "o", "o", "o", "o", "o", "o", "o", "R", "R", "S", "S", "S", "S", "T", "T", "T", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "W", "Y", "Y", "Y", "Z", "Z", "Z", "r", "r", "s", "s", "s", "s", "B", "t", "t", "b", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "w", "y", "y", "y", "z", "z", "z");


        $from = array_merge($from, $cyrylicFrom);
        $to = array_merge($to, $cyrylicTo);

        $newstring = str_replace($from, $to, $string);

        return $newstring;
    }

    public static function makeSlugs($string, $maxlen = 0)
    {
        $newStringTab = array();
        $string = strtolower(self::noDiacritics($string));
        if (function_exists('str_split')) {
            $stringTab = str_split($string);
        } else {
            $stringTab = self::my_str_split($string);
        }

        $numbers = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "-");
        //$numbers=array("0","1","2","3","4","5","6","7","8","9");

        foreach ($stringTab as $letter) {
            if (in_array($letter, range("a", "z")) || in_array($letter, $numbers)) {
                $newStringTab[] = $letter;
            } elseif ($letter == " ") {
                $newStringTab[] = "_";
            }
        }

        if (count($newStringTab)) {
            $newString = implode($newStringTab);
            if ($maxlen > 0) {
                $newString = substr($newString, 0, $maxlen);
            }

            $newString = self::removeDuplicates('--', '_', $newString);
        } else {
            $newString = '';
        }

        return $newString;
    }


    public static function checkSlug($sSlug)
    {
        if (preg_match("/^[a-zA-Z0-9]+[a-zA-Z0-9\-]*$/", $sSlug) == 1) {
            return true;
        }

        return false;
    }

    public static function removeDuplicates($sSearch, $sReplace, $sSubject)
    {
        $i = 0;
        do {

            $sSubject = str_replace($sSearch, $sReplace, $sSubject);
            $pos = strpos($sSubject, $sSearch);

            $i++;
            if ($i > 100) {
                die('self::removeDuplicates() loop error');
            }

        } while ($pos !== false);

        return $sSubject;
    }

    public static function allowedIps() {
        $ip = getenv('REMOTE_ADDR');
        //$ips = array('46.175.160.37', '46.175.166.67', '46.175.166.61', '46.175.165.37', '109.73.13.105', '93.170.246.38', '109.73.13.105', '109.73.13.105');
        //$ips = array('46.175.160.37', '46.175.166.67', '46.175.166.61', '46.175.165.37', '109.73.13.105');
        $ips = array('46.175.160.37', '46.175.166.67', '46.175.166.61', '46.175.165.37', '109.73.13.105');

        return in_array($ip, $ips);
    }

    /**
     * Получаем корректный год и квартал
     * @param $date
     * @param $currentYear
     * @param $currentQ
     * @return array
     */
    public static function correctYearAndQ($date, $currentYear, $currentQ) {
        $createdYear = D::getYear($date);
        if ($currentYear > $createdYear) {
            return array($createdYear, 4);
        }

        return array($currentYear, $currentQ);
    }
}
