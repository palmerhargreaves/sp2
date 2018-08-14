<?php
/**
 * Created by PhpStorm.
 * User: kostig51
 * Date: 26.11.2017
 * Time: 14:13
 */
?>

<div id="sc-modal" class="modal" style="width: 400px;">
    <div class="modal-header">Заполните статистику</div>
    <div class="modal-close modal-close-many"></div>
    <div class="modal-text">
        <p>Необходимо заполнить статистику по активност(и, ям):</p>

        <?php foreach ($data as $concept_id => $activity_data): ?>
            <?php foreach ($activity_data as $activity_id => $items): ?>
                <?php $activity = ActivityTable::getInstance()->findOneById($activity_id); ?>
                <div  style="margin-left: 10px; margin-bottom: 10px;">
                    <div>
                        <a href="<?php echo url_for('@activity_extended_statistic?activity=' . $activity->getId()); ?>"><?php echo $activity->getName(); ?></a>
                    </div>

                    <?php foreach ($items as $step => $item_data): ?>
                        <div style="margin-left: 20px;">
                            <?php echo sprintf("Часть: %s, за %s г. / %s квартал, № концепции - %s", $step, $item_data[ 'year' ], $item_data[ 'q' ], $item_data[ 'concept_id' ]); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>


</div>
