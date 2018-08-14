<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddAcceptedDealerActivities extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createTable('accepted_dealer_activity', array(
             'id' => 
             array(
              'type' => 'integer',
              'primary' => '1',
              'autoincrement' => '1',
              'length' => '8',
             ),
             'activity_id' => 
             array(
              'type' => 'integer',
              'notnull' => '1',
              'length' => '8',
             ),
             'dealer_id' => 
             array(
              'type' => 'integer',
              'notnull' => '1',
              'length' => '8',
             ),
             ), array(
             'type' => 'MyISAM',
             'primary' => 
             array(
              0 => 'id',
             ),
             'collate' => 'utf8_unicode_ci',
             'charset' => 'utf8',
             ));
        $this->createForeignKey('accepted_dealer_activity', 'accepted_dealer_activity_activity_id_activity_id', array(
             'name' => 'accepted_dealer_activity_activity_id_activity_id',
             'local' => 'activity_id',
             'foreign' => 'id',
             'foreignTable' => 'activity',
             ));
        $this->createForeignKey('accepted_dealer_activity', 'accepted_dealer_activity_dealer_id_dealers_id', array(
             'name' => 'accepted_dealer_activity_dealer_id_dealers_id',
             'local' => 'dealer_id',
             'foreign' => 'id',
             'foreignTable' => 'dealers',
             ));
        $this->addIndex('accepted_dealer_activity', 'accepted_dealer_activity_activity_id', array(
             'fields' => 
             array(
              0 => 'activity_id',
             ),
             ));
        $this->addIndex('accepted_dealer_activity', 'accepted_dealer_activity_dealer_id', array(
             'fields' => 
             array(
              0 => 'dealer_id',
             ),
             ));
    }

    public function down()
    {
        $this->dropForeignKey('accepted_dealer_activity', 'accepted_dealer_activity_activity_id_activity_id');
        $this->dropForeignKey('accepted_dealer_activity', 'accepted_dealer_activity_dealer_id_dealers_id');
        $this->removeIndex('accepted_dealer_activity', 'accepted_dealer_activity_activity_id', array(
             'fields' => 
             array(
              0 => 'activity_id',
             ),
             ));
        $this->removeIndex('accepted_dealer_activity', 'accepted_dealer_activity_dealer_id', array(
             'fields' => 
             array(
              0 => 'dealer_id',
             ),
             ));
        $this->dropTable('accepted_dealer_activity');
    }
}