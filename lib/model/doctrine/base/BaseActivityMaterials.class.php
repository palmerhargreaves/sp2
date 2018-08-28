<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ActivityMaterials', 'doctrine');

/**
 * BaseActivityMaterials
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $activity_id
 * @property integer $material_id
 * 
 * @method integer           getId()          Returns the current record's "id" value
 * @method integer           getActivityId()  Returns the current record's "activity_id" value
 * @method integer           getMaterialId()  Returns the current record's "material_id" value
 * @method ActivityMaterials setId()          Sets the current record's "id" value
 * @method ActivityMaterials setActivityId()  Sets the current record's "activity_id" value
 * @method ActivityMaterials setMaterialId()  Sets the current record's "material_id" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseActivityMaterials extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('activity_materials');
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
        $this->hasColumn('material_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
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