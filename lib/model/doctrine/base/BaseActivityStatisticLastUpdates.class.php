<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ActivityStatisticLastUpdates', 'doctrine');

/**
 * BaseActivityStatisticLastUpdates
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $activity_id
 * @property integer $dealer_id
 * @property integer $statistic_id
 * 
 * @method integer                      get()             Returns the current record's "id" value
 * @method integer                      get()             Returns the current record's "activity_id" value
 * @method integer                      get()             Returns the current record's "dealer_id" value
 * @method integer                      get()             Returns the current record's "statistic_id" value
 * @method ActivityStatisticLastUpdates set()             Sets the current record's "id" value
 * @method ActivityStatisticLastUpdates set()             Sets the current record's "activity_id" value
 * @method ActivityStatisticLastUpdates set()             Sets the current record's "dealer_id" value
 * @method ActivityStatisticLastUpdates set()             Sets the current record's "statistic_id" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseActivityStatisticLastUpdates extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('activity_statistic_last_updates');
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
        $this->hasColumn('dealer_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'notnull' => true,
             ));
        $this->hasColumn('statistic_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'notnull' => true,
             ));


        $this->index('activity_id', array(
             'fields' => 
             array(
              0 => 'activity_id',
             ),
             ));
        $this->index('statistic_id', array(
             'fields' => 
             array(
              0 => 'statistic_id',
             ),
             ));
        $this->index('dealer_id', array(
             'fields' => 
             array(
              0 => 'dealer_id',
             ),
             ));
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();

        $timestampable0 = new Doctrine_Template_Timestampable(array(
            'updated' => array(
                'disable' => true
            )
        ));
        $this->actAs($timestampable0);
    }
}