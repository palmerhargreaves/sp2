<?php

/**
 * ActivityModelsTypesNecessarily form base class.
 *
 * @method ActivityModelsTypesNecessarily getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityModelsTypesNecessarilyForm extends BaseFormDoctrine
{
    public function setup()
    {
        $this->setWidgets(array(
            'id' => new sfWidgetFormInputHidden(),
            'model_type_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('AgreementModelType'), 'add_empty' => false)),
            'activity_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Activity'), 'add_empty' => false)),
            'activity_task_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ActivityTask'), 'add_empty' => false)),
        ));

        $this->setValidators(array(
            'id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
            'model_type_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('AgreementModelType'), 'required' => true)),
            'activity_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Activity'), 'required' => true)),
            'activity_task_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ActivityTask'), 'required' => true)),
        ));

        $this->widgetSchema->setNameFormat('activity_models_types_necessarily[%s]');

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

        $this->setupInheritance();

        parent::setup();
    }

    public function getModelName()
    {
        return 'ActivityModelsTypesNecessarily';
    }

}
