<?php

/**
 * discussion_online actions.
 *
 * @package    Servicepool2.0
 * @subpackage discussion
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class discussion_onlineActions extends ActionsWithJsonForm
{
    /**
     * Executes state action
     *
     * @param sfRequest $request A request object
     */
    const FILTER_NAMESPACE = 'messages';

    public function executeIndex(sfWebRequest $request)
    {
        $this->discussions = $this->getDiscussion($request);
    }

    /**
     * Принудительный переход в сообщение
     * @param sfWebRequest $request
     * @return void
     */
    public function executeMessageTo(sfWebRequest $request) {
        $this->discussions = $this->getDiscussion($request);
        $move_to_message_id = $request->getParameter('message_id', 0);

        //Автоматический переход к сообщению полученному от пользоватея
        if ($move_to_message_id != 0) {
            $message = MessageTable::getInstance()->find($move_to_message_id);
            if ($message) {
                if ($message->getUser()->getDealerUsers()->getFirst()) {
                    $this->dealer_to_go = $message->getUser()->getDealerUsers()->getFirst()->getDealer();
                }
            }
        }

        $this->setTemplate('index');
    }

    /**
     * Load messages list by model
     * @param sfWebRequest $request
     * @return string
     */
    public function executeMessagesList(sfWebRequest $request)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $discussions = $this->getDiscussion($request);
        $this->messages_list = $discussions->messagesList();

        return $this->sendJson(
            array
            (
                'success' => true,
                'messages_list' => get_partial('messages_list', array('messages_list' => $this->messages_list))
            )
        );
    }

    public function executeAskMessagesList(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        return $this->sendJson(
            array
            (
                'success' => true,
                'messages_list' => get_partial('messages_list', array('messages_list' => $this->getDiscussion($request)->messagesList(Message::MSG_TYPE_ASK)))
            )
        );
    }

    public function executeLoadDiscussions(sfWebRequest $request)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $discussions = $this->getDiscussion($request);
        $messages_data = $discussions->getMessagesData();

        return $this->sendJson(
            array
            (
                'success' => true,
                'first_message' => $discussions->getFirstDiscussion(),
                'discussions' => get_partial('load_discussions',
                    array (
                        'messages_data' => $messages_data,
                    ))
            )
        );
    }

    public function executeDiscussionUnreadCount(sfWebRequest $request) {
        return $this->sendJson(array('unread_count' => $this->getDiscussion($request)->getUnreadCount()));
    }

    public function executeDiscussionUnreadModelsCount(sfWebRequest $request) {
        return $this->sendJson($this->getDiscussion($request)->getUnreadModelsCount());
    }

    public function executeLoadDiscussionsByDealersUnreadCount(sfWebRequest $request) {
        return $this->sendJson(array('unread_count' => $this->getDiscussion($request)->getUnreadCount()));
    }

    public function executeDiscussionVisibility(sfWebRequest $request)
    {
        return $this->sendJson(array('unread_count' => $this->getDiscussion($request)->getDiscussionVisibility()));
    }

    public function executePostMessage(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $post_data = $this->getDiscussion($request)->postMessage();

        $post_data['response']['messages_list'] = get_partial('messages_list_in_chat', array(
            'messages_list' => $post_data['response']['messages_list']
        ));

        return $this->sendJson($post_data);
    }

    public function executeDealerDiscussionsList(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $discussions = $this->getDiscussion($request)->getDealerDiscussionsList();
        return $this->sendJson(
            array
            (
                'success' => true,
                'dealer_discussions' => get_partial('discussion_online/admin_importer/_dealer_messages_list',
                    array (
                        'default_dealer_data' => $discussions['default_dealer_data'],
                    )),
                'dealer_discussions_messages' => get_partial('messages_list', array(
                    'messages_list' => $discussions['default_messages_list']
                )),
                'dealer_discussion_ask_messages_list' => get_partial('messages_list', array(
                    'messages_list' => $discussions['default_ask_messages_list']
                )),
                'unread_messages_list' => get_partial('messages_list', array(
                    'messages_list' => $discussions['unread_messages_list']
                ))
            )
        );
    }

    public function executeGetChatMessagesList(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $discussions = $this->getDiscussion($request);
        $messages_list = $discussions->messagesList();

        return $this->sendJson(
            array
            (
                'success' => true,
                'messages_list' => get_partial('messages_list_in_chat', array( 'messages_list' => $messages_list ))
            )
        );
    }

    /**
     * @param sfWebRequest $request
     * @return DiscussionOnlineAbstract
     */
    private function getDiscussion(sfWebRequest $request) {
        return DiscussionOnlineFactory::getInstance()->createClass($this->getUser()->getAuthUser(), $request, $this->getResponse());
    }

    /**
     * Открыть заявку для админа / менеджера / импортера
     * @param sfWebRequest $request
     */
    public function executeAgreementModelOpenFromChat(sfWebRequest $request) {

    }

    /**
     * Открыть заявку для дилера
     * @param sfWebRequest $request
     */
    public function executeModelOpenFromChat(sfWebRequest $request) {

    }
}
