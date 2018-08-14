<?php

/**
 * Helps to copy discussion messages to the log
 *
 * @author Сергей
 */
class DealerDiscussionLogger extends BaseDealerDiscussionLogger
{
    protected function getMessages ( $last_id )
    {
        return MessageTable::getInstance()
            ->createQuery('m')
            ->innerJoin('m.Discussion d')
            ->innerJoin('d.DealerDiscussions dd')
            ->leftJoin('m.User u')
            ->where('m.id>?', $last_id)
            ->andWhere('m.contact_id = ?', 0)
            ->orderBy('id ASC')
            ->execute();
    }
}
