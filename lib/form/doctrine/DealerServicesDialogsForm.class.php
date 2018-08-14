<?php

/**
 * DealerServicesDialogs form.
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class DealerServicesDialogsForm extends BaseDealerServicesDialogsForm
{
    public function configure()
    {
        unset($this['created_at'], $this['updated_at']);

        $this->widgetSchema['template'] = new sfWidgetFormDoctrineChoice(array('model' => 'DealersServicesDialogTemplates', 'method' => 'getHeader', 'add_empty' => true));
        $this->validatorsSchema['template'] = new sfValidatorDoctrineChoice(array('model' => 'DealersServicesDialogTemplates', 'multiple' => true, 'required' => false));

        $this->widgetSchema['activity_id'] = new sfWidgetFormDoctrineChoice(array('model' => 'Activity', 'query' => Doctrine::getTable('Activity')->getActivitesList(), 'add_empty' => true, 'multiple' => false, 'method' => 'getIdName'));
        $this->validatorsSchema['activity_id'] = new sfValidatorDoctrineChoice(array('model' => 'Activity', 'multiple' => false, 'required' => false));
    }
}
