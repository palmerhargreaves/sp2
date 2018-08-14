<?php

/**
 * Description of ActivityCreateMail
 *
 */
class AgreementModelNoModelChanges extends HistoryMail
{
    function __construct(LogEntry $entry, User $user)
    {
        $model = AgreementModelTable::getInstance()->find($entry->getObjectId());
        $subject = sprintf('Новая заявка с галочкой В макет не вносились изменения');

        parent::__construct(
            $user->getEmail(),
            'global/mail_common',
            array(
                'user' => $user,
                'subject' => $subject,
                'text' =>
<<<TEXT
        <p>
        Дилер "{$model->getDealer()->getName()}" ({$model->getDealer()->getNumber()}) отправил заявку с галочкой "В макет не вносились изменения"
        "<a href="{$this->getHistoryUrl($entry)}">{$model->getName()}</a>".
        </p>
TEXT
            )
        );
    }
}
