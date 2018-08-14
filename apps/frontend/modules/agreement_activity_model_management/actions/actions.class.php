<?php

include(sfConfig::get('sf_root_dir') . '/lib/dompdf/dompdf_config.inc.php');

ini_set('memory_limit', '1000M');

/**
 * agreement_activity_management actions.
 *
 * @package    Servicepool2.0
 * @subpackage agreement_activity_management
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class agreement_activity_model_managementActions extends ActionsWithJsonForm
{
    const SORT_ATTR = 'man_sort';
    const SORT_DIRECT_ATTR = 'man_sort_direct';
    const FILTER_NAMESPACE = 'agreement_filter_main';
    const FILTER_NAMESPACE_FAVORITES = 'agreement_filter_favorites';

    const MAX_MODEL_RECORDS_FILES = 10;
    const LIMIT_MODELS_COUNT = 50;

    const IMAGE_WIDTH = 1024;

    const DECLINE_MODEL_ACTION = 'decline_model';
    const DECLINE_REPORT_ACTION = 'decline_report';

    protected $_dealer_filter = null;
    protected $_activity_filter = null;
    protected $_designer_filter = null;

    protected $_favorites_dealer_filter = null;
    protected $_favorites_activity_filter = null;
    protected $_favorites_activity_finished_filter = null;
    protected $_favorites_model_type_filter = null;

    private $isReset = false;

    private $pager = null;

    function executeIndex(sfWebRequest $request)
    {
        $this->resetModelFilterByOffset();

        $this->getYearFilter($request);

        //Show models list by filter (manager or designer)
        if ($this->getDesignerFilter() && $this->getWaitFilter() == 'specialist') {
            $this->outputDesignerModels($request);
        } else {
            $this->outputModels($request);
        }

        $this->outputConcepts($request);
        $this->outputDeclineReasons();
        $this->outputDeclineReportReasons();
        $this->outputSpecialistGroups();

        $this->outputFilter();
    }

    function executeSort(sfWebRequest $request)
    {
        $column = $request->getParameter('sort', 'id');
        $cur_column = $this->getSortColumn();
        $direction = $this->getSortDirection();

        if ($column == $cur_column) {
            $direction = !$direction;
        } else {
            $direction = false;
            $cur_column = $column;
        }

        $this->setSortColumn($cur_column);
        $this->setSortDirection($direction);

        $this->redirect('@agreement_module_management_models');
    }

    function executeModel(sfWebRequest $request)
    {
        $model = $this->getModel($request);
        if (!$model)
            return sfView::ERROR;

        $this->outputSpecialistGroups();

        $this->model = $model;
    }

    function executeReport(sfWebRequest $request)
    {
        $model = $this->getModel($request);
        if (!$model)
            return sfView::ERROR;

        $this->report = $model->getReport();
        $this->model = $model;

        $this->outputSpecialistGroups();
    }

    function executeDeclineModel(sfWebRequest $request)
    {
        $model = $this->getModel($request);
        $this->forward404Unless($model);

        $form = new AgreementDeclineForm(array(), array(
            'comments_file_path' => AgreementModel::AGREEMENT_COMMENTS_FILE_PATH,
            //'reason_model' => 'AgreementDeclineReason'
        ));

        $form->bind(
            array(
                //'decline_reason_id' => $request->getPostParameter('decline_reason_id'),
                'agreement_comments' => $request->getPostParameter('agreement_comments'),
                'designer_approve' => $request->getPostParameter('designer_approve')
            ),
            array()//$this->getModelCommentsFiles($request, 'agreement_comments_file')
        );

        $send_to_specialist = $form->getValue('designer_approve');
        if ($send_to_specialist) {
            return $this->executeSendModelToSpecialists($request, $form, 'decline');
        }

        if ($form->isValid()) {
            $accept_utils = new AgreementModelAcceptByManager(array(
                'model' => $model,
                'user' => $this->getUser()->getAuthUser(),
                'request' => $request,
                'form' => $form
            ));
            $message = $accept_utils->agreementManagerDecline();

            $message_data = null;
            /*if ($message) {
                $message_data = Utils::formatMessageData($message);
            }*/
        }

        return $this->sendFormBindResult($form, 'window.decline_model_form.onResponse');
    }

    function executeAcceptModel(sfWebRequest $request)
    {
        $action_type = $request->getParameter('action_type');
        if ($action_type == self::DECLINE_MODEL_ACTION) {
            return $this->executeDeclineModel($request);
        }

        $model = $this->getModel($request);
        $this->forward404Unless($model);

        $form = new AgreementAcceptForm(array(),
            array(
                'comments_file_path' => AgreementModel::AGREEMENT_COMMENTS_FILE_PATH,
            )
        );

        $form->bind(
            array(
                'agreement_comments' => $request->getPostParameter('agreement_comments'),
                'designer_approve' => $request->getPostParameter('designer_approve')
            ),
            $this->getModelCommentsFiles($request, 'agreement_comments_file')
        );

        $send_to_specialist = $form->getValue('designer_approve');
        if ($send_to_specialist) {
            return $this->executeSendModelToSpecialists($request, $form, 'accept');
        }

        if ($form->isValid()) {
            $accept_utils = new AgreementModelAcceptByManager(array(
                'model' => $model,
                'user' => $this->getUser()->getAuthUser(),
                'request' => $request,
                'form' => $form
            ));
            $result = $accept_utils->agreementManagerAccept();

            if (!empty($result)) {

                if (is_array($result)) {
                    return $this->sendFormBindResult($result['form'], $result['response']);
                }

                $message_data = null;
                /*if ($result) {
                    $message_data = Utils::formatMessageData($result);
                }*/
            }
        }

        return $this->sendFormBindResult($form, 'window.accept_decline_form.onResponse');

        //return $this->sendFormBindResult($form);
    }

    function executeDeclineReport(sfWebRequest $request)
    {
        $model = $this->getModel($request);
        $this->forward404Unless($model);

        $form = new AgreementDeclineForm(array(), array(
            'comments_file_path' => AgreementModelReport::AGREEMENT_COMMENTS_FILE_PATH,
            //'reason_model' => 'AgreementDeclineReportReason'
        ));
        $form->bind(
            array(
                //'decline_reason_id' => $request->getPostParameter('decline_reason_id'),
                'agreement_comments' => $request->getPostParameter('agreement_comments'),
                'designer_approve' => $request->getPostParameter('designer_approve')
            ),
            array() //$this->getModelCommentsFiles($request, 'agreement_comments_file')
        );

        /*$model->setManagerStatus('wait');
        $model->setDesignerStatus('wait');
        $model->save();*/

        $send_to_specialist = $form->getValue('designer_approve');
        if ($send_to_specialist) {
            return $this->executeSendReportToSpecialists($request, $form, 'decline');
        }

        if ($form->isValid()) {
            $accept_report_utils = new AgreementModelReportAcceptByManager(array(
                'model' => $model,
                'report' => $model->getReport(),
                'request' => $request,
                'form' => $form,
                'user' => $this->getUser()->getAuthUser()
            ));

            $message = $accept_report_utils->agreementManagerDecline();
        }

        $message_data = null;
        /*if ($message) {
            $message_data = Utils::formatMessageData($message);
        }*/

        return $this->sendFormBindResult($form, 'window.decline_report_form.onResponse');
    }

    function executeAcceptReport(sfWebRequest $request)
    {
        $action_type = $request->getParameter('action_type');

        if ($action_type == self::DECLINE_REPORT_ACTION) {
            return $this->executeDeclineReport($request);
        }

        $model = $this->getModel($request);
        $this->forward404Unless($model);

        $form = new AgreementAcceptForm(array(),
            array(
                'comments_file_path' => AgreementModelReport::AGREEMENT_COMMENTS_FILE_PATH,
            ));

        $form->bind(
            array(
                'agreement_comments' => $request->getPostParameter('agreement_comments'),
                'designer_approve' => $request->getPostParameter('designer_approve')
            ),
            array() //$this->getModelCommentsFiles($request, 'agreement_comments_file')
        );

        $send_to_specialist = $form->getValue('designer_approve');
        if ($send_to_specialist) {
            return $this->executeSendReportToSpecialists($request, $form, 'accept');
        }

        if ($form->isValid()) {
            $accept_report_utils = new AgreementModelReportAcceptByManager(array(
                'model' => $model,
                'report' => $model->getReport(),
                'request' => $request,
                'form' => $form,
                'user' => $this->getUser()->getAuthUser()
            ));

            $message = $accept_report_utils->agreementManagerAccept();
        }

        $message_data = null;
        /*if ($message) {
            $message_data = Utils::formatMessageData($message);
        }*/

        return $this->sendFormBindResult($form, 'window.accept_report_form.onResponse');

        //return $this->sendFormBindResult($form);
    }

    function executeSendModelToSpecialists(sfWebRequest $request, $form = null, $status = 'accept')
    {
        $model = $this->getModel($request);
        $this->forward404Unless($model);

        $model->cancelSpecialistSending();

        $agreement_comments = $request->getParameter('agreement_comments');
        $data = $this->getSpecialistData($request);

        if (!$data) {
            return $this->sendFormBindResult($form, 'window.accept_decline_form.onResponse');
        }

        if ($status == 'accept') {
            $model->setManagerStatus('accepted');
            $model->setDesignerStatus('wait');
        } else if ($status == 'decline') {
            $model->setManagerStatus('declined');
        }

        if (count($data['group']) > 0) {
            /**
             * Make copy of uploaded temp files and remove
             */
            $msg_files = TempFileTable::copyFilesByRequest($request, AgreementModel::AGREEMENT_COMMENTS_FILE_PATH);

            $model->setAgreementCommentsFile('');
            if (!empty($msg_files)) {
                $model->setAgreementCommentsFile($msg_files[0]);
            }

            $comment_text = $form->getValue('agreement_comments');
            $model->setAgreementComments('');
            if (!empty($comment_text) && $status != 'accept') {
                $model->setAgreementComments($comment_text);
            }

            $model->setStatus('wait_specialist');
            $model->save();

            $discussionMsg = $model->isConcept() ? 'Концепция на проверке у дизайнера.' : 'Макет на проверке у дизайнера.';
            if ($model->isModelScenario()) {
                if ($model->getStep1() == "wait" && ($model->getStep2() == "none" || $model->getStep2() == "wait")) {
                    $discussionMsg = 'Сценарий на проверке у дизайнера.';
                } else if ($model->getStep1() == "accepted" && $model->getStep2() != "accepted") {
                    $discussionMsg = 'Запись на проверке у дизайнера.';
                }
            }

            //$message = $this->addMessageToDiscussion($model, $discussionMsg);
            $message = $this->addMessageToDiscussion($model, $discussionMsg, true, $status != 'accept' ? Message::MSG_STATUS_DECLINED_TO_SPECIALST : Message::MSG_STATUS_SENDED);
            //$this->addFileToSpecialistMessage($message, $model->getAgreementCommentsFile(), AgreementModel::AGREEMENT_COMMENTS_FILE_PATH);

            LogEntryTable::getInstance()->addEntry(
                $this->getUser()->getAuthUser(),
                $model->isConcept() ? 'agreement_concept' : 'agreement_model',
                'sent_to_specialist',
                $model->getActivity()->getName() . '/' . $model->getName(),
                $discussionMsg,
                '',
                $model->getDealer(),
                $model->getId(),
                'agreement'
            );
        }

        foreach ($data['group'] as $group_id => $true) {
            $specialist = $this->getSpecialist($data, $group_id);
            //$msg = $this->getMessageForSpecialist($data, $group_id);
            $this->sendModelToSpecialist($model, $specialist, $agreement_comments, false);
        }

        if ($status != 'accept') {
            $utils = new AgreementModelStatusUtils();
            $utils->setMsgType(Message::MSG_TYPE_MANAGER);
            $utils->setCanSendMail(false);
            $utils->declineModelOnlyMail(
                $model,
                $this->getUser()->getAuthUser(),
                $form->getValue('agreement_comments'),
                $msg_files,
                false
            );
        } else {
            $utils = new AgreementModelStatusUtils();
            $utils->setMsgType(Message::MSG_TYPE_MANAGER);
            $utils->setCanSendMail(false);
            $utils->acceptModelOnlyMail(
                $model,
                $this->getUser()->getAuthUser(),
                $form->getValue('agreement_comments'),
                $msg_files,
                $message
            );
        }

        $message_data = null;
        /*if ($message) {
            $message_data = Utils::formatMessageData($message);
        }*/

        //return $this->sendJson(array('success' => true));
        return $this->sendFormBindResult($form, 'window.accept_decline_form.onResponse');
    }

    private function addFileToSpecialistMessage(Message $message, $file_name, $path)
    {
        if (isset($file_name) && !empty($file_name)) {
            $file = new MessageFile();
            $file->setMessageId($message->getId());
            $file->setFile($message->getId() . '-' . $file_name);

            copy(
                sfConfig::get('sf_upload_dir') . '/' . $path . '/' . $file_name,
                sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH . '/' . $file->getFile()
            );

            $file->save();
        }
    }

    private function addFilesToSpecialistMessage(Message $message, $msg_files)
    {
        array_shift($msg_files);
        foreach ($msg_files as $saved_file) {
            $file = new MessageFile();
            $file->setMessageId($message->getId());
            $file->setFile($message->getId() . '-' . $saved_file);

            copy(
                sfConfig::get('sf_upload_dir') . '/' . AgreementModelReport::AGREEMENT_COMMENTS_FILE_PATH . '/' . $saved_file,
                sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH . '/' . $file->getFile()
            );

            $file->save();
        }
    }

    protected function sendModelToSpecialist(AgreementModel $model, User $specialist, $msg)
    {
        $comment = new AgreementModelComment();
        $comment->setArray(array(
            'model_id' => $model->getId(),
            'user_id' => $specialist->getId()
        ));

        $comment->setStatus('wait');
        $comment->save();

        //$this->sendMessageToSpecialist($model->getDiscussion(), $specialist, $msg ?: 'Отправлено для согласования');

        $log_entry = LogEntryTable::getInstance()->addEntry(
            $this->getUser()->getAuthUser(),
            $model->isConcept() ? 'agreement_concept' : 'agreement_model',
            'sent_to_specialist',
            $model->getActivity()->getName() . '/' . $model->getName(),
            $model->isConcept() ? 'Вам отправлена концепция для согласования' : 'Вам отправлен макет для согласования',
            '',
            $model->getDealer(),
            $model->getId(),
            'agreement'
        );
        $log_entry->setPrivateUser($specialist);
        $log_entry->save();

        AgreementSpecialistHistoryMailSender::send('AgreementModelSentToSpecialistMail', $log_entry, $specialist, $msg);
    }

    function executeSendReportToSpecialists(sfWebRequest $request, $form = null, $status = 'accept')
    {
        $model = $this->getModel($request);
        $this->forward404Unless($model);

        $report = $model->getReport();
        $this->forward404Unless($report);

        $report->cancelSpecialistSending();

        $agreement_comments = $request->getParameter('agreement_comments');
        $data = $this->getSpecialistData($request);

        if (!$data) {
            return $this->sendFormBindResult($form, 'window.accept_model_form.onResponse');
        }

        if ($status == 'accept') {
            $report->setManagerStatus('accepted');
        } else if ($status == 'decline') {
            $report->setManagerStatus('declined');
        }

        $report->setDesignerStatus('wait');
        $report->save();

        if (count($data['group']) > 0) {
            /**
             * Make copy of uploaded temp files and remove
             */
            $msg_files = TempFileTable::copyFilesByRequest($request, AgreementModelReport::AGREEMENT_COMMENTS_FILE_PATH);
            $report->setAgreementCommentsFile('');
            if ($form && !empty($msg_files)) {
                $report->setAgreementCommentsFile($msg_files[0]);
            }

            $report->setStatus('wait_specialist');
            $report->save();

            $message = $this->addMessageToDiscussion($model, 'Отчёт на проверке дизайнера.', true, $status != 'accept' ? Message::MSG_STATUS_DECLINED_TO_SPECIALST : Message::MSG_STATUS_SENDED);
            $msg_comment = null;
            if ($status != 'accept') {
                $discussionLabel = 'Отчет не согласован. Внесите комментарии.';
                $this->addMessageToDiscussion(
                    $model,
                    $discussionLabel,
                    false,
                    Message::MSG_STATUS_SENDED
                );

                if (!empty($agreement_comments)) {
                    $message = $this->addMessageToDiscussion($model, 'Комментарий менеджера. ' . (!empty($agreement_comments) ? $agreement_comments . '.' : ''), false, $status != 'accept' ? Message::MSG_STATUS_DECLINED_TO_SPECIALST : Message::MSG_STATUS_SENDED);
                }
            }

            $this->addFileToSpecialistMessage($message, $report->getAgreementCommentsFile(), AgreementModelReport::AGREEMENT_COMMENTS_FILE_PATH);
            $this->addFilesToSpecialistMessage($message, $msg_files);

            $entry = LogEntryTable::getInstance()->addEntry(
                $this->getUser()->getAuthUser(),
                $model->isConcept() ? 'agreement_concept_report' : 'agreement_report',
                'sent_to_specialist',
                $model->getActivity()->getName() . '/' . $model->getName(),
                'Отчёт отправлен специалистам',
                '',
                $model->getDealer(),
                $model->getId(),
                'agreement'
            );

            if ($status != 'accept') {
                AgreementDealerHistoryMailSender::send('AgreementReportDeclinedMail', $entry, $model->getDealer(), null, false);
            }
        }

        foreach ($data['group'] as $group_id => $true) {
            $specialist = $this->getSpecialist($data, $group_id);
            //$msg = $this->getMessageForSpecialist($data, $group_id);
            $this->sendReportToSpecialist($report, $specialist, $agreement_comments);
        }

        return $this->sendFormBindResult($form, 'window.accept_report_form.onResponse');
    }

    protected function sendReportToSpecialist(AgreementModelReport $report, User $specialist, $msg)
    {
        $comment = new AgreementModelReportComment();
        $comment->setArray(array(
            'report_id' => $report->getId(),
            'user_id' => $specialist->getId()
        ));

        $comment->setStatus('wait');
        $comment->save();

        $model = $report->getModel();

        //$this->sendMessageToSpecialist($model->getDiscussion(), $specialist, $msg ?: 'Отправлено для согласования');

        $log_entry = LogEntryTable::getInstance()->addEntry(
            $this->getUser()->getAuthUser(),
            $model->isConcept() ? 'agreement_concept_report' : 'agreement_report',
            'sent_to_specialist',
            $model->getActivity()->getName() . '/' . $model->getName(),
            'Вам отправлен отчёт для согласования',
            '',
            $model->getDealer(),
            $model->getId(),
            'agreement'
        );
        $log_entry->setPrivateUser($specialist);
        $log_entry->save();

        AgreementSpecialistHistoryMailSender::send('AgreementReportSentToSpecialistMail', $log_entry, $specialist, $msg);
    }

    protected function sendMessageToSpecialist(Discussion $discussion, User $specialist, $msg)
    {
        $owner = $this->getUser()->getAuthUser();

        $message = new Message();
        $message->setDiscussion($discussion);
        $message->setUser($owner);
        $message->setUserName($owner->selectName());
        $message->setText('>>> ' . $specialist->selectName() . "\r\n" . $msg);
        $message->setPrivateUser($specialist);
        $message->setSystem(true);
        $message->save();

        // приватное сообщение себе
        $message = new Message();
        $message->setDiscussion($discussion);
        $message->setUser($owner);
        $message->setUserName($owner->selectName());
        $message->setText('>>> ' . $specialist->selectName() . "\r\n" . $msg);
        $message->setPrivateUser($owner);
        $message->setSystem(true);
        $message->save();

        $last_read = $discussion->getLastRead($owner);
        $last_read->setMessageId($message->getId());
        $last_read->save();
    }

    protected function getSpecialist($data, $group_id)
    {
        if (!isset($data['user'][$group_id]))
            throw new NotFoundSpecialistForGroupException($group_id);

        $specialist = UserTable::getInstance()->createQuery()->where('id=? and group_id=?', array($data['user'][$group_id], $group_id))->fetchOne();
        if (!$specialist)
            throw new SpecialistNotFoundException($group_id, $data['user'][$group_id]);

        if (!$specialist->getGroup()->isSpecialist())
            throw new UserIsNotSpecialistException($specialist);

        return $specialist;
    }

    protected function getMessageForSpecialist($data, $group_id)
    {
        return isset($data['msg']) && is_array($data['msg']) && isset($data['msg'][$group_id])
            ? $data['msg'][$group_id]
            : '';
    }

    protected function getSpecialistData(sfWebRequest $request)
    {
        $data = $request->getPostParameter('specialist', array());
        if (!isset($data['group']) || !isset($data['user']) || !is_array($data['group']) || !is_array($data['user']))
            throw new BadSpecialistsFormatException();

        return $data;
    }

    function outputModels(sfWebRequest $request)
    {
        $this->models = $this->loadModelsList($request);
    }

    function loadModelsList(sfWebRequest $request)
    {
        $sorts = array(
            'id' => 'm.id',
            'dealer' => 'm.dealer_id', // сортировка по id дилеров (фактически - это группировка)
            'name' => 'm.name',
            'cost' => 'm.cost'
        );

        $sort_column = $this->getSortColumn();
        $sort_direct = $this->getSortDirection();

        $sql_sort = 'm.id';
        if (isset($sorts[$sort_column])) {
            if ($this->getWaitFilter() == 'blocked') {
                $sort_direct = $sort_direct ?: 'DESC';
            }

            $sql_sort = $sorts[$sort_column] . ' ' . ($sort_direct ? 'DESC' : 'ASC');
        }

        $modelType = $this->getModelTypeFilter();

        if ($modelType == "concepts_list") {
            return array();
        }

        $query = AgreementModelTable::getInstance()
            ->createQuery('m')
            ->select('m.*, r.status, mc.status, v.*')
            ->innerJoin('m.ModelType mt WITH mt.concept=?', false)
            ->leftJoin('m.Report r')
            ->orderBy($sql_sort);

        if ($this->getModelFilter()) {
            $query->andWhere('m.id=? and m.is_deleted = ?', array($this->getModelFilter(), false));
        } else {
            switch ($this->getWaitFilter()) {
                case 'specialist':
                    $query->andWhere('m.wait_specialist=?', true);
                    break;
                case 'dealer':
                    $query->andWhere('(m.status=? or r.status=?)', array('not_sent', 'not_sent'));
                    break;
                case 'manager':
                    $query->andWhere('(m.status=? or r.status=? or m.status = ?)', array('wait', 'wait', 'wait_manager_specialist'));
                    break;
                case 'agreed':
                    $query->andWhere('(m.status=? and r.status=?)', array('accepted', 'accepted'));
                    break;
            }

            if ($this->getWaitFilter() == "blocked") {
                $query->andWhere('m.is_blocked = ? and m.allow_use_blocked = 0', true);
            } else {
                $query->andWhere('m.is_blocked = ? or m.allow_use_blocked = ?', array(false, true));
            }

            //Удаленные заявки
            if ($this->getWaitFilter() == "deleted") {
                $query->andWhere('is_deleted = ?', true);
            } else {
                $query->andWhere('is_deleted = ?', false);
            }

            if ($this->getDealerFilter()) {
                $query->andWhere('m.dealer_id=?', $this->getDealerFilter()->getId());
            }

            if ($this->getActivityFilter()) {
                $query->andWhere('m.activity_id = ?', $this->getActivityFilter()->getId());
            }

            if ($this->getStartDateFilter()) {
                $query->andWhere('m.created_at>=?', D::toDb($this->getStartDateFilter()));
            }

            if ($this->getEndDateFilter()) {
                $query->andWhere('m.created_at<=?', D::toDb($this->getEndDateFilter()));
            }

            //В макет не вносились изменения
            //Выводится в другом шаблоне _no_models_changes
            if ($this->getWaitFilter() == "no_model_changes") {
                $query->andWhere('no_model_changes = ?', true);

                $query->andWhere('year(m.created_at) = ? or year(m.updated_at) = ?',
                    array
                    (
                        $this->getYearFilter($request),
                        $this->getYearFilter($request),
                    )
                );
            }

            $modelStatus = $this->getModelStatusFilter();
            if ($modelStatus && $modelStatus != 'all') {
                if ($modelStatus == 'accepted') {
                    $query->andWhere('m.status = ? and r.status = ?', array('accepted', 'accepted'));
                } else if ($modelStatus == 'wait') {
                    $query->andWhere('m.wait_specialist = ?', 1);
                } else if ($modelStatus == 'comment') {
                    $query->leftJoin('m.Comments mc');
                    $query->andWhere('mc.status = ?', array('wait'))
                        ->andWhere('m.agreement_comments IS NOT NULL');
                    //$query->andWhere('mc.status = ?', 'wait');
                }
            }

            if ($modelType && $modelType == 'all') {
                $query->orWhere('r.status = ?', array('wait_specialist'));
            } else {
                if ($modelType && $modelType != 'all') {
                    if ($modelType == 'makets') {
                        $query->andWhere('m.status = ? or m.status = ? or m.status = ?', array('wait', 'wait_specialist', 'wait_manager_specialist'));
                    } else if ($modelType == 'reports') {
                        $query->andWhere('(r.status = ? or r.status = ?) and m.status = ?', array('wait', 'wait_specialist', 'accepted'));
                    }
                }
            }
        }

        $this->dealers_list = array();
        $dealers = DealerTable::getDealersList()->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        foreach ($dealers as $dealer) {
            $this->dealers_list[$dealer['id']] = $dealer;
        }

        if ($this->getWaitFilter() == 'agreed' || $this->getWaitFilter() == 'all' || $this->getWaitFilter() == 'blocked') {
            $this->initPager($query);
            $this->initPaginatorData(null, 'agreement_module_management_models');

            $this->models = $this->pager->getResults();
        } else {
            $this->page = 0;
            $this->models = $query->execute();
        }

        $models_ids = array();
        foreach ($this->models as $model) {
            $models_ids[$model->getId()] = $model;
        }

        $models_to_sort = array();
        foreach ($this->models as $m) {
            if ($this->getWaitFilter() == 'blocked' || $this->getWaitFilter() == 'agreed' || $this->getWaitFilter() == 'all') {
                $date = $m->getUpdatedAt();
            } else {
                $date = $m->getModelAcceptToDate(($this->getWaitFilter() && ($this->getWaitFilter() == 'manager' || $this->getWaitFilter() == 'no_model_changes')) ? false : true);

                $date = date('d-m-Y H:i:s', strtotime($date) + mt_rand(1, 60));
                $dateTime = D::toUnix($date);

                if (array_key_exists($dateTime, $models_to_sort)) {
                    $date = date('d-m-Y H:i:s', $dateTime + mt_rand(1, 60));
                }
            }

            $models_to_sort[D::toUnix($date)] = $m;
        }

        if ($this->getWaitFilter() != 'blocked') {
            $wait_filter = $this->getWaitFilter();
            uksort($models_to_sort, function($a, $b) use ($wait_filter) {
                if ($a == $b) { return 0; }

                return $wait_filter == 'no_model_changes' ? ($a > $b ? -1 : 1) : ($a > $b ? 1 : -1);
            });
        }

        return $models_to_sort;
    }

    function executeLoadModelsByAjax(sfWebRequest $request)
    {
        $this->models = null;
        /*if(!$this->getModelFilter()) {
            $this->outputFilter();

          if($this->getDesignerFilter())
            $this->models = $this->loadDesignerModels($request);
          else
              $this->models = $this->loadModelsList($request);
        }*/
    }

    function outputDealerModels()
    {
        $sorts = array(
            'id' => 'm.id',
            'dealer' => 'm.dealer_id', // сортировка по id дилеров (фактически - это группировка)
            'name' => 'm.name',
            'cost' => 'm.cost'
        );

        $sort_column = $this->getSortColumn();
        $sort_direct = $this->getSortDirection();

        $sql_sort = 'm.id DESC';
        if (isset($sorts[$sort_column]))
            $sql_sort = $sorts[$sort_column] . ' ' . ($sort_direct ? 'DESC' : 'ASC');

        $query = AgreementModelTable::getInstance()
            ->createQuery('m')
            ->innerJoin('m.Activity a')
            ->innerJoin('m.ModelType mt WITH mt.concept=?', false)
            ->leftJoin('m.Discussion d')
            ->leftJoin('m.Report r')
            ->orderBy($sql_sort);

        $query->andWhere('m.dealer_id=?', $this->getUser()->getAuthUser()->getDealer()->getId());

        if ($this->getActivityStatusFilter()) {
            switch ($this->getActivityStatusFilter()) {
                case 'in_work':
                    $query->andWhere('m.wait_specialist = ?', 1);
                    break;

                case 'complete':
                    $query->andWhere('m.status = ? and r.status=?', array('accepted', 'accepted'));
                    break;

                case 'process_draft':
                    //$query->andWhere('m.status = ? or m.wait_specialist = ?', array('wait', 1));
                    $query->andWhere('m.status = ? or m.status = ?', array('declined', 'not_sent'))
                        ->andWhere('m.report_id is null');
                    break;

                case 'process_reports':
                    $query->andWhere('m.status = ? and m.report_id is null', 'accepted');
                    break;

                case 'all':
                    /*$query->andWhere('m.report_id is null')
                          ->andWhere('m.status = ?', 'accepted'); */
                    break;
            }
        } else {
            $query->andWhere('m.status = ? or m.status = ? or m.status = ?', array('declined', 'not_sent', 'accepted'))
                ->andWhere('m.report_id is null');
        }

        if ($this->getStartDateFilter())
            $query->andWhere('m.created_at>=?', D::toDb($this->getStartDateFilter()));
        if ($this->getEndDateFilter())
            $query->andWhere('m.created_at<=?', D::toDb($this->getEndDateFilter()));

        $this->models = $query->execute();

        $mods = array();
        foreach ($this->models as $m) {

            $mods[strtotime($m->getModelAcceptToDate())] = $m;
        }

        ksort($mods, SORT_NUMERIC);

        $this->models = $mods;
    }

    function loadDesignerModels(sfWebRequest $request)
    {
        $sorts = array(
            'id' => 'm.id',
            'dealer' => 'm.dealer_id', // сортировка по id дилеров (фактически - это группировка)
            'name' => 'm.name',
            'cost' => 'm.cost'
        );

        $sort_column = $this->getSortColumn();
        $sort_direct = $this->getSortDirection();

        $sql_sort = 'm.id';

        if (isset($sorts[$sort_column]))
            $sql_sort = $sorts[$sort_column] . ' ' . ($sort_direct ? 'DESC' : 'ASC');

        /*$query = AgreementModelTable::getInstance()
                 ->createQuery('m')
                 ->innerJoin('m.Activity a')
                 ->innerJoin('m.ModelType mt WITH mt.concept=?', false)
                 ->leftJoin('m.Discussion d')
                 ->leftJoin('m.Report r')
                 ->orderBy($sql_sort);*/

        $designer_id = $this->getDesignerFilter();

        $query = AgreementModelTable::getInstance()
            ->createQuery('m')
            ->innerJoin('m.Activity a')
            ->innerJoin('m.ModelType mt WITH mt.concept=?', false)
            ->leftJoin('m.Comments mc')
            //->leftJoin('m.Discussion d')
            ->leftJoin('m.Report r')
            ->leftJoin('r.Comments rc')
            ///->leftJoin('m.Dealer dealer')
            //->where('(mc.user_id=? and mc.status=?) or (rc.user_id=? and rc.status=?)', array($user->getId(), 'wait', $user->getId(), 'wait'));
            ->where('(mc.user_id=?) or (rc.user_id=?)', array($designer_id->getId(), $designer_id->getId()))
            ->orderBy($sql_sort);;

        if ($this->getModelFilter()) {
            $query->andWhere('m.id=?', $this->getModelFilter());
        } else {
            switch ($this->getWaitFilter()) {
                /*case 'specialist':
                  $query->andWhere('m.wait_specialist=?', true);
                  break;
                case 'dealer':
                  $query->andWhere('m.status=? or r.status=?', array('declined', 'declined'));
                  break;
                case 'manager':
                  $query->andWhere('m.wait=?', true);
                  break;
                case 'agreed':
                  $query->andWhere('m.status=? and r.status=?', array('accepted', 'accepted'));
                  break;*/
                case 'specialist':
                    $query->andWhere('m.wait_specialist=?', true);
                    break;
                case 'dealer':
                    //$query->andWhere('m.status=? or r.status=?', array('declined', 'declined'));
                    $query->andWhere('m.status=? or r.status=?', array('not_sent', 'not_sent'));
                    break;
                case 'manager':
                    //$query->andWhere('m.wait=?', true);
                    $query->andWhere('m.status=? or r.status=?', array('wait', 'wait'));
                    break;
                case 'agreed':
                    $query->andWhere('m.status=? and r.status=?', array('accepted', 'accepted'));
                    break;
            }

            if ($this->getDealerFilter())
                $query->andWhere('m.dealer_id=?', $this->getDealerFilter()->getId());

            if ($this->getActivityFilter())
                $query->andWhere('m.activity_id = ?', $this->getActivityFilter()->getId());

            if ($this->getStartDateFilter())
                $query->andWhere('m.created_at>=?', D::toDb($this->getStartDateFilter()));
            if ($this->getEndDateFilter())
                $query->andWhere('m.created_at<=?', D::toDb($this->getEndDateFilter()));

            if (!$this->getStartDateFilter() && !$this->getEndDateFilter()) {
                $query->andWhere('year(m.created_at) = ? or year(m.updated_at) = ?', array($this->getYearFilter($request), $this->getYearFilter($request)));
            }

            $modelStatus = $this->getModelStatusFilter();
            if ($modelStatus && $modelStatus != 'all') {
                if ($modelStatus == 'accepted') {
                    $query->andWhere('m.status = ? and m.report_id is null', 'accepted');
                } else if ($modelStatus == 'wait') {
                    $query->andWhere('m.wait_specialist = ?', 1);
                } else if ($modelStatus == 'comment') {
                    //$query->andWhere('m.status = ? or m.status = ?', array('wait_specialist', 'declined'));
                    $query->andWhere('m.status = ?', array('declined'));
                    $query->andWhere('m.agreement_comments is not null');
                    //$query->andWhere('mc.status = ?', 'wait');
                }

            }

            $offset = $this->getModelsFilterByOffset();
            $query->limit(self::LIMIT_MODELS_COUNT);

            if ($offset != 0)
                $query->offset($offset * self::LIMIT_MODELS_COUNT);

        }

        $this->models = $query->execute();
        if (count($this->models) == 0)
            $this->setModelFilterOffsetTo(--$offset);

        $mods = array();

        foreach ($this->models as $m) {
            $mods[strtotime($m->getModelAcceptToDate(false))] = $m;
        }

        ksort($mods, SORT_NUMERIC);

        /*$k = 0;
        for($i = count($this->models); $i >= 0; $i--)  {
          $mods[$k++] = $this->models[$i];
        }*/

        $this->models = $mods;

        return $this->models;
    }

    function outputDesignerModels(sfWebRequest $request)
    {
        $this->models = $this->loadDesignerModels($request);
    }

    function outputDeclineReasons()
    {
        $this->decline_reasons = AgreementDeclineReasonTable::getInstance()->createQuery()->execute();
    }

    function outputDeclineReportReasons()
    {
        $this->decline_report_reasons = AgreementDeclineReportReasonTable::getInstance()->createQuery()->execute();
    }

    function outputSpecialistGroups()
    {
        $this->specialist_groups = UserGroupTable::getInstance()
            ->createQuery('g')
            ->distinct()
            ->select('g.*')
            ->innerJoin('g.Roles r WITH r.role=?', 'specialist')
            ->innerJoin('g.Users u WITH u.active=?', true)
            ->execute();
    }

    function outputFilter()
    {
        $this->outputWaitFilter();

        $this->outputDealers();
        $this->outputActivities();
        $this->outputFinishedAndGrouphActvities();


        $this->outputDesigners();

        $this->outputDealerFilter();
        $this->outputActivityFilter();
        $this->outputStartDateFilter();
        $this->outputEndDateFilter();
        $this->outputModelFilter();
        $this->outputModelTypeFilter();

        $this->outputDesignerFilter();
        $this->outputDesignerModelstatusFilter();

        $this->outputActivitystatusFilter();

        //$this->getModelsFilterByOffset();
    }

    function outputWaitFilter()
    {
        $this->wait_filter = $this->getWaitFilter();
    }

    function outputDealerFilter()
    {
        $this->dealer_filter = $this->getDealerFilter();
    }

    function outputActivityFilter()
    {
        $this->activity_filter = $this->getActivityFilter();
    }

    function outputDesignerFilter()
    {
        $this->designer_filter = $this->getDesignerFilter();
    }

    function outputDesignerModelstatusFilter()
    {
        $this->model_status_filter = $this->getModelStatusFilter();
    }

    function outputStartDateFilter()
    {
        $this->start_date_filter = $this->getStartDateFilter();
    }

    function outputEndDateFilter()
    {
        $this->end_date_filter = $this->getEndDateFilter();
    }

    function outputModelFilter()
    {
        $this->model_filter = $this->getModelFilter();
    }

    function outputDealers()
    {
        $this->dealers = DealerTable::getVwDealersQuery()->execute();
    }

    function outputActivities()
    {
        $this->activities = $this->getActivities(false);
    }

    function outputFinishedActvities()
    {
        $this->finishedActivities = $this->getActivities(true);
    }

    public function outputFinishedAndGrouphActvities()
    {
        $this->outputFinishedActvities();

        $prev_year = date('Y', strtotime('-1 year', time()));

        $activities_result = array();
        foreach ($this->finishedActivities as $activity) {
            if (D::getYear($activity->getStartDate()) >= $prev_year && $prev_year <= D::getYear($activity->getEndDate())) {
                $activities_result[$prev_year][] = $activity;
            }
        }

        $this->finished_activities_by_prev_year = $activities_result;
    }

    function getActivities($finished = false)
    {
        return ActivityTable::getInstance()
            ->createQuery()
            //->where('year(updated_at) = ? and finished = ?', array(date('Y'), $finished))
            ->where('finished = ?', array($finished))
            ->orderBy('id ASC')
            ->execute();
    }

    function outputModelTypeFilter()
    {
        $this->model_type_filter = $this->getModelTypeFilter();
    }

    function outputActivitystatusFilter()
    {
        $this->activity_status = $this->getActivityStatusFilter();
    }

    function outputDesigners()
    {
        $this->designers = UserTable::getInstance()
            ->createQuery()
            ->where('group_id = ?', 22)
            ->andWhere('active = ?', true)
            ->orderBy('name ASC')
            ->execute();
    }

    function outputConcepts(sfWebRequest $request)
    {
        $query = AgreementModelTable::getInstance()
            ->createQuery('m')
            ->innerJoin('m.Activity a')
            ->innerJoin('m.ModelType mt WITH mt.concept = ?', true)
            ->leftJoin('m.Discussion d')
            ->leftJoin('m.Report r')
            ->where('m.special_agreement = ? and m.agreement_by_importer = ?', array(false, false))
            ->orderBy('m.id desc');

        if ($this->getModelFilter()) {
            $query->andWhere('m.id=?', $this->getModelFilter());
        } else {
            switch ($this->getWaitFilter()) {
                case 'specialist':
                    $query->andWhere('m.wait_specialist=?', true);
                    break;
                case 'dealer':
                    $query->andWhere('m.status=? or r.status=?', array('declined', 'declined'));
                    break;
                case 'manager':
                    $query->andWhere('m.wait=?', true);
                    break;
                case 'agreed':
                    $query->andWhere('m.status=? and r.status=?', array('accepted', 'accepted'));
                    break;
            }

            //Фильтр удаленных заявок
            if ($this->getWaitFilter() == 'deleted') {
                $query->andWhere('is_deleted = ?', true);
            } else {
                $query->andWhere('is_deleted = ?', false);
            }

            if ($this->getDealerFilter()) {
                $query->andWhere('m.dealer_id=?', $this->getDealerFilter()->getId());
            }

            if ($this->getStartDateFilter()) {
                $query->andWhere('m.created_at>=?', D::toDb($this->getStartDateFilter()));
            }

            if ($this->getEndDateFilter()) {
                $query->andWhere('m.created_at<=?', D::toDb($this->getEndDateFilter()));
            }

            /*if (!$this->getStartDateFilter() && !$this->getEndDateFilter() && $this->getWaitFilter() && $this->getWaitFilter() != 'all') {
                $query->andWhere('year(m.created_at) = ? or year(m.updated_at) = ?', array(date('Y'), date('Y')));
            }*/
        }

        $this->concepts = $query->execute();

    }

    protected function attachAgreementModelCommentsFileToMessage(AgreementModel $model, Message $message)
    {
        $file = new MessageFile();
        $file->setMessageId($message->getId());
        $file->setFile($message->getId() . '-' . $model->getAgreementCommentsFile());

        copy(
            sfConfig::get('sf_upload_dir') . '/' . AgreementModel::AGREEMENT_COMMENTS_FILE_PATH . '/' . $model->getAgreementCommentsFile(),
            sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH . '/' . $file->getFile()
        );

        $file->save();
    }

    protected function attachAgreementModelReportCommentsFileToMessage(AgreementModelReport $report, Message $message)
    {
        $file = new MessageFile();
        $file->setMessageId($message->getId());
        $file->setFile($message->getId() . '-' . $report->getAgreementCommentsFile());

        copy(
            sfConfig::get('sf_upload_dir') . '/' . AgreementModelReport::AGREEMENT_COMMENTS_FILE_PATH . '/' . $report->getAgreementCommentsFile(),
            sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH . '/' . $file->getFile()
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
    protected function addMessageToDiscussion(AgreementModel $model, $text, $show_msg = true, $msg_status = 'none')
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
        $message->setMsgShow($show_msg);
        $message->setMsgStatus($msg_status);
        $message->save();

        // mark as unread
        $discussion->getUnreadMessages($user);

        return $message;
    }

    function getSortColumn()
    {
        return $this->getUser()->getAttribute(self::SORT_ATTR, 'id');
    }

    function getSortDirection()
    {
        return $this->getUser()->getAttribute(self::SORT_DIRECT_ATTR, false);
    }

    function setSortColumn($column)
    {
        $this->getUser()->setAttribute(self::SORT_ATTR, $column);
    }

    function setSortDirection($direction)
    {
        $this->getUser()->setAttribute(self::SORT_DIRECT_ATTR, $direction);
    }

    /**
     * Returns model
     *
     * @param sfWebRequest $request
     * @return AgreementModel|false
     */
    function getModel(sfWebRequest $request)
    {
        return AgreementModelTable::getInstance()
            ->createQuery('m')
            ->innerJoin('m.Activity a')
            ->leftJoin('m.Report r')
            ->where('m.status<>? and m.id=?', array('not_send', $request->getParameter('id')))
            ->fetchOne();
    }

    function getWaitFilter()
    {
        $default = $this->getUser()->getAttribute('wait', 'manager', self::FILTER_NAMESPACE);
        $wait = $this->getRequestParameter('wait', $default);

        if ($wait != 'manager' && $wait != "all" && $wait != "specialist" && $wait != 'dealer' && $wait != 'blocked' && $wait != 'agreed') {
            $this->getUser()->setAttribute('wait', $wait, self::FILTER_NAMESPACE);
            $this->resetFilters();

            //$this->redirect('@agreement_module_management_models?wait='.$wait);
        } else if ($wait == 'manager') {
            $this->getUser()->setAttribute('designer_id', 0, self::FILTER_NAMESPACE);
        }

        $this->getUser()->setAttribute('wait', $wait, self::FILTER_NAMESPACE);
        //$this->wait_filter = $wait;

        return $wait;
    }

    /**
     * Returns dealer
     *
     * @return Dealer|null
     */
    function getDealerFilter()
    {
        if ($this->_dealer_filter === null) {
            $default = $this->getUser()->getAttribute('dealer_id', 0, self::FILTER_NAMESPACE);
            $id = $this->isReset ? $default : $this->getRequestParameter('dealer_id', $default);

            $this->getUser()->setAttribute('dealer_id', $id, self::FILTER_NAMESPACE);

            $this->_dealer_filter = $id ? DealerTable::getInstance()->find($id) : false;
        }

        return $this->_dealer_filter;
    }

    function getActivityFilter()
    {
        if ($this->_activity_filter === null) {
            $default = $this->getUser()->getAttribute('activity_id', 0, self::FILTER_NAMESPACE);
            $id = $this->isReset ? $default : $this->getRequestParameter('activity_id', $default);

            $this->getUser()->setAttribute('activity_id', $id, self::FILTER_NAMESPACE);

            $this->_activity_filter = $id ? ActivityTable::getInstance()->find($id) : false;
        }

        return $this->_activity_filter;
    }

    function getDesignerFilter()
    {
        if ($this->_designer_filter === null) {
            $default = $this->getUser()->getAttribute('designer_id', 0, self::FILTER_NAMESPACE);
            $id = $this->isReset ? $default : $this->getRequestParameter('designer_id', $default);

            if ($id == -1) {
                $this->_designer_filter = null;

                return $this->_designer_filter;
            }

            if ($id == 0) {
                $this->outputDesigners();
                $id = $this->designers->getFirst()->getId();
            }

            $this->getUser()->setAttribute('designer_id', $id, self::FILTER_NAMESPACE);
            $this->_designer_filter = $id ? UserTable::getInstance()->find($id) : false;
        }

        return $this->_designer_filter;
    }

    function getStartDateFilter()
    {
        return $this->getDateFilter('start_date');
    }

    function getEndDateFilter()
    {
        return $this->getDateFilter('end_date');
    }

    function getModelsFilterByOffset()
    {
        $offset = $this->getUser()->getAttribute('models_offset', -1, self::FILTER_NAMESPACE);
        $this->getUser()->setAttribute('models_offset', ++$offset, self::FILTER_NAMESPACE);

        return $offset;
    }

    function setModelFilterOffsetTo($offset)
    {
        $this->getUser()->setAttribute('models_offset', $offset, self::FILTER_NAMESPACE);
    }

    function resetModelFilterByOffset()
    {
        $this->getUser()->setAttribute('models_offset', -1, self::FILTER_NAMESPACE);

    }

    function getModelStatusFilter()
    {
        $default = $this->getUser()->getAttribute('model_status', 'all', self::FILTER_NAMESPACE);
        $model_status = $this->isReset ? $default : $this->getRequestParameter('model_status', $default);
        $this->getUser()->setAttribute('model_status', $model_status, self::FILTER_NAMESPACE);

        return $model_status;
    }

    function getModelFilter()
    {
        $default = $this->getUser()->getAttribute('model', '', self::FILTER_NAMESPACE);
        $model_id = $this->isReset ? $default : $this->getRequestParameter('model', $default);
        $this->getUser()->setAttribute('model', $model_id, self::FILTER_NAMESPACE);

        return $model_id;
    }

    function getActivityStatusFilter()
    {
        $default = $this->getUser()->getAttribute('activity_status', '', self::FILTER_NAMESPACE);
        $status = $this->isReset ? $default : $this->getRequestParameter('activity_status', $default);
        $this->getUser()->setAttribute('activity_status', $status, self::FILTER_NAMESPACE);

        return $status;
    }

    function getModelTypeFilter()
    {
        $default = $this->getUser()->getAttribute('model_type', '', self::FILTER_NAMESPACE);
        $status = $this->isReset ? $default : $this->getRequestParameter('model_type', $default);
        $this->getUser()->setAttribute('model_type', $status, self::FILTER_NAMESPACE);

        return $status;
    }

    function getYearFilter($request, $plus_year = 0)
    {
        $this->year = D::getBudgetYear($request);
        $this->budgetYears = D::getBudgetYears($request);

        $default = $this->getUser()->getAttribute('year', $this->year, self::FILTER_NAMESPACE);

        $this->year = $this->isReset ? $default : $this->getRequestParameter('year', $default);
        $this->getUser()->setAttribute('year', $this->year, self::FILTER_NAMESPACE);

        return $this->year + $plus_year;
    }

    protected function getDateFilter($name)
    {
        $default = $this->getUser()->getAttribute($name, '', self::FILTER_NAMESPACE);
        $date = $this->isReset ? $default : $this->getRequestParameter($name, $default);
        $this->getUser()->setAttribute($name, $date, self::FILTER_NAMESPACE);

        return preg_match('#^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$#', $date)
            ? D::fromRus($date)
            : false;
    }

    function resetFilters()
    {
        //$this->getUser()->setAttribute('models_offset', -1, self::FILTER_NAMESPACE);
        $this->getUser()->setAttribute('start_date', '', self::FILTER_NAMESPACE);
        $this->getUser()->setAttribute('end_date', '', self::FILTER_NAMESPACE);
        $this->getUser()->setAttribute('model_type', '', self::FILTER_NAMESPACE);
        $this->getUser()->setAttribute('model', '', self::FILTER_NAMESPACE);
        $this->getUser()->setAttribute('model_status', 'all', self::FILTER_NAMESPACE);
        $this->getUser()->setAttribute('designer_id', 0, self::FILTER_NAMESPACE);
        $this->getUser()->setAttribute('dealer_id', 0, self::FILTER_NAMESPACE);
        $this->getUser()->setAttribute('activity_id', 0, self::FILTER_NAMESPACE);

        $this->isReset = true;
    }

    function executeUnblock(sfWebRequest $request)
    {
        $modelId = $request->getParameter('modelId');

        $model = AgreementModelTable::getInstance()->find($modelId);

        if ($model) {
            $to_date = D::getNewDate(date('d-m-Y H:i:s'), 2, '+', false, 'd-m-Y H:i:s');
            //$to_date = D::toDb(strtotime('+2 day', strtotime(date('d-m-Y H:i:s'))), true);
            $model->setAllowUseBlocked(true);
            $model->setUseBlockedTo($to_date);
            $model->setBlockedInformStatus(AgreementModelsBlockInform::INFORM_STATUS_LEFT_2);
            $model->save();

            $log_blocked_model = new LogAgreementModelBlocked();
            $log_blocked_model->setArray(
                array(
                    'object_id' => $modelId,
                    'action' => 'agreement_model_unblock',
                    'description' => sprintf('Заявка %s, разблокирована до %s', $modelId, $to_date),
                    'user_id' => $this->getUser()->getAuthUser()->getId(),
                    'dealer_id' => 0//$this->getUser()->getAuthUser()->getDealer()->getId()
                )
            );
            $log_blocked_model->save();

            $reportId = $model->getReport() ? $model->getReport()->getId() : null;
            AgreementModelTable::addModelBlockedStatistic($model->getId(), $reportId, AgreementModelTable::MODEL_BLOCKED_ACTIVE, $this->getUser()->getAuthUser()->getId());

            $dealer_users = UserTable::getInstance()
                ->createQuery('u')
                ->innerJoin('u.DealerUsers du WITH dealer_id=?', $model->getDealerId())
                ->where('active=?', true)
                ///->groupBy('du.dealer_id')
                ->execute();

            foreach ($dealer_users as $user) {
                if ($user->getAllowReceiveMails()) {
                    $message = new AgreementDealerModelBlockInform($user, $model, 'unblock');
                    $message->setPriority(1);

                    sfContext::getInstance()->getMailer()->send($message);
                }
            }

            $user = UserTable::getInstance()->find(946);
            //$user = UserTable::getInstance()->find(692);
            if ($user) {
                $message = new AgreementDealerModelBlockInform($user, $model, 'unblock');
                $message->setPriority(1);

                sfContext::getInstance()->getMailer()->send($message);
            }

            return $this->sendJson(array('success' => true));
        }

        return $this->sendJson(array('success' => false));
    }

    //Favorites reports

    function executeReportFileAddToFavorites(sfWebRequest $request)
    {
        $item = new AgreementModelReportFavorites();

        $fileId = $request->getParameter('fileId');
        $file_to_fav = AgreementModelReportFilesTable::getInstance()->find($fileId);

        if ($file_to_fav) {
            $report = AgreementModelReportTable::getInstance()->find($request->getParameter('reportId'));

            $item->setReportId($request->getParameter('reportId'));
            $item->setFileName($file_to_fav->getFile());
            $item->setFileId($fileId);
            $item->setReportModelTypeId($request->getParameter('modelTypeId'));
            $item->setUserId($this->getUser()->getAuthUser()->getId());
            $item->save();

            $this->file = $file_to_fav;
            $this->report = $report;
            $this->model_type_id = $request->getParameter('modelTypeId');
        }

        $this->setTemplate('_favs/_remove_fav');
    }

    function executeReportFileRemoveFromFavorites(sfWebRequest $request)
    {
        $fileId = $request->getParameter('fileId');
        $file_to_fav = AgreementModelReportFilesTable::getInstance()->find($fileId);

        if ($file_to_fav) {
            $report = AgreementModelReportTable::getInstance()->find($request->getParameter('reportId'));

            $item = AgreementModelReportFavoritesTable::getInstance()
                ->createQuery()
                ->select()
                ->where('report_id = ? and file_id = ?',
                    array(
                        $request->getParameter('reportId'),
                        $request->getParameter('fileId'),
                    )
                )->fetchOne();

            if ($item) {
                $item->delete();

                $this->file = $file_to_fav;
                $this->report = $report;
                $this->model_type_id = $request->getParameter('modelTypeId');
            }
        }

        $this->setTemplate('_favs/_add_to_fav');
    }

    function outputFavoritesReports()
    {

        $query = AgreementModelReportFavoritesTable::getInstance()
            ->createQuery('f')
            ->select('f.*, r.*, m.*, log_entry.created_at as report_added')
            ->innerJoin('f.Report r')
            ->innerJoin('r.Model m')
            ->innerJoin('m.Activity a')
            ->innerJoin('m.ModelType mt')
            ->innerJoin('m.LogEntry log_entry')
            ->andWhere('log_entry.action = ? and log_entry.object_type = ? and log_entry.private_user_id = ?', array('edit', 'agreement_report', 0))
            ->orderBy('f.id DESC');

        if ($this->getFavoritesActivity()) {
            $query->andWhere('a.id = ?', $this->getFavoritesActivity()->getId());
        } else if ($this->getFavoritesFinishedActivity()) {
            $query->andWhere('a.id = ?', $this->getFavoritesFinishedActivity()->getId());
        }

        if ($this->getFavoritesDealer()) {
            $query->andWhere('m.dealer_id = ?', $this->getFavoritesDealer()->getId());
        }

        if ($this->getFavoritesModelType()) {
            $query->andWhere('m.model_type_id = ?', $this->getFavoritesModelType()->getId());
        }

        if ($this->getFavoritesStartDateFilter() || $this->getFavoritesStartDateFilter()) {
            if ($this->getFavoritesStartDateFilter()) {
                $query->andWhere('log_entry.created_at >= ?', D::toDb($this->getFavoritesStartDateFilter()));
            }

            if ($this->getFavoritesEndDateFilter()) {
                $query->andWhere('log_entry.created_at <= ?', D::toDb($this->getFavoritesEndDateFilter()));
            }
        }

        $this->initPager($query, 'AgreementModelReportFavorites', 50);
        $this->initPaginatorData(null, 'favorites_reports');

        $this->favorites = $this->pager->getResults();

        return $this->favorites;
    }

    function executeFavoritesReports(sfWebRequest $request)
    {
        $this->getYearFilter($request);

        $this->outputDeclineReasons();
        $this->outputDeclineReportReasons();
        $this->outputSpecialistGroups();

        $this->outputFavortiesReportsFilters();

        $this->outputFavoritesReports();
    }

    function outputModelTypes()
    {
        $this->modelTypes = AgreementModelTypeTable::getInstance()
            ->createQuery()
            ->select()
            ->orderBy('id ASC')
            ->execute();
    }

    function outputFavortiesReportsFilters()
    {

        $this->outputDealers();
        $this->outputActivities();
        $this->outputFinishedActvities();
        $this->outputModelTypes();

        $this->outputFavoritesActivityFilter();
        $this->outputFavoritesFinishedActivityFilter();
        $this->outputFavoritesDealerFilter();
        $this->outputFavortiesDatesFilter();
        $this->outputFavoritesModelTypeFilter();
    }

    function outputFavoritesActivityFilter()
    {
        $this->favorites_activity_filter = $this->getFavoritesActivity();
    }

    function outputFavoritesFinishedActivityFilter()
    {
        $this->favorites_activity_finished_filter = $this->getFavoritesFinishedActivity();
    }

    function outputFavoritesDealerFilter()
    {
        $this->favorites_dealer_filter = $this->getFavoritesDealer();
    }

    function outputFavortiesDatesFilter()
    {
        $this->favorites_start_date_filter = $this->getFavoritesStartDateFilter();
        $this->favorites_end_date_filter = $this->getFavoritesEndDateFilter();
    }

    function outputFavoritesModelTypeFilter()
    {
        $this->favorites_model_type_filter = $this->getFavoritesModelType();
    }

    private function getFavoritesActivity()
    {
        if ($this->_favorites_activity_filter === null) {
            $default = $this->getUser()->getAttribute('activity_id', 0, self::FILTER_NAMESPACE_FAVORITES);
            $id = $this->isReset ? $default : $this->getRequestParameter('activity_id', $default);

            $this->getUser()->setAttribute('activity_id', $id, self::FILTER_NAMESPACE_FAVORITES);

            $this->_favorites_activity_filter = $id ? ActivityTable::getInstance()->find($id) : false;
        }
        return $this->_favorites_activity_filter;
    }

    private function getFavoritesFinishedActivity()
    {
        if ($this->_favorites_activity_finished_filter === null) {
            $default = $this->getUser()->getAttribute('finished_activity_id', 0, self::FILTER_NAMESPACE_FAVORITES);
            $id = $this->isReset ? $default : $this->getRequestParameter('finished_activity_id', $default);

            $this->getUser()->setAttribute('finished_activity_id', $id, self::FILTER_NAMESPACE_FAVORITES);

            $this->_favorites_activity_finished_filter = $id ? ActivityTable::getInstance()->find($id) : false;
        }
        return $this->_favorites_activity_finished_filter;
    }


    private function getFavoritesDealer()
    {
        if ($this->_favorites_dealer_filter === null) {
            $default = $this->getUser()->getAttribute('dealer_id', 0, self::FILTER_NAMESPACE_FAVORITES);
            $id = $this->getRequestParameter('dealer_id', $default);

            $this->getUser()->setAttribute('dealer_id', $id, self::FILTER_NAMESPACE_FAVORITES);

            $this->_favorites_dealer_filter = $id ? DealerTable::getInstance()->find($id) : false;
        }
        return $this->_favorites_dealer_filter;
    }

    private function getFavoritesModelType()
    {
        $this->_favorites_model_type_filter = null;
        if ($this->_favorites_model_type_filter === null) {
            $default = $this->getUser()->getAttribute('model_type', 0, self::FILTER_NAMESPACE_FAVORITES);
            $id = $this->getRequestParameter('model_type', $default);

            $this->getUser()->setAttribute('model_type', $id, self::FILTER_NAMESPACE_FAVORITES);

            $this->_favorites_model_type_filter = $id ? AgreementModelTypeTable::getInstance()->find($id) : false;
        }
        return $this->_favorites_model_type_filter;
    }

    private function getFavoritesStartDateFilter()
    {
        return $this->getFavoritesDateFilter('start_date');
    }

    private function getFavoritesEndDateFilter()
    {
        return $this->getFavoritesDateFilter('end_date');
    }

    protected function getFavoritesDateFilter($name)
    {
        $default = $this->getUser()->getAttribute($name, '', self::FILTER_NAMESPACE_FAVORITES);
        $date = $this->isReset ? $default : $this->getRequestParameter($name, $default);
        $this->getUser()->setAttribute($name, $date, self::FILTER_NAMESPACE_FAVORITES);

        return preg_match('#^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$#', $date)
            ? D::fromRus($date)
            : false;
    }

    function executeFavoritesAddToArchive(sfWebRequest $request)
    {
        $reports = $this->outputFavoritesReports();

        $zip = new ZipArchive();
        $zipFile = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'favorites.zip';

        @unlink($zipFile);
        $res = $zip->open($zipFile, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);

        if ($res) {
            $activityDirs = array();
            $dealersDirs = array();

            foreach ($reports as $item) {
                $report = $item->getReport();
                $model = $report->getModel();
                $dealer = $model->getDealer();
                $activity = $model->getActivity();

                $activityName = Utils::normalize($activity->getName());
                $dealerName = Utils::normalize($dealer->getName());

                $f = 'getAdditionalFile';
                if ($item->getFileIndex() != 0)
                    $f = 'getAdditionalFileExt' . $item->getFileIndex();

                $zip->addFile(sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . AgreementModelReport::ADDITIONAL_FILE_PATH . DIRECTORY_SEPARATOR . $report->$f(),
                    $activityName . DIRECTORY_SEPARATOR . $dealerName . DIRECTORY_SEPARATOR . $report->$f());

            }

            $res = $zip->close();
        }

        return $this->sendJson(array('url' => '/uploads/favorites.zip'));
    }

    function executeDeleteFavoritesItem(sfWebRequest $request)
    {
        $id = $request->getParameter('id');

        $item = AgreementModelReportFavoritesTable::getInstance()->find($id);
        if ($item) {
            $item->delete();

            return $this->sendJson(array('success' => true, 'id' => $id));
        }

        return $this->sendJson(array('success' => false));

    }

    function executeModelReportFileContainer(sfWebRequest $request)
    {
        $this->idx = $request->getParameter('fileIdx');
    }

    function executeFavoritesReportsExportToPdf(sfWebRequest $request)
    {
        $items = explode(':', $request->getParameter('items'));

        $totalItems = count($items);
        $itemIndex = 1;

        $htmlText = '';
        foreach ($items as $item) {
            $favItem = AgreementModelReportFavoritesTable::getInstance()->find($item);

            if ($favItem) {
                $model = $favItem->getReport()->getModel();

                $htmlText .= '<div style="width: 100%; height: 95%; float: left; ">';
                $htmlText .= '<h1 style="margin-left: 25px;">' . $model->getModelType()->getName() . '</h1>';
                $htmlText .= '<div style="padding: 10px; background: #ccc; height: 670px; padding: 10px;"><span style="font-size: 16px; font-weight: bold;">Активность:</span> ' . sprintf("%s [%s]", $model->getActivity()->getName(), $model->getActivity()->getId());
                $htmlText .= '<br/><span style="font-size: 16px; font-weight: bold;">' . $model->getName() . '</span>';
                $htmlText .= '<br/><span style="font-size: 16px; font-weight: bold;">Дилер:</span> ' . sprintf("%s [%s]", $model->getDealer()->getName(), $model->getDealer()->getShortNumber());

                $func = 'getAdditionalFile';
                if ($favItem->getFileIndex() != 0) {
                    $func = 'getAdditionalFileExt' . $favItem->getFileIndex();
                }

                $imageFile = sfconfig::get('sf_root_dir') . '/www/uploads/' . AgreementModelReport::ADDITIONAL_FILE_PATH . '/' . $favItem->getReport()->$func();
                $copyFile = $favItem->getReport()->$func();
                if (file_exists($imageFile)) {
                    $copyFileAr = F::imageResize($imageFile, 2024);

                    $copyFile = $copyFileAr['file'];
                    $maxW = $copyFileAr['aw'];
                    $maxH = $copyFileAr['ah'];
                }

                if (!is_null($copyFile)) {
                    $htmlText .= '<br/>';
                    $htmlText .= '<a target="_blank" href="' . sfConfig::get('app_site_url') . 'uploads/' . AgreementModelReport::ADDITIONAL_FILE_PATH . '/' . $copyFile . '">';

                    if ($maxW > 750) {
                        $maxW = 750;
                    }

                    if ($maxW > $maxH) {
                        $htmlText .= '<img style="width: ' . $maxW . 'px; -moz-box-shadow: inset 3px 3px 3px rgba(0,0,0,0.1); -webkit-box-shadow: inset 3px 3px 3px rgba(0,0,0,0.1); box-shadow: inset 3px 3px 3px rgba(0,0,0,0.1); margin-top: 10px; text-align: center;" src="uploads/' . AgreementModelReport::ADDITIONAL_FILE_PATH . '/' . $copyFile . '"/>';
                    } else {
                        $htmlText .= '<img style="width: ' . $maxW . 'px; max-height: 600px; -moz-box-shadow: inset 3px 3px 3px rgba(0,0,0,0.1); -webkit-box-shadow: inset 3px 3px 3px rgba(0,0,0,0.1); box-shadow: inset 3px 3px 3px rgba(0,0,0,0.1); margin-top: 10px; text-align: center;" src="uploads/' . AgreementModelReport::ADDITIONAL_FILE_PATH . '/' . $copyFile . '"/>';
                    }
                    $htmlText .= '</a>';
                }

                $htmlText .= '</div';

                if ($itemIndex != $totalItems) {
                    $htmlText .= '<img style="page-break-after: always; margin-top: 5px; margin-right: 15px; float: right;" src="images/logo.png"/>';
                } else {
                    $htmlText .= '<img style="margin-top: 5px; margin-right: 15px; float: right;" src="images/logo.png"/>';
                }

                $htmlText .= '</div>';

                $itemIndex++;
            }
        }

        //$htmlText = iconv('UTF-8', 'windows-1251', $htmlText);

        $html = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <style>
            * {
                font-family: times;
                line-height: 1em;
             }

             @page { margin: 0px !important; padding: 0px !important; }
        </style>
    </head>
 <body>
 ' . $htmlText . '
 </body>
</html>';

        $dompdf = new DOMPDF();
        $dompdf->load_html($html);
        $dompdf->set_paper(DOMPDF_DEFAULT_PAPER_SIZE, 'landscape');
        $dompdf->render();
        //$dompdf->stream(sfConfig::get('sf_root_dir')."/uploads/sample.pdf", array("Attachment" => true));
        $output = $dompdf->output();

        $fileName = "gen_" . rand(1, 10000) . ".pdf";
        $toFile = sfConfig::get('sf_root_dir') . "/www/uploads/pdf_gen/" . $fileName;
        file_put_contents($toFile, $output);

        return $this->sendJson(array('success' => true, 'fileUrl' => sfConfig::get('app_site_url') . "uploads/pdf_gen/" . $fileName));
    }

    public function executeModelsLoadDiscussionCount(sfWebRequest $request)
    {
        $models = AgreementModelTable::getInstance()
            ->createQuery()
            ->whereIn('id', explode(':', $request->getParameter('models')))
            ->execute();

        $designer_filter = $request->getParameter('designer_filter') == 1 ? true : false;

        $result = array();
        foreach ($models as $model) {
            $discussion = $model->getDiscussion();
            $new_messages_count = $discussion ? $discussion->countUnreadMessages($this->getUser()->getAuthUser()) : 0;

            $result[$model->getId()] = array('count' => $new_messages_count, 'designer_filter' => $model->isModelAcceptActiveToday($designer_filter));
        }

        return $this->sendJson(array('data' => $result, 'success' => count($result) > 0 ? true : false));
    }

    private function initPager($query, $object = 'AgreementModel', $items_per_page = -1)
    {
        if ($items_per_page == -1) {
            $items_per_page = sfConfig::get('app_max_models_on_page');
        }

        $request = $this->getRequest();
        $page = $request->getParameter('page', 1);
        if ($page) {
            $max_items_on_page = $items_per_page;
        } else {
            $max_items_on_page = 0;
            $page = 1;
        }

        $this->page = $page;

        $this->pager = new sfDoctrinePager(
            $object,
            $max_items_on_page
        );

        $this->pager->setQuery($query);
        $this->pager->setPage($page);
        $this->pager->init();

        if ($this->pager->getLastPage() < $page) $this->pager->setPage($this->pager->getLastPage());
        $this->pager->init();
    }

    private function initPaginatorData($route_object, $route_name)
    {
        $request = $this->getRequest();
        $this->parameters = $request->getGetParameters();
        $this->pageLinkArray = array_merge($this->parameters, array('sf_subject' => $route_object));

        $this->paginatorData = array('pager' => $this->pager,
            'pageLinkArray' => $this->pageLinkArray,
            'route' => $route_name);
    }

    function getModelCommentsFiles(sfWebRequest $request, $field)
    {
        $files = $request->getFiles();
        if (!is_array($files)) {
            return $files;
        }

        /*if (isset($files['model_file']) && isset($files['model_file']['tmp_name']) && $files['model_file']['tmp_name']) {
            return $files;
        }*/

        $uploaded_files = $this->getUploadedFilesByField($files, $this->getModel($request), $field);
        if (!empty($uploaded_files)) {
            return $uploaded_files;
        }

        $server_file = $request->getPostParameter('server_model_file');
        if (!$server_file || preg_match('#[\\\/]#', $server_file)) {
            if (isset($files[$field]) && isset($files['model_file'][0])) {
                return array($field . '_1' => $files['model_file'][0]);
            }

            return $files;
        }

        $tmp_name = $this->getUser()->getAuthUser()->getDealerUploadPath() . '/' . $server_file;
        if (!file_exists($tmp_name)) {
            return $files;
        }

        $files[$field] = array(
            'name' => $server_file,
            'tmp_name' => $tmp_name,
            'type' => F::getFileMimeType($server_file)
        );

        return $files;
    }

    private function getUploadedFilesByField($files, $model, $field)
    {
        $fields = array($field);

        $max_upload_files = sfConfig::get('app_max_files_upload_count');

        $this->uploaded_files_result = array();
        foreach ($files as $key => $file) {
            if (isset($files[$key]['tmp_name']) && $files[$key]['tmp_name']) {
                $this->uploaded_files_result[$key] = $files[$key];
            }
        }

        $ind = 0;
        foreach ($fields as $field) {
            if (isset($files[$field]) && count($files[$field]) >= 0) {
                foreach ($files[$field] as $key => $values) {
                    if ($ind > $max_upload_files) {
                        break;
                    }

                    if ($ind == 0) {
                        $this->uploaded_files_result[$field] = $values;
                    } else if (!empty($values) && is_array($values)) {
                        $this->uploaded_files_result[$field . '_' . $ind] = $values;
                    }

                    $ind++;
                }
            } else if (isset($files[$field]) && isset($files[$field][0]['tmp_name']) && $files[$field][0]['tmp_name']) {
                $this->uploaded_files_result[$field] = $files[$field][0];
            }
        }

        return $this->uploaded_files_result;
    }

    /**
     * Download files by model and model file type
     * @param sfWebRequest $request
     * @throws sfStopException
     */
    public function executeDownloadAllFiles(sfWebRequest $request)
    {
        $model = $this->getModel($request);
        $by_type = $request->getParameter('model_file_type');

        $this->redirect(ModelReportFiles::packUploadedFilesToZip($model, $by_type));
    }

    /**
     * Отмечаем заявку просмотренной менеджером, с галочкой В макет не вносились изменения
     * @param sfWebRequest $request
     * @return string
     */
    public function executeNoModelChangesCheckViewed(sfWebRequest $request) {
        $model = AgreementModelTable::getInstance()->find($request->getParameter('model_id'));
        if ($model) {
            $model->setNoModelChangesView(true);
            $model->save();

            return $this->sendJson(array('success' => true));
        }

        return $this->sendJson(array('success' => false));
    }

    /**
     * Список согласованных заявок
     * @param sfWebRequest $request
     */
    public function executeAcceptedModelsList(sfWebRequest $request) {
        $this->outputDeclineReasons();
        $this->outputDeclineReportReasons();
        $this->outputSpecialistGroups();

        $query = AgreementModelTable::getInstance()->createQuery('m')
            ->innerJoin('m.LogEntry log')
            ->select('m.id, log.created_at as accepted_date')
            ->where('m.status = ? and log.action = ? and object_type = ?', array('accepted', 'accepted', 'agreement_model'))
            ->orderBy('accepted_date DESC');

        $this->initPager($query, 'AgreementModel', 50);
        $this->initPaginatorData(null, 'accepted_models_list');

        $this->models = $this->pager->getResults();
    }
}
