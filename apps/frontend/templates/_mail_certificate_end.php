<?php
/**
 * Created by PhpStorm.
 * User: kostig51
 * Date: 24.11.2017
 * Time: 14:03
 */
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>VW</title>
</head>

<body style="font-family:Arial, Helvetica, sans-serif; color:#2d2d2d; font-size:14px; line-height:20px">
<?php if (isset($sf_data['user'])): ?>
    <p>Здравствуйте, <?php echo $user->getName() ?>!</p>
<?php else: ?>
    <p>Здравствуйте!</p>
<?php endif; ?>

<p>
    Уважаемый дилер!<br/>
    Вам необходимо заполнить вторую часть статистики по активности  <strong>"<?php echo $sf_data->getRaw('activity')->getName(); ?>"</strong>
    <?php echo $sf_data->getRaw('link'); ?>
</p>

<p>----------------------------------</p>

<p>С уважением,<br>
    Ваша команда Servicepool<br>
    <a href="mailto:<?php echo sfConfig::get('app_mail_sender') ?>"><?php echo sfConfig::get('app_mail_sender') ?></a>
</p>
</body>
</html>
