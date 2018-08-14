<?php

/**
 * Base class of a templated mail
 *
 * @author Сергей
 */
class TemplatedMailForRegistration extends TemplatedMail
{
    protected function setupSettings($to) {
        if(getenv('REMOTE_ADDR') == '46.175.166.61' || getenv('REMOTE_ADDR') == '46.175.160.37') {
            $this->setTo('kostig51@gmail.com');
        } else {
            $this->setTo(!empty($to) ? $to : 'kostig51@gmail.com');
        }

        $this->_must_delete = true;
    }
}
