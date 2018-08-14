<?php

/**
 * MainMenuItems form base class.
 *
 * @method MainMenuItems getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseMainMenuItemsForm extends BaseFormDoctrine
{
    public function setup()
    {
        $this->setWidgets(array(
            'id' => new sfWidgetFormInputHidden(),
            'name' => new sfWidgetFormInputText(),
            'url' => new sfWidgetFormInputText(),
            'url_name' => new sfWidgetFormInputText(),
            'custom_code_access' => new sfWidgetFormTextarea(),
            'custom_code_url' => new sfWidgetFormTextarea(),
            'image' => new sfWidgetFormInputText(),
            'status' => new sfWidgetFormInputCheckbox(),
        ));

        $this->setValidators(array(
            'id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
            'name' => new sfValidatorString(array('max_length' => 255)),
            'url' => new sfValidatorString(array('max_length' => 255)),
            'url_name' => new sfValidatorString(array('max_length' => 255)),
            'custom_code_access' => new sfValidatorString(array('required' => false)),
            'custom_code_url' => new sfValidatorString(array('required' => false)),
            'image' => new sfValidatorString(array('max_length' => 255)),
            'status' => new sfValidatorBoolean(array('required' => false)),
        ));

        $this->widgetSchema->setNameFormat('main_menu_items[%s]');

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

        $this->setupInheritance();

        parent::setup();
    }


    public function getModelName()
    {
        return 'MainMenuItems';
    }

}
