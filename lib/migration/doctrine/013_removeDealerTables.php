<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class RemoveDealerTables extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->dropTable('city');
        $this->dropTable('dealer');
        $this->dropTable('region');
        $this->dropTable('legal_person');
    }

    public function down()
    {
        $this->createTable('city', array(
             'id' => 
             array(
              'type' => 'integer',
              'length' => '8',
              'autoincrement' => '1',
              'primary' => '1',
             ),
             'name' => 
             array(
              'type' => 'string',
              'length' => '60',
             ),
             'region_id' => 
             array(
              'type' => 'integer',
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
        $this->createTable('dealer', array(
             'id' => 
             array(
              'type' => 'integer',
              'primary' => '1',
              'autoincrement' => '1',
              'length' => '8',
             ),
             'number' => 
             array(
              'type' => 'integer',
              'unique' => '1',
              'length' => '8',
             ),
             'name' => 
             array(
              'type' => 'string',
              'length' => '60',
             ),
             'address' => 
             array(
              'type' => 'string',
              'length' => '255',
             ),
             'phone' => 
             array(
              'type' => 'string',
              'length' => '255',
             ),
             'site' => 
             array(
              'type' => 'string',
              'length' => '128',
             ),
             'email' => 
             array(
              'type' => 'string',
              'length' => '128',
             ),
             'longitude' => 
             array(
              'type' => 'decimal',
              'scale' => '6',
              'length' => '18',
             ),
             'latitude' => 
             array(
              'type' => 'decimal',
              'scale' => '6',
              'length' => '18',
             ),
             'city_id' => 
             array(
              'type' => 'integer',
              'length' => '8',
             ),
             'company_id' => 
             array(
              'type' => 'integer',
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
        $this->createTable('region', array(
             'id' => 
             array(
              'type' => 'integer',
              'length' => '8',
              'autoincrement' => '1',
              'primary' => '1',
             ),
             'name' => 
             array(
              'type' => 'string',
              'length' => '60',
             ),
             'position' => 
             array(
              'type' => 'integer',
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
        
        $this->createForeignKey('city', 'city_region_id_region_id', array(
             'name' => 'city_region_id_region_id',
             'local' => 'region_id',
             'foreign' => 'id',
             'foreignTable' => 'region',
             ));
        $this->createForeignKey('dealer', 'dealer_city_id_city_id', array(
             'name' => 'dealer_city_id_city_id',
             'local' => 'city_id',
             'foreign' => 'id',
             'foreignTable' => 'city',
             ));
        $this->addIndex('city', 'city_region_id', array(
             'fields' => 
             array(
              0 => 'region_id',
             ),
             ));
        $this->addIndex('dealer', 'dealer_city_id', array(
             'fields' => 
             array(
              0 => 'city_id',
             ),
             ));
        $this->createTable('legal_person', array(
             'id' => 
             array(
              'type' => 'integer',
              'length' => '8',
              'autoincrement' => '1',
              'primary' => '1',
             ),
             'name' => 
             array(
              'type' => 'string',
              'notnull' => '1',
              'length' => '255',
             ),
             'legal_address' => 
             array(
              'type' => 'string',
              'notnull' => '1',
              'length' => '255',
             ),
             'inn' => 
             array(
              'type' => 'string',
              'notnull' => '1',
              'length' => '128',
             ),
             'kpp' => 
             array(
              'type' => 'string',
              'notnull' => '1',
              'length' => '128',
             ),
             'okpo' => 
             array(
              'type' => 'string',
              'notnull' => '1',
              'length' => '128',
             ),
             'transactional_account' => 
             array(
              'type' => 'string',
              'notnull' => '1',
              'length' => '128',
             ),
             'correspondent_account' => 
             array(
              'type' => 'string',
              'notnull' => '1',
              'length' => '128',
             ),
             'bik' => 
             array(
              'type' => 'string',
              'notnull' => '1',
              'length' => '128',
             ),
             'bank_name' => 
             array(
              'type' => 'string',
              'notnull' => '1',
              'length' => '255',
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
        $this->createForeignKey('dealer', 'dealer_company_id_legal_person_id', array(
             'name' => 'dealer_company_id_legal_person_id',
             'local' => 'company_id',
             'foreign' => 'id',
             'foreignTable' => 'legal_person',
             ));
        $this->addIndex('dealer', 'dealer_company_id', array(
             'fields' => 
             array(
              0 => 'company_id',
             ),
             ));
    }
}