<?php

/**
 * Description of DealerUserRegisteredForImporterMail
 *
 * @author Сергей
 */
class ImporterRegisteredForAdminMail extends TemplatedMail
{
  function __construct(User $user, User $admin)
  {
    parent::__construct(
      $admin->getEmail(),
      'global/mail_common', 
      array(
        'user' => $admin,
        'subject' => 'Регистрация',
        'text' => 
<<<TEXT
        <p>В системе был зарегистрирован новый пользователь со стороны импортёра: "{$user->getEmail()}".</p>
TEXT
      )
    );
  }
}
