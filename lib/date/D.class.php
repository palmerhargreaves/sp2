<?php

/**
 * Utils class for date conversation
 *
 * @author Сергей
 */
class D
{
    const SECONDS_IN_YEAR = 31536000;

    private static $_calendar_holidays_dates = array();
    private static $_calendar_dates = array();

    static public $genetiveRusMonths = array(
        'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа',
        'сентября', 'октября', 'ноября', 'декабря'
    );

    static private $quarterStartMonths = array(1 => 1, 2 => 4, 3 => 7, 4 => 10);

    const START_YEAR = 2013;
    const MIN_DAYS = 5;

    static function toDb($date, $with_time = false)
    {
        return is_numeric($date) ? date('Y-m-d' . ($with_time ? ' H:i:s' : ''), $date) : $date;
    }

    static function toUnix($date)
    {
        return is_numeric($date) ? intval($date) : strtotime($date);
    }

    static function compare($date1, $date2)
    {
        return self::toDb($date1) == self::toDb($date2)
            ? 0
            : self::toUnix($date1) - self::toUnix($date2);
    }

    static function fromRus($date)
    {
        $exploded = explode('.', $date);
        return mktime(12, 0, 0, intval($exploded[1]), intval($exploded[0]), intval($exploded[2]));
    }

    static function toLongRus($date)
    {
        $current_year = D::getYear(D::calcQuarterData(time()));
        $unix_date = self::toUnix($date);
        $date = getdate($unix_date);
        $result = $date['mday'] . ' ' . self::$genetiveRusMonths[$date['mon'] - 1];

        /*if (abs(time() - $unix_date) > self::SECONDS_IN_YEAR) {
            $result .= ' ' . $date['year'];
        }*/

        if ($current_year != D::getYear($unix_date)) {
            $result .= ' ' . $date['year'];
        }

        return $result;
    }

    static function toShortRus($date, $return_to_day = false)
    {
        $unix_date = self::toUnix($date);
        if (self::compare($unix_date, time()) == 0)
            return $return_to_day ? 'сегодня' : '';

        return self::compare($unix_date, strtotime('-1 day')) == 0
            ? 'вчера'
            : date('d.m.y', $unix_date);
    }

    static function getQuarter($date)
    {
        if (is_numeric($date))
            $month = date('n', $date);
        else
            $month = date('n', self::toUnix($date));

        return floor(($month - 1) / 3) + 1;
    }

    static function getYear($date)
    {
        return date('Y', self::toUnix($date));
    }

    static function getCorrectCurrentYear($date) {
        return self::getYear(self::calcQuarterData($date));
    }

    static function isPrevYear($date)
    {
        $date = self::toUnix($date);
        $year = self::getYear($date);

        $minDay = self::getQuarterStartDay($date);
        $diff_days = date('n', $date) == 1 ? 20 : $minDay;

        $nDate = strtotime('-' . $diff_days . ' days', $date);
        $nYear = self::getYear($nDate);

        if ($year != $nYear)
            return true;

        return false;
    }

    static function calcQuarterData($date, $special = false)
    {
        $date = self::toUnix($date);
        $todayDay = date('j', $date);

        $minDay = self::getQuarterStartDay($date, $special);
        $qStart = self::getFirstMonthOfQuarter(D::getQuarter($date));

        if ($qStart != date('n', $date)) {
            return $date;
        }

        $diff_days = 0;
        if ($todayDay < $minDay) {
            $diff_days = date('n', $date) == 1 ? self::getQuarterStartDay($date) : $minDay;
        }
        $nDate = strtotime('-' . $diff_days . ' days', $date);

        return $nDate;
    }

    static function getFirstMonthOfQuarter($quarter)
    {
        return (($quarter - 1) * 3) + 1;
    }

    static function getQuarterStartDay($date, $special = false)
    {
        $q = D::getQuarter($date);

        $days = BudgetCalendarTable::getDays(D::getYear($date), $special);
        if (empty($days)) {
            return self::MIN_DAYS;
        }

        return $days[$q];
    }

    static function getBudgetYears(sfWebRequest $request = null, $simple = false)
    {
        $years = array();

        if (!is_null($request)) {
            $year = D::getBudgetYear($request);
        }

        for ($i = self::START_YEAR; $i <= date('Y'); $i++) {
            $years[] = $i;
        }

        return $years;
    }

    static function getBudgetYear(sfWebRequest $request)
    {
        if ($request && $request->getParameter('year')) {
            return $request->getParameter('year');
        }

        $q = self::getQuarter(D::calcQuarterData(time()));
        $year = date('Y');

        if ($q == 1 && date('m') == 1) {
            $day = date('d');

            $days = BudgetCalendarTable::getDays(D::getYear(date('Y-m-d')));
            if (empty($days)) {
                return $year;
            }

            return ($day >= $days[$q] ? $year : $year - 1);
        }

        return date('Y');
    }

    static function isSpecialFirstQuarter(sfWebRequest $request)
    {
        $q = self::getQuarter(date('Y-m-d'));

        if ($q == 1) {
            $day = date('d');

            $days = BudgetCalendarTable::getDays(D::getYear(date('Y-m-d')));
            if (empty($days)) {
                return false;
            }

            return ($day > $days[$q] ? false : true);
        }

        return false;
    }

    static function getElapsedDays($st)
    {
        return floor(($st / 3600) / 24);
    }

    static function getYearsRangeList($begin = null, $end = null, $total = 10)
    {
        $year = range(!is_null($begin) ? $begin : D::START_YEAR,
            (!is_null($end) ? $end : date('Y')) + $total);

        return array_merge(array(''), array_combine($year, $year));
    }

    static function getQuarterStartMonth($date)
    {
        $quartersStart = array(
            1 => 1,
            2 => 4,
            3 => 7,
            4 => 10
        );

        $quarter = self::getQuarter($date);

        return $quartersStart[$quarter];
        /*$current_month = date('m', strtotime($date));
        $current_quarter_start = floor($current_month / 4) * 3 + 1;*/

        /*
         * $start_date = date("Y-m-d H:i:s", mktime(0, 0, 0, $current_quarter_start, 1, date('Y', strtotime($data[$field])) ));
         * $end_date = date("Y-m-d H:i:s", mktime(0, 0, 0, $current_quarter_start + 3, 1, date('Y', strtotime($data[$field])) ));
         */

        //return $current_quarter_start;
    }

    private static function loadCalendarDates() {
        self::$_calendar_dates = CalendarTable::getInstance()->createQuery()->select('start_date, end_date')->orderBy('id ASC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
    }

    /**
     * @param $date
     * @return int
     */
    static function checkDateInCalendar($date)
    {
        if (empty(self::$_calendar_dates)) {
            self::loadCalendarDates();
        }

        $days = 0;
        $check_date = date('Y-m-d', D::toUnix($date));

        $item = null;
        foreach (self::$_calendar_dates as $calendar_date) {
            if (strtotime($check_date) >= strtotime($calendar_date[ 'start_date' ]) && strtotime($check_date) <= strtotime($calendar_date[ 'end_date' ])) {
                $item = $calendar_date;
                break;
            }
        }

        if (!is_null($item) && isset($item['end_date'])) {
            $endDate = strtotime($item['end_date']);

            $days = 1;
            $i = 1;
            while (1) {
                $calc_date = strtotime(date("Y-m-d", strtotime('+' . $i . ' days', D::toUnix($check_date))));
                if ($calc_date <= $endDate) {
                    $days++;
                    $i++;
                } else {
                    break;
                }
            }
        }

        return $days;
    }

    /**
     * @param $model
     * @param $date
     * @return bool|string
     */
    static function makePlusDaysForModel($model, $date) {
        $plusDays = 1; //Количество дней для выполнения заявки
        if ($model->getStatus() == "accepted") {
            $plusDays = 5; //Количество дней для выполнения отчета
        }

        for ($i = 1; $i <= $plusDays; $i++) {
            $tempDate = date("d-m-Y H:i:s", strtotime('+' . $i . ' days', D::toUnix($date)));
            $d = getdate(strtotime($tempDate));

            $dPlus = self::checkDateInCalendar($tempDate);
            if ($dPlus == 0) {
                if ($d['wday'] == 0 || $d['wday'] == 6)
                    $dPlus++;
            } else if ($dPlus > 1) {
                $i += $dPlus;            }

            $plusDays += $dPlus;
        }

        return date("H:i:s d-m-Y", strtotime('+' . $plusDays . ' days', D::toUnix($date)));
    }

    static function getNewDate($date, $plusDays = 1, $sign = '+', $only_days = false, $format = 'd-m-Y H:i:s', $start_from_day_idx = 1)
    {
        $total_days = 0;
        for ($i = $start_from_day_idx; $i <= $plusDays; $i++) {
            $tempDate = date($format, strtotime($sign . $i . ' days', D::toUnix($date)));

            $d = getdate(strtotime($tempDate));
            $dPlus = self::checkDateInCalendar($tempDate);
            if ($dPlus == 0) {
                if ($d['wday'] == 0 || $d['wday'] == 6) {
                    $dPlus++;
                }
            } else if ($dPlus > 1) {
                $i += $dPlus;
            }

            $plusDays += $dPlus;
            $total_days += $dPlus;
        }

        if ($only_days) {
            return $total_days;
        }

        return date($format, strtotime($sign . $plusDays . ' days', D::toUnix($date)));
    }

    public static function calcCorrectModelDateToSort($model, $calc_date) {
        $days = 1; //Количество дней для выполнения заявки
        if ($model->getStatus() == "accepted") {
            $days = 5; //Количество дней для выполнения отчета
        }

        return D::getNewDate($calc_date, $days);
    }

    public static function getFirstDayOfMonth() {
        return date('d', strtotime('first day of this month'));
    }

    public static function getLastDayOfMonth() {
        return cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
    }

    static function getMonthName($month_index)
    {
        $months = array("Янв.", "Фев.", "Мар.", "Апр.", "Май", "Июнь", "Июль", "Авг.", "Сен.", "Окт.", "Ноя.", "Дек.");

        return $months[$month_index - 1];
    }

    static function getMonthNameMessages($month_index)
    {
        $months = array("янв", "фев", "мар", "апр", "май", "июн", "июл", "авг", "сен", "окт", "ноя", "дек");

        return $months[$month_index - 1];
    }

    static function getQuarterMonths($q) {
        $q_months = array
        (
            1 => array(1, 2, 3),
            2 => array(4, 5, 6),
            3 => array(7, 8, 9),
            4 => array(10, 11, 12)
        );

        return $q_months[$q];
    }

    /**
     * Format model period (get last date)
     * @param $period
     * @return array|string
     */
    public static function formatModelPeriod($period) {
        $split_period = explode('-', $period);
        $last_period_item = array_pop($split_period);

        $model_period = explode("-", str_replace(".", "-", $last_period_item));
        $formatted_year = DateTime::createFromFormat('y', $model_period[count($model_period) - 1]);

        $model_period[count($model_period) - 1] = $formatted_year->format('Y');
        $model_period = date('d-m-Y', strtotime(implode('-', $model_period))) .' '. date('H:i:s', time());

        return $model_period;
    }

    /**
     * Check date for weekend
     * @param $date
     * @return false|string
     */
    public static function checkDateForWeekend($date) {
        $date = !is_numeric($date) ? strtotime($date) : $date;

        $weekend_date = getdate($date);

        $days_in_calendar = D::checkDateInCalendar($date);

        $plus_days = 0;
        if ($days_in_calendar == 0) {
            if ($weekend_date['wday'] == 0) {
                $plus_days = 2;
            } else if($weekend_date['wday'] == 6) {
                $plus_days = 1;
            }
        } else {
            $plus_days = $days_in_calendar;
            $date = self::checkDateForWeekend(date('Y-m-d', strtotime('-'.$plus_days.' days', $date)));
        }

        return date('Y-m-d', strtotime('-'.$plus_days.' days', $date));
    }

    /**
     * Format date for messages in form (day month - string Year)
     * @param $date
     * @return string
     */
    public static function formatMessagesDate($date) {
        $day = date('j', strtotime($date));
        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));

        return sprintf('%s %s %s', $day, self::getMonthNameMessages($month), $year);
    }
}

