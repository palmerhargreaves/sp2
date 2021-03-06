<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class RemoveCommentFieldsFromAgreementComment extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->removeColumn('agreement_comment', 'comment');
        $this->removeColumn('agreement_comment', 'request');
        $this->removeColumn('agreement_model_comment', 'comment');
        $this->removeColumn('agreement_model_comment', 'request');
    }

    public function down()
    {
        $this->addColumn('agreement_comment', 'comment', 'clob', '', array(
             'notnull' => '',
             ));
        $this->addColumn('agreement_comment', 'request', 'clob', '', array(
             'notnull' => '',
             ));
        $this->addColumn('agreement_model_comment', 'comment', 'clob', '', array(
             'notnull' => '',
             ));
        $this->addColumn('agreement_model_comment', 'request', 'clob', '', array(
             'notnull' => '',
             ));
    }
}