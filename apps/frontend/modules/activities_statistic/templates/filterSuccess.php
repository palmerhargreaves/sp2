<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 30.11.2016
 * Time: 10:04
 */
include_partial('filter_data',
    array
    (
        '_activity' => $_activity,
        'completed_models' => $completed_models,
        'in_work_models' => $in_work_models
    )
);
?>


