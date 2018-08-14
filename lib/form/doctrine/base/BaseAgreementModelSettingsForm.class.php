<?php

/**
 * AgreementModelSettings form base class.
 *
 * @method AgreementModelSettings getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAgreementModelSettingsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'model_id'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Model'), 'add_empty' => false)),
      'certificate_date_to' => new sfWidgetFormDate(),
      'msg_send'            => new sfWidgetFormInputCheckbox(),
      'activate_msg_send'   => new sfWidgetFormInputCheckbox(),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'model_id'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Model'))),
      'certificate_date_to' => new sfValidatorDate(),
      'msg_send'            => new sfValidatorBoolean(array('required' => false)),
      'activate_msg_send'   => new sfValidatorBoolean(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('agreement_model_settings[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AgreementModelSettings';
  }

}
