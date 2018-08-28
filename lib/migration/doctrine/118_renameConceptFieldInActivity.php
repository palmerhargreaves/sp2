<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class RenameConceptFieldInActivity extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->removeIndex('activity', 'concept', array(
             'fields' => 
             array(
              0 => 'concept',
             ),
             ));
        $this->removeColumn('activity', 'concept');
        $this->addColumn('activity', 'has_concept', 'boolean', '25', array(
             'default' => '0',
             'notnull' => '1',
             ));
        $this->addIndex('activity', 'has_concept', array(
             'fields' => 
             array(
              0 => 'has_concept',
             ),
             ));
    }

    public function down()
    {
        $this->removeIndex('activity', 'has_concept', array(
             'fields' => 
             array(
              0 => 'has_concept',
             ),
             ));
        $this->addColumn('activity', 'concept', 'boolean', '25', array(
             'default' => '0',
             'notnull' => '1',
             ));
        $this->addIndex('activity', 'concept', array(
             'fields' => 
             array(
              0 => 'concept',
             ),
             ));
        $this->removeColumn('activity', 'has_concept');
    }
}