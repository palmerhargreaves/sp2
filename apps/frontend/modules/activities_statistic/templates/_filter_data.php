<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 23.01.2018
 * Time: 14:15
 */
?>

<h1 class="f-vw"><?php echo !is_null($_activity) ? sprintf('%d - %s', $_activity->getId(), $_activity->getName()) : 'Все активности' ;?></h1>
<div class="stats-summary f-vw d-cb">
    <?php
    include_partial('models', array('models_data' => $completed_models, 'allow_extended_filter' => false, 'model_status' => '', 'title' => 'Засчитанные в бюджет квартала', 'activity' => $_activity));
    include_partial('models', array('models_data' => $in_work_models, 'allow_extended_filter' => true, 'model_status' => $_model_status, 'title' => 'Незасчитанные  в бюджет квартала', 'activity' => $_activity));
    ?>
</div>
