<?php

/**
 * Description of AgreementCompleteModelMail
 *
 * @author Сергей
 */
class AgreementCompleteModelMail extends TemplatedMail
{
    function __construct(array $emails, AgreementModel $model, $can_send_mail = true)
    {
        $emails = array_filter($emails);
        parent::__construct(
            $emails[0],
            'agreement_activity_model/mail_complete_model',
            array(
                'model' => $model,
                'dealer' => $model->getDealer(),
                'activity' => $model->getActivity()
            )
        );

        for ($n = 1, $l = count($emails); $n < $l; $n++) {
            if (isset($emails[$n]) && !empty($emails[$n])) {
                $this->addCc($emails[$n]);
            }
        }

        $model_files = $model->getModelUploadedFiles();
        if (count($model_files) > 0) {
            $model_file = $model_files->getFirst();
            $path = sfConfig::get('sf_upload_dir') . '/' . AgreementModel::MODEL_FILE_PATH . $model_file->getPath(). '/' . $model_file->getFile();

            if (file_exists($path)) {
                $this->attach(Swift_Attachment::fromPath($path));
            }
        }

        //$this->attach(Swift_Attachment::fromPath($model->getModelFileNameHelper()->getPath()));
    }
}
