<?php

/**
 * Description of AgreementModelFieldRenderer
 *
 * @author Сергей
 */
abstract class AgreementModelFieldRenderer
{
    /**
     * Field
     *
     * @var AgreementModelField
     */
    protected $field;

    function __construct(AgreementModelField $field)
    {
        $this->field = $field;
    }

    /**
     * Returns field
     *
     * @return AgreementModelField
     */
    function getField()
    {
        return $this->field;
    }

    function getFieldName()
    {
        $field_name = ($this->field->getParentCategoryId() > 0 ? $this->field->getCategory()->getIdentifier() : $this->field->getModelType()->getIdentifier()) . '[' . $this->field->getIdentifier() . ']';

        return $field_name;
    }

    protected function getRequiredValue()
    {
        return $this->field->getRequired() ? 'true' : 'false';
    }

    protected function isChild() {
        return $this->field->getFieldParentId() != 0 ? true : false;
    }

    abstract function render();
}
