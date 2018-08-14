<?php

/**
 * Description of AgreementCompleteModelMail
 *
 * @author Сергей
 */
class AgreementCompleteReportMail extends TemplatedMail
{
    function __construct(array $emails, AgreementModelReport $report)
    {
        $model = $report->getModel();

        parent::__construct(
            $emails[0],
            'agreement_activity_model_report/mail_complete_report',
            array(
                'model' => $model,
                'report' => $report,
                'dealer' => $model->getDealer(),
                'activity' => $model->getActivity()
            )
        );

        for ($n = 1, $l = count($emails); $n < $l; $n++) {
            if (isset($emails[$n]) && !empty($emails[$n])) {
                $this->addCc($emails[$n]);
            }
        }

        $fin_files = $report->getUploadedFilesList(AgreementModelReport::UPLOADED_FILE_FINANCIAL);
        $add_files = $report->getUploadedFilesList(AgreementModelReport::UPLOADED_FILE_ADDITIONAL);

        //Финансовые документы
        if ($fin_files && count($fin_files) > 0) {
            $fin_file = $fin_files->getFirst();

            if ($fin_file) {
                $path_fin = sfConfig::get('sf_upload_dir') . '/' . AgreementModelReport::FINANCIAL_DOCS_FILE_PATH . $fin_file->getPath(). '/' . $fin_file->getFile();
                $this->attach(Swift_Attachment::fromPath($path_fin));
            }
        }

        //Дополнительные документы
        if ($add_files && count($add_files) > 0) {
            $add_file = $add_files->getFirst();

            if ($add_file) {
                $path_add = sfConfig::get('sf_upload_dir') . '/' . AgreementModelReport::ADDITIONAL_FILE_PATH . $add_file->getPath(). '/' . $add_file->getFile();
                $this->attach(Swift_Attachment::fromPath($path_add));
            }
        }

        /*$this->attach(Swift_Attachment::fromPath($report->getFinancialDocsFileNameHelper()->getPath()));
        if ($model->getModelType()->hasAdditionalFile())
            $this->attach(Swift_Attachment::fromPath($report->getAdditionalFileNameHelper()->getPath()));*/

    }
}
