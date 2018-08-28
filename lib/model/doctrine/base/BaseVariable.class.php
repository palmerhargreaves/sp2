<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Variable', 'doctrine');

/**
 * BaseVariable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property string $value
 * 
 * @method integer  getId()    Returns the current record's "id" value
 * @method string   getName()  Returns the current record's "name" value
 * @method string   getValue() Returns the current record's "value" value
 * @method Variable setId()    Sets the current record's "id" value
 * @method Variable setName()  Sets the current record's "name" value
 * @method Variable setValue() Sets the current record's "value" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseVariable extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('variable');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => 255,
             ));
        $this->hasColumn('value', 'string', 255, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 255,
             ));


        $this->index('name', array(
             'fields' => 
             array(
              0 => 'name',
             ),
             ));
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        
    }
}