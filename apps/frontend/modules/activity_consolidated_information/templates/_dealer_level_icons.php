<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 25.10.2018
 * Time: 10:58
 */

?>
<?php foreach ($completed_by_levels[$level_index] as $level): ?>


<tr>
    <td class="report-dealers__num"><?php echo $level['dealer']['number']; ?></td>
    <td class="report-dealers__title"><?php echo $level['dealer']['name']; ?></td>
    <td class="report-dealers__icons">
        <?php if ($level['service_action']): ?><img src="http://dm.vw-servicepool.ru/pdf/img/ico_plus.png" alt="" /> <?php endif; ?>
        <?php if ($level['models_completed']): ?><img src="http://dm.vw-servicepool.ru/pdf/img/ico_pencil.png" alt="" /><?php endif; ?>
        <?php if ($level['statistic_completed']): ?><img src="http://dm.vw-servicepool.ru/pdf/img/ico_check2.png" alt="" /><?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
