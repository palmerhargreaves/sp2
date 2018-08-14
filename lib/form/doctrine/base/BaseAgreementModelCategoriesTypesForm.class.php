<?php

/**
 * AgreementModelCategoriesTypes form base class.
 *
 * @method AgreementModelCategoriesTypes getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAgreementModelCategoriesTypesForm extends BaseFormDoctrine
{
    public function setup()
    {
        $this->setWidgets(array(
            'id' => new sfWidgetFormInputHidden(),
            'parent_category_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('AgreementModelCategories'), 'add_empty' => false)),
            'name' => new sfWidgetFormInputText(),
            'description' => new sfWidgetFormTextarea(),
            'identifier' => new sfWidgetFormInputText(),
            'concept' => new sfWidgetFormInputCheckbox(),
            'is_photo_report' => new sfWidgetFormInputCheckbox(),
            'status' => new sfWidgetFormInputCheckbox(),
        ));

        $this->setValidators(array(
            'id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
            'parent_category_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('AgreementModelCategories'), 'required' => true)),
            'name' => new sfValidatorString(array('max_length' => 255, 'required' => true)),
            'description' => new sfValidatorString(array('required' => false)),
            'identifier' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
            'concept' => new sfValidatorBoolean(array('required' => false)),
            'is_photo_report' => new sfValidatorBoolean(array('required' => false)),
            'status' => new sfValidatorBoolean(array('required' => false)),
        ));

        $this->widgetSchema->setNameFormat('agreement_model_categories_types[%s]');
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

        $this->setupInheritance();

        parent::setup();
    }

    public function getModelName()
    {
        return 'AgreementModelType';
    }

}
