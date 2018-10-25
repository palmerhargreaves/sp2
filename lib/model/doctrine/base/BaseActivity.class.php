<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Activity', 'doctrine');

/**
 * BaseActivity
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property date $start_date
 * @property date $end_date
 * @property clob $custom_date
 * @property clob $description
 * @property clob $brief
 * @property string $materials_url
 * @property boolean $finished
 * @property boolean $importance
 * @property boolean $has_concept
 * @property boolean $hide
 * @property integer $sort
 * @property Doctrine_Collection $Files
 * @property Doctrine_Collection $Tasks
 * @property ActivityModule $Module
 * @property Doctrine_Collection $AgreementModels
 * @property Doctrine_Collection $AcceptedDealerActivities
 * @property Doctrine_Collection $Modules
 * @property Doctrine_Collection $Materials
 * @property Doctrine_Collection $Info
 * @property Doctrine_Collection $UserViews
 * @property Doctrine_Collection $AgreementModelBlanks
 * 
 * @method integer             getId()                       Returns the current record's "id" value
 * @method string              getName()                     Returns the current record's "name" value
 * @method date                getStartDate()                Returns the current record's "start_date" value
 * @method date                getEndDate()                  Returns the current record's "end_date" value
 * @method clob                getCustomDate()               Returns the current record's "custom_date" value
 * @method clob                getDescription()              Returns the current record's "description" value
 * @method clob                getBrief()                    Returns the current record's "brief" value
 * @method string              getMaterialsUrl()             Returns the current record's "materials_url" value
 * @method boolean             getFinished()                 Returns the current record's "finished" value
 * @method boolean             getImportance()               Returns the current record's "importance" value
 * @method boolean             getHasConcept()               Returns the current record's "has_concept" value
 * @method boolean             getHide()                     Returns the current record's "hide" value
 * @method integer             getSort()                     Returns the current record's "sort" value
 * @method Doctrine_Collection getFiles()                    Returns the current record's "Files" collection
 * @method Doctrine_Collection getTasks()                    Returns the current record's "Tasks" collection
 * @method ActivityModule      getModule()                   Returns the current record's "Module" value
 * @method Doctrine_Collection getAgreementModels()          Returns the current record's "AgreementModels" collection
 * @method Doctrine_Collection getAcceptedDealerActivities() Returns the current record's "AcceptedDealerActivities" collection
 * @method Doctrine_Collection getModules()                  Returns the current record's "Modules" collection
 * @method Doctrine_Collection getMaterials()                Returns the current record's "Materials" collection
 * @method Doctrine_Collection getInfo()                     Returns the current record's "Info" collection
 * @method Doctrine_Collection getUserViews()                Returns the current record's "UserViews" collection
 * @method Doctrine_Collection getAgreementModelBlanks()     Returns the current record's "AgreementModelBlanks" collection
 * @method Activity            setId()                       Sets the current record's "id" value
 * @method Activity            setName()                     Sets the current record's "name" value
 * @method Activity            setStartDate()                Sets the current record's "start_date" value
 * @method Activity            setEndDate()                  Sets the current record's "end_date" value
 * @method Activity            setCustomDate()               Sets the current record's "custom_date" value
 * @method Activity            setDescription()              Sets the current record's "description" value
 * @method Activity            setBrief()                    Sets the current record's "brief" value
 * @method Activity            setMaterialsUrl()             Sets the current record's "materials_url" value
 * @method Activity            setFinished()                 Sets the current record's "finished" value
 * @method Activity            setImportance()               Sets the current record's "importance" value
 * @method Activity            setHasConcept()               Sets the current record's "has_concept" value
 * @method Activity            setHide()                     Sets the current record's "hide" value
 * @method Activity            setSort()                     Sets the current record's "sort" value
 * @method Activity            setFiles()                    Sets the current record's "Files" collection
 * @method Activity            setTasks()                    Sets the current record's "Tasks" collection
 * @method Activity            setModule()                   Sets the current record's "Module" value
 * @method Activity            setAgreementModels()          Sets the current record's "AgreementModels" collection
 * @method Activity            setAcceptedDealerActivities() Sets the current record's "AcceptedDealerActivities" collection
 * @method Activity            setModules()                  Sets the current record's "Modules" collection
 * @method Activity            setMaterials()                Sets the current record's "Materials" collection
 * @method Activity            setInfo()                     Sets the current record's "Info" collection
 * @method Activity            setUserViews()                Sets the current record's "UserViews" collection
 * @method Activity            setAgreementModelBlanks()     Sets the current record's "AgreementModelBlanks" collection
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseActivity extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('activity');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => 255,
             ));
        $this->hasColumn('start_date', 'date', null, array(
             'type' => 'date',
             'notnull' => true,
             ));
        $this->hasColumn('end_date', 'date', null, array(
             'type' => 'date',
             'notnull' => true,
             ));
        $this->hasColumn('custom_date', 'clob', null, array(
             'type' => 'clob',
             'notnull' => false,
             ));
        $this->hasColumn('description', 'clob', null, array(
             'type' => 'clob',
             'notnull' => false,
             ));
        $this->hasColumn('efficiency_description', 'clob', null, array(
            'type' => 'clob',
            'notnull' => false,
        ));
        $this->hasColumn('brief', 'clob', null, array(
             'type' => 'clob',
             'notnull' => false,
             ));
        $this->hasColumn('materials_url', 'string', 255, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 255,
             ));
        $this->hasColumn('finished', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => true,
             'default' => false,
             ));
        $this->hasColumn('importance', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => true,
             'default' => false,
             ));
        $this->hasColumn('has_concept', 'boolean', null, array(
             'type' => 'boolean',
             'default' => false,
             'notnull' => true,
             ));
        $this->hasColumn('many_concepts', 'boolean', null, array(
             'type' => 'boolean',
             'default' => false,
             'notnull' => true,
             ));
        $this->hasColumn('is_concept_complete', 'boolean', null, array(
             'type' => 'boolean',
             'default' => false,
             'notnull' => true,
             ));

        $this->hasColumn('hide', 'boolean', null, array(
             'type' => 'boolean',
             'default' => false,
             'notnull' => true,
             ));
        $this->hasColumn('sort', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             'default' => 0,
             ));
        $this->hasColumn('select_activity', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => true,
             'default' => false,
             ));
        $this->hasColumn('is_limit_run', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => true,
             'default' => false,
             ));
        $this->hasColumn('stats_description', 'clob', null, array(
             'type' => 'clob',
             'notnull' => false,
             ));

        $this->hasColumn('allow_to_all_dealers', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => true,
             'default' => false,
             ));
        $this->hasColumn('is_own', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => true,
             'default' => false,
             ));
        $this->hasColumn('allow_share_name', 'boolean', null, array(
            'type' => 'boolean',
            'default' => false,
            'notnull' => true,
        ));

        /*$this->hasColumn('stat_quarter', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => '1',
              1 => '2',
              0 => '3',
              1 => '4'
             ),
             'notnull' => true,
             ));*/
        $this->hasColumn('stat_quarter', 'string', 255, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 255,
             ));
        
        $this->hasColumn('stat_year', 'integer', null, array(
             'type' => 'interger',
             'notnull' => true,
             ));

       	$this->hasColumn('position', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true
             ));

         $this->hasColumn('allow_extended_statistic', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => true,
             'default' => false,
             ));        

        $this->hasColumn('allow_certificate', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => true,
             'default' => false,
             ));

        $this->hasColumn('allow_special_agreement', 'boolean', null, array(
            'type' => 'boolean',
            'notnull' => true,
            'default' => false,
        ));

        $this->hasColumn('type_company_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true,
            'default' => 0,
        ));

        $this->hasColumn('own_activity', 'boolean', null, array(
            'type' => 'boolean',
            'notnull' => true,
            'default' => false,
        ));

        $this->hasColumn('required_activity', 'boolean', null, array(
            'type' => 'boolean',
            'notnull' => true,
            'default' => false,
        ));

        $this->hasColumn('mandatory_activity', 'boolean', null, array(
            'type' => 'boolean',
            'notnull' => true,
            'default' => false,
        ));

        $this->hasColumn('event_name', 'string', 255, array(
            'type' => 'string',
            'notnull' => true,
            'length' => 255,
        ));

        $this->hasColumn('allow_statistic_pre_check', 'boolean', null, array(
            'type' => 'boolean',
            'notnull' => true,
            'default' => false,
        ));

        $this->hasColumn('allow_agreement_by_one_user', 'boolean', null, array(
            'type' => 'boolean',
            'notnull' => true,
            'default' => false,
        ));

        //Activity images
        $this->hasColumn('preview_file', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));

        $this->hasColumn('image_file', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));

        $this->hasColumn('company_target', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));

        $this->hasColumn('target_audience', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));

        $this->hasColumn('company_mechanics', 'string', 255, array(
            'type' => 'string',
            'notnull' => false,
            'length' => 255,
        ));

        $this->index('importance', array(
             'fields' => 
             array(
              0 => 'finished',
              1 => 'importance',
              2 => 'id',
             ),
             ));
        $this->index('start_date', array(
             'fields' => 
             array(
              0 => 'start_date',
              1 => 'finished',
             ),
             ));
        $this->index('has_concept', array(
             'fields' => 
             array(
              0 => 'has_concept',
             ),
             ));
        $this->index('sort', array(
             'fields' => 
             array(
              0 => 'finished',
              1 => 'importance',
              2 => 'sort',
              3 => 'id',
             ),
             ));
        $this->index('hidden_sort', array(
             'fields' => 
             array(
              0 => 'hide',
              1 => 'finished',
              2 => 'importance',
              3 => 'sort',
              4 => 'id',
             ),
             ));
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('ActivityFile as Files', array(
             'local' => 'id',
             'foreign' => 'activity_id',
             'cascade' => array(
             0 => 'delete',
             )));

        $this->hasMany('ActivityTask as Tasks', array(
             'local' => 'id',
             'foreign' => 'activity_id',
             'cascade' => array(
             0 => 'delete',
             )));
			 
		$this->hasOne('Dealer as Dealer', array(
             'local' => 'dealer_id',
             'foreign' => 'id'));

        $this->hasOne('ActivityModule as Module', array(
             'local' => 'module_id',
             'foreign' => 'id'));

        $this->hasOne('Quarters as Quarter', array(
             'local' => 'quarter_id',
             'foreign' => 'id'));

        $this->hasMany('AgreementModel as AgreementModels', array(
             'local' => 'id',
             'foreign' => 'activity_id',
             'cascade' => array(
             0 => 'delete',
             )));

        $this->hasMany('AcceptedDealerActivity as AcceptedDealerActivities', array(
             'local' => 'id',
             'foreign' => 'activity_id',
             'cascade' => array(
             0 => 'delete',
             )));
			 
		$this->hasMany('Dealer as Dealers', array(
             'refClass' => 'ActivityDealer',
             'local' => 'activity_id',
             'foreign' => 'dealer_id'));

        $this->hasMany('ActivityModule as Modules', array(
             'refClass' => 'AcivityModuleActivity',
             'local' => 'activity_id',
             'foreign' => 'module_id'));

        $this->hasMany('Quarters as Quarters', array(
             'refClass' => 'ActivityQuarters',
             'local' => 'activity_id',
             'foreign' => 'quarter_id'));

        $this->hasMany('Material as Materials', array(
             'local' => 'id',
             'foreign' => 'activity_id'));

        $this->hasMany('ActivityInfo as Info', array(
             'local' => 'id',
             'foreign' => 'activity_id'));

        $this->hasMany('ActivityFields as ActivityField', array(
             'local' => 'id',
             'foreign' => 'activity_id'));

        $this->hasMany('ActivityUserView as UserViews', array(
             'local' => 'id',
             'foreign' => 'activity_id'));

        $this->hasMany('AgreementModelBlank as AgreementModelBlanks', array(
             'local' => 'id',
             'foreign' => 'activity_id'));

        $this->hasMany('AgreementModelBlank as AgreementModelBlanks', array(
            'local' => 'id',
            'foreign' => 'activity_id'));

        $this->hasOne('ActivityCompanyType as CompanyType', array(
            'local' => 'type_company_id',
            'foreign' => 'id'));

        $this->hasMany('ActivityEfficiencyFormulas as Formulas', array(
            'local' => 'id',
            'foreign' => 'activity_id'));

        $this->hasMany('ActivityExtendedStatisticSections as ServiceClinicSections', array(
            'local' => 'id',
            'foreign' => 'activity_id')
        );

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}
