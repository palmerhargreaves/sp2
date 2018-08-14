<?php

require_once dirname(__FILE__) . '/../lib/activity_models_types_necessarilyGeneratorConfiguration.class.php';
require_once dirname(__FILE__) . '/../lib/activity_models_types_necessarilyGeneratorHelper.class.php';

/**
 * activity_task actions.
 *
 * @package    Servicepool2.0
 * @subpackage activity_task
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class activity_models_types_necessarilyActions extends autoActivity_models_types_necessarilyActions
{
    protected $activity_id = '';

    public function preExecute()
    {
        //$this->dispatcher->connect('admin.save_object', array($this, 'onSaveObject'));
        //$this->dispatcher->connect('admin.delete_object', array($this, 'onDeleteObject'));

        parent::preExecute();
    }

    function executeIndex(sfWebRequest $request)
    {
        $this->redirect('@activity');
    }

    function executeNew(sfWebRequest $request)
    {
        parent::executeNew($request);
        $this->form->bind(
            array
            (
                'activity_id' => $request->getParameter('activity_id'),
            ),
            array()
        );
    }

    public function executeCreate(sfWebRequest $request)
    {
        $this->action = 'add';

        parent::executeCreate($request);
    }

    public function executeUpdate(sfWebRequest $request)
    {
        $this->action = 'edit';

        parent::executeUpdate($request);
    }

    function redirect($url, $statusCode = 302)
    {
        if ($url == '@activity_models_types_necessarily_new' && $this->form)
            $url .= '?activity_id=' . $this->form->getValue('activity_id');

        parent::redirect($url, $statusCode);
    }

}
