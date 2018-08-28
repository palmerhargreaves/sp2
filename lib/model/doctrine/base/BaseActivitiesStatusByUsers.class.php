<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ActivitiesStatusByUsers', 'doctrine');

/**
 * BaseActivitiesStatusByUsers
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $dealer_id
 * @property integer $activity_id
 * 
 * @method integer          getId()          Returns the current record's "id" value
 * @method integer          getDealerId()    Returns the current record's "dealer_id" value
 * @method integer          getActivityId()  Returns the current record's "activity_id" value
 * @method integer          getUserId()      Returns the current record's "user_id" value
 * @method integer          getByYear()      Returns the current record's "by_year" value
 * @method integer          getByQuarter()   Returns the current record's "by_quarter" value
 * @method ActivitiesStatus setId()          Sets the current record's "id" value
 * @method ActivitiesStatus setDealerId()    Sets the current record's "dealer_id" value
 * @method ActivitiesStatus setActivityId()  Sets the current record's "activity_id" value
 * @method ActivitiesStatus setUserId()      Sets the current record's "user_id" value
 * @method ActivitiesStatus setByYear()      Sets the current record's "by_year" value
 * @method ActivitiesStatus setByQuarter()   Sets the current record's "by_quarter" value
 *
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseActivitiesStatusByUsers extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('activities_status_by_users');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('dealer_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('activity_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('by_year', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
        ));
        $this->hasColumn('by_quarter', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
        ));
        $this->hasColumn('user_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
        ));
        $this->hasColumn('completed_date', 'string', null, array(
            'type' => 'string',
            'notnull' => true,
        ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
 
        $this->hasOne('Dealer', array(
             'local' => 'dealer_id',
             'foreign' => 'id'));       

        $this->hasOne('Activity', array(
             'local' => 'activity_id',
             'foreign' => 'id'));

        $this->hasOne('User', array(
            'local' => 'user_id',
            'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}