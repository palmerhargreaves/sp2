<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ActivityTypeCompanyImages', 'doctrine');

/**
 * BaseActivityTypeCompanyImages
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $activity_id
 * @property integer $company_type_id
 * 
 * @method integer                   getId()              Returns the current record's "id" value
 * @method integer                   getActivityId()      Returns the current record's "activity_id" value
 * @method integer                   getCompanyTypeId()   Returns the current record's "company_type_id" value
 * @method ActivityTypeCompanyImages setId()              Sets the current record's "id" value
 * @method ActivityTypeCompanyImages setActivityId()      Sets the current record's "activity_id" value
 * @method ActivityTypeCompanyImages setCompanyTypeId()   Sets the current record's "company_type_id" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseActivityTypeCompanyImages extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('activity_type_company_images');
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
        $this->hasColumn('company_type_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'notnull' => true,
             ));
        $this->hasColumn('path', 'string', 255, array(
            'type' => 'string',
            'notnull' => true,
            'length' => 255,
        ));


        $this->index('user_model', array(
             'fields' => 
             array(
              0 => 'activity_id',
              1 => 'company_type_id',
             ),
             ));
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        
    }
}
