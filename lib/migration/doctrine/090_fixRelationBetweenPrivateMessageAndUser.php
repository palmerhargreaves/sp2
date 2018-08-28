<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class FixRelationBetweenPrivateMessageAndUser extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createForeignKey('message', 'message_private_user_id_user_id', array(
             'name' => 'message_private_user_id_user_id',
             'local' => 'private_user_id',
             'foreign' => 'id',
             'foreignTable' => 'user',
             ));
        $this->addIndex('message', 'message_private_user_id', array(
             'fields' => 
             array(
              0 => 'private_user_id',
             ),
             ));
    }

    public function down()
    {
        $this->dropForeignKey('message', 'message_private_user_id_user_id');
        $this->removeIndex('message', 'message_private_user_id', array(
             'fields' => 
             array(
              0 => 'private_user_id',
             ),
             ));
    }
}