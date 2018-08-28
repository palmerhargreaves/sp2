<?php
/* @var Activity $activity */


?>

<div class="activities-list" style="margin-top: 5px; <?php echo empty($description) ? 'padding-top: 1px;' : '' ?>">
    <h1><?php echo $title; ?></h1>
    <?php if (!empty($description)) : ?>
        <p><?php echo $sf_data->getRaw('description') ?></p>
    <?php endif; ?>

    <div class="grid-activities">
        <?php foreach ($activities as $activity):
            if (!$activity->isActiveForUser($sf_user->getRawValue()->getAuthUser()))
                continue;

            $activity_status = $activity->getStatus($sf_user->getRawValue()->getAuthUser(), $year, D::getQuarter(date('Y-m-d')));

            $status_icon = null;
            $status_icon_title = '';
            $status = '';
            $statusIcon = '';
            switch ($activity_status) {
                case ActivityModuleDescriptor::STATUS_ACCEPTED:
                    $status_icon = 'ok-icon-active_new.png';
                    $status = 'completed';
                    $statusIcon = 'completed';
                    break;
                case ActivityModuleDescriptor::STATUS_WAIT_AGREEMENT:
                    $status_icon = 'wait-icon.png';
                    $status = 'in_work';
                    $statusIcon = 'wait';
                    break;
                case ActivityModuleDescriptor::STATUS_WAIT_DEALER:
                    $status_icon = 'pencil-icon_new.png';
                    $status = 'in_work';
                    $statusIcon = 'progress';
                    break;
                default:
                    $status = 'not_start';
                    $statusIcon = '';
                    break;
            }

            $req_own = '';
            if ($activity->getRequiredActivity()) {
                $req_own = 'yellow';
            } else if ($activity->getOwnActivity()) {
                $req_own = 'blue';
            }
            ?>

            <div class="grid-activities__item">
                <a href="<?php echo url_for('activity/index?activity=' . $activity['id']) ?>" <?php if ($filters['filter_by_status'] != -1 && !is_null($filters['filter_by_status'])):; ?> style="display: <?php echo $filters['filter_by_status'] == $status ? 'block' : "none"; ?>" <?php endif; ?>
                   class="grid-activities__link <?php echo $req_own; ?> <?php if ($activity->getFinished()) echo ' closed' ?>"
                   data-activity-owned="<?php echo $activity->getOwnActivity(); ?>"
                   data-activity-required="<?php echo $activity->getRequiredActivity(); ?>"
                   data-activity-status="<?php echo $status; ?>">

                    <figure class="grid-activities__link__img"
                            style="background-image:url(<?php echo $activity->getImageFile() ? '/uploads/' . Activity::FILE_PREVIEW_PATH . $activity->getPreviewFile() : '/images/logo.png'; ?>);"></figure>

                    <strong class="grid-activities__link__cnt"><?php echo $activity['id'] ?></strong>

                    <span class="grid-activities__link__date">
                    <?php if ($activity['custom_date']): ?>
                        <?php echo nl2br($activity['custom_date']) ?>
                    <?php else: ?>
                        <span>c <?php echo D::toLongRus($activity['start_date']) ?></span>
                        <span>по <?php echo D::toLongRus($activity['end_date']) ?></span>
                    <?php endif; ?>
                        <? /*php if ($activity['select_activity'] == 1): ?>
                        <img src="/images/warn-icon.png" alt="<?php ?>" title="<?php ?>">
                    <?php endif; */
                        ?>
                </span>

                    <strong class="grid-activities__link__title"><?php echo $activity['name'] ?></strong><br/>
                    <span class="grid-activities__link__preview"><?php echo $activity->getRaw('brief') ?></span>

                    <?php if ($status): ?>
                        <figure class="grid-activities__link__action grid-activities__link__action_<?php echo $statusIcon; ?> <?php if (!$activity['is_viewed']) echo ' border'; ?>"
                                title="<?php echo $status_icon_title ?>"></figure>
                    <?php endif; ?>

                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
