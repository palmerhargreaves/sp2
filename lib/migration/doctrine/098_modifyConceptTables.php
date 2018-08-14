<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ModifyConceptTables extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createTable('agreement_concept_comment', array(
             'id' => 
             array(
              'type' => 'integer',
              'primary' => '1',
              'autoincrement' => '1',
              'length' => '8',
             ),
             'user_id' => 
             array(
              'type' => 'integer',
              'notnull' => '1',
              'length' => '8',
             ),
             'status' => 
             array(
              'type' => 'enum',
              'values' => 
              array(
              0 => 'wait',
              1 => 'accepted',
              2 => 'declined',
              ),
              'notnull' => '1',
              'length' => '',
             ),
             'concept_id' => 
             array(
              'type' => 'integer',
              'notnull' => '1',
              'length' => '8',
             ),
             'created_at' => 
             array(
              'notnull' => '1',
              'type' => 'timestamp',
              'length' => '25',
             ),
             'updated_at' => 
             array(
              'notnull' => '1',
              'type' => 'timestamp',
              'length' => '25',
             ),
             ), array(
             'type' => 'MyISAM',
             'indexes' => 
             array(
              'status' => 
              array(
              'fields' => 
              array(
               0 => 'status',
              ),
              ),
             ),
             'primary' => 
             array(
              0 => 'id',
             ),
             'collate' => 'utf8_unicode_ci',
             'charset' => 'utf8',
             ));
        $this->createTable('agreement_concept_decline_reason', array(
             'id' => 
             array(
              'type' => 'integer',
              'primary' => '1',
              'autoincrement' => '1',
              'length' => '8',
             ),
             'name' => 
             array(
              'type' => 'string',
              'notnull' => '',
              'length' => '255',
             ),
             ), array(
             'type' => 'MyISAM',
             'primary' => 
             array(
              0 => 'id',
             ),
             'collate' => 'utf8_unicode_ci',
             'charset' => 'utf8',
             ));
        $this->addColumn('agreement_concept', 'discussion_id', 'integer', '8', array(
             'notnull' => '',
             ));
        $this->addColumn('agreement_concept', 'decline_reason_id', 'integer', '8', array(
             'notnull' => '',
             ));
        $this->changeColumn('agreement_concept', 'status', 'enum', '', array(
             'values' => 
             array(
              0 => 'wait',
              1 => 'wait_specialist',
              2 => 'accepted',
              3 => 'declined',
              4 => 'not_sent',
             ),
             'notnull' => '1',
             ));
        $this->createForeignKey('agreement_concept', 'adai_1', array(
             'name' => 'adai_1',
             'local' => 'decline_reason_id',
             'foreign' => 'id',
             'foreignTable' => 'agreement_concept_decline_reason',
             ));
        $this->createForeignKey('agreement_concept', 'agreement_concept_discussion_id_discussion_id', array(
             'name' => 'agreement_concept_discussion_id_discussion_id',
             'local' => 'discussion_id',
             'foreign' => 'id',
             'foreignTable' => 'discussion',
             ));
        $this->createForeignKey('agreement_concept_comment', 'agreement_concept_comment_concept_id_agreement_concept_id', array(
             'name' => 'agreement_concept_comment_concept_id_agreement_concept_id',
             'local' => 'concept_id',
             'foreign' => 'id',
             'foreignTable' => 'agreement_concept',
             ));
        $this->createForeignKey('agreement_concept_comment', 'agreement_concept_comment_user_id_user_id', array(
             'name' => 'agreement_concept_comment_user_id_user_id',
             'local' => 'user_id',
             'foreign' => 'id',
             'foreignTable' => 'user',
             ));
        $this->addIndex('agreement_concept', 'agreement_concept_decline_reason_id', array(
             'fields' => 
             array(
              0 => 'decline_reason_id',
             ),
             ));
        $this->addIndex('agreement_concept', 'agreement_concept_discussion_id', array(
             'fields' => 
             array(
              0 => 'discussion_id',
             ),
             ));
        $this->addIndex('agreement_concept_comment', 'agreement_concept_comment_concept_id', array(
             'fields' => 
             array(
              0 => 'concept_id',
             ),
             ));
        $this->addIndex('agreement_concept_comment', 'agreement_concept_comment_user_id', array(
             'fields' => 
             array(
              0 => 'user_id',
             ),
             ));
    }

    public function down()
    {
        $this->dropForeignKey('agreement_concept', 'adai_1');
        $this->dropForeignKey('agreement_concept', 'agreement_concept_discussion_id_discussion_id');
        $this->dropForeignKey('agreement_concept_comment', 'agreement_concept_comment_concept_id_agreement_concept_id');
        $this->dropForeignKey('agreement_concept_comment', 'agreement_concept_comment_user_id_user_id');
        $this->removeIndex('agreement_concept', 'agreement_concept_decline_reason_id', array(
             'fields' => 
             array(
              0 => 'decline_reason_id',
             ),
             ));
        $this->removeIndex('agreement_concept', 'agreement_concept_discussion_id', array(
             'fields' => 
             array(
              0 => 'discussion_id',
             ),
             ));
        $this->removeIndex('agreement_concept_comment', 'agreement_concept_comment_concept_id', array(
             'fields' => 
             array(
              0 => 'concept_id',
             ),
             ));
        $this->removeIndex('agreement_concept_comment', 'agreement_concept_comment_user_id', array(
             'fields' => 
             array(
              0 => 'user_id',
             ),
             ));
        $this->dropTable('agreement_concept_comment');
        $this->dropTable('agreement_concept_decline_reason');
        $this->removeColumn('agreement_concept', 'discussion_id');
        $this->removeColumn('agreement_concept', 'decline_reason_id');
    }
}