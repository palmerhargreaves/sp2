<?php

/**
 * discussion actions.
 *
 * @package    Servicepool2.0
 * @subpackage discussion
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class discussionActions extends ActionsWithJsonForm
{
    /**
     * Executes state action
     *
     * @param sfRequest $request A request object
     */
    const FILTER_NAMESPACE = 'messages';

    public function executeState(sfWebRequest $request)
    {
        $discussion = $this->getDiscussion($request);
        $discussion->updateOnline($this->getUser()->getAuthUser());

        $start_message = $request->getParameter('start', false);
        if ($start_message)
            $this->messages = $discussion->getLastMessagesFrom($start_message, $this->getUser()->getAuthUser(), true);
        else
            $this->messages = $discussion->getLastMessages(10, $this->getUser()->getAuthUser());

        $this->outputFiles($this->messages);
        $this->setTemplate('messages');
        $this->setLayout(false);
    }

    public function executeNewMessages(sfWebRequest $request)
    {
        $discussion = $this->getDiscussion($request);
        $discussion->updateOnline($this->getUser()->getAuthUser());

        $this->messages = $discussion->getUnreadMessages($this->getUser()->getAuthUser());
        $this->outputFiles($this->messages);
        $this->setTemplate('messages');
        $this->setLayout(false);
    }

    public function executePrevious(sfWebRequest $request)
    {
        $this->messages = $this->getDiscussion($request)->getPreviousMessages(10, $request->getParameter('before'), $this->getUser()->getAuthUser());
        $this->outputFiles($this->messages);
        $this->setTemplate('messages');
        $this->setLayout(false);
    }

    public function executeSearch(sfWebRequest $request)
    {
        $this->messages = $this->getDiscussion($request)->searchMessages($request->getParameter('text'), $this->getUser()->getAuthUser());
        $this->outputFiles($this->messages);
        $this->setTemplate('messages');
        $this->setLayout(false);
    }

    public function executePost(sfWebRequest $request)
    {
        $text = trim(strip_tags($request->getPostParameter('message')));
        /*if (!$text)
            return sfView::NONE;*/

        $files = $request->getParameter('files');
        if ((!$files || !is_array($files)) && empty($text)) {
            return sfView::NONE;
        }

        $discussion = $this->getDiscussion($request);
        $user = $this->getUser()->getAuthUser();

        $message = new Message();
        $message->setDiscussion($discussion);
        $message->setUser($user);
        $message->setUserName($user->selectName());
        $message->setText($text);
        $message->save();

        $this->saveFiles($message, $request);

        $logEntry = LogEntryTable::getInstance()->createQuery()->where('message_id = ?', $message->getId())->execute();
        if($user->getAllowReceiveMails()) {
            new DealerDiscussionMail($user, $logEntry, sfConfig::get('app_mail_sender'));
        }

        return $this->sendJson(array('success' => true, 'message_data' => Utils::formatMessageData($message, false)));
    }

    public function executeDealerDiscussion(sfWebRequest $request)
    {
        $dealer = DealerTable::getInstance()->find($request->getParameter('id'));
        $this->forward404Unless($dealer);

        $discussion = DealerDiscussionTable::getInstance()->findDiscussion($dealer);

        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent(json_encode(array('id' => $discussion->getId())));

        return sfView::NONE;
    }

    public function executeCheckForOnline(sfWebRequest $request)
    {
        $online = array();
        foreach ($this->getDiscussion($request)->getOnlineUsersInOnlinePeriod() as $user)
            $online[$user->getId()] = true;

        $this->getResponse()->setContentType('application/json');
        $this->getResponse()->setContent(json_encode($online));

        return sfView::NONE;
    }

    protected function outputFiles($messages)
    {
        $ids = array();
        foreach ($messages as $message)
            $ids[] = $message->getId();

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

            if (file_exists(sfConfig::get('app_uploads_path').'/'.MessageFile::FILE_PATH.'/'.$file->getFileName())) {
                $grouped_files[$message_id][] = $file;
            }
        }

        $this->files = $grouped_files;
    }

    protected function saveFiles(Message $message, sfWebRequest $request)
    {
        $files = $request->getParameter('files');
        if (!$files || !is_array($files))
            return;

        foreach ($files as $temp_id) {
            $temp_file = TempFileTable::getInstance()
                ->createQuery()
                ->where('user_id=? and id=?', array($this->getUser()->getAuthUser()->getId(), $temp_id))
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
     * Returns discussion
     *
     * @param sfWebRequest $request
     * @return Discussion
     */
    protected function getDiscussion(sfWebRequest $request)
    {
        $id = $request->getParameter('id');
        $discussion = DiscussionTable::getInstance()->find($id);

        if (!$discussion) {
            $this->forward404('обсуждение не найдено');
        }

        return $discussion;
    }

    public function executeAllMessages()
    {
        $query = MessageTable::getInstance()
            ->createQuery('m')
            ->select('*')
            ->leftJoin('m.PrivateUser u')
            ->leftJoin('u.Group gr')
            //->where('m.created_at LIKE ?', '%'.date('Y-m').'%')
            ->andWhere('m.user_id != ?', $this->getUser()->getAuthUser()->getId())
            ->andWhere('m.system != 1')
            ->orderBy('m.id DESC');
        //->limit(100)
        //->execute();
        $this->initPager($query);
        $this->initPaginatorData(null, 'discussion_all_messages');
    }

    public function executeSpecialMessagesList(sfWebRequest $request)
    {
        $discussion = $this->getDiscussion($request);
        $discussion->updateOnline($this->getUser()->getAuthUser());

        $this->messages = $discussion->getLastMessagesForDiscussion();

        $this->setTemplate('messages');
        $this->setLayout(false);
    }

    public function executeSpecialMessageAdd(sfWebRequest $request)
    {
        $user = UserTable::getInstance()->createQuery()->select('*')->where('id = ?', $request->getParameter('userId'))->fetchOne();
        if (!$user)
            $this->messages = '';
        else {
            $discussion = $this->getDiscussion($request);
            $discussion->updateOnline($user);

            $userMsg = $discussion->getLastMessageUser();
            if (!empty($userMsg) && $userMsg->getUser()->getId() != $user->getId()) {

                if($userMsg->getUser()->getAllowReceiveMails()) {
                    $message = new UserDiscussionInform($userMsg->getUser(), $userMsg);
                    $message->setPriority(1);
                    sfContext::getInstance()->getMailer()->send($message);
                }
            }

            $lastMsg = $discussion->addNewMessage($request, $user);
            if ($lastMsg)
                $this->saveFiles($lastMsg, $request);

            $this->messages = $discussion->getLastMessagesForDiscussion();
        }

        $this->setTemplate('messages');
        $this->setLayout(false);
    }

    public function executeSwitchToDealer(sfWebRequest $request)
    {
        if ($this->getUser()->isManager() || $this->getUser()->isImporter()) {
            $dealer = DealerTable::getInstance()->find($request->getParameter('dealer'));
            $this->forward404Unless($dealer);

            $dealer_user = DealerUserTable::getInstance()->findOneByUserId($this->getUser()->getAuthUser()->getId());

            if (!$dealer_user) {
                $dealer_user = new DealerUser();
                $dealer_user->setUser($this->getUser()->getAuthUser());
                $dealer_user->setManager(true);
            }

            $dealer_user->setDealer($dealer);
            $dealer_user->save();
        }

        $activityId = $request->getParameter('activityId');
        $modelId = $request->getParameter('modelId');

        //'/activity/' + $(this).data('activity-id') + '/module/agreement/models/model/' + $(this).data('model'), '_blank');
        $model = AgreementModelTable::getInstance()->find($modelId);

        $model_date = $model->getModelQuarterDate();
        $model_quarter = D::getQuarter($model_date);
        $model_year = D::getYear($model_date);

        //Коррекция по году и кварталу
        //list($model_year, $model_quarter) = Utils::correctYearAndQ($model->getCreatedAt(), $model_year, $model_quarter);

        $this->redirect("/activity/{$activityId}/module/agreement/models/model/{$modelId}/quarter/" . $model_quarter . '/year/' . $model_year);

        //$this->redirect("/activity/{$activityId}/module/agreement/models/model/{$modelId}/quarter/".$model_quarter);
    }

    private function initPager($query)
    {
        $request = $this->getRequest();
        $page = $request->getParameter('page', 1);
        if ($page) {
            $max_items_on_page = sfConfig::get('app_max_items_on_page');
        } else {
            $max_items_on_page = 0;
            $page = 1;
        }

        $this->pager = new sfDoctrinePager(
            'Message',
            $max_items_on_page
        );

        $this->pager->setQuery($query);
        $this->pager->setPage($page);
        $this->pager->init();

        if ($this->pager->getLastPage() < $page) $this->pager->setPage($this->pager->getLastPage());
        $this->pager->init();
    }

    private function initPaginatorData($route_object, $route_name)
    {
        $request = $this->getRequest();
        $this->parameters = $request->getGetParameters();
        $this->pageLinkArray = array_merge($this->parameters, array('sf_subject' => $route_object));

        $this->paginatorData = array('pager' => $this->pager,
            'pageLinkArray' => $this->pageLinkArray,
            'route' => $route_name);
    }

    public function executeDownloadFile(sfWebRequest $request)
    {
        $id = $request->getParameter('file');

        $msgFile = MessageFileTable::getInstance()->find($id);
        if ($msgFile) {
            $filePath = sfConfig::get('app_uploads_path') . '/' . MessageFile::FILE_PATH . '/' . $msgFile->getFileName();
            if (!F::downloadFile($filePath, $msgFile->getFile())) {
                $this->getResponse()->setContentType('application/json');
                $this->getResponse()->setContent(json_encode(array('success' => false, 'message' => 'Файл не найден')));
            }
        }

        return sfView::NONE;
    }

    public function executeLoadChatLastMessagesAndFiles(sfWebRequest $request)
    {
        $this->msg_text = '';

        $this->makeMessagesListByQuery($request, array(Message::MSG_STATUS_DECLINED, Message::MSG_STATUS_DECLINED_TO_SPECIALST, Message::MSG_STATUS_DECLINED_BY_SPECIALIST));
    }

    public function executeLoadChatLastMessagesAndDealerFiles(sfWebRequest $request)
    {
        $this->makeMessagesListByQuery($request, array(Message::MSG_STATUS_SENDED, Message::MSG_STATUS_DECLINED_TO_SPECIALST, Message::MSG_STATUS_SENDED_TO_SPECIALIST, Message::MSG_STATUS_DECLINED_BY_SPECIALIST));

        $this->msg_text = 'Загруженные файлы:';
        $this->setTemplate('loadChatLastMessagesAndFiles');
    }

    private function makeMessagesListByQuery($request, $msg_status) {
        $discussion = $this->getDiscussion($request);

        $query = MessageTable::getInstance()->createQuery();

        if (!is_array($msg_status)) {
            $query->andWhere('msg_status = ?', $msg_status);
        } else {
            $query->andWhereIn('msg_status', $msg_status);
        }

        $this->msg_text = '';
        $this->model = $discussion->getModels()->getFirst();
        $this->messages = $query->andWhere('discussion_id = ?', $discussion->getId())
            ->andWhere('msg_show = ?', true)
            ->orderBy('id DESC')
            ->limit(10)
            ->execute();

        $this->messages_files = array();
        $this->last_message = null;

        if (count($this->messages) > 0) {
            $checked_users = array();
            $this->users_messages = array();
            $this->messages_files = array();

            foreach ($this->messages as $message) {
                if (!in_array($message->getUserId(), $checked_users)) {
                    $this->last_message = $message;

                    $checked_users[] = $message->getUserId();

                    $this->users_messages[$message->getId()] = $message;
                    $temp_messages_files[] = F::getMessagesFiles($message->getFiles());
                }
            }

            foreach ($temp_messages_files as $key => $msg_files) {
                foreach($msg_files as $msg_file) {
                    $this->messages_files[] = $msg_file;
                }
            }
        }
    }

    /**
     * Pack message files to archive
     * @param sfWebRequest $request
     * @return string
     */
    public function executeLoadMessageFiles(sfWebRequest $request) {
        $messages = explode(':', $request->getParameter('messages_ids'));
        if (!empty($messages)) {
            $gen_file = date('h:m:s', time()).'_'.date('d-m-Y', time()).'.zip';

            $zip = new ZipArchive();
            $zipFile = sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR .'messages'. DIRECTORY_SEPARATOR .$gen_file;

            @unlink($zipFile);
            $zip_handler = $zip->open($zipFile, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);

            if ($zip_handler) {
                $users_dir = array();
                foreach ($messages as $key => $msg_id) {
                    $message = MessageTable::getInstance()->find($msg_id);

                    $message_files = $message->getFiles();
                    foreach ($message_files as $file) {
                        $file_path = sfConfig::get('app_uploads_path') . '/' . MessageFile::FILE_PATH . '/' . $file->getFile();

                        $user_email = $message->getUser()->getEmail();
                        if (!in_array($user_email, $users_dir)) {
                            $files_dir[] = $user_email;

                            $zip->addEmptyDir($user_email);
                        }

                        $info = pathinfo($file->getFile());
                        $fileInfo = sprintf('[%s] %s.%s', $file->getId(), $info['filename'], $info['extension']);

                        $zip->addFile($file_path, $user_email . '/' . $fileInfo);
                    }
                }

                $zip->close();
            }

            return $this->sendJson(arraY('success' => true, 'file_name' => $gen_file));
        }

        return $this->sendJson(array('success' => false));
    }
}
