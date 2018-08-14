<?php

/**
 * ActivityAcceptStats form base class.
 *
 * @method ActivityAcceptStats getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityAcceptStatsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'dealer_id'  => new sfWidgetFormInputText(),
      'year'       => new sfWidgetFormInputText(),
      'q1'         => new sfWidgetFormInputText(),
      'q2'         => new sfWidgetFormInputText(),
      'q3'         => new sfWidgetFormInputText(),
      'q4'         => new sfWidgetFormInputText(),
      'created_at' => new sfWidgetFormDateTime(),
      'updated_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'dealer_id'  => new sfValidatorInteger(array('required' => false)),
      'year'       => new sfValidatorInteger(array('required' => false)),
      'q1'         => new sfValidatorString(array('max_length' => 80, 'required' => false)),
      'q2'         => new sfValidatorString(array('max_length' => 80, 'required' => false)),
      'q3'         => new sfValidatorString(array('max_length' => 80, 'required' => false)),
      'q4'         => new sfValidatorString(array('max_length' => 80, 'required' => false)),
      'created_at' => new sfValidatorDateTime(),
      'updated_at' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('activity_accept_stats[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityAcceptStats';
  }

}
