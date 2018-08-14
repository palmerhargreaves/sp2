<?php
/**
 * Created by PhpStorm.
 * User: kostig51
 * Date: 24.11.2017
 * Time: 14:00
 */

class ActivityStatisticStepsSendMail extends TemplatedMail {

    function __construct($user, $activity, $template_name)
    {
        $site_url = sfConfig::get('app_site_url');
        $subject = sprintf('Заполнение статистики');
        $link = "<a href=".$site_url."/activity/".$activity->getId().">перейти</a>";

        parent::__construct(
            $user->getEmail(),
            //'kostig51@gmail.com',
            'global/'.$template_name,
            array(
                'user' => $user,
                'subject' => $subject,
                'activity' => $activity,
                'link' => $link,
            )
        );
    }
}
