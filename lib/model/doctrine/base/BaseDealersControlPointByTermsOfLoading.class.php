<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('DealersControlPointByTermsOfLoading', 'doctrine');

/**
 * BaseDealersControlPointByTermsOfLoading
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $dealer_id
 * @property integer $year
 * @property integer $q1
 * @property integer $q2
 * @property integer $q3
 * @property integer $q4
 * @property integer $user_id
 * 
 * @method integer                             getId()        Returns the current record's "id" value
 * @method integer                             getDealerId()  Returns the current record's "dealer_id" value
 * @method integer                             getYear()      Returns the current record's "year" value
 * @method integer                             getQ1()        Returns the current record's "q1" value
 * @method integer                             getQ2()        Returns the current record's "q2" value
 * @method integer                             getQ3()        Returns the current record's "q3" value
 * @method integer                             getQ4()        Returns the current record's "q4" value
 * @method integer                             getUserId()    Returns the current record's "user_id" value
 * @method DealersControlPointByTermsOfLoading setId()        Sets the current record's "id" value
 * @method DealersControlPointByTermsOfLoading setDealerId()  Sets the current record's "dealer_id" value
 * @method DealersControlPointByTermsOfLoading setYear()      Sets the current record's "year" value
 * @method DealersControlPointByTermsOfLoading setQ1()        Sets the current record's "q1" value
 * @method DealersControlPointByTermsOfLoading setQ2()        Sets the current record's "q2" value
 * @method DealersControlPointByTermsOfLoading setQ3()        Sets the current record's "q3" value
 * @method DealersControlPointByTermsOfLoading setQ4()        Sets the current record's "q4" value
 * @method DealersControlPointByTermsOfLoading setUserId()    Sets the current record's "user_id" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseDealersControlPointByTermsOfLoading extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('dealers_control_point_by_terms_of_loading');
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
        $this->hasColumn('q1', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'notnull' => true,
             ));
        $this->hasColumn('q2', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'notnull' => true,
             ));
        $this->hasColumn('q3', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'notnull' => true,
             ));
        $this->hasColumn('q4', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'notnull' => true,
             ));
        $this->hasColumn('user_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'notnull' => true,
             ));


        $this->index('dealer_point_data', array(
             'fields' => 
             array(
              0 => 'dealer_id',
              1 => 'year',
             ),
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
            'foreign' => 'id',
        ));
    }
}
