<?php

/**
 * AgreementModelType
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class AgreementModelType extends BaseAgreementModelType
{
    private $AGREEMENT_TYPES = array('simple' => 'Обычный', 'by_steps_2' => 'Сценарий / Запись');

    const SIMPLE_TYPE = 'simple';

    function hasAdditionalFile()
    {
        return !!$this->getReportFieldDescription();
    }

    public function getAgreementTypeLabel() {
        return array_key_exists($this->getAgreementType(), $this->AGREEMENT_TYPES) ? $this->AGREEMENT_TYPES[$this->getAgreementType()] : '';
    }

    public function isScenarioRecord() {
        return $this->getAgreementType() == 'simple' ? false : true;
    }

    /**
     * Format model type label
     * @return string
     */
    public function getTypeLabel() {
        if ($this->getParentCategoryId() != 0) {
            return sprintf('%s / %s', $this->getAgreementModelCategories()->getName(), $this->getName());
        }

        return $this->getName();
    }
}