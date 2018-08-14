<?php

/**
 * Notify mail about new messages in ask-chat
 *
 * @author Сергей
 */
class DealerAskDiscussionMail extends DiscussionMail
{
    function __construct(User $fromUser, User $toUser, $message, $entries = null, $email = '')
    {
        $site_url = sfConfig::get('app_site_url');

        $discussion_messages = array();
        if (!is_null($entries) && count($entries) > 0) {
            foreach ($entries as $entry) {
                $sender = $entry->getUser();
                $message = $this->getMessage($entry);
                if (!$message)
                    continue;

                $text = nl2br(strip_tags($message->getText()));
                $date = date('d.m.Y H:i', D::toUnix($message->created_at));
                $dealer = $entry->getDealer() ? $entry->getDealer()->getName() : '';

                $discussion_messages[] =
                    <<<TEXT
        <p>
          <small>{$dealer}</small>
          <br>
          <i>$date</i>
          <b>{$sender->selectName()}:</b>
          <br>
          {$text}
          <div>{$this->getFiles($message)}</div>
          <br>
          <a href="{$site_url}messages/to/{$message->getId()}">перейти к сообщению</a>
        </p>
TEXT;
            }
        } else {
            $text = nl2br(strip_tags($message->getText()));
            $date = date('d.m.Y H:i', D::toUnix($message->created_at));
            $dealer = $fromUser->getDealer() ? $fromUser->getDealer()->getName() : '';

            $discussion_messages[] =
<<<TEXT
          <p>
          <small>{$dealer}</small>
          <br>
          <i>$date</i>
          <b>{$fromUser->selectName()}:</b>
          <br>
          {$text}
        </p>
TEXT;
        }

        $discussion_messages = implode('', $discussion_messages);
        $email_to = empty($email) ? $toUser->getEmail() : $email;
        parent::__construct(
            $email_to,
            'global/mail_common',
            array(
                'user' => $toUser,
                'subject' => 'Новые сообщения из раздела Контакты',
                'text' =>
<<<TEXT
        <p>Уважаемый пользователь!</p>
        <p>Поступили новые сообщения:</p>
        {$discussion_messages}
TEXT
            )
        );
    }
}
