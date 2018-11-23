<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 16.10.2018
 * Time: 14:13
 */

$roman = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV');

$activities_list = $activities_list->getRawValue();

$activity = null;
if (array_key_exists($activity_id, $activities_list)) {
    $activity = $activities_list[$activity_id];
}
?>

<main id="d-content">

    <div class="d-grid">

        <?php if ($activity): ?>
            <table class="tbl_campaign_intro">
                <tbody>
                <tr>
                    <td>Тип кампании:</td>
                    <td><?php echo $activity['company_name']; ?></td>
                </tr>
                <tr>
                    <td>Название кампании:</td>
                    <td><strong><?php echo $activity['activity_name']; ?></strong></td>
                </tr>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="report-campaign-stats">
            <div class="report-campaign-stats__table">
                <?php foreach ($statistic_data as $field): ?>
                    <div class="report-campaign-stats__row">
                        <div class="report-campaign-stats__name"><span><?php echo $field['header']; ?></span>
                        </div>
                        <div class="report-campaign-stats__value"><span><?php echo $field['value']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</main>

<footer id="d-footer">
    <div class="d-grid">

        <div class="d-footer">
            <div class="d-footer__title">Примечание</div>
            <div>На основе данных статистики, загруженной диллерским центром на портал dm.vw-servicepool.ru (последняя
                по дате загрузки)
            </div>
        </div>

    </div>
</footer>
