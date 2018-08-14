<div class="finished">
    <?php //include_partial('activity/activities', array('activities' => $activities, 'title' => '', 'onlyShow' => $onlyShow)); ?>
    <div class="activities-list" style="margin-top: 5px; <?php echo empty($description) ? 'padding-top: 1px;' : '' ?>">
        <?php foreach ($activities as $activity):
            if (!$activity->isActiveForUser($sf_user->getRawValue()->getAuthUser())) {
                continue;
            }

            if (!$activity->getFinished()) {
                continue;
            }

            $status_icon = null;
            $status_icon_title = '';
            $status = '';

            switch ($activity->getStatus($sf_user->getRawValue()->getAuthUser())) {
                case ActivityModuleDescriptor::STATUS_ACCEPTED:
                    $status_icon = 'ok-icon-active_new.png';
                    $status = 'completed';
                    break;
                case ActivityModuleDescriptor::STATUS_WAIT_AGREEMENT:
                    $status_icon = 'wait-icon.png';
                    $status = 'in_work';
                    break;
                case ActivityModuleDescriptor::STATUS_WAIT_DEALER:
                    $status_icon = 'pencil-icon_new.png';
                    $status = 'in_work';
                    break;
                default:
                    $status = 'not_start';
                    break;
            }
            ?>

            <a href="<?php echo url_for('activity/index?activity=' . $activity['id']) ?>" <?php if ($filters['filter_by_status'] != -1):; ?>  style="display: <?php echo $filters['filter_by_status'] == $status ? 'block' : "none"; ?>" <?php endif; ?>
               class="activity<?php if ($activity['is_viewed']) echo ' closed' ?>"
               data-activity-owned="<?php echo $activity->getOwnActivity(); ?>"
               data-activity-required="<?php echo $activity->getRequiredActivity(); ?>"
               data-activity-status="<?php echo $status; ?>">
                <div class="num"><?php echo $activity['id'] ?></div>
                <div class="date">
                    <?php if ($activity['custom_date']): ?>
                        <?php echo nl2br($activity['custom_date']) ?>
                    <?php else: ?>
                        <div >c <?php echo D::toLongRus($activity['start_date']) ?></div>
                        <div >по <?php echo D::toLongRus($activity['end_date']) ?></div>
                    <?php endif; ?>
                    <?php if ($activity['select_activity'] == 1): ?>
                        <img src="/images/warn-icon.png" alt="<?php ?>" title="<?php ?>">
                    <?php endif; ?>
                </div>
                <div class="text">
                    <div class="title"><?php echo $activity['name'] ?></div>
                    <div class="desc"><?php echo $activity->getRaw('brief') ?></div>
                </div>
                <div class="activity-status-ico">
                    <div class="img-wrapper<?php if (!$activity['is_viewed']) echo ' border'; ?>">
                        <?php if ($status_icon): ?>
                            <img src="/images/<?php echo $status_icon ?>" alt="<?php echo $status_icon_title ?>"
                                 title="<?php echo $status_icon_title ?>">
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

</div>
