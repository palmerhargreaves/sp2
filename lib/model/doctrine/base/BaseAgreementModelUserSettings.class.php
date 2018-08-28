<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('AgreementModelUserSettings', 'doctrine');

/**
 * BaseAgreementModelUserSettingsTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $dealer_id
 * @property integer $activity_id
 * @property date $certificate_end
 * @property boolean $is_msg_sended
 * @property boolean $is_blocked
 * 
 * @method integer                         getId()              Returns the current record's "id" value
 * @method integer                         getDealerId()        Returns the current record's "dealer_id" value
 * @method integer                         getActivityId()      Returns the current record's "activity_id" value
 * @method date                            getCertificateEnd()  Returns the current record's "certificate_end" value
 * @method boolean                         getIsMsgSended()     Returns the current record's "is_msg_sended" value
 * @method boolean                         getIsBlocked()       Returns the current record's "is_blocked" value
 * @method AgreementModelUserSettingsTable setId()              Sets the current record's "id" value
 * @method AgreementModelUserSettingsTable setDealerId()        Sets the current record's "dealer_id" value
 * @method AgreementModelUserSettingsTable setActivityId()      Sets the current record's "activity_id" value
 * @method AgreementModelUserSettingsTable setCertificateEnd()  Sets the current record's "certificate_end" value
 * @method AgreementModelUserSettingsTable setIsMsgSended()     Sets the current record's "is_msg_sended" value
 * @method AgreementModelUserSettingsTable setIsBlocked()       Sets the current record's "is_blocked" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseAgreementModelUserSettings extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('agreement_model_user_settings');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('dealer_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('activity_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('model_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('plus_days', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('certificate_end', 'date', null, array(
             'type' => 'date',
             'notnull' => true,
             ));
        $this->hasColumn('is_msg_sended', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => true,
             'default' => false,
             ));
        $this->hasColumn('is_blocked', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => true,
             'default' => false,
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
             'foreign' => 'id'));
        
        $this->hasOne('Activity', array(
             'local' => 'activity_id',
             'foreign' => 'id'));
        
        $this->hasOne('AgreementModel as Model', array(
             'local' => 'model_id',
             'foreign' => 'id'));
    }
}