<?php

/**
 * AgreementModelReportFavorites form base class.
 *
 * @method AgreementModelReportFavorites getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAgreementModelReportFavoritesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                   => new sfWidgetFormInputHidden(),
      'report_id'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Report'), 'add_empty' => true)),
      'file_id'              => new sfWidgetFormInputText(),
      'report_model_type_id' => new sfWidgetFormInputText(),
      'file_name'            => new sfWidgetFormInputText(),
      'file_index'           => new sfWidgetFormInputText(),
      'user_id'              => new sfWidgetFormInputText(),
      'created_at'           => new sfWidgetFormDateTime(),
      'updated_at'           => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                   => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'report_id'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Report'), 'required' => false)),
      'file_id'              => new sfValidatorInteger(array('required' => false)),
      'report_model_type_id' => new sfValidatorInteger(array('required' => false)),
      'file_name'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'file_index'           => new sfValidatorInteger(array('required' => false)),
      'user_id'              => new sfValidatorInteger(array('required' => false)),
      'created_at'           => new sfValidatorDateTime(),
      'updated_at'           => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('agreement_model_report_favorites[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AgreementModelReportFavorites';
  }

}
