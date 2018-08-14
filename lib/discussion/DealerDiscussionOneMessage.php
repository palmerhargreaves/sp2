<?php
/**
 * Created by PhpStorm.
 * User: kostig51
 * Date: 04.02.2018
 * Time: 15:30
 */

class DealerDiscussionOneMessage extends BaseDealerDiscussionLogger
{
    protected $_action = 'post_to_user';

    protected function getMessages ( $last_id )
    {
        return MessageTable::getInstance()
            ->createQuery('m')
            ->innerJoin('m.Discussion d')
            ->innerJoin('d.DealerDiscussions dd')
            ->leftJoin('m.User u')
            ->where('m.id>?', $last_id)
            ->andWhere('m.contact_id != ?', 0)
            ->orderBy('id ASC')
            ->execute();
    }
}