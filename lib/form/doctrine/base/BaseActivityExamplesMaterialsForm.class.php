<?php

/**
 * BaseActivityExamplesMateriels form base class.
 *
 * @method BaseActivityExamplesMaterials getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityExamplesMaterialsForm extends BaseFormDoctrine
{
    public function setup()
    {
        $this->setWidgets(array(
            'id' => new sfWidgetFormInputHidden(),
            'dealer_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Dealer'), 'add_empty' => false)),
            'name' => new sfWidgetFormInputText(),
            'description' => new sfWidgetFormTextarea(),
            'category_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ActivityExamplesMaterialsCategories'), 'add_empty' => false)),
            'year' => new sfWidgetFormChoice(array('choices' => array(), 'multiple' => true)),
            'preview_file' => new sfWidgetFormInputText(),
            'material_file' => new sfWidgetFormInputText(),
        ));

        $this->setValidators(array(
            'id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
            'dealer_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Dealer'), 'required' => true)),
            'name' => new sfValidatorString(array('max_length' => 255, 'required' => true)),
            'description' => new sfValidatorString(array('required' => false, 'required' => false)),
            'category_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ActivityExamplesMaterialsCategories'), 'required' => true)),
            'year' => new sfValidatorChoice(array('choices' => '', 'required' => true)),
            'preview_file' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
            'material_file' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
        ));

        $this->widgetSchema->setNameFormat('activity_examples_materials[%s]');

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->setupInheritance();

        parent::setup();
    }

    public function getModelName()
    {
        return 'ActivityExamplesMaterials';
    }

}
