<?php include_partial('modal_model', array('decline_reasons' => $decline_reasons, 'decline_report_reasons' => $decline_report_reasons, 'specialist_groups' => $specialist_groups)) ?>
<?php include_partial('menu', array('active' => 'agreement', 'budYears' => $budgetYears, 'year' => $year, 'url' => 'agreement_module_management_models')) ?>
<div class="approvement" style="min-height: 55px;">
    <div style="display: block; width: 100%; float: left; margin-bottom: 20px;">
        <div style='float:left; width: 25%;'>
            <h1>Согласование</h1>
        </div>

        <?php
        $concepts_filter_items = array(
            'all' => 'Все концепции',
            'regional_manager' => 'Отправлено импортеру'
            /*'declined_concepts' => 'Отклоненные концепции',
            'declined_reports' => 'Отклоненные отчеты',
            'concept_in_work' => 'Концепция на проверке',
            'reports_in_work' => 'Отчеты на проверке',
            'accepted_concepts' => 'Согласованные концепции',
            'accepted_reports' => 'Согласованные отчеты'*/
        );

        ?>
        <div style='float:left; width: 75%;'>
            <div id="filters">
                <form action="<?php echo url_for('@agreement_module_management_importer_models') ?>" method="get">
                    <div class="modal-select-wrapper krik-select select type filter" style="width: 200px;">
                        <span class="select-value"><?php echo $concepts_filter_items[$sf_data->getRaw('concept_filter')] ?></span>

                        <div class="ico"></div>
                        <input type="hidden" name="concept_filter" value="<?php echo $concept_filter ?>">

                        <div class="modal-input-error-icon error-icon"></div>
                        <div class="error message"></div>
                        <div class="modal-select-dropdown">
                            <?php foreach ($concepts_filter_items as $value => $name): ?>
                                <div class="modal-select-dropdown-item select-item"
                                     data-value="<?php echo $value ?>"><?php echo $name ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (count($activities)): ?>
                    <div class="modal-select-wrapper krik-select select type filter" style="margin-left: 7px; width: 370px;">
                        <?php if ($activity_filter): ?>
                            <span class="select-value"><?php echo $activity_filter->getRawValue() ?></span>
                        <?php else: ?>
                            <span class="select-value">Все активности</span>
                        <?php endif; ?>

                        <input type="hidden" name="activity_id" value="<?php echo $activity_filter ? $activity_filter->getId() : 0 ?>">

                        <div class="ico"></div>
                        <div class="modal-input-error-icon error-icon"></div>
                        <div class="error message"></div>
                        <div class="modal-select-dropdown">
                            <div class="modal-select-dropdown-item select-item" data-value="0">Все активности</div>
                            <?php foreach ($activities as $activity): ?>
                                <div class="modal-select-dropdown-item select-item"
                                     data-value="<?php echo $activity['id'] ?>"><?php echo sprintf('[%s] %s', $activity['id'], $activity['name']); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

    </div>

    <br/>
    <div id="agreement-models" data-url='<?php echo url_for('@agreement_module_management_model_unblock'); ?>'>
        <?php
        include_partial('concepts', array(
            'wait_filter' => $wait_filter,
            'concepts' => $concepts,
        ));
        ?>
    </div>
</div>

<script type="text/javascript">
    var isLoading = false;

    $(function () {
        new TableSorter({
            selector: '#models-list'
        }).start();

        $('#filters form :input[name]').change(function () {
            this.form.submit();
        });

        $('#filters form .with-date').datepicker();

        window.reportFavorites = new AgreementModelReportFavoritesManagementController({
            add_to_favorites_url: "<?php echo url_for('agreement_module_report_add_to_favorites'); ?>",
            remove_to_favorites_url: "<?php echo url_for('agreement_module_report_remove_from_favorites'); ?>"
        }).start();


    });
</script>

