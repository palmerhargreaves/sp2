<?php
use FileUpload\Util;

/**
 * Description of components
 *
 * @author kostig51
 */
class budget_by_pointsComponents extends sfComponents
{
    function executeBudgetPanel(sfWebRequest $request)
    {
        $this->year = D::getBudgetYear($request);
        $this->isPrevYear = '';

        $this->outputPlan();
        $this->outputReal();
        $this->outputMonths();
        $this->outputCurrentQuarter();
        $this->outputQuarterEnds();
        $this->outputQuarterDays();
        $this->outputAcceptStat();

        //$this->outputServiceBooks();

        $this->calculateYearFactPlanData();
    }

    protected function outputPlan()
    {
        $dealer = $this->dealer;
        $year_plan = 0;

        if ($dealer) {
            $defined_plan = BudgetTable::getInstance()
                ->createQuery()
                ->where(
                    'dealer_id=? and year=?',
                    array($dealer->getId(), $this->year)
                )
                ->orderBy('quarter asc')
                ->execute();

            $plan = array();
            for ($n = 1; $n <= 4; $n++) {
                $empty_plan = new Budget();
                $empty_plan->setArray(array(
                    'dealer_id' => $dealer->getId(),
                    'year' => $this->year,
                    'quarter' => $n,
                    'plan' => 0
                ));
                $plan[$n] = $empty_plan;
            }

            foreach ($defined_plan as $p) {
                $plan[$p->getQuarter()] = $p;
                $year_plan += $p->getPlan();
            }
        }

        $this->plan = $plan;
        $this->year_plan = $year_plan;
    }

    protected function outputReal()
    {
        $dealer = $this->dealer;
        $year_real = 0;

        $real = new RealBudgetCalculator($dealer, $this->year);

        $this->real = $real->calculate();
        foreach ($this->real as $k => $item) {
            $year_real += $item;
        }

        $this->year_real = $year_real;
    }

    protected function outputMonths()
    {
        $this->months = array(
            1 => array('Январь', 'Февраль', 'Март'),
            2 => array('Апрель', 'Май', 'Июнь'),
            3 => array('Июль', 'Август', 'Сентябрь'),
            4 => array('Октябрь', 'Ноябрь', 'Декабрь'),
        );
    }

    protected function outputCurrentQuarter()
    {
        $this->current_quarter = D::getQuarter(D::calcQuarterData(time()));
    }

    protected function outputQuarterEnds()
    {
        $this->quarter_ends = array(
            1 => 31,
            2 => 30,
            3 => 30,
            4 => 31
        );
    }

    protected function outputQuarterDays()
    {
        $quarter_days = array();

        $cur_date = getdate();
        for ($n = 1; $n <= 4; $n++) {
            $start_month = ($n - 1) * 3 + 1;
            $end_month = $start_month + 2;
            $start_date = getdate(mktime(12, 0, 0, $start_month, 1, $this->year));
            $end_date = getdate(mktime(12, 0, 0, $end_month + 1, 0, $this->year));
            $quarter_length = $end_date['yday'] - $start_date['yday'] + 1;
            $quarter_day = $cur_date['yday'] - $start_date['yday'];
            if ($quarter_day < 0)
                $quarter_day = 0;
            elseif ($cur_date['yday'] > $end_date['yday'])
                $quarter_day = $quarter_length - 1;

            $quarter_days[$n] = array(
                'length' => $quarter_length,
                'day' => $quarter_day
            );
        }

        $this->quarter_days = $quarter_days;
    }

    protected function outputAcceptStat()
    {
        $full_stat = ActivityTable::getInstance()->getAcceptStat($this->year, $this->dealer);
        /*$view_stat = array(
          1 => 0,
          2 => 0,
          3 => 0,
          4 => 0
        );*/

        /*for($q = 1; $q <= 4; $q ++)
        {
          $view_stat[$q] += $full_stat[$q];

          if($view_stat[$q] > 3)
          {
            $overflow = $view_stat[$q] - 3;
            $view_stat[$q] = 3;
            /*if($q < 4)
              $view_stat[$q + 1] += $overflow;*/

        /*}
      }*/

        $this->accept_stat = $full_stat;
    }

    /**
     *
     */
    protected function calculateYearFactPlanData() {
        $this->year_work_result = array(
            'real' => $this->year_real,
            'real_left' => 0,
            'real_recomplete' => 0,
            'plan' => $this->year_plan,
            'percent_complete' => 0,
            'quarters_result' => array(1 => 0, )
        );

        //Если выполненно меньше запланированной суммы, вычисляем оставшуюся сумму для выполнения и процент выполнения плана
        if ($this->year_real < $this->year_plan) {
            $this->year_work_result['real_left'] = $this->year_plan - $this->year_real;
            $this->year_work_result['percent_complete'] = $this->year_real * 100 / $this->year_plan;
        } else {
            $this->year_work_result['percent_complete'] = 100;
            $this->year_work_result['real_recomplete'] = $this->year_real - $this->year_plan;
        }

        //Вычисляем выполнение плана по кварталам
        $q_statistics = new ActivitiesBudgetByControlPoints($this->year, $this->dealer, $this->getUser(), $this->real, $this->plan);
        $this->quarters_statistics = $q_statistics->getData();

    }

    private function outputServiceBooks() {
        if ($this->getUser()->getAttribute('service_books_new-' . $this->dealer->getId(), -1, 'service-books-new') == -1) {
            $this->service_books_count = ServiceBooks::getDealerServiceBookData($this->dealer->getShortNumber());

            $this->getUser()->setAttribute('service_books_new-' . $this->dealer->getId(), $this->service_books_count, 'service-books-new');
        } else {
            $this->service_books_count = $this->getUser()->getAttribute('service_books_new-' . $this->dealer->getId(), -1, 'service-books-new');
        }
    }
}
