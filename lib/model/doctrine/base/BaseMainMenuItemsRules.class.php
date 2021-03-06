<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('MainMenuItemsRules', 'doctrine');

/**
 * BaseMainMenuItemsRules
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $menu_item_id
 * @property string $users_rules
 * @property string $users_extra_rules
 * @property string $users_departments
 * 
 * @method integer            getId()                Returns the current record's "id" value
 * @method integer            getMenuItemId()        Returns the current record's "menu_item_id" value
 * @method string             getUsersRules()        Returns the current record's "users_rules" value
 * @method string             getUsersExtraRules()   Returns the current record's "users_extra_rules" value
 * @method string             getUsersDepartments()  Returns the current record's "users_departments" value
 * @method MainMenuItemsRules setId()                Sets the current record's "id" value
 * @method MainMenuItemsRules setMenuItemId()        Sets the current record's "menu_item_id" value
 * @method MainMenuItemsRules setUsersRules()        Sets the current record's "users_rules" value
 * @method MainMenuItemsRules setUsersExtraRules()   Sets the current record's "users_extra_rules" value
 * @method MainMenuItemsRules setUsersDepartments()  Sets the current record's "users_departments" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseMainMenuItemsRules extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('main_menu_items_rules');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('menu_item_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'notnull' => true,
             ));
        $this->hasColumn('users_rules', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             'notnull' => true,
             ));
        $this->hasColumn('users_extra_rules', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             'notnull' => true,
             ));
        $this->hasColumn('users_departments', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             'notnull' => true,
             ));
        $this->hasColumn('dealers_types', 'string', 255, array(
            'type' => 'string',
            'length' => 255,
            'notnull' => true,
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
