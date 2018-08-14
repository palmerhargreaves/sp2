<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 03.06.2016
 * Time: 17:54
 */

class UsersStatistics {
    private $_stats = array
    (
        'active_users' => array('label' => 'Активных пользователей', 'count' => 0),
    );

    public function build() {
        $this->_stats['active_users']['count'] = UserTable::getInstance()->createQuery()->where('active = ?', 1)->count();

        foreach (UserGroupTable::getInstance()->createQuery()->execute() as $group) {
            $this->_stats[$group->getId()] =
                array
                (
                    'label' => $group->getName(),
                    'count' => UserTable::getInstance()->createQuery()->where('active = ? and group_id = ?', array(1, $group->getId()))->count()
                );
        }

        return $this->_stats;
    }
}