<?php

/**
 * AgreementModelUserSettings form base class.
 *
 * @method AgreementModelUserSettings getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAgreementModelUserSettingsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'dealer_id'       => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Dealer'), 'add_empty' => false)),
      'activity_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Activity'), 'add_empty' => false)),
      'model_id'        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Model'), 'add_empty' => false)),
      'plus_days'       => new sfWidgetFormInputText(),
      'certificate_end' => new sfWidgetFormDate(),
      'is_msg_sended'   => new sfWidgetFormInputCheckbox(),
      'is_blocked'      => new sfWidgetFormInputCheckbox(),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'dealer_id'       => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Dealer'))),
      'activity_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Activity'))),
      'model_id'        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Model'))),
      'plus_days'       => new sfValidatorInteger(),
      'certificate_end' => new sfValidatorDate(),
      'is_msg_sended'   => new sfValidatorBoolean(array('required' => false)),
      'is_blocked'      => new sfValidatorBoolean(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('agreement_model_user_settings[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AgreementModelUserSettings';
  }

}
