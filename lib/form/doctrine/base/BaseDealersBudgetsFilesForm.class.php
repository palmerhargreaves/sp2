<?php

/**
 * DealersBudgetsFiles form base class.
 *
 * @method DealersBudgetsFiles getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseDealersBudgetsFilesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'file_name'      => new sfWidgetFormInputText(),
      'total_dealers'  => new sfWidgetFormInputText(),
      'year'           => new sfWidgetFormInputText(),
      'budget_of_year' => new sfWidgetFormInputText(),
      'budget_of_q1'   => new sfWidgetFormInputText(),
      'budget_of_q2'   => new sfWidgetFormInputText(),
      'budget_of_q3'   => new sfWidgetFormInputText(),
      'budget_of_q4'   => new sfWidgetFormInputText(),
      'status'         => new sfWidgetFormInputCheckbox(),
      'created_at'     => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'file_name'      => new sfValidatorString(array('max_length' => 255)),
      'total_dealers'  => new sfValidatorInteger(),
      'year'           => new sfValidatorInteger(),
      'budget_of_year' => new sfValidatorNumber(array('required' => false)),
      'budget_of_q1'   => new sfValidatorNumber(array('required' => false)),
      'budget_of_q2'   => new sfValidatorNumber(array('required' => false)),
      'budget_of_q3'   => new sfValidatorNumber(array('required' => false)),
      'budget_of_q4'   => new sfValidatorNumber(array('required' => false)),
      'status'         => new sfValidatorBoolean(array('required' => false)),
      'created_at'     => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('dealers_budgets_files[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DealersBudgetsFiles';
  }

}
