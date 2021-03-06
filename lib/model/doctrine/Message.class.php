<?php

/**
 * Message
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Message extends BaseMessage
{
    const MSG_STATUS_SENDED = 'sended';
    const MSG_STATUS_DECLINED = 'declined';
    const MSG_STATUS_DECLINED_TO_SPECIALST = 'declined_to_specialist';
    const MSG_STATUS_SENDED_TO_SPECIALIST = 'sended_to_specialist';
    const MSG_STATUS_DECLINED_BY_SPECIALIST = 'declined_by_specialist';
    const MSG_STATUS_ACCEPTED = 'accepted';

    const MSG_STATUS_UNREAD = 'unread';
    const MSG_TYPE_ASK = 'ask';

    const MSG_TYPE_NONE = 'none';
    const MSG_TYPE_MANAGER = 'manager';
    const MSG_TYPE_SPECIALIST = 'specialist';

    const MSG_TYPE_IMPORTER = 'importer';
    const MSG_TYPE_REGIONAL_MANAGER = 'regional_manager';

    const MSG_DIRECTION_ALL = 'all';
    const MSG_DIRECTION_IMPORTER = 'importer';
    const MSG_DIRECTION_ADMIN_DEALER = 'admin_dealer';

    public function isNewForUser($userId)
    {
        $result = MessageTable::getInstance()
            ->createQuery('m')
            ->innerJoin('m.LastReads lr')
            ->where('m.id = ?', $this->getId())
            ->andWhere('lr.user_id = ?', $userId)
            ->execute();

        if (count($result) > 0)
            return false;

        return true;
    }

    public function isNew()
    {
        if ($this->PrivateUser->Group->getId() != 1)
            return true;

        return false;
        /*$discussion = $this->getDiscussion();

        return $discussion->getMessagesCount() > 1 ? false : true;*/
    }

    public function getModel()
    {
        $model = AgreementModelTable::getInstance()
            ->createQuery('am')
            ->select('am.id')
            ->where('am.discussion_id = ?', $this->getDiscussionId())
            ->fetchOne();
        if ($model)
            return $model;

        return null;
    }

    public function isReaded()
    {
        $isReaded = DiscussionLastReadTable::getInstance()
            ->createQuery()
            ->where('message_id = ?', array($this->getId()))
            ->limit(1)
            ->fetchOne();

        return $isReaded ? true : false;
    }
}
