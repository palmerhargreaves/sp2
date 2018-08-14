<?php

class spNotifyDealerModelsLeftDaysTask extends sfBaseTask
{
    protected function configure()
    {
        // // add your own arguments here
        // $this->addArguments(array(
        //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
        // ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'console'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
            // add your own options here
        ));

        $this->namespace = 'sp';
        $this->name = 'spNotifyDealerModelsLeftDays';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [spNotifyDealerModelsLeftDays|INFO] task does things.
Call it with:

  [php symfony spNotifyDealerModelsLeftDays|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        sfContext::createInstance($this->configuration);

        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $inform = new DealersModelsInformLeftDays();
        $inform->sendMessages();

        //Удаление писем
        MailMessageTable::getInstance()->createQuery()->delete()->where('must_delete = ?', true)->execute();

        /*$items = AgreementModelsBlockInformTable::getInstance()->createQuery()->where('created_at LIKE ?', '%'.date('Y-m-d').'%')->orderBy('id DESC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        foreach ($items as $item) {
            $model = AgreementModelTable::getInstance()->find($item['model_id']);
            if ($model) {
                var_dump($model->getId());
                $model->setIsBlocked(false);
                $model->setBlockedInform(0);
                $model->save();
            }
        }

        exit;*/

        //26924
        /*$dealer_users = UserTable::getInstance()
            ->createQuery('u')
            ->innerJoin('u.DealerUsers du')
            ->where('active=?', true)
            ->andWhereIn('du.dealer_id', array(390, 282))
            //->groupBy('du.dealer_id')
            ->execute();
        //678

        foreach ($dealer_users as $user) {
            if ($user && $user->getAllowReceiveMails()) {
                $message = new DealersModelsBlockInformLeftDays($user, AgreementModelTable::getInstance()->find(26924), AgreementModelsBlockInform::INFORM_STATUS_LEFT_2);
                $message->setPriority(1);

                sfContext::getInstance()->getMailer()->send($message);
            }
        }*/

        /*$user = UserTable::getInstance()->find(946);
        if ($user) {
            $message = new DealersModelsBlockInformLeftDays($user, AgreementModelTable::getInstance()->find(26924), AgreementModelsBlockInform::INFORM_STATUS_LEFT_2);
            $message->setPriority(1);

            //sfContext::getInstance()->getMailer()->send($message);
        }*/

        //yulia.taygina@vw-mariauto.ru 678

        // add your code here
    }
}
