<?php use_stylesheet('budget_by_points'); ?>

<?php
$roman = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV');
$summFoQ4 = 0;
?>

<?php if ($plan->count() > 0): ?>
    <div class="budget-wrapper">
        <div class="budget">

            <h1 style="height: 38px;">

                <div id="chBudYears" class="modal-select-wrapper select input krik-select float-right"
                     style="height: 23px; padding-bottom: 1px; padding-right: 18px; width: 120px; margin-right: 10px;">

                    <span class="select-value"><?php echo "Бюджет на " . $year . " г."; ?></span>
                    <input type="hidden" name="year" value="<?php echo $year; ?>">

                    <div class="ico"></div>

                    <div class="modal-input-error-icon error-icon"></div>
                    <div class="error message"></div>
                    <div class="modal-select-dropdown">
                        <?php

                        foreach ($budYears as $year):
                            $url = !empty($fromDealer) ? url_for("@agreement_module_dealer?id=" . $dealer->getId() . "&year=" . $year) : url_for("@homepage?year=" . $year);
                            ?>
                            <div style='height:auto; padding: 7px;' class="modal-select-dropdown-item select-item"
                                 data-url="<?php echo $url ?>"><?php echo "Бюджет на " . $year . " г."; ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php echo $sf_data->getRaw('header'); ?>

            </h1>

            <div class="quarter-tabs tabs">
                <?php foreach ($plan as $n => $budget): ?>
                    <div class="tab<?php if ($n == $current_quarter) echo ' active' ?>"
                         data-pane="budget-pane<?php echo $n ?>">
                        <div class="tab-header">
                            <div class="required-activities">
                                <?php
                                $icons = $accept_stat[$n];
                                $totalAct = count($icons);
                                $allComplete = 0;
                                ?>

                                <?php
                                $show_icons = true;
                                /*if ($n > $current_quarter) {
                                    $show_icons = false;
                                }*/

                                if ($show_icons):
                                    $quarter_status = 'warning.png';

                                    if ($quarters_statistics[$n]['quarter_completed']) {
                                        $quarter_status = 'check.png';
                                    } else if (!$quarters_statistics[$n]['quarter_completed'] && !$quarters_statistics[$n]['current_quarter']) {
                                        $quarter_status = 'forbidden.png';
                                    }
                                    ?>

                                    <img src="/images/<?php echo $quarter_status; ?>" alt=""/>
                                <?php endif; ?>
                            </div>

                            <span><?php echo $roman[$budget->getQuarter()] ?></span> квартал
                        </div>

                        <?php $control_point_index = 1; ?>
                        <div class="tab-select">
                            <div class="tab-select__value">Контрольные пункты</div>
                            <ul>
                                <li class="<?php if ($show_icons): ?> is-<?php echo $quarters_statistics[$n]['quarter_plan_completed'] ? 'passed' : 'alert'; ?> <?php endif; ?>"><?php echo $control_point_index++ . '. '; ?>
                                    Выполнение бюджета
                                </li>

                                <?php if (count($quarters_statistics[$n]['mandatory_activities']['list'])): ?>
                                    <li data-q="<?php echo $n; ?>"
                                        class="mandatory-activities <?php if ($show_icons): ?> is-<?php echo $quarters_statistics[$n]['mandatory_activities']['completed'] ? 'passed' : 'alert'; ?> <?php endif; ?>"><?php echo $control_point_index++ . '. '; ?>
                                        Обязательные активности
                                    </li>

                                    <?php foreach ($quarters_statistics[$n]['mandatory_activities']['list'] as $slot_id => $slots): ?>
                                        <li class="row-mandatory-activity-item mandatory-activity-item-<?php echo $n; ?>">
                                            <?php foreach ($slots as $activity): ?>
                                                <div>
                                                    <span>
                                                        <a class="tooltip-line" href="javascript:;">
                                                            <div class="activity-work-info <?php echo $activity['work_status']; ?>-circle">&nbsp;</div>
                                                            <div class="tooltip-line-content">
                                                                <div class="tooltip-line-text" style="border-bottom: 2px solid <?php echo $activity['work_status']; ?>">
                                                                    <div class="tooltip-line-inner"><?php echo $activity['work_status_msg']; ?></div>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </span>

                                                    <span>
                                                        <?php if ($activity['can_redirect']): ?>
                                                            <a href="<?php echo url_for("@activity_quarter_data?activity=".$activity['id']."&current_q=".$n."&current_year=".$quarters_statistics[$n]['year']); ?>" style="font-weight: normal;">
                                                                <?php echo $activity['name']; ?>
                                                                <span><?php echo $activity['id']; ?></span>
                                                            </a>
                                                        <?php else: ?>
                                                            <?php echo $activity['name']; ?>
                                                            <span><?php echo $activity['id']; ?></span>
                                                        <?php endif; ?>

                                                    <!--<li class="<?php if ($show_icons): ?>mandatory-activity-item-<?php echo $activity['completed'] ? 'completed' : 'in-work'; ?> <?php endif; ?> mandatory-activity-item-<?php echo $n; ?>">
                                                        <?php echo $activity['name']; ?>
                                                        <span><?php echo $activity['id']; ?></span>
                                                    </li>-->
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <li class="<?php if ($show_icons): ?>is-<?php echo $quarters_statistics[$n]['emails_completed'] ? 'passed' : 'alert'; ?> <?php endif; ?>"><?php echo $control_point_index++ . '. '; ?>
                                    E-mail адреса
                                </li>

                                <?php if ($quarters_statistics[$n]['active_terms_of_loading']): ?>
                                    <li class="<?php if ($show_icons): ?> is-<?php echo $quarters_statistics[$n]['terms_of_loading'] ? 'passed' : 'alert'; ?> <?php endif; ?>"><?php echo $control_point_index . '. '; ?>
                                        Сроки подгрузки
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="tab-body">
                            <div>
                                План: <strong><?php echo number_format($budget->getPlan(), 0, '.', ' ') ?></strong> руб.
                            </div>
                            <div>
                                Факт: <strong><?php echo number_format($real[$n], 0, '.', ' ') ?></strong> руб.
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php for ($n = 1; $n <= 4; $n++): ?>
                <div class="quarter-pane-points" id="budget-pane<?php echo $n ?>">
                    <div class="quarter-pane__progress is-time">
                        <div class="quarter-pane__progress__bar">
                            <div
                                    class="quarter-pane__progress__track"
                                    data-percent="<?php echo $quarter_days[$n]['day'] / $quarter_days[$n]['length'] * 100; ?>"
                                    style="width: <?php echo $quarter_days[$n]['day'] / $quarter_days[$n]['length'] * 100 ?>%;"
                            ></div>
                        </div>
                        <div class="labels-by-points">
                            <?php for ($m = 0; $m < 3; $m++): ?>
                                <div class="label"><?php echo $months[$n][$m] ?></div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <?php
                    $done = $plan[$n]->getPlan() == 0 ? 0 : $real[$n] / $plan[$n]->getPlan() * 100;
                    if ($done > 100) $done = 100;
                    ?>

                    <div class="quarter-pane__progress is-sum">
                        <span class="quarter-pane__progress__caption">квартал</span>
                        <div class="quarter-pane__progress__bar">
                            <div
                                    class="quarter-pane__progress__track"
                                    data-percent="<?php echo $done ?>"
                                    style="width: <?php echo $done ?>%;"
                            ></div>
                        </div>

                        <div class="caption_done">
                            Выполнено: <strong><?php echo number_format($real[$n], 0, '.', ' ') ?></strong> руб.
                        </div>

                        <div class="caption_overdraft">
                            &nbsp;
                            <?php if ($real[$n] > $plan[$n]->getPlan()) { ?>
                                Перевыполнение:
                                <strong><?php echo number_format($real[$n] - $plan[$n]->getPlan(), 0, '.', ' ') ?></strong> руб.
                            <?php } else if ($real[$n] != $plan[$n]->getPlan() && $n < $current_quarter) { ?>
                                Недовыполнение:
                                <strong><?php echo number_format($plan[$n]->getPlan() - $real[$n], 0, '.', ' ') ?></strong> руб.
                            <?php } else if ($real[$n] != $plan[$n]->getPlan() && $n >= $current_quarter) { ?>
                                Осталось выполнить:
                                <strong><?php echo number_format($plan[$n]->getPlan() - $real[$n], 0, '.', ' ') ?></strong> руб.
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>


            <?php $year_work_result = $year_work_result->getRawValue(); ?>

            <div class="quarter-pane__progress is-sum">
                <span class="quarter-pane__progress__caption">год</span>
                <div class="quarter-pane__progress__bar">
                    <div
                            class="quarter-pane__progress__track"
                            data-percent="0"
                            style="width: <?php echo $year_work_result['percent_complete']; ?>%;"
                    ></div>
                </div>

                <div class="caption_done">
                    Выполнено: <strong><?php echo number_format($year_work_result['real'], 0, '.', ' '); ?></strong>
                    руб.
                </div>

                <div class="caption_overdraft"> &nbsp;
                    <?php if ($year_work_result['real'] > $year_work_result['plan']): ?>
                        Перевыполнение:
                        <strong><?php echo number_format($year_work_result['real_recomplete'], 0, '.', ' '); ?></strong> руб.
                    <?php else: ?>
                        Осталось выполнить:
                        <strong><?php echo number_format($year_work_result['real_left'], 0, '.', ' '); ?></strong> руб.
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <script type="text/javascript">
            $(function () {
                $(document).on("click", "#chBudYears .select-item", function () {
                    location.href = $(this).data('url');
                });
            });
        </script>

    </div>
<?php endif; ?>

