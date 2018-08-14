<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 05.08.2016
 * Time: 16:32
 */

if (!$model->isCompleted()) {
    /** @var AgreementModel $model */

    if (!$model->getReport() || ($model->getReport() && $model->getReport()->getStatus() != 'wait')) {

        //Check model work process (blocked, left days)
        if ($work_status = $model->workStatus()) { ?>
            <?php $work_status = $work_status->getRawValue(); ?>
            <div>
                <img style="float: left; margin-right: 5px;"
                     src="/images/<?php echo $work_status['img']; ?>" data-toggle="tooltip"
                     title="<?php echo $work_status['label']; ?>"/>
                <span><?php echo $work_status['text']; ?></span>
            </div>
            <?php
        }
        
    }
}

