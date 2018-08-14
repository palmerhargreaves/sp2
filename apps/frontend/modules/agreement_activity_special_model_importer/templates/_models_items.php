<?php

$k = 0;
$isBlocked = false;
$ind = $page > 1 ? ($page - 1) * sfConfig::get('app_max_models_on_page') : 1;

$is_user_admin_or_importer = $sf_user->getAuthUser()->isSuperAdmin() || $sf_user->getAuthUser()->isImporter();
$is_user_manager_or_specialist = $sf_user->isSpecialist() || $sf_user->isManager();

foreach ($models as $n => $model):
    ?>
    <tr class="sorted-row model model-row<?php if ($k % 2 == 0) echo ' even' ?>"
        data-model="<?php echo $model->getId() ?>"
        data-discussion="<?php echo $model->getDiscussionId() ?>" data-new-messages="<?php echo $new_messages_count ?>"
        data-is-blocked='<?php echo $isBlocked ? 1 : 0; ?>'>
        <td data-sort-value="<?php echo $ind ?>"><?php echo $ind++; ?></td>
        <td data-sort-value="<?php echo $model->getId() ?>">
            <div class="num">№ <?php echo $model->getId() ?></div>
            <div class="date"><?php echo D::toLongRus($model->created_at) ?></div>
            <?php
            if ($is_user_admin_or_importer):
                if ($model->getIsBlocked() && !$model->getAllowUseBlocked()):
                    ?>
                    <input type="button" class="button small unblock-model" value="Разблокировать"
                           data-model-id="<?php echo $model->getId(); ?>"
                           style="margin-top: 5px; margin-bottom: 5px; z-index: 999;">
                <?php endif;
            endif;
            ?>
        </td>
        <?php
        $dealer = isset($dealers_list[$model->getDealerId()]) ? $dealers_list[$model->getDealerId()] : null;
        if ($dealer):
            ?>
            <td data-sort-value="<?php echo $dealer['name']; ?>"><?php echo $dealer['name'], ' (', $dealer['number'], ')' ?></td>
        <?php else: ?>
            <td></td>
        <?php endif; ?>

        <td data-sort-value="<?php echo $model->getName() ?>" style="word-break: break-word;">
            <div><?php echo $model->getName() ?></div>
            <div class="sort"></div>
        </td>
        <td data-sort-value="<?php echo $model->getShareName() ?>">
            <div><?php echo $model->getShareName() ?></div>
            <div class="sort"></div>
        </td>
        <?php /*<td class="placement <?php echo $model->getModelType()->getIdentifier() ?>"><div class="address"><?php echo $model->getValueByType('place') ?></div></td> */
        ?>
        <td>
            <?php if ($model->isValidModelCategory()): ?>
                <?php echo $model->getPeriod(); ?>
            <?php else: ?>
                <?php echo $model->getValueByType('period') ?>
            <?php endif; ?>
        </td>
        <td data-sort-value="<?php echo $model->getCost() ?>" style="white-space: nowrap;">
            <div><?php echo number_format($model->getCost(), 0, '.', ' ') ?> руб.</div>
            <div class="sort"></div>
        </td>
        <td class="darker">
            <div><?php echo $wait_filter == 'specialist' ? $model->getSpecialistActionText() : $model->getManagerActionText() ?></div>
            <div class="sort"></div>
        </td>

        <?php if ($model->getStatus() != 'not_sent' && $model->getStatus() != 'declined'): ?>
            <?php if ($is_user_manager_or_specialist && $model->getCssStatus() != 'ok') { ?>
                <?php if (!empty($n)) { ?>
                    <td class="darker darker-model-<?php echo $model->getId(); ?>"
                        style="white-space: nowrap; <?php echo $model->isModelAcceptActiveToday($designer_filter ? false : true) ? 'background-color: rgb(233, 66, 66);' : '' ?>">
                        <div><?php echo date('H:i d-m-Y', $n); ?></div>
                        <div class="sort"></div>
                    </td>
                <?php } else { ?>
                    <td class="darker">
                        <div></div>
                    </td>
                <?php } ?>
            <?php } else {
                if ($model->getReport() && $model->getReport()->getStatus() != "accepted" && $model->getReport()->getStatus() != 'declined' && $model->getReport()->getStatus() != 'not_sent'):
                    ?>
                    <td class="darker darker-model-<?php echo $model->getId(); ?>"
                        style="white-space: nowrap; <?php echo $model->isModelAcceptActiveToday(!$designer_filter ? false : true) ? 'background-color: rgb(233, 66, 66);' : ''
                        ?>">
                        <div><?php echo date('H:i d-m-Y', $n); ?></div>
                    </td>
                <?php else: ?>
                    <td class="darker" style="">
                        <div class="sort"></div>
                    </td>
                <?php endif;
            } ?>
        <?php else: ?>
            <td class="darker" style="">
                <div class="sort"></div>
            </td>
        <?php endif; ?>

        <?php $waiting_specialists = $model->countWaitingSpecialists(); ?>
        <td class="darker">
            <div
                class="<?php echo $model->getCssStatus() ?>"><?php //echo $waiting_specialists ? 'x' . $waiting_specialists : '' ?></div>
        </td>
        <?php $waiting_specialists = $model->countReportWaitingSpecialists(); ?>
        <td class="darker">
            <div
                class="<?php echo $model->getReportCssStatus() ?>"><?php //echo $waiting_specialists ? 'x' . $waiting_specialists : '' ?></div>
        </td>
        <td data-sort-value="0" class="darker">
            <div class="message message-model-<?php echo $model->getId(); ?>" style="display: none;"></div>
        </td>
    </tr>
    <?php $k++; endforeach; ?>

<script>
    $(function () {
        window.model_discussion_load = new ModelDiscussionCountLoad({
            load_url: '<?php echo url_for('@agreement_models_load_discussion_count'); ?>',
            designer_filter: <?php echo !$designer_filter ? 0 : 1; ?>
        }).start();
    });
</script>
