<?php

/**
 * MailingList form base class.
 *
 * @method MailingList getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseMailingListForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'dealer_id'        => new sfWidgetFormInputText(),
      'first_name'       => new sfWidgetFormInputText(),
      'last_name'        => new sfWidgetFormInputText(),
      'middle_name'      => new sfWidgetFormInputText(),
      'gender'           => new sfWidgetFormInputText(),
      'phone'            => new sfWidgetFormInputText(),
      'email'            => new sfWidgetFormInputText(),
      'last_visit_date'  => new sfWidgetFormTextarea(),
      'last_upload_data' => new sfWidgetFormTextarea(),
      'vin'              => new sfWidgetFormTextarea(),
      'added_date'       => new sfWidgetFormDateTime(),
      'model'            => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'dealer_id'        => new sfValidatorInteger(),
      'first_name'       => new sfValidatorString(array('max_length' => 255)),
      'last_name'        => new sfValidatorString(array('max_length' => 255)),
      'middle_name'      => new sfValidatorString(array('max_length' => 255)),
      'gender'           => new sfValidatorString(array('max_length' => 10)),
      'phone'            => new sfValidatorString(array('max_length' => 255)),
      'email'            => new sfValidatorString(array('max_length' => 255)),
      'last_visit_date'  => new sfValidatorString(),
      'last_upload_data' => new sfValidatorString(),
      'vin'              => new sfValidatorString(),
      'added_date'       => new sfValidatorDateTime(),
      'model'            => new sfValidatorString(),
    ));

    $this->widgetSchema->setNameFormat('mailing_list[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'MailingList';
  }

}
