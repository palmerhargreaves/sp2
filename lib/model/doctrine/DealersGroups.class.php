<?php

/**
 * DealersGroups
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class DealersGroups extends BaseDealersGroups
{
    /**
     * Get binded dealer count to group
     * @return mixed
     */
    public function getBindedDealersCount() {
        return DealerTable::getInstance()->createQuery()->where('dealer_group_id = ?', $this->getId())->count();
    }
}