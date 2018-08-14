<?php

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 02.08.2017
 * Time: 15:19
 */
class DealerDiscussionOnline extends DiscussionOnlineAbstract implements DiscussionOnlineInterface
{

    public function getLabel()
    {
        return 'dealer_discussions';
    }

    public function getUnreadCount() {
        $discussion_ids = explode(':', $this->_request->getParameter('discussion_ids'));
        $discussion_unread_count = array();

        foreach ($discussion_ids as $discussion_id) {
            /** @var Discussion $discussion */
            $discussion = DiscussionTable::getInstance()->find($discussion_id);
            if ($discussion) {
                if (!array_key_exists($discussion_id, $discussion_unread_count)) {
                    $discussion_unread_count[$discussion_id] = 0;
                }

                $discussion_unread_count[$discussion_id] += $discussion->countUnreadMessages($this->_user);
            }
        }

        return $discussion_unread_count;
    }

    public function getMessagesData()
    {
        $messages_list = $this->getLastMessagesList();

        $this->_fist_discussion = array_values($messages_list);
        if (!empty($this->_fist_discussion)) {
            $this->_fist_discussion = $this->_fist_discussion[0];
        }

        return array('messages_list' => $messages_list, 'activities_list' => $this->getActivitiesList());
    }

    protected function getDefaultDealerData() {
        $data = array('models_list' => array(), 'messages_list' => array());

        if (!empty($this->_first_dealer)) {
            $default_messages_list = MessageTable::getInstance()->createQuery('m')
                ->where('discussion_id = ?', $this->_first_dealer['message']['discussion_id'])
                ->andWhere('m.created_at LIKE ?', array('%' . date('Y-m') . '%'))
                ->andWhere('m.user_id = ?', $this->_first_dealer['user']['id'])
                ->orderBy('id DESC')
                ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        }

        return $data;
    }

    protected function filterQuery(&$query)
    {
        $query->andWhereIn('m.who_get_message', array('all', 'admin_dealer'))->andWhere('m.user_id = ?', $this->_user->getId());
    }

}
