<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddDiscussionForAgreementModel extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('agreement_model', 'discussion_id', 'integer', '8', array(
             'notnull' => '',
             ));
        $this->createForeignKey('agreement_model', 'agreement_model_discussion_id_discussion_id', array(
             'name' => 'agreement_model_discussion_id_discussion_id',
             'local' => 'discussion_id',
             'foreign' => 'id',
             'foreignTable' => 'discussion',
             ));
        $this->addIndex('agreement_model', 'agreement_model_discussion_id', array(
             'fields' => 
             array(
              0 => 'discussion_id',
             ),
             ));
    }

    public function down()
    {
        $this->dropForeignKey('agreement_model', 'agreement_model_discussion_id_discussion_id');
        $this->removeIndex('agreement_model', 'agreement_model_discussion_id', array(
             'fields' => 
             array(
              0 => 'discussion_id',
             ),
             ));
        $this->removeColumn('agreement_model', 'discussion_id');
    }
}