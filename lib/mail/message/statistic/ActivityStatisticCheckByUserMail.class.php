<?php

/**
 * Description of ActivityCreateMail
 *
 */
class ActivityStatisticCheckByUserMail extends TemplatedMail
{
    function __construct(User $user, $to_user, Activity $activity, $current_quarter, $current_year)
    {
        $site_url = sfConfig::get('app_site_url');

        $subject = sprintf('Согласование статистики по активности %s', $activity->getName());
        $dealerName = $user->getDealer()->getName();

        $link = "<a href=" . $site_url . "activity/statistic/pre/check/" . $activity->getId() . "/dealer/".$user->getDealer()->getId()."/current_q/".$current_quarter."/year/".$current_year.">перейти</a>";
        parent::__construct(
            $to_user->getEmail(),
            'global/mail_common',
            array(
                'user' => $to_user,
                'subject' => $subject,
                'text' =>
<<<TEXT
        <p>
        Дилер ($dealerName) отправил на согласование статистику по активности (<strong>"{$activity->getName()}"</strong>) ({$link}).
        <br/>
        </p>
TEXT
            )
        );
    }
}
