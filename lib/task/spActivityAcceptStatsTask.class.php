    d<?php

class spActivityAcceptStatsTask extends sfBaseTask
{
    const MIN_YEAR = 1;

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
        $this->name = 'spActivityAcceptStats';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [spActivityAcceptStats|INFO] task does things.
Call it with:

  [php symfony spActivityAcceptStats|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        set_time_limit(0);
        ini_set('memory_limit', '8000M');

        sfContext::createInstance($this->configuration);

        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $lastYear = ActivityAcceptStatsUpdatesTable::getInstance()->createQuery()->select()->fetchOne();
        if (!$lastYear) {
            $lastYear = new ActivityAcceptStatsUpdates();
            $lastYear->setYear(self::MIN_YEAR);
            $lastYear->save();
        }

        $year = (int)$lastYear->getYear();
        //$year = D::getBudgetYear(null, $year);

        $total = 0;
        $dealers = DealerTable::getInstance()->createQuery()->select('id')->where('status = ?', 1)->orderBy('id ASC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $raw_db = new RawDb();
        foreach ($dealers as $dealer) {
            $raw_db->updateRow('activity_accept_stats',
                array
                (
                    'q1' => '',
                    'q2' => '',
                    'q3' => '',
                    'q4' => '',
                ),
                array
                (
                    'dealer_id' => $dealer['id'],
                    'year' => $year
                ));
        }

        $mandatory_activities = array();
        foreach (MandatoryActivityQuartersTable::getInstance()->createQuery()->select('activity_id, quarters')->where('year = ?', $year)->execute(array(), Doctrine_Core::HYDRATE_ARRAY) as $item) {
            $quarters = explode(":", $item['quarters']);
            foreach ($quarters as $q) {
                $mandatory_activities[$year][$q][] = $item['activity_id'];
            }
        }

        foreach ($dealers as $dealer) {
            $stats = null;

            $query = Doctrine_Query::create()
                ->select('am.id as modelId, am.updated_at, am.report_id, am.activity_id, log.created_at as logCreatedAt, r.id as rId, r.status as rStatus, r.accept_date as rAcceptDate')
                ->from('AgreementModel am')
                ->innerJoin('am.Report r')
                ->innerJoin('am.Activity ac')
                ->innerJoin('am.LogEntry log')
                ->where('am.dealer_id=?', array($dealer['id']))
                ->andWhere('(log.object_type = ? or log.object_type = ? or log.object_type = ?) and log.icon = ? and log.action = ? and private_user_id = ?',
                    array
                    (
                        'agreement_report',
                        'agreement_model',
                        'agreement_concept_report',
                        'clip',
                        'edit', 0
                    )
                )
                ->andWhere('am.status = ? and r.status = ?', array('accepted', 'accepted'))
                ->andWhere('ac.is_own != ?', 1)
                //->orderBy('r.accept_date ASC, log.id DESC')
                ->orderBy('log.id DESC');

            $query->andWhere('(year(log.created_at) = ? or year(log.created_at) = ?)', array($year, $year + 1));

            $stats = ActivityTable::getInstance()->fillAcceptStatsArray($query->execute(array(), Doctrine_Core::HYDRATE_ARRAY), $stats, $dealer, $year, $mandatory_activities);
            foreach ($stats as $key => $stat) {
                if (!empty($stat)) {
                    $qData = implode(":", $stat);
                    $funcQ = 'setQ' . $key;

                    if (ActivityAcceptStatsTable::getInstance()->createQuery()->where('dealer_id = ? and year = ?', array($dealer['id'], $year))->count() == 0) {
                        $statData = new ActivityAcceptStats();
                        $statData->setDealerId($dealer['id']);
                        $statData->setYear($year);

                        $statData->$funcQ($qData);
                        $statData->save();
                    } //else if($year == $currentYear || $year == $prevYear) {
                    else {
                        $statData = ActivityAcceptStatsTable::getInstance()->createQuery()->where('dealer_id = ? and year = ?', array($dealer['id'], $year))->fetchOne();

                        $statData->$funcQ($qData);
                        $statData->save();
                    }

                    $total++;
                }
            }
        }

        echo sprintf('%s - %s', $year, $total) . "\r\n";
        if ($year == date('Y')) {
            $lastYear->setYear(date('Y') - self::MIN_YEAR);
        } else {
            $lastYear->setYear(++$year);
        }
        $lastYear->save();
    }
}
