<?php

/**
 * AgreementModelListField filter form base class.
 *
 * @package    Servicepool2.0
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseAgreementModelListFieldFormFilter extends AgreementModelFieldFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema->setNameFormat('agreement_model_list_field_filters[%s]');
  }

  public function getModelName()
  {
    return 'AgreementModelListField';
  }
}