<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('DealerUserServiceAction', 'doctrine');

/**
 * BaseDealerUserServiceAction
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $user_id
 * @property integer $dealer_id
 * @property boolean $approved
 * @property boolean $manager
 * @property User $User
 * @property Dealer $Dealer
 * 
 * @method integer    getId()        Returns the current record's "id" value
 * @method integer    getUserId()    Returns the current record's "user_id" value
 * @method integer    getDealerId()  Returns the current record's "dealer_id" value
 * @method User       getUser()      Returns the current record's "User" value
 * @method Dealer     getDealer()    Returns the current record's "Dealer" value
 * @method DealerUser setId()        Sets the current record's "id" value
 * @method DealerUser setUserId()    Sets the current record's "user_id" value
 * @method DealerUser setDealerId()  Sets the current record's "dealer_id" value
 * @method DealerUser setUser()      Sets the current record's "User" value
 * @method DealerUser setDealer()    Sets the current record's "Dealer" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseDealerUserServiceAction extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('dealer_user_service_action');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('user_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('dealer_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
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

        /*$this->index('relation', array(
             'fields' => 
             array(
              0 => 'user_id',
              1 => 'dealer_id',
             ),
             'type' => 'unique',
             ));*/
        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('User', array(
             'local' => 'user_id',
             'foreign' => 'id'));

        $this->hasOne('Dealer', array(
             'local' => 'dealer_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}