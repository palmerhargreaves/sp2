<?php

/**
 * ActivityExtendedStatisticFieldsData form base class.
 *
 * @method ActivityExtendedStatisticFieldsData getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityExtendedStatisticFieldsDataForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'field_id'    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Field'), 'add_empty' => true)),
      'activity_id' => new sfWidgetFormInputText(),
      'user_id'     => new sfWidgetFormInputText(),
      'dealer_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Dealer'), 'add_empty' => true)),
      'value'       => new sfWidgetFormInputText(),
      'concept_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Concept'), 'add_empty' => true)),
      'created_at'  => new sfWidgetFormDateTime(),
      'updated_at'  => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'field_id'    => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Field'), 'required' => false)),
      'activity_id' => new sfValidatorInteger(array('required' => false)),
      'user_id'     => new sfValidatorInteger(array('required' => false)),
      'dealer_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Dealer'), 'required' => false)),
      'value'       => new sfValidatorInteger(array('required' => false)),
      'concept_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Concept'), 'required' => false)),
      'created_at'  => new sfValidatorDateTime(),
      'updated_at'  => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('activity_extended_statistic_fields_data[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityExtendedStatisticFieldsData';
  }

}
