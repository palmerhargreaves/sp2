<?php

/**
 * Created by PhpStorm.
 * User: Andrey
 * Date: 16.11.2015
 * Time: 12:29
 */
class mailingActions extends BaseActivityActions
{
    function executeIndex(sfWebRequest $request)
    {
//        var_dump(strtotime('21.09.15'));
        $User = $this->getUser();
        $this->authUserId = $User->getAuthUser()->getId();

        $dealer = $User->getAuthUser()->getDealerUsers()->getFirst();
        $dealer = DealerTable::getInstance()->findOneById($dealer->getDealerId());

        $this->display_load_panel = date('d') <= 10 ? true : false;

        $this->error_message = '';

        if ($User->isDealerUser()) {
            $file = $request->getFiles();
            $this->total_result = array(
                'total_file_count' => 0,
                'total_duplicate' => 0,
                'total_file_duplicate' => 0,
                'total_db_unique' => 0,
                'total_unique' => 0,
                'date_error' => 0,
                'file_format_error' => false
            );
            $this->display_stat = false;
            $this->approve = "";

            $clients = MailingList::readDealerFile($file);

            foreach ($clients as $key => $item) {
                $emails_array[] = $item[5];
                MailingList::validateFileItem($item, $dealer, $this->total_result); //Валидация елемента массива в файле
            }

            $this->total_result['total_file_duplicate'] = (count($clients) - count(array_unique($emails_array)));
            $this->total_result['total_unique'] = count(array_unique($emails_array));

            foreach ($clients as $item_from_file) {
                ++$this->total_result['total_file_count'];
                if (MailingList::checkDuplicate($item_from_file, $dealer, self::getQuarter(date('m')))) {
                    ++$this->total_result['total_duplicate'];
                    MailingList::logger('../log/mailing-errors.log', date('Y-m-d H:i:s') . " dealer number - " . $dealer->getNumber() . " - Email address '" . $item_from_file[5] . "' duplicate\n", FILE_APPEND);
                } else {
                    ++$this->total_result['total_db_unique'];
                }
            }

            $duplicate_percent = ($this->total_result['total_duplicate'] / $this->total_result['total_file_count']) * 100;
            if ($duplicate_percent < 30) {
                foreach ($clients as $item) {
                    $model = new MailingList();
                    $model->validateItem($item, $this->total_result, $dealer);
                }

                if ($this->total_result['date_error']) {
                    $this->approve = "Ваш файл не принят";
                    $this->display_stat = true;
                    $this->error_message = "Необходимо написать дату в правильном формате: 09.10.2017<br>Все столбцы с датами необходимо перевести в текстовый формат. Для этого нужно выделить необходимый столбец, кликнуть по нему правой кнопкой мыши, выбрать \"Формат ячеек\" и выбрать тип Текст. <a href=\"http://dm.vw-servicepool.ru/dealer_file.xlsx\">Скачать образец файла</a>.<br>";
                }

                if ($this->total_result['total_file_count'] && $this->total_result['total_incorrect'] == $this->total_result['total_file_count']) {
                    $this->approve = "Ваш файл не принят";
                    $this->display_stat = true;
                    $this->next_step = "<b>Следующий шаг:</b> вам необходимо оформить файл с адресами корректно и повторно загрузить его на портал.";
                }

                if ($this->total_result['total_incorrect'] < $this->total_result['total_file_count']) {
                    $this->approve = "Ваш файл принят";
                    $this->display_stat = true;
                }

            } else {
                $this->approve = "Ваш файл не принят";
                $this->error_message = "Процент электронных адресов в текущем файле, которые совпадают с загруженными ранее превышает 30%.";
                $this->next_step = "<b>Следующий шаг:</b> вам необходимо удалить дублирующиеся адреса из вашего файла, добавив новые уникальные адреса, и повторить загрузку файла.";
            }


        } else throw new Exception('Пользователь не является дилером');
    }

    function executeStat(sfWebRequest $request)
    {
        $User = $this->getUser();
        $export = $request->getParameter('export');
        $plan_month = $request->getParameter('plan_month');

        $this->year = 2017;//$request->getParameter('year');
        if (empty($this->year))
            $this->year = D::getBudgetYear($request);

        $this->dealer_name = $request->getParameter('dealer_name');

        if ($User->isImporter()) {
            $file = $request->getFiles();
            $this->total_result = false;
            $this->report = array();

            $this->month_report = array();

            if (!empty($file)) {
                foreach (DealerPlans::readImporterFile($file) as $item) {
                    $model = new DealerPlans();
                    $model->addPlan($item, $plan_month, $this->year);
                }
            }

            $Dealers = DealerTable::getInstance()->createQuery()->select('number, name')
                ->where('number LIKE ?', '%93500%')
                ->andWhere('importer_id = 1')
                ->andWhere('dealer_type IN (1,3)')
                ->orderBy('number');

            if (!empty($this->dealer_name) && empty($export)) {
                $field = is_numeric($this->dealer_name) ? 'number' : 'name';
                $Dealers->andWhere($field . ' LIKE ?', '%' . $this->dealer_name . '%');
            }

            $MailingReport = DealerPlansTable::getInstance()->createQuery('dp')
                ->select('dp.dealer_id, dp.name, MONTH(dp.added_date) as month, count(ml.email) as count, (dp.plan1 + dp.plan2) as plan, (count(ml.email) / ((dp.plan1 + dp.plan2) / 100)) as percent_complete')
                ->leftJoin('dp.Mailings ml ON ml.dealer_id = dp.dealer_id AND YEAR(ml.added_date) = ' . $this->year . ' AND MONTH(ml.added_date) = MONTH(dp.added_date)')
                ->where('YEAR(dp.added_date) = ' . $this->year)
                ->groupBy('dp.dealer_id, MONTH(dp.added_date)')
                ->execute()->toArray();

            $CustomMailingReport = array();
            $QuarterPlanCalculation = array();

            foreach ($Dealers->execute()->toArray() as $dealer) {
                $CustomMailingReport[$dealer['number']]['name'] = $dealer['name'];
                for ($i = 1; $i <= 12; $i++) {
                    foreach ($MailingReport as $report) {

                        if ($report['dealer_id'] == $dealer['number'] && $report['month'] == $i) {
                            $CustomMailingReport[$dealer['number']][$i] = round($report['percent_complete']);
                            $QuarterPlanCalculation[$dealer['number']][$i]['plan'] = isset($QuarterPlanCalculation[$dealer['number']][$i]['plan']) ? $QuarterPlanCalculation[$dealer['number']][$i]['plan'] + $report['plan'] : $report['plan'];
                            $QuarterPlanCalculation[$dealer['number']][$i]['count'] = isset($QuarterPlanCalculation[$dealer['number']][$i]['count']) ? $QuarterPlanCalculation[$dealer['number']][$i]['count'] + $report['count'] : $report['count'];
                        }
                    }

                    if (!isset($CustomMailingReport[$dealer['number']][$i])) {
                        $CustomMailingReport[$dealer['number']][$i] = 0;
                    }

                    if ($i == 3) {
//                        $CustomMailingReport[$dealer['number']]['1qr'] = ($CustomMailingReport[$dealer['number']][1] + $CustomMailingReport[$dealer['number']][2] + $CustomMailingReport[$dealer['number']][3]);
                        $percent = 0;
                        $plan = 0;
                        $count = 0;
                        for ($a = $i - 2; $a <= $i; $a++) {
                            $plan = $plan + $QuarterPlanCalculation[$dealer['number']][$a]['plan'];
                            $count = $count + $QuarterPlanCalculation[$dealer['number']][$a]['count'];
                            $percent = $count / ($plan / 100);
                        }
                        $CustomMailingReport[$dealer['number']]['1qr'] = round($percent);
                        unset($a);
                    }

                    if ($i == 6) {
//                        $CustomMailingReport[$dealer['number']]['2qr'] = ($CustomMailingReport[$dealer['number']][4] + $CustomMailingReport[$dealer['number']][5] + $CustomMailingReport[$dealer['number']][6]);
                        $percent = 0;
                        $plan = 0;
                        $count = 0;
                        for ($a = $i - 2; $a <= $i; $a++) {
                            $plan = $plan + $QuarterPlanCalculation[$dealer['number']][$a]['plan'];
                            $count = $count + $QuarterPlanCalculation[$dealer['number']][$a]['count'];
                            $percent = $count / ($plan / 100);
                        }
                        $CustomMailingReport[$dealer['number']]['2qr'] = round($percent);
                        unset($a);
                    }

                    if ($i == 9) {
//                        $CustomMailingReport[$dealer['number']]['3qr'] = ($CustomMailingReport[$dealer['number']][7] + $CustomMailingReport[$dealer['number']][8] + $CustomMailingReport[$dealer['number']][9]);
                        $percent = 0;
                        $plan = 0;
                        $count = 0;
                        for ($a = $i - 2; $a <= $i; $a++) {
                            $plan = $plan + $QuarterPlanCalculation[$dealer['number']][$a]['plan'];
                            $count = $count + $QuarterPlanCalculation[$dealer['number']][$a]['count'];
                            $percent = $count / ($plan / 100);
                        }
                        $CustomMailingReport[$dealer['number']]['3qr'] = round($percent);
                        unset($a);
                    }

                    if ($i == 12) {
                        $percent = 0;
                        $plan = 0;
                        $count = 0;
                        for ($a = $i - 2; $a <= $i; $a++) {
                            $plan = $plan + $QuarterPlanCalculation[$dealer['number']][$a]['plan'];
                            $count = $count + $QuarterPlanCalculation[$dealer['number']][$a]['count'];
                            $percent = $count / ($plan / 100);
                        }
                        $CustomMailingReport[$dealer['number']]['4qr'] = round($percent);
                        unset($a);
                    }

                }
            }
            $this->report = empty($CustomMailingReport) ? null : $CustomMailingReport;

            if ($export == 'xls') {
                DealerPlans::exportStatsToXLS($this->report, $this->year);
                die();
            }

        } else throw new Exception('Пользователь не является импортёром');
    }

    /**
     * Просмотр планов (проходов)
     * @param sfWebRequest $request
     */
    function executePlan(sfWebRequest $request)
    {
        $this->year = $request->getParameter('year');
        $this->report = array();

        if (empty($this->year))
            $this->year = D::getBudgetYear($request);

//        echo '<pre>'. print_r($this->year, 1) .'</pre>'; die();

        $Dealers = array();
        $all_dealers = DealerTable::getInstance()->createQuery()
            ->select('number, name')
            ->where('number LIKE ?', '%93500%')->andWhere('dealer_type IN (1,3)')->andWhere('status = ?', 1)->orderBy('number')
            ->execute();

        foreach ($all_dealers as $dealer)
            $Dealers[$dealer->number] = $dealer->name;

        $Report = DealerPlansTable::getInstance()->createQuery()
            ->select('*')
            ->where('YEAR(added_date) = ' . $this->year)
            ->groupBy('dealer_id, MONTH(added_date)')
            ->execute()->toArray();

        foreach ($Dealers as $key => $d) {
            $tmp = array();

            foreach ($Report as $r) {
                if ($r['dealer_id'] == $key) {
                    $dateTime = new DateTime($r['added_date']);
                    $tmp[$dateTime->format('n')] = $r;
                }
            }
            $this->report[$d]['dealer_id'] = $key;
            $this->report[$d]['data'] = $tmp;
        }
    }

    function executeDealer(sfWebRequest $request)
    {
        $this->dealer_id = $request->getParameter('dealer_id');
        $this->export_plan = $request->getParameter('export_plan');
        $this->month = $request->getParameter('plan_month');

        $query = MailingListTable::getInstance()->createQuery()->select()->where('dealer_id = ?', $this->dealer_id);
        $this->year = $request->getParameter('year');

        if (empty($this->year))
            $this->year = D::getBudgetYear($request);

        $this->dealer = DealerTable::getInstance()->findOneByNumber($this->dealer_id);
        $this->color_lite = true;

        $quarter = $request->getParameter('quarter');
        $this->quarter = empty($quarter) ? D::getQuarter(date('Y-m-d')) : $quarter;


        if (!empty($this->month))
            $query->andWhere('MONTH(added_date) = ?', $this->month);


        $query->andWhere('YEAR(added_date) = ?', $this->year);
        $query->andWhere('QUARTER(added_date) = ?', $this->quarter);
        $query->orderBy('added_date DESC');
        $this->mailings = $query->execute();

        if (!empty($this->export_plan)) {
            MailingList::exportStatToXls($this->mailings);
            die();
        }
    }

    function executeDelete(sfWebRequest $request)
    {
        $confirm_deleted = $request->getParameter('confirm_deleted');

        if (!$confirm_deleted) {
            $this->confirm_deleted = 1;
        } else {
            $User = $this->getUser();
            $dealer = $User->getAuthUser()->getDealerUsers()->getFirst();
            $dealer = DealerTable::getInstance()->findOneById($dealer->getDealerId());

            MailingList::deleteMailings($dealer->getNumber());
            $this->confirm_deleted = false;
        }

    }

    /**
     * Возвращает текущий квартал
     * @return int
     */
    public static function getQuarter($month = null)
    {
        $quarter = 1;
        $date = new DateTime();
        if ($month) {
            $date = new DateTime(date('Y-' . $month . '-d'));
            $date->modify('-1 month');
        }

        if ($date->format('m') == 4 || $date->format('m') == 5 || $date->format('m') == 6)
            $quarter = 2;
        if ($date->format('m') == 7 || $date->format('m') == 8 || $date->format('m') == 9)
            $quarter = 3;
        if ($date->format('m') == 10 || $date->format('m') == 11 || $date->format('m') == 12)
            $quarter = 4;

        return $quarter;
    }
}
