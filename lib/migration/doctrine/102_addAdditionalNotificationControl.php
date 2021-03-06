<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddAdditionalNotificationControl extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('user', 'final_agreement_notification', 'boolean', '25', array(
             'default' => '1',
             'notnull' => '1',
             ));
        $this->addColumn('user', 'dealer_discussion_notification', 'boolean', '25', array(
             'default' => '1',
             'notnull' => '1',
             ));
        $this->addColumn('user', 'model_discussion_notification', 'boolean', '25', array(
             'default' => '1',
             'notnull' => '1',
             ));
        $this->addIndex('user', 'final_agreement_notification', array(
             'fields' => 
             array(
              0 => 'final_agreement_notification',
             ),
             ));
        $this->addIndex('user', 'dealer_discussion_notification', array(
             'fields' => 
             array(
              0 => 'dealer_discussion_notification',
             ),
             ));
        $this->addIndex('user', 'model_discussion_notification', array(
             'fields' => 
             array(
              0 => 'model_discussion_notification',
             ),
             ));
    }

    public function down()
    {
        $this->removeIndex('user', 'final_agreement_notification', array(
             'fields' => 
             array(
              0 => 'final_agreement_notification',
             ),
             ));
        $this->removeIndex('user', 'dealer_discussion_notification', array(
             'fields' => 
             array(
              0 => 'dealer_discussion_notification',
             ),
             ));
        $this->removeIndex('user', 'model_discussion_notification', array(
             'fields' => 
             array(
              0 => 'model_discussion_notification',
             ),
             ));
        $this->removeColumn('user', 'final_agreement_notification');
        $this->removeColumn('user', 'dealer_discussion_notification');
        $this->removeColumn('user', 'model_discussion_notification');
    }
}