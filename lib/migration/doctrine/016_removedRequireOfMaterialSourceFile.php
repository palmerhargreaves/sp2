<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class RemovedRequireOfMaterialSourceFile extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->changeColumn('material_source', 'file', 'string', '255', array(
             'notnull' => '',
             ));
    }

    public function down()
    {
        $this->changeColumn('material_source', 'file', 'string', '255', array(
             'notnull' => '1',
             ));
    }
}