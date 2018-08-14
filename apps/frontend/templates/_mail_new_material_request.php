<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 26.08.2016
 * Time: 13:25
 */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>VW</title>
</head>

<body style="font-family:Arial, Helvetica, sans-serif; color:#2d2d2d; font-size:14px; line-height:20px">

<?php
$user = $sf_data->getRaw('user');
$dealer = $user->getDealer();

$data = $sf_data->getRaw('data');
?>

Пользователь: <?php echo sprintf('%s %s', $user->getSurname(), $user->getName()) ?><br/>
Дилер: <?php echo sprintf('%s %s', $dealer->getNumber(), $dealer->getName()); ?><br/>
<?php echo $user->getEmail(); ?> </br><br/>

<table width="564" border="1" bordercolor="#ced8d9" cellspacing="0" cellpadding="10">
    <tr>
        <td bgcolor="#eaeeed">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>Тип</td>
                    <td align="right"><?php echo $data['model_type']; ?></td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>Название</td>
                    <td align="right"><?php echo $data['material_name']; ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td bgcolor="#eaeeed">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>Размер, мм</td>
                    <td align="right"><?php echo $data['material_width_height']; ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>Формат</td>
                    <td align="right"><?php echo $data['material_format']; ?></td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td bgcolor="#eaeeed">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>Объем</td>
                    <td align="right"><?php echo $data['material_volume']; ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>Обязательный текст</td>
                    <td align="right"><?php echo $data['material_required_info']; ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <?php if (!empty($data['material_suggestions'])): ?>
    <tr>
        <td bgcolor="#eaeeed">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>Ваши пожелания</td>
                    <td align="right"><?php echo $data['material_suggestions']; ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <?php endif; ?>
</table>

</body>
</html>
