<?php
    $model_statuses = arraY
    (
        'all' => 'Все заявки',
        'declined' => 'Заявки с несогласованными материалами',
        'wait' => 'Заявки на проверке в агентстве',
        'no_report' => 'Заявки с неподгруженными отчетами',
        'wait_report' => 'Отчеты на проверке в агентстве',
        'blocked' => 'Заблокированные заявки',
    );
?>
<div class="stats-summary__block">
    <table>
        <thead>
        <tr class="ttop">
            <th colspan="2"><?php echo $title; ?></th>
            <th class="d-tar" style="white-space: nowrap;"><span><?php echo Utils::numberFormat($models_data->getTotalAmountByModels(), ''); ?></span> руб.</th>
        </tr>

        <?php if ($allow_extended_filter): ?>
            <tr class="tmid">
                <th colspan="2">
                    <div id="sb-model-status" class="modal-select-wrapper select input krik-select select_white">
                        <span class="select-value"><?php echo $model_statuses[$model_status]; ?></span>
                        <div class="ico"></div>
                        <input type="hidden" name="model_status" value="<?php echo $model_status; ?>">
                        <div class="modal-input-error-icon error-icon"></div>
                        <div class="error message"></div>
                        <div class="modal-select-dropdown">
                            <?php foreach ($model_statuses as $key => $status): ?>
                                <div class="modal-select-dropdown-item select-item" data-value="<?php echo $key; ?>"><?php echo $status; ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </th>
                <th></th>
            </tr>
        <?php else: ?>
            <tr class="tmid">
                <th colspan="2"></th>
                <th></th>
            </tr>
        <?php endif; ?>

        <tr>
            <th width="20%">№ заявки</th>
            <?php if (!is_null($activity)): ?>
                <th>Название материала</th>
            <?php else: ?>
                <th>Название активности</th>
            <?php endif; ?>
            <th width="32%" class="d-tar">Сумма, руб.</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($models_data->getModelsList() as $model_item): ?>
            <?php $model = $model_item['model']; ?>
        <tr>
            <td>
                <div class="stats-summary__num <?php echo $model_item['model_cls']; ?>">
                    <a style="color: #000; text-decoration: none;" href="<?php echo url_for("@agreement_module_models_model_copy?activity=".$model->getActivityId().'&model='.$model->getId().'&current_q='.$model_item['quarter']); ?>" target="_blank"><?php echo $model->getId(); ?></a>
                </div>
            </td>
            <td>
                <?php if (!is_null($activity)): ?>
                    <?php echo $model->getName(); ?>
                <?php else: ?>
                    <?php echo sprintf('%d - %s', $model->getActivity()->getId(), $model->getActivity()->getName()); ?>
                <?php endif; ?>
            </td>
            <td class="d-tar"><strong><?php echo Utils::numberFormat($model->getCost(), ''); ?></strong></td>
        </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
</div><!-- /stats-summary__block -->
