<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Dealers_activities_avg_percent', 'doctrine');

/**
 * BaseDealers_activities_avg_pecent
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $dealer_id
 * @property integer $year
 * @property integer $quarter
 * 
 * @method integer                       getId()        Returns the current record's "id" value
 * @method integer                       getDealerId()  Returns the current record's "dealer_id" value
 * @method integer                       getYear()      Returns the current record's "year" value
 * @method integer                       getQuarter()   Returns the current record's "quarter" value
 * @method Dealers_activities_avg_pecent setId()        Sets the current record's "id" value
 * @method Dealers_activities_avg_pecent setDealerId()  Sets the current record's "dealer_id" value
 * @method Dealers_activities_avg_pecent setYear()      Sets the current record's "year" value
 * @method Dealers_activities_avg_pecent setQuarter()   Sets the current record's "quarter" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseDealersActivitiesAvgPercent extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('dealers_activities_avg_percent');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('dealer_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'notnull' => true,
             ));
        $this->hasColumn('year', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'notnull' => true,
             ));
        $this->hasColumn('quarter', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'notnull' => true,
             ));


        $this->index('user', array(
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
        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}