<?php

/**
 * ActivityInfoFieldsData form base class.
 *
 * @method ActivityInfoFieldsData getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityInfoFieldsDataForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'activity_id' => new sfWidgetFormInputText(),
      'field_id'    => new sfWidgetFormInputText(),
      'description' => new sfWidgetFormInputText(),
      'order_num'   => new sfWidgetFormInputText(),
      'status'      => new sfWidgetFormInputCheckbox(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'activity_id' => new sfValidatorInteger(array('required' => false)),
      'field_id'    => new sfValidatorInteger(array('required' => false)),
      'description' => new sfValidatorString(array('max_length' => 255)),
      'order_num'   => new sfValidatorPass(),
      'status'      => new sfValidatorBoolean(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('activity_info_fields_data[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityInfoFieldsData';
  }

}
