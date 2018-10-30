<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 16.10.2018
 * Time: 14:13
 */

$activity_statistic_data = $consolidated_information->getActivityStatistic();
$activity_statistic = $activity_statistic_data['statistic_data'];
$fields_values_by_max = $activity_statistic_data['fields_values_by_max']->getRawValue();

?>

<main id="d-content">

    <div class="d-grid">

        <?php include_partial('quarters_list', array('consolidated_information' => $consolidated_information)); ?>

        <h2 class="h1 d-ttu fw_400"><?php echo $consolidated_information->getCompany()->getName(); ?></h2>

        <h2 class="report-campaign-title is-flexbox">
            <span><?php echo $consolidated_information->getActivity()->getId(); ?>.</span>
            <span><?php echo $consolidated_information->getActivity()->getName(); ?></span>
        </h2>

        <h2 class="report-campaign-title is-flexbox">
            <span>Ключевая статистика</span>
        </h2>
        <div class="report-campaign-box">

            <?php foreach ($activity_statistic as $key => $statistic_item): ?>
                <?php if ($statistic_item['section_data']->getGraphType() == ActivityConsolidatedInformation::GRAPH_TYPE_WATERFALL): ?>
                    <div class="report-campaign-chart-row is-flexbox">
                        <div class="report-campaign-chart-row__body">
                            <div class="report-campaign-funnel report-campaign-funnel_sm is-flexbox is-flexbox_center">
                                <div class="report-campaign-funnel__values">
                                    <div class="report-campaign-funnel__values__chart">
                                        <ul class="d-plain is-flexbox">
                                            <?php if (!empty($fields_values_by_max)): ?>
                                                <?php

                                                $statistic_fields_list = array();
                                                foreach ($fields_values_by_max as $field_id => $value) {
                                                    if (array_key_exists($field_id, $statistic_item['fields']->getRawValue())) {
                                                        $field = $statistic_item['fields'][$field_id];
                                                        $statistic_fields_list[$field_id] = array('name' => $field['name'], 'value' => $value, 'color' => $field['color_value'], 'color_name' => $field['color_name']);
                                                    }
                                                }

                                                $field_index = 1;
                                                foreach ($statistic_fields_list as $field_id => $field): ?>
                                                    <?php if ($field_index > 3) {
                                                        break;
                                                    } ?>
                                                    <li class="is-flexbox is-flexbox_center">
                                                        <span><?php echo $field['value']; ?></span>
                                                    </li>
                                                    <?php $field_index++; endforeach; ?>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="report-campaign-chart-row__legend">
                            <h3><?php echo $statistic_item['section_data']->getHeader(); ?></h3>
                            <ul class="report-campaign-chart-legend d-plain">
                                <?php
                                    $field_index = 1;
                                    $field_colors = arraY('is-blue', 'is-blue2', 'is-gray', 'is-gray2', 'is-gray3');
                                ?>

                                <?php foreach ($statistic_fields_list as $field_id => $field): ?>
                                    <?php if ($field_index > 3) {
                                        break;
                                    } ?>
                                    <li class="<?php echo $field_colors[$field_index - 1]; ?>"><?php echo $field['name']; ?></li>
                                <?php $field_index++; endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="report-campaign-chart-row is-flexbox">
                        <div class="report-campaign-chart-row__body">
                            <?php if (!empty($statistic_item['graph_url'])): ?>
                                <img src="<?php echo $statistic_item['graph_url']; ?>" alt=""/>
                            <?php endif; ?>
                        </div>

                        <div class="report-campaign-chart-row__legend">
                            <h3><?php echo $statistic_item['section_data']->getHeader(); ?></h3>
                            <ul class="report-campaign-chart-legend d-plain <?php echo count($statistic_item['fields']) > 3 ? "report-campaign-chart-legend_cols" : ""; ?>">
                                <?php foreach ($statistic_item['fields'] as $field): ?>
                                    <li class="is-<?php echo $field['color_name']; ?>"><?php echo $field['name']; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php
            $other_blocks = $consolidated_information->getActivityStatisticOtherBlocks();
            if (!empty($other_blocks)):
                foreach ($other_blocks as $block_key => $block_data):
                    if (count($block_data['fields'])):
                        ?>
                        <div class="report-campaign-sum">
                            <h3><?php echo $block_data['section_data']->getHeader(); ?></h3>

                            <?php foreach ($block_data['fields'] as $field): ?>
                                <dl class="d-plain is-flexbox is-flexbox_center">
                                    <dt><?php echo $field['name']; ?></dt>
                                    <dd class="<?php echo !empty($field['custom_function']) ? "is-green" : ""; ?>"><?php echo $field['value']; ?></dd>
                                </dl>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>

    </div>

</main>

<footer id="d-footer">
    <div class="d-grid">

        <div class="d-footer">
            <div class="d-footer__title">Примечание</div>
            <div>Данные дилерских центров, заполнивших статистику на портале dm.vw-servicepool.ru</div>
        </div>

    </div>
</footer>
