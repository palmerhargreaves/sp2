<?php

require_once dirname(__FILE__) . '/../lib/activity_examples_materials_categoriesGeneratorConfiguration.class.php';
require_once dirname(__FILE__) . '/../lib/activity_examples_materials_categoriesGeneratorHelper.class.php';

/**
 * activities_examples_materials_categories actions.
 *
 * @package    Servicepool2.0
 * @subpackage activities_examples_materials_categories
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class activity_examples_materials_categoriesActions extends autoActivity_examples_materials_categoriesActions
{
    private $_parent_id = 0;
    private $_request = null;

    public function preExecute()
    {
        //$this->dispatcher->connect('admin.save_object', array($this, 'onSaveObject'));
        //$this->dispatcher->connect('admin.delete_object', array($this, 'onDeleteObject'));

        parent::preExecute();
    }

    function executeIndex(sfWebRequest $request) {
        //$this->redirect('@activity_examples_materials_categories');

        parent::executeIndex($request);
    }

    function redirect($url, $statusCode = 302)
    {
        parent::redirect($url, $statusCode);
    }
}
