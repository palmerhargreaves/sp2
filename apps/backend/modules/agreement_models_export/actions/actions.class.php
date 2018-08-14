<?php

include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Cell.php');
include(sfConfig::get('sf_root_dir') . '/lib/PHPExcel/Writer/Excel5.php');

/**
 * agreement_models_export actions.
 *
 * @package    Servicepool2.0
 * @subpackage deleted_models
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class agreement_models_exportActions extends sfActions
{

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    function executeIndex(sfWebRequest $request)
    {
        $this->dealers = DealerTable::getVwDealersQuery()->execute();
        $this->years = range(date('Y') - 5, date('Y') + 5);
    }

    function executeShow(sfWebRequest $request)
    {
        $this->setTemplate('index');
    }

    public function executeExportData(sfWebRequest $request)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));

        $activity = ActivityTable::getInstance()->find($request->getParameter('activityId'));

        $by_dealer = $request->getParameter('by_dealer');
        $by_year = $request->getParameter('by_year');

        $query = AgreementModelTable::getInstance()->createQuery('am')
            ->where('am.dealer_id = ?', $by_dealer)
            ->innerJoin('am.Report amr')
            ->andWhere('am.status = ? and amr.status = ?', array('accepted', 'accepted'));

        if ($by_year != -1) {
            $query->andWhere('year(created_at) = ?', $by_year);
        }

        $models = $query->execute();

        $gen_file = 'dealer_agreement_models_docs.zip';

        $zip = new ZipArchive();
        $zipFile = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . $gen_file;

        @unlink($zipFile);
        $zip_handler = $zip->open($zipFile, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);

        $files_dir = arraY();
        if ($zip_handler) {

            foreach ($models as $model) {
                $report = $model->getReport();
                if (!$report) {
                    continue;
                }

                $files = array_merge
                (
                    $this->getFilesList($report, 'getAdditionalFile', AgreementModelReport::ADDITIONAL_FILE_PATH, 2, $max = 7, 'add', 'getAdditionalFile'),
                    $this->getFilesList($report, 'getAdditionalFileExt', AgreementModelReport::ADDITIONAL_FILE_PATH, 1, 10, 'add'),
                    $this->getFilesList($report, 'getFinancialDocsFile', AgreementModelReport::FINANCIAL_DOCS_FILE_PATH, 1, 10, 'fin', 'getFinancialDocsFile')
                );

                if (count($files) > 0) {
                    $current_dir = D::getYear($model->getCreatedAt());
                    if (!in_array($current_dir, $files_dir)) {
                        $files_dir[] = $current_dir;

                        $zip->addEmptyDir($current_dir);
                    }

                    foreach ($files as $file) {
                        $info = pathinfo($file['file']);
                        $fileInfo = sprintf('[%s] %s.%s', $report->getId(), $info['filename'], $info['extension']);

                        $zip->addFile($file['file'], D::getYear($model->getCreatedAt()) . '/'. $model->getId() . '/' . $file['label'] . '/' . $fileInfo);
                    }
                }

            }
            $zip->close();

            return sfView::NONE;
        }
    }

    private function getFilesList($report, $func, $path, $from = 1, $max = 10, $label = '', $ext_func = '')
    {
        $files_list = array();

        if (!empty($ext_func)) {
            $file = $report->$ext_func();
            if (!empty($file)) {
                $files_list[] = array('file' => sfConfig::get('app_uploads_path') . '/' . $path . '/' . $file, 'label' => $label);
            }
        }

        for($ind = $from; $ind <= $max; $ind++) {
            $gen_func = $func.$ind;
            $file = $report->$gen_func();
            if (!empty($file)) {
                $files_list[] = array('file' => sfConfig::get('app_uploads_path') . '/' . $path . '/' . $file, 'label' => $label);

            }
        }

        return $files_list;
    }
}
