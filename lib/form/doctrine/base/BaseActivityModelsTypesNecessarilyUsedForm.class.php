<?php

/**
 * ActivityModelsTypesNecessarilyUsed form base class.
 *
 * @method ActivityModelsTypesNecessarilyUsed getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityModelsTypesNecessarilyUsedForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'activity_id'    => new sfWidgetFormInputText(),
      'dealer_id'      => new sfWidgetFormInputText(),
      'necessarily_id' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'activity_id'    => new sfValidatorInteger(array('required' => false)),
      'dealer_id'      => new sfValidatorInteger(array('required' => false)),
      'necessarily_id' => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('activity_models_types_necessarily_used[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityModelsTypesNecessarilyUsed';
  }

}
