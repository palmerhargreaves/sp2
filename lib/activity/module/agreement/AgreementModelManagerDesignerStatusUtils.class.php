<?php

/**
 * Description of AgreementStatusUtils
 *
 * @author Сергей
 */
class AgreementModelManagerDesignerStatusUtils extends AgreementModelStatusUtils
{
    /**
     * Add message to discussion
     *
     * @param AgreementModel $model
     * @param string $text
     * @return Message|false
     */
    function addMessageToDiscussion(AgreementModel $model, User $user, $text, $show_msg = true, $msg_status = 'none')
    {
        $discussion = $model->getDiscussion();

        if (!$discussion) {
            return;
        }

        if (empty($text)) {
            return;
        }

        $message = new Message();
        $message->setDiscussionId($discussion->getId());
        $message->setUser($user);
        $message->setUserName($user->selectName());
        $message->setText($text);
        $message->setSystem(true);
        $message->setMsgShow(false);
        $message->setMsgStatus($this->_msg_status);
        $message->setMsgType($this->_msg_type);
        $message->save();

        // mark as unread
        $discussion->getUnreadMessages($user);

        return $message;
    }

    protected function canSendMail() {
        return false;
    }

    function getDiscussionDeclineMessage(AgreementModel $model) {
        $statusLabel = $statusDiscussionLabel = $model->isConcept() ? 'Концепция отклонена. Внесите комментарии.': 'Макет отклонен. Внесите комментарии.';
        if ($model->isModelScenario()) {
            if ($model->getStep1() == "none" && $model->getStep2() == "none") {
                $statusLabel = "Сценарий отклонён";
                $statusDiscussionLabel = 'Сценарий отклонен. Внесите комментарии.';
            } else if ($model->getStep2() == "none") {
                $statusLabel = "Запись отклонёна";
                $statusDiscussionLabel = 'Запись отклонена. Внесите комментарии.';
            }
        }

        return array($statusLabel, $statusDiscussionLabel);
    }

    function getDiscussionAcceptMessage(AgreementModel $model, $model_status = '') {
        /*Agreement status*/
        $statusLabelDiscussion = '';

        if ($model->isModelScenario()) {
            if ($model->getStep1() == "accepted" && ($model->getStep2() == "accepted" || $model->getStep2() == 'wait')) {
                $statusLabelDiscussion = 'Запись согласована.';
                $model->setStatus(!empty($model_status) ? $model_status : 'accepted');
            } else if ($model->getStep1() == "accepted" && $model->getStep2() == "none") {
                $statusLabelDiscussion = 'Сценарий согласован.';
            }else if (!empty($model_status) && $model->getStep1() == 'wait') {
                $statusLabelDiscussion = 'Сценарий согласован.';
            }
        } else {
            if (!empty($model_status) && $model_status == 'accepted') {
                $statusLabelDiscussion = ($model->isConcept() ? 'Концепция согласована. ' : 'Макет согласован. ');
            } else {
                if ($model->getManagerStatus() == 'wait' && $model->getDesignerStatus() == 'wait') {
                    $statusLabelDiscussion = ($model->isConcept() ? 'Концепция согласована. ' : 'Макет согласован. ');
                } else {
                    $statusLabelDiscussion = ($model->isConcept() ? 'Концепция согласована. ' : 'Макет согласован. ');
                }
            }
        }

        return $statusLabelDiscussion;
    }
}
