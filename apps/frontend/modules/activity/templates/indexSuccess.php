<div class="activity">
    <?php

    if($activity->getHide())
    {
        if(($sf_user->getRawValue()->getAuthUser()->isSuperAdmin() || $sf_user->getRawValue()->getAuthUser()->isImporter()) ||
            $sf_user->getRawValue()->getAuthUser()->checkUserDealerAcceptServiceActivity($activity->getId())) {
            include_partial('activity/activity_head', array('activity' => $activity, 'year' => $year, 'active' => 'info', 'current_q' => $current_q, 'current_year' => $current_year, 'quartersModels' => $quartersModels));
            include_partial('activity_data', array('activity' => $activity));
        }
        else {
            include_partial('activity/activity_head', array('activity' => $activity, 'showTask' => false, 'year' => $year, 'active' => 'info', 'current_q' => $current_q, 'current_year' => $current_year, 'quartersModels' => $quartersModels));

            echo "<div class='activity-unavailable-text'>".sfConfig::get('app_activity_unavailable')."</div>";
        }
    }
    else {
        include_partial('activity/activity_head', array('activity' => $activity, 'year' => $year, 'active' => 'info', 'current_q' => $current_q, 'current_year' => $current_year, 'quartersModels' => $quartersModels));
        include_partial('activity_data', array('activity' => $activity));
    }

    ?>
</div>
