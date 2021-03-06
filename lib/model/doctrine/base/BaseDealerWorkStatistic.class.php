<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('DealerWorkStatistic', 'doctrine');

/**
 * BaseDealerWorkStatistic
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $dealer_id
 * @property integer $year
 * @property float $q1
 * @property float $q2
 * @property float $q3
 * @property float $q4
 * @property decimal $total_sum
 * @property integer $models_complete
 * @property integer $activity_complete
 * 
 * @method integer             getId()                Returns the current record's "id" value
 * @method integer             getDealerId()          Returns the current record's "dealer_id" value
 * @method integer             getYear()              Returns the current record's "year" value
 * @method float               getQ1()                Returns the current record's "q1" value
 * @method float               getQ2()                Returns the current record's "q2" value
 * @method float               getQ3()                Returns the current record's "q3" value
 * @method float               getQ4()                Returns the current record's "q4" value
 * @method decimal             getTotalSum()          Returns the current record's "total_sum" value
 * @method integer             getModelsComplete()    Returns the current record's "models_complete" value
 * @method integer             getActivityComplete()  Returns the current record's "activity_complete" value
 * @method DealerWorkStatistic setId()                Sets the current record's "id" value
 * @method DealerWorkStatistic setDealerId()          Sets the current record's "dealer_id" value
 * @method DealerWorkStatistic setYear()              Sets the current record's "year" value
 * @method DealerWorkStatistic setQ1()                Sets the current record's "q1" value
 * @method DealerWorkStatistic setQ2()                Sets the current record's "q2" value
 * @method DealerWorkStatistic setQ3()                Sets the current record's "q3" value
 * @method DealerWorkStatistic setQ4()                Sets the current record's "q4" value
 * @method DealerWorkStatistic setTotalSum()          Sets the current record's "total_sum" value
 * @method DealerWorkStatistic setModelsComplete()    Sets the current record's "models_complete" value
 * @method DealerWorkStatistic setActivityComplete()  Sets the current record's "activity_complete" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseDealerWorkStatistic extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('dealer_work_statistic');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('dealer_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => false,
             ));
        $this->hasColumn('year', 'float', null, array(
             'type' => 'float',
             'notnull' => true,
             ));
        $this->hasColumn('calc_year', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
        ));
        $this->hasColumn('q1', 'float', null, array(
             'type' => 'float',
             'notnull' => true,
             'default' => 0,
             ));
        $this->hasColumn('q2', 'float', null, array(
             'type' => 'float',
             'notnull' => true,
             'default' => 0,
             ));
        $this->hasColumn('q3', 'float', null, array(
             'type' => 'float',
             'notnull' => true,
             'default' => 0,
             ));
        $this->hasColumn('q4', 'float', null, array(
             'type' => 'float',
             'notnull' => true,
             ));
        $this->hasColumn('total_sum', 'decimal', null, array(
             'type' => 'decimal',
             'scale' => 2,
             'notnull' => true,
             'default' => 0,
             ));
        $this->hasColumn('models_complete', 'integer', null, array(
             'type' => 'integer',
             'notnull' => false,
             ));
        $this->hasColumn('activity_complete', 'integer', null, array(
             'type' => 'integer',
             'notnull' => false,
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