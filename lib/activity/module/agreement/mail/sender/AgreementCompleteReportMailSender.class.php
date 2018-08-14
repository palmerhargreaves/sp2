<?php

/**
 * Description of AgreementCompleteReportMailSender
 *
 * @author Сергей
 */
class AgreementCompleteReportMailSender
{
    static function send(AgreementModelReport $report, $can_send_mail = true, $msg_type = 'none')
    {
        $additional_file_name = $report->getModel()->getModelType()->getReportFieldDescription();
        $send_to_importers = $additional_file_name == 'Фотоотчёт';

        AgreementCompleteMailSenderUtils::sendByDealer(
            function ($emails) use ($report) {
                return new AgreementCompleteReportMail($emails, $report);
            },
            $report->getModel()->getDealer(),
            $report->getModel()->isConcept() ? 'final_agreement_concept_report_notification' : 'final_agreement_report_notification',
            $send_to_importers,
            false,
            $report->getModel()->getId(),
            $can_send_mail,
            $msg_type
        );
    }
}
