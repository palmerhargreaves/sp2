<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Dealer', 'vw_general');

/**
 * BaseDealer
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $number
 * @property string $name
 * @property string $address
 * @property string $phone
 * @property string $site
 * @property string $email
 * @property decimal $longitude
 * @property decimal $latitude
 * @property integer $city_id
 * @property integer $regional_manager_id
 * @property integer $company_id
 * @property integer $importer_id
 * @property City $City
 * @property LegalPerson $LegalPerson
 * @property NaturalPerson $RegionalManager
 * @property Doctrine_Collection $LogEntries
 * @property Doctrine_Collection $TaskResults
 * @property Doctrine_Collection $AcceptedDealerActivities
 * @property Doctrine_Collection $DealerUsers
 * @property Doctrine_Collection $Budget
 * @property Doctrine_Collection $RealBudget
 * @property Doctrine_Collection $RealTotalBudget
 * @property Doctrine_Collection $Bonuses
 * @property Doctrine_Collection $Discussions
 * @property Doctrine_Collection $AgreementModels
 * 
 * @method integer             getId()                       Returns the current record's "id" value
 * @method integer             getNumber()                   Returns the current record's "number" value
 * @method string              getName()                     Returns the current record's "name" value
 * @method string              getAddress()                  Returns the current record's "address" value
 * @method string              getPhone()                    Returns the current record's "phone" value
 * @method string              getSite()                     Returns the current record's "site" value
 * @method string              getEmail()                    Returns the current record's "email" value
 * @method decimal             getLongitude()                Returns the current record's "longitude" value
 * @method decimal             getLatitude()                 Returns the current record's "latitude" value
 * @method integer             getCityId()                   Returns the current record's "city_id" value
 * @method integer             getRegionalManagerId()        Returns the current record's "regional_manager_id" value
 * @method integer             getCompanyId()                Returns the current record's "company_id" value
 * @method integer             getImporterId()               Returns the current record's "importer_id" value
 * @method City                getCity()                     Returns the current record's "City" value
 * @method LegalPerson         getLegalPerson()              Returns the current record's "LegalPerson" value
 * @method NaturalPerson       getRegionalManager()          Returns the current record's "RegionalManager" value
 * @method Doctrine_Collection getLogEntries()               Returns the current record's "LogEntries" collection
 * @method Doctrine_Collection getTaskResults()              Returns the current record's "TaskResults" collection
 * @method Doctrine_Collection getAcceptedDealerActivities() Returns the current record's "AcceptedDealerActivities" collection
 * @method Doctrine_Collection getDealerUsers()              Returns the current record's "DealerUsers" collection
 * @method Doctrine_Collection getBudget()                   Returns the current record's "Budget" collection
 * @method Doctrine_Collection getRealBudget()               Returns the current record's "RealBudget" collection
 * @method Doctrine_Collection getRealTotalBudget()          Returns the current record's "RealTotalBudget" collection
 * @method Doctrine_Collection getBonuses()                  Returns the current record's "Bonuses" collection
 * @method Doctrine_Collection getDiscussions()              Returns the current record's "Discussions" collection
 * @method Doctrine_Collection getAgreementModels()          Returns the current record's "AgreementModels" collection
 * @method Dealer              setId()                       Sets the current record's "id" value
 * @method Dealer              setNumber()                   Sets the current record's "number" value
 * @method Dealer              setName()                     Sets the current record's "name" value
 * @method Dealer              setAddress()                  Sets the current record's "address" value
 * @method Dealer              setPhone()                    Sets the current record's "phone" value
 * @method Dealer              setSite()                     Sets the current record's "site" value
 * @method Dealer              setEmail()                    Sets the current record's "email" value
 * @method Dealer              setLongitude()                Sets the current record's "longitude" value
 * @method Dealer              setLatitude()                 Sets the current record's "latitude" value
 * @method Dealer              setCityId()                   Sets the current record's "city_id" value
 * @method Dealer              setRegionalManagerId()        Sets the current record's "regional_manager_id" value
 * @method Dealer              setCompanyId()                Sets the current record's "company_id" value
 * @method Dealer              setImporterId()               Sets the current record's "importer_id" value
 * @method Dealer              setCity()                     Sets the current record's "City" value
 * @method Dealer              setLegalPerson()              Sets the current record's "LegalPerson" value
 * @method Dealer              setRegionalManager()          Sets the current record's "RegionalManager" value
 * @method Dealer              setLogEntries()               Sets the current record's "LogEntries" collection
 * @method Dealer              setTaskResults()              Sets the current record's "TaskResults" collection
 * @method Dealer              setAcceptedDealerActivities() Sets the current record's "AcceptedDealerActivities" collection
 * @method Dealer              setDealerUsers()              Sets the current record's "DealerUsers" collection
 * @method Dealer              setBudget()                   Sets the current record's "Budget" collection
 * @method Dealer              setRealBudget()               Sets the current record's "RealBudget" collection
 * @method Dealer              setRealTotalBudget()          Sets the current record's "RealTotalBudget" collection
 * @method Dealer              setBonuses()                  Sets the current record's "Bonuses" collection
 * @method Dealer              setDiscussions()              Sets the current record's "Discussions" collection
 * @method Dealer              setAgreementModels()          Sets the current record's "AgreementModels" collection
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseDealer extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('dealers');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('number', 'integer', null, array(
             'type' => 'string',
             'unique' => true,
             ));
        $this->hasColumn('slug', 'string', null, array(
             'type' => 'string'
             ));
        $this->hasColumn('name', 'string', 60, array(
             'type' => 'string',
             'length' => 60,
             ));
        $this->hasColumn('address', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             ));
        $this->hasColumn('phone', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             ));
        $this->hasColumn('site', 'string', 128, array(
             'type' => 'string',
             'length' => 128,
             ));
        $this->hasColumn('email', 'string', 128, array(
             'type' => 'string',
             'length' => 128,
             ));
        $this->hasColumn('email_so', 'string', 128, array(
            'type' => 'string',
            'length' => 128,
        ));
        $this->hasColumn('longitude', 'decimal', null, array(
             'type' => 'decimal',
             'scale' => 6,
             ));
        $this->hasColumn('latitude', 'decimal', null, array(
             'type' => 'decimal',
             'scale' => 6,
             ));
        $this->hasColumn('city_id', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('regional_manager_id', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('nfz_regional_manager_id', 'integer', null, array(
            'type' => 'integer',
        ));
        $this->hasColumn('company_id', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('importer_id', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('dealer_type', 'tinyint', null, array(
             'type' => 'tinyint',
             ));
        $this->hasColumn('status', 'tinyint', null, array(
             'type' => 'tinyint',
             ));
        $this->hasColumn('dealer_group_id', 'integer', null, array(
            'type' => 'integer',
        ));
        $this->hasColumn('only_sp', 'boolean', null, array(
            'type' => 'boolean',
            'default' => true,
            'notnull' => true
        ));
        $this->hasColumn('number_length', 'integer', null, array(
            'type' => 'integer',
            'default' => 3
        ));

        $this->option('orderBy', 'name asc');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('City', array(
             'local' => 'city_id',
             'foreign' => 'id'));

        $this->hasOne('LegalPerson', array(
             'local' => 'company_id',
             'foreign' => 'id'));

        $this->hasOne('NaturalPerson as RegionalManager', array(
             'local' => 'regional_manager_id',
             'foreign' => 'id'));

        $this->hasOne('NaturalPerson as NfzRegionalManager', array(
            'local' => 'nfz_regional_manager_id',
            'foreign' => 'id'));

        $this->hasMany('LogEntry as LogEntries', array(
             'local' => 'id',
             'foreign' => 'dealer_id'));

        $this->hasMany('ActivityTaskResult as TaskResults', array(
             'local' => 'id',
             'foreign' => 'dealer_id'));

        $this->hasMany('AcceptedDealerActivity as AcceptedDealerActivities', array(
             'local' => 'id',
             'foreign' => 'dealer_id'));

        $this->hasMany('DealerUser as DealerUsers', array(
             'local' => 'id',
             'foreign' => 'dealer_id'));

        $this->hasMany('Budget', array(
             'local' => 'id',
             'foreign' => 'dealer_id'));

        $this->hasMany('RealBudget', array(
             'local' => 'id',
             'foreign' => 'dealer_id'));

        $this->hasMany('RealTotalBudget', array(
             'local' => 'id',
             'foreign' => 'dealer_id'));

        $this->hasMany('DealerBonus as Bonuses', array(
             'local' => 'id',
             'foreign' => 'dealer_id'));

        $this->hasMany('DealerDiscussion as Discussions', array(
             'local' => 'id',
             'foreign' => 'dealer_id'));

        $this->hasMany('AgreementModel as AgreementModels', array(
             'local' => 'id',
             'foreign' => 'dealer_id'));
		
		$this->hasMany('Dealer as Dealers', array(
             'refClass' => 'ActivityDealer',
             'local' => 'dealer_id',
             'foreign' => 'activity_id'));

        $this->hasMany('ActivityExtendedStatisticFieldsData as ActivityExtendedStatisticFieldsDatas', array(
            'local' => 'id',
            'foreign' => 'dealer_id'));

        $this->hasMany('ActivityFieldsValues', array(
            'local' => 'id',
            'foreign' => 'dealer_id'));

        $this->hasOne('DealersGroups as DealerGroup', array(
            'local' => 'dealer_group_id',
            'foreign' => 'id'));
    }
}
