<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 22.10.2017
 * Time: 14:36
 */
?>
<div class="activity">
    <?php include_partial('activity/activity_head', array('activity' => $activity, 'year' => $year, 'active' => 'settings', 'current_q' => $current_q, 'current_year' => $current_year, 'quartersModels' => $quartersModels)); ?>
    <div class="content-wrapper">
        <?php include_partial('activity/activity_tabs', array('activity' => $activity, 'active' => 'settings')) ?>

        <div class="pane-shadow"></div>
        <div class="pane clear">
            <div id="information" class="active">
                <div class="main-column">
                    <div class="agreement-info" style="float: left; width: 99%;">
                        <div class="alert alert-callout alert-info" role="alert">
                            <strong>Управление выполнением активности!</strong>
                        </div>

                        <div class="alert alert-callout alert-warning" role="alert">
                            Выберите квартал(ы) которые будут считаться выполненнымим. Год выполнения активности - <?php echo $year; ?>.
                        </div>
                    </div>

                    <?php $user_dealer = $sf_user->getRawValue()->getAuthUser()->getDealerUsers()->getFirst(); ?>
                    <?php if ($user_dealer): ?>

                        <?php for ($q = 1; $q <= 4; $q++): ?>
                            <div class="checkbox checkbox-inline">
                                <input type="checkbox" class="js-activity-status-by-user"
                                       data-url="<?php echo url_for('@activity_change_status_by_user?activity=' . $activity->getId()); ?>"
                                       data-quarter="<?php echo $q; ?>"
                                       id="ch-activity-status-by-user-<?php echo $q; ?>"
                                       value="<?php echo $activity->getId(); ?>" <?php echo ActivitiesStatusByUsersTable::checkActivityStatus($activity->getId(), $user_dealer->getDealerId(), $year, $q) ? "checked" : ''; ?>>
                                <label for="ch-activity-status-by-user-<?php echo $q; ?>"><?php echo sprintf('Квартал - %d', $q); ?></label>
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>

                <div class="clear"></div>
            </div>

        </div>
    </div>
</div>
