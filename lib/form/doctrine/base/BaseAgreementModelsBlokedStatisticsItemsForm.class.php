<?php

/**
 * AgreementModelsBlokedStatisticsItems form base class.
 *
 * @method AgreementModelsBlokedStatisticsItems getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAgreementModelsBlokedStatisticsItemsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'parent_id'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('BlockedStatistic'), 'add_empty' => false)),
      'type'                => new sfWidgetFormChoice(array('choices' => array('active' => 'active', 'blocked' => 'blocked'))),
      'user_id'             => new sfWidgetFormInputText(),
      'blocked_with_report' => new sfWidgetFormInputCheckbox(),
      'created_at'          => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'parent_id'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('BlockedStatistic'))),
      'type'                => new sfValidatorChoice(array('choices' => array(0 => 'active', 1 => 'blocked'))),
      'user_id'             => new sfValidatorInteger(),
      'blocked_with_report' => new sfValidatorBoolean(array('required' => false)),
      'created_at'          => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('agreement_models_bloked_statistics_items[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AgreementModelsBlokedStatisticsItems';
  }

}
