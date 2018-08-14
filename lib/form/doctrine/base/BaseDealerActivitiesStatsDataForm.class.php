<?php

/**
 * DealerActivitiesStatsData form base class.
 *
 * @method DealerActivitiesStatsData getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseDealerActivitiesStatsDataForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                      => new sfWidgetFormInputHidden(),
      'activity_id'             => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Activity'), 'add_empty' => false)),
      'dealer_stat_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('DealerActivitiesStats'), 'add_empty' => false)),
      'status'                  => new sfWidgetFormInputText(),
      'value'                   => new sfWidgetFormInputText(),
      'field_name'              => new sfWidgetFormInputText(),
      'year'                    => new sfWidgetFormInputText(),
      'q1'                      => new sfWidgetFormInputText(),
      'q2'                      => new sfWidgetFormInputText(),
      'q3'                      => new sfWidgetFormInputText(),
      'q4'                      => new sfWidgetFormInputText(),
      'total_completed'         => new sfWidgetFormInputText(),
      'total_in_work_dealers'   => new sfWidgetFormInputText(),
      'total_completed_dealers' => new sfWidgetFormInputText(),
      'created_at'              => new sfWidgetFormDateTime(),
      'updated_at'              => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                      => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'activity_id'             => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Activity'))),
      'dealer_stat_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('DealerActivitiesStats'))),
      'status'                  => new sfValidatorString(array('max_length' => 20)),
      'value'                   => new sfValidatorInteger(),
      'field_name'              => new sfValidatorString(array('max_length' => 255)),
      'year'                    => new sfValidatorInteger(),
      'q1'                      => new sfValidatorInteger(),
      'q2'                      => new sfValidatorInteger(),
      'q3'                      => new sfValidatorInteger(),
      'q4'                      => new sfValidatorInteger(),
      'total_completed'         => new sfValidatorInteger(),
      'total_in_work_dealers'   => new sfValidatorInteger(),
      'total_completed_dealers' => new sfValidatorInteger(),
      'created_at'              => new sfValidatorDateTime(),
      'updated_at'              => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('dealer_activities_stats_data[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DealerActivitiesStatsData';
  }

}
