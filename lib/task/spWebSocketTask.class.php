<?php

use WebSocket\Application\DiscussionWebSocket;

class spWebSocketTask extends sfBaseTask
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

        $this->namespace = 'sp';
        $this->name = 'webSocket';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [sp:webSocket|INFO] task does things.
Call it with:

  [php symfony sp:webSocket|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $classLoader = new SplClassLoader('WebSocket', __DIR__ . '/web_socket_lib');
        $classLoader->register();

        $server = new \WebSocket\Server('dm.vw-servicepool.ru', 48880, false);

        // server settings:
        $server->setMaxClients(1000);
        $server->setCheckOrigin(false);
        $server->setAllowedOrigin('dm.vw-servicepool.ru/discussion');
        $server->setMaxConnectionsPerIp(10);
        $server->setMaxRequestsPerMinute(100000);

        // Hint: Status application should not be removed as it displays usefull server informations:
        $server->registerApplication('status', \WebSocket\Application\StatusApplication::getInstance());
        $server->registerApplication('discussion', \WebSocket\Application\DiscussionSocket::getInstance());

        $server->run();
        // add your code here
    }
}
