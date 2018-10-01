<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 02.08.2017
 * Time: 16:02
 */

class DiscussionOnlineAbstract extends ActionsWithJsonForm {
    protected $_user = null;
    protected $_request = null;
    protected $_response = null;
    protected $_activities_list = array();

    protected $_default_user_id = 0;
    protected $_default_model_id = 0;
    protected $_default_dealer_id = 0;

    protected $_fist_discussion = 0;
    protected $_first_dealer = 0;

    /**
     * DiscussionOnlineAbstract constructor.
     * @param User $user
     * @param sfWebRequest $request
     */
    public function __construct(User $user, sfWebRequest $request, sfWebResponse $response)
    {
        $this->_user = $user;
        $this->_request = $request;
        $this->_response = $response;

        $this->loadActivitiesList();
    }

    public function getLabel()
    {
        throw new Exception('Must implement in child class');
    }

    public function getActivitiesList() {
        return $this->_activities_list;
    }

    private function loadActivitiesList() {
        $this->_activities_list = ActivityTable::getInstance()->createQuery()->select('id, name')->where('finished = ?', false)->orderBy('id DESC')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
    }

    public function getFirstDiscussion() {
        return $this->_fist_discussion;
    }

    public function getUnreadCount() {
        $discussion_ids = explode(':', $this->_request->getParameter('discussion_ids'));
        $discussion_unread_count = array();

        foreach ($discussion_ids as $discussion_id) {
            /** @var Discussion $discussion */
            $model = AgreementModelTable::getInstance()->createQuery()->select('dealer_id')->where('discussion_id = ?', $discussion_id)->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
            if ($model) {
                $dealer_id = $model['dealer_id'];

                $models_by_dealer = AgreementModelTable::getInstance()->createQuery()->select('discussion_id')->where('dealer_id = ?', $dealer_id)->andWhere('created_at LIKE ?', '%'.date('Y-m').'%')->groupBy('discussion_id')->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
                foreach ($models_by_dealer as $model) {
                    $discussion = DiscussionTable::getInstance()->find($model['discussion_id']);
                    if ($discussion) {
                        if (!array_key_exists($discussion_id, $discussion_unread_count)) {
                            $discussion_unread_count[$discussion_id] = 0;
                        }

                        $discussion_unread_count[$discussion_id] += $discussion->countUnreadMessages($this->_user);
                    }
                }
            }
        }

        return $discussion_unread_count;
    }

    public function getDiscussionVisibility() {
        throw new Exception('Must implement');
    }

    /**
     * Get messages list
     */
    protected function getLastMessagesList()
    {
        $start_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
        $end_date = date('Y-m-d', strtotime(date('Y-m-d') . ' + 1 days'));

        $query = MessageTable::getInstance()->createQuery('m')
            ->innerJoin('m.Discussion d')
            ->innerJoin('d.Models models')
            ->innerJoin('models.Activity activity')
            ->select('m.discussion_id, m.user_id, m.user_name, m.created_at, m.text, m.system')
            //->where('m.created_at LIKE ? and m.system = ?', array('%' . date('Y-m') . '%', false))
            ->where('m.created_at >= ? and m.created_at <= ?', array($start_date, $end_date))
            //->groupBy('m.discussion_id')
            ->orderBy('m.created_at DESC');

        $filter_by_activity = $this->_request->getParameter('filter_by_activity');
        if (!is_null($filter_by_activity) && $filter_by_activity != 0) {
            $query->andWhere('models.activity_id = ?', $filter_by_activity);
        }

        $this->filterByUser($query);

        $this->filterByDealer($query);

        $this->filterQuery($query);

        $messages_list = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $messages_with_model = array();
        $discussions_ids = array_map(function ($item) use (&$messages_with_model) {
            $messages_with_model[$item['discussion_id']] = $item;

            return $item['discussion_id'];
        }, $messages_list);

        $models = AgreementModelTable::getInstance()->createQuery()->select('name, discussion_id, dealer_id')->whereIn('discussion_id', $discussions_ids)->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        array_map(function ($item) use (&$messages_with_model) {
            if (array_key_exists($item['discussion_id'], $messages_with_model)) {
                $messages_with_model[$item['discussion_id']]['model'] = $item;

                return true;
            }
        }, $models);

        return $messages_with_model;
    }

    public function postMessage() {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Url');

        $text = trim($this->_request->getPostParameter('message'));
        /*if (!$text)
            return sfView::NONE;*/

        $files = $this->_request->getParameter('files');
        if ((!$files || !is_array($files)) && empty($text)) {
            return sfView::NONE;
        }

        $discussion = $this->getDiscussion();

        //Тип сообщения (Обычное / Вопрос)
        $message_type = $this->_request->getParameter('message_type');;

        $message = new Message();

        //Цитирование сообщения
        $reply_id = $this->_request->getParameter('reply_on_message_id', 0);
        if (!empty($reply_id)) {
            $message->setReplyOnMessageId($reply_id);
        }

        //Кому отправляем сообщение (дилеру или импортерам)
        $this->workWithMessageDirection($message);

        if ($message_type != Message::MSG_TYPE_ASK) {

            $message->setDiscussion($discussion);
            $message->setUser($this->_user);
            $message->setUserName($this->_user->selectName());
            $message->setMsgShow(true);
            $message->setText($text);

            $message->save();
            $this->saveFiles($message, $this->_request);

            $logEntry = LogEntryTable::getInstance()->createQuery()->where('message_id = ?', $message->getId())->execute();

            if ($this->_user->getAllowReceiveMails()) {
                //new DealerDiscussionMail($this->_user, $logEntry, sfConfig::get('app_mail_sender'));
            }
        } else if ($message_type == Message::MSG_TYPE_ASK) {
            //Сохраняем отдельно вопрос в общий список вопросов
            $model_id = $this->getModelId();
            $model = AgreementModelTable::getInstance()->createQuery()->select('name, dealer_id, discussion_id')->where('id = ?', $model_id)->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
            if ($model) {
                $discussion = DealerDiscussionTable::getInstance()->findDiscussion(DealerTable::getInstance()->find($model['dealer_id']));

                //Ответ на сообщение если был задан вопрос от дилера
                $user_reply_to = 0;
                $reply_message = null;
                if ($reply_id != 0) {
                    $reply_message = MessageTable::getInstance()->find($reply_id);

                    if ($reply_message && $reply_message->getContactId() != 0) {
                        $user = UserTable::getInstance()->find($reply_message->getUserId());

                        $user_reply_to = $user->getId();
                    }
                }

                $message = new Message();
                $message->setDiscussion($discussion);
                $message->setUser($this->_user);
                $message->setUserName($this->_user->selectName());
                $message->setMsgShow(true);
                $message->setSystem(false);
                $message->setMsgType(Message::MSG_TYPE_ASK);
                $message->setText($text);
                $message->setReplyOnMessageId($reply_id);
                $message->setContactId($user_reply_to);
                $message->save();

                $this->saveFiles($message, $this->_request);

                //Получаем сообщение на которое был получен ответ, для отправки дилеру ответа

                if ($reply_message && $user_reply_to != 0) {
                    $user = UserTable::getInstance()->find($reply_message->getUserId());

                    $messages_logger = new DealerDiscussionOneMessage();
                    $messages_logger->copy();

                    $logEntry = LogEntryTable::getInstance()->createQuery()->where('message_id = ?', $message->getId())->execute();

                    $message_mail = new DealerAnswerDiscussionMail($user, $logEntry, '');
                    $message_mail->setPriority(1);
                    sfContext::getInstance()->getMailer()->send($message_mail);
                }
            }
        }

        $chat_with_users = $this->getDiscussionUsers();
        $chat_with = array();

        foreach ($chat_with_users as $chat_item) {
            $user_item = UserTable::getInstance()->find($chat_item);
            if ($user_item) {
                $chat_with[] = sprintf('%s %s', $user_item->getName(), $user_item->getSurname());
            }
        }

        $model = AgreementModelTable::getInstance()->find($this->_request->getParameter('model_id'));

        $date = date('Y-m-d H:i:s');
        $model_quarter = D::getQuarter(D::calcQuarterData($date));
        $model_year = D::getYear(D::calcQuarterData($date));
        $activityId = 0;
        $modelId = 0;

        if ($model) {
            $model_date = $model->getModelQuarterDate();
            $model_quarter = D::getQuarter($model_date);
            $model_year = D::getYear($model_date);
            $modelId = $model->getId();
            $activityId = $model->getActivityId();
        }

        return array(
            'success' => true,
            'response' => array
            (
                'message' => $text,
                'users_who_get_messages' => $this->getDiscussionUsers(),
                'send_user' => sprintf('%s %s', $this->_user->getName(), $this->_user->getSurname()),
                'message_time' => date('H:i', strtotime($message->getCreatedAt())),
                'users_names' => implode(' / ', $chat_with),
                'messages_list' => $this->messagesList($message_type),
                'messages_count' => 1,
                'message_type' => $message_type,
                'discussion_id' => $discussion->getId(),
                'model_id' => $this->_request->getParameter('model_id'),
                'dealer_id' => $discussion->getModels()->count() ? $discussion->getModels()->getFirst()->getDealerId() : $this->_request->getParameter('dealer_id'),
                'model_url' => ($this->_user->isManager() || $this->_user->isAdmin() || $this->_user->isSuperAdmin()) ? ("/activity/module/agreement/management/models/{$modelId}") : ("/activity/{$activityId}/module/agreement/models/model/{$modelId}/quarter/" . $model_quarter . '/year/' . $model_year)
            )
        );
    }

    public function getAskMessagesList($default_discussion = null) {
        $messages_result = array();

        $query = MessageTable::getInstance()->createQuery('m')
            ->select('m.*, u.name, u.surname')
            ->innerJoin('m.User u')
            ->where('m.system = ?', array(false))
            //->andWhere('m.user_id = ?', $this->_user->getId())
            ->andWhere('m.msg_show = ?', true)
            ->orderBy('m.created_at DESC');

        //Берем данные по дилеру из первой дискуссии
        if (!is_null($default_discussion)) {
            $dealer_id = $default_discussion['model']['dealer_id'];

            //Получаем по умолчанию дискуссию привязанную к дилеру
            if (!is_null($dealer_id)) {
                $discussion = DealerDiscussionTable::getInstance()->findDiscussion(DealerTable::getInstance()->find($dealer_id));
                if ($discussion) {
                    $query->andWhere('discussion_id = ?', $discussion->getId());
                }
            } else {
                return $messages_result;
            }
        }

        $messages = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $ids = array();
        foreach ($messages as $message) {
            $ids[] = $message['id'];

            $messages_result[$message['id']] = array('data' => $message, 'message_type' => 'ask', 'files' => array());
        }

        if (!$ids)
            return array();

        $files = MessageFileTable::getInstance()
            ->createQuery()
            ->whereIn('message_id', $ids)
            ->execute();

        $grouped_files = array();
        foreach ($files as $file) {
            $message_id = $file->getMessageId();
            if (!isset($grouped_files[$message_id])) {
                $grouped_files[$message_id] = array();
            }

            if (file_exists(sfConfig::get('app_uploads_path') . '/' . MessageFile::FILE_PATH . '/' . $file->getFileName())) {
                $messages_result[$message_id]['files'][] = $file;
            }
        }

        return $messages_result;
    }

    public function messagesList($messages_type = null)
    {
        $messages = null;

        $model_id = $this->getModelId();

        if (is_null($model_id)) {
            return array();
        }

        $model = AgreementModelTable::getInstance()->createQuery()->select('name, dealer_id, discussion_id')->where('id = ?', $model_id)->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

        $filter_by_messages_status = $this->_request->getParameter('messages_status');
        $filter_by_messages_type = $this->_request->getParameter('messages_type');
        if (!is_null($messages_type)) {
            $filter_by_messages_type = $messages_type;
        }

        $query = MessageTable::getInstance()->createQuery('m')
            ->select('m.*, u.name, u.surname')
            ->innerJoin('m.User u')
            ///->where('m.discussion_id = ? and m.system = ?', array($model['discussion_id'], false))
            ->andWhere('msg_show = ?', true)
            ->orderBy('m.created_at DESC');

        if ((!is_null($filter_by_messages_status) && !empty($filter_by_messages_status) && $filter_by_messages_status == Message::MSG_STATUS_UNREAD) || $filter_by_messages_type == Message::MSG_STATUS_UNREAD) {
            $discussion = DiscussionTable::getInstance()->find($model['discussion_id']);

            if (!$discussion) {
                return array();
            }

            $messages = $discussion->getUnreadMessages($this->_user, true);
        } else if (!is_null($filter_by_messages_type) && !empty($filter_by_messages_type) && $filter_by_messages_type == Message::MSG_TYPE_ASK) {
            $dealer_id = $this->_request->getParameter('dealer_id');
            if (is_null($dealer_id)) {
                $dealer_id = $model['dealer_id'];
            }

            $discussion = DealerDiscussionTable::getInstance()->findDiscussion(DealerTable::getInstance()->find($dealer_id));

            $query->andWhere('m.discussion_id = ? and m.system = ?', array($discussion->getId(), false));
            $query->andWhereIn('m.private_user_id', array(0, $this->_user->getId()));

            $this->filterQuery($query);
        }
        else {
            $query->where('m.discussion_id = ?', array($model['discussion_id']));

            //$this->filterQuery($query);

            if (($user_id = $this->getUserId()) != 0) {
                //$query->andWhere('m.user_id = ?', $user_id);
            }
        }

        //Если нет сообщений, получаем с БД
        if (is_null($messages)) {
            $messages = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        }

        $messages_result = array();
        $ids = array();
        foreach ($messages as $message) {
            $ids[] = is_object($message) ? $message->getId() : $message['id'];

            $messages_result[$message['id']] = array
            (
                'data' => $message,
                'message_type' => (!is_null($filter_by_messages_type) && !empty($filter_by_messages_type) && $filter_by_messages_type == Message::MSG_TYPE_ASK) ? Message::MSG_TYPE_ASK : 'message',
                'files' => array()
            );
        }

        if (!$ids) {
            return array();
        }

        $files = MessageFileTable::getInstance()
            ->createQuery()
            ->whereIn('message_id', $ids)
            ->execute();

        $grouped_files = array();
        foreach ($files as $file) {
            $message_id = $file->getMessageId();
            if (!isset($grouped_files[$message_id])) {
                $grouped_files[$message_id] = array();
            }

            if (file_exists(sfConfig::get('app_uploads_path') . '/' . MessageFile::FILE_PATH . '/' . $file->getFileName())) {
                $messages_result[$message_id]['files'][] = $file;
            }
        }

        return $messages_result;
    }

    protected function getUserId() {
        return $this->_default_user_id != 0 ? $this->_default_user_id : $this->_user->getId();
    }

    protected function getDiscussion() {
        $id = $this->_request->getParameter('id');
        $discussion = DiscussionTable::getInstance()->find($id);

        if (!$discussion) {
            $this->forward404('обсуждение не найдено');
        }

        return $discussion;
    }

    /**
     * Получить список пользователей которые будут участвовать с чате
     * @return array
     */
    protected function getDiscussionUsers() {
        $messages = $this->getDiscussion()->getMessages();

        $user_list_ids = array();
        foreach ($messages as $message) {
            if (!in_array($message->getUserId(), $user_list_ids) /*&& $message->getUserId() != $this->_user->getId()*/) {
                $user_list_ids[] = $message->getUserId();
            }
        }

        //Цитирование сообщения, выбираем пользователя который получает сообщение
        $message_reply_on = $this->_request->getParameter('reply_on_message_id');

        //Если импортер то получаем список импортера, если админ / дилер то отправляем им
        $message_direction = $this->_request->getParameter('direction');

        if (!empty($message_reply_on) || !empty($message_direction)) {
            if (!empty($message_reply_on)) {
                $user_list_ids = array();

                $message = MessageTable::getInstance()->find($this->_request->getParameter('reply_on_message_id'));
                if ($message) {
                    $user_list_ids[] = $message->getUserId();
                }
            }

            if (!empty($message_direction)) {
                //Список импортеров
                if ($message_direction == Message::MSG_DIRECTION_IMPORTER) {
                    $user_list_ids = $this->getImportersIds();
                } //
            }
        }

        return $user_list_ids;
    }

    protected function saveFiles(Message $message, sfWebRequest $request)
    {
        $files = $request->getParameter('files');
        if (!$files || !is_array($files))
            return;

        foreach ($files as $temp_id) {
            $temp_file = TempFileTable::getInstance()
                ->createQuery()
                ->where('user_id=? and id=?', array($this->_user->getId(), $temp_id))
                ->fetchOne();

            if (!$temp_file)
                continue;

            $file = new MessageFile();
            $file->setMessageId($message->getId());
            $file->applyTemp($temp_file);
            $file->save();
        }
    }

    /**
     * Получить список заявок по пользователю
     * @return array
     */
    public function getDealerModelsList() {
        return $this->getLastMessagesList();
    }

    public function getResponse() {
        return $this->_response;
    }

    /**
     *
     * @return array
     */
    private function getImportersIds()
    {
        $users = UserTable::getInstance()->createQuery('u')->select('u.id')->innerJoin('u.Group g')->andWhere('g.id = ?', User::USER_GROUP_IMPORTER)->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        $users_ids = array_map(function ($item) {
            return $item['id'];
        }, $users);

        return $users_ids;
    }

    protected function filterQuery(&$query) {

    }

    protected function filterByUser(&$query) {
        $filter_by_user = $this->_request->getParameter('filter_by_user');
        if ($this->_default_user_id != 0) {
            $query->andWhere('m.user_id = ?', $this->_default_user_id);
        } else {
            if (!is_null($filter_by_user) && $filter_by_user != 0) {
                $query->andWhere('m.user_id = ?', $filter_by_user);
            } else {
                //$query->andWhere('m.user_id = ?', $this->_user->getId());
            }
        }
    }

    protected function filterByDealer(&$query) {
        //Фильтр списка заявок по дилеру
        $filter_by_dealer = $this->_request->getParameter('filter_by_dealer');
        if (empty($filter_by_dealer) || $filter_by_dealer == 0) {
            $filter_by_dealer = $this->_default_dealer_id;
        }

        if (!empty($filter_by_dealer) && $filter_by_dealer != 0) {
            $query->andWhere('models.dealer_id = ?', $filter_by_dealer);
        }
    }

    /**
     * Получить номер заявки
     * @return int|mixed
     */
    private function getModelId() {
        $model_id = $this->_request->getParameter('model_id');
        if (!empty($model_id) && $model_id != 0) {
            return $model_id;
        }

        return $this->_default_model_id;
    }

    /**
     * Кому отправляем сообщение
     * @param $message
     */
    protected function workWithMessageDirection(&$message) {
        $message_direction = $this->_request->getParameter('direction');
        if (!empty($message_direction)) {
            $message->setWhoGetMessage($message_direction);

            //Если отправка идет импортеру делаем доп. обработку
            if ($message_direction == Message::MSG_DIRECTION_IMPORTER) {
                $message->setWhoGetMessageIds(implode(':', $this->getImportersIds()));
            }
        }
    }
}
