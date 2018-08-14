<?php

/**
 * histoy actions.
 *
 * @package    Servicepool2.0
 * @subpackage histoy
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class historyActions extends sfActions
{
    const PAGE_LEN = 20;

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    function executeIndex(sfWebRequest $request)
    {
        LogEntryTable::getInstance()->getLastRead($this->getUser()->getAuthUser())->markAsRead();

        $this->outputHistory($request);
        $this->outputDealers();

        $this->page_len = self::PAGE_LEN;
    }

    private function outputDealers() {
        $this->dealers = DealerTable::getVwDealersQuery()->execute();
    }

    function executePage(sfWebRequest $request)
    {
        $this->outputHistory($request);
    }

    function executeEntry(sfWebRequest $request)
    {
        $entry = LogEntryTable::getInstance()->findOneById($request->getParameter('id'));
        $this->forward404Unless($entry);

        LogEntryReadTable::getInstance()->addRead($this->getUser()->getAuthUser(), $entry);

        $uri = HistoryProcessorFactory::getInstance()->getProcessor('system')->getSourceUri($entry);
        if ($uri)
            $this->redirect($uri);

        foreach (ActivityModuleTable::getInstance()->createQuery()->execute() as $module) {
            $uri = $module->getHistoryProcessor()->getSourceUri($entry);

            if ($uri)
                $this->redirect($uri);
        }

        $this->forward404();
    }

    function outputHistory(sfWebRequest $request)
    {
        $query = LogEntryTable::getInstance()
            ->createQuery('l')
            ->leftJoin('l.User u')
            ->leftJoin('u.Group g')
            //->leftJoin('u.DealerUsers du')
            ->orderBy('created_at desc, id desc')
            ->offset($request->getParameter('offset', 0))
            ->limit(self::PAGE_LEN);

        /*$search = trim($request->getParameter('search', ''));
        if ($search)
            $query->andWhere('match(title, description) against (?)', $search);*/

        $search_by_object_id = $request->getParameter('search_by_id');
        $search_by_dealer_id = $request->getParameter('search_by_dealer');
        $search_by_date = $request->getParameter('search_by_date');

        if (!empty($search_by_object_id)) {
            $query->andWhere('object_id = ?', $search_by_object_id);
        }

        if (!empty($search_by_dealer_id)) {
            $query->andWhere('dealer_id = ?', $search_by_dealer_id);
        }

        if (!empty($search_by_date)) {
            $query->andWhere('created_at LIKE ?', '%'.date('Y-m-d', strtotime(str_replace('.', '-', $search_by_date))).'%');
        }

        LogEntryTable::applyConditionsToSkipUnreadableEntries(
            $query,
            $this->getUser()->getAuthUser(),
            !$this->getUser()->isManager() && $this->getUser()->isSpecialist()
        );

        if (!$this->getUser()->isManager() && $this->getUser()->isDealerUser())
            $query->andWhere('l.dealer_id=? or l.dealer_id=0', $this->getUser()->getAuthUser()->getDealer()->getId());

        $this->history = $query->execute();
    }
}
