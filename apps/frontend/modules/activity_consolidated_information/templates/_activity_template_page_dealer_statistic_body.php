<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 16.10.2018
 * Time: 14:13
 */

$roman = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV');

$information = $information->getRawValue();

?>

<main id="d-content">

    <div class="d-grid">

        <table class="tbl_campaign_intro">
            <tbody>
            <tr>
                <td>Тип кампании:</td>
                <td>Сервисная</td>
            </tr>
            <tr>
                <td>Название кампании:</td>
                <td><strong>Service Clinic</strong></td>
            </tr>
            </tbody>
        </table>

        <div class="report-campaign-stats">
            <div class="report-campaign-stats__title"><?php echo $step_header; ?></div>

            <?php foreach ($statistic_data as $section_id => $section_data): ?>
                <div class="report-campaign-stats__table">
                    <div class="report-campaign-stats__caption">
                        <div class="report-campaign-stats__caption__title"><?php echo $section_data['section_header']; ?></div>
                    </div>

                    <?php foreach ($section_data['fields'] as $field): ?>
                        <div class="report-campaign-stats__row">
                            <div class="report-campaign-stats__name"><span><?php echo $field['header']; ?></span></div>
                            <div class="report-campaign-stats__value"><span><?php echo $field['value']; ?></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
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
