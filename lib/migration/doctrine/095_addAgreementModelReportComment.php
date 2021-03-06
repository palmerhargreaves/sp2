<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddAgreementModelReportComment extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->createTable('agreement_model_report_comment', array(
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
             'report_id' => 
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
        $this->createForeignKey('agreement_model_report_comment', 'arai', array(
             'name' => 'arai',
             'local' => 'report_id',
             'foreign' => 'id',
             'foreignTable' => 'agreement_model_report',
             ));
        $this->createForeignKey('agreement_model_report_comment', 'agreement_model_report_comment_user_id_user_id', array(
             'name' => 'agreement_model_report_comment_user_id_user_id',
             'local' => 'user_id',
             'foreign' => 'id',
             'foreignTable' => 'user',
             ));
        $this->addIndex('agreement_model_report_comment', 'agreement_model_report_comment_report_id', array(
             'fields' => 
             array(
              0 => 'report_id',
             ),
             ));
        $this->addIndex('agreement_model_report_comment', 'agreement_model_report_comment_user_id', array(
             'fields' => 
             array(
              0 => 'user_id',
             ),
             ));
    }

    public function down()
    {
        $this->dropForeignKey('agreement_model_report_comment', 'arai');
        $this->dropForeignKey('agreement_model_report_comment', 'agreement_model_report_comment_user_id_user_id');
        $this->removeIndex('agreement_model_report_comment', 'agreement_model_report_comment_report_id', array(
             'fields' => 
             array(
              0 => 'report_id',
             ),
             ));
        $this->removeIndex('agreement_model_report_comment', 'agreement_model_report_comment_user_id', array(
             'fields' => 
             array(
              0 => 'user_id',
             ),
             ));
        $this->dropTable('agreement_model_report_comment');
    }
}