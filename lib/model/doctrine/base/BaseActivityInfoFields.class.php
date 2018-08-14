<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ActivityInfoFields', 'doctrine');

/**
 * BaseActivityInfoFields
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $header
 * @property string $image
 * @property enum $type
 * 
 * @method integer            getId()     Returns the current record's "id" value
 * @method string             getHeader() Returns the current record's "header" value
 * @method string             getImage()  Returns the current record's "image" value
 * @method enum               getType()   Returns the current record's "type" value
 * @method ActivityInfoFields setId()     Sets the current record's "id" value
 * @method ActivityInfoFields setHeader() Sets the current record's "header" value
 * @method ActivityInfoFields setImage()  Sets the current record's "image" value
 * @method ActivityInfoFields setType()   Sets the current record's "type" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseActivityInfoFields extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('activity_info_fields');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('header', 'string', 255, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 255,
             ));
        $this->hasColumn('image', 'string', 255, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 255,
             ));
        $this->hasColumn('type', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => 'sym',
              1 => 'dig',
             ),
             'notnull' => true,
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