<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 10.10.2018
 * Time: 11:25
 */

$dealers_information = $consolidated_information->getDealersInformation();
?>

<div class="activity-summary__stats__item">
    <strong data-value="<?php echo $dealers_information['count']; ?>"><?php echo $dealers_information['count']; ?></strong>
    Все дилеры
</div>
<div class="activity-summary__stats__item">
    <strong data-value="<?php echo $dealers_information['service_action_count']; ?>"><?php echo $dealers_information['service_action_count']; ?></strong>
    Дилеры-участники акции
</div>
<div class="activity-summary__stats__item">
    <strong data-value="<?php echo $dealers_information['models_count']; ?>"><?php echo $dealers_information['models_count']; ?></strong>
    Дилеры, приступившие к&nbsp;активности
</div>
<div class="activity-summary__stats__item">
    <strong data-value="<?php echo $dealers_information['statistic_completed_count']; ?>"><strong><?php echo $dealers_information['statistic_completed_count']; ?></strong></strong>
    Дилеры, заполнившие статистику
</div>
