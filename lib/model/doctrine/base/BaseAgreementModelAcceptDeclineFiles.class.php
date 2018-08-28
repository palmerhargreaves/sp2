<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('AgreementModelAcceptDeclineFiles', 'doctrine');

/**
 * BaseAgreementModelAcceptDeclineFiles
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $model_id
 * @property enum $file_type
 * @property string $file_name
 * 
 * @method integer                          getId()        Returns the current record's "id" value
 * @method integer                          getModelId()   Returns the current record's "model_id" value
 * @method enum                             getFileType()  Returns the current record's "file_type" value
 * @method string                           getFileName()  Returns the current record's "file_name" value
 * @method AgreementModelAcceptDeclineFiles setId()        Sets the current record's "id" value
 * @method AgreementModelAcceptDeclineFiles setModelId()   Sets the current record's "model_id" value
 * @method AgreementModelAcceptDeclineFiles setFileType()  Sets the current record's "file_type" value
 * @method AgreementModelAcceptDeclineFiles setFileName()  Sets the current record's "file_name" value
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseAgreementModelAcceptDeclineFiles extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('agreement_model_accept_decline_files');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('model_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('file_type', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => 'accept',
              1 => 'decline',
             ),
             ));
        $this->hasColumn('file_name', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => 255,
             ));

        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}