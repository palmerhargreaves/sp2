<?php

/**
 * Description of AgreementReportStatusUtils
 *
 * @author Сергей
 */
class AgreementReportStatusUtils
{
    const NO_COMMENTS = 'no_comments';

    protected $_accept_decline_comments = false;
    protected $_msg_status = 'none';
    protected $_msg_type = 'none';
    protected $_can_send_mail = true;
    protected $_show_msg = true;

    function acceptReport(AgreementModelReport $report, User $user, $comments = '', $msg_files = array())
    {
        $message = null;

        $model = $report->getModel();
        if ($model->getManagerStatus() == 'accepted' && $model->getDesignerStatus() == 'accepted') {
            $report->setStatus('accepted');
            $report->setAcceptDate(D::toDb(time(), true));
            $report->setAcceptProcessed(false);
        }

        if ($comments != self::NO_COMMENTS) {
            $report->setAgreementComments($comments);
        }

        $report->save();

        $model = AgreementModelTable::getInstance()->find($report->getModelId());

        if (!$model->isConcept()) {
            RealBudgetTable::getInstance()->removeByObjectOnly(ActivityModule::byIdentifier('agreement'), $model->getId());
            RealBudgetTable::getInstance()->addByReportDate(
                $model->getDealer(),
                $model->getCost(),
                ActivityModule::byIdentifier('agreement'),
                $report->created_at,
                $model->getId()
            );
        }

        $activity_utils = new AgreementActivityStatusUtils($model->getActivity(), $model->getDealer());
        $activity_utils->updateActivityAcceptance();

        if (!$this->_accept_decline_comments) {
            $entry = LogEntryTable::getInstance()->addEntry(
                $user,
                $model->isConcept() ? 'agreement_concept_report' : 'agreement_report',
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

            if (!empty($comments) && $comments != self::NO_COMMENTS) {
                $message_text = 'Комментарии менеджера. '.$comments;
                $this->addMessageToDiscussion(
                    $model,
                    $user,
                    $message_text
                );
            }

//    $model->createPrivateLogEntryForSpecialists($entry);
            if ($comments != self::NO_COMMENTS) {
                $comment = $report->getAgreementComments() ?: 'Отчёт согласован';
                if ($model->getManagerStatus() != 'wait') {
                    $comment = 'Отчет согласован ' . ($user->isDesigner() ? 'дизайнером' : 'менеджером') . '.' . $report->getAgreementComments();
                }

                $message = $this->addMessageToDiscussion($model, $user, $comment);
                if ($message && count($commentFiles) > 0) {
                    $this->attachReportCommentsFileToMessage($report, $message, $commentFiles, true);
                }
            }

//    AgreementDealerHistoryMailSender::send('AgreementReportAcceptedMail', $entry, $model->getDealer());
//    AgreementManagementHistoryMailSender::send('AgreementReportAcceptedMail', $entry, false, false, AgreementManagementHistoryMailSender::FINAL_AGREEMENT_NOTIFICATION);
            AgreementCompleteReportMailSender::send($report);
        }

        return $message;
    }

    function declineReport(AgreementModelReport $report, User $user, AgreementDeclineReason $reason = null, $comments = '', $msg_files = array())
    {
        $message = null;

        $report->setStatus('declined');
        //$report->setDeclineReasonId($reason ? $reason->getId() : 0);

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

        if (!$this->_accept_decline_comments) {
            $entry = LogEntryTable::getInstance()->addEntry(
                $user,
                $model->isConcept() ? 'agreement_concept_report' : 'agreement_report',
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

//    $model->createPrivateLogEntryForSpecialists($entry);
            $model = $report->getModel();
            $message = $this->addMessageToDiscussion($model, $user, 'Отчет не согласован. Внесите комментарии.', Message::MSG_STATUS_DECLINED);

            if (!empty($comments) && $comments != self::NO_COMMENTS) {
                $message = $this->addMessageToDiscussion(
                    $model,
                    $user,
                    'Комментарии менеджера. ' . $comments,
                    Message::MSG_STATUS_DECLINED
                );
            }

            if ($message && $comments_file) {
                $this->attachReportCommentsFileToMessage($report, $message, $commentFiles);
            }

            AgreementDealerHistoryMailSender::send('AgreementReportDeclinedMail', $entry, $model->getDealer());
            AgreementManagementHistoryMailSender::send(
                'AgreementReportDeclinedMail',
                $entry,
                false,
                false,
                $model->isConcept() ? AgreementManagementHistoryMailSender::AGREEMENT_CONCEPT_REPORT_NOTIFICATION : AgreementManagementHistoryMailSender::AGREEMENT_REPORT_NOTIFICATION
            );
        }

        return $message;
    }

    function syncReportAndCommentsStatus(AgreementModelReport $report, User $user, $comments = '')
    {
        $waits = AgreementModelReportCommentTable::getInstance()
            ->createQuery()
            ->where('report_id=? and status=?', array($report->getId(), 'wait'))
            ->count();

        if ($waits == 0) {
            $declined = AgreementModelReportCommentTable::getInstance()
                    ->createQuery()
                    ->where('report_id=? and status=?', array($report->getId(), 'declined'))
                    ->count() > 0;

            if ($declined) {
                /*$comment = 'Отчёт не согласован. См. замечания специалистов.';

                $model = $report->getModel();
                if ($model->getManagerStatus() != 'wait') {
                    $comment = $comment = 'Отчет требует доработок. Внесите комментарии '.($user->isDesigner() ? 'дизайнера' : 'менеджера').'.';
                }*/

                $this->declineReport($report, $user, null, $comments);
            }
            else {
                $this->acceptReport($report, $user, $comments);
            }
        }
    }

    function acceptComment(AgreementModelReportComment $comment, User $user, $comments = '', $msg_files = array(), $comment_from = 'менеджера', $report_status = '')
    {
        $message = null;

        $report = AgreementModelReportTable::getInstance()->find($comment->getReportId());
        $model = $report->getModel();

        $comment->setStatus('accepted');
        if ($report->getManagerStatus() != 'wait') {
            if ($report->getManagerStatus() == 'declined' || $report->getDesignerStatus() == 'declined') {
                $comment->setStatus('declined');

                $entry = LogEntryTable::getInstance()->addEntry(
                    $user,
                    $model->isConcept() ? 'agreement_concept_report' : 'agreement_report',
                    'accepted_by_specialist',
                    $model->getActivity()->getName() . '/' . $model->getName(),
                    'Отчё отклонен  менеджером / дизайнером',
                    'ok',
                    $model->getDealer(),
                    $model->getId(),
                    'agreement'
                );
            } else {
                $entry = LogEntryTable::getInstance()->addEntry(
                    $user,
                    $model->isConcept() ? 'agreement_concept_report' : 'agreement_report',
                    'accepted_by_specialist',
                    $model->getActivity()->getName() . '/' . $model->getName(),
                    'Отчёт утверждён специалистом',
                    'ok',
                    $model->getDealer(),
                    $model->getId(),
                    'agreement'
                );
            }
        }
        $comment->save();

        $model->createPrivateLogEntryForSpecialists($entry);

        $commentFiles = array();
        if (!empty($msg_files)) {
            foreach ($msg_files as $file) {
                $commentFiles[] = $file;
                $model->addAcceptFile($file);
            }
        }

        $this->_accept_decline_comments = true;

        $this->syncReportAndCommentsStatus($report, $user);
        if ($report->getDesignerStatus() == 'declined') {
            $message = $this->addMessageToDiscussion($model, $user, 'Отчет не согласован. Внесите комментарии.');

            $message_comments = null;
            if ($comments && $comment->getStatus() != 'accepted') {
                $message_comments = $this->addMessageToDiscussion($model, $user, 'Комментарий ' . $comment_from . '. ' . $comments);
            }

            if (($message || $message_comments) && count($commentFiles) > 0) {
                $this->attachCommentsFileToMessage($message_comments ? $message_comments : $message, $commentFiles, true);
            }
        } else if ($report->getManagerStatus() == 'accepted' && $report->getDesignerStatus() == 'accepted') {
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

        return $message;
    }

    function declineComment(AgreementModelReportComment $comment, User $user, $comments = '', $msg_files = array(), $comment_from = 'менеджера', $report_status = '')
    {
        $message = null;

        $comment->setStatus('declined');
        $comment->save();

        $report = AgreementModelReportTable::getInstance()->find($comment->getReportId());
        $model = $report->getModel();

        $entry = LogEntryTable::getInstance()->addEntry(
            $user,
            $model->isConcept() ? 'agreement_concept_report' : 'agreement_report',
            'declined_by_specialist',
            $model->getActivity()->getName() . '/' . $model->getName(),
            'Отчёт отклонён специалистом.',
            !empty($msg_files) ? 'clip' : '',
            $model->getDealer(),
            $model->getId(),
            'agreement'
        );

        $model->createPrivateLogEntryForSpecialists($entry);

        $commentFiles = array();
        if (!empty($msg_files)) {
            foreach ($msg_files as $file) {
                $commentFiles[] = $file;
                $model->addDeclineFile($file);
            }
        }

        $statusLabel = 'Отчёт требует доработок. Внесите комментарии.';

        $this->_accept_decline_comments = true;
        $this->syncReportAndCommentsStatus($report, $user);

        $message = null;
        if ($report->getManagerStatus() != 'declined') {
            $message = $this->addMessageToDiscussion($model, $user, $statusLabel, Message::MSG_STATUS_DECLINED_BY_SPECIALIST);
        }

        $comment_msg = null;
        if ($comments) {
            $comment_msg = $this->addMessageToDiscussion($model, $user, 'Комментарий '.$comment_from.'. '.$comments, Message::MSG_STATUS_DECLINED_BY_SPECIALIST);
        }

        $attached_file = array();
        if (($message || $comment_from) && !empty($commentFiles)) {
            $attached_file = $this->attachCommentsFileToMessage($comment_msg ? $comment_msg : $message, $commentFiles)->getFile();
        }

        AgreementDealerHistoryMailSender::send('AgreementReportDeclinedMail', $entry, $model->getDealer());
        AgreementManagementHistoryMailSender::send(
            'AgreementModelCommentDeclinedMail',
            $entry,
            array(
                'specialist' => $user,
                'comment' => $comments,
                'comment_file' => $attached_file
            ),
            'manager',
            $model->isConcept() ? AgreementManagementHistoryMailSender::AGREEMENT_CONCEPT_REPORT_NOTIFICATION : AgreementManagementHistoryMailSender::AGREEMENT_REPORT_NOTIFICATION
        );

        return $message;
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
        $message->setMsgStatus($msg_status);
        $message->save();

        // mark as unread
        $discussion->getUnreadMessages($user);

        return $message;
    }

    protected function attachReportCommentsFileToMessage(AgreementModelReport $report, Message $message, $commentFiles, $accept = false)
    {

        if (!$accept) {
            $file = new MessageFile();
            $file->setMessageId($message->getId());
            $file->setFile($message->getId() . '-' . $report->getAgreementCommentsFile());

            copy(
                sfConfig::get('sf_upload_dir') . '/' . AgreementModelReport::AGREEMENT_COMMENTS_FILE_PATH . '/' . $report->getAgreementCommentsFile(),
                sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH . '/' . $file->getFile()
            );

            $file->save();
        }

        foreach ($commentFiles as $commentFile) {
            $file = new MessageFile();
            $file->setMessageId($message->getId());
            $file->setFile($message->getId() . '-' . $commentFile);

            copy(
                sfConfig::get('sf_upload_dir') . '/' . AgreementModelReport::AGREEMENT_COMMENTS_FILE_PATH . '/' . $commentFile,
                sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH . '/' . $file->getFile()
            );

            $file->save();
        }
    }

    /**
     * Attaches a file to message
     *
     * @param Message $message
     * @param $commentFiles
     * @param bool $accept
     * @return MessageFile attached file
     */
    function attachCommentsFileToMessage(Message $message, $commentFiles, $accept = false)
    {
        /*if (!$accept) {
            $file = new MessageFile();
            $file->setMessageId($message->getId());
            $file->setFile($message->getId() . '-' . $uploaded_file->generateFilename());

            $uploaded_file->save(sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH . '/' . $file->getFile());
            $file->save();
        }*/

        foreach ($commentFiles as $commentFile) {
            $fileC = new MessageFile();
            $fileC->setMessageId($message->getId());
            $fileC->setFile($message->getId() . '-' . $commentFile);

            copy(
                sfConfig::get('sf_upload_dir') . '/' . AgreementModelReport::AGREEMENT_COMMENTS_FILE_PATH . '/' . $commentFile,
                sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH . '/' . $fileC->getFile()
            );

            $fileC->save();
        }

        return isset($file) ? $file : $fileC;
    }

    public function setMsgStatus($msg_status) {
        $this->_msg_status = $msg_status;
    }

    protected function canSendMail() {
        return $this->_can_send_mail;
    }

    public function setCanSendMail($send) {
        $this->_can_send_mail = $send;
    }

    public function getMsgType() {
        return $this->_msg_type;
    }

    public function setMsgType($msg_type) {
        $this->_msg_type = $msg_type;
    }
}
