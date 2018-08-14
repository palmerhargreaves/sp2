<?php

/**
 * ActivityInfoFields form base class.
 *
 * @method ActivityInfoFields getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityInfoFieldsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'     => new sfWidgetFormInputHidden(),
      'header' => new sfWidgetFormInputText(),
      'image'  => new sfWidgetFormInputText(),
      'type'   => new sfWidgetFormChoice(array('choices' => array('sym' => 'sym', 'dig' => 'dig'))),
    ));

    $this->setValidators(array(
      'id'     => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'header' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'image'  => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'type'   => new sfValidatorChoice(array('choices' => array(0 => 'sym', 1 => 'dig'))),
    ));

    $this->widgetSchema->setNameFormat('activity_info_fields[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityInfoFields';
  }

}
