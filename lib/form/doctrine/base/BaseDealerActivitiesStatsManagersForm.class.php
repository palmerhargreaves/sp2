<?php

/**
 * DealerActivitiesStatsManagers form base class.
 *
 * @method DealerActivitiesStatsManagers getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseDealerActivitiesStatsManagersForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'manager_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Manager'), 'add_empty' => false)),
      'year'       => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'manager_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Manager'))),
      'year'       => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('dealer_activities_stats_managers[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DealerActivitiesStatsManagers';
  }

}
