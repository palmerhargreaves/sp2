<?php

/**
 * Description of ActivityCreateMail
 *
 */
class ActivityStatisticCheckByUserAcceptMail extends TemplatedMail
{
    function __construct(User $user, Activity $activity, $current_quarter, $current_year)
    {
        $subject = sprintf('Статистика по активности %s', $activity->getName());

        parent::__construct(
            $user->getEmail(),
            'global/mail_common',
            array(
                'user' => $user,
                'subject' => $subject,
                'text' =>
<<<TEXT
        <p>
        Статистика по активности (<strong>"{$activity->getName()}"</strong>) была согласована.
        <br/>
        </p>
TEXT
            )
        );
    }
}
