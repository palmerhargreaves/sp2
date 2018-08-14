<?php

/**
 * ActivityExamplesMaterialsCategories form.
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ActivityExamplesMaterialsCategoriesForm extends BaseActivityExamplesMaterialsCategoriesForm
{
    public function configure()
    {

        $this->widgetSchema['parent_category_id'] = new sfWidgetFormDoctrineChoice(array('model' => 'ActivityExamplesMaterialsCategories', 'query' => Doctrine::getTable('ActivityExamplesMaterialsCategories')->getCategoriesList(!$this->getObject()->isNew() ? $this->getObject()->getId() : 0), 'add_empty' => true, 'method' => 'getFormattedName'));
        $this->validatorsSchema['parent_category_id'] = new sfValidatorDoctrineChoice(array('model' => 'ActivityExamplesMaterialsCategories', 'required' => true));

        $this->getWidget('parent_category_id')->setAttribute('class', 'example-parent-category');

        foreach ($this->validatorSchema->getFields() as $validator) {
            $validator->setMessage('required', 'Обязательно для заполнения');
        }
    }

}
