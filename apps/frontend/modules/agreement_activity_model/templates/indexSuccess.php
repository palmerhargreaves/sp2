<div class="activity">
    <?php include_partial('modal_model',
        array
        (
            'activity' => $activity,
            'model_types' => $model_types,
            'model_types_fields' => $model_types_fields,
            'concept_type' => $concept_type,
            'dealer_files' => $dealer_files,
            'activities' => $activities,
            'forms_activities' => $forms_activities,
            'model_categories' => $model_categories,
            'model_categories_fields' => $model_categories_fields,
        )
    );

    include_partial('modal_model_with_categories',
        array
        (
            'activity' => $activity,
            'model_categories' => $model_categories,
            'model_categories_fields' => $model_categories_fields,
            'model_types' => $model_types,
            'model_types_fields' => $model_types_fields,
            'concept_type' => $concept_type,
            'dealer_files' => $dealer_files,
            'activities' => $activities,
            'forms_activities' => $forms_activities,
        )
    );
    ?>

    <?php
    if ($activity->getHide()) {
        if ($sf_user->getRawValue()->getAuthUser()->isSuperAdmin() ||
            $sf_user->getRawValue()->getAuthUser()->checkUserDealerAcceptServiceActivity($activity->getId())
        ) {
            include_partial('activity/activity_head', array('activity' => $activity, 'quartersModels' => $quartersModels, 'current_q' => $current_q, 'current_year' => $current_year, 'show_quarters_tabs' => true));
            include_partial('activity_models',
                array(
                    'activity' => $activity,
                    'has_concept' => $has_concept,
                    'concept' => $concept,
                    'models' => $models,
                    'blanks' => $blanks,
                    'modelId' => $modelId,
                    'necessarily_models' => $necessarily_models,
                    'open_model' => $open_model
                )
            );
        } else {
            include_partial('activity/activity_head', array('activity' => $activity, 'showTask' => false, 'quartersModels' => $quartersModels, 'current_q' => $current_q, 'current_year' => $current_year,));

            echo "<div class='activity-unavailable-text'>" . sfConfig::get('app_activity_unavailable') . "</div>";
        }
    } else {
        include_partial('activity/activity_head', array('activity' => $activity, 'quartersModels' => $quartersModels, 'current_q' => $current_q, 'current_year' => $current_year,));
        include_partial('activity_models',
            array(
                'activity' => $activity,
                'has_concept' => $has_concept,
                'concept' => $concept,
                'models' => $models,
                'blanks' => $blanks,
                'modelId' => $modelId,
                'necessarily_models' => $necessarily_models,
                'open_model' => $open_model
            )
        );
    }
    ?>
</div>
