<?php

/**
 * AgreementModelReport
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class AgreementModelReport extends BaseAgreementModelReport
{
    const FINANCIAL_DOCS_FILE_PATH = 'activities/module/agreement/report/financial';
    const ADDITIONAL_FILE_PATH = 'activities/module/agreement/report/additional';
    const AGREEMENT_COMMENTS_FILE_PATH = 'activities/module/agreement/report/agreement_comments_file';

    const UPLOADED_FILE_REPORT = 'report';
    const UPLOADED_FILE_TYPE_REPORT = 'report';
    const UPLOADED_FILE_FINANCIAL = 'report_financial';
    const UPLOADED_FILE_ADDITIONAL = 'report_additional';

    protected $declines = null;

    function setUp()
    {
        parent::setUp();

        $this->addListener(new UploadHelper('financial_docs_file', self::FINANCIAL_DOCS_FILE_PATH));
        $this->addListener(new UploadHelper('additional_file', self::ADDITIONAL_FILE_PATH));
        $this->addListener(new UploadHelper('agreement_comments_file', self::AGREEMENT_COMMENTS_FILE_PATH));
    }

    function cancelSpecialistSending()
    {
        AgreementModelReportCommentTable::getInstance()
            ->createQuery()
            ->delete()
            ->where('report_id=?', $this->getId())
            ->execute();

        //Убираем спец. согласование по отчету
        if ($this->getModel()->isConcept() && $this->getModel()->getActivity()->getAllowSpecialAgreement()) {
            SpecialAgreementConceptStatuses::deleteStatuses($this->getModel()->getId());
        }
    }

    function countWaitingSpecialists()
    {
        if ($this->getStatus() != 'wait_specialist')
            return 0;

        return AgreementModelReportCommentTable::getInstance()
            ->createQuery()
            ->where('report_id=? and status=?', array($this->getId(), 'wait'))
            ->count();
    }

    function countDeclines()
    {
        if ($this->getStatus() != 'declined')
            return 0;

        if ($this->declines === null) {
            $this->declines = AgreementModelReportCommentTable::getInstance()
                ->createQuery()
                ->where('report_id=? and status=?', array($this->getId(), 'declined'))
                ->count();
        }
        return $this->declines;
    }

    /**
     * Returns a file name helper for financial documents
     *
     * @return FileNameHelper
     */
    function getFinancialDocsFileNameHelper()
    {
        return new FileNameHelper(sfConfig::get('sf_upload_dir') . '/' . self::FINANCIAL_DOCS_FILE_PATH . '/' . $this->getFinancialDocsFile());
    }

    /**
     * Returns a file name helper for financial documents
     *
     * @return FileNameHelper
     */
    function getFinancialDocsFileNameHelperByName($name)
    {
        return new FileNameHelper(sfConfig::get('sf_upload_dir') . '/' . self::FINANCIAL_DOCS_FILE_PATH . '/' . $name);
    }

    /**
     * Returns a file name helper for an additional file
     *
     * @return FileNameHelper
     */
    function getAdditionalFileNameHelperByName($name)
    {
        return new FileNameHelper(sfConfig::get('sf_upload_dir') . '/' . self::ADDITIONAL_FILE_PATH . '/' . $name);
        //return new FileNameHelper(sfConfig::get('sf_upload_dir').'/'.self::ADDITIONAL_FILE_PATH.'/'.$this->getAdditionalFile());
    }

    /**
     * Returns a file name helper for an additional file
     *
     * @return FileNameHelper
     */
    function getAdditionalFileNameHelper($func = null)
    {
        if (empty($func))
            $func = "getAdditionalFile";

        return new FileNameHelper(sfConfig::get('sf_upload_dir') . '/' . self::ADDITIONAL_FILE_PATH . '/' . $this->$func());
        //return new FileNameHelper(sfConfig::get('sf_upload_dir').'/'.self::ADDITIONAL_FILE_PATH.'/'.$this->getAdditionalFile());
    }

    /**
     * Returns a file name helper for a comments file
     *
     * @return FileNameHelper
     */
    function getAgreementCommentsFileNameHelper()
    {
        return new FileNameHelper(sfConfig::get('sf_upload_dir') . '/' . self::AGREEMENT_COMMENTS_FILE_PATH . '/' . $this->getAgreementCommentsFile());
    }

    /**
     * Returns specialist comment
     *
     * @param User $user
     * @return AgreementModelComment|false
     */
    function getSpecialistComment(User $user)
    {
        return AgreementModelReportCommentTable::getInstance()
            ->createQuery()
            ->where('report_id=? and user_id=?', array($this->getId(), $user->getId()))
            ->fetchOne();
    }

    function postSave($event)
    {
        $model = $this->getModel();
        $model->setWait($this->getStatus() == 'wait');
        $model->setWaitSpecialist($this->getStatus() == 'wait_specialist');
        $model->save();
    }

    /**
     * @param $file_id
     * @param $file_name
     * @return bool
     * @internal param AgreementModelReportFiles $file
     * @internal param $user
     */
    function inFavoritesFile($file_id, $file_name)
    {
        $added = AgreementModelReportFavoritesTable::getInstance()
            ->createQuery()
            ->where('report_id = ? and file_id = ?', array($this->getId(), $file_id))->count() > 0 ? true : false;

        if (!$added) {
            $added = AgreementModelReportFavoritesTable::getInstance()
                ->createQuery()
                ->where('report_id = ? and file_name = ?', array($this->getId(), $file_name))->count() > 0 ? true : false;
        }

        return $added;
    }

    public function getUploadedFilesCount()
    {
        return array
        (
            self::UPLOADED_FILE_ADDITIONAL => AgreementModelReportFilesTable::getUploadedFilesListBy($this->getId(), self::UPLOADED_FILE_REPORT, self::UPLOADED_FILE_ADDITIONAL, true),
            self::UPLOADED_FILE_FINANCIAL => AgreementModelReportFilesTable::getUploadedFilesListBy($this->getId(), self::UPLOADED_FILE_REPORT, self::UPLOADED_FILE_FINANCIAL, true),
        );
    }

    public function getUploadedFilesList($by_type) {
        return AgreementModelReportFilesTable::getUploadedFilesListBy($this->getId(), self::UPLOADED_FILE_REPORT, $by_type);
    }

    public function getSortedUploadedFilesList(Closure $callback, $by_type)
    {
        $uploaded_files_list = $this->getUploadedFilesList($by_type);

        $img_result = array();
        $files_result = array();

        foreach ($uploaded_files_list as $file) {
            if ($file->isImage()) {
                $img_result[] = $file;
            } else {
                $files_result[] = $file;
            }
        }

        $callback(array_merge($img_result, $files_result));
    }

    public function getUploadedFilesSchemaByType($label = null) {
        $model_type = $this->getModel()->getModelType();

        if ($this->getModel()->isConcept()) {
            return array
            (
                self::UPLOADED_FILE_FINANCIAL => array
                (
                    'type' => self::UPLOADED_FILE_REPORT,
                    'show' => true,
                    'label' => !is_null($label) && isset($label[1]) ? $label[1] : 'Отчёт по концепции'
                ),
            );
        } else {
            return array
            (
                self::UPLOADED_FILE_ADDITIONAL => array
                (
                    'type' => self::UPLOADED_FILE_REPORT,
                    'show' => true,
                    'label' => !is_null($label) && isset($label[0]) ? $label[0] : $model_type->getReportFieldDescription(), //'Фотоотчет'
                    'allow_add_to_favorites' => true
                ),
                self::UPLOADED_FILE_FINANCIAL => array
                (
                    'type' => self::UPLOADED_FILE_REPORT,
                    'show' => true,
                    'label' => !is_null($label) && isset($label[1]) ? $label[1] : 'Финансовые документы',
                    'allow_add_to_favorites' => false
                ),
            );
        }
    }

    /**
     * Проверка на отклонение заявки
     * @return int
     */
    public function getLastLogAction() {
        return LogEntryTable::getInstance()->createQuery()->where('object_id = ? and object_type = ? and action = ?', array($this->getModel()->getId(), 'agreement_report', 'declined'))->count();
    }
}
