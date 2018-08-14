<?php

/**
 * ActivityDealer form base class.
 *
 * @method ActivityDealer getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityDealerForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'activity_id' => new sfWidgetFormInputHidden(),
      'dealer_id'   => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'activity_id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('activity_id')), 'empty_value' => $this->getObject()->get('activity_id'), 'required' => false)),
      'dealer_id'   => new sfValidatorChoice(array('choices' => array($this->getObject()->get('dealer_id')), 'empty_value' => $this->getObject()->get('dealer_id'), 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('activity_dealer[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityDealer';
  }

}
