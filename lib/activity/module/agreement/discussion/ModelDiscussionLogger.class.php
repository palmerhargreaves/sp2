<?php

/**
 * Helps to copy discussion messages to the log
 *
 * @author Сергей
 */
class ModelDiscussionLogger
{
    function copy()
    {
        $last_id = VariableTable::getInstance()->getValue('last_model_discussion_id', 0);
        $new_messages = MessageTable::getInstance()
            ->createQuery('m')
            ->innerJoin('m.Discussion d')
            ->innerJoin('d.Models dm')
            ->innerJoin('dm.Activity a')
            ->leftJoin('m.User u')
            ->where('m.id>? and m.system=?', array($last_id, false))
            ->orderBy('m.id ASC')
            ->execute();

        if (count($new_messages) > 0) {
            VariableTable::getInstance()->setValue('last_model_discussion_id', $new_messages->getLast()->getId());
        }

        foreach ($new_messages as $message) {
            $entry = new LogEntry();
            $has_files = MessageFileTable::getInstance()
                    ->createQuery()
                    ->where('message_id=?', $message->getId())
                    ->count() > 0;

            $model = $message->getDiscussion()->getModels()->offsetGet(0);
            if (!$model)
                continue;

            $activity = $model->getActivity();
            $entry->setArray(array(
                'user_id' => $message->getUserId(),
                'login' => $message->getUser() ? $message->getUser()->getEmail() : $message->getUserName(),
                'title' => $activity->getName() . '/' . $model->getName(),
                'description' => $message->getText(),
                'icon' => $has_files ? 'clip' : '',
                'object_id' => $message->getId(),
                'object_type' => 'model_message',
                'action' => 'post',
                'dealer_id' => $model->getDealerId(),
                'message_id' => $message->getId(),
                'created_at' => $message->created_at,
                'private_user_id' => $message->getPrivateUserId()
            ));
            $entry->save();

            //Отправка сообщений после создания записи в логах
            foreach ($message->getDiscussion()->getModels() as $model) {
                foreach (DealerUserTable::getInstance()->createQuery()->where('dealer_id = ?', $model->getDealerId())->execute() as $dealer_user) {
                    if ($dealer_user->getUser()->getId() && $dealer_user->getUser()->getAllowReceiveMails() && $dealer_user->getUser()->getId() != $message->getUser()->getId()) {
                        $logEntry = LogEntryTable::getInstance()->createQuery()->where('message_id = ?', $message->getId())->execute();

                        if ($logEntry) {
                            $mail_item = new DealerUsersDiscussionMail($dealer_user->getUser(), $logEntry);

                            sfContext::getInstance()->getMailer()->send($mail_item);
                        }
                    }
                }
            }

            $this->addReadingForDiscussionParticipants($entry, $message);
            foreach ($model->createPrivateLogEntryForSpecialists($entry) as $copy)
                $this->addReadingForDiscussionParticipants($copy, $message);
        }

        //Только новые сообщения могут быть отправлены при сл. синхронизации
        //VariableTable::getInstance()->setValue('last_model_discussion_id', $last_id);

    }

    protected function addReadingForDiscussionParticipants(LogEntry $entry, Message $message)
    {
        $query = DiscussionLastReadTable::getInstance()
            ->createQuery('lr')
            ->select('lr.*, u.id')
            ->innerJoin('lr.User u')
            ->innerJoin('lr.Message m WITH m.id>=?', $message->getId())
            ->innerJoin('m.Discussion d WITH d.id=?', $message->getDiscussionId());

        if ($entry->getPrivateUserId())
            $query->andWhere('u.id=?', $entry->getPrivateUserId());

        $last_reads = $query->execute();

        $users = array();
        foreach ($last_reads as $last_read) {
            // обход возможной ситуации, когда в DiscussionLastRead окажется дубликат
            if (!isset($users[$last_read->getUserId()])) {
                LogEntryReadTable::getInstance()->addRead($last_read->getUser(), $entry);
                $users[$last_read->getUserId()] = true;
            }
        }
    }
}
