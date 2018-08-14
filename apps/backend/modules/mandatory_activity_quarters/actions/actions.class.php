<?php

require_once dirname(__FILE__).'/../lib/mandatory_activity_quartersGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/mandatory_activity_quartersGeneratorHelper.class.php';

/**
 * mandatory_activity_quarters actions.
 *
 * @package    Servicepool2.0
 * @subpackage mandatory_activity_quarters
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class mandatory_activity_quartersActions extends autoMandatory_activity_quartersActions
{
    function executeIndex(sfWebRequest $request)
    {
        $this->redirect('@activity');
    }

    function executeNew(sfWebRequest $request)
    {
        parent::executeNew($request);

        $this->form->bind(array(
            'activity_id' => $request->getParameter('activity_id')
        ), array());
    }

    function redirect($url, $statusCode = 302)
    {
        if ($url == '@mandatory_activity_quarters_new' && $this->form)
            $url .= '?activity_id=' . $this->form->getValue('activity_id');

        parent::redirect($url, $statusCode);
    }
}
