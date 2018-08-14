<?php

/**
 * ActivityExtendedStatisticSections form base class.
 *
 * @method ActivityExtendedStatisticSections getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityExtendedStatisticSectionsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'parent_id'   => new sfWidgetFormInputText(),
      'activity_id' => new sfWidgetFormInputText(),
      'status'      => new sfWidgetFormInputText(),
      'header'      => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'parent_id'   => new sfValidatorInteger(array('required' => false)),
      'activity_id' => new sfValidatorInteger(array('required' => false)),
      'status'      => new sfValidatorInteger(array('required' => false)),
      'header'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('activity_extended_statistic_sections[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityExtendedStatisticSections';
  }

}
