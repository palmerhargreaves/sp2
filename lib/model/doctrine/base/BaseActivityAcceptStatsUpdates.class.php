<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ActivityAcceptStatsUpdates', 'doctrine');

/**
 * BaseActivityAcceptStatsUpdates
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $year
 * 
 * @method integer             getId()        Returns the current record's "id" value
 * @method integer             getYear()      Returns the current record's "year" value
 * @method ActivityAcceptStats setId()        Sets the current record's "id" value
 * @method ActivityAcceptStats setYear()      Sets the current record's "year" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseActivityAcceptStatsUpdates extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('activity_accept_stats_updates');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('year', 'integer', null, array(
             'type' => 'integer',
             'notnull' => false,
             ));
        $this->hasColumn('quarter', 'integer', null, array(
            'type' => 'integer',
            'notnull' => false,
            'default' => 0
        ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}
