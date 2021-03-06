<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ActivityFields', 'doctrine');

/**
 * BaseActivityFields
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property enum $type
 * @property integer $activity_id
 * @property Activity $Activity
 * @property Doctrine_Collection $Feild
 * 
 * @method integer             getId()          Returns the current record's "id" value
 * @method string              getName()        Returns the current record's "name" value
 * @method enum                getType()        Returns the current record's "type" value
 * @method integer             getActivityId()  Returns the current record's "activity_id" value
 * @method Activity            getActivity()    Returns the current record's "Activity" value
 * @method Doctrine_Collection getFeild()       Returns the current record's "Feild" collection
 * @method ActivityFields      setId()          Sets the current record's "id" value
 * @method ActivityFields      setName()        Sets the current record's "name" value
 * @method ActivityFields      setType()        Sets the current record's "type" value
 * @method ActivityFields      setActivityId()  Sets the current record's "activity_id" value
 * @method ActivityFields      setActivity()    Sets the current record's "Activity" value
 * @method ActivityFields      setFeild()       Sets the current record's "Feild" collection
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseActivityFields extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('activity_fields');
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
        $this->hasColumn('parent_header_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
        ));
        $this->hasColumn('group_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
        ));
        $this->hasColumn('owner', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
        ));
        $this->hasColumn('description', 'clob', null, array(
            'type' => 'clob',
            'notnull' => false,
        ));
        $this->hasColumn('type', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
                  0 => 'string',
                  1 => 'date',
                  2 => 'number',
                  3 => 'file'
             ),
             'notnull' => true,
             ));
        $this->hasColumn('content', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => 'price',
              1 => 'counts'
             ),
             'notnull' => true,
             ));
        $this->hasColumn('activity_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('position', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
            'default' => 0
        ));
        $this->hasColumn('hash_id', 'string', 255, array(
            'type' => 'string',
            'notnull' => true,
            'length' => 255,
        ));

        $this->hasColumn('req', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => true,
             'default' => false,
             ));

        $this->hasColumn('status', 'boolean', null, array(
            'type' => 'boolean',
            'notnull' => true,
            'default' => false,
        ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');

        $this->setSubClasses(array(
             'ActivityFieldsStringField' => 
             array(
              'type' => 'string',
             ),
             'ActivityFieldsDateField' => 
             array(
              'type' => 'date',
             ),
             'ActivityFieldsNumberField' => 
             array(
              'type' => 'number',
             ),
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Activity', array(
             'local' => 'activity_id',
             'foreign' => 'id'));

        $this->hasMany('ActivityFieldsValues as Field', array(
             'local' => 'id',
             'foreign' => 'field_id'));

        $this->hasOne('ActivityVideoRecordsStatisticsHeaders', array(
            'local' => 'parent_header_id',
            'foreign' => 'id'));

        $this->hasOne('ActivityVideoRecordsStatisticsHeadersGroups', array(
            'local' => 'group_id',
            'foreign' => 'id'));

        $this->hasMany('ActivityVideoRecordsStatisticsHeadersGroupsFields', array(
            'local' => 'id',
            'foreign' => 'group_id'));
    }
}