<?php
/**
 * Created by PhpStorm.
 * User: kostig51
 * Date: 30.01.2018
 * Time: 3:38
 */

class contactsActions extends BaseActivityActions {

    public function executeIndex() {
        $this->contacts = ContactsTable::getInstance()->createQuery()->orderBy('position ASC')->execute();
    }

    public function executeSendMessage(sfWebRequest $request) {
        $user_who_get = $request->getParameter('user_who_get');
        $text = $request->getParameter('message');

        $discussion = DiscussionTable::getInstance()->find($request->getParameter('discussion_id'));
        if (!$discussion) {
            return $this->sendJson(array('success' => false, 'msg' => 'Обсуждение не найдено.'));
        }

        $contact_user = ContactsTable::getInstance()->find($user_who_get);
        if (!$contact_user) {
            return $this->sendJson(array('success' => false, 'msg' => 'Пользователь не найден.'));
        }

        //Получить данные о пользователе который получает сообщения
        $user_with_contact = $contact_user->getRedirectUser();
        if (is_null($user_with_contact)) {
            $user_with_contact = $contact_user->getUser();
        }

        if (!$user_with_contact) {
            return $this->sendJson(array('success' => false, 'msg' => 'Пользователь не найден.'));
        }

        $user = $this->getUser()->getAuthUser();

        $message = new Message();
        $message->setDiscussion($discussion);
        $message->setUser($user);
        $message->setUserName($user->selectName());
        $message->setText($text);
        $message->setContactId($user_who_get);
        $message->setSystem(false);
        $message->setMsgType(Message::MSG_TYPE_ASK);
        $message->save();

        $messages_logger = new DealerDiscussionOneMessage();
        $messages_logger->copy();

        $logEntry = LogEntryTable::getInstance()->createQuery()->where('message_id = ?', $message->getId())->execute();

        $message = new DealerAskDiscussionMail($user, $user_with_contact, $message, $logEntry, '');
        $message->setPriority(1);
        sfContext::getInstance()->getMailer()->send($message);

        return $this->sendJson(array('success' => true, 'msg' => 'Сообщение успешно отправлено пользователю.'));
    }
}
