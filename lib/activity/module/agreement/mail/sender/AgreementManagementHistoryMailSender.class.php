<?php

/**
 * Description of AgreementManagementHistoryMailSender
 *
 * @author Сергей
 */
class AgreementManagementHistoryMailSender
{
    const OTHER_NOTIFICATION = 0;
    // model notifcation
    const AGREEMENT_NOTIFICATION = 1;
    const FINAL_AGREEMENT_NOTIFICATION = 2;
    const NEW_AGREEMENT_NOTIFICATION = 3;
    // report notfication
    const AGREEMENT_REPORT_NOTIFICATION = 4;
    const FINAL_AGREEMENT_REPORT_NOTIFICATION = 5;
    const NEW_AGREEMENT_REPORT_NOTIFICATION = 6;
    // concept notifcation
    const AGREEMENT_CONCEPT_NOTIFICATION = 7;
    const FINAL_AGREEMENT_CONCEPT_NOTIFICATION = 8;
    const NEW_AGREEMENT_CONCEPT_NOTIFICATION = 9;
    // concept report notfication
    const AGREEMENT_CONCEPT_REPORT_NOTIFICATION = 10;
    const FINAL_AGREEMENT_CONCEPT_REPORT_NOTIFICATION = 11;
    const NEW_AGREEMENT_CONCEPT_REPORT_NOTIFICATION = 12;

    static function send($mail_class, LogEntry $entry, $params = false, $roles = false, $type = self::OTHER_NOTIFICATION, $message = null, $can_send = true, $msg_type = 'none')
    {
        if (!$roles)
            $roles = array('manager');

        if (!is_array($roles))
            $roles = array($roles);

        /*$users_query = UserTable::getInstance()
                       ->createQuery('u')
                       ->innerJoin('u.Group g')
                       ->innerJoin('g.Roles r')
                       ->whereIn('r.role', $roles)
                       ->andWhere('u.active=?', true); */

        $users_query = UserTable::getInstance()
            ->createQuery('u')
            ->innerJoin('u.Group g')
            ->innerJoin('g.Roles r')
            ->whereIn('r.role', $roles)
            ->andWhere('u.active=?', true)
            ->andWhere('u.allow_receive_mails = ?', true);

        if ($type == self::AGREEMENT_NOTIFICATION)
            $users_query->andWhere('u.agreement_notification=?', true);
        if ($type == self::FINAL_AGREEMENT_NOTIFICATION)
            $users_query->andWhere('u.final_agreement_notification=?', true);
        if ($type == self::NEW_AGREEMENT_NOTIFICATION)
            $users_query->andWhere('u.new_agreement_notification=?', true);

        if ($type == self::AGREEMENT_REPORT_NOTIFICATION)
            $users_query->andWhere('u.agreement_report_notification=?', true);
        if ($type == self::FINAL_AGREEMENT_REPORT_NOTIFICATION)
            $users_query->andWhere('u.final_agreement_report_notification=?', true);
        if ($type == self::NEW_AGREEMENT_REPORT_NOTIFICATION)
            $users_query->andWhere('u.new_agreement_report_notification=?', true);

        if ($type == self::AGREEMENT_CONCEPT_NOTIFICATION)
            $users_query->andWhere('u.agreement_concept_notification=?', true);
        if ($type == self::FINAL_AGREEMENT_CONCEPT_NOTIFICATION)
            $users_query->andWhere('u.final_agreement_concept_notification=?', true);
        if ($type == self::NEW_AGREEMENT_CONCEPT_NOTIFICATION)
            $users_query->andWhere('u.new_agreement_concept_notification=?', true);

        if ($type == self::AGREEMENT_CONCEPT_REPORT_NOTIFICATION)
            $users_query->andWhere('u.agreement_concept_report_notification=?', true);
        if ($type == self::FINAL_AGREEMENT_CONCEPT_REPORT_NOTIFICATION)
            $users_query->andWhere('u.final_agreement_concept_report_notification=?', true);
        if ($type == self::NEW_AGREEMENT_CONCEPT_REPORT_NOTIFICATION)
            $users_query->andWhere('u.new_agreement_concept_report_notification=?', true);

        $model = AgreementModelTable::getInstance()->find($entry->getObjectId());
        foreach ($users_query->execute() as $user) {
            /*$dealersList = $user->getDealersList();

            if(!empty($dealersList) && $model)
            {
              if(in_array($model->getDealer()->getId(), $dealersList)) {
                self::sendMail($mail_class, $entry, $user, $params);
              }
            }
            else if($user->isManager())
              self::sendMail($mail_class, $entry, $user, $params);
            */
            if($user->getAllowReceiveMails()) {
                self::sendMail($mail_class, $entry, $user, $params, $message, $can_send, $msg_type);
            }
        }

    }

    /**
     * Спец. отправка письма (для регионального менеджера и импортера привязанного к активности)
     * @param $mail_class
     * @param LogEntry $entry
     * @param array $users_ids
     * @param bool $params
     * @param null $message
     * @param bool $can_send
     * @param string $msg_type
     */
    static function sendSpecial($mail_class, LogEntry $entry, $users_ids = array(), $params = false, $message = null, $can_send = true, $msg_type = 'none')
    {
        $users_query = UserTable::getInstance()
            ->createQuery('u')
            ->whereIn('u.id', $users_ids)
            ->andWhere('u.active=?', true)
            ->andWhere('u.allow_receive_mails = ?', true);

        foreach ($users_query->execute() as $user) {
            if($user->getAllowReceiveMails()) {
                self::sendMail($mail_class, $entry, $user, $params, $message, $can_send, $msg_type);
            }
        }
    }

    private static function sendMail($mail_class, $entry, $user, $params, $message_file = null, $can_send = true, $msg_type = 'none')
    {
        if($mail_class == "AgreementModelDeclinedMail") {
            $message = new $mail_class($entry, $user, $message_file);
        } else {
            $message = $params ? new $mail_class($entry, $user, $params) : new $mail_class($entry, $user);
        }

        $message->setParams($params);
        $message->setCanSendMail($can_send);
        $message->setMsgType($msg_type);
        $message->setModelId($entry->getObjectId());
        $message->setPriority(1);

        sfContext::getInstance()->getMailer()->send($message);
    }
}
