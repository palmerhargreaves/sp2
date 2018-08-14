<?php

class sfReuploadDealerFilesTask extends sfBaseTask
{
    protected function configure()
    {
        // // add your own arguments here
        // $this->addArguments(array(
        //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
        // ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
            // add your own options here
        ));

        $this->namespace = 'sf';
        $this->name = 'reuploadDealerFiles';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [sf:reuploadDealerFiles|INFO] task does things.
Call it with:

  [php symfony sf:reuploadDealerFiles|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $models = AgreementModelTable::getInstance()->createQuery()
            ->select()
            ->where('created_at LIKE ?', '%'.date('Y-m-d').'%')
            ->execute();

        foreach ($models as $model) {
            if (AgreementModelReportFilesTable::getInstance()->createQuery()->where('object_id = ?', $model->getId())->count() == 0) {
                $files_ids = explode(':', $model->getFilesIds());

                foreach ($files_ids as $f_id) {
                    $temp_file = TempFileTable::getInstance()->find($f_id);

                    if ($temp_file) {
                        /**
                         * Save uploaded files before check for model statuses
                         */
                        if (!$model->isModelScenario()) {
                            $copied_files = UploadModelFilesFactory::getInstance()->createUpload($model, $temp_file->getUser(), array($temp_file->getId()), $model->getActivityId())->reuploadFiles();
                        }
                        else if ($model->isModelScenario()) {
                            $copied_files = UploadModelFilesFactory::getInstance()->createUpload($model, $temp_file->getUser(), array($temp_file->getId()), $model->getActivityId(),'ScenarioRecord')->reuploadFiles();
                        }

                        $message = MessageTable::getInstance()->createQuery()->where('discussion_id = ?', $model->getDiscussionId())->limit(1)->orderBy('id DESC')->fetchOne();
                        if ($message) {
                            foreach ($copied_files as $copied_file) {
                                if (file_exists(sfConfig::get('sf_upload_dir') . '/' . AgreementModel::MODEL_FILE_PATH . '/' . $copied_file['upload_path'].'/'.$copied_file['gen_file_name'])) {
                                    $file = new MessageFile();

                                    $file->setMessageId($message->getId());
                                    $file->setFile($message->getId() . '-' . $copied_file['gen_file_name']);
                                    $file->setPath($copied_file['upload_path']);

                                    $msg_path = sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH;
                                    if (isset($copied_file['upload_path']) && !empty($copied_file['upload_path'])) {
                                        $msg_path = sfConfig::get('sf_upload_dir') . '/' . MessageFile::FILE_PATH . $copied_file['upload_path'];
                                        if (!file_exists($msg_path)) {
                                            mkdir($msg_path, 0777, true);
                                        }
                                    }

                                    copy(
                                        sfConfig::get('sf_upload_dir') . '/' . AgreementModel::MODEL_FILE_PATH . '/' . $copied_file['upload_path'].'/'.$copied_file['gen_file_name'],
                                        $msg_path . '/' . $file->getFile()
                                    );
                                    $file->save();
                                }
                            }
                        }
                    }

                }
            }
        }
    }
}
