<?php

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 02.08.2017
 * Time: 15:19
 */
class AdminDiscussionOnline extends DiscussionOnlineAbstract implements DiscussionOnlineInterface
{

    public function getLabel()
    {
        return 'admin_discussions';
    }

    public function getMessagesData()
    {
        $defaults_dealer_data = array();
        $default_messages_list = array();
        $default_ask_messages_list = array();
        $unread_messages_list = array();

        $dealers_list = $this->getDealersListByLastMessages();

        $this->_first_dealer = array_values($dealers_list);
        if (!empty($this->_first_dealer)) {
            $dealer_idx = 0;
            while (1) {
                $this->_first_dealer = $this->_first_dealer[$dealer_idx++];

                if (!empty($this->_first_dealer['user'])) {
                    $this->_default_user_id = $this->_first_dealer['user']['id'];
                    $this->_default_dealer_id = $this->_first_dealer['dealer']['id'];

                    break;
                }
            }

            $defaults_dealer_data = $this->getLastMessagesList();

            $default_messages_list = array_values($defaults_dealer_data);

            if (count($default_messages_list)) {
                $this->_default_model_id = $default_messages_list[0]['model']['id'];
                $default_messages_list = $this->messagesList();
                $default_ask_messages_list = $this->messagesList(Message::MSG_TYPE_ASK);
                $unread_messages_list = $this->messagesList(Message::MSG_STATUS_UNREAD);
            }
        }

        return array
        (
            'default_model_id' => $this->_default_model_id,
            'dealers_list' => $dealers_list,
            'first_dealer' => $this->_first_dealer,
            'default_dealer_data' => $defaults_dealer_data,
            'default_messages_list' => $default_messages_list,
            'default_ask_messages_list' => $default_ask_messages_list,
            'unread_messages_list' => $unread_messages_list
        );
    }

    private function getDealersListByLastMessages()
    {
        $dealers_with_messages = array();
        $users_with_messages = array();

        $query = MessageTable::getInstance()->createQuery('m')
            ->innerJoin('m.Discussion d')
            ->innerJoin('m.User m_user')
            ->leftJoin('d.Models models')
            ->leftJoin('models.Activity activity')
            ->select('m.discussion_id, m.user_id, m.user_name, m.created_at, m.text')
            //->where('m.created_at LIKE ? and m.system = ?', array('%' . date('Y-m') . '%', false))
            ->where('m.created_at LIKE ?', array('%' . date('Y-m') . '%'))
            //->andWhere('m.user_id = ?', $this->_user->getId())
            ->groupBy('m.discussion_id')
            ->orderBy('m.id DESC');

        //Делаем выборку заявок привязанных к дилеру
        if ($this->_default_dealer_id != 0) {
            $query->andWhere('models.dealer_id = ?', $this->_default_dealer_id);
        }

        $this->filterQuery($query);
        $messages_list = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $filter_visibility = $this->_request->getParameter('filter_by_visibility');
        if (count($messages_list)) {
            $users_ids = array_map(function ($item) use (&$users_with_messages, $filter_visibility) {
                if (!array_key_exists($item['user_id'], $users_with_messages)) {
                    $users_with_messages[$item['user_id']] = array('user' => array(), 'dealer' => array(), 'message' => $item);

                    return $item['user_id'];
                }
            }, $messages_list);

            $users_list = UserTable::getInstance()->createQuery()->select('id, name')->whereIn('id', array_values($users_ids))->execute();
            foreach ($users_list as $user) {
                if ($dealer = $user->getDealer()) {
                    //Присваиваем значения по умолчанию
                    if (!array_key_exists($dealer->getId(), $dealers_with_messages)) {
                        $dealers_with_messages[$dealer->getId()] = $users_with_messages[$user->getId()];
                    }

                    //Если дилер уже есть добавляем информацию по нему
                    if (array_key_exists($dealer->getId(), $dealers_with_messages)) {
                        $dealers_with_messages[$dealer->getId()]['user'] = array('id' => $user->getId(), 'name' => sprintf('%s %s', $user->getName(), $user->getSurname()));
                        $dealers_with_messages[$dealer->getId()]['dealer'] = array('id' => $dealer->getId(), 'name' => $dealer->getName(), 'number' => $dealer->getShortNumber());
                    }
                }
            }
        }

        return $dealers_with_messages;
    }

    /**
     * Получаем список непрочитанных сообщений в разрезе заявки
     * @return array
     */
    public function getUnreadModelsCount()
    {
        $models_ids = $this->_request->getParameter('models_ids');

        $model_unread_count = array();
        foreach ($models_ids as $model_id) {
            $model = AgreementModelTable::getInstance()->createQuery()->select('discussion_id')->where('id = ?', $model_id)->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
            if ($model) {
                $discussion = DiscussionTable::getInstance()->find($model['discussion_id']);

                if ($discussion) {
                    if (!array_key_exists($model_id, $model_unread_count)) {
                        $model_unread_count[$model_id] = 0;
                    }

                    $model_unread_count[$model_id] += $discussion->countUnreadMessages($this->_user);
                }
            }
        }

        return array('success' => count($model_unread_count) > 0, 'unread_count' => $model_unread_count);
    }

    public function getDealerDiscussionsList()
    {
        $defaults_dealer_data = $this->getLastMessagesList();

        $default_messages_list = array_values($defaults_dealer_data);
        if (count($default_messages_list)) {
            $this->_default_model_id = $default_messages_list[0]['model']['id'];
            $default_messages_list = $this->messagesList();
            $default_ask_messages_list = $this->messagesList(Message::MSG_TYPE_ASK);

            $unread_messages_list = array();
            if ($this->_request->getParameter('messages_type') == Message::MSG_STATUS_UNREAD) {
                $unread_messages_list = $this->messagesList(Message::MSG_STATUS_UNREAD);
            }
        }

        return array
        (
            'default_dealer_data' => $defaults_dealer_data,
            'default_messages_list' => $default_messages_list,
            'default_ask_messages_list' => $default_ask_messages_list,
            'unread_messages_list' => $unread_messages_list

        );
    }

    protected function getUserId()
    {
        $user_id = $this->_request->getParameter('filter_by_user_id');
        if (!empty($user_id)) {
            return $user_id;
        }

        return 0;
    }

    public function filterMessagesList()
    {
        // TODO: Implement filterMessagesList() method.
    }


    protected function filterByUser(&$query)
    {

    }
}
