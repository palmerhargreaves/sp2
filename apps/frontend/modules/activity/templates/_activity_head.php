<?php
$roman = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV');

$customDateLen = 0;
$customDate = $activity->getCustomDate();
if (!$customDate) {
    $startDate = D::toLongRus($activity->getStartDate());
    $endDate = D::toLongRus($activity->getEndDate());

    $customDateLen = strlen($startDate . $endDate) + 4;
} else
    $customDateLen = strlen($customDate);
?>
<?php /*
<a href="<?php echo url_for('home/index') ?>" class="small back button">Назад</a>
*/ ?>

<div class="activity-header-wrapper">

    <div class="activity-header d-cb">

        <div class="activity-header-selects">
            <div class="activity-header-selects-i">
                <?php foreach (ActivityCompanyTypeTable::getInstance()->createQuery()->execute() as $company_type): ?>
                    <div class="modal-select-wrapper select input krik-select">
                        <span class="select-value"><span class="cnt"><?php echo $company_type->getPercent(); ?>
                                %</span><?php echo $company_type->getName(); ?></span>
                        <div class="ico"></div>
                        <input type="hidden" name="company_activity_id"
                               class="sb_activity_status sb_activity_company_type" value="">
                        <div class="modal-input-error-icon error-icon"></div>
                        <div class="error message"></div>
                        <div class="modal-select-dropdown">
                            <div class="modal-select-dropdown-item select-item" data-value=""><span
                                        class="cnt"><?php echo $company_type->getPercent(); ?>%</span>
                                <?php echo $company_type->getName(); ?>
                            </div>

                            <?php foreach ($company_type->getActivitiesList($activity->getId()) as $company_activity): ?>
                                <div class="modal-select-dropdown-item select-item"
                                     data-value="<?php echo url_for('activity/index?activity=' . $company_activity->getId()) ?>">
                                    <?php echo sprintf('[%s] %s', $company_activity->getId(), $company_activity->getName()); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <span class="num"><?php echo $activity->getId() ?></span>
        <span class="date">
            <?php if (!$activity->getCustomDate()) { ?>
                <?php echo $startDate; ?> - <?php echo $endDate; ?>
            <?php } else {
                echo $activity->getCustomDate();
            }
            ?>
        </span>
        <div class="title"><?php echo $activity->getName() ?></div>
    </div>

    <hr class="hr-sep"/>

    <?php

    if (!is_null($quartersModels)):
        $is_task_activity_complete = false;
        $dealer = $sf_user->getAuthUser()->getDealer();

        $qData = $quartersModels->getData();
        $quarters_list = $qData->getRawValue();

        /** @var Activity $activity */

        if (!empty($quarters_list)):
            ?>
            <div class="nav-sm-tabs tabs-quart d-cb">
                <ul>
                    <?php
                    $selected_q = $current_q;

                    /** @var quarters list $q_list */
                    $q_list = array();
                    $years_list = arraY();
                    foreach ($quarters_list as $y_key => $q_data) {
                        $years_list[] = $y_key;
                        $q_list = array_merge($q_list, array_map(function ($key) {
                            return $key;
                        }, array_keys($q_data)));
                    }

                    /** selected default quarter from list if no one is selected **/
                    if (!in_array($current_q, $q_list)) {
                        $selected_q = key(array_slice($q_data, -1, 1, true));
                    }

                    /** check year if not exists in exists years, set last of years */
                    if (!empty($years_list) && !in_array($current_year, $years_list)) {
                        $current_year = array_pop($years_list);
                    }

                    //Учет статуса заполнения статистики
                    $activities_task_statistics = array(1 => false, 2 => false, 3 => false, 4 => false);

                    foreach ($qData as $y_key => $yItem):
                        foreach ($yItem as $q => $qItem):
                            $qDataItem = $qItem['data'];

                            $is_activity_complete = true;
                            if (!$qDataItem['forcibly_completed']) {
                                if ($activity->getActivityField()->count() > 0 && $dealer) {
                                    $is_activity_complete = $activity->isActivityStatisticComplete($dealer, null, false, $y_key, $q, array('check_by_quarter' => true));
                                    //$is_activity_complete = $activity->checkForSimpleStatisticComplete($dealer->getId(), $q, $y_key);
                                } else if ($activity->getAllowExtendedStatistic() && $dealer) {
                                    //$is_activity_complete = $activity->checkForStatisticComplete($dealer->getId(), $q, $y_key);
                                    $is_activity_complete = $activity->isActivityStatisticComplete($dealer, null, false, $y_key, $q, array('check_by_quarter' => true));
                                }

                                $activities_task_statistics[$q] = $is_activity_complete;
                            } else {
                                $activities_task_statistics[$q] = true;
                            }

                            ?>
                            <li id="statistic-tab-<?php echo $q; ?>"
                                class="activity-quarter-data <?php echo $selected_q == $q && $y_key == $current_year ? "active" : ""; ?>"
                                data-activity-q="<?php echo $q; ?>"
                                data-activity-year="<?php echo $y_key; ?>">
                                <a href="<?php echo url_for('@activity_quarter_data?activity=' . $activity->getId() . '&current_q=' . $q . '&current_year=' . $y_key); ?>"
                                   style="padding: 5px;">
                                <span>
                                    <i class="icon">
                                    <img src="<?php echo ($qDataItem['completed'] && $activities_task_statistics[$q]) ? "/images/ok-icon-active.png" : "/images/ok-icon.png"; ?>"
                                         alt=""/></i>
                                    <?php echo sprintf('%s - %s', $qDataItem['year'], $roman[$q]); ?> квартал
                                </span>
                                </a>
                            </li>
                        <?php endforeach;
                    endforeach;
                    ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php
        if (!isset($showTask)): ?>
            <div class="stages-wrapper" id="activity-stages">
                <?php
                $activity->callWithModule(function (ActivityModuleDescriptor $descriptor) use ($activity, $sf_user) {
                    $additional = $descriptor->getActivityAdditional();

                    echo $additional;
                }, $sf_user->getAuthUser()->getRawValue());

                $tasks = ActivityTaskTable::getInstance()->createQuery()->where('activity_id = ?', $activity->getId())->orderBy('position ASC')->execute();
                foreach ($tasks as $n => $task):
                    $wasDone = false;
                    try {
                        $dealer = $sf_user->getAuthUser()->getDealer();
                        if ($dealer) {
                            $wasDone = $task->wasDone($dealer->getRawValue(), $activity->getRawValue(), $current_q, $current_year);

                        }
                    } catch (Exception $ex) {
                        if ($activity->getStatus($sf_user->getRawValue()->getAuthUser()) == ActivityModuleDescriptor::STATUS_ACCEPTED) {
                            $wasDone = true;
                        }
                    }

                    ?>
                    <div class="stage<?php if ($sf_user->isDealerUser() && $wasDone) echo ' active' ?>"><?php echo $task->getName() ?></div>
                <?php endforeach; ?>

                <?php if ($activity->isActivityStatisticActivatedInPeriod($current_year, $current_q)): ?>
                    <div
                            class="stage<?php echo isset($activities_task_statistics[$selected_q]) && $activities_task_statistics[$selected_q] ? ' active' : ''; ?>">
                        Статистика
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="stages-wrapper" id="activity-stages"></div>
    <?php endif; ?>
</div>

<script>
    $(function () {
        new ActivityQuartersData({
            activity: '<?php echo $activity->getId(); ?>',
        }).start();
    });
</script>
