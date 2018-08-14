<div class="content-header">Управление дилерским предприятием</div>
<div class="content-desc">Ниже перечислены сотрудники, зарегистрированные от Вашего дилерского предприятия. Пожалуйста, удалите тех, кто у Вас больше не работает или чей e-mail не актуален.</div>

<div class="contributors-wrapper" id="dealer-users">
<?php foreach($users as $user): ?>
        <div class="contributor">
                <div class="name"><?php echo $user->getName(), ' ', $user->getSurname() ?></div>
                <div class="position"><?php echo $user->getPost() ?></div>
                <div class="email"><?php echo $user->getEmail() ?></div>
                <div class="remove"><a href="<?php echo url_for('dealer_user/delete?id='.$user->getId()) ?>" class="del-link"></a></div>
                <div class="cf"></div>					
        </div>
<?php endforeach; ?>
</div>

<script type="text/javascript">
  $(function() {
    $('#dealer-users .contributor').click(function() {
      confirm_delete($(this).closest('.contributor').find('a.del-link').attr('href'), 'Подтвердите удаление пользователя');
      
      return false;
    });
  });
</script>
