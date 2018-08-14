<?php

/**
 * Base class of a templated mail
 *
 * @author Сергей
 */
class TemplatedMail extends Swift_Message
{
    protected $_can_send_mail = true;
    protected $_msg_type = 'none';
    protected $_model_id = 0;
    protected $_must_delete = false;
    protected $_email = '';

    function __construct($to, $template, $data)
    {
        parent::__construct(
            $this->generateSubject($template, $data),
            $this->generateBody($template, $data),
            'text/html'
        );

        $this->setupFrom();

        //Настройки для рассылки писем
        $this->setupSettings(trim($to));

        $this->_email = $to;
    }

    protected function setupSettings($to) {
        /*if(Utils::allowedIps()) {
            $this->setTo('kostig51@gmail.com');
        } else {
            $this->setTo(!empty($to) ? $to : 'kostig51@gmail.com');
        }*/

        $this->setTo(!empty($to) ? $to : 'kostig51@gmail.com');

        $can_send_email = true;
        $user = UserTable::getInstance()->findOneBy('email', $to);
        if ($user ) {
            //Если пользователь неактивен, запрещаем рассылку писем
            if (!$user->getActive()) {
                $can_send_email = false;
            }

            //Если пользователь не должен получать письма, запрещаем рассылку писем
            if (!$user->getAllowReceiveMails()) {
                $can_send_email = false;
            }

            //Если отдел пользователя не должен принимать письма, запрещаем рассылку писем
            if ($user->getDepartment() && !$user->getDepartment()->getUserDepartment()->getAllowEmails()) {
                $can_send_email = false;
            }
        }

        if (!$can_send_email) {
            $this->_can_send_mail = false;
            $this->_must_delete = true;
        }

    }

    protected function setupFrom()
    {
        $name = sfConfig::get('app_mail_sender_name');
        $email = sfConfig::get('app_mail_sender');

        $this->setFrom($email, $name);
    }

    protected function generateBody($template, $data)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        return get_partial($template, $data);
    }

    protected function generateSubject($template, $data)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        return trim(get_partial($template . '_subject', $data));
    }

    public function setCanSendMail($can_send) {
        $this->_can_send_mail = $can_send;
    }

    public function getCanSendMail() {
        return $this->_can_send_mail;
    }

    public function setModelId($model_id) {
        $this->_model_id = $model_id;
    }

    public function getModelId() {
        return $this->_model_id;
    }

    public function getMustDelete() {
        return $this->_must_delete;
    }

    public function setMsgType($msg_type) {
        $this->_msg_type = $msg_type;
    }

    public function getMsgType() {
        return $this->_msg_type;
    }

    public function getEmail() {
        return $this->_email;
    }
}
