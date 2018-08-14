<?php

/**
 * DealersGroups form base class.
 *
 * @method DealersGroups getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseDealersGroupsForm extends BaseFormDoctrine
{
    public function setup()
    {
        $this->setWidgets(array(
            'id' => new sfWidgetFormInputHidden(),
            'header' => new sfWidgetFormInputText(),
            'description' => new sfWidgetFormInputText(),
            'status' => new sfWidgetFormInputCheckbox(),
            'created_at' => new sfWidgetFormDateTime(),
        ));

        $this->setValidators(array(
            'id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
            'header' => new sfValidatorString(array('max_length' => 255, 'required' => true)),
            'description' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
            'status' => new sfValidatorBoolean(array('required' => false)),
            'created_at' => new sfValidatorDateTime(),
        ));

        $this->widgetSchema->setNameFormat('dealers_groups[%s]');

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

        $this->setupInheritance();

        parent::setup();
    }

    public function getModelName()
    {
        return 'DealersGroups';
    }

}
