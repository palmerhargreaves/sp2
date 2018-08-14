<?php

/**
 * ActivityModelsTypesNecessarily form.
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ActivityModelsTypesNecessarilyForm extends BaseActivityModelsTypesNecessarilyForm
{
    public function configure()
    {
        $this->widgetSchema['activity_id'] = new sfWidgetFormInputHidden();

        /*$this->widgetSchema['activity_task_id'] = new sfWidgetFormDoctrineChoice(array('model' => 'ActivityTask', 'query' => Doctrine::getTable('ActivityTask')->getTaskList($this->getObject()->getId()), 'add_empty' => false, 'multiple' => false, 'method' => 'getName'));
        $this->validatorsSchema['activity_task_id'] = new sfValidatorDoctrineChoice(array('model' => 'ActivityTask', 'multiple' => false, 'required' => true));*/

        foreach ($this->validatorSchema->getFields() as $validator) {
            $validator->setMessage('required', 'Обязательно для заполнения');
        }
    }

    protected function doBind(array $values)
    {
        $this->resetFormFields();

        $this->widgetSchema['activity_task_id'] = new sfWidgetFormDoctrineChoice(array('model' => 'ActivityTask', 'query' => Doctrine::getTable('ActivityTask')->getTaskList($values['activity_id']), 'add_empty' => false, 'multiple' => false, 'method' => 'getName'));
        $this->validatorsSchema['activity_task_id'] = new sfValidatorDoctrineChoice(array('model' => 'ActivityTask', 'multiple' => false, 'required' => true));

        $this->widgetSchema['model_type_id'] = new sfWidgetFormDoctrineChoice(array('model' => 'AgreementModelType', 'query' => Doctrine::getTable('AgreementModelType')->getAvailTypes($values['activity_id']), 'add_empty' => false, 'method' => 'getTypeLabel'));
        $this->validatorSchema['model_type_id'] = new sfValidatorDoctrineChoice(array('model' => 'AgreementModelType', 'multiple' => false, 'required' => true));

        parent::doBind($values);
    }
}
