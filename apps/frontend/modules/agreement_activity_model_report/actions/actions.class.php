<?php

/**
 * agreement_activity_model_report actions.
 *
 * @package    Servicepool2.0
 * @subpackage agreement_activity_model_report
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class agreement_activity_model_reportActions extends BaseActivityActions
{
    protected $check_for_module = 'agreement';

    const MAX_FILES = 10;

    function executeEdit ( sfWebRequest $request )
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url', 'Asset', 'Tag' ));

        $report = $this->getReport($request);
        if ($report) {
            $model = $report->getModel();

            $report_additional_uploaded_files = AgreementModelReportFilesTable::getUploadedFilesListBy($report->getId(), AgreementModel::UPLOADED_FILE_REPORT, AgreementModel::UPLOADED_FILE_ADDITIONAL_FILE_TYPE, false);
            $report_financial_uploaded_files = AgreementModelReportFilesTable::getUploadedFilesListBy($report->getId(), AgreementModel::UPLOADED_FILE_REPORT, AgreementModel::UPLOADED_FILE_FINANCIAL_FILE_TYPE, false);

            $result = array(
                'success' => true,
                'values' => array(
                    'id' => $report->getModelId(),
                    'report_id' => $report->getId(),
                    'is_concept' => $report->getModel()->isConcept(),
                    'status' => $report->getStatus(),
                    'css_status' => $report->getModel()->getReportCssStatus(),
                    'model_status' => $report->getModel()->getStatus(),
                    'additional_file_description' => $report->getModel()->getModelType()->getReportFieldDescription(),
                    'cost' => $model->getStatus() == 'accepted' && $report->getId() ? $model->getCost() : '',
                    'isOutOfDate' => $model->getIsBlocked() && !$model->getAllowUseBlocked(),
                    'places_count' => $model->getModelTypePlacesCount(),
                    'report_additional_uploaded_files_count' => count($report_additional_uploaded_files),
                    'report_financial_uploaded_files_count' => count($report_financial_uploaded_files)
                )
            );
        } else {
            $result = array(
                'success' => false,
                'error' => 'not_found'
            );
        }

        return $this->sendJson($result);
    }

    function getModelFieldValue ( AgreementModel $model, AgreementModelField $field )
    {
        return AgreementModelValueTable::getInstance()->createQuery()->select()->where('model_id = ? and field_id = ?', array( $model->getId(), $field->getId() ))->fetchOne();
    }


    function executeUpdate ( sfWebRequest $request )
    {
        $report = $this->getReport($request);
        if (!$report) {
            return $this->sendJson(array( 'success' => false, 'error' => 'not_found' ), 'agreement_model_report_form.onResponse');
        }

        $model = $report->getModel();
        if ($report->getModel()->getStatus() != 'accepted' || $report->getStatus() != 'not_sent' && $report->getStatus() != 'declined') {
            return $this->sendJson(array( 'success' => false, 'error' => 'wrong_status' ), 'agreement_model_report_form.onResponse');
        }

        $form = new AgreementModelReportForm($report);

        $cost = $request->getParameter('cost');
        $required_financial = ( is_numeric($cost) && floatval($cost) ) || $report->getModel()->isConcept();

        /**
         * Work with additional files block
         */
        $upload_files_add_ids = $request->getPostParameter('upload_files_additional_ids');
        if (empty($upload_files_add_ids) && !$report->getModel()->isConcept()) {
            $uploaded_files_list = $report->getUploadedFilesList(AgreementModelReport::UPLOADED_FILE_ADDITIONAL);
            if (count($uploaded_files_list) == 0) {
                $form->getValidator('is_valid_add_data')->setOption('required', true);
            }
        }

        /**
         * Work with financial files block
         */
        $upload_files_fin_ids = $request->getPostParameter('upload_files_financial_ids');

        if (empty($upload_files_fin_ids) && $required_financial) {
            $uploaded_files_list = $report->getUploadedFilesList(AgreementModelReport::UPLOADED_FILE_FINANCIAL);
            if (count($uploaded_files_list) == 0) {
                $form->getValidator('is_valid_fin_data')->setOption('required', true);
            }
        }

        $form->bind(
            array(
                'model_id' => $report->getModelId(),
                'status' => 'wait',
                'cost' => $cost
            ),
            array()
        );

        $message = null;

        if ($form->isValid()) {
            $form->save();

            $model = $form->getObject()->getModel();
            $model->setReport($form->getObject());
            $model->setCost($cost);

            $model->setIsBlocked(false);
            $model->setAllowUseBlocked(false);
            $model->setUseBlockedTo('');
            $model->save();

            $saved_add_files = $this->saveReportFiles($report, $request, 'upload_files_additional_ids', AgreementModelReport::ADDITIONAL_FILE_PATH, AgreementModelReport::UPLOADED_FILE_ADDITIONAL);
            $saved_fin_files = $this->saveReportFiles($report, $request, 'upload_files_financial_ids', AgreementModelReport::FINANCIAL_DOCS_FILE_PATH, AgreementModelReport::UPLOADED_FILE_FINANCIAL);

            /**
             * Set model discussion messages statuses to none
             * Must do this when update out model status
             */
            $model->nullDiscussionMessagesStatuses();

            /**
             * Save additional / financial files to report
             */
            if (!empty($saved_add_files) && !$report->getAdditionalFile()) {
                $report->setAdditionalFile($saved_add_files[ 0 ][ 'gen_file_name' ]);
            }

            if (!empty($saved_fin_files) && !$report->getFinancialDocsFile()) {
                $report->setFinancialDocsFile($saved_fin_files[ 0 ][ 'gen_file_name' ]);
            }
            $report->save();

            $special_agreement = false;
            if ($model->isConcept() && $model->getActivity()->getAllowSpecialAgreement()) {
                $entry = LogEntryTable::getInstance()->addEntry(
                    $this->getUser()->getAuthUser(),
                    'agreement_special_concept_report_regional_manager',
                    'edit',
                    $model->getActivity()->getName() . '/' . $model->getName(),
                    'Отчёт отправлен на согласование региональному менеджеру',
                    'clip',
                    $model->getDealer(),
                    $model->getId(),
                    'agreement'
                );
                $special_agreement = true;

                //Отправка письма импортеру привязанного к активности со спец. согласованием
                $users_ids = array_map(function($item) {
                    return $item['user_id'];
                },
                    ActivityAgreementByUserTable::getInstance()
                        ->createQuery()
                        ->where('activity_id = ?', $model->getActivityId())
                        ->execute(array(), Doctrine_Core::HYDRATE_ARRAY)
                );

                if (!empty($users_ids)) {
                    foreach ($users_ids as $user_id) {
                        $agreement_status = AgreementModelImporterTable::getInstance()->createQuery()->where('user_id = ? and model_id = ?', array( $user_id, $model->getId() ))->fetchOne();
                        if (!$agreement_status) {
                            $agreement_status = new AgreementModelImporter();
                            $agreement_status->setArray(array(
                                'user_id' => $user_id,
                                'model_id' => $model->getId(),
                                'status' => 'system'
                            ));
                            $agreement_status->save();
                        } else {
                            $agreement_status->setStatus('system');
                            $agreement_status->save();
                        }
                    }
                }
            }

            if ($model->isConcept() && $model->getActivity()->getAllowAgreementByOneUser()) {
                $entry = LogEntryTable::getInstance()->addEntry(
                    $this->getUser()->getAuthUser(),
                    'agreement_concept_report_by_importer',
                    'edit',
                    $model->getActivity()->getName() . '/' . $model->getName(),
                    'Отчёт отправлен на согласование импортеру',
                    'clip',
                    $model->getDealer(),
                    $model->getId(),
                    'agreement'
                );
                $special_agreement = true;
            }

            if (!$special_agreement) {
                $entry = LogEntryTable::getInstance()->addEntry(
                    $this->getUser()->getAuthUser(),
                    $model->isConcept() ? 'agreement_concept_report' : 'agreement_report',
                    'edit',
                    $model->getActivity()->getName() . '/' . $model->getName(),
                    'Отчёт отправлен на согласование',
                    'clip',
                    $model->getDealer(),
                    $model->getId(),
                    'agreement'
                );
            }

            $model->createPrivateLogEntryForSpecialists($entry);

            $message = $this->addMessageToDiscussion($model, 'Отчёт отправлен на согласование', Message::MSG_STATUS_SENDED);

            $this->attachFinancialFilesDocsToMessage($report, $message);
            $this->attachAdditionalFilesToMessage($report, $message);

            //Отправка писем только импортеру
            if ($model->isConcept() && $model->getActivity()->getAllowAgreementByOneUser()) {
                $this->sendMailToUsersBindedToActivityByModel($model, $entry, 'ActivityAgreementByUserTable');
            }

            if ($model->isConcept() && $model->getActivity()->getAllowSpecialAgreement()) {
                $this->sendToManagerImporter($model, null, array( SpecialAgreementConceptStatuses::AGREEMENT_REPORT_STATUS_REG_MANAGER_WAIT ));

                //Отправка письма рег. менеджеру привязанного к дилеру
                $dealer_manager = UserTable::getInstance()->find($model->getDealer()->getRegionalManager()->getRegionalManagerId());
                if ($dealer_manager) {
                    //Отправка письма Региональному менеджеру
                    AgreementManagementHistoryMailSender::sendSpecial(
                        'AgreementSendReportMail',
                        $entry,
                        array( $dealer_manager->getId() )
                    );
                }
            } else {
                AgreementManagementHistoryMailSender::send(
                    'AgreementSendReportMail',
                    $entry,
                    false,
                    false,
                    $model->isConcept() ? AgreementManagementHistoryMailSender::NEW_AGREEMENT_CONCEPT_REPORT_NOTIFICATION : AgreementManagementHistoryMailSender::NEW_AGREEMENT_REPORT_NOTIFICATION
                );
            }
        }

        $message_data = null;
        /*if ($message) {
            $message_data = Utils::formatMessageData($message);
        }*/

        return $model->isValidModelCategory() ?
            $this->sendFormBindResult($form, 'agreement_model_report_with_category_form.onResponse', '', $message_data) :
            $this->sendFormBindResult($form, 'agreement_model_report_form.onResponse', '', $message_data);
    }

    /**
     * Получить список пользователей привязанных к активности
     * @param $model
     * @param $entry
     * @param $cls
     */
    private function sendMailToUsersBindedToActivityByModel ( $model, $entry, $cls )
    {
        $users_ids = array_map(function ( $item ) {
            return $item[ 'user_id' ];
        },
            $cls::getInstance()
                ->createQuery()
                ->where('activity_id = ?', $model->getActivityId())
                ->execute(array(), Doctrine_Core::HYDRATE_ARRAY)
        );

        if (!empty($users_ids)) {
            AgreementManagementHistoryMailSender::sendSpecial(
                'AgreementReportSentToSpecialistMail',
                $entry,
                //array($model->getDealer()->getRegionalManagerId())
                $users_ids
            );
        }
    }

    /**
     * @param $model
     * @param $entry
     * @param int $mode
     */
    private
    function sendToManagerImporter ( $model, $entry, $mode = null )
    {
        //Работа со статусами при работе с концепцией
        //Если рег. менеджер согласовал концепцию, в согласовании он не участвует
        //Если импортер согласовал, а рег. менеджер отклонил, письма ипортеру приодят такжеs
        if (is_array($mode)) {
            foreach ($mode as $mode_item) {
                SpecialAgreementConceptStatuses::setStatus($model, $mode_item);
            }
        } else {
            SpecialAgreementConceptStatuses::setStatus($model, $mode);
        }
    }

    function executeCancel ( sfWebRequest $request )
    {
        $report = $this->getReport($request);
        if ($report) {
            $report->setStatus('not_sent');
            $report->save();

            $model = $report->getModel();

            RealBudgetTable::getInstance()->removeByObjectOnly(ActivityModule::byIdentifier('agreement'), $model->getId());

            $entry = LogEntryTable::getInstance()->addEntry(
                $this->getUser()->getAuthUser(),
                $model->isConcept() ? 'agreement_concept_report' : 'agreement_report',
                'cancel',
                $model->getActivity()->getName() . '/' . $model->getName(),
                'Отменена отправка отчёта на согласование',
                '',
                $model->getDealer(),
                $model->getId(),
                'agreement'
            );

            $model->createPrivateLogEntryForSpecialists($entry);

            $report->cancelSpecialistSending();

            $message = $this->addMessageToDiscussion($model, 'отменена отправка отчёта на согласование');

            AgreementManagementHistoryMailSender::send(
                'AgreementCancelReportMail',
                $entry,
                false,
                false,
                $model->isConcept() ? AgreementManagementHistoryMailSender::NEW_AGREEMENT_CONCEPT_REPORT_NOTIFICATION : AgreementManagementHistoryMailSender::NEW_AGREEMENT_REPORT_NOTIFICATION
            );
        }

        $result = array( 'success' => true );

        $message_data = null;
        /*if ($message) {
            $result['message_data'] = Utils::formatMessageData($message);
        }*/

        return $this->sendJson($result);
    }

    /**
     * Returns a report
     *
     * @param sfWebRequest $request
     * @return AgreementModelReport
     */
    function getReport ( sfWebRequest $request )
    {
        $activity = $this->getActivity($request);
        $dealer = $this->getUser()->getAuthUser()->getDealer();
        $model = AgreementModelTable::getInstance()
            ->createQuery()
            ->where('activity_id=? and dealer_id=? and id=?', array( $activity->getId(), $dealer->getId(), $request->getParameter('id') ))
            ->fetchOne();

        if (!$model)
            return false;

        $report = $model->getReport();

        if ($report->isNew()) {
            $report->setModel($model);
            $report->status = 'not_sent';
        }

        return $report;
    }

    public
    function attachFinancialFilesDocsToMessage ( $report, $message )
    {
        $uploaded_files_list = $report->getUploadedFilesList(AgreementModelReport::UPLOADED_FILE_FINANCIAL);

        foreach ($uploaded_files_list as $file) {
            $this->saveAttachFile($message, $file->getFile(), AgreementModelReport::FINANCIAL_DOCS_FILE_PATH . $file->getPath(), 'fin', $file->getPath());
        }
    }

    public
    function attachAdditionalFilesToMessage ( $report, $message )
    {
        $uploaded_files_list = $report->getUploadedFilesList(AgreementModelReport::UPLOADED_FILE_ADDITIONAL);
        foreach ($uploaded_files_list as $file) {
            $this->saveAttachFile($message, $file->getFile(), AgreementModelReport::ADDITIONAL_FILE_PATH . $file->getPath(), 'add', $file->getPath());
        }
    }

    private
    function saveAttachFile ( $message, $file_name, $path, $label, $file_path = '' )
    {
        $file = new MessageFile();
        $file->setMessageId($message->getId());
        $file->setFile($label . '-' . $message->getId() . '-' . $file_name);
        $file->setPath($file_path);

        $msg_path = sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH;
        if (!empty($file_path)) {
            $msg_path = sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH . $file_path;
            if (!file_exists($msg_path)) {
                mkdir($msg_path, 0777, true);
            }
        }

        copy(
            sfConfig::get('sf_upload_dir') . '/' . $path . '/' . $file_name,
            $msg_path . '/' . $file->getFile()
        );

        $file->save();
    }

    /**
     * Add message to discussion
     *
     * @param AgreementModel $model
     * @param string $text
     * @return Message|false
     */
    protected
    function addMessageToDiscussion ( AgreementModel $model, $text, $msg_status = 'none' )
    {
        $discussion = $model->getDiscussion();

        if (!$discussion)
            return;

        $message = new Message();
        $user = $this->getUser()->getAuthUser();
        $message->setDiscussionId($discussion->getId());
        $message->setUser($user);
        $message->setUserName($user->selectName());
        $message->setText($text);
        $message->setSystem(true);
        $message->setMsgStatus($msg_status);
        $message->save();

        // mark as unread
        $discussion->getUnreadMessages($user);

        return $message;
    }

    public
    function executeDownloadAdditionalFile ( sfWebRequest $request )
    {
        return $this->downloadFile($request->getParameter('file'), AgreementModelReport::ADDITIONAL_FILE_PATH);
    }

    public
    function executeDownloadFinancialFile ( sfWebRequest $request )
    {
        return $this->downloadFile($request->getParameter('file'), AgreementModelReport::FINANCIAL_DOCS_FILE_PATH);
    }

    private
    function downloadFile ( $file, $path )
    {
        if (is_numeric($file)) {
            $file_item = AgreementModelReportFilesTable::getInstance()->find($file);

            $file = ( $file_item->getFileType() == AgreementModelReport::UPLOADED_FILE_FINANCIAL
                    ? AgreementModelReport::FINANCIAL_DOCS_FILE_PATH
                    : AgreementModelReport::ADDITIONAL_FILE_PATH ) . '/' . $file_item->getFileName();

            $filePath = sfConfig::get('app_uploads_path') . '/' . $file;
        } else {
            $filePath = sfConfig::get('app_uploads_path') . '/' . $path . '/' . $file;
        }

        if (file_exists($filePath)) {
            $file = end(explode('/', $filePath));

            if (!F::downloadFile($filePath, $file)) {
                $this->getResponse()->setContentType('application/json');
                $this->getResponse()->setContent(json_encode(array( 'success' => false, 'message' => 'Файл не найден' )));
            }
        }

        return sfView::NONE;
    }

    public
    function executeDownloadUploadedFile ( sfWebRequest $request )
    {
        $file_id = $request->getParameter('file');

        $file_item = AgreementModelReportFilesTable::getInstance()->find($file_id);
        if ($file_item) {
            if ($file_item->getFileType() == AgreementModelReport::UPLOADED_FILE_ADDITIONAL) {
                $path = AgreementModelReport::ADDITIONAL_FILE_PATH;
            } else if ($file_item->getFileType() == AgreementModelReport::UPLOADED_FILE_FINANCIAL) {
                $path = AgreementModelReport::FINANCIAL_DOCS_FILE_PATH;
            }

            $filePath = sfConfig::get('app_uploads_path') . '/' . $path . '/' . $file_item->getFileName();
            if (file_exists($filePath)) {
                $file = end(explode('/', $filePath));

                if (!F::downloadFile($filePath, $file)) {
                    $this->getResponse()->setContentType('application/json');
                    $this->getResponse()->setContent(json_encode(array( 'success' => false, 'message' => 'Файл не найден' )));
                }
            }
        }

        return sfView::NONE;
    }

    public
    function executeLoadAdditionalFinancialDocsFiles ( sfWebRequest $request )
    {
        $this->by_type = $request->getParameter('by_type');

        $this->report = AgreementModelReportTable::getInstance()->find($request->getParameter('id'));
    }

    /**
     * @param $model
     * @param $request
     * @param $param_name
     * @param $path
     * @param string $field_type
     * @return array
     * @internal param $form
     */
    private
    function saveReportFiles ( $model, $request, $param_name, $path, $field_type = 'report_additional_ext' )
    {
        $fileModel = AgreementModelReport::UPLOADED_FILE_REPORT;

        $files_list = TempFileTable::copyFilesByRequest($request, $path, $param_name, $this->getUser()->getAuthUser());
        foreach ($files_list as $file) {
            $record = new AgreementModelReportFiles();
            $record->setArray(
                array(
                    'file' => $file[ 'gen_file_name' ],
                    'object_id' => $model->getId(),
                    'object_type' => $fileModel,
                    'file_type' => $field_type,
                    'user_id' => $this->getUser()->getAuthUser()->getId(),
                    'field' => '',
                    'field_name' => '',
                    'path' => $file[ 'upload_path' ]
                )
            );

            $record->save();
        }

        /**
         * Get already uploaded files
         */
        if ($field_type == AgreementModelReport::UPLOADED_FILE_ADDITIONAL) {
            $uploaded_files = AgreementModelReportFilesTable::getUploadedFilesListBy($model->getId(), AgreementModel::UPLOADED_FILE_REPORT, AgreementModel::UPLOADED_FILE_ADDITIONAL_FILE_TYPE, false);
        } else {
            $uploaded_files = AgreementModelReportFilesTable::getUploadedFilesListBy($model->getId(), AgreementModel::UPLOADED_FILE_REPORT, AgreementModel::UPLOADED_FILE_FINANCIAL_FILE_TYPE, false);
        }

        foreach ($uploaded_files as $file) {
            $files_list[] = array( 'gen_file_name' => $file->getFile(), 'upload_path' => $file->getPath() );
        }

        return $files_list;
    }

    public
    function executeDeleteUploadedAddFinDocsFile ( sfWebRequest $request )
    {
        $file = AgreementModelReportFilesTable::getInstance()->find($request->getParameter('id'));
        if ($file) {
            $file->delete();

            return $this->sendJson(array( 'success' => true ));
        }

        return $this->sendJson(array( 'success' => false ));
    }

    /**
     * Download files by model and model file type
     * @param sfWebRequest $request
     * @throws sfStopException
     */
    public
    function executeDownloadAllFiles ( sfWebRequest $request )
    {
        $report = AgreementModelReportTable::getInstance()->find($request->getParameter('id'));
        $by_type = $request->getParameter('model_file_type');

        $this->redirect(ModelReportFiles::packUploadedFilesToZip($report, $by_type));
    }
}
