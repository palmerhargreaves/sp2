<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddMaterialRelationWithActivity extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('material', 'activity_id', 'integer', '8', array(
             'notnull' => '',
             ));
        $this->createForeignKey('material', 'material_activity_id_activity_id', array(
             'name' => 'material_activity_id_activity_id',
             'local' => 'activity_id',
             'foreign' => 'id',
             'foreignTable' => 'activity',
             ));
        $this->addIndex('material', 'material_activity_id', array(
             'fields' => 
             array(
              0 => 'activity_id',
             ),
             ));
    }

    public function down()
    {
        $this->dropForeignKey('material', 'material_activity_id_activity_id');
        $this->removeIndex('material', 'material_activity_id', array(
             'fields' => 
             array(
              0 => 'activity_id',
             ),
             ));
        $this->removeColumn('material', 'activity_id');
    }
}