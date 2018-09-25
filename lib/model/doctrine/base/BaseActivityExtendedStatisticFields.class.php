<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ActivityExtendedStatisticFields', 'doctrine');

/**
 * BaseActivityExtendedStatisticFields
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @property integer $id
 * @property string $header
 * @property string $description
 * @property integer $activity_id
 * @property integer $status
 * @property enum $value_type
 *
 * @method integer                         getId()          Returns the current record's "id" value
 * @method string                          getHeader()      Returns the current record's "header" value
 * @method string                          getDescription() Returns the current record's "description" value
 * @method integer                         getActivityId()  Returns the current record's "activity_id" value
 * @method integer                         getStatus()      Returns the current record's "status" value
 * @method enum                            getValueType()   Returns the current record's "value_type" value
 * @method ActivityExtendedStatisticFields setId()          Sets the current record's "id" value
 * @method ActivityExtendedStatisticFields setHeader()      Sets the current record's "header" value
 * @method ActivityExtendedStatisticFields setDescription() Sets the current record's "description" value
 * @method ActivityExtendedStatisticFields setActivityId()  Sets the current record's "activity_id" value
 * @method ActivityExtendedStatisticFields setStatus()      Sets the current record's "status" value
 * @method ActivityExtendedStatisticFields setValueType()   Sets the current record's "value_type" value
 *
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseActivityExtendedStatisticFields extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('activity_extended_statistic_fields');
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
        $this->hasColumn('description', 'clob', null, array(
            'type' => 'clob',
            'notnull' => false,
        ));
        $this->hasColumn('activity_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => false,
        ));
        $this->hasColumn('parent_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => false,
        ));
        $this->hasColumn('step_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => false,
        ));
        $this->hasColumn('status', 'integer', null, array(
            'type' => 'integer',
            'notnull' => false,
        ));
        $this->hasColumn('value_type', 'enum', null, array(
            'type' => 'enum',
            'values' =>
                array(
                    0 => 'date',
                    1 => 'dig',
                    2 => 'calc',
                    3 => 'text',
                    4 => 'any',
                    5 => 'file'
                ),
            'notnull' => false,
        ));
        $this->hasColumn('position', 'integer', null, array(
            'type' => 'integer',
            'notnull' => false,
        ));
        $this->hasColumn('required', 'boolean', null, array(
            'type' => 'boolean',
            'notnull' => true,
            'default' => true,
        ));

        $this->hasColumn('editable', 'boolean', null, array(
            'type' => 'boolean',
            'notnull' => true,
            'default' => true,
        ));

        $this->hasColumn('def_value', 'string', null, array(
            'type' => 'string',
            'notnull' => true,
            'default' => true,
        ));

        $this->hasColumn('dealers_group', 'string', null, array(
            'type' => 'string',
            'notnull' => true,
            'default' => true,
        ));

        $this->hasColumn('show_in_export', 'boolean', null, array(
            'type' => 'boolean',
            'notnull' => true,
            'default' => true,
        ));

        $this->hasColumn('show_in_statistic', 'boolean', null, array(
            'type' => 'boolean',
            'notnull' => true,
            'default' => true,
        ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();

        $this->hasOne('Activity', array(
            'local' => 'activity_id',
            'foreign' => 'id'));

        $this->hasOne('ActivityExtendedStatisticSections as Section', array(
            'local' => 'parent_id',
            'foreign' => 'id'));

    }
}
