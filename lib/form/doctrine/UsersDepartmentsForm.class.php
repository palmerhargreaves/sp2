<?php

/**
 * UsersDepartments form.
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class UsersDepartmentsForm extends BaseUsersDepartmentsForm
{
    public function configure()
    {
        unset($this['created_at']);

        $this->widgetSchema['parent_id'] = new sfWidgetFormDoctrineChoice(array('model' => 'UsersDepartments', 'query' => Doctrine::getTable('UsersDepartments')->getDepartments(), 'add_empty' => true, 'multiple' => false));
        $this->validatorsSchema['parent_id'] = new sfValidatorDoctrineChoice(array('model' => 'UsersDepartments', 'multiple' => false, 'required' => false));
    }
}
