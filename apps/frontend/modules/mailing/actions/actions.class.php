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
        $User = $this->getUser();
        $this->authUserId = $User->getAuthUser()->getId();

        $dealer = $User->getAuthUser()->getDealerUsers()->getFirst();
        $dealer = DealerTable::getInstance()->findOneById($dealer->getDealerId());
        $this->dealer = $dealer;

        $this->display_load_panel = date('n') == 1 ? date('d') <= 12 ? true : false : date('d') <= 10 ? true : false;
        $this->error = array(
            'title' => '',
            'message' => '',
            'next_step' => ''
        );

        $this->display_stat = true;

        if ($User->isDealerUser()) {
            $file = $request->getFiles();

            $this->total_result = array(
                'total_unique' => 0,
                'total_duplicate' => 0,
                'total_on_file' => 0,
                'total_duplicate_on_file' => 0,
                'total_incorrect' => 0,
                'total_added' => 0,
                'date_error' => false,
                'file_error' => false
            );

            list($year, $quarter) = Utils::getCorrectYearByCalendar(date('Y-m-d'));
            try {
                $clients = MailingList::readDealerFile($file, $this->total_result, $dealer, $year, $quarter);
            } catch (Exception $e) {
                if ($e->getMessage() == MailingList::EXCEPTION_FILE)
                    $this->total_result['file_error'] = 'Необходимо загрузить файл в формате .csv.';
            }
//                echo '<pre>'. print_r($this->total_result['file_error'], 1) .'</pre>'; die();

            if (empty($this->total_result['file_error']) && !empty($file) && empty($clients))
                $this->total_result['file_error'] = 'Вам необходимо проверить корректность данных и порядок расположения столбцов в файле.';

            if (!$this->total_result['file_error'] && !empty($clients)) {
                if ($this->total_result['date_error']) {
                    $this->error['title'] = "Ваш файл не принят";
                    $this->error['message'] = $this->total_result['date_error'];
                    $this->display_stat = false;
                } else {
                    if (MailingList::checkDuplicatePerecnt($dealer->getNumber(), $this->total_result, $year, $quarter) > 30) {
                        $this->error['title'] = "Ваш файл не принят";
                        $this->error['message'] = "Процент электронных адресов в текущем файле, которые совпадают с загруженными ранее превышает 30%.";
                        $this->error['next_step'] = "Вам необходимо удалить дублирующиеся адреса из вашего файла, добавив новые уникальные адреса, и повторить загрузку файла.";
                    } elseif ($this->total_result['total_unique'] != 0 && $this->total_result['total_unique'] == $this->total_result['total_incorrect']) {
                        $this->error['title'] = "Ваш файл не принят";
                        $this->error['message'] = "";
                        $this->error['next_step'] = "Вам необходимо оформить файл с адресами корректно и повторно загрузить его на портал.";
                    } else {
                        if (!empty($clients)) {
                            $this->error['title'] = "Ваш файл принят";

                            MailingList::addAllTrueClients($clients, $dealer, $this->total_result);
                        }
                    }
                }
            } else {
                if (!empty($file)) {
                    $this->error['title'] = "Ваш файл не принят";
                    $this->error['message'] = $this->total_result['file_error'];
                    $this->display_stat = false;
                }
            }

        } else throw new Exception('Пользователь не является дилером');
    }

    function executeStat(sfWebRequest $request)
    {
        $User = $this->getUser();

        $export = $request->getParameter('export');
        $plan_month = $request->getParameter('plan_month');

        $this->year = D::getBudgetYear($request);;
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

            $Dealers = DealerTable::getInstance()->createQuery()->select('number, name')->where('number LIKE ?', '%93500%')->andWhere('importer_id = 1')->orderBy('number');

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
//            echo "<pre>".print_r($QuarterPlanCalculation, 1)."</pre>"; die();
            $this->report = empty($CustomMailingReport) ? null : $CustomMailingReport;

            if ($export == 'xls') {
                DealerPlans::exportStatsToXLS($this->report, $this->year);
                die();
            }

        } else throw new Exception('Пользователь не является импортёром');
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


        if (!empty($this->month)) {
            $query->andWhere('MONTH(added_date) = ?', $this->month);
        } else {
            $query->andWhere('QUARTER(added_date) = ?', $this->quarter);
        }

        $query->andWhere('YEAR(added_date) = ?', $this->year);
        $query->orderBy('added_date DESC');

//        echo '<pre>'. print_r($query->count(), 1) .'</pre>'; die();
        $this->mailings = $query->execute();

        if (!empty($this->export_plan)) {
            MailingList::exportStatToXls($this->mailings);
            die();
        }
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

        $dealers_numbers = array();
        foreach ($all_dealers as $dealer) {
            $Dealers[$dealer->number] = $dealer->name;
            $dealers_numbers[] = $dealer->number;
        }

        $Report = DealerPlansTable::getInstance()->createQuery()
            ->select('*')
            ->where('YEAR(added_date) = ' . $this->year)
            ->andWhereIn('dealer_id', $dealers_numbers)
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

            $this->report[$key]['dealer_id'] = $key;
            $this->report[$key]['data'] = $tmp;
            $this->report[$key]['name'] = $d;
        }

        /*} else {
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
        }*/
    }

    /**
     * Удаление адресов
     * @param sfWebRequest $request
     */
    function executeDelete(sfWebRequest $request)
    {
        $this->removal_allowed = $this->userRemovalAllowed();

        $date = new DateTime();
        if ($this->removal_allowed) {
            $this->current_year = $date->format('Y');
            $this->current_month = $date->format('n');
            $this->years = array();

            $start = new DateTime();
            $start->modify('-1 years');
            $end = new DateTime();
            $end->modify('+3 years');
            $period = new DatePeriod($start, new DateInterval('P1Y'), $end);

            foreach ($period as $item)
                $this->years[] = $item->format('Y');

            $this->months = array();
            for ($i = 1; $i <= 12; $i++) {
                $this->months[] = $i;
            }
        } else {
            $date->modify("-1 month");
            $this->d_year = $date->format("Y");
            $this->d_month = $date->format("n");
        }

        $confirm_deleted = $request->getParameter('confirm_deleted');
        if (!$confirm_deleted) {
            $this->confirm_deleted = 1;
        } else {
            $post = $request->getPostParameters();
            $User = $this->getUser();
            $dealer = $User->getAuthUser()->getDealerUsers()->getFirst();
            $dealer = DealerTable::getInstance()->findOneById($dealer->getDealerId());

            MailingList::deleteMailings($dealer->getNumber(), $post);
            $this->deleted_month = $post['month'];
            $this->confirm_deleted = false;
        }

    }

    /**
     * Пользователи которым разрешено удаление статистики
     * @return bool
     */
    public function userRemovalAllowed()
    {
        $user_id = $this->getUser()->getAuthUser()->getId();
        if ($user_id == 1 || $user_id == 671 || $user_id == 946) {
            return true;
        }
        return false;
    }
}
