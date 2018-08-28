<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('AgreementModelsBlockInform', 'doctrine');

/**
 * BaseAgreementModelsBlockInform
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @property integer $id
 * @property enum $block_type
 * @property integer $model_id
 *
 * @method integer                    getId()         Returns the current record's "id" value
 * @method enum                       getBlockType()  Returns the current record's "block_type" value
 * @method integer                    getModelId()    Returns the current record's "model_id" value
 * @method AgreementModelsBlockInform setId()         Sets the current record's "id" value
 * @method AgreementModelsBlockInform setBlockType()  Sets the current record's "block_type" value
 * @method AgreementModelsBlockInform setModelId()    Sets the current record's "model_id" value
 *
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseAgreementModelsBlockInform extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('agreement_models_block_inform');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true,
        ));
        $this->hasColumn('block_type', 'enum', null, array(
            'type' => 'enum',
            'values' =>
                array(
                    0 => 'left_10',
                    1 => 'left_2',
                    2 => 'blocked',
                ),
        ));
        $this->hasColumn('model_id', 'integer', 11, array(
            'type' => 'integer',
            'length' => 11,
            'notnull' => true,
        ));

        $this->hasColumn('left_days', 'integer', 2, array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => true,
            'default' => 0
        ));

        $this->hasColumn('period_end', 'date', null, array(
            'type' => 'date',
            'notnull' => false
        ));


        $this->option('type', 'MyISAM');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $timestampable0 = new Doctrine_Template_Timestampable(array(
            'updated' =>
                array(
                    'disabled' => true,
                ),
        ));
        $this->actAs($timestampable0);
    }
}