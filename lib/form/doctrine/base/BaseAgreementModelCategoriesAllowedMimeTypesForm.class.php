<?php

/**
 * AgreementModelCategoriesAllowedMimeTypes form base class.
 *
 * @method AgreementModelCategoriesAllowedMimeTypes getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAgreementModelCategoriesAllowedMimeTypesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'category_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Category'), 'add_empty' => false)),
      'mime_type_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('MimeType'), 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'category_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Category'))),
      'mime_type_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('MimeType'))),
    ));

    $this->widgetSchema->setNameFormat('agreement_model_categories_allowed_mime_types[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AgreementModelCategoriesAllowedMimeTypes';
  }

}
