<?php

/**
 * UsersDepartments form base class.
 *
 * @method UsersDepartments getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseUsersDepartmentsForm extends BaseFormDoctrine
{
    public function setup()
    {
        $this->setWidgets(array(
            'id' => new sfWidgetFormInputHidden(),
            'name' => new sfWidgetFormInputText(),
            'parent_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('UserDepartment'), 'add_empty' => true)),
            'allow_emails' => new sfWidgetFormInputCheckbox(),
        ));

        $this->setValidators(array(
            'id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
            'name' => new sfValidatorString(array('max_length' => 255, 'required' => true)),
            'parent_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('UserDepartment'), 'required' => false)),
            'allow_emails' => new sfValidatorBoolean(array('required' => false)),
        ));

        $this->widgetSchema->setNameFormat('users_departments[%s]');

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

        $this->setupInheritance();

        parent::setup();
    }

    public function getModelName()
    {
        return 'UsersDepartments';
    }

}
