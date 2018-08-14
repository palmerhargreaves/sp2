<?php

class spSendMailsByActivitiesNotUsingImportersTask extends sfBaseTask
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
        $this->name = 'spSendMailsByActivitiesNotUsingImporters';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [spSendMailsByActivitiesNotUsingImporters|INFO] task does things.
Call it with:

  [php symfony spSendMailsByActivitiesNotUsingImporters|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        sfContext::createInstance($this->configuration);

        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $inform = new ActivitiesNotUsingImporterMails();
        $inform->send();
        // add your code here
    }
}
