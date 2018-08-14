<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 02.08.2017
 * Time: 15:19
 */

class ImporterDiscussionOnline extends AdminDiscussionOnline
{

    public function getLabel()
    {
        return 'importer_discussions';
    }

    protected function filterQuery(&$query)
    {
        $query->andWhere('m.who_get_message_ids = ?', $this->_user->getId())->andWhere('user_id = ?', $this->_user->getId());
        //$query->andWhere('m.who_get_message_ids IS NOT NULL');
    }

    protected function filterByUser(&$query) {

    }

    protected function filterByDealer(&$query) {

    }

    /**
     * Когда отправляет импортер, фиксируем отправку сообщения пользователям для вывода сообщение в списке у импортера
     * @param $message
     */
    protected function workWithMessageDirection(&$message) {
        $message->setWhoGetMessageIds(implode(':', array($this->_user->getId())));
    }
}
