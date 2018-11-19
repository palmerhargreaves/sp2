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

        <h2 class="h1 d-ttu">Маркетинговый бюджет</h2>
        <div class="report-budget d-cb">

            <?php foreach($information["dealer_budget"]["quarters"] as $quarter => $quarter_data): ?>
                <div class="report-budget__item">
                    <div class="report-budget__item__header ff_head"><?php echo $roman[$quarter]; ?> квартал</div>
                    <dl class="report-budget__item__row">
                        <dt>План:</dt>
                        <dd><?php echo Utils::format_amount($quarter_data["plan"]); ?></dd>
                    </dl>
                    <dl class="report-budget__item__row">
                        <dt>Факт:</dt>
                        <dd><?php echo Utils::format_amount($quarter_data["fact"]); ?></dd>
                    </dl>
                </div>
            <?php endforeach; ?>

            <div class="report-budget__item report-budget__item_annual">
                <div class="report-budget__item__header ff_head">Годовой бюджет</div>
                <div class="report-budget__item__col">
                    <dl class="report-budget__item__row">
                        <dt>План:</dt>
                        <dd><?php echo Utils::format_amount($information["dealer_budget"]["all_year"]["plan"]); ?></dd>
                    </dl>
                    <dl class="report-budget__item__row">
                        <dt>Выполнено:</dt>
                        <dd><?php echo Utils::format_amount($information["dealer_budget"]["all_year"]["fact"]); ?></dd>
                    </dl>
                </div>
                <div class="report-budget__item__col">
                    <dl class="report-budget__item__row">
                        <dt>Выполнено:</dt>
                        <dd><strong><?php echo round($information["dealer_budget"]["all_year"]["completed"], 0); ?>%</strong></dd>
                    </dl>
                    <dl class="report-budget__item__row">
                        <dt>Осталось выполнить:</dt>
                        <dd><?php echo Utils::format_amount($information["dealer_budget"]["all_year"]["left_to_complete"]); ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <?php
            //Разделяем общее количетсов активностей на два столбца

            $items_per_col = count($information['activities']) / 2;
            $item_index = 0;
            $column_index = 0;

            $activities_per_column = array();
            foreach ($information['activities'] as $activity_id => $activity) {
                if (!array_key_exists($column_index, $activities_per_column)) {
                    $activities_per_column[$column_index] = array();
                }

                $activities_per_column[$column_index][] = $activity;

                if ($item_index++ > $items_per_col) {
                    $item_index = 0;
                    $column_index++;
                }
            }
        ?>

        <h2 class="h1 d-ttu">Участие в активностях</h2>
        <div class="report-activity is-flexbox is-flexbox_justify">

            <?php foreach ($activities_per_column as $column_index => $activities): ?>
            <div class="report-activity__col">
                <dl class="report-activity__header ff_head d-tac">
                    <dt>Статус</dt>
                    <dd>Кампания</dd>
                </dl>

                <?php foreach ($activities as $activity): ?>
                <dl class="report-activity__row is-<?php echo $activity['status']['status']; ?> <?php echo $activity['mandatory_activity'] ? 'is-required' : ''; ?>">
                    <dt><figure></figure></dt>
                    <dd><?php echo $activity['name']; ?></dd>
                </dl>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</main>

<footer id="d-footer">
    <div class="d-grid">

        <div class="report-activity__legend is-flexbox is-flexbox_justify">

            <dl class="report-activity__legend__item is-green">
                <dt><figure></figure></dt>
                <dd>Активность выполнена, <br/>статистика заполнена</dd>
            </dl>

            <dl class="report-activity__legend__item is-yellow">
                <dt><figure></figure></dt>
                <dd>Активность выполнена, <br/>статистика не заполнена</dd>
            </dl>

            <dl class="report-activity__legend__item is-orange">
                <dt><figure></figure></dt>
                <dd>Заведена заявка <br/>на согласование</dd>
            </dl>

            <dl class="report-activity__legend__item is-red">
                <dt><figure></figure></dt>
                <dd>В активности участия <br/>не принимал</dd>
            </dl>

            <dl class="report-activity__legend__item is-required">
                <dt><figure></figure></dt>
                <dd>Обязательные <br/>активности</dd>
            </dl>

        </div>

    </div>
</footer>
