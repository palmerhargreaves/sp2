<?php

/**
 * AgreementModelReportFiles form base class.
 *
 * @method AgreementModelReportFiles getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAgreementModelReportFilesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'object_id'        => new sfWidgetFormInputText(),
      'activity_id'      => new sfWidgetFormInputText(),
      'object_type'      => new sfWidgetFormChoice(array('choices' => array('model' => 'model', 'report' => 'report'))),
      'file_type'        => new sfWidgetFormChoice(array('choices' => array('model' => 'model', 'model_record' => 'model_record', 'model_scenario' => 'model_scenario', 'report' => 'report', 'report_additional' => 'report_additional', 'report_financial' => 'report_financial', 'report_additional_ext' => 'report_additional_ext'))),
      'file'             => new sfWidgetFormInputText(),
      'file_size'        => new sfWidgetFormInputText(),
      'user_id'          => new sfWidgetFormInputText(),
      'file_mime_type'   => new sfWidgetFormInputText(),
      'download_count'   => new sfWidgetFormInputText(),
      'field'            => new sfWidgetFormTextarea(),
      'field_name'       => new sfWidgetFormTextarea(),
      'is_external_file' => new sfWidgetFormInputCheckbox(),
      'user_agent'       => new sfWidgetFormInputText(),
      'path'             => new sfWidgetFormInputText(),
      'created_at'       => new sfWidgetFormDateTime(),
      'updated_at'       => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'object_id'        => new sfValidatorInteger(),
      'activity_id'      => new sfValidatorInteger(),
      'object_type'      => new sfValidatorChoice(array('choices' => array(0 => 'model', 1 => 'report'))),
      'file_type'        => new sfValidatorChoice(array('choices' => array(0 => 'model', 1 => 'model_record', 2 => 'model_scenario', 3 => 'report', 4 => 'report_additional', 5 => 'report_financial', 6 => 'report_additional_ext'))),
      'file'             => new sfValidatorString(array('max_length' => 255)),
      'file_size'        => new sfValidatorString(array('max_length' => 80)),
      'user_id'          => new sfValidatorInteger(),
      'file_mime_type'   => new sfValidatorString(array('max_length' => 160, 'required' => false)),
      'download_count'   => new sfValidatorInteger(array('required' => false)),
      'field'            => new sfValidatorString(array('required' => false)),
      'field_name'       => new sfValidatorString(array('required' => false)),
      'is_external_file' => new sfValidatorBoolean(),
      'user_agent'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'path'             => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'created_at'       => new sfValidatorDateTime(),
      'updated_at'       => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('agreement_model_report_files[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AgreementModelReportFiles';
  }

}
