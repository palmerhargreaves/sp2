<?php

/**
 * activities_statistic actions.
 *
 * @package    Servicepool2.0
 * @subpackage activity
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class activities_statisticActions extends ActionsWithJsonForm
{
    const FILTER_NAMESPACE = 'activities_stats_filter';

    private $_request = null;

    function executeIndex(sfWebRequest $request)
    {
        $this->_request = $request;

        $this->outputFilters();

        $this->outputActivities();
        $this->outputBudget();

        $this->outputModels();
    }

    function executeFilter(sfWebRequest $request)
    {
        $this->_request = $request;

        $this->outputModels(false);
    }

    /**
     * Фильтр данных по выбранному году + получение данных по бюджетам
     * @param sfWebRequest $request
     * @return string
     */
    function executeFilterByYear(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $this->_request = $request;

        $this->outputFilters();

        $this->outputActivities();
        $this->outputBudget();
        $this->outputModels(false);

        return $this->sendJson(array(
            'filter_data' => get_partial('filter_data',
                array
                (
                    '_activity' => $this->_activity,
                    'completed_models' => $this->completed_models,
                    'in_work_models' => $this->in_work_models
                )
            ),
            'budget_panel' => get_partial('budget_panel',
                array
                (
                    'plan' => $this->_plan,
                    'real' => $this->_real,
                    'current_quarter' => $this->_current_quarter,
                    'year' => $this->_year
                )
            )
        ));
    }

    /**
     * Get models list (completed / in work)
     */
    private function outputModels($use_default_filter = true) {

        $this->outputYearFilter();

        /*Get completed models list*/
        $this->completed_models = DealerStatisticFactory::getInstance()->getDealerStatistic('completed',
            array
            (
                'request' => $this->_request,
                'default_filter' => $use_default_filter ? array
                (
                    'dealer_id' => $this->_dealer->getId(),
                    'quarter' => $this->_current_quarter,
                    'year' => $this->_year
                ) : null
            )
        );

        /*Get in work models list*/
        $this->in_work_models = DealerStatisticFactory::getInstance()->getDealerStatistic('inWork',
            array
            (
                'request' => $this->_request,
                'user' => $this->getUser(),
                'default_filter' => $use_default_filter ? array
                (
                    'dealer_id' => $this->_dealer->getId(),
                    'quarter' => $this->_current_quarter,
                    'year' => $this->_year
                ) : null
            )
        );
    }


    public function outputFilters() {
        $this->outputModelStatusFilter();
        $this->outputActivityFilter();
        $this->outputYearFilter();
    }

    private function outputModelStatusFilter() {
        $default = $this->getUser()->getAttribute('model_status', 'all', self::FILTER_NAMESPACE);
        $model_status = $this->getRequestParameter('model_status', $default);
        //$this->getUser()->setAttribute('model_status', $model_status, self::FILTER_NAMESPACE);

        $this->_model_status = $model_status;
    }

    private function outputActivityFilter() {
        $default = $this->getUser()->getAttribute('activity', '', self::FILTER_NAMESPACE);
        $activity_id = $this->getRequestParameter('activity', $default);
        //$this->getUser()->setAttribute('model_status', $model_status, self::FILTER_NAMESPACE);

        $this->_activity = null;
        if (!empty($activity_id)) {
            $activity = ActivityTable::getInstance()->find($activity_id);
            $this->_activity = $activity;
        }
    }

    private function outputYearFilter() {
        $year = D::getYear(D::calcQuarterData(time()));

        $default = $this->getUser()->getAttribute('year', $year, self::FILTER_NAMESPACE);
        $this->_year = $this->getRequestParameter('year', empty($default) ? $year : $default);
        $this->getUser()->setAttribute('year', $this->_year, self::FILTER_NAMESPACE);
    }

    /**
     * Get active activities list
     */
    private function outputActivities() {
        $query = ActivityTable::getInstance()
            ->createQuery()
            ->select('id, name')
            //->where('finished = ?', false)
            ->orderBy('id DESC');

        $query->where('year(start_date) = ? or year(end_date) = ?', array($this->_year, $this->_year));

        $this->activities_list = $query->execute();
    }

    /**
     * Get dealer budget by quarters
     */
    private function outputBudget() {
        $this->getCurrentDealer();

        $this->outputPlan();
        $this->outputReal();
    }

    private function getCurrentDealer() {
        $current_date = D::calcQuarterData(time());

        $this->_year = is_null($this->_year) ? D::getYear($current_date) : $this->_year;
        $this->_current_quarter = D::getQuarter($current_date);

        $dealer = $this->getUser()->getAuthUser()->getDealer();
        if (!$dealer) {
            throw new UserIsNotDealerException('User not binded to dealer');
        }

        $this->_dealer = $dealer;
    }

    /**
     * Get plan budget
     */
    private function outputPlan() {
        $defined_plan = BudgetTable::getInstance()
            ->createQuery()
            ->where(
                'dealer_id=? and year=?',
                array($this->_dealer->getId(), $this->_year)
            )
            ->orderBy('quarter asc')
            ->execute();

        $plan = array();
        for ($n = 1; $n <= 4; $n++) {
            $empty_plan = new Budget();
            $empty_plan->setArray(array(
                'dealer_id' => $this->_dealer->getId(),
                'year' => $this->_year,
                'quarter' => $n,
                'plan' => 0
            ));
            $plan[$n] = $empty_plan;
        }

        foreach ($defined_plan as $p) {
            $plan[$p->getQuarter()] = $p;
        }

        $this->_plan = $plan;
    }

    /**
     * Get fact budget
     */
    private function outputReal() {
        $real = new RealBudgetCalculator($this->_dealer, $this->_year);

        $this->_real = $real->calculate();
    }
}
