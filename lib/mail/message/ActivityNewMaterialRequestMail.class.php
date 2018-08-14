<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 26.08.2016
 * Time: 13:24
 */

class ActivityNewMaterialRequestMail extends TemplatedMail
{
    function __construct(User $user, $data)
    {
        $subject = 'Поступил запрос на разработку материала';
        parent::__construct(
            //'kostig51@gmail.com',
            sfConfig::get('app_mail_sender'),
            'global/mail_new_material_request',
            array(
                'user' => $user,
                'subject' => $subject,
                'data' => $data
            ),
            sprintf('%s %s', $user->getSurname(), $user->getName()),
            $user->getEmail()
        );
    }
}