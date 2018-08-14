<?php

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 04.12.2017
 * Time: 16:26
 */
class ActivitiesNotUsingImporterMail extends TemplatedMail
{
    public function __construct($data)
    {
        $site_url = sfConfig::get('app_site_url');
        $subject = sprintf('Обновление статистики');
        $link = "<a href=".$site_url."activity/".$data['activity']['id'].">перейти</a>";

        parent::__construct(
            $data['user_mail'],
            //'kostig51@gmail.com',
            'global/mail_activities_not_using_importer',
            array(
                'user_name' => $data['user_name'],
                'subject' => $subject,
                'activity_name' => $data['activity']['name'],
                'link' => $link,
            )
        );

    }
}
