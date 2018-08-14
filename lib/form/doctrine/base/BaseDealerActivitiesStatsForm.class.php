<?php

/**
 * DealerActivitiesStats form base class.
 *
 * @method DealerActivitiesStats getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseDealerActivitiesStatsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                   => new sfWidgetFormInputHidden(),
      'manager_id'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ManagerStat'), 'add_empty' => false)),
      'dealer_id'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('DealerStat'), 'add_empty' => false)),
      'percent_of_budget'    => new sfWidgetFormInputText(),
      'models_completed'     => new sfWidgetFormInputText(),
      'activities_completed' => new sfWidgetFormInputText(),
      'q1'                   => new sfWidgetFormInputText(),
      'q2'                   => new sfWidgetFormInputText(),
      'q3'                   => new sfWidgetFormInputText(),
      'q4'                   => new sfWidgetFormInputText(),
      'q_activity1'          => new sfWidgetFormInputText(),
      'q_activity2'          => new sfWidgetFormInputText(),
      'q_activity3'          => new sfWidgetFormInputText(),
      'q_activity4'          => new sfWidgetFormInputText(),
      'created_at'           => new sfWidgetFormDateTime(),
      'updated_at'           => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                   => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'manager_id'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ManagerStat'))),
      'dealer_id'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('DealerStat'))),
      'percent_of_budget'    => new sfValidatorNumber(),
      'models_completed'     => new sfValidatorInteger(),
      'activities_completed' => new sfValidatorInteger(),
      'q1'                   => new sfValidatorInteger(),
      'q2'                   => new sfValidatorInteger(),
      'q3'                   => new sfValidatorInteger(),
      'q4'                   => new sfValidatorInteger(),
      'q_activity1'          => new sfValidatorInteger(),
      'q_activity2'          => new sfValidatorInteger(),
      'q_activity3'          => new sfValidatorInteger(),
      'q_activity4'          => new sfValidatorInteger(),
      'created_at'           => new sfValidatorDateTime(),
      'updated_at'           => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('dealer_activities_stats[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DealerActivitiesStats';
  }

}
