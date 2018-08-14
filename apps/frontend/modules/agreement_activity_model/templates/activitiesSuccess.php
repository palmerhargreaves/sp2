<?php //include_partial('modal_model', array('decline_reasons' => $decline_reasons, 'decline_report_reasons' => $decline_report_reasons, 'specialist_groups' => $specialist_groups, 'outOfDate' => $outOfDate)) ?>

<div class="approvement">
    <h1>Мои заявки</h1>
    <?php
    $years_range_gen = range(sfConfig::get('app_min_year'), date('Y'));
    $years_range = array_combine($years_range_gen, $years_range_gen);

    $model_filter_statuses = array(
        'default' => 'Макеты',
        'current' => 'Текущие',
        'process_draft' => 'Черновики',
        'complete' => 'Выполненные',
        'blocked' => 'Заблокированные'
    );

    $report_filter_statuses = array(
        'default' => 'Отчеты',
        'current' => 'Текущие',
        'complete' => 'Выполненные',
    );

    $filter_by_year = $sf_data->getRaw('model_report_year_filter');
    $model_status_filter = $sf_data->getRaw('model_status_filter');
    $report_status_filter = $sf_data->getRaw('report_status_filter');

    $on_check_due_date = $sf_user->isManager() || $sf_user->getAuthUser()->isDealerUser() || $sf_user->getAuthUser()->isImporter();

    ?>
    <div id="filters" style='left: 100px;'>
        <form action="<?php echo url_for('@agreement_module_model_activities') ?>" method="get">
            <div class="date-input filter" style="margin-right: 10px;">
                <input type="text" placeholder="№ заявки" name="model" value="<?php echo $model_filter ?>"/>
            </div>

            <select id="filter-models-by-years" name="model_report_year[]" multiple>
                <?php foreach ($years_range as $key => $value): ?>
                    <option
                        value="<?php echo $key; ?>" <?php echo !empty($filter_by_year) && in_array($key, $filter_by_year) ? 'selected' : ''; ?>><?php echo $value; ?></option>
                <?php endforeach; ?>
            </select>

            <div class="date-input filter">
                <input type="text" placeholder="от" name="start_date"
                       value="<?php echo $start_date_filter ? date('d.m.Y', $start_date_filter) : '' ?>"
                       class="with-date"/>
            </div>
            <div class="date-input filter">
                <input type="text" placeholder="до" name="end_date" class="with-date"
                       value="<?php echo $end_date_filter ? date('d.m.Y', $end_date_filter) : '' ?>"/>
            </div>

            <div class="modal-select-wrapper krik-select select type filter" style="margin-left: 20px;">
                <span
                    class="select-value"><?php echo !is_null($model_status_filter) ? $model_filter_statuses[$model_status_filter] : $model_filter_statuses['default']; ?></span>

                <div class="ico"></div>
                <input type="hidden" name="model_status" value="<?php echo $model_status_filter ?>">

                <div class="modal-input-error-icon error-icon"></div>
                <div class="error message"></div>
                <div class="modal-select-dropdown">
                    <?php foreach ($model_filter_statuses as $value => $name): ?>
                        <div class="modal-select-dropdown-item select-item"
                             data-value="<?php echo $value ?>"><?php echo $name ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="modal-select-wrapper krik-select select type filter"
                 style="margin-left: 10px; <?php echo !is_null($model_status_filter) && ($model_status_filter != 'complete' && $model_status_filter != 'default') ? 'pointer-events: none; background-color: #ccc;' : ''; ?>">
                <span
                    class="select-value"><?php echo !is_null($report_status_filter) ? $report_filter_statuses[$report_status_filter] : $report_filter_statuses['default']; ?></span>

                <div class="ico"></div>
                <input type="hidden" name="report_status" value="<?php echo $report_status_filter ?>">

                <div class="modal-input-error-icon error-icon"></div>
                <div class="error message"></div>
                <div class="modal-select-dropdown">
                    <?php foreach ($report_filter_statuses as $value => $name): ?>
                        <div class="modal-select-dropdown-item select-item"
                             data-value="<?php echo $value ?>"><?php echo $name ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php ///endif; ?>

        </form>
    </div>

    <div id="agreement-models">
        <div id="materials" class="active" style="padding-top: 7px;">
            <?php if (count($models) > 0): ?>
                <?php $k = 0;
                foreach ($models as $year => $data):
                    $header = sprintf('Заявки за %s', $year);
                    $summ = '';

                    //if ($model_status_filter == 'default' || $model_status_filter == 'complete') {
                    $summ = number_format($data['summ'], 0, '.', ' ') . ' руб.';
                    //}
                    ?>
                    <div class="group <?php echo $year == date('Y') ? 'open' : '' ?>">
                        <div class="group-header">
                            <span class="title"><?php echo $header; ?></span>

                            <div class="summary"><?php echo $summ; ?></div>
                            <div class="group-header-toggle"></div>
                        </div>
                        <div class="group-content">
                            <table class="models" id="models-list">
                                <thead>
                                <tr>
                                    <td width="75">
                                        <div class="has-sort">ID / Дата</div>
                                        <div class="sort has-sort"></div>
                                    </td>
                                    <td width="146">
                                        <div class="has-sort">Дилер</div>
                                        <div class="sort has-sort"></div>
                                    </td>
                                    <td width="130">
                                        <div class="has-sort">Название</div>
                                        <div class="sort has-sort"></div>
                                    </td>
                                    <td width="80">
                                        <div class="has-sort">Акция</div>
                                        <div class="sort has-sort"></div>
                                    </td>
                                    <!--<td width="146"><div>Размещение</div></td>-->
                                    <td width="105">
                                        <div>Период</div>
                                    </td>
                                    <td width="81">
                                        <div class="has-sort">Сумма</div>
                                        <div class="sort has-sort" data-sort="cost"></div>
                                    </td>
                                    <td>
                                        <div>Действие</div>
                                    </td>

                                    <?php if ($on_check_due_date) { ?>
                                        <td width="100">
                                            <div>На проверке до</div>
                                        </td>
                                    <?php } ?>

                                    <td width="35">
                                        <div>Макет</div>
                                    </td>
                                    <td width="35">
                                        <div>Отчет</div>
                                    </td>
                                    <td width="135">
                                        <div>
                                            <div class="has-sort">&nbsp;</div>
                                            <!--div class="sort has-sort" data-sort="messages"></div--></div>
                                    </td>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($data['data'] as $item):
                                    $model = $item['model'];
                                    $dateText = date('H:i d-m-Y', $item['date']);

                                    $isDealer = $sf_user->isDealerUser();
                                    if ($sf_user->isImporter()) {
                                        $isDealer = false;
                                    }

                                    $model_date = $model->getModelQuarterDate();

                                    ?>
                                    <?php $discussion = $model->getDiscussion() ?>
                                    <?php //$new_messages_count = $discussion ? $discussion->countUnreadMessages($sf_user->getAuthUser()->getRawValue()) : 0
                                    ?>
                                    <tr class="sorted-row model-row<?php if ($k % 2 == 0) echo ' even' ?> dummy"
                                        data-activity-id="<?php echo $model->getActivityId(); ?>"
                                        data-model="<?php echo $model->getId() ?>"
                                        data-discussion="<?php echo $model->getDiscussionId() ?>"
                                        data-new-messages="<?php echo $new_messages_count ?>"
                                        data-model-quarter="<?php echo D::getQuarter($model_date); ?>"
                                        data-model-year="<?php echo D::getYear($model_date); ?>">
                                        <td data-sort-value="<?php echo $model->getId() ?>">
                                            <div
                                                style='float:left; width: 12px;'><?php echo $item['status'] ? '<img src="/images/hOpened.gif" title="Переход по годам" />' : ''; ?></div>
                                            <div class="num">№ <?php echo $model->getId() ?></div>
                                            <div class="date"><?php echo D::toLongRus($model->created_at) ?></div>
                                        </td>
                                        <td data-sort-value="<?php echo $model->getDealer()->getName() ?>"><?php echo $model->getDealer()->getName(), ' (', $model->getDealer()->getNumber(), ')' ?></td>
                                        <td data-sort-value="<?php echo $model->getName() ?>">
                                            <div><?php echo $model->getName() ?></div>
                                            <div class="sort"></div>
                                        </td>
                                        <td data-sort-value="<?php echo $model->getShareName() ?>">
                                            <div><?php echo $model->getShareName() ?></div>
                                            <div class="sort"></div>
                                        </td>
                                        <?php /*<td class="placement <?php echo $model->getModelType()->getIdentifier() ?>"><div class="address"><?php echo $model->getValueByType('place') ?></div></td> */
                                        ?>
                                        <td>
                                            <?php if ($model->isValidModelCategory()): ?>
                                                <?php echo $model->getPeriod(); ?>
                                            <?php else: ?>
                                                <?php echo $model->getValueByType('period'); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td data-sort-value="<?php echo $model->getCost() ?>">
                                            <div><?php echo number_format($model->getCost(), 0, '.', ' ') ?> руб.</div>
                                            <div class="sort"></div>
                                        </td>
                                        <td class="darker">
                                            <div><?php echo $model->getDealerActionText() ?></div>
                                            <div class="sort"></div>
                                        </td>

                                        <?php $on_check_due_date_show = $on_check_due_date && $model->showCheckDateToStatusLabel(); ?>

                                        <?php if ($on_check_due_date_show) { ?>
                                            <td class="darker"
                                                style="<?php echo $model->isModelAcceptActiveToday($isDealer) ? 'background-color: rgb(233, 66, 66);' : '' ?>">
                                                <div><?php echo $dateText; ?></div>
                                                <div class="sort"></div>
                                            </td>
                                        <?php } else { ?>
                                            <td class=""></td>
                                        <?php } ?>

                                        <?php $waiting_specialists = $model->countWaitingSpecialists(); ?>
                                        <td class="darker">
                                            <div
                                                class="<?php echo $model->getCssStatus() ?>"><?php echo $waiting_specialists ? 'x' . $waiting_specialists : '' ?></div>
                                        </td>
                                        <?php $waiting_specialists = $model->countReportWaitingSpecialists(); ?>
                                        <td class="darker">
                                            <div
                                                class="<?php echo $model->getReportCssStatus() ?>"><?php echo $waiting_specialists ? 'x' . $waiting_specialists : '' ?></div>
                                        </td>
                                        <td class="darker">
                                            <?php include_partial('activity_model_status', array('model' => $model)); ?>
                                        </td>
                                    </tr>
                                    <?php $k++; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php
                endforeach;
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(function () {
        var submit_timer = null;

        $('#filter-models-by-years').multiselect({
            texts: {
                placeholder: 'Выберите год'
            },
            columns: 1
        });

        new TableSorter({
            selector: '#models-list'
        }).start();

        $('#filters form :input[name]').change(function (e) {
            var self = this;

            if ($(this).attr('id') == 'filter-models-by-years') {
                if (submit_timer != null) {
                    clearInterval(submit_timer);
                }

                submit_timer = setInterval(function () {
                    self.form.submit();
                    clearInterval(submit_timer);
                }, 2000);
            } else {
                this.form.submit();
            }
        });

        $('#filters form .with-date').datepicker();

        $('#models-list .dummy').live('click', function () {
            window.open('/activity/' + $(this).data('activity-id') +
                '/module/agreement/models/model/' + $(this).data('model') +
                '/quarter/' + $(this).data('model-quarter') +
                '/year/' + $(this).data('model-year')
                , '_blank');
        });
    });
</script>
