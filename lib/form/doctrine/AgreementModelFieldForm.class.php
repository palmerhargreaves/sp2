<?php

/**
 * AgreementModelField form.
 *
 * @package    Servicepool2.0
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class AgreementModelFieldForm extends BaseAgreementModelFieldForm
{
    public function configure()
    {
        unset($this['model_type_id'], $this['sort']);

        $this->getWidgetSchema()->setPositions(array(
            'id', 'parent_category_id', 'name', 'type', 'identifier', 'units', 'format_expression',
            'format_hint', 'right_format',
            'def_value', 'list', 'editable', 'child_field', 'hide', 'required'
        ));
    }
}
