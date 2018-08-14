<?php

/**
 * makets_info actions.
 *
 * @package    Servicepool2.0
 * @subpackage comment_stat
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class makets_infoActions extends sfActions
{
    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    private $filesDir = '/activities/module/agreement/model_file/';

    const START_YEAR = 2013;

    function executeIndex(sfWebRequest $request)
    {
        $year = $request->getParameter('year');
        $quarter = $request->getParameter('quarter');
        $dealer = $request->getParameter('dealer_filter');
        $activity = $request->getParameter('activity');
        $fTypes = $request->getParameter('fTypes');
        $onlyAccepted = $request->getParameter('cbOnlyAcceptModels') == "on" ? true : false;

        $this->currentYear = (empty($year) ? date('Y') : $year);
        $this->currentActivity = $activity;
        $this->dealer_filter = $dealer;
        $this->fType = $fTypes;
        $this->currentQuarter = $quarter;
        $this->onlyAccepted = $onlyAccepted;

        $this->startYear = self::START_YEAR;
        $this->endYear = date('Y');

        $res = null;
        if ($year) {

            $zip = new ZipArchive();
            $zipFile = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'makets.zip';

            @unlink($zipFile);
            $res = $zip->open($zipFile, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);

            if ($res) {

                $activities = array();
                $dealers = array();

                $query = AgreementModelTable::getInstance()
                    ->createQuery('m')
                    ->select('*')
                    ->leftJoin('m.Report r')
                    ->where('m.status = ?', array('accepted'))
                    ->orderBy('m.activity_id ASC');

                if (!empty($dealer) && $dealer != -1) {
                    $query->andWhere('m.dealer_id = ?', $dealer);
                }

                $query->andWhere('m.created_at LIKE ?', $year . '%');
                if (!empty($activity) && $activity != -1) {
                    $query->andWhere('m.activity_id = ?', $activity);
                }

                if ($onlyAccepted) {
                    $query->andWhere('r.status = ?', 'accepted');
                }

                if ($quarter != 0 && !$onlyAccepted) {
                    $query->andWhere('quarter(m.created_at) = ?', $quarter);
                }

                $result = $query->execute();
                foreach ($result as $item) {
                    if ($onlyAccepted && $quarter > 0) {
                        //$itemQuarter = D::getQuarter(D::calcQuarterData($item->getReport()->getUpdatedAt()));
                        $itemQuarterDate = Utils::getModelDateFromLogEntryWithYear($item->getId());
                        if (is_null($itemQuarterDate)) {
                            continue;
                        }

                        $itemQuarter = D::getQuarter($itemQuarterDate);
                        if ($quarter != $itemQuarter) {
                            continue;
                        }
                    }

                    $activity = $this->normalize($item->getActivity()->getName());
                    $dealer = $this->normalize($item->getDealer()->getName());

                    if (!in_array($activity, $activities)) {
                        $dealers[] = $activity;

                        $zip->addEmptyDir($activity);
                    }

                    if (!is_null($fTypes)) {
                        $fTypes = array_filter(array_map(function ($item) {
                            return $item != -1 ? $item : null;
                        }, $fTypes));
                    }

                    $files_list = $this->getModelFiles($item, $fTypes == -1 ? null : $fTypes);
                    foreach ($files_list as $file) {
                        $fileInfo = sprintf('[%s] %s', $item->getId(), $file['file']);

                        $zip->addFile(sfConfig::get('sf_upload_dir') . $this->filesDir . DIRECTORY_SEPARATOR . $file['file'], $activity . '/' . $dealer . '/' . $file['file_ext'] . '/' . $fileInfo);
                    }
                }

                $res = $zip->close();
            }
        }

        if ($res && count($result) > 0) {
            $this->redirect('/uploads/makets.zip');
            $this->status = true;
        } else
            $this->status = false;


        $this->dealers = DealerTable::getVwDealersQuery()->execute();
        $this->activities = ActivityTable::getInstance()
            ->createQuery()
            ->orderBy('position ASC')
            ->execute();
        $this->filesTypes = $this->getFilesTypes($year);

    }

    /**
     * @param AgreementModel $item
     * @param null $file_ext
     * @return array
     */
    private function getModelFiles(AgreementModel $item, $file_ext = null)
    {
        $files_list = array();
        $record_files = array();

        /**
         * Make backward compatibility with old models
         */
        $model_file_ext = pathinfo($item->getModelFile(), PATHINFO_EXTENSION);
        if (!empty($model_file_ext)) {
            return $this->getModelFilesOld($item, $file_ext);
        }

        if ($item->isModelScenario()) {
            $model_files = $item->getModelUploadedFiles(AgreementModel::BY_SCENARIO);
            $record_files = $item->getModelUploadedFiles(AgreementModel::BY_RECORD);
        } else {
            $model_files = $item->getModelUploadedFiles();
        }

        if (count($model_files)) {
            foreach ($model_files as $model_file) {
                $file_data = $this->makeFileData($model_file, $file_ext);
                if (!is_null($file_data)) {
                    $files_list[] = $file_data;
                }
            }
        }

        if (count($record_files)) {
            foreach ($record_files as $record_file) {
                $file_data = $this->makeFileData($record_file, $file_ext);
                if (!is_null($file_data)) {
                    $files_list[] = $file_data;
                }
            }
        }

        return $files_list;
    }

    private function getModelFilesOld($item, $file_ext = null) {
        $files_list = array();

        $model_file_ext = pathinfo($item->getModelFile(), PATHINFO_EXTENSION);
        $record_model_file_ext = pathinfo($item->getModelRecordFile(), PATHINFO_EXTENSION);

        if (!is_null($file_ext) && !empty($file_ext))
        {
            if (in_array($model_file_ext, $file_ext)  && $item->getModelFile()) {
                $files_list[] = array('file_ext' => $model_file_ext, 'file' => $item->getModelFile());
            }

            if (in_array($record_model_file_ext, $file_ext) && $item->getModelRecordFile()) {
                $files_list[] = array('file_ext' => $record_model_file_ext, 'file' => $item->getModelRecordFile());
            }
        } else {
            if ($item->getModelFile()) {
                $files_list[] = array('file_ext' => $model_file_ext, 'file' => $item->getModelFile());
            }

            if ($item->getModelRecordFile()) {
                $files_list[] = array('file_ext' => $record_model_file_ext, 'file' => $item->getModelRecordFile());
            }
        }

        for ($i = 1; $i <= sfConfig::get('app_max_files_upload_count'); $i++ )
        {
            $model_file_f = 'getModelFile'.$i;
            $model_record_file_f = 'getModelRecordFile'.$i;

            $model_file_ext = pathinfo($item->$model_file_f(), PATHINFO_EXTENSION);
            $model_record_file_ext = pathinfo($item->$model_record_file_f(), PATHINFO_EXTENSION);

            if (!is_null($file_ext) && !empty($file_ext)) {
                if (!empty($model_file_ext) && in_array($model_file_ext, $file_ext)) {
                    $files_list[] = array('file_ext' => $model_file_ext, 'file' => $item->$model_file_f());
                }

                if (!empty($model_record_file_ext) && in_array($file_ext, $model_record_file_ext)) {
                    $files_list[] = array('file_ext' => $model_record_file_ext, 'file' => $item->$model_record_file_f());
                }
            } else {
                if (!empty($model_file_ext) ) {
                    $files_list[] = array('file_ext' => $model_file_ext, 'file' => $item->$model_file_f());
                }

                if (!empty($model_record_file_ext)) {
                    $files_list[] = array('file_ext' => $model_record_file_ext, 'file' => $item->$model_record_file_f());
                }
            }
        }

        return $files_list;
    }

    /**
     * @param $item
     * @param null $extension_by_filter
     * @return array|null
     */
    private function makeFileData(AgreementModelReportFiles $item, $extension_by_filter = null) {
        $file_extension = pathinfo($item->getFile(), PATHINFO_EXTENSION);

        if (!is_null($extension_by_filter) && !empty($extension_by_filter)) {
            if (!empty($file_extension) && in_array($file_extension, $extension_by_filter)) {
                return array('file_ext' => $file_extension, 'file' => $item->getFile());
            }
        } else {
            if (!empty($file_extension) ) {
                return array('file_ext' => $file_extension, 'file' => $item->getFile());
            }
        }

        return null;
    }

    function getFilesTypes($year)
    {
        if (empty($year)) {
            $year = date('Y');
        }

        $types = array();
        $query = AgreementModelReportFilesTable::getInstance()->createQuery()
            ->select('file')
            ->where('file_type = ? or file_type = ? or file_type = ?',
                array
                (
                    AgreementModel::UPLOADED_FILE_MODEL,
                    AgreementModel::UPLOADED_FILE_SCENARIO_TYPE,
                    AgreementModel::UPLOADED_FILE_RECORD_TYPE
                )
            );
        $query->andWhere('created_at LIKE ?', $year . '%');

        $result = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        foreach ($result as $res) {
            $fileExt = pathinfo($res['file'], PATHINFO_EXTENSION);
            if (strpos($res['file'], 'http') === FALSE && strpos($res['file'], 'https') === FALSE && !empty($fileExt)) {
                if (!in_array($fileExt, $types))
                    $types[] = $fileExt;
            }
        }
        sort($types);

        return $types;
    }

    function executeShow(sfWebRequest $request)
    {

        $this->setTemplate('index');
    }


    function normalize($name)
    {
        $str = '';
        $name = mb_strtolower($this->toUtf8($name), 'UTF-8');

        for ($n = 0, $len = mb_strlen($name, 'UTF-8'); $n < $len; $n++) {
            $new_sym = $sym = mb_substr($name, $n, 1, 'UTF-8');
            if (!$this->isSymEnabled($sym)) {
                $new_sym = $this->symToTranslit($sym);
                if (!$new_sym)
                    $new_sym = '_';
            }

            $str .= $new_sym;
        }

        return $str;
    }

    function isSymEnabled($sym)
    {
        $enabled = 'abcdefghijklmnopqrstuvwxyz0123456789';
        return mb_strpos($enabled, $sym, 0, 'UTF-8') !== false;
    }

    function symToTranslit($sym)
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

    function toUtf8($name)
    {
        return mb_convert_encoding($name, 'UTF-8', 'UTF-8,CP1251,ASCII');
    }

}
