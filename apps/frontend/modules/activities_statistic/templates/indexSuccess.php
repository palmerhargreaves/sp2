<div id="activities-stats-container">
    <div id="container-budget-panel">
    <?php include_partial('activities_statistic/budget_panel', array('real' => $_real, 'plan' => $_plan, 'current_quarter' => $_current_quarter, 'year' => $_year)); ?>
    </div>

    <div class="section-header f-vw d-cb">
        <h2>Сводная таблица по заявкам</h2>

        <div id="sb-current-activities-list" class="modal-select-wrapper select input krik-select select_white select_ib" style="width: 320px;">
            <span class="select-value">Все активности</span>
            <div class="ico"></div>
            <input type="hidden" name="selected_activity" value="">
            <div class="modal-input-error-icon error-icon"></div>
            <div class="error message"></div>
            <div class="modal-select-dropdown">
                <div class="modal-select-dropdown-item select-item" data-value="">Все активности</div>
                <?php $idx = 0; ?>
                <?php foreach ($activities_list as $activity): $idx++; ?>
                    <div class="modal-select-dropdown-item select-item" style="height: 33px;"
                         data-value="<?php echo $activity->getId(); ?>"><?php echo sprintf('%s - %s', $activity->getId(), $activity->getName()); ?></div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="sb-years-list" class="modal-select-wrapper select input krik-select select_white selected_year" style="width: 320px;">
            <span class="select-value"><?php echo $_year; ?></span>
            <div class="ico"></div>
            <input type="hidden" name="selected_year" value="<?php echo $_year; ?>">
            <div class="modal-input-error-icon error-icon"></div>
            <div class="error message"></div>
            <div class="modal-select-dropdown">
                <div class="modal-select-dropdown-item select-item" data-value=""><?php echo $_year; ?></div>
                <?php foreach (range(D::getYear(time()) - 3, D::getYear(time()))  as $year_item): if ($_year == $year_item) { continue; } ?>
                    <div class="modal-select-dropdown-item select-item" style="height: 33px;"
                         data-value="<?php echo $year_item; ?>"><?php echo $year_item; ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="content_container">
        <h1 class="f-vw"><?php echo !is_null($_activity) ? sprintf('%d - %s', $_activity->getId(), $_activity->getName()) : 'Все активности' ;?></h1>

        <div class="stats-summary f-vw d-cb">
            <?php include_partial('models', array('models_data' => $completed_models, 'allow_extended_filter' => false, 'model_status' => '', 'title' => 'Засчитанные в бюджет квартала', 'activity' => $_activity)); ?>
            <?php include_partial('models', array('models_data' => $in_work_models, 'allow_extended_filter' => true, 'model_status' => $_model_status, 'title' => 'Незасчитанные  в бюджет квартала', 'activity' => $_activity)); ?>
        </div><!-- /stats-summary -->
    </div>
</div>

<p>В таблице указаны суммы в соответствии с текущим статусом согласования заявок/отчетов. Дилерское предприятие
    самостоятельно планирует и рассчитывает необходимые инвестиции на основании данных, указанных в целевом соглашении,
    и несет ответственность за своевременное согласования маркетинговых активностей и загрузку отчетных документов на
    основании требований действующей маркетинговой политики.</p>

<script>
    $(function () {
        window.dealers_models_statistic = new DealersModelsStatistic({
            data_by_filter_url: '<?php echo url_for('@activities_statistic_filter_data');?>',
            data_by_year_filter_url: '<?php echo url_for('@activities_statistic_filter_by_year_data'); ?>',
            content_container: 'stats-summary',
            dealer_id: '<?php echo $_dealer->getId(); ?>',
        }).start();
    });
</script>
