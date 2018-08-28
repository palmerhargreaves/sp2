<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ActivityVideoRecordsStatisticsHeadersFieldsData', 'doctrine');

/**
 * BaseActivityVideoRecordsStatisticsHeadersFieldsData
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $field_id
 * @property integer $user_id
 * @property integer $dealer_id
 * @property integer $quarter
 * @property integer $year
 * @property ActivityVideoRecordsStatisticsHeadersFields $ActivityVideoRecordsStatisticsHeadersFields
 * 
 * @method integer                                         getId()                                          Returns the current record's "id" value
 * @method integer                                         getFieldId()                                     Returns the current record's "field_id" value
 * @method integer                                         getUserId()                                      Returns the current record's "user_id" value
 * @method integer                                         getDealerId()                                    Returns the current record's "dealer_id" value
 * @method integer                                         getQuarter()                                     Returns the current record's "quarter" value
 * @method integer                                         getYear()                                        Returns the current record's "year" value
 * @method ActivityVideoRecordsStatisticsHeadersFields     getActivityVideoRecordsStatisticsHeadersFields() Returns the current record's "ActivityVideoRecordsStatisticsHeadersFields" value
 * @method ActivityVideoRecordsStatisticsHeadersFieldsData setId()                                          Sets the current record's "id" value
 * @method ActivityVideoRecordsStatisticsHeadersFieldsData setFieldId()                                     Sets the current record's "field_id" value
 * @method ActivityVideoRecordsStatisticsHeadersFieldsData setUserId()                                      Sets the current record's "user_id" value
 * @method ActivityVideoRecordsStatisticsHeadersFieldsData setDealerId()                                    Sets the current record's "dealer_id" value
 * @method ActivityVideoRecordsStatisticsHeadersFieldsData setQuarter()                                     Sets the current record's "quarter" value
 * @method ActivityVideoRecordsStatisticsHeadersFieldsData setYear()                                        Sets the current record's "year" value
 * @method ActivityVideoRecordsStatisticsHeadersFieldsData setActivityVideoRecordsStatisticsHeadersFields() Sets the current record's "ActivityVideoRecordsStatisticsHeadersFields" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseActivityVideoRecordsStatisticsHeadersFieldsData extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('activity_video_records_statistics_headers_fields_data');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('field_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('user_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('dealer_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('quarter', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('year', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('value', 'string', null, array(
            'type' => 'string',
            'notnull' => true,
            'length' => 255
        ));

        $this->index('field', array(
             'fields' => 
             array(
              0 => 'field_id',
             ),
             ));
        $this->index('user', array(
             'fields' => 
             array(
              0 => 'user_id',
              1 => 'dealer_id',
             ),
             ));
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('ActivityVideoRecordsStatisticsHeadersFields', array(
             'local' => 'field_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}