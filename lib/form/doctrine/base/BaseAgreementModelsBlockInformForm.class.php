<?php

/**
 * AgreementModelsBlockInform form base class.
 *
 * @method AgreementModelsBlockInform getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAgreementModelsBlockInformForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'block_type' => new sfWidgetFormChoice(array('choices' => array('left_10' => 'left_10', 'left_2' => 'left_2', 'blocked' => 'blocked'))),
      'model_id'   => new sfWidgetFormInputText(),
      'left_days'  => new sfWidgetFormInputText(),
      'created_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'block_type' => new sfValidatorChoice(array('choices' => array(0 => 'left_10', 1 => 'left_2', 2 => 'blocked'), 'required' => false)),
      'model_id'   => new sfValidatorInteger(),
      'left_days'  => new sfValidatorInteger(array('required' => false)),
      'created_at' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('agreement_models_block_inform[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AgreementModelsBlockInform';
  }

}
