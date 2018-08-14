<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 03.04.2017
 * Time: 14:53
 */

class AgreementModelSpecialistStatusUtils extends AgreementModelManagerDesignerStatusUtils
{
    function acceptComment(AgreementModelComment $comment, User $user, $comments = '', $msg_files = array(), $model_status = '')
    {
        $model = AgreementModelTable::getInstance()->find($comment->getModelId());

        /*Agreement status*/
        //$statusLabel = $model->isConcept() ? 'Концепция согласована.' . ($user->isDesigner() ? 'дизайнером' : 'менеджером') . '.' : 'Макет согласован ' . ($user->isDesigner() ? 'дизайнером' : 'менеджером') . '.';
        $discussionLabel = $model->isConcept()
            ? (($model->getManagerStatus() == 'accepted' || $model->getDesignerStatus() == 'accepted') ? 'Концепция согласована.' : '')
            : (($model->getManagerStatus() == 'accepted' || $model->getDesignerStatus() == 'accepted') ? 'Макет согласован.' : '');

        if ($model->isModelScenario()) {
            if (($model->getStep1() == 'accepted' && $model->getStep2() != "accepted" && $model->getStep2() != "wait") || ($model->getStep1() == 'wait' && $model->getStep2() == 'wait')) {
                $discussionLabel = ($model->getManagerStatus() == 'accepted' || $model->getDesignerStatus() == 'accepted') ? "Сценарий согласован." : '';
            } else if(($model->getStep1() == 'accepted' && $model->getStep2() == 'wait') || $model->getStep2() == 'accepted') {
                $discussionLabel = ($model->getManagerStatus() == 'accepted' || $model->getDesignerStatus() == 'accepted') ? "Запись согласована." : '';
            }
        }

        if ($model->getManagerStatus() != 'wait') {
            if ($model->getManagerStatus() == 'accepted') {
                $comment->setStatus('accepted');
            } else if ($model->getManagerStatus() == 'declined' || $model->getDesignerStatus() == 'declined') {
                if ($model->getStep1() == 'accepted' && $model->getStep2() == 'accepted') {
                    $model->getStep2('none');
                } else  {
                    $model->getStep1('none');
                    $model->getStep2('none');
                }
                $comment->setStatus('declined');
            }
        } else {
            $comment->setStatus('accepted');
        }
        $comment->save();

        $entry = LogEntryTable::getInstance()->addEntry(
            $user,
            $model->isConcept() ? 'agreement_concept' : 'agreement_model',
            'accepted_by_specialist',
            $model->getActivity()->getName() . '/' . $model->getName(),
            //$model->isConcept() ? 'Концепция утверждена специалистом' : 'Макет утверждён специалистом',
            $discussionLabel,
            'ok',
            $model->getDealer(),
            $model->getId(),
            'agreement'
        );

        $model->createPrivateLogEntryForSpecialists($entry);
        $commentFiles = array();
        if (!empty($msg_files)) {
            $file = array_shift($msg_files);
            $commentFiles[] = $file;
            $model->addAcceptFile($file);

            foreach ($msg_files as $file) {
                $commentFiles[] = $file;
                $model->addAcceptFile($file);
            }
        }

        if (!empty($discussionLabel)) {
            $this->addMessageToDiscussion($model, $user, $discussionLabel);
        }

        $message = null;
        $status_label = 'Комментарий дизайнера. ';
        if (!empty($comments)) {
            $message = $this->addMessageToDiscussion($model, $user, $status_label . ($comments ? $comments : ''));
        }

        if (!$message && count($commentFiles) > 0) {
            $message = $this->addMessageToDiscussion($model, $user, $status_label);
        }

        if ($message && count($commentFiles) > 0) {
            $this->attachModelCommentsFileToMessage($model, $message, $commentFiles, true);
        }

        !$this->_accept_decline_message = true;
        $this->syncModelAndCommentsStatus($model, $user, true, $model_status);

        /*if ($model->getManagerStatus() == 'declined') {
            MessageTable::cloneMessage($model, $user);
        }*/

        if ($entry && $model->getManagerStatus() == "accepted") {
            AgreementManagementHistoryMailSender::send(
                'AgreementModelCommentAcceptedMail',
                $entry,
                array(
                    'specialist' => $user,
                    'comment' => $comments
                ),
                'manager',
                $model->isConcept() ? AgreementManagementHistoryMailSender::AGREEMENT_CONCEPT_NOTIFICATION : AgreementManagementHistoryMailSender::AGREEMENT_NOTIFICATION,
                null,
                $this->canSendMail(),
                $this->getMsgType()
            );

            AgreementCompleteModelMailSender::send($model, $this->canSendMail(), $this->getMsgType());
        }
    }

    function declineComment(AgreementModelComment $comment, User $user, $comments = '', $msg_files = array(), $model_status = '')
    {
        $comment->setStatus('declined');
        $comment->save();

        $model = AgreementModelTable::getInstance()->find($comment->getModelId());
        if ($model->getManagerStatus() == "accepted") {
            MailMessageTable::getInstance()->createQuery()->delete()->where('model_id = ?', $model->getId())->execute();
        }

        /*Agreement status*/
        $statusLabel = $statusLabelDiscussion = $model->isConcept() ? 'Концепция отклонена. Внесите комментарии.': 'Макет отклонен. Внесите комментарии.';
        if ($model->isModelScenario()) {
            $model->setStatus(!empty($model_status) ? $model_status : 'declined');
            if ($model->getStep1() != "accepted") {
                $model->setStep1('none');
                $model->setStep2('none');
            } else if ($model->getStep1() == "accepted") {
                $model->setStep2('none');
            }
            $model->save();

            if ($model->getStep1() != "accepted") {
                $statusLabel = $statusLabelDiscussion = "Сценарий отклонен. Внесите комментарии.";
            } else if ($model->getStep1() == "accepted") {
                $statusLabel = $statusLabelDiscussion = "Запись отклонена. Внесите комментарии.";
            }
        }

        $entry = LogEntryTable::getInstance()->addEntry(
            $user,
            $model->isConcept() ? 'agreement_concept' : 'agreement_model',
            'declined_by_specialist',
            $model->getActivity()->getName() . '/' . $model->getName(),
            //$model->isConcept() ? 'Концепция отклонена специалистом.' : 'Макет отклонён специалистом.',
            $statusLabel,
            !empty($msg_files) ? 'clip' : '',
            $model->getDealer(),
            $model->getId(),
            'agreement'
        );

        $model->createPrivateLogEntryForSpecialists($entry);

        $commentFiles = array();
        if (!empty($msg_files)) {
            $file = array_shift($msg_files);

            $commentFiles[] = $file;
            $model->setAgreementCommentsFile($file);
            $model->addDeclineFile($file);

            foreach ($msg_files as $file) {
                $commentFiles[] = $file;
                $model->addDeclineFile($file);
            }
        }

        $this->_accept_decline_message = true;

        $this->syncModelAndCommentsStatus($model, $user, false, $model_status);

        if ($model->getManagerStatus() != 'declined') {
            $this->addMessageToDiscussion($model, $user, $statusLabelDiscussion);
        }

        $model->setAgreementComments($comments);
        $model->setAgreementCommentManager('');
        $model->save();

        //MessageTable::cloneMessage($model, $user);

        $message = null;
        if (!empty($comments)) {
            $message = $this->addMessageToDiscussion($model, $user, 'Комментарий дизайнера. '.$comments, true, Message::MSG_STATUS_DECLINED_BY_SPECIALIST);
        }

        /*if ($comments_file && !$message) {
            $message = $this->addMessageToDiscussion($model, $user, $statusLabelDiscussion);
        }*/
        if (empty($comments) && !empty($commentFiles)) {
            $message = $this->addMessageToDiscussion($model, $user, 'Комментарий дизайнера. ', true, Message::MSG_STATUS_DECLINED_BY_SPECIALIST);
        }

        $attached_file = array();
        if ($message && !empty($commentFiles)) {
            $attached_file = $this->attachCommentsFileToMessage($message, $commentFiles)->getFile();
        }

        AgreementDealerHistoryMailSender::send('AgreementModelDeclinedMail', $entry, $model->getDealer(), $message, $this->canSendMail(), $this->getMsgType());
        AgreementManagementHistoryMailSender::send(
            'AgreementModelCommentDeclinedMail',
            $entry,
            array(
                'specialist' => $user,
                'comment' => $comments,
                'comment_file' => $attached_file
            ),
            'manager',
            $model->isConcept() ? AgreementManagementHistoryMailSender::AGREEMENT_CONCEPT_NOTIFICATION : AgreementManagementHistoryMailSender::AGREEMENT_NOTIFICATION,
            null,
            $this->canSendMail(),
            $this->getMsgType()
        );
    }

    protected function addDiscussionMessageByStatus($model, $user, $discussionLabel) {
        return null;
    }

    protected function canSendMail() {
        return false;
    }
}
