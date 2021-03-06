<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddControlOfNewAgreementNotification extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('user', 'new_agreement_notification', 'boolean', '25', array(
             'default' => '1',
             'notnull' => '1',
             ));
        $this->addIndex('user', 'new_agreement_notification', array(
             'fields' => 
             array(
              0 => 'new_agreement_notification',
             ),
             ));
    }

    public function down()
    {
        $this->removeIndex('user', 'new_agreement_notification', array(
             'fields' => 
             array(
              0 => 'new_agreement_notification',
             ),
             ));
        $this->removeColumn('user', 'new_agreement_notification');
    }
}