<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 04.12.2017
 * Time: 16:33
 */
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>VW</title>
</head>

<body style="font-family:Arial, Helvetica, sans-serif; color:#2d2d2d; font-size:14px; line-height:20px">
<?php if (isset($sf_data['user_name'])): ?>
    <p>Здравствуйте, <?php echo $user_name ?>!</p>
<?php else: ?>
    <p>Здравствуйте!</p>
<?php endif; ?>

<p>
    Уважаемый дилер!<br/>
    Не забудьте до конца недели обновить статистику по активности  <strong>"<?php echo $activity_name; ?>"</strong>
    <?php echo $sf_data->getRaw('link'); ?>
</p>

<p>----------------------------------</p>

<p>С уважением,<br>
    Ваша команда Servicepool<br>
    <a href="mailto:<?php echo sfConfig::get('app_mail_sender') ?>"><?php echo sfConfig::get('app_mail_sender') ?></a>
</p>
</body>
</html>

