<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('LogAgreementModelBlocked', 'doctrine');

/**
 * BaseLogAgreementModelBlocked
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $object_id
 * @property integer $user_id
 * @property integer $dealer_id
 * @property string $action
 * @property string $description
 * 
 * @method integer                  getId()          Returns the current record's "id" value
 * @method integer                  getObjectId()    Returns the current record's "object_id" value
 * @method integer                  getUserId()      Returns the current record's "user_id" value
 * @method integer                  getDealerId()    Returns the current record's "dealer_id" value
 * @method string                   getAction()      Returns the current record's "action" value
 * @method string                   getDescription() Returns the current record's "description" value
 * @method LogAgreementModelBlocked setId()          Sets the current record's "id" value
 * @method LogAgreementModelBlocked setObjectId()    Sets the current record's "object_id" value
 * @method LogAgreementModelBlocked setUserId()      Sets the current record's "user_id" value
 * @method LogAgreementModelBlocked setDealerId()    Sets the current record's "dealer_id" value
 * @method LogAgreementModelBlocked setAction()      Sets the current record's "action" value
 * @method LogAgreementModelBlocked setDescription() Sets the current record's "description" value
 * @property  $
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseLogAgreementModelBlocked extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('log_agreement_model_blocked');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('object_id', 'integer', null, array(
             'type' => 'integer',
             'default' => 0,
             'notnull' => false,
             ));
        $this->hasColumn('user_id', 'integer', null, array(
             'type' => 'integer',
             'default' => 0,
             'notnull' => false,
             ));
        $this->hasColumn('dealer_id', 'integer', null, array(
             'type' => 'integer',
             'default' => 0,
             'notnull' => false,
             ));
        $this->hasColumn('action', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             'notnull' => false,
             ));
        $this->hasColumn('description', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             'notnull' => false,
             ));


        $this->index('main', array(
             'fields' => 
             array(
              0 => 'object_id',
              1 => 'user_id',
              2 => 'dealer_id',
             ),
             ));
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('AgreementModel', array(
             'local' => 'object_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable(array(
             'updated' => 
             array(
              'disabled' => true,
             ),
             ));
        $this->actAs($timestampable0);
    }
}