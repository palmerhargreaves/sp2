<?php

/**
 * Description of AgreementModelAcceptedMail
 *
 * @author Сергей
 */
class AgreementModelDeclinedMail extends HistoryMail
{
    function __construct(LogEntry $entry, User $user, Message $message = null)
    {
        $model = AgreementModelTable::getInstance()->find($entry->getObjectId());
        $reason = $model->getDeclineReason() && $model->getDeclineReason()->getName()
            ? 'по следующей причине: "' . $model->getDeclineReason()->getName() . '"'
            : '';

        $type = $model->isConcept() ? 'Концепция' : 'Макет';
        $ending = $model->isConcept() ? 'а' : '';

        $text = $model->isConcept()
            ? $this->getConceptText($entry, $reason, $model)
            : $this->getModelText($entry, $reason, $model);

        $label = $this->getMailLabel($model);
        if ($model->getAgreementComments()) {
            $label .= nl2br($model->getAgreementComments());
        }
        $text .= '<p>'.$label.'</p>';

        if ($model->getAgreementCommentsFile()) {
            if(is_null($message)) {
                $text .= '<p><a href="' . sfConfig::get('app_site_url') . '/uploads/' . AgreementModel::AGREEMENT_COMMENTS_FILE_PATH . '/' . $model->getAgreementCommentsFile() . '">Скачать файл с комментариями</a></p>';
            } else {
                $messageFiles = MessageFileTable::getInstance()->createQuery()->where('message_id = ?', $message->getId())->execute();
                foreach($messageFiles as $file) {
                    $url = sfContext::getInstance()->getController()->genUrl("@agreement_model_discussion_message_download_file?file=".$file->getId(), true);
                    $text .= "<a href='".$url."' target='_blank'>Скачать файл с комментариями: ".$file->getFile()."(".$file->getFileNameHelper()->getSmartSize().")</a><br/>";
                }
            }
        }

        parent::__construct(
            $user->getEmail(),
            'global/mail_common_dealer',
            array(
                'user' => $user,
                'subject' => "$type не согласован{$ending}",
                'text' => $text
            )
        );
    }

    /**
     * @param AgreementModel $model
     * @return string
     */
    private function getMailLabel($model) {
        $label = "Внесите комментарии менеджера. ";

        if ($model->isValidModelCategory() && $model->getModelCategory()->getWorkType() == "manager_designer") {
            //If designer has action, set label to him
            if ($model->getStatus() == 'wait') {
                $label = "Внесите комментарии дизайнера. ";
            }
            //If manager has action, set label to him
            else if ($model->getStatus() == 'wait_specialist') {
                $label = "Внесите комментарии менеджера. ";
            }
        } else {
            if($model->getManagerStatus() == 'declined' && $model->getDesignerStatus() == 'wait') {
                $label = "Внесите комментарии менеджера. ";
            }
            else if ($model->getDesignerStatus() == 'declined') {
                $label = "Внесите комментарии дизайнера. ";
            } else if ($model->getManagerStatus() == 'wait' && $model->getDesignerStatus() == 'wait') {
                $label = "Внесите комментарии менеджера. ";
            }
        }

        return $label;
    }

    protected function getConceptText(LogEntry $entry, $reason, AgreementModel $model)
    {
        return
            <<<TEXT
                        <p>Концепция № {$model->getId()} «<a href="{$this->getHistoryUrl($entry)}">{$model->getName()}</a>» не согласована.</p>
TEXT;
    }

    protected function getModelText(LogEntry $entry, $reason, AgreementModel $model)
    {
        return
            <<<TEXT
                        <p>Макет № {$model->getId()} «<a href="{$this->getHistoryUrl($entry)}">{$model->getName()}</a>» не согласован.</p>
TEXT;
    }
}
