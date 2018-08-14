<?php

/**
 * ActivityDealerStaticticStatus form base class.
 *
 * @method ActivityDealerStaticticStatus getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityDealerStaticticStatusForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'dealer_id'           => new sfWidgetFormInputText(),
      'activity_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Activity'), 'add_empty' => true)),
      'stat_type'           => new sfWidgetFormChoice(array('choices' => array('simple' => 'simple', 'extended' => 'extended'))),
      'q1'                  => new sfWidgetFormInputText(),
      'q2'                  => new sfWidgetFormInputText(),
      'q3'                  => new sfWidgetFormInputText(),
      'q4'                  => new sfWidgetFormInputText(),
      'concept_id'          => new sfWidgetFormInputText(),
      'year'                => new sfWidgetFormInputText(),
      'complete'            => new sfWidgetFormInputCheckbox(),
      'always_open'         => new sfWidgetFormInputCheckbox(),
      'ignore_statistic'    => new sfWidgetFormInputCheckbox(),
      'ignore_q1_statistic' => new sfWidgetFormInputCheckbox(),
      'ignore_q2_statistic' => new sfWidgetFormInputCheckbox(),
      'ignore_q3_statistic' => new sfWidgetFormInputCheckbox(),
      'ignore_q4_statistic' => new sfWidgetFormInputCheckbox(),
      'created_at'          => new sfWidgetFormDateTime(),
      'updated_at'          => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'dealer_id'           => new sfValidatorInteger(array('required' => false)),
      'activity_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Activity'), 'required' => false)),
      'stat_type'           => new sfValidatorChoice(array('choices' => array(0 => 'simple', 1 => 'extended'))),
      'q1'                  => new sfValidatorInteger(array('required' => false)),
      'q2'                  => new sfValidatorInteger(array('required' => false)),
      'q3'                  => new sfValidatorInteger(array('required' => false)),
      'q4'                  => new sfValidatorInteger(array('required' => false)),
      'concept_id'          => new sfValidatorInteger(array('required' => false)),
      'year'                => new sfValidatorInteger(array('required' => false)),
      'complete'            => new sfValidatorBoolean(array('required' => false)),
      'always_open'         => new sfValidatorBoolean(array('required' => false)),
      'ignore_statistic'    => new sfValidatorBoolean(array('required' => false)),
      'ignore_q1_statistic' => new sfValidatorBoolean(array('required' => false)),
      'ignore_q2_statistic' => new sfValidatorBoolean(array('required' => false)),
      'ignore_q3_statistic' => new sfValidatorBoolean(array('required' => false)),
      'ignore_q4_statistic' => new sfValidatorBoolean(array('required' => false)),
      'created_at'          => new sfValidatorDateTime(),
      'updated_at'          => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('activity_dealer_statictic_status[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityDealerStaticticStatus';
  }

}
