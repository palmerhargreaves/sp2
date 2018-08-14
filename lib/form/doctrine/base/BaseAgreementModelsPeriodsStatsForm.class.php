<?php

/**
 * AgreementModelsPeriodsStats form base class.
 *
 * @method AgreementModelsPeriodsStats getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAgreementModelsPeriodsStatsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'model_id'        => new sfWidgetFormInputText(),
      'model_period_id' => new sfWidgetFormInputText(),
      'model_status'    => new sfWidgetFormChoice(array('choices' => array('commented' => 'commented', 'commented_by_specialist' => 'commented_by_specialist', 'sended' => 'sended', 'completed' => 'completed'))),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'model_id'        => new sfValidatorInteger(),
      'model_period_id' => new sfValidatorInteger(),
      'model_status'    => new sfValidatorChoice(array('choices' => array(0 => 'commented', 1 => 'commented_by_specialist', 2 => 'sended', 3 => 'completed'), 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('agreement_models_periods_stats[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AgreementModelsPeriodsStats';
  }

}
