<?php

class spDealerBudgetStatsTask extends sfBaseTask
{
    const START_YEAR = 2013;

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
        $this->name = 'dealer-budget-stats';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [spDealerBudgetStats|INFO] task does things.
Call it with:

  [php symfony spDealerBudgetStats|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->calcBudgetsStats();

        // add your code here
    }

    private function calcBudgetsStats()
    {
        $this->dealers = DealerTable::getInstance()->getDealersList()->execute();
        foreach ($this->dealers as $dealer) {
            for($year = date('Y') - 1; $year <= date('Y'); $year++) {
                $total = 0;

                //if($year == date('Y'))
                {
                    $real = new RealBudgetCalculator($dealer, $year);
                    $this->real = $real->calculate();

                    foreach ($this->real as $k => $item) {
                        $total += $item;
                    }

                    $stat = new DealerWorkStatistic();
                    $data = array(
                        'dealer_id' => $dealer->getId(),
                        'q1' => $this->real[1],
                        'q2' => $this->real[2],
                        'q3' => $this->real[3],
                        'q4' => $this->real[4],
                        'year' => $total,
                        'calc_year' => $year,
                        'total_sum' => $real->getTotalBudget()
                    );
                    $stat->setArray($data);
                    $stat->save();

                    $models_list = $real->getCalcModelsList();
                    foreach ($models_list as $q => $models) {
                        foreach ($models as $model) {
                            $data_model = new DealerWorkStatisticModels();

                            $data_model->setArray(
                                array
                                (
                                    'model_id' => $model['mId'],
                                    'model_cost' => $model['cost'],
                                    'activity_id' => $model['mActivityId'],
                                    'parent_id' => $stat->getId()
                                )
                            );
                            $data_model->save();
                        }
                    }

                    $activities_status = $real->getActivitiesList();
                    foreach ($activities_status as $activity_key => $activity_status) {
                        $activity_status_item = new DealerWorkStatisticActivities();

                        $activity_status_item->setArray(
                            array
                            (
                                'activity_id' => $activity_key,
                                'parent_id' => $stat->getId(),
                                'statistic_complete' => $activity_status
                            )
                        );
                        $activity_status_item->save();
                    }
                }
            }
        }


    }
}
