<?php

/**
 * AgreementModelCategoriesForm form base class.
 *
 * @method AgreementModelCategoriesForm getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAgreementModelCategoriesForm extends BaseFormDoctrine
{
    public function setup()
    {
        $this->setWidgets(array(
            'id' => new sfWidgetFormInputHidden(),
            'name' => new sfWidgetFormInputText(),
            'description' => new sfWidgetFormTextarea(),
            'work_type' => new sfWidgetFormChoice(array('choices' => array('manager' => 'Менеджер', 'manager_designer' => 'Менеджер / Дизайнер'))),
            'days_to_agreement' => new sfWidgetFormInputText(),
            'days_to_agreement_report' => new sfWidgetFormInputText(),
            'status' => new sfWidgetFormInputCheckbox(),
        ));

        $this->setValidators(array(
            'id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
            'name' => new sfValidatorString(array('max_length' => 255, 'required' => true)),
            'description' => new sfValidatorString(array('required' => false)),
            'work_type' => new sfValidatorChoice(array('choices' => array(0 => 'manager', 1 => 'manager_designer'))),
            'days_to_agreement' => new sfValidatorString(array('max_length' => 255, 'required' => true)),
            'days_to_agreement_report' => new sfValidatorString(array('max_length' => 255, 'required' => true)),
            'status' => new sfValidatorBoolean(array('required' => false)),
        ));

        $this->widgetSchema->setNameFormat('agreement_model_categories[%s]');
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

        $this->setupInheritance();

        parent::setup();
    }

    public function getModelName()
    {
        return 'AgreementModelCategories';
    }

}
