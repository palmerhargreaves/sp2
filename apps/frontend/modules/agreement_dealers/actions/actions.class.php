<?php

/**
 * agreement_dealers actions.
 *
 * @package    Servicepool2.0
 * @subpackage activity_dealers
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class agreement_dealersActions extends sfActions
{
    const FILTER_NAMESPACE = 'agreement_dealer_filter';

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        //$this->year = $this->getBudgetYear($request->getParameter('onlyShow'));
        $this->year = $this->getFilterByYear();

        //var_dump(new DateTime());
        $builder = new AgreementDealersStatisticBuilder($this->year, $this->getUser()->getAuthUser());
        $builder->build();

        //var_dump(new DateTime());

        $this->builder = $builder;
        $this->budgetYears = D::getBudgetYears($request);
    }

    public function executeDealer(sfWebRequest $request)
    {
        $dealer = DealerTable::getInstance()->find($request->getParameter('id'));
        $this->forward404Unless($dealer);

        $this->year = D::getBudgetYear($request);

        $company_types_builder = new ActivitiesCompanyTypesBuilder
        (
            $this->getUser(),
            $request,
            array
            (
                'filter_by_year' => $this->year,
                'filter_by_dealer' => $dealer->getId()
            )
        );
        $company_types_builder->buildOnlyForStatistic();

        $this->builder = $company_types_builder;
        $this->dealers_statistics = $company_types_builder->getDealersStatistic();

        $this->outputDeclineReasons();
        $this->outputDeclineReportReasons();
        $this->outputSpecialistGroups();

        $this->budgetYears = D::getBudgetYears($request);
    }

    public function executeFake()
    {

    }

    function outputDeclineReasons()
    {
        $this->decline_reasons = AgreementDeclineReasonTable::getInstance()->createQuery()->execute();
    }

    function outputDeclineReportReasons()
    {
        $this->decline_report_reasons = AgreementDeclineReportReasonTable::getInstance()->createQuery()->execute();
    }

    function outputSpecialistGroups()
    {
        $this->specialist_groups = UserGroupTable::getInstance()
            ->createQuery('g')
            ->distinct()
            ->select('g.*')
            ->innerJoin('g.Roles r WITH r.role=?', 'specialist')
            ->innerJoin('g.Users u WITH u.active=?', true)
            ->execute();
    }

    function getFilterByYear()
    {
        $default = $this->getUser()->getAttribute('filter_year', date('Y'), self::FILTER_NAMESPACE);
        $value = $this->getRequestParameter('filter_year', $default);

        $this->getUser()->setAttribute('filter_year', $value, self::FILTER_NAMESPACE);

        return $value;
    }

}
