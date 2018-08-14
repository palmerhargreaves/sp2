<?php

class spSendApproveMailsTask extends sfBaseTask
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
        $this->name = 'sendApproveMails';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [sp:sendApproveMails|INFO] task does things.
Call it with:

  [php symfony sp:sendApproveMails|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        sfContext::createInstance($this->configuration);

        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        // add your code here
        /*$user = UserTable::getInstance()->createQuery()->where('id = ? and approve_by_email = ?', array(1, false))->fetchOne();

        $user->setApproveReceiveEmail(true);
        $user->save();

        $message = new UserApproveMail($user);
        $message->setPriority(1);
        sfContext::getInstance()->getMailer()->send($message);*/

        $users = UserTable::getInstance()->createQuery()->where('active = ? and approve_by_email = ?', array(true, false))->execute();
        foreach ($users as $user) {
            //Сохранить данные об информировании пользователя
            $user->setApproveReceiveEmail(true);
            $user->save();

            $message = new UserApproveMail($user);
            $message->setPriority(1);
            $message->setCanSendMail(true);

            sfContext::getInstance()->getMailer()->send($message);

            exit;
        }
    }
}
