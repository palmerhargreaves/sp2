<?php

/**
 * ActivityVideoRecordsStatisticsHeadersFieldsData form base class.
 *
 * @method ActivityVideoRecordsStatisticsHeadersFieldsData getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityVideoRecordsStatisticsHeadersFieldsDataForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'field_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ActivityVideoRecordsStatisticsHeadersFields'), 'add_empty' => false)),
      'user_id'    => new sfWidgetFormInputText(),
      'dealer_id'  => new sfWidgetFormInputText(),
      'quarter'    => new sfWidgetFormInputText(),
      'year'       => new sfWidgetFormInputText(),
      'value'      => new sfWidgetFormTextarea(),
      'created_at' => new sfWidgetFormDateTime(),
      'updated_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'field_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ActivityVideoRecordsStatisticsHeadersFields'))),
      'user_id'    => new sfValidatorInteger(),
      'dealer_id'  => new sfValidatorInteger(),
      'quarter'    => new sfValidatorInteger(),
      'year'       => new sfValidatorInteger(),
      'value'      => new sfValidatorString(),
      'created_at' => new sfValidatorDateTime(),
      'updated_at' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('activity_video_records_statistics_headers_fields_data[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityVideoRecordsStatisticsHeadersFieldsData';
  }

}
