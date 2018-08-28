<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddDealerDiscussion extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createTable('dealer_discussion', array(
             'id' => 
             array(
              'type' => 'integer',
              'primary' => '1',
              'autoincrement' => '1',
              'length' => '8',
             ),
             'dealer_id' => 
             array(
              'type' => 'integer',
              'notnull' => '1',
              'length' => '8',
             ),
             'discussion_id' => 
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
        $this->createForeignKey('dealer_discussion', 'dealer_discussion_dealer_id_dealers_id', array(
             'name' => 'dealer_discussion_dealer_id_dealers_id',
             'local' => 'dealer_id',
             'foreign' => 'id',
             'foreignTable' => 'dealers',
             ));
        $this->createForeignKey('dealer_discussion', 'dealer_discussion_dealer_id_discussion_id', array(
             'name' => 'dealer_discussion_dealer_id_discussion_id',
             'local' => 'dealer_id',
             'foreign' => 'id',
             'foreignTable' => 'discussion',
             ));
        $this->addIndex('dealer_discussion', 'dealer_discussion_dealer_id', array(
             'fields' => 
             array(
              0 => 'dealer_id',
             ),
             ));
    }

    public function down()
    {
        $this->dropForeignKey('dealer_discussion', 'dealer_discussion_dealer_id_dealers_id');
        $this->dropForeignKey('dealer_discussion', 'dealer_discussion_dealer_id_discussion_id');
        $this->removeIndex('dealer_discussion', 'dealer_discussion_dealer_id', array(
             'fields' => 
             array(
              0 => 'dealer_id',
             ),
             ));
        $this->dropTable('dealer_discussion');
    }
}