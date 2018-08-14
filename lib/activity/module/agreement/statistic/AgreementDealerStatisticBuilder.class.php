<?php

/**
 * Description of AgreementDealerStatisticBuilder
 *
 * @author Сергей
 */
class AgreementDealerStatisticBuilder
{
    protected $year;
    protected $stat = array();
    /**
     * A dealer
     *
     * @var Dealer
     */
    protected $dealer;
    private $activities = array();

    private $_filters = array();
    private $_company_type = null;

    /** @var ActivityCompanyCalculator */
    private $_company_calculator = null;

    function __construct($year, Dealer $dealer, $company_type = null, $filters = array())
    {
        $this->year = $year;
        $this->dealer = $dealer;

        $this->_filters = $filters;
        $this->_company_type = $company_type;

        if (!empty($filters) && isset($filters['filter_by_year'])) {
            $this->year = $filters['filter_by_year'];
        }
    }

    /**
     * Build statistics data by dealer
     * @return array
     */
    function build()
    {
        $this->stat = array();

        $this->loadActivities();

        $activities_keys = array_keys($this->activities);

        if (!is_null($this->_company_type)) {
            $this->_company_calculator = ActivityCompanyCalculator::createCalculator($this->_company_type, array(
                'dealer' => $this->dealer,
                'year' => $this->year,
                '_company_type' => $this->_company_type
            ));
        }


        /**
         * Make query for current year and fill data
         */
        $query = AgreementModelTable::getInstance()
            ->createQuery('am')
            ->select('am.id as mId, am.cost as mCost, r.accept_date as rAcceptDate, r.status as rStatus, am.activity_id as mActivityId, a.*')
            ->leftJoin('am.Report r')
            ->leftJoin('am.Activity a')
            ->where('am.dealer_id = ? and (year(am.created_at) = ? or year(am.updated_at) = ?) and am.status = ? and r.status = ?',
                array
                (
                    $this->dealer->getId(),
                    $this->year,
                    $this->year,
                    'accepted',
                    'accepted'
                )
            )
            ->andWhereIn('am.activity_id', $activities_keys)
            ->orderBy('am.id ASC');
        $this->addModelToStat($query->execute(array(), Doctrine_Core::HYDRATE_ARRAY));

        /**
         * Make query for next year and fill data
         */
        $query = AgreementModelTable::getInstance()
            ->createQuery('am')
            ->select('am.id as mId, am.cost as mCost, r.accept_date as rAcceptDate, r.status as rStatus, am.activity_id as mActivityId, , a.*')
            ->leftJoin('am.Report r')
            ->leftJoin('am.Activity a')
            ->where('am.dealer_id = ? and year(am.updated_at) = ? and quarter(am.updated_at) = ? and am.status = ? and r.status = ?',
                array
                (
                    $this->dealer->getId(),
                    ($this->year + 1),
                    RealBudgetCalculator::FIRST_QUARTER,
                    'accepted',
                    'accepted'
                )
            )
            ->andWhereIn('am.activity_id', $activities_keys)
            ->orderBy('am.id ASC');

        $this->addModelToStat($query->execute(array(), Doctrine_Core::HYDRATE_ARRAY));
        if ($this->_company_calculator) {
            for ($q = 1; $q <= 4; $q++) {
                $this->stat[$q][$this->_company_type]['company_statistic']['data'] = $this->_company_calculator->calculate($q);
            }
        }

        return $this->stat;
    }

    private function loadActivities()
    {
        $query = ActivityTable::getInstance()->createQuery()->orderBy('id ASC');
        if (!is_null($this->_company_type)) {
            $query->where('type_company_id = ?', $this->_company_type);
        }

        $items = $query->execute();
        foreach ($items as $item) {
            $this->activities[$item->getId()] = $item;
        }
    }

    function getStat()
    {
        return $this->stat;
    }

    public function getCompanyStatistic() {
        return $this->_company_calculator->getStatisticByQuarters();
    }

    public function getStatisticByMonths() {
        return $this->_company_calculator->getStatisticByMonths();
    }

    function getYear()
    {
        return $this->year;
    }

    /**
     * Returns a dealer
     *
     * @return Dealer
     */
    function getDealer()
    {
        return $this->dealer;
    }

    /**
     * Add models to statistic
     * @param $items
     */
    protected function addModelToStat($items)
    {
        $activities = array();

        foreach ($items as $model) {
            $entry = Utils::getModelDateFromLogEntryWithYear($model['mId']);
            if ($entry) {
                $nDate = $entry;
            } else {
                $nDate = D::calcQuarterData($model['rAcceptDate']);
            }

            $year = D::getYear($nDate);
            $quarter = D::getQuarter($nDate);

            if ($this->year != $year) {
                continue;
            }

            $month_num =  intval(date('m', strtotime($nDate)));

            if (!array_key_exists($model['mActivityId'], $activities)) {
                $activities[$model['mActivityId']] = ActivityTable::getInstance()->find($model['mActivityId']);
            }

            if (!isset($activities[$model['mActivityId']])) {
                continue;
            }

            $activity = $activities[$model['mActivityId']];
            if (!$activity->isActivityStatisticComplete($this->dealer, $nDate)) {
                continue;
            }

            if (!isset($this->stat[$quarter])) {
                $this->stat[$quarter][$this->_company_type] = array(
                    'activities' => array()
                );
            }
            if (!isset($this->stat[$quarter][$this->_company_type]['activities'][$model['mActivityId']])) {
                $this->stat[$quarter][$this->_company_type]['activities'][$model['mActivityId']] = array(
                    'activity' => $activity,
                    'sum' => 0,
                    'models' => array()
                );
            }

            if ($model['rStatus'] == 'accepted') {
                $this->stat[$quarter][$this->_company_type]['activities'][$model['mActivityId']]['sum'] += $model['mCost'];
            }

            $this->stat[$quarter][$this->_company_type]['activities'][$model['mActivityId']]['models'][] = AgreementModelTable::getInstance()->find($model['mId']);

            if ($this->_company_calculator) {
                $this->_company_calculator->addToBudget($quarter, $model['mCost'], $model, $month_num);
            }
        }
    }

    protected function addModelToStatExt(AgreementModel $model)
    {
        $report = $model->getReport();
        if ($model->getReportCssStatus() != 'ok') {
            $date = $model->getCreatedAt();
        } else {
            $date = $report->getAcceptDate();
            //$year = date('Y', $date);

            $entry = LogEntryTable::getInstance()
                ->createQuery()
                ->where('object_id = ?', array($model->getId()))
                ->andWhere('object_type = ? and icon = ? and action = ?', array('agreement_report', 'clip', 'edit'))
                ->orderBy('id DESC')
                ->limit(1)
                ->fetchOne();

            if ($entry) {
                $date = $entry->getCreatedAt();
            }
        }

        $nDate = D::calcQuarterData($date);

        $year = D::getYear($nDate);
        if ($year != $this->year) {
            return;
        }

        $quarter = D::getQuarter($nDate);

        if (!$model->getActivity()->isActivityStatisticComplete($this->dealer, $nDate))
            return;

        //$quarter = D::getQuarter($model->created_at);

        if (!isset($this->stat[$quarter]['activities'][$model->getActivityId()])) {
            $this->stat[$quarter]['activities'][$model->getActivityId()] = array(
                'activity' => $model->getActivity(),
                'sum' => 0,
                'models' => array()
            );
        }

        if ($model->getReportCssStatus() == 'ok')
            $this->stat[$quarter]['activities'][$model->getActivityId()]['sum'] += $model->getCost();


        $this->stat[$quarter]['activities'][$model->getActivityId()]['models'][] = $model;
    }
}
