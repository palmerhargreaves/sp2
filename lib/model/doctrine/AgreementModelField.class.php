<?php

/**
 * AgreementModelField
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class AgreementModelField extends BaseAgreementModelField
{
    const IDENTIFIER_PERIOD_FIELD = 'period';

    public function isPeriodField() {
        return $this->getType() == AgreementModelField::IDENTIFIER_PERIOD_FIELD;
    }

    public function canAddChildFields() {
        return AgreementModelFieldTable::getInstance()->createQuery()->where('field_parent_id = ? and hide = ?', array($this->getId(), true))->count() > 0 ? true : false;
    }
}
