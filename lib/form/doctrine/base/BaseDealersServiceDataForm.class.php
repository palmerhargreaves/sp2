<?php

/**
 * DealersServiceData form base class.
 *
 * @method DealersServiceData getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseDealersServiceDataForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'user_id'           => new sfWidgetFormInputText(),
      'dealer_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Dealer'), 'add_empty' => true)),
      'dialog_service_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Dialog'), 'add_empty' => true)),
      'start_date'        => new sfWidgetFormDate(),
      'end_date'          => new sfWidgetFormDate(),
      'status'            => new sfWidgetFormChoice(array('choices' => array('accepted' => 'accepted', 'declined' => 'declined'))),
      'declined_date'     => new sfWidgetFormDate(),
      'created_at'        => new sfWidgetFormDateTime(),
      'updated_at'        => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'user_id'           => new sfValidatorInteger(array('required' => false)),
      'dealer_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Dealer'), 'required' => false)),
      'dialog_service_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Dialog'), 'required' => false)),
      'start_date'        => new sfValidatorDate(),
      'end_date'          => new sfValidatorDate(),
      'status'            => new sfValidatorChoice(array('choices' => array(0 => 'accepted', 1 => 'declined'))),
      'declined_date'     => new sfValidatorDate(),
      'created_at'        => new sfValidatorDateTime(),
      'updated_at'        => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('dealers_service_data[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DealersServiceData';
  }

}
