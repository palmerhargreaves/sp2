<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ActivityAcceptStats', 'doctrine');

/**
 * BaseActivityAcceptStats
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $dealer_id
 * @property integer $year
 * @property string $q1
 * @property string $q2
 * @property string $q3
 * @property string $q4
 * 
 * @method integer             getId()        Returns the current record's "id" value
 * @method integer             getDealerId()  Returns the current record's "dealer_id" value
 * @method integer             getYear()      Returns the current record's "year" value
 * @method string              getQ1()        Returns the current record's "q1" value
 * @method string              getQ2()        Returns the current record's "q2" value
 * @method string              getQ3()        Returns the current record's "q3" value
 * @method string              getQ4()        Returns the current record's "q4" value
 * @method ActivityAcceptStats setId()        Sets the current record's "id" value
 * @method ActivityAcceptStats setDealerId()  Sets the current record's "dealer_id" value
 * @method ActivityAcceptStats setYear()      Sets the current record's "year" value
 * @method ActivityAcceptStats setQ1()        Sets the current record's "q1" value
 * @method ActivityAcceptStats setQ2()        Sets the current record's "q2" value
 * @method ActivityAcceptStats setQ3()        Sets the current record's "q3" value
 * @method ActivityAcceptStats setQ4()        Sets the current record's "q4" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseActivityAcceptStats extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('activity_accept_stats');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('dealer_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => false,
             ));
        $this->hasColumn('year', 'integer', null, array(
             'type' => 'integer',
             'notnull' => false,
             ));
        $this->hasColumn('q1', 'string', 80, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 80,
             ));
        $this->hasColumn('q2', 'string', 80, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 80,
             ));
        $this->hasColumn('q3', 'string', 80, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 80,
             ));
        $this->hasColumn('q4', 'string', 80, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 80,
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