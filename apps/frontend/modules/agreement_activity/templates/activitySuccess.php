<?php include_partial('agreement_activity_model_management/modal_model', array('decline_reasons' => $decline_reasons, 'decline_report_reasons' => $decline_report_reasons, 'specialist_groups' => $specialist_groups)) ?>
<?php include_partial('agreement_activity_model_management/menu', array('active' => 'activities', 'year' => $year, 'url' => 'agreement_module_activities')) ?>

<?php

$statsResult = $builder->getStat();

$stats = $statsResult['dealers'];
$tempStats = $statsResult['extended'];

$extendedStats = array(1 => '', 2 => '', 3 => '', 4 => '');
foreach ($extendedStats as $qKey => $data) {
    if (isset($tempStats[$qKey])) {
        $extendedStats[$qKey] = $tempStats[$qKey];
    } else {
        unset($extendedStats[$qKey]);
    }
}

$current_q = max(array_keys($extendedStats));
?>

<div class="activities" id="agreement-models">
    <h1><a href="<?php echo url_for('@agreement_module_activities') ?>">Активности</a>
        / <?php echo $builder->getActivity()->getName() ?></h1>

    <div style='float:left; margin-top: 15px; margin-bottom: 15px; width: 100%;'>
        <div id="filters" style="position: initial;">
            <fieldset class="agreeemnt-activities-dealers-fieldset">
                <legend class="agreeemnt-activities-dealers-legend">Параметры экспорта</legend>
                <form id="frmActivityDealers" name="frmActivityDealers"
                      action="<?php echo url_for('@agreement_module_activity?id=' . $builder->getActivity()->getId()); ?>"
                      data-url="<?php echo url_for('@agreement_module_activity_export') ?>" method="post">
                    <div class="modal-select-wrapper krik-select select dealer filter">
                        <?php if ($dealer_filter): ?>
                            <span class="select-value"><?php echo $dealer_filter->getRawValue(); ?></span>
                            <input type="hidden" name="dealer" value="<?php echo $dealer_filter->getId(); ?>">
                        <?php else: ?>
                            <span class="select-value">Все дилеры</span>
                            <input type="hidden" name="dealer">
                        <?php endif; ?>

                        <div class="ico"></div>
                        <span class="select-filter"><input type="text"></span>

                        <div class="modal-input-error-icon error-icon"></div>
                        <div class="error message"></div>
                        <div class="modal-select-dropdown">
                            <div class="modal-select-dropdown-item select-item" data-value="">Все</div>
                            <?php foreach (DealerTable::getVwDealersQuery()->execute() as $dealer): ?>
                                <div class="modal-select-dropdown-item select-item"
                                     data-value="<?php echo $dealer->getId() ?>"><?php echo sprintf('[%s] %s', $dealer->getShortNumber(), $dealer->getName()); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="date-input filter">
                        <input type="text" placeholder="от" name="start_date"
                               value="<?php echo $start_date_filter ? date('d.m.Y', $start_date_filter) : '' ?>"
                               class="with-date"/>
                    </div>
                    <div class="date-input filter">
                        <input type="text" placeholder="до" name="end_date" class="with-date"
                               value="<?php echo $end_date_filter ? date('d.m.Y', $end_date_filter) : '' ?>"/>
                    </div>

                    <?php
                    $model_type_filter_items = array('all' => 'Все', 'makets' => 'Согласование - макеты', 'reports' => 'Согласование - отчеты');
                    $clsAlign = 'right';
                    ?>
                    <!--<div class="modal-select-wrapper krik-select select type filter"
                             style="width: 200px; margin-left: 10px;">
                            <span class="select-value">Все</span>

                            <div class="ico"></div>

                            <input type="hidden" name="model_work_status" value="all">

                            <div class="modal-input-error-icon error-icon"></div>
                            <div class="error message"></div>
                            <div class="modal-select-dropdown">
                                <?php foreach ($model_type_filter_items as $value => $name): ?>
                                    <div class="modal-select-dropdown-item select-item"
                                         data-value="<?php echo $value ?>"><?php echo $name ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>-->

                    <div id="bt-make-export-data" class="add small button"
                         style="width: 115px; height: 25px; float: right; line-height: 25px;"
                         data-quarter="<?php echo $view_data_filter == "all" ? 0 : $current_q; ?>">Экспорт
                    </div>

                    <div
                        style="float: left; clear: left; color: #3A3939; margin: 10px; margin-left: 15px; display: block;">
                        <input type="checkbox"
                               id="ch-show-all-data" <?php echo $view_data_filter == "all" ? "checked" : ""; ?>
                               data-quarter="<?php echo $current_q; ?>">
                        <label for="ch-show-all-data">Без кварталов</label>

                    </div>

                    <input type="hidden" name="activity_id" value="<?php echo $activityId; ?>"/>
                    <input type="hidden" name="view_data"
                           value="<?php echo $view_data_filter ? ($view_data_filter == 'all' ? 'quarters' : 'all') : 'quarters'; ?>"/>
                </form>
            </fieldset>
        </div>
    </div>


    <fieldset class="agreeemnt-activities-dealers-fieldset" style="margin-top: 15px;">
        <legend class="agreeemnt-activities-dealers-legend">Дилеры</legend>

        <div id="materials" class="active" style="padding-top: 10px;">
            <div class="activity-main-page">
                <?php if (count($extendedStats) == 0): ?>
                    Ничего не найдено!
                <?php else: ?>
                    <?php if ($view_data_filter == "all"):
                        include_partial('activity_dealers_all', array('extendedStats' => $extendedStats, 'current_q' => $current_q));
                    else:
                        include_partial('activity_dealers_quarters', array('extendedStats' => $extendedStats, 'current_q' => $current_q));
                    endif;
                endif;
                ?>
            </div>
        </div>
    </fieldset>
</div>

<script>
    $(function () {
        $('.filter .with-date').datepicker();

        $('#filters form :input[name]').change(function () {
            this.form.submit();
        });

        $('#ch-show-all-data').change(function () {
            $('#frmActivityDealers').submit();
        });

        $('#bt-make-export-data').live('click', function () {
            var $bt = $(this),
                $dealer = $("input[name=dealer]"),
                $modelWorkStatus = $("input[name=model_work_status]"),
                $activityId = $("input[name=activity_id]"),
                $form = $dealer.closest('form');

            $bt.fadeOut();
            $.post($form.data('url'),
                {
                    dealer: $dealer.val(),
                    model_work_status: $modelWorkStatus.val(),
                    activity_id: $activityId.val(),
                    quarter: $bt.data('quarter')
                },
                function (result) {
                    $bt.fadeIn();

                    if (result.success) {
                        location.href = result.url;
                    }
                }
            );
        });

        $('.nav-tabs li a').live('click', function () {
            $('#bt-make-export-data').data('quarter', $(this).data('quarter'));
        });

    });
</script>
