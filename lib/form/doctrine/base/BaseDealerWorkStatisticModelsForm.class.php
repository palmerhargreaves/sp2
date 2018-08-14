<?php

/**
 * DealerWorkStatisticModels form base class.
 *
 * @method DealerWorkStatisticModels getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseDealerWorkStatisticModelsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'model_id'    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('AgreementModel'), 'add_empty' => false)),
      'model_cost'  => new sfWidgetFormInputText(),
      'activity_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Activity'), 'add_empty' => false)),
      'parent_id'   => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'model_id'    => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('AgreementModel'))),
      'model_cost'  => new sfValidatorNumber(),
      'activity_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Activity'))),
      'parent_id'   => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('dealer_work_statistic_models[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DealerWorkStatisticModels';
  }

}
