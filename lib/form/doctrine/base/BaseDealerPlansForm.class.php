<?php

/**
 * DealerPlans form base class.
 *
 * @method DealerPlans getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseDealerPlansForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'dealer_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Mailings'), 'add_empty' => false)),
      'name'       => new sfWidgetFormInputText(),
      'plan1'      => new sfWidgetFormInputText(),
      'plan2'      => new sfWidgetFormInputText(),
      'added_date' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'dealer_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Mailings'))),
      'name'       => new sfValidatorString(array('max_length' => 255)),
      'plan1'      => new sfValidatorInteger(),
      'plan2'      => new sfValidatorInteger(),
      'added_date' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('dealer_plans[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DealerPlans';
  }

}
