<?php

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 15.12.2017
 * Time: 10:04
 */

class UsersNewsMailing
{
    private $_entries = array();

    public function __construct()
    {
        $news_to_mailing = NewsTable::getInstance()->createQuery()->where('is_mailing = ? and sended = ?', array(true, false))->execute();
        if (count($news_to_mailing)) {
            $this->makeEntries($news_to_mailing);
        }
    }

    private function makeEntries($news_list) {
        $users = UserTable::getInstance()->createQuery()->where('active = ? and allow_receive_mails = ?', array(true, true))->execute();

        foreach ($news_list as $news_item) {
            foreach ($users as $user) {
                $entry = new UsersNewsMailingMailTemplate($user, $news_item);
                $entry->setPriority(1);
                $entry->setCanSendMail(true);

                $this->_entries[] = $entry;
            }

            $news_item->setSended(true);
            $news_item->save();
        }
    }

    public function start() {
        foreach ($this->_entries as $entry) {
            sfContext::getInstance()->getMailer()->send($entry);
        }

        return count($this->_entries);
    }
}
