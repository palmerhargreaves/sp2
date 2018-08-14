<?php

require_once dirname(__FILE__).'/../lib/agreement_model_categories_fieldsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/agreement_model_categories_fieldsGeneratorHelper.class.php';

/**
 * agreement_model_categories_fields actions.
 *
 * @package    Servicepool2.0
 * @subpackage agreement_model_categories_fields
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class agreement_model_categories_fieldsActions extends autoAgreement_model_categories_fieldsActions
{
    private $_parent_category_id = 0;

    function executeCreate(sfWebRequest $request) {
        $this->_parent_category_id = $request->getParameter('parent_category_id');

        parent::executeCreate($request);
    }

    public function onSaveObject(sfEvent $event) {
        $object = $event['object'];

        $identifier = $object->getIdentifier();
        if (empty($identifier)) {
            $object->setIdentifier(sprintf('%s_%s', Utils::normalize($object->getAgreementModelCategories()->getName()), Utils::normalize($object->getName())));
        }

        $object->save();
    }

    function preExecute() {
        $this->dispatcher->connect('admin.save_object', array($this, 'onSaveObject'));

        parent::preExecute();
    }

    function executeIndex(sfWebRequest $request) {
        $this->redirect('@agreement_model_categories');
    }

    function executeNew(sfWebRequest $request) {
        parent::executeNew($request);

        $this->_parent_category_id = $request->getParameter('parent_category_id');
        $this->form->bind(array(
            'parent_category_id' => $request->getParameter('parent_category_id')
        ), array());
    }

    function redirect($url, $statusCode = 302)
    {
        if ($url == '@agreement_model_field_new' && $this->form) {
            $url .= '?parent_category_id=' . $this->form->getValue('parent_category_id');
        }

        parent::redirect($url, $statusCode);
    }
}
