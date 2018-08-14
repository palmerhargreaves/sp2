<?php

/**
 * LogEntryTable
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class LogEntryTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return LogEntryTable
     */
    static function getInstance()
    {
        return Doctrine_Core::getTable('LogEntry');
    }

    /**
     * Add entry to the log
     *
     * @param User $user
     * @param string $object_type
     * @param string $action
     * @param string $title
     * @param string $description
     * @param string $icon
     * @param Dealer $dealer
     * @param int $object_id
     * @param string $module_identifier
     * @param boolean $importance
     * @return LogEntry an added entry
     */
    function addEntry(User $user, $object_type = '', $action = '', $title = '', $description = '', $icon = '', Dealer $dealer = null, $object_id = 0, $module_identifier = '', $importance = false)
    {
        $entry = new LogEntry();
        $entry->setUser($user);
        $entry->setLogin($user->getEmail());
        $entry->setArray(array(
            'object_type' => $object_type,
            'action' => $action,
            'title' => $title,
            'description' => $description,
            'icon' => $icon,
            'dealer_id' => $dealer ? $dealer->getId() : 0,
            'object_id' => $object_id,
            'module_id' => $module_identifier ? ActivityModule::byIdentifier($module_identifier)->getId() : 0,
            'importance' => $importance
        ));
        $entry->save();

        LogEntryReadTable::getInstance()->addRead($user, $entry);

        return $entry;
    }

    /**
     * Returns an object with information about last reading of the log by an user.
     *
     * @param User $user
     * @return LogLastRead
     */
    function getLastRead(User $user)
    {
        $last_read = LogLastReadTable::getInstance()->findOneByUserId($user->getId());
        if (!$last_read) {
            $last_read = new LogLastRead();
            $last_read->setUserId($user->getId());
            $last_read->markAsRead();
        }

        return $last_read;
    }

    /**
     * Returns amount of unread messages
     *
     * @param User $user
     * @param Dealer $dealer if this parameter was passed then limit count only messages for this dealer
     * @return int
     */
    function countUnread(User $user, Dealer $dealer = null, $only_private = false)
    {
        $last_read = $this->getLastRead($user);

        $query = $this->createQuery('l')
            ->select('l.*')
            ->leftJoin('l.UserReads ur WITH ur.user_id=?', $last_read->getUserId())
            ->where('l.created_at>=? and ur.id IS NULL', $last_read->getLastRead());

        self::applyConditionsToSkipUnreadableEntries($query, $user, $only_private);

        if ($dealer)
            $query->andWhereIn('l.dealer_id', array(0, $dealer->getId()));

        return $query->count();
    }

    static function applyConditionsToSkipUnreadableEntries(Doctrine_Query $query, User $user, $only_private = false)
    {
        $query->andWhereNotIn('l.object_type', array('user', 'user_group'));

        if ($only_private)
            $query->andWhere('l.private_user_id=?', $user->getId());
        else
            $query->andWhereIn('l.private_user_id', array(0, $user->getId()));
    }
}