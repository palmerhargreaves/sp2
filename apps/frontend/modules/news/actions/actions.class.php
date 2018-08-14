<?php

/**
 * news actions.
 *
 * @package    Servicepool2.0
 * @subpackage news
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class newsActions extends sfActions
{
    function executeIndex(sfWebRequest $request)
    {
        $result = array();
        //$news = NewsTable::getInstance()->createQuery()->select('*')->where('status = ? and year(created_at) = ?', array(true, date('Y')))->orderBy('id DESC')->execute();
        $news = NewsTable::getInstance()->createQuery()->select('*')->where('status = ?', array(true))->orderBy('id DESC')->execute();

        $days = 0;
        $today = $this->getElapsedDays(strtotime(date('d-m-Y')));
        $lastDate = date('d-m-Y', strtotime($news->getFirst()->getCreatedAt()));

        foreach ($news as $item) {
            $isNew = false;
            $createdAt = $this->getElapsedDays(strtotime($item->getCreatedAt()));
            $tempDate = date('d-m-Y', strtotime($item->getCreatedAt()));

            $elDays = $today - $createdAt;
            if ($elDays < 30 && $days < 3 && ($lastDate == $tempDate))
                $isNew = true;

            $result[] = array("item" => $item, "isNew" => $isNew);

            $days++;
        }

        $this->news = $result;
    }

    function executeNewsInfo(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        $today = $this->getElapsedDays(strtotime(date('d-m-Y')));

        $query = NewsTable::getInstance()->createQuery()->select('*')->where('id = ?', $id);
        $item = $query->fetchOne();

        $createdAt = $this->getElapsedDays(strtotime($item->getCreatedAt()));

        $elDays = $today - $createdAt;
        $lastDate = date('d-m-Y', strtotime($item->getCreatedAt()));

        $this->item = array("item" => $item, "isNew" => $elDays < 30 ? true : false);

        $query = NewsTable::getInstance()->createQuery()->select('*')->where('status = ? and id != ?', array(true, $item->getId()))->orderBy('id DESC')->limit(10);
        $lastNews = $query->execute();

        $result = array();
        $totalNewNews = 0;

        foreach ($lastNews as $item) {
            $isNew = false;
            $tempDate = date('d-m-Y', strtotime($item->getCreatedAt()));

            $createdAt = $this->getElapsedDays(strtotime($item->getCreatedAt()));
            $elDays = $today - $createdAt;

            if ($totalNewNews < 2 && $elDays < 30 && ($tempDate == $lastDate))
                $isNew = true;

            $result[] = array("item" => $item, "isNew" => $isNew);
        }

        $this->last10News = $result;
    }

    function getElapsedDays($st)
    {
        return floor(($st / 3600) / 24);
    }
}
