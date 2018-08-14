<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 29.05.2018
 * Time: 19:25
 */

$is_user_manager_or_specialist = $sf_user->isSpecialist() || $sf_user->isManager();

?>

<div class="stats-summary__block">
    <table id="<?php echo $models_table_id; ?>">
        <thead>
        <tr class="ttop">
            <th colspan="4"><?php echo $label; ?></th>
        </tr>

        <tr class="tmid">
            <th colspan="4"></th>
        </tr>

        <tr>
            <th width="15%">№ заявки</th>
            <th>Название</th>
            <th>Макет</th>
            <th>Отчет</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($models as $n => $model): ?>
            <?php //Вывод заявок только отмеченных как просмотренные ?>
            <?php if ($is_viewed && !$model->getNoModelChangesView()): ?>
                <?php continue; ?>
            <?php endif; ?>

            <?php if (!$is_viewed && $model->getNoModelChangesView()): ?>
                <?php continue; ?>
            <?php endif; ?>

            <tr class="sorted-row model model-row model-row-id-<?php echo $model->getId(); ?>"
                data-model="<?php echo $model->getId() ?>"
                data-discussion="<?php echo $model->getDiscussionId() ?>">
                <td>
                    <div class="stats-summary__num bc_green">
                        <a style="color: #000; text-decoration: none;" href="javascript:;"
                           target="_blank"><?php echo $model->getId(); ?></a>
                    </div>
                </td>
                <td>
                    <div style="float: left; width: 100%; display: inline-block;"><?php echo $model->getName(); ?></div>
                    <div style="float: left; width: 100%; display: inline-block; font-size: 12px; padding-left: 3px; white-space: nowrap; <?php echo $model->isModelAcceptActiveToday(!$designer_filter ? false : true) ? 'background-color: rgb(233, 66, 66);' : ''; ?>">
                    <?php
                        if ($model->getStatus() != 'not_sent' && $model->getStatus() != 'declined') {
                            if ($is_user_manager_or_specialist && $model->getCssStatus() != 'ok') {
                                if (!empty($n)) {
                                    echo "согласовать до: ". date('H:i d-m-Y', $n);
                                }
                            } else {
                                if ($model->getReport() && $model->getReport()->getStatus() != "accepted" && $model->getReport()->getStatus() != 'declined' && $model->getReport()->getStatus() != 'not_sent') {
                                    echo "согласовать до: ". date('H:i d-m-Y', $n);
                                }
                            }
                        }
                    ?>
                    </div>
                </td>
                <td class="d-tar">
                    <div class="<?php echo $model->getCssStatus() ?>"></div>
                </td>
                <td class="d-tar">
                    <div class="<?php echo $model->getReportCssStatus() ?>"></div>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
</div>
