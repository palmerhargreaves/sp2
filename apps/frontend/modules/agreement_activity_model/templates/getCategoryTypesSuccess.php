<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 15.01.2017
 * Time: 15:11
 */
?>

<div class="modal-select-wrapper select input krik-select model-category-types-select">
    <?php if ($model_type): ?>
        <span class="select-value select-value-model-type"><?php echo $model_type->getName(); ?></span>
    <?php else: ?>
        <span class="select-value select-value-model-type"></span>
    <?php endif; ?>
    <div class="ico"></div>
    <input type="hidden" name="model_type_id" value="<?php echo $model_type ? $model_type->getId() : ''; ?>" data-is-sys-admin="<?php echo $sf_user->getRawValue()->getAuthUser()->isSuperAdmin() ? 1 : 0; ?>">
    <div class="modal-input-error-icon error-icon"></div>
    <div class="error message"></div>
    <div class="modal-select-dropdown">
        <?php foreach ($model_types as $type): ?>
            <div class="modal-select-dropdown-item select-item select-model-type-item-<?php echo $type->getId(); ?>"
                 data-value="<?php echo $type->getId() ?>"><?php echo $type->getName() ?></div>
        <?php endforeach; ?>
    </div>
</div>
<div class="value"><?php echo $model_type ? $model_type->getName() : ''; ?></div>
