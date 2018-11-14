<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 16.10.2018
 * Time: 14:13
 */

$roman = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV');


$dealers_total_cost = $data['dealers_total_cost']->getRawValue();
$dealer_total_models_cost_by_categories = $data['dealer_total_models_cost_by_categories']->getRawValue();

$activity = $data['activity_data']->getRawValue();
$tick_values = $data['tick_values']->getRawValue();
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

        <h2 class="h1 d-ttu">Затраты на продвижение <span class="d-ttn">(руб.)</span></h2>

        <div class="report-chart">
            <?php

            foreach ($dealers_total_cost as $category_id => $category_data):
                $category = AgreementModelCategoriesTable::getInstance()->find($category_id);
                if ($category && !$category->getIsBlank()):
            ?>
                <div class="report-chart__row">
                    <div class="report-chart__caption"><?php echo $category->getName(); ?></div>
                    <div class="report-chart__item" style="width:<?php echo round($category_data['percent'], 0); ?>%;">
                        <span> <?php echo Utils::format_number($category_data['total_models_cost'], 0); ?></span>
                    </div>
                    <!--<div class="report-chart__item is-orange" style="width:13%;"><span> 13 333,99</span></div>-->
                    <?php if (array_key_exists($category_id, $dealer_total_models_cost_by_categories)): ?>
                        <div class="report-chart__item is-blue" style="width:<?php echo round($dealer_total_models_cost_by_categories[$category_id]['percent'],0); ?>%;">
                            <span> <?php echo Utils::format_number($dealer_total_models_cost_by_categories[$category_id]['total_cost'], 0); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="report-chart__axis">
                <?php foreach ($tick_values as $value): ?>
                    <div><span><?php echo Utils::format_number($value, 0); ?></span></div>
                <?php endforeach; ?>
            </div>

        </div>

    </div>

</main>

<footer id="d-footer">
    <div class="d-grid">

        <div class="d-footer">
            <div class="d-footer__title">Примечание</div>
            <div>На основе данных статистики, загруженной диллерским центром на портал dm.vw-servicepool.ru (последняя по дате загрузки)</div>
        </div>

    </div>
</footer>
