<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddWaitFieldForModel extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('agreement_model', 'wait', 'boolean', '25', array(
             'default' => '0',
             'notnull' => '1',
             ));
        $this->addIndex('agreement_model', 'wait', array(
             'fields' => 
             array(
              0 => 'wait',
             ),
             ));
    }

    public function down()
    {
        $this->removeIndex('agreement_model', 'wait', array(
             'fields' => 
             array(
              0 => 'wait',
             ),
             ));
        $this->removeColumn('agreement_model', 'wait');
    }
}