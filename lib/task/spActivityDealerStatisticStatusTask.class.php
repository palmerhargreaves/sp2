<?php

class spActivityDealerStatisticStatusTask extends sfBaseTask
{
    const STAT_TYPE_SIMPLE = 'simple';
    const STAT_TYPE_EXTENDED = 'extended';

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
        $this->name = 'spActivityDealerStatisticStatus';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [spActivityDealerStatisticStatus|INFO] task does things.
Call it with:

  [php symfony spActivityDealerStatisticStatus|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        sfContext::createInstance($this->configuration);

        set_time_limit(0);
        ini_set('memory_limit', '8000M');

        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $activites = ActivityTable::getInstance()
            ->createQuery('a')
            ->select('id')
            ->innerJoin('a.ActivityField af')
            ->where('af.parent_header_id = ?', 0)
                ->orderBy('id DESC')
                ->execute();

        //$activitesExtended = ActivityTable::getInstance()->createQuery()->select('id')->where('allow_extended_statistic = ?', 1)->orderBy('id DESC')->execute();
        $dealers = DealerTable::getInstance()
            ->createQuery('d')
            ->select('id')
            ->innerJoin('d.ActivityFieldsValues afv')
            ->innerJoin('afv.ActivityFields af')
            ->where('status = ?', 1)
            ->andWhere('af.parent_header_id = ?', 0)
            ->orderBy('id ASC')
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        /*$dealersExtended = DealerTable::getInstance()
            ->createQuery('d')
            ->select('id')
            ->innerJoin('d.ActivityExtendedStatisticFieldsDatas ed')
            ->where('status = ?', 1)
            ->orderBy('id ASC')
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
       */
        $total = 0;

        //ActivityDealerStaticticStatusTable::getInstance()->createQuery()->delete()->execute();
        /*foreach ($dealers as $dealer) {
            foreach ($activites as $activity) {
                $this->checkForStatisticByType($dealer, $activity, 'simple');
                $total++;
            }
        }*/

        /*foreach ($dealersExtended as $dealer) {
            foreach ($activitesExtended as $activity) {
                $this->checkForStatisticByType($dealer, $activity, 'extended');
                $total++;
            }
        }*/

        echo $total . "\r\n";
    }

    private function checkForStatisticByType($dealer, $activity, $type)
    {
        $status = $activity->checkForStatisticsFillTask($dealer, $type);
        if ($type == self::STAT_TYPE_EXTENDED) {
            //$this->extendedStatistic($dealer, $activity, $status);
        } else {
            $this->saveSimpleStatStatus($dealer, $activity, $status, $type);
        }
    }

    private function extendedStatistic($dealer, $activity, $status)
    {
        if (empty($status)) {
            $this->saveExtendedStatStatus($dealer, $activity, true, self::STAT_TYPE_EXTENDED);
        } else {
            $this->saveExtendedStatStatus($dealer, $activity, $status, self::STAT_TYPE_EXTENDED);
        }
    }

    private function saveExtendedStatStatus($dealer, $activity, $status, $type)
    {
        if (is_array($status))
        {
            foreach ($status as $conceptId => $isComplete) {
                if ($conceptId != 0 && $isComplete) {
                    $res = Doctrine_Query::create()
                        ->select('*')
                        ->from('AgreementModel am')
                        ->innerJoin('am.Report r')
                        ->where('am.concept_id = ?', array($conceptId))
                        ->andWhere('am.status = ? and r.status = ?', array('accepted', 'accepted'))
                        ->orderBy('id DESC')
                        ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

                    if ($res) {
                        $date_obj = Utils::getModelDateFromLogEntryWithYear($res['id']);

                        if (!is_null($date_obj)) {
                            $currentQ = D::getQuarter($date_obj);
                            $year = D::getYear($date_obj);

                            $statisticStatus = $this->createStatisticStatus($dealer, $activity, $year, $type);
                            $statisticStatus->setArray(
                                array(
                                    'q'.$currentQ => $isComplete,
                                    'concept_id' => $conceptId,
                                    'complete' => $isComplete
                                )
                            );
                            $statisticStatus->save();
                        }
                    }
                }
            }
        }
    }

    private function saveSimpleStatStatus($dealer, $activity, $status, $type)
    {
        $statisticStatus = $this->createStatisticStatus($dealer, $activity, (isset($status['year']) && !is_null($status['year']) ? $status['year'] : null), $type);

        if(!is_array($status)) {
            $complete = $status;
        }
        else {
            $complete = false;

            foreach ($status['status'] as $key => $status) {
                foreach ($status as $qInd => $qData) {
                    if ($qData['q'] != 0) {
                        if ($qData['complete']) {
                            $funcQ = 'setQ' . $qData['q'];
                            $statisticStatus->$funcQ($qData['q']);
                        }

                        $complete = $qData['complete'];
                    }
                }
            }
        }

        if ($complete) {
            $statisticStatus->setComplete($complete);
        }

        $statisticStatus->save();
    }

    private function createStatisticStatus($dealer, $activity, $year, $type)
    {
        $query =  ActivityDealerStaticticStatusTable::getInstance()
            ->createQuery()
            ->where('dealer_id = ? and activity_id = ? and stat_type = ?',
                array(
                    $dealer['id'],
                    $activity->getId(),
                    $type,
                )
            );

        if(!is_null($year)) {
            $query->andWhere('year = ?', $year);
        }

        $statisticStatus = $query->fetchOne();
        if (!$statisticStatus) {
            $statisticStatus = new ActivityDealerStaticticStatus();

            $statisticStatus->setArray(
                array
                (
                    'dealer_id' => $dealer['id'],
                    'activity_id' => $activity->getId(),
                    'stat_type' => $type,
                    'year' => !is_null($year) ? $year : date('Y')
                ));

            $statisticStatus->save();
        }

        return $statisticStatus;
    }
}
