<?php

/**
 * User filter form.
 *
 * @package    Servicepool2.0
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class UserFormFilter extends BaseUserFormFilter
{
    public function configure()
    {
        $this->widgetSchema['bind_dealers_list'] = new sfWidgetFormDoctrineChoice(array('model' => 'Dealer', 'query' => Doctrine::getTable('Dealer')->getAllDealers(), 'add_empty' => true, 'multiple' => false, 'method' => 'getNameAndNumber'));
        $this->validatorsSchema['bind_dealers_list'] = new sfValidatorDoctrineChoice(array('model' => 'Dealer', 'multiple' => false, 'required' => false));

        $this->widgetSchema['company_departments'] = new sfWidgetFormDoctrineChoice(array('model' => 'Department', 'query' => Doctrine::getTable('UsersDepartments')->getDepartments(), 'add_empty' => true, 'multiple' => false));
        $this->validatorsSchema['company_departments'] = new sfValidatorDoctrineChoice(array('model' => 'Department', 'multiple' => false, 'required' => false));
    }
}
