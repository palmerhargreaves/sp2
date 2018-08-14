<?php

/**
 * Description of ActivityCreateMail
 *
 */
class UserApproveMail extends TemplatedMail
{
    function __construct(User $user)
    {
        $subject = sprintf('Подтверждение аккаунта');

        $site_url = sfConfig::get('app_site_url');
        $link = "<a href=" . $site_url . "user/approve/" . $user->getId().">ссылке</a>";
        $foreign_link = "<a href=".$site_url."user/approve/foreign/" . $user->getId().">ссылке</a>";


        parent::__construct(
            $user->getEmail(),
            //'kostig51@gmail.com',
            'global/mail_common',
            array(
                'user' => $user,
                'subject' => $subject,
                'text' =>
<<<TEXT
        <p>Вы получили это письмо, так как зарегистрированы на портале http://dm.vw-servicepool.ru</p>
        <p>Если это Ваш акканут и Вы используете его для работы с порталом, то подтвердите его перейдя по {$link}.</p>
        <p>Если это аккаунт предыдущего сотрудника и Вы его используете, то перейдите по {$foreign_link}.</p>
        <p>Если в ближайшие 2 недели Ваш аккаунт не будет подтвержден - то он будет удален с сайта.</p>
TEXT
            )
        );
    }
}
