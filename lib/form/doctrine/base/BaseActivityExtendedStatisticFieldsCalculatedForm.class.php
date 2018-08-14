<?php

/**
 * ActivityExtendedStatisticFieldsCalculated form base class.
 *
 * @method ActivityExtendedStatisticFieldsCalculated getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityExtendedStatisticFieldsCalculatedForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'parent_field' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ParentField'), 'add_empty' => true)),
      'calc_field'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('CalculatedField'), 'add_empty' => true)),
      'calc_type'    => new sfWidgetFormChoice(array('choices' => array('plus' => 'plus', 'minus' => 'minus', 'divide' => 'divide', 'multiple' => 'multiple', 'percent' => 'percent'))),
      'activity_id'  => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'parent_field' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ParentField'), 'required' => false)),
      'calc_field'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('CalculatedField'), 'required' => false)),
      'calc_type'    => new sfValidatorChoice(array('choices' => array(0 => 'plus', 1 => 'minus', 2 => 'divide', 3 => 'multiple', 4 => 'percent'), 'required' => false)),
      'activity_id'  => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('activity_extended_statistic_fields_calculated[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityExtendedStatisticFieldsCalculated';
  }

}
