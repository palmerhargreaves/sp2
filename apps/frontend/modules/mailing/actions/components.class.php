<?php
use FileUpload\Util;

/**
 * Description of components
 *
 * @author Andrey
 */
class mailingComponents extends sfComponents
{
    public function executeMailingPanel(sfWebRequest $request)
    {

//        echo '<pre>'. print_r($request, 1) .'</pre>';
        $budget_year = D::getBudgetYear($request);
        $year = $request->getParameter('year');
        $this->year = (empty($year) ? $budget_year : $year);

        $this->quater_month = array();
        $this->dealer_mailings = array();
        $this->total_plan = 0;

        $request_quarter = $request->getParameter('quarter');

        $this->quarter = $request_quarter = empty($request_quarter) ? D::getQuarter(D::calcQuarterData(date('Y-m-d'))) : $request_quarter;

        $this->month_number = 1;
        $this->end_month = 3;
        $this->total_dealer_mailings = 0;

        $months = '1,2,3';

        $quarters = BudgetCalendarTable::getQuarters($this->year);
        foreach ($quarters as $q) {
            if ($q->getYear() == $this->year && $q->getQuarter() == $this->quarter) {
                $dt = new DateTime();
                if (empty($request_quarter)) {
                    $dt->modify('-' . $q->getDay() + 1 . ' days');
                    $month = $dt->format('m');
                    $month = intval($month);
                } else {
                    if ($this->quarter == 1) $month = 1;
                    if ($this->quarter == 2) $month = 4;
                    if ($this->quarter == 3) $month = 7;
                    if ($this->quarter == 4) $month = 10;
                }

                if ($month == 4 or $month == 5 or $month == 6) {
                    $months = '4,5,6';
                    $this->quarter = 2;
                    $this->month_number = 4;
                    $this->end_month = 6;
                }

                if ($month == 7 or $month == 8 or $month == 9) {
                    $months = '7,8,9';
                    $this->quarter = 3;
                    $this->month_number = 7;
                    $this->end_month = 9;
                }

                if ($month == 10 or $month == 11 or $month == 12) {
                    $months = '10,11,12';
                    $this->quarter = 4;
                    $this->month_number = 10;
                    $this->end_month = 12;
                }

                $this->current_month = $month;
                break;
            }
        }

        $this->removal_allowed = $this->userRemovalAllowed();

//        $this->year = $year;
        $dealer_number = $this->getDealerNumber($request);
//        $this->display_filter = false;
        $this->display_load_panel = date('n') == 1 ? date('d') <= 12 ? true : false : date('d') <= 10 ? true : false;

        $dealer = $this->getDealer();

        if ($this->userRemovalAllowed()) {
            $this->display_load_panel = true;
        }
//        $this->display_filter = true;

        //Initialize mails count by month
        $this->dealer_mailings_plan = array();
        foreach (explode(',', $months) as $month) {
            $this->dealer_mailings[$month] = 0;
            $this->dealer_mailings_plan[$month] = 0;
        }
        
        foreach (self::getDealerPlan($dealer_number, $months, $this->year) as $plan) {
            $this->quater_month[$plan->getMonth()] = $plan->getPlan();
            $this->total_plan = $this->total_plan + $plan->getPlan();

            $this->dealer_mailings_plan[$plan->getMonth()] = $plan->getPlan();
        }

        foreach (self::getMailings($dealer_number, $months, $this->year) as $mailing) {
            $this->dealer_mailings[$mailing->getMonth()] = $mailing->getCount();
            $this->total_dealer_mailings = $this->total_dealer_mailings + $mailing->getCount();
        }

        //Проходим по количеству писем за месяц, если за месяц загружено 0 писем, обнуляем запись в массиве
        $this->dealer_mailings = array_filter(array_map(function($item) {
            return $item > 0 ? $item : null;
        }, $this->dealer_mailings));

        $this->dealer_mailings_plan = array_filter(array_map(function($item) {
            return $item > 0 ? $item : null;
        }, $this->dealer_mailings_plan));

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

    /**
     * Текущий авторизованный дилер
     * @return mixed
     */
    public function getDealer()
    {
        $dealer = $this->getUser()->getAuthUser()->getDealerUsers()->getFirst();
        return DealerTable::getInstance()->findOneById($dealer->getDealerId());
    }

    /**
     * Номер дилера
     * @param sfWebRequest $request
     * @return mixed
     */
    public function getDealerNumber(sfWebRequest $request)
    {
        $dealer_number = $request->getParameter('dealer_id');
        if (empty($dealer_number)) {
            $dealer = $this->getDealer();
            $dealer_number = $dealer->getNumber();
        }
        return $dealer_number;
    }

    /**
     * План дилеров
     * @param $dealer_number
     * @param $months
     * @param $year
     * @return mixed
     */
    private static function getDealerPlan($dealer_number, $months, $year)
    {
        return DealerPlansTable::getInstance()->createQuery()
            ->select('MONTH(added_date) as month, SUM(plan1 + plan2) as plan')
            ->where('dealer_id = \'' . $dealer_number . '\' AND MONTH(added_date) IN (' . $months . ') AND YEAR(added_date) IN (' . $year . ')')
            ->groupBy('MONTH(added_date)')
            ->execute();
    }

    /**
     * Список загруженных емейлов
     * @param $dealer_number
     * @param $months
     * @param $year
     * @return mixed
     */
    private static function getMailings($dealer_number, $months, $year)
    {
        return MailingListTable::getInstance()->createQuery()
            ->select('MONTH(added_date) as month, count(*) as count')
            ->where('dealer_id = \'' . $dealer_number . '\' AND MONTH(added_date) IN (' . $months . ') AND YEAR(added_date) IN (' . $year . ')')
            ->groupBy('MONTH(added_date)')
            ->execute();
    }
}
