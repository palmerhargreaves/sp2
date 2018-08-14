<?php

/**
 * MainMenuItemsRules form base class.
 *
 * @method MainMenuItemsRules getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseMainMenuItemsRulesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'menu_item_id'      => new sfWidgetFormInputText(),
      'users_rules'       => new sfWidgetFormInputText(),
      'users_extra_rules' => new sfWidgetFormInputText(),
      'users_departments' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'menu_item_id'      => new sfValidatorInteger(),
      'users_rules'       => new sfValidatorString(array('max_length' => 255)),
      'users_extra_rules' => new sfValidatorString(array('max_length' => 255)),
      'users_departments' => new sfValidatorString(array('max_length' => 255)),
    ));

    $this->widgetSchema->setNameFormat('main_menu_items_rules[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'MainMenuItemsRules';
  }

}
