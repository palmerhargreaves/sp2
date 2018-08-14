<?php

/**
 * Description of AgreementReportStatusUtils
 *
 */
class AgreementSpecialConceptReportImporterStatusUtils extends  AgreementReportStatusUtils
{
    protected $_can_send_mail = false;
    protected $_show_msg = false;

    protected function showMessage() {
        return $this->_show_msg;
    }

    public function setShowMessage($value) {
        $this->_show_msg = $value;
    }

    /**
     * Add message to discussion
     *
     * @param AgreementModel $model
     * @param string $text
     * @return Message|false
     */
    protected function addMessageToDiscussion(AgreementModel $model, User $user, $text, $msg_status = 'none')
    {
        $discussion = $model->getDiscussion();

        if (!$discussion)
            return;

        $message = new Message();
        $message->setDiscussionId($discussion->getId());
        $message->setUser($user);
        $message->setUserName($user->selectName());
        $message->setText($text);
        $message->setSystem(true);
        $message->setMsgStatus($this->_msg_status);
        $message->setMsgType($this->_msg_type);
        $message->setMsgShow($this->showMessage());
        $message->save();

        // mark as unread
        $discussion->getUnreadMessages($user);

        return $message;
    }

    function acceptReport(AgreementModelReport $report, User $user, $comments = '', $msg_files = array())
    {
        $message = null;

        if ($comments != self::NO_COMMENTS) {
            $report->setAgreementComments($comments);
        }
        $report->save();

        $model = AgreementModelTable::getInstance()->find($report->getModelId());

        $activity_utils = new AgreementActivityStatusUtils($model->getActivity(), $model->getDealer());
        $activity_utils->updateActivityAcceptance();

        //Для концепций отправленных рег. менеджером, статус заявки меняется в зависимости от действий дву пользователей (рег. менеджера и импортера)
        if (AgreementModelImporterTable::getInstance()->createQuery()->where('model_id = ? and status = ?',
            array(
                $model->getId(),
                'regional_manager'
            ))->count()) {
            //Если менеджер согласовал отчет, добавляем в логи информацию о том что импортер отклонил отчет
            if (SpecialAgreementConceptStatuses::status($model->getId(), SpecialAgreementConceptStatuses::AGREEMENT_REPORT_STATUS_REG_MANAGER_ACCEPTED)) {
                $message = $this->addMessageToDiscussion($model, $user, 'Отчёт утверждён.', Message::MSG_STATUS_ACCEPTED);
            }
        } else {
            $message = $this->addMessageToDiscussion($model, $user, 'Отчёт утверждён.', Message::MSG_STATUS_DECLINED);
        }

        LogEntryTable::getInstance()->addEntry(
            $user,
            'agreement_special_concept_report_importer',
            'accepted',
            $model->getActivity()->getName() . '/' . $model->getName(),
            'Отчёт утверждён',
            'ok',
            $model->getDealer(),
            $model->getId(),
            'agreement',
            true
        );

        $commentFiles = array();
        if (!empty($msg_files)) {
            foreach ($msg_files as $file) {
                $commentFiles[] = $file;
                $model->addAcceptFile($file);
            }
        }

        $this->addMessageToDiscussion(
            $model,
            $user,
            'Отчет согласован'
        );

        if (!empty($comments) && $comments != self::NO_COMMENTS) {
            $message_text = 'Комментарии специалиста. '.$comments;
            $this->addMessageToDiscussion(
                $model,
                $user,
                $message_text
            );
        }

        if ($comments != self::NO_COMMENTS) {
            /*$comment = $report->getAgreementComments() ?: 'Отчёт согласован';

            $message = $this->addMessageToDiscussion($model, $user, $comment);*/
            if ($message && count($commentFiles) > 0) {
                $this->attachReportCommentsFileToMessage($report, $message, $commentFiles, true);
            }
        }
        AgreementCompleteReportMailSender::send($report, $this->canSendMail(), $this->getMsgType());

        return $message;
    }

    function acceptComment(AgreementModelReportComment $comment, User $user, $comments = '', $msg_files = array(), $comment_from = 'менеджера', $report_status = '')
    {
        $report = AgreementModelReportTable::getInstance()->find($comment->getReportId());
        $model = $report->getModel();

        $comment->setStatus('accepted');
        $entry = LogEntryTable::getInstance()->addEntry(
            $user,
            'agreement_special_concept_report_importer',
            'accepted_by_specialist',
            $model->getActivity()->getName() . '/' . $model->getName(),
            'Отчёт утверждён специалистом',
            'ok',
            $model->getDealer(),
            $model->getId(),
            'agreement'
        );
        $comment->save();

        $model->createPrivateLogEntryForSpecialists($entry);

        $commentFiles = array();
        if (!empty($msg_files)) {
            foreach ($msg_files as $file) {
                $commentFiles[] = $file;
                $model->addAcceptFile($file);
            }
        }

        $this->syncReportAndCommentsStatus($report, $user, self::NO_COMMENTS);
        $this->addMessageToDiscussion($model, $user, 'Отчет согласован.');

        AgreementCompleteReportMailSender::send($report);
        AgreementManagementHistoryMailSender::send(
            'AgreementReportCommentAcceptedMail',
            $entry,
            array(
                'specialist' => $user,
                'comment' => $comments
            ),
            'manager',
            $model->isConcept() ? AgreementManagementHistoryMailSender::AGREEMENT_CONCEPT_REPORT_NOTIFICATION : AgreementManagementHistoryMailSender::AGREEMENT_REPORT_NOTIFICATION
        );
    }

    function declineReport(AgreementModelReport $report, User $user, AgreementDeclineReason $reason = null, $comments = '', $msg_files = array())
    {
        if (!empty($comments) && $comments != self::NO_COMMENTS) {
            $report->setAgreementComments($comments);
        }

        $comments_file = '';
        if (!empty($msg_files)) {
            $comments_file = array_shift($msg_files);
            $report->setAgreementCommentsFile($comments_file);
        }

        $report->save();

        $model = AgreementModelTable::getInstance()->find($report->getModelId());
        RealBudgetTable::getInstance()->removeByObjectOnly(ActivityModule::byIdentifier('agreement'), $model->getId());

        $utils = new AgreementActivityStatusUtils($model->getActivity(), $model->getDealer());
        $utils->updateActivityAcceptance();

        $entry = LogEntryTable::getInstance()->addEntry(
            $user,
            'agreement_special_concept_report_importer',
            'declined',
            $model->getActivity()->getName() . '/' . $model->getName(),
            $reason ? $reason->getName() . '.' : 'Отчёт отклонён.',
            $comments_file ? 'clip' : '',
            $model->getDealer(),
            $model->getId(),
            'agreement',
            true
        );

        $commentFiles = array();
        if (!empty($msg_files)) {
            foreach($msg_files as $file) {
                $commentFiles[] = $file;
                $model->addDeclineFile($file);
            }
        }

        $model = $report->getModel();

        //Для концепций отправленных рег. менеджером, статус заявки меняется в зависимости от действий дву пользователей (рег. менеджера и импортера)
        if (AgreementModelImporterTable::getInstance()->createQuery()->where('model_id = ? and status = ?',
            array(
                $model->getId(),
                'regional_manager'
            ))->count()) {
            //Если менеджер согласовал отчет, добавляем в логи информацию о том что импортер отклонил отчет
            if (!SpecialAgreementConceptStatuses::status($model->getId(), SpecialAgreementConceptStatuses::AGREEMENT_REPORT_STATUS_REG_MANAGER_DECLINED)) {
                $message = $this->addMessageToDiscussion($model, $user, 'Отчет не согласован. Внесите комментарии.', Message::MSG_STATUS_DECLINED);
            }
        } else {
            $message = $this->addMessageToDiscussion($model, $user, 'Отчет не согласован. Внесите комментарии.', Message::MSG_STATUS_DECLINED);
        }

        if (!empty($comments) && $comments != self::NO_COMMENTS) {
            $message = $this->addMessageToDiscussion(
                $model,
                $user,
                'Комментарии специалиста. ' . $comments,
                Message::MSG_STATUS_DECLINED
            );
        }

        if ($message && $comments_file) {
            $this->attachReportCommentsFileToMessage($report, $message, $commentFiles);
        }

        AgreementDealerHistoryMailSender::send('AgreementReportDeclinedMail', $entry, $model->getDealer(), null, $this->canSendMail(), $this->getMsgType());
        AgreementManagementHistoryMailSender::send(
            'AgreementReportDeclinedMail',
            $entry,
            false,
            false,
            $model->isConcept() ? AgreementManagementHistoryMailSender::AGREEMENT_CONCEPT_REPORT_NOTIFICATION : AgreementManagementHistoryMailSender::AGREEMENT_REPORT_NOTIFICATION,
            null,
            $this->canSendMail(),
            $this->getMsgType()
        );
    }

}
