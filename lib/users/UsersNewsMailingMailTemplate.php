<?php

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 15.12.2017
 * Time: 10:06
 */
class UsersNewsMailingMailTemplate extends TemplatedMail
{
    public function __construct($user, $news_item)
    {
        parent::__construct(
            $user->getEmail(),
            'global/mail_user_news_mailing',
            array(
                'user' => $user,
                'subject' => str_replace("\"", "", $news_item->getName()),
                'text' => $news_item->getText()
            )
        );
    }
}
