<?php

/**
 * Description of AgreementReportAcceptedMail
 *
 * @author Сергей
 */
class AgreementReportDeclinedMail extends HistoryMail
{
    function __construct(LogEntry $entry, User $user)
    {
        $model = AgreementModelTable::getInstance()->find($entry->getObjectId());
        $report = $model->getReport();
        $reason = $model->getDeclineReason() && $model->getDeclineReason()->getName()
            ? 'по следующей причине: "' . $model->getDeclineReason()->getName() . '"'
            : '';

        $text =
            <<<TEXT
                    <p>
        Отчёт "<a href="{$this->getHistoryUrl($entry)}">{$report->getModel()->getName()}</a>" дилера "{$model->getDealer()->getName()}" ({$model->getDealer()->getNumber()}) 
        отклонён {$reason}
        </p>
TEXT;

        if ($report->getAgreementComments())
            $text .= '<p>' . nl2br($report->getAgreementComments()) . '</p>';

        if ($report->getAgreementCommentsFile())
            $text .= '<p><a href="' . sfConfig::get('app_site_url') . '/uploads/' . AgreementModelReport::AGREEMENT_COMMENTS_FILE_PATH . '/' . $report->getAgreementCommentsFile() . '">Скачать файл с комментариями</a></p>';

        parent::__construct(
            $user->getEmail(),
            'global/mail_common',
            array(
                'user' => $user,
                'subject' => 'Отчёт отклонён',
                'text' => $text
            )
        );
    }
}
