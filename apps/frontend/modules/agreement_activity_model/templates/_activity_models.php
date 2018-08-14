<div class="content-wrapper">
    <?php include_partial('activity/activity_tabs', array('activity' => $activity, 'active' => 'agreement')) ?>

    <div id="agreement-models" class="pane clear">

        <?php if ($has_concept): ?>
            <?php include_partial('agreement_activity_model/concept', array('concept' => $concept, 'activity' => $activity)) ?>
        <?php endif; ?>

        <div id="approvement" class="active">
            <div class="agreement-info" style="float: left; width: 99%;">
                <!--<div class="alert alert-callout alert-error" role="alert">
                    <strong>Внимание!</strong> Все заявки, размещаемые в течение квартала, должны быть заведены в период этого квартала.
                </div>-->

                <?php $days = $activity->getActivityLeftDays(); ?>
                <div class="alert alert-callout alert-<?php echo $activity->getLeftDaysStatus(); ?>" role="alert">
                    <strong>Выполнение активности!</strong> До завершения активности:
                    <?php echo is_string($days) ? 'Последний день' : sprintf('%s д%s', $days, NumbersHelper::numberEnd($days, array('ень', 'ня', 'ней'))); ?>
                </div>
            </div>

            <?php /* обязательные заявки */ ?>
            <?php if (!$activity->getFinished()): ?>
                <?php if ($activity->canAddModels()
                    && $activity->canAddModelsForSC($sf_user->getAuthUser()->getRawValue())
                    && $activity->canAddModelsWithSpecialAgreement($sf_user->getAuthUser()->getRawValue())): ?>
                <div class="agreement-models-btns">
                    <?php foreach ($activity->getActiveModelsTypesNecessarilyList($sf_user->getAuthUser()->getRawValue()) as $item): ?>
                        <span id="add-necessarily-model"
                              data-id="<?php echo $item->getId(); ?>"
                              data-model-type-id="<?php echo $item->getAgreementModelType()->getId(); ?>"
                              data-model-type="<?php echo $item->getAgreementModelType()->getIdentifier(); ?>"
                              data-model-type-name="<?php echo $item->getAgreementModelType()->getName(); ?>"
                              data-model-type-category-id="<?php echo $item->getAgreementModelType()->getParentCategoryId(); ?>"
                              class="btn btn-<?php echo $item->getAgreementModelType()->getIdentifier(); ?>"><?php echo $item->getAgreementModelType()->getName(); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (count($necessarily_models) > 0): ?>
                <div class="agreement-models-title" style="float:left; clear: left;">Обязательные заявки</div>
                <?php include_partial('activity_necessarily_models', array('models' => $necessarily_models, 'activity' => $activity)); ?>

                <hr class="hr-sep" style="clear:both;"/>
            <?php endif; ?>

            <?php /* обычные заявки */ ?>
            <?php if (!$activity->getFinished()) { ?>
                <?php if ($activity->canAddModels()
                    && $activity->canAddModelsForSC($sf_user->getAuthUser()->getRawValue())
                    && $activity->canAddModelsWithSpecialAgreement($sf_user->getAuthUser()->getRawValue())): ?>
                <div class="agreement-models-btns">
                    <span id="add-model-categories-button" data-temp="add-model-button"
                          class="btn btn-add"><span>+</span> Добавить макет</span>
                </div>
                <?php endif; ?>
            <?php } ?>
        </div>

        <?php if (count($necessarily_models) > 0): ?>
            <div class="agreement-models-title">Дополнительные заявки</div>
        <?php endif; ?>

        <?php include_partial('activity_normal_models', array('blanks' => $blanks, 'models' => $models, 'open_model' => $open_model, 'activity' => $activity)); ?>
    </div>
</div>


<script type="text/javascript">
    $(function () {
        new TableSorter({
            selector: '#approvement table.normal-models'
        }).start();

        new TableSorter({
            selector: '#approvement table.necessarily-models'
        }).start();

        $('table.normal-models .auto-click').trigger('click');

        $('.copy-model').click(function (e) {
            var model_id = $(this).data('model-id');

            e.stopPropagation();
            if (confirm('Скопировать заявку ?')) {
                $.post('<?php echo url_for('@agreement_model_copy'); ?>', {model_id: model_id}, function (result) {
                    if (result.success) {
                        window.location.href = result.url;
                    } else {
                        alert('Ошибка при копировании заявки!');
                    }
                });
            }
        });

        //Удаление заявки дилером или администратором
        $('.delete-model').click(function(event) {
            var model_id = $(event.currentTarget).data('model-id');

            event.stopPropagation();
            if (confirm('Вы действительно хотите удалить заявку? После удаления она не будет доступна и будет удалена из бюджета.')) {
                $.post('<?php echo url_for('@agreement_model_delete_by_dealer'); ?>', { model_id: model_id }, function (result) {
                    if (result.success) {
                        alert('Заявка успешно удалена!');
                        window.location.reload();
                    } else {
                        alert('Ошибка при удалении заявки!');
                    }
                });
            }
        });

        //Восстановление удаленной заявки администратором
        $('.undo-delete-model').click(function(event) {
            var model_id = $(event.currentTarget).data('model-id');

            event.stopPropagation();
            if (confirm('Вы действительно хотите восстановить удаленную заявку?')) {
                $.post('<?php echo url_for('@agreement_model_undo_delete_by_dealer'); ?>', { model_id: model_id }, function (result) {
                    if (result.success) {
                        alert('Заявка успешно восстановлена!');
                        window.location.reload();
                    } else {
                        alert('Ошибка при восстановлении заявки!');
                    }
                });
            }
        });
    });
</script>
