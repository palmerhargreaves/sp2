<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 08.11.2017
 * Time: 11:41
 */

?>

<div class="row-fluid">
    <div class="span12">
        <div class="well sidebar-nav">
            <?php if (!$model): ?>
                <div class="alert alert-error">
                    Заявка не найдена.
                </div>
            <?php else: ?>
            <ul class="nav nav-list">
                <li class="nav-header">Активность:</li>
                <li>
                    <?php echo $model->getActivity()->getName(); ?>
                </li>

                <li class="nav-header">Тип заявки:</li>
                <li>
                    <?php echo $model->getModelType()->getName(); ?>
                </li>

                <li class="nav-header">Обязательные типы заявок для активности:</li>
                <li>
                    <ul>
                        <?php if ($model->getIsNecessarilyModel() != 0): ?>
                            <li class="nav-header">
                                <input type='radio' id='ch_type_0'
                                       name='model_type[]'
                                       class='js-change-model-type'
                                       data-id='0'
                                       data-activity-id='0'
                                       data-type-id='0'
                                       data-model-index='<?php echo $model->getId(); ?>'
                                       data-url='<?php echo url_for('@mandatory_model_change_type'); ?>'>
                            </li>


                            <li><label for="ch_type_0">Не обязательная</label></li>
                        <?php endif; ?>

                        <?php
                        $activity_mandatory_types = ActivityModelsTypesNecessarilyTable::getInstance()->createQuery()->where('activity_id = ?', $model->getActivity()->getId())->execute();
                        foreach ($activity_mandatory_types as $type) {
                            if (ActivityModelsTypesNecessarilyUsedTable::alreadyUsed($model, $type)) {
                                continue;
                            }
                            echo "<li class='nav-header'><input type='radio' id='ch_type_" . $type->getId() . "' 
                                     name='model_type[]' 
                                     class='js-change-model-type'
                                     " . ($model->getIsNecessarilyModel() != 0 && $type->getId() == $model->getIsNecessarilyModel() ? "checked" : "") . "
                                     data-id='" . $type->getId() . "'
                                     data-activity-id='" . $type->getActivityId() . "'
                                     data-type-id='" . $type->getModelTypeId() . "'
                                     data-model-index='" . $model->getId() . "'
                                     data-url='" . url_for('@mandatory_model_change_type') . "'></li>";

                            echo "<li>" . sprintf('<label for="ch_type_%s">%s</label><br/>', $type->getId(), $type->getAgreementModelType()->getName()) . "</li>";
                        }
                        ?>
                    </ul>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

