<?php

/**
 * ActivityExtendedStatisticFields form base class.
 *
 * @method ActivityExtendedStatisticFields getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityExtendedStatisticFieldsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'header'      => new sfWidgetFormInputText(),
      'description' => new sfWidgetFormInputText(),
      'activity_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Activity'), 'add_empty' => true)),
      'parent_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Section'), 'add_empty' => true)),
      'status'      => new sfWidgetFormInputText(),
      'value_type'  => new sfWidgetFormChoice(array('choices' => array('date' => 'date', 'dig' => 'dig', 'calc' => 'calc', 'text' => 'text', 'any' => 'any', 'file' => 'file'))),
      'position'    => new sfWidgetFormInputText(),
      'required'    => new sfWidgetFormInputCheckbox(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'header'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'description' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'activity_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Activity'), 'required' => false)),
      'parent_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Section'), 'required' => false)),
      'status'      => new sfValidatorInteger(array('required' => false)),
      'value_type'  => new sfValidatorChoice(array('choices' => array(0 => 'date', 1 => 'dig', 2 => 'calc', 3 => 'text', 4 => 'any', 5 => 'file'), 'required' => false)),
      'position'    => new sfValidatorInteger(array('required' => false)),
      'required'    => new sfValidatorBoolean(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('activity_extended_statistic_fields[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityExtendedStatisticFields';
  }

}
