<?php

/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 08.11.16
 * Time: 12:24
 */
class mailing_listActions extends sfActions
{
    public function executeIndex(sfWebRequest $request)
    {
        $this->dealer_id = $request->getParameter('dealer_id', '93500777');
        $this->limit = $request->getParameter('limit', 50);
        $this->offset = $request->getParameter('offset', 0);
        $this->dealers = DealerTable::getInstance()->createQuery()->select()->where('number LIKE ?', '%93500%')->andWhere('importer_id = 1')->orderBy('number')->execute();

        $Dq = Doctrine_Query::create()
            ->from('MailingList');
        if($this->dealer_id)
            $Dq->where('dealer_id = ?', $this->dealer_id);

        $Dq->limit($this->limit)->offset($this->offset)->orderBy('added_date DESC');
        $this->mailings = $Dq->execute();

        $this->mailing_years = MailingListTable::getInstance()->createQuery()->select('year(added_date) as mail_year')->groupBy('mail_year')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
    }

    public function executeStat(sfWebRequest $request)
    {
        $this->month = $request->getParameter('month', 1);

        MailingList::exportStatToXlsAll($this->month, $request->getParameter('mailing_year', date('Y'))); die();
    }
}
