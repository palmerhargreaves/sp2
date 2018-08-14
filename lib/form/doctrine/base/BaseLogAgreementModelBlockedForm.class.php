<?php

/**
 * LogAgreementModelBlocked form base class.
 *
 * @method LogAgreementModelBlocked getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseLogAgreementModelBlockedForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'object_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('AgreementModel'), 'add_empty' => true)),
      'user_id'     => new sfWidgetFormInputText(),
      'dealer_id'   => new sfWidgetFormInputText(),
      'action'      => new sfWidgetFormInputText(),
      'description' => new sfWidgetFormInputText(),
      'created_at'  => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'object_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('AgreementModel'), 'required' => false)),
      'user_id'     => new sfValidatorInteger(array('required' => false)),
      'dealer_id'   => new sfValidatorInteger(array('required' => false)),
      'action'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'description' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'created_at'  => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('log_agreement_model_blocked[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LogAgreementModelBlocked';
  }

}
