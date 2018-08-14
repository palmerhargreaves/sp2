<?php

/**
 * AgreementModelsBlokedStatistics form base class.
 *
 * @method AgreementModelsBlokedStatistics getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAgreementModelsBlokedStatisticsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'model_id'   => new sfWidgetFormInputText(),
      'status'     => new sfWidgetFormChoice(array('choices' => array('active' => 'active', 'blocked' => 'blocked'))),
      'created_at' => new sfWidgetFormDateTime(),
      'updated_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'model_id'   => new sfValidatorInteger(),
      'status'     => new sfValidatorChoice(array('choices' => array(0 => 'active', 1 => 'blocked'))),
      'created_at' => new sfValidatorDateTime(),
      'updated_at' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('agreement_models_bloked_statistics[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AgreementModelsBlokedStatistics';
  }

}
