<?php

/**
 * AgreementModelReportAcceptDeclineFiles form base class.
 *
 * @method AgreementModelReportAcceptDeclineFiles getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAgreementModelReportAcceptDeclineFilesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'report_id'  => new sfWidgetFormInputText(),
      'file_type'  => new sfWidgetFormChoice(array('choices' => array('accept' => 'accept', 'decline' => 'decline'))),
      'file_name'  => new sfWidgetFormInputText(),
      'created_at' => new sfWidgetFormDateTime(),
      'updated_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'report_id'  => new sfValidatorInteger(),
      'file_type'  => new sfValidatorChoice(array('choices' => array(0 => 'accept', 1 => 'decline'), 'required' => false)),
      'file_name'  => new sfValidatorString(array('max_length' => 255)),
      'created_at' => new sfValidatorDateTime(),
      'updated_at' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('agreement_model_report_accept_decline_files[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AgreementModelReportAcceptDeclineFiles';
  }

}
