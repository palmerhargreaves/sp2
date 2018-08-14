<div class="activity-main-page">
    <h2>Активности</h2>

    <div class="activity-main-page-sums d-cb">
        <?php $n = 0;
        foreach ($company_list_data as $key => $data):
            $item = $data['company_type_item'];
            ?>
            <div class="activity-main-page-sum <?php echo ++$n == 1 ? "d-fl" : "d-fr" ?> <?php echo $n == $filters['filter_by_company'] ? 'active' : ''; ?>" data-id="<?php echo $key; ?>">
                <div class="activity-main-page-sum-dummy" style="position: absolute; width: 470px; height: 150px; cursor: pointer; z-index: 9999999;" data-id="<?php echo $key; ?>"></div>
                <div class="title">
                    <?php echo $item->getName(); ?> (<?php echo $item->getPercent(); ?>%)
                    <div style="float: right; font-size: 16px; margin-top: 4px;"><?php echo Utils::format_amount($data['budget_plan']['total_plan_cash'], 0); ?></div>
                </div>
                <div class="d-progressbar js-progressbar"><i data-percent="<?php echo $data['budget_plan']['completed']; ?>"></i></div>
                <strong class="d-fl" style="width: 40%;">
                    <span class="completed-percents">Выполнено (<?php echo $data['budget_plan']['completed']; ?>%)</span>
                    <span class="completed-cash"><?php echo Utils::format_amount($data['budget_plan']['complete_cash'], 0); ?></span>
                </strong>

                <?php if ($data['budget_plan']['completed_over'] != 0): ?>
                    <strong class="d-fr">Перевыполнение (<?php echo $data['budget_plan']['completed_over']; ?>%)</strong>
                <?php else: ?>
                    <strong class="d-fr" style="width: 45%;">
                        <span class="completed-percents">
                            Осталось выполнить (<?php echo $data['budget_plan']['wait']; ?>%)
                            <span class="completed-cash"><?php echo Utils::format_amount($data['budget_plan']['wait_cash'], 0); ?></span>
                        </span>

                    </strong>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div><!-- /activity-main-page-sums -->

    <div class="activity-main-content">
    <?php include_partial('activities_items', array('company_list_data' => $company_list_data, 'filters' => $filters)); ?>
    </div>

    <form id="frm-activities-list">
        <input type="hidden" name="filter_by_year" value="<?php echo $filters['filter_by_year'] ; ?>" />
        <input type="hidden" name="filter_by_status" value="<?php echo $filters['filter_by_status'] ; ?>" />
        <input type="hidden" name="filter_by_company" value="<?php echo $filters['filter_by_company'] ; ?>" />
        <input type="hidden" name="filter_field_name" value="<?php echo $filters['filter_by_sort']['sort_field'] ; ?>" />
        <input type="hidden" name="filter_field_direction" value="<?php echo $filters['filter_by_sort']['sort_direction'] ; ?>" />
    </form>
</div>

<script>
    $(function() {
        window.company_types = new ActivitiesCompanyTypes({
            activities_filter_by_url: "<?php echo url_for('@activities_filter_by'); ?>",
            activity_tab: '.js-activity-tab'
        }).start();
    });
</script>
