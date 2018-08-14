<?php

/**
 * BaseActivityExamplesMaterialsCategoriesForm form base class.
 *
 * @method BaseActivityExamplesMaterialsCategoriesForm getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityExamplesMaterialsCategoriesForm extends BaseFormDoctrine
{
    public function setup()
    {
        $this->setWidgets(array(
            'id' => new sfWidgetFormInputHidden(),
            'name' => new sfWidgetFormInputText(),
            'description' => new sfWidgetFormTextarea(),
            'parent_category_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ActivityExamplesMaterialsCategories'), 'add_empty' => true)),
            'status' => new sfWidgetFormInputCheckbox(),
        ));

        $this->setValidators(array(
            'id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
            'name' => new sfValidatorString(array('max_length' => 255, 'required' => true)),
            'description' => new sfValidatorString(array('required' => false, 'required' => false)),
            'parent_category_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ActivityExamplesMaterialsCategories'), 'required' => false)),
            'status' => new sfValidatorBoolean(array('required' => false)),
        ));

        $this->widgetSchema->setNameFormat('activity_examples_materials_categories[%s]');

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->setupInheritance();

        parent::setup();
    }

    public function getModelName()
    {
        return 'ActivityExamplesMaterialsCategories';
    }

}
