<?php

/**
 * Description of AgreementReportStatusUtils
 *
 * @author Сергей
 */
class AgreementModelReportManagerDesignerStatusUtils extends  AgreementReportStatusUtils
{
    protected function showMessage() {
        return false;
    }

    protected function canSendMail() {
        return false;
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
        $message->setMsgShow($this->showMessage());
        $message->save();

        // mark as unread
        $discussion->getUnreadMessages($user);

        return $message;
    }

    function acceptComment(AgreementModelReportComment $comment, User $user, $comments = '', $msg_files = array(), $comment_from = 'менеджера', $report_status = '')
    {
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
                    'Отчёт отклонен  менеджером / дизайнером',
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

        $this->syncReportAndCommentsStatus($report, $user, self::NO_COMMENTS);
        if ($report->getDesignerStatus() == 'declined' && $report->getManagerStatus() != 'declined') {
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
    }

    function declineReport(AgreementModelReport $report, User $user, AgreementDeclineReason $reason = null, $comments = '', $msg_files = array())
    {
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

            $model = $report->getModel();
            if ($report->getManagerStatus() != 'declined') {
                $message = $this->addMessageToDiscussion($model, $user, 'Отчет не согласован. Внесите комментарии.', Message::MSG_STATUS_DECLINED);
            }

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
    }
}
