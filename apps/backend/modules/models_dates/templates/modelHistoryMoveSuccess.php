<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 12.07.2016
 * Time: 10:25
 */
?>

<table class="table">
    <tr>
        <th>Заявка</th>
        <th>Пользователь</th>
        <th>Сообщение</th>
    </tr>
    <?php
    foreach ($history_list as $item):
        ?>
        <tr>
            <td><?php echo $item->getObjectId(); ?></td>
            <td><?php echo sprintf('<strong>%s</strong><br/> %s', $item->getUser()->getName(), $item->getLogin()); ?></td>
            <td><?php echo $item->getRawValue()->getDescription(); ?></td>
        </tr>
    <?php
    endforeach;;
    ?>
</table>
