<?php

/**
 * Description of ActivityCreateMail
 *
 */
class ActivityStatisticCheckByUserCancelMail extends TemplatedMail
{
    function __construct(User $user, Activity $activity, $current_quarter, $current_year)
    {
        $subject = sprintf('Статистики по активности %s', $activity->getName());

        parent::__construct(
            $user->getEmail(),
            //'kostig51@gmail.com',
            'global/mail_common',
            array(
                'user' => $user,
                'subject' => $subject,
                'text' =>
                    <<<TEXT
        <p>
        Статистика по активности (<strong>"{$activity->getName()}"</strong>) была отклонена.
        <br/>
        </p>
TEXT
            )
        );
    }
}
