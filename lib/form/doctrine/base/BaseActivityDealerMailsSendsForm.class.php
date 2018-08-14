<?php

/**
 * ActivityDealerMailsSends form base class.
 *
 * @method ActivityDealerMailsSends getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityDealerMailsSendsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'dealer_id'   => new sfWidgetFormInputText(),
      'activity_id' => new sfWidgetFormInputText(),
      'mail_type'   => new sfWidgetFormInputText(),
      'msg'         => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'dealer_id'   => new sfValidatorInteger(),
      'activity_id' => new sfValidatorInteger(),
      'mail_type'   => new sfValidatorString(array('max_length' => 30)),
      'msg'         => new sfValidatorString(),
    ));

    $this->widgetSchema->setNameFormat('activity_dealer_mails_sends[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityDealerMailsSends';
  }

}
