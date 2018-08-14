<?php

/**
 * DialogsUsersLimits form base class.
 *
 * @method DialogsUsersLimits getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseDialogsUsersLimitsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'        => new sfWidgetFormInputHidden(),
      'dialog_id' => new sfWidgetFormInputText(),
      'user_id'   => new sfWidgetFormInputText(),
      'dealer_id' => new sfWidgetFormInputText(),
      'post_type' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'        => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'dialog_id' => new sfValidatorInteger(),
      'user_id'   => new sfValidatorInteger(),
      'dealer_id' => new sfValidatorInteger(),
      'post_type' => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('dialogs_users_limits[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DialogsUsersLimits';
  }

}
