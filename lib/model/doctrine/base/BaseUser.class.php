<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('User', 'doctrine');

/**
 * BaseUser
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @property integer $id
 * @property integer $group_id
 * @property string $email
 * @property string $password
 * @property string $name
 * @property string $surname
 * @property string $patronymic
 * @property enum $company_type
 * @property string $company_name
 * @property string $post
 * @property string $phone
 * @property string $mobile
 * @property string $recovery_key
 * @property string $activation_key
 * @property boolean $registration_notification
 * @property boolean $agreement_notification
 * @property boolean $new_agreement_notification
 * @property boolean $final_agreement_notification
 * @property boolean $agreement_report_notification
 * @property boolean $new_agreement_report_notification
 * @property boolean $final_agreement_report_notification
 * @property boolean $agreement_concept_notification
 * @property boolean $new_agreement_concept_notification
 * @property boolean $final_agreement_concept_notification
 * @property boolean $agreement_concept_report_notification
 * @property boolean $new_agreement_concept_report_notification
 * @property boolean $final_agreement_concept_report_notification
 * @property boolean $dealer_discussion_notification
 * @property boolean $model_discussion_notification
 * @property boolean $active
 * @property UserGroup $Group
 * @property Doctrine_Collection $DealerUsers
 * @property Doctrine_Collection $DiscussionLastReads
 * @property Doctrine_Collection $LogLastReads
 * @property Doctrine_Collection $LogEntryReads
 * @property Doctrine_Collection $TempFiles
 * @property Doctrine_Collection $DiscussionOnline
 * @property Doctrine_Collection $AgreementModelComments
 * @property Doctrine_Collection $AgreementModelReportComments
 * @property Doctrine_Collection $PrivateMesages
 * @property Doctrine_Collection $LogEntries
 * @property Doctrine_Collection $PrivateLogEntries
 * @property Doctrine_Collection $MaterialViews
 * @property Doctrine_Collection $ActivityViews
 * @property Doctrine_Collection $Messages
 *
 * @method integer             getId()                                          Returns the current record's "id" value
 * @method integer             getGroupId()                                     Returns the current record's "group_id" value
 * @method string              getEmail()                                       Returns the current record's "email" value
 * @method string              getPassword()                                    Returns the current record's "password" value
 * @method string              getName()                                        Returns the current record's "name" value
 * @method string              getSurname()                                     Returns the current record's "surname" value
 * @method string              getPatronymic()                                  Returns the current record's "patronymic" value
 * @method enum                getCompanyType()                                 Returns the current record's "company_type" value
 * @method string              getCompanyName()                                 Returns the current record's "company_name" value
 * @method string              getPost()                                        Returns the current record's "post" value
 * @method string              getPhone()                                       Returns the current record's "phone" value
 * @method string              getMobile()                                      Returns the current record's "mobile" value
 * @method string              getRecoveryKey()                                 Returns the current record's "recovery_key" value
 * @method string              getActivationKey()                               Returns the current record's "activation_key" value
 * @method boolean             getRegistrationNotification()                    Returns the current record's "registration_notification" value
 * @method boolean             getAgreementNotification()                       Returns the current record's "agreement_notification" value
 * @method boolean             getNewAgreementNotification()                    Returns the current record's "new_agreement_notification" value
 * @method boolean             getFinalAgreementNotification()                  Returns the current record's "final_agreement_notification" value
 * @method boolean             getAgreementReportNotification()                 Returns the current record's "agreement_report_notification" value
 * @method boolean             getNewAgreementReportNotification()              Returns the current record's "new_agreement_report_notification" value
 * @method boolean             getFinalAgreementReportNotification()            Returns the current record's "final_agreement_report_notification" value
 * @method boolean             getAgreementConceptNotification()                Returns the current record's "agreement_concept_notification" value
 * @method boolean             getNewAgreementConceptNotification()             Returns the current record's "new_agreement_concept_notification" value
 * @method boolean             getFinalAgreementConceptNotification()           Returns the current record's "final_agreement_concept_notification" value
 * @method boolean             getAgreementConceptReportNotification()          Returns the current record's "agreement_concept_report_notification" value
 * @method boolean             getNewAgreementConceptReportNotification()       Returns the current record's "new_agreement_concept_report_notification" value
 * @method boolean             getFinalAgreementConceptReportNotification()     Returns the current record's "final_agreement_concept_report_notification" value
 * @method boolean             getDealerDiscussionNotification()                Returns the current record's "dealer_discussion_notification" value
 * @method boolean             getModelDiscussionNotification()                 Returns the current record's "model_discussion_notification" value
 * @method boolean             getActive()                                      Returns the current record's "active" value
 * @method UserGroup           getGroup()                                       Returns the current record's "Group" value
 * @method Doctrine_Collection getDealerUsers()                                 Returns the current record's "DealerUsers" collection
 * @method Doctrine_Collection getDiscussionLastReads()                         Returns the current record's "DiscussionLastReads" collection
 * @method Doctrine_Collection getLogLastReads()                                Returns the current record's "LogLastReads" collection
 * @method Doctrine_Collection getLogEntryReads()                               Returns the current record's "LogEntryReads" collection
 * @method Doctrine_Collection getTempFiles()                                   Returns the current record's "TempFiles" collection
 * @method Doctrine_Collection getDiscussionOnline()                            Returns the current record's "DiscussionOnline" collection
 * @method Doctrine_Collection getAgreementModelComments()                      Returns the current record's "AgreementModelComments" collection
 * @method Doctrine_Collection getAgreementModelReportComments()                Returns the current record's "AgreementModelReportComments" collection
 * @method Doctrine_Collection getPrivateMesages()                              Returns the current record's "PrivateMesages" collection
 * @method Doctrine_Collection getLogEntries()                                  Returns the current record's "LogEntries" collection
 * @method Doctrine_Collection getPrivateLogEntries()                           Returns the current record's "PrivateLogEntries" collection
 * @method Doctrine_Collection getMaterialViews()                               Returns the current record's "MaterialViews" collection
 * @method Doctrine_Collection getActivityViews()                               Returns the current record's "ActivityViews" collection
 * @method Doctrine_Collection getMessages()                                    Returns the current record's "Messages" collection
 * @method User                setId()                                          Sets the current record's "id" value
 * @method User                setGroupId()                                     Sets the current record's "group_id" value
 * @method User                setEmail()                                       Sets the current record's "email" value
 * @method User                setPassword()                                    Sets the current record's "password" value
 * @method User                setName()                                        Sets the current record's "name" value
 * @method User                setSurname()                                     Sets the current record's "surname" value
 * @method User                setPatronymic()                                  Sets the current record's "patronymic" value
 * @method User                setCompanyType()                                 Sets the current record's "company_type" value
 * @method User                setCompanyName()                                 Sets the current record's "company_name" value
 * @method User                setPost()                                        Sets the current record's "post" value
 * @method User                setPhone()                                       Sets the current record's "phone" value
 * @method User                setMobile()                                      Sets the current record's "mobile" value
 * @method User                setRecoveryKey()                                 Sets the current record's "recovery_key" value
 * @method User                setActivationKey()                               Sets the current record's "activation_key" value
 * @method User                setRegistrationNotification()                    Sets the current record's "registration_notification" value
 * @method User                setAgreementNotification()                       Sets the current record's "agreement_notification" value
 * @method User                setNewAgreementNotification()                    Sets the current record's "new_agreement_notification" value
 * @method User                setFinalAgreementNotification()                  Sets the current record's "final_agreement_notification" value
 * @method User                setAgreementReportNotification()                 Sets the current record's "agreement_report_notification" value
 * @method User                setNewAgreementReportNotification()              Sets the current record's "new_agreement_report_notification" value
 * @method User                setFinalAgreementReportNotification()            Sets the current record's "final_agreement_report_notification" value
 * @method User                setAgreementConceptNotification()                Sets the current record's "agreement_concept_notification" value
 * @method User                setNewAgreementConceptNotification()             Sets the current record's "new_agreement_concept_notification" value
 * @method User                setFinalAgreementConceptNotification()           Sets the current record's "final_agreement_concept_notification" value
 * @method User                setAgreementConceptReportNotification()          Sets the current record's "agreement_concept_report_notification" value
 * @method User                setNewAgreementConceptReportNotification()       Sets the current record's "new_agreement_concept_report_notification" value
 * @method User                setFinalAgreementConceptReportNotification()     Sets the current record's "final_agreement_concept_report_notification" value
 * @method User                setDealerDiscussionNotification()                Sets the current record's "dealer_discussion_notification" value
 * @method User                setModelDiscussionNotification()                 Sets the current record's "model_discussion_notification" value
 * @method User                setActive()                                      Sets the current record's "active" value
 * @method User                setGroup()                                       Sets the current record's "Group" value
 * @method User                setDealerUsers()                                 Sets the current record's "DealerUsers" collection
 * @method User                setDiscussionLastReads()                         Sets the current record's "DiscussionLastReads" collection
 * @method User                setLogLastReads()                                Sets the current record's "LogLastReads" collection
 * @method User                setLogEntryReads()                               Sets the current record's "LogEntryReads" collection
 * @method User                setTempFiles()                                   Sets the current record's "TempFiles" collection
 * @method User                setDiscussionOnline()                            Sets the current record's "DiscussionOnline" collection
 * @method User                setAgreementModelComments()                      Sets the current record's "AgreementModelComments" collection
 * @method User                setAgreementModelReportComments()                Sets the current record's "AgreementModelReportComments" collection
 * @method User                setPrivateMesages()                              Sets the current record's "PrivateMesages" collection
 * @method User                setLogEntries()                                  Sets the current record's "LogEntries" collection
 * @method User                setPrivateLogEntries()                           Sets the current record's "PrivateLogEntries" collection
 * @method User                setMaterialViews()                               Sets the current record's "MaterialViews" collection
 * @method User                setActivityViews()                               Sets the current record's "ActivityViews" collection
 * @method User                setMessages()                                    Sets the current record's "Messages" collection
 *
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseUser extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('user');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true,
        ));
        $this->hasColumn('group_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => false,
        ));
        $this->hasColumn('email', 'string', 255, array(
            'type' => 'string',
            'notnull' => true,
            'unique' => true,
            'length' => 255,
        ));
        $this->hasColumn('password', 'string', 255, array(
            'type' => 'string',
            'notnull' => true,
            'length' => 255,
        ));
        $this->hasColumn('name', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));
        $this->hasColumn('surname', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));
        $this->hasColumn('patronymic', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));
        $this->hasColumn('company_type', 'enum', null, array(
            'type' => 'enum',
            'values' =>
                array(
                    0 => 'dealer',
                    1 => 'importer',
                    2 => 'regional_manager',
                    3 => 'other',
                ),
            'notnull' => true,
        ));
        $this->hasColumn('company_name', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));
        $this->hasColumn('company_department', 'integer', 11, array(
            'type' => 'integer',
            'notnull' => false,
            'default' => 0,
            'length' => 11,
        ));
        $this->hasColumn('post', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));
        $this->hasColumn('phone', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));
        $this->hasColumn('mobile', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));
        $this->hasColumn('recovery_key', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));
        $this->hasColumn('activation_key', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));
        $this->hasColumn('registration_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('agreement_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('new_agreement_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('final_agreement_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('agreement_report_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('new_agreement_report_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('final_agreement_report_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('agreement_concept_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('new_agreement_concept_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('final_agreement_concept_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('agreement_concept_report_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('new_agreement_concept_report_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('final_agreement_concept_report_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('allow_to_get_dealers_messages', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('dealer_discussion_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('model_discussion_notification', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));
        $this->hasColumn('active', 'boolean', null, array(
            'type' => 'boolean',
            'default' => true,
            'notnull' => true,
        ));
        $this->hasColumn('allow_receive_mails', 'boolean', null, array(
            'type' => 'boolean',
            'default' => true,
            'notnull' => true,
        ));

        /**
         * Special budget
         */
        $this->hasColumn('special_budget_quater', 'float', null, array(
            'type' => 'float',
            'default' => 0,
            'notnull' => false,
        ));

        $this->hasColumn('special_budget_summ', 'float', null, array(
            'type' => 'float',
            'default' => 0,
            'notnull' => false,
        ));

        $this->hasColumn('special_budget_date_of', 'string', null, array(
            'type' => 'string',
            'length' => 30,
            'notnull' => false,
        ));

        $this->hasColumn('special_budget_status', 'integer', null, array(
            'type' => 'integer',
            'default' => -1,
            'notnull' => false,
        ));

        $this->hasColumn('summer_action_start_date', 'string', null, array(
            'type' => 'string',
            'length' => 30,
            'notnull' => false,
        ));

        $this->hasColumn('summer_action_end_date', 'string', null, array(
            'type' => 'string',
            'length' => 30,
            'notnull' => false,
        ));

        $this->hasColumn('summer_service_action_start_date', 'string', null, array(
            'type' => 'string',
            'length' => 30,
            'notnull' => false,
        ));

        $this->hasColumn('summer_service_action_end_date', 'string', null, array(
            'type' => 'string',
            'length' => 30,
            'notnull' => false,
        ));

        $this->hasColumn('prod_of_year3', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));

        $this->hasColumn('is_first_login', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));

        $this->hasColumn('natural_person_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => false,
            'default' => 0
        ));

        $this->hasColumn('is_default_specialist', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));

        $this->hasColumn('allow_to_receive_messages_in_chat', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));

        $this->hasColumn('approve_by_email', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));

        $this->hasColumn('approve_receive_email', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));

        $this->hasColumn('foreign_account', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));

        $this->index('email', array(
            'fields' =>
                array(
                    0 => 'email',
                ),
        ));
        $this->index('agreement_notification', array(
            'fields' =>
                array(
                    0 => 'agreement_notification',
                ),
        ));
        $this->index('new_agreement_notification', array(
            'fields' =>
                array(
                    0 => 'new_agreement_notification',
                ),
        ));
        $this->index('final_agreement_notification', array(
            'fields' =>
                array(
                    0 => 'final_agreement_notification',
                ),
        ));
        $this->index('agreement_report_notification', array(
            'fields' =>
                array(
                    0 => 'agreement_report_notification',
                ),
        ));
        $this->index('new_agreement_report_notification', array(
            'fields' =>
                array(
                    0 => 'new_agreement_report_notification',
                ),
        ));
        $this->index('final_agreement_report_notification', array(
            'fields' =>
                array(
                    0 => 'final_agreement_report_notification',
                ),
        ));
        $this->index('agreement_concept_notification', array(
            'fields' =>
                array(
                    0 => 'agreement_concept_notification',
                ),
        ));
        $this->index('new_agreement_concept_notification', array(
            'fields' =>
                array(
                    0 => 'new_agreement_concept_report_notification',
                ),
        ));
        $this->index('final_agreement_concept_notification', array(
            'fields' =>
                array(
                    0 => 'final_agreement_concept_report_notification',
                ),
        ));
        $this->index('agreement_concept_report_notification', array(
            'fields' =>
                array(
                    0 => 'agreement_concept_report_notification',
                ),
        ));
        $this->index('dealer_discussion_notification', array(
            'fields' =>
                array(
                    0 => 'dealer_discussion_notification',
                ),
        ));
        $this->index('model_discussion_notification', array(
            'fields' =>
                array(
                    0 => 'model_discussion_notification',
                ),
        ));
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('UserGroup as Group', array(
            'local' => 'group_id',
            'foreign' => 'id'));

        $this->hasMany('DealerUser as DealerUsers', array(
            'local' => 'id',
            'foreign' => 'user_id',
            'cascade' => array(
                0 => 'delete',
            )));

        $this->hasMany('DiscussionLastRead as DiscussionLastReads', array(
            'local' => 'id',
            'foreign' => 'user_id',
            'cascade' => array(
                0 => 'delete',
            )));

        $this->hasMany('LogLastRead as LogLastReads', array(
            'local' => 'id',
            'foreign' => 'user_id',
            'cascade' => array(
                0 => 'delete',
            )));

        $this->hasMany('LogEntryRead as LogEntryReads', array(
            'local' => 'id',
            'foreign' => 'user_id',
            'cascade' => array(
                0 => 'delete',
            )));

        $this->hasMany('TempFile as TempFiles', array(
            'local' => 'id',
            'foreign' => 'user_id',
            'cascade' => array(
                0 => 'delete',
            )));

        $this->hasMany('DiscussionOnline', array(
            'local' => 'id',
            'foreign' => 'user_id',
            'cascade' => array(
                0 => 'delete',
            )));

        $this->hasMany('AgreementModelComment as AgreementModelComments', array(
            'local' => 'id',
            'foreign' => 'user_id',
            'cascade' => array(
                0 => 'delete',
            )));

        $this->hasMany('AgreementModelReportComment as AgreementModelReportComments', array(
            'local' => 'id',
            'foreign' => 'user_id',
            'cascade' => array(
                0 => 'delete',
            )));

        $this->hasMany('Message as PrivateMesages', array(
            'local' => 'id',
            'foreign' => 'private_user_id',
            'cascade' => array(
                0 => 'delete',
            )));

        $this->hasMany('LogEntry as LogEntries', array(
            'local' => 'id',
            'foreign' => 'user_id'));

        $this->hasMany('LogEntry as PrivateLogEntries', array(
            'local' => 'id',
            'foreign' => 'private_user_id'));

        $this->hasMany('MaterialUserView as MaterialViews', array(
            'local' => 'id',
            'foreign' => 'user_id'));

        $this->hasMany('ActivityUserView as ActivityViews', array(
            'local' => 'id',
            'foreign' => 'user_id'));

        $this->hasMany('Message as Messages', array(
            'local' => 'id',
            'foreign' => 'user_id'));

        /*$this->hasMany('UserDealers as AllowedDealers', array(
             'local' => 'id',
             'foreign' => 'user_id'));*/

        $this->hasMany('Dealer as AllowedDealers', array(
            'refClass' => 'UserDealers',
            'local' => 'user_id',
            'foreign' => 'dealer_id'));

        $this->hasOne('UsersDepartments as Department', array(
            'local' => 'company_department',
            'foreign' => 'id'
        ));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}