<?php

/**
 * ActivityFieldsDateField form base class.
 *
 * @method ActivityFieldsDateField getObject() Returns the current form's model object
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseActivityFieldsDateFieldForm extends ActivityFieldsForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema->setNameFormat('activity_fields_date_field[%s]');
  }

  public function getModelName()
  {
    return 'ActivityFieldsDateField';
  }

}
