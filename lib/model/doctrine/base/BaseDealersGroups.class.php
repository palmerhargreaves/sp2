<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('DealersGroups', 'doctrine');

/**
 * BaseDealersGroups
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $header
 * @property string $desription
 * @property boolean $status
 * 
 * @method integer       getId()         Returns the current record's "id" value
 * @method string        getHeader()     Returns the current record's "header" value
 * @method string        getDesription() Returns the current record's "desription" value
 * @method boolean       getStatus()     Returns the current record's "status" value
 * @method DealersGroups setId()         Sets the current record's "id" value
 * @method DealersGroups setHeader()     Sets the current record's "header" value
 * @method DealersGroups setDesription() Sets the current record's "desription" value
 * @method DealersGroups setStatus()     Sets the current record's "status" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseDealersGroups extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('dealers_groups');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('header', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             'notnull' => true,
             ));
        $this->hasColumn('description', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             'notnull' => true,
             ));
        $this->hasColumn('dealer_type', 'enum', null, array(
             'type' => 'enum',
             'values' =>
             array(
              0 => 'nfz',
              1 => 'pkw',
             ),
             'notnull' => true,
             ));
        $this->hasColumn('status', 'boolean', null, array(
             'type' => 'boolean',
             'default' => true,
             'notnull' => true,
             ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $timestampable0 = new Doctrine_Template_Timestampable(array(
             'updated' => 
             array(
              'disabled' => true,
             ),
             ));
        $this->actAs($timestampable0);
    }
}
