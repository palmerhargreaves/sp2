<?php

/**
 * ActivityVideoRecordsStatisticsHeadersGroupsFields form base class.
 *
 * @method ActivityVideoRecordsStatisticsHeadersGroupsFields getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityVideoRecordsStatisticsHeadersGroupsFieldsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'group_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ActivityVideoRecordsStatisticsHeadersGroups'), 'add_empty' => false)),
      'field_id'   => new sfWidgetFormInputText(),
      'created_at' => new sfWidgetFormDateTime(),
      'updated_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'group_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ActivityVideoRecordsStatisticsHeadersGroups'))),
      'field_id'   => new sfValidatorInteger(),
      'created_at' => new sfValidatorDateTime(),
      'updated_at' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('activity_video_records_statistics_headers_groups_fields[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityVideoRecordsStatisticsHeadersGroupsFields';
  }

}
