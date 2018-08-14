<?php
if ($dealers_statistics) {
    //include_partial('agreement_activity_model_management/modal_model', array('decline_reasons' => $decline_reasons, 'decline_report_reasons' => $decline_report_reasons, 'specialist_groups' => $specialist_groups))

    $companies_statistics_by_months = array();
    $companies_ids = array();
    ?>


    <div class="actions-wrapper">
        <div class="activities dealer-activities-statistics" id="agreement-models">
            <h1 style="margin-top: 10px;">Список активностей по кварталам / кампаниям</h1>
            <?php $quarters = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV') ?>
            <div id="materials" class="active">
                <div id="accommodation" class="active">
                    <?php foreach ($dealers_statistics as $q_key => $item_data): ?>
                        <?php $builder_data = $item_data['companies']; ?>
                        <h2><?php echo $quarters[$q_key] ?> Квартал</h2>
                        <?php foreach ($builder_data as $c_key => $stat_item): ?>
                            <?php
                            $stat = $stat_item['stat'];
                            $company_statistic = $stat_item['company_statistic_by_quarters'];

                            $companies_statistics_by_months[$c_key] = array('statistic' => $stat_item['company_statistic_by_months'][$c_key], 'name' => $stat_item['company_type']);

                            if (!in_array($c_key, $companies_ids)) {
                                $companies_ids[] = $c_key;
                            }

                            ?>

                            <div class="drop-shadow perspective">
                                <p style="font-size: 14px;"><?php echo $stat_item['company_type']; ?>.</p>
                                <p><?php echo sprintf('Выполнено %s%% на сумму %s', $stat_item['company_statistic']['completed'],
                                        Utils::numberFormat($stat_item['company_statistic']['total_cash'])); ?></p>
                                <p><?php echo sprintf('Заявок - %s', ($company_statistic[$q_key][$c_key]['total_models'] - $company_statistic[$q_key][$c_key]['total_models_from_prev_quarter'])); ?></p>

                                <?php
                                $prev_q_complete = true;
                                if (isset($company_statistic[$q_key - 1])) {
                                    $prev_q_data = $company_statistic[$q_key - 1][$c_key];
                                    if ($prev_q_data['complete_percent'] != 100) {
                                        $prev_q_complete = false;
                                    }
                                }
                                ?>

                                <?php
                                $moved_cash_to_prev_q = 0;
                                $show_next_q_moved_cash = true;
                                ?>
                                <?php if (!$prev_q_complete): ?>
                                    <p>
                                        <?php if ($company_statistic[$q_key][$c_key]['complete_percent'] == 100): ?>
                                            <?php
                                            $moved_cash_to_prev_q = $prev_q_data['plan_company_budget'] - $prev_q_data['total_cash'];

                                            $prev_q_cash = $prev_q_data['total_cash'] + $company_statistic[$q_key][$c_key]['total_moved_models_cash'];
                                            if ($prev_q_cash >= $prev_q_data['plan_company_budget']) {
                                                echo sprintf('Покрыто за предыдущий квартал - %s', Utils::numberFormat($moved_cash_to_prev_q));
                                            } else {
                                                echo sprintf('Недовыполнение за предыдущий квартал - %s', Utils::numberFormat($prev_q_data['plan_company_budget'] - $prev_q_cash));
                                                $show_next_q_moved_cash = false;
                                            }
                                            ?>
                                        <?php else: ?>
                                            <?php if ($prev_q_data['complete_percent'] != 100): ?>
                                                <?php
                                                    echo sprintf('Недовыполнение за предыдущий квартал - %s', Utils::numberFormat($prev_q_data['plan_company_budget'] - $prev_q_data['total_cash']));
                                                    $show_next_q_moved_cash = false;
                                                ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>

                                <?php
                                $total_moved_model_cash_with_prev_q = 0;
                                $total_moved_model_cash_with_prev_q_percent = 0;

                                if ($moved_cash_to_prev_q) {
                                    $total_moved_model_cash_with_prev_q = $company_statistic[$q_key][$c_key]['total_moved_models_cash'] - $moved_cash_to_prev_q;
                                    $total_moved_model_cash_with_prev_q_percent = $total_moved_model_cash_with_prev_q * 100 / $company_statistic[$q_key][$c_key]['plan_company_budget'];
                                }
                                ?>
                                <?php if (isset($company_statistic[$q_key][$c_key]['total_moved_models_percent']) && $show_next_q_moved_cash): ?>
                                <p>
                                    <?php echo sprintf('Перешло в сл. квартал - %s, %s%%',
                                        Utils::numberFormat($company_statistic[$q_key][$c_key]['total_moved_models_cash']),
                                        $company_statistic[$q_key][$c_key]['total_moved_models_percent']);
                                    ?>
                                </p>
                                <?php endif; ?>

                                <?php if ($total_moved_model_cash_with_prev_q > 0): ?>
                                    <p>
                                        <?php echo sprintf('Перешло в сл. квартал с учетом пред. квартала - %s, %s%%',
                                            Utils::numberFormat($total_moved_model_cash_with_prev_q),
                                            round($total_moved_model_cash_with_prev_q_percent, 0));
                                        ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <?php $statistics_models = $company_statistic[$q_key][$c_key]['models']->getRawValue(); ?>

                            <?php foreach ($stat['activities'] as $activity): ?>
                                <div class="group" style="width: 97%; margin-left: 17px;">
                                    <div class="<?php echo in_array($activity['activity']->getId(), $company_statistic[$q_key][$c_key]['activities_moved']->getRawValue()) ? 'group-header-alert' : ''; ?> group-header">
                                        <span class="title"><?php echo sprintF('[%s] %s', $activity['activity']->getId(), $activity['activity']->getName()) ?></span>

                                        <div class="summary"><?php echo number_format($activity['sum'], 0, '.', ' ') ?>
                                            руб.
                                        </div>
                                        <div class="group-header-toggle"></div>
                                    </div>
                                    <div class="group-content">
                                        <table class="models">
                                            <tbody>
                                            <?php foreach ($activity['models'] as $n => $model): $move_to_next_quarter = false; ?>
                                                <?php $discussion = $model->getDiscussion() ?>
                                                <?php $new_messages_count = $discussion ? $discussion->countUnreadMessages($sf_user->getAuthUser()->getRawValue()) : 0 ?>

                                                <?php if (array_key_exists($model->getId(), $statistics_models)): ?>
                                                    <?php
                                                    $statistic_model = $statistics_models[$model->getId()];
                                                    if ($statistic_model['next_quarter']) {
                                                        $move_to_next_quarter = true;
                                                    }
                                                    ?>
                                                <?php endif; ?>

                                                <tr class="sorted-row <?php if ($n % 2 == 0) echo ' even' ?>"
                                                    data-model="<?php echo $model->getId() ?>"
                                                    data-discussion="<?php echo $model->getDiscussionId() ?>"
                                                    data-new-messages="<?php echo $new_messages_count ?>"
                                                    style="<?php echo $move_to_next_quarter ? '-webkit-box-shadow: -5px 0px 5px -2px rgba(255,0,0,1); -moz-box-shadow: -5px 0px 5px -2px rgba(255,0,0,1); box-shadow: -5px 0px 5px -2px rgba(255,0,0,1);' : ''; ?>">
                                                    <td width="75" data-sort-value="<?php echo $model->getId() ?>">
                                                        <div class="num">№ <?php echo $model->getId() ?></div>
                                                        <div
                                                                class="date"><?php echo D::toLongRus($model->created_at) ?></div>
                                                    </td>
                                                    <td width="180"
                                                        data-sort-value="<?php echo $model->getName() ?>">
                                                        <div><?php echo $model->getName() ?></div>
                                                        <div class="sort"></div>
                                                    </td>
                                                    <td width="146"
                                                        class="placement <?php echo $model->getModelType()->getIdentifier() ?>">
                                                        <div
                                                                class="address"><?php echo $model->getValueByType('place') ?></div>
                                                        <div
                                                                class="address"><?php echo $model->getValueByType('period') ?></div>
                                                    </td>
                                                    <td width="81"
                                                        data-sort-value="<?php echo $model->getCost() ?>">
                                                        <div><?php echo number_format($model->getCost(), 0, '.', ' ') ?>
                                                            руб.
                                                        </div>
                                                        <div class="sort"></div>
                                                    </td>
                                                    <td width="181" class="darker">
                                                        <div><?php $model->getSpecialistActionText() ?></div>
                                                        <div class="sort"></div>
                                                    </td>
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
                                                    <td data-sort-value="<?php echo $new_messages_count ?>"
                                                        class="darker">
                                                        <?php if ($new_messages_count > 0): ?>
                                                            <div
                                                                    class="message"><?php echo $new_messages_count ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php
            if (getenv('REMOTE_ADDR') == '46.175.166.61'):

                $company_names = array();
                foreach ($companies_statistics_by_months as $company => $month_data) {
                    $company_names[$company] = array('name' => $month_data['name'], 'data' => $month_data['statistic']->getRawValue());
                }
                ?>
                <h2>Факт бюджет по месяцам</h2>
                <div id="container" style="min-width: 310px; height: 800px; margin: 0 auto; margin-top: 30px;"></div>

                <script>
                    $(function () {
                        Highcharts.createElement('link', {
                            href: 'https://fonts.googleapis.com/css?family=Roboto',
                            rel: 'stylesheet',
                            type: 'text/css'
                        }, null, document.getElementsByTagName('head')[0]);

                        Highcharts.theme = {
                            colors: ['#2b908f', '#90ee7e', '#f45b5b', '#7798BF', '#aaeeee', '#ff0066', '#eeaaee',
                                '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
                            chart: {
                                backgroundColor: {
                                    linearGradient: {x1: 0, y1: 0, x2: 1, y2: 1},
                                    stops: [
                                        [0, '#2a2a2b'],
                                        [1, '#3e3e40']
                                    ]
                                },
                                style: {
                                    fontFamily: '\'Unica One\', sans-serif'
                                },
                                plotBorderColor: '#606063'
                            },
                            title: {
                                style: {
                                    color: '#E0E0E3',
                                    textTransform: 'uppercase',
                                    fontSize: '20px'
                                }
                            },
                            subtitle: {
                                style: {
                                    color: '#E0E0E3',
                                    textTransform: 'uppercase'
                                }
                            },
                            xAxis: {
                                gridLineColor: '#707073',
                                labels: {
                                    style: {
                                        color: '#E0E0E3'
                                    }
                                },
                                lineColor: '#707073',
                                minorGridLineColor: '#505053',
                                tickColor: '#707073',
                                title: {
                                    style: {
                                        color: '#A0A0A3'

                                    }
                                }
                            },
                            yAxis: {
                                gridLineColor: '#707073',
                                labels: {
                                    style: {
                                        color: '#E0E0E3'
                                    }
                                },
                                lineColor: '#707073',
                                minorGridLineColor: '#505053',
                                tickColor: '#707073',
                                tickWidth: 1,
                                title: {
                                    style: {
                                        color: '#A0A0A3'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.85)',
                                style: {
                                    color: '#F0F0F0'
                                }
                            },
                            plotOptions: {
                                series: {
                                    dataLabels: {
                                        color: '#B0B0B3'
                                    },
                                    marker: {
                                        lineColor: '#333'
                                    }
                                },
                                boxplot: {
                                    fillColor: '#505053'
                                },
                                candlestick: {
                                    lineColor: 'white'
                                },
                                errorbar: {
                                    color: 'white'
                                }
                            },
                            legend: {
                                itemStyle: {
                                    color: '#E0E0E3'
                                },
                                itemHoverStyle: {
                                    color: '#FFF'
                                },
                                itemHiddenStyle: {
                                    color: '#606063'
                                }
                            },
                            credits: {
                                style: {
                                    color: '#666'
                                }
                            },
                            labels: {
                                style: {
                                    color: '#707073'
                                }
                            },

                            drilldown: {
                                activeAxisLabelStyle: {
                                    color: '#F0F0F3'
                                },
                                activeDataLabelStyle: {
                                    color: '#F0F0F3'
                                }
                            },

                            navigation: {
                                buttonOptions: {
                                    symbolStroke: '#DDDDDD',
                                    theme: {
                                        fill: '#505053'
                                    }
                                }
                            },

                            // scroll charts
                            rangeSelector: {
                                buttonTheme: {
                                    fill: '#505053',
                                    stroke: '#000000',
                                    style: {
                                        color: '#CCC'
                                    },
                                    states: {
                                        hover: {
                                            fill: '#707073',
                                            stroke: '#000000',
                                            style: {
                                                color: 'white'
                                            }
                                        },
                                        select: {
                                            fill: '#000003',
                                            stroke: '#000000',
                                            style: {
                                                color: 'white'
                                            }
                                        }
                                    }
                                },
                                inputBoxBorderColor: '#505053',
                                inputStyle: {
                                    backgroundColor: '#333',
                                    color: 'silver'
                                },
                                labelStyle: {
                                    color: 'silver'
                                }
                            },

                            navigator: {
                                handles: {
                                    backgroundColor: '#666',
                                    borderColor: '#AAA'
                                },
                                outlineColor: '#CCC',
                                maskFill: 'rgba(255,255,255,0.1)',
                                series: {
                                    color: '#7798BF',
                                    lineColor: '#A6C7ED'
                                },
                                xAxis: {
                                    gridLineColor: '#505053'
                                }
                            },

                            scrollbar: {
                                barBackgroundColor: '#808083',
                                barBorderColor: '#808083',
                                buttonArrowColor: '#CCC',
                                buttonBackgroundColor: '#606063',
                                buttonBorderColor: '#606063',
                                rifleColor: '#FFF',
                                trackBackgroundColor: '#404043',
                                trackBorderColor: '#404043'
                            },

                            // special colors for some of the
                            legendBackgroundColor: 'rgba(0, 0, 0, 0.5)',
                            background2: '#505053',
                            dataLabelsColor: '#B0B0B3',
                            textColor: '#C0C0C0',
                            contrastTextColor: '#F0F0F3',
                            maskColor: 'rgba(255,255,255,0.3)'
                        };

                        Highcharts.setOptions(Highcharts.theme);

                        var series = [], categories = [], fact_cash = [];
                        <?php
                        $months_ids = range(1, 12);
                        foreach ($months_ids as $m_key => $m_value): ?>
                        categories.push('<?php echo D::getMonthName($m_value); ?>');
                        <?php endforeach; ?>

                        <?php $company_stats = array(); $total_cash = 0; $total_moved_cash = 0; ?>
                        <?php foreach ($companies_ids as $c_key): ?>
                        var series_data = [], series_moved_cash = [], series_plan_company_cash = [],
                            series_total_q_cash = [];

                        <?php foreach ($months_ids as $m_key => $m_value): ?>

                        if (fact_cash[<?php echo $m_key; ?>] != undefined) {
                            fact_cash[<?php echo $m_key; ?>] += <?php echo $company_names[$c_key]['data']['months'][$m_value]['fact_cash']; ?>;
                        } else {
                            fact_cash[<?php echo $m_key; ?>] = <?php echo $company_names[$c_key]['data']['months'][$m_value]['fact_cash']; ?>;
                        }

                        series_data.push(<?php echo $company_names[$c_key]['data']['months'][$m_value]['cash']; ?>);
                        series_moved_cash.push(-<?php echo $company_names[$c_key]['data']['months'][$m_value]['moved_cash']; ?>);
                        series_plan_company_cash.push(<?php echo $company_names[$c_key]['data']['months'][$m_value]['plan_company_cash']; ?>);
                        series_total_q_cash.push(<?php echo $company_names[$c_key]['data']['months'][$m_value]['total_cash']; ?>);
                        <?php endforeach; ?>

                        series.push({
                            type: 'column',
                            name: 'Факт: <?php echo $company_names[$c_key]['name']; ?>',
                            data: series_data,
                            dataLabels: {
                                enabled: true,
                                formatter: function () {
                                    return Highcharts.numberFormat(this.y, 2);
                                }
                            }
                        });

                        series.push({
                            type: 'column',
                            name: 'Перенесено в сл. квартал: (<?php echo $company_names[$c_key]['name']; ?>)',
                            data: series_moved_cash,
                            color: Highcharts.getOptions().colors[<?php echo $c_key;?> +4],
                            dataLabels: {
                                enabled: true,
                                formatter: function () {
                                    return Highcharts.numberFormat(this.y, 2);
                                }
                            }
                        });

                        series.push({
                            type: 'spline',
                            name: 'Бюджет(<?php echo $company_names[$c_key]['name']; ?>)',
                            data: series_plan_company_cash,
                            marker: {
                                lineWidth: 1,
                                lineColor: Highcharts.getOptions().colors[<?php echo $c_key;?> +4],
                                fillColor: 'green'
                            },
                            showInLegend: false,
                            dataLabels: {
                                enabled: false
                            }
                        });
                        <?php endforeach; ?>

                        series.push({
                            type: 'spline',
                            name: 'Факт',
                            data: fact_cash,
                            marker: {
                                lineWidth: 1,
                                lineColor: "#f38b02",
                                fillColor: 'yellow'
                            },
                            dashStyle: 'shortdot',
                            dataLabels: {
                                enabled: true,
                                formatter: function () {
                                    return Highcharts.numberFormat(this.y, 2);
                                }
                            }
                        });

                        Highcharts.chart('container', {
                            title: {
                                text: 'Факт выполнения бюджета за тек. год'
                            },
                            xAxis: {
                                categories: categories
                            },
                            yAxis: {
                                title: {
                                    text: "Факт по месяцам"
                                }
                            },
                            labels: {
                                items: [{
                                    html: '',
                                    style: {
                                        left: '50px',
                                        top: '18px',
                                        color: (Highcharts.theme && Highcharts.theme.textColor) || 'black'
                                    }
                                }]
                            },
                            series: series
                        });

                    });
                </script>
            <?php endif; ?>
        </div>
    </div>

<?php } ?>
