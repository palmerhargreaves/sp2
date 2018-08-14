<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 05.08.2016
 * Time: 13:35
 */

/** @var AgreementModel $model */
?>
<?php if (count($models) > 0 || count($blanks) > 0): ?>
    <table class="models normal-models">
        <thead>
        <tr>
            <td width="80">
                <div class="has-sort">ID / Дата</div>
                <div class="sort has-sort"></div>
            </td>
            <td width="130">
                <div class="has-sort">Название</div>
                <div class="sort has-sort"></div>
            </td>

            <?php if ($activity->getIsOwn()): ?>
                <td width="60">
                    <div class="has-sort">Акция</div>
                    <div class="sort has-sort"></div>
                </td>
            <?php endif; ?>

            <td width="160">
                <div>Размещение</div>
            </td>
            <td>
                <div>Период</div>
            </td>
            <td width="75">
                <div class="has-sort">Сумма</div>
                <div class="sort has-sort"></div>
            </td>
            <td width="100">
                <div>Действие</div>
            </td>
            <td width="50" style="padding:0;text-align:center;">
                Макет
            </td>
            <td width="50" style="padding:0;text-align:center;">
                Отчет
            </td>
            <td width="120" style="padding:0;text-align:center;">
                Статус
                <div class="has-sort"></div>
                <!--div class="sort has-sort" data-sort="messages"></div-->
            </td>
            <td>&nbsp;</td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($blanks as $n => $blank): ?>
            <tr class="draft<?php echo $model->isValidModelCategory() ? 'model-row-with-category' : 'model-row'; ?><?php if ($n % 2 == 0) echo ' even' ?>"
                data-blank="<?php echo $blank->getId() ?>"
                data-type="<?php echo $blank->getModelType()->getId() ?>"
                data-name="<?php echo $blank->getName() ?>">
                <td>
                    <div class="num">№ ...</div>
                    <div class="date">...</div>
                </td>
                <td>
                    <div><?php echo $blank->getName() ?></div>
                    <div class="sort"></div>
                </td>
                <td title="<?php echo $blank->getModelType()->getName() ?>"
                    class="placement <?php echo $blank->getModelType()->getIdentifier() ?>">
                    <div class="address"></div>
                </td>
                <td></td>
                <td>
                    <div></div>
                    <div class="sort"></div>
                </td>
                <td class="darker">
                    <div>Нажмите, чтобы добавить макет</div>
                    <div class="sort"></div>
                </td>
                <td class="darker">
                    <div class="none"></div>
                </td>
                <td class="darker">
                    <div class="none"></div>
                </td>
                <td class="darker"></td>
            </tr>
        <?php endforeach; ?>

        <?php foreach ($models as $n => $model): ?>
            <?php $discussion = $model->getDiscussion() ?>
            <?php $new_messages_count = $discussion ? $discussion->countUnreadMessages($sf_user->getAuthUser()->getRawValue()) : 0 ?>
            <tr class="model-row-id-<?php echo $model->getId(); ?> sorted-row <?php echo $model->isValidModelCategory() ? 'model-row-with-category' : 'model-row'; ?><?php if (($n + count($blanks) % 2) % 2 == 0) echo ' even' ?><?php if ($model->getStatus() == 'not_sent') echo ' draft' ?> <?php echo $model->getId() == $open_model ? 'auto-click' : ''; ?> "
                data-model="<?php echo $model->getId() ?>"
                data-discussion="<?php echo $model->getDiscussionId() ?>"
                data-new-messages="<?php echo $new_messages_count ?>"
                style="<?php echo $model->getIsDeleted() ? "opacity: 0.5;" : "" ; ?>"
            >
                <!-- TBD: Добавить класс draft, если черновик -->
                <td data-sort-value="<?php echo $model->getId() ?>">
                    <div class="num">№ <?php echo $model->getId() ?></div>
                    <div class="date"><?php echo D::toLongRus($model->created_at) ?></div>
                </td>
                <td data-sort-value="<?php echo $model->getName() ?>">
                    <?php if (!$model->getActivity()->getFinished() && $model->isValidModelCategory()): ?>
                        <img class="copy-model" src="/images/ico_files.png" title="Скопировать заявку" data-model-id="<?php echo $model->getId(); ?>" />
                    <?php endif; ?>

                    <div class="title-w-icon" style="left: 23px; width: 120px; text-overflow: ellipsis; overflow: hidden;"><?php echo $model->getName() ?></div>
                    <div class="sort"></div>
                </td>

                <?php if ($activity->getIsOwn()): ?>
                    <td data-sort-value="<?php echo $model->getShareName() ?>">
                        <div><?php echo $model->getShareName() ?></div>
                        <div class="sort"></div>
                    </td>
                <?php endif; ?>

                <td title="<?php echo $model->getModelType()->getName() ?>"
                    class="placement <?php echo $model->getModelType()->getIdentifier() ?>">
                    <div
                        class="address"><?php if ($model->getValueByType('place')): ?><?php echo $model->getValueByType('place') ?><? else: ?>-<?php endif; ?></div>
                </td>
                <td>
                    <?php if ($model->isValidModelCategory()): ?>
                        <?php echo $model->getPeriod(); ?>
                    <?php else: ?>
                        <?php echo $model->getValueByType('period') ?>
                    <?php endif; ?>
                </td>
                <td data-sort-value="<?php echo $model->getCost() ?>">
                    <div><?php echo number_format($model->getCost(), 0, '.', ' ') ?> руб.</div>
                    <div class="sort"></div>
                </td>
                <td class="darker">
                    <div><?php echo $model->getDealerActionText() ?></div>
                    <div class="sort"></div>
                </td>
                <td class="darker">
                    <div class="<?php echo $model->getCssStatus() ?>">
                        <?php //if ($model->getStatus() == 'wait_specialist') echo 'x' . $model->countWaitingSpecialists(); ?>
                        <?php //if ($model->getStatus() == 'declined' && $model->countDeclines()) echo 'x' . $model->countDeclines(); ?>
                    </div>
                </td>
                <?php $report = $model->getReport(); ?>
                <td class="darker">
                    <div class="<?php echo $model->getReportCssStatus() ?>">
                        <?php //if ($report && $report->getStatus() == 'wait_specialist') echo 'x' . $report->countWaitingSpecialists(); ?>
                        <?php //if ($report && $report->getStatus() == 'declined' && $report->countDeclines()) echo 'x' . $report->countDeclines(); ?>
                    </div>
                </td>
                <td class="darker">
                    <?php include_partial('activity_model_status', array('model' => $model)); ?>
                </td>
                <td class="darker">
                    <?php if ($model->getIsDeleted()): ?>
                        <?php if ($sf_user->getAuthUser()->isManager() || $sf_user->getAuthUser()->isImporter()): ?>
                        <img src="/images/arrow_large_up.png" class="undo-delete-model"
                             title="Восстановить заявку"
                             data-model-id="<?php echo $model->getId(); ?>" >
                        <?php endif; ?>
                    <?php else: ?>
                        <img src="/images/remove-icon.png" class="delete-model"
                             title="Удалить заявку"
                             data-model-id="<?php echo $model->getId(); ?>" >
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
