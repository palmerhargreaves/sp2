<?php include_partial('agreement_activity_model_management/menu', array( 'active' => 'activities_status', 'year' => $year, 'url' => 'agreement_module_activities_status' )) ?>

<?php $total_activities_completed = $total_activities_completed->getRawValue(); ?>
<?php $dealers_statistics = $dealers_statistics->getRawValue(); ?>

<?php
$activities_mandatory_list = $activities_mandatory_list->getRawValue();
$dealers_statistics_activities = $dealers_statistics_activities->getRawValue();
?>

<table class="dealers-table" id="status-table" style="z-index:9;">
    <thead>
    <tr>
        <td class="header" style="height: 185px;">
            <!--<a href="#" class="save">сохранить</a>-->
            <div style="display: inline-block; float: left; width: 220px; height: 100%;">
                <h1 style="margin-top: 16px;">Статус выполнения активностей</h1>

                <form action="<?php url_for('@agreement_module_activities_status') ?>" method="get">
                    <select name="year">
                        <option value=''>Выберите год</option>
                        <?php foreach ($budgetYears as $item): ?>
                            <option
                                    value="<?php echo $item; ?>" <?php echo $item == $year ? "selected" : ""; ?>><?php echo "Бюджет на " . $item . " г."; ?></option>
                        <?php
                        endforeach;
                        ?>
                    </select>

                    <select name="quarter">
                        <option value="">за весь год</option>
                        <option value="1"<?php echo $quarter == 1 ? ' selected' : '' ?>>за I квартал</option>
                        <option value="2"<?php echo $quarter == 2 ? ' selected' : '' ?>>за II квартал</option>
                        <option value="3"<?php echo $quarter == 3 ? ' selected' : '' ?>>за III квартал</option>
                        <option value="4"<?php echo $quarter == 4 ? ' selected' : '' ?>>за IV квартал</option>
                    </select>

                    <select name="activities_filter">
                        <option value="">Все активности</option>
                        <option value="simple"<?php echo $activities_filter == 'simple' ? ' selected' : '' ?>>Обычные
                        </option>
                        <option value="mandatory"<?php echo $activities_filter == 'mandatory' ? ' selected' : '' ?>>
                            Обязательные
                        </option>
                    </select>

                    <input placeholder="фильтр по дилерам" class="filter" type="text" name="dealer"
                           value="<?php echo $dealer ?>"/>
                </form>
            </div>
        </td>
        <td class="activity" title="Процент выполнения бюджета">
            <div>
                <span>Процент выполнения бюджета</span>
            </div>
        </td>
        <td class="activity" title="Завершённых активностей с начала года">
            <div>
                <span>Завершено с начала года (активности)</span>
            </div>
        </td>
        <td class="activity" title="Завершённых заявок с начала года">
            <div>
                <span>Завершено с начала года (заявок)</span>
            </div>
        </td>
        <?php if ($quarter): ?>
            <td class="activity" title="Завершённых активностей за квартал">
                <div>
                    <span>Завершено за квартал (активностей)</span>
                </div>
            </td>

            <td class="activity" title="Завершённых заявок за квартал">
                <div>
                    <span>Завершено за квартал (заявок)</span>
                </div>
            </td>
        <?php endif; ?>
        <?php
        $activeActivities = array();
        foreach ($activities as $activity_row):
            $activeActivities[ $activity_row[ 'activityId' ] ] = $activity_row[ 'activityId' ];

            $mandatory_activity_style = '';
            if (array_key_exists($activity_row[ 'activity_id' ], $activities_mandatory_list) && $activities_mandatory_list[ $activity_row[ 'activity_id' ] ]) {
                $mandatory_activity_style = 'background-color: #fff5ef;';
            }
            ?>
            <td class="activity" style="<?php echo $mandatory_activity_style; ?>"
                title="<?php echo $activity_row[ 'activityName' ]; ?>">
                <div><span style="overflow: initial; left: -57px;"><?php echo $activity_row[ 'activityName' ] ?></span>
                </div>
            </td>
        <?php endforeach; ?>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td></td>
        <td title="средний % выполнения бюджета"><?php echo round(isset($total[ 'average_percent' ])) ? $total[ 'average_percent' ] : 0; ?>
            %
        </td>
        <td></td>
        <td></td>
        <?php if ($quarter): ?>
            <td><?php ?></td>
            <td><?php ?></td>
        <?php endif; ?>
        <?php foreach ($workStats as $key => $workStat): ?>
            <?php
            $mandatory_activity_style = '';
            if (array_key_exists($key, $activities_mandatory_list) && $activities_mandatory_list[ $key ]) {
                $mandatory_activity_style = 'background-color: #fff5ef;';
            }
            ?>
            <td title="кол-во дилеров, завершивших активность"
                style="<?php echo $mandatory_activity_style; ?>"><?php echo $workStat[ 'completed' ]; ?></td>
        <?php endforeach; ?>
    </tr>

    <tr class="dealer odd">
        <td>Количество дилеров, в работе</td>
        <td></td>
        <td></td>
        <td></td>
        <?php if ($quarter): ?>
            <td></td>
            <td></td>
        <?php endif; ?>
        <?php foreach ($workStats as $key => $workStat): ?>
            <?php
            $mandatory_activity_style = '';
            if (array_key_exists($key, $activities_mandatory_list) && $activities_mandatory_list[ $key ]) {
                $mandatory_activity_style = 'background-color: #fff5ef;';
            }
            ?>
            <td title="кол-во дилеров, выполняющих активность"
                style="<?php echo $mandatory_activity_style; ?>"><?php echo $workStat[ 'in_work' ]; ?></td>
        <?php endforeach; ?>
    </tr>

    <?php foreach ($managers as $manager):
        if (!array_key_exists($manager[ 'manager_id' ], $dealers_statistics)) {
            continue;
        }

        $managerData = NaturalPersonTable::getInstance()->find($manager[ 'manager_id' ]);
        if (!$managerData) {
            $managerName = "Без менеджера";
        } else {
            $managerName = sprintf('%s %s', $managerData->getFirstName(), $managerData->getSurname());
        }

        ?>
        <tr class="regional-manager filter-group " data-manager-id="<?php echo $manager[ 'manager_id' ]; ?>">
            <td class="header">
                <div data-manager-id="<?php echo $managerData ? $managerData->getId() : 0; ?>"><?php echo $managerName; ?></div>
            </td>
            <td>
                <?php
                $avg_percent = 0;
                if (isset($avg_percent_of_budget_for_regional_manager[ $manager[ 'id' ] ])) {
                    echo round(array_sum($avg_percent_of_budget_for_regional_manager[ $manager[ 'id' ] ]->getRawValue()) / count($avg_percent_of_budget_for_regional_manager[ $manager[ 'id' ] ]), 2);
                }
                ?>
            </td>
            <td>
                <?php if (isset($avg_completed_activities_for_manager_by_year[ $manager[ 'id' ] ])): ?>
                    <?php echo $avg_completed_activities_for_manager_by_year[ $manager[ 'id' ] ]; ?>
                <?php endif; ?>
            </td>
            <td>
                <?php if (isset($completed_models_count_by_year[ $manager[ 'id' ] ])): ?>
                    <?php echo $completed_models_count_by_year[ $manager[ 'id' ] ]; ?>
                <?php endif; ?>
            </td>

            <?php if ($quarter): ?>
                <td>
                    <?php if (isset($avg_completed_activities_for_manager_by_quarter[ $manager[ 'id' ] ])): ?>
                        <?php echo $avg_completed_activities_for_manager_by_quarter[ $manager[ 'id' ] ]; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (isset($completed_models_count_by_quarter[ $quarter ][ $manager[ 'id' ] ])): ?>
                        <?php echo $completed_models_count_by_quarter[ $quarter ][ $manager[ 'id' ] ]; ?>
                    <?php endif; ?>
                </td>
            <?php endif; ?>

            <?php foreach ($activities as $activity): ?>
                <td>
                    <?php if ($manager_dealers_activities_work_statuses[ $manager[ 'id' ] ][ $activity[ 'activity_id' ] ][ 'completed' ] > 0): ?>
                        <div class="completed-dealers">
                            <img class="completed" src="/images/ok-icon-active.png">
                            <div class="completed-dealers-count"><?php echo $manager_dealers_activities_work_statuses[ $manager[ 'id' ] ][ $activity[ 'activity_id' ] ][ 'completed' ]; ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($manager_dealers_activities_work_statuses[ $manager[ 'id' ] ][ $activity[ 'activity_id' ] ][ 'in_work' ] > 0): ?>
                        <div class="in-work-dealers">
                            <img src="/images/wait-icon.png">
                            <div class="in-work-dealers-count"><?php echo $manager_dealers_activities_work_statuses[ $manager[ 'id' ] ][ $activity[ 'activity_id' ] ][ 'in_work' ]; ?></div>
                        </div>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
        </tr>
        <?php
        $n = 1;

        foreach ($dealers_statistics[ $manager[ 'manager_id' ] ] as $manager_key => $dealer_statistic):
            if (!array_key_exists($dealer_statistic[ 'id' ], $dealers_statistics_activities)) {
                continue;
            }

            $totalDealerActivitiesCompleted = 0;
            if (array_key_exists($dealer_statistic[ 'id' ], $total_activities_completed)) {
                $totalDealerActivitiesCompleted = $total_activities_completed[ $dealer_statistic[ 'id' ] ];
            }

            ?>
            <tr class="regional-manager-id-<?php echo $manager[ 'manager_id' ]; ?> dealer <?php if ($n++ % 2 == 0) echo ' odd'; ?>"
                style="display: none;"
                data-filter="<?php echo $dealer_statistic[ 'DealerStat' ][ 'name' ] ?>">
                <td class="header">
                    <div><span class="num"><?php //echo $dealer->getShortNumber()
                            ?></span>
                        <a href="/activity/module/agreement/dealers/<?php echo $dealer_statistic[ 'DealerStat' ][ 'id' ] ?>"><?php echo $dealer_statistic[ 'DealerStat' ][ 'name' ] ?></a>
                    </div>
                </td>

                <td><?php echo round($dealer_statistic[ 'percent_of_budget' ]) ?>%</td>
                <td><?php echo round($totalDealerActivitiesCompleted/*$dealer['activities_completed']*/) ?></td>
                <td><?php echo round($dealer_statistic[ 'models_completed' ]) ?></td>

                <?php if ($quarter): ?>
                    <td><?php echo round($dealer_statistic[ 'q_activity' . $quarter ]) ?></td>
                    <td><?php echo round($dealer_statistic[ 'q' . $quarter ]) ?></td>
                <?php endif; ?>

                <?php
                $activeActivitiesCopy = $activeActivities;

                $empty_td_items = ( count($activeActivitiesCopy) - count($dealers_statistics_activities[ $dealer_statistic[ 'id' ] ]) ) - 1;
                for ($td_index = 0; $td_index < $empty_td_items; $td_index++) {
                    $activity_copy = array_shift($activeActivitiesCopy);

                    $mandatory_activity_style = '';
                    if (array_key_exists($activity_copy, $activities_mandatory_list) && $activities_mandatory_list[ $activity_copy ]) {
                        $mandatory_activity_style = 'background-color: #fff5ef;';
                    }

                    echo "<td style='" . $mandatory_activity_style . "'></td>";
                }

                ?>

                <?php foreach ($dealers_statistics_activities[ $dealer_statistic[ 'id' ] ] as $item): ?>
                    <?php if (!in_array($item[ 'activity_id' ], $activeActivities)): ?>
                        <td></td>
                    <?php else:
                        //$activityStatusInfo = DealerActivitiesStatsDataTable::getActivityStatus($item);
                        $mandatory_activity_style = '';
                        if (array_key_exists($item[ 'activity_id' ], $activities_mandatory_list) && $activities_mandatory_list[ $item[ 'activity_id' ] ]) {
                            $mandatory_activity_style = 'background-color: #fff5ef;';
                        }

                        //-moz-box-shadow: 0 0 5px #888; -webkit-box-shadow: 0 0 5px#888; box-shadow: 0 0 5px #888;
                        ?>
                        <td class="<?php echo ( $item[ 'activity_complete' ] == 'ok' || $item[ 'activity_complete' ] == 'wait' ) ? $item[ 'activity_complete' ] : ( $user->isRegionalManager() ? 'error' : '' ); ?>"
                            style="<?php echo $mandatory_activity_style; ?>">
                            <?php
                            if ($item[ 'activity_complete' ] != 'none') {
                                echo "<a href='/activity/module/agreement/activities/" . $item[ 'activity_id' ] . "?dealer=" . $dealer_statistic[ 'DealerStat' ][ 'id' ] . "'>&nbsp</a>";
                            }
                            ?>

                        </td>
                    <?php
                    endif;
                endforeach;
                ?>
            </tr>
        <?php endforeach; ?>
        <?php //endif;
        ?>
    <?php endforeach; ?>
    </tbody>
</table>
<?php
/*$managers = $builder->build();
$activities = $builder->getActivitiesStat();
$total = $builder->getTotalStat();*/
?>

<script type="text/javascript">
    $(function () {
        new Filter({
            field: 'table.dealers-table.clone input.filter',
            filtering_blocks: '#status-table tr.dealer-row-visible'
        }).start();

        new TableHighlighter({
            table_selector: '#status-table',
            rows_header_selector: 'tbody tr.dealer td.header',
            columns_header_selector: 'thead td.activity'
        }).start();

        $(document).on('change', 'table.dealers-table.clone select', function () {
            this.form.submit();
        });

        new TableHeaderFixer({
            selector: '#status-table'
        }).start();

        $(document).on('click', 'tr.regional-manager', function (event) {
            var manager_row = $(event.currentTarget);

            if (!$('.regional-manager-id-' + manager_row.data('manager-id')).hasClass('dealer-row-visible')) {
                $('.regional-manager-id-' + manager_row.data('manager-id')).addClass('dealer-row-visible').show();
                manager_row.addClass('selected');
            } else {
                $('.regional-manager-id-' + manager_row.data('manager-id')).removeClass('dealer-row-visible').hide();
                manager_row.removeClass('selected');
            }
        });
    })
</script>
