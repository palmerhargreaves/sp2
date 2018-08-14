<?php

/**
 * mails actions.
 *
 * @package    Servicepool2.0
 * @subpackage mails
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */


class mailsActions extends sfActions
{
    private $mails_paths = array('new_registered_user' => '/apps/frontend/templates/_mail_common_register.php');

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    function executeIndex(sfWebRequest $request)
    {
        $this->mail_type = $request->getParameter('param');

        $this->mail_text = $this->getMailText();
    }

    private function getMailText() {
        if (array_key_exists($this->mail_type, $this->mails_paths)) {
            return $this->readFile(sfConfig::get('sf_root_dir').$this->mails_paths[$this->mail_type]);
        }
    }

    private function saveMail() {

    }

    private function readFile($file) {
        return file_get_contents($file);
    }

}
