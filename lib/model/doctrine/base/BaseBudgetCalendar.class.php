<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('BudgetCalendar', 'doctrine');

/**
 * BaseBudgetCalendar
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $year
 * @property integer $quarter
 * @property integer $day
 * 
 * @method integer        getId()      Returns the current record's "id" value
 * @method integer        getYear()    Returns the current record's "year" value
 * @method integer        getQuarter() Returns the current record's "quarter" value
 * @method integer        getDay()     Returns the current record's "day" value
 * @method BudgetCalendar setId()      Sets the current record's "id" value
 * @method BudgetCalendar setYear()    Sets the current record's "year" value
 * @method BudgetCalendar setQuarter() Sets the current record's "quarter" value
 * @method BudgetCalendar setDay()     Sets the current record's "day" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseBudgetCalendar extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('budget_calendar');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('year', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('quarter', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('day', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
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