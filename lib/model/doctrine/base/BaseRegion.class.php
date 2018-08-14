<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Region', 'vw_general');

/**
 * BaseRegion
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $name
 * @property integer $position
 * @property Doctrine_Collection $Cities
 * 
 * @method string              getName()     Returns the current record's "name" value
 * @method integer             getPosition() Returns the current record's "position" value
 * @method Doctrine_Collection getCities()   Returns the current record's "Cities" collection
 * @method Region              setName()     Sets the current record's "name" value
 * @method Region              setPosition() Sets the current record's "position" value
 * @method Region              setCities()   Sets the current record's "Cities" collection
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseRegion extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('regions');
        $this->hasColumn('name', 'string', 60, array(
             'type' => 'string',
             'length' => 60,
             ));
        $this->hasColumn('position', 'integer', null, array(
             'type' => 'integer',
             ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('City as Cities', array(
             'local' => 'id',
             'foreign' => 'region_id'));
    }
}