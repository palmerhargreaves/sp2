<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('MandatoryActivityQuarters', 'doctrine');

/**
 * BaseMandatoryActivityQuarters
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $activity_id
 * @property integer $q
 * @property integer $year
 * 
 * @method integer                   getId()          Returns the current record's "id" value
 * @method integer                   getActivityId()  Returns the current record's "activity_id" value
 * @method integer                   getQ()           Returns the current record's "q" value
 * @method integer                   getYear()        Returns the current record's "year" value
 * @method MandatoryActivityQuarters setId()          Sets the current record's "id" value
 * @method MandatoryActivityQuarters setActivityId()  Sets the current record's "activity_id" value
 * @method MandatoryActivityQuarters setQ()           Sets the current record's "q" value
 * @method MandatoryActivityQuarters setYear()        Sets the current record's "year" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseMandatoryActivityQuarters extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('mandatory_activity_quarters');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('activity_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'notnull' => true,
             ));
        $this->hasColumn('quarters', 'string', 80, array(
             'type' => 'integer',
             'length' => 80,
             'notnull' => true,
             ));
        $this->hasColumn('year', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
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

        $this->hasOne('Activity', array(
            'local' => 'activity_id',
            'foreign' => 'id'));
    }
}
