<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ActivityFile', 'doctrine');

/**
 * BaseActivityFile
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property integer $activity_id
 * @property string $file
 * @property Activity $Activity
 * 
 * @method integer      getId()          Returns the current record's "id" value
 * @method string       getName()        Returns the current record's "name" value
 * @method integer      getActivityId()  Returns the current record's "activity_id" value
 * @method string       getFile()        Returns the current record's "file" value
 * @method Activity     getActivity()    Returns the current record's "Activity" value
 * @method ActivityFile setId()          Sets the current record's "id" value
 * @method ActivityFile setName()        Sets the current record's "name" value
 * @method ActivityFile setActivityId()  Sets the current record's "activity_id" value
 * @method ActivityFile setFile()        Sets the current record's "file" value
 * @method ActivityFile setActivity()    Sets the current record's "Activity" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseActivityFile extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('activity_file');
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
        $this->hasColumn('activity_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('file', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => 255,
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

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}