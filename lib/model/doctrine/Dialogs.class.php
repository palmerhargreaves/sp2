<?php

/**
 * Dialogs
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Dialogs extends BaseDialogs
{
    public function getBindingPostsAndUsersInfo() {
        return array('posts' => DialogsUsersLimitsTable::getInstance()
                        ->createQuery()
                        ->where('dialog_id = ?', $this->getId())
                        ->andWhere('post_type != ?', 0)
                        ->count(),
                    'users' => DialogsUsersLimitsTable::getInstance()
                        ->createQuery()
                        ->where('dialog_id = ?', $this->getId())
                        ->andWhere('user_id != ?', 0)
                        ->count()
        );
    }
}
