<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 14.08.2017
 * Time: 18:44
 */
?>
<ul class="sf_admin_actions">
    <?php
    if ($main_menu_items->getDepartmentsRules()): ?>
        <li class="sf_admin_action_edit modal-activity-info-params">
            <ul style="font-size: 12px;">
                <?php foreach ($main_menu_items->getDepartmentsRules(false) as $rule): ?>
                    <li style="display: block; margin-top: 3px;"><?php echo $rule; ?></li>
                <?php endforeach; ?>
            </ul>

            <a href="#" class="action-departments-rules-config" style="border-top: 1px solid #e4e4e4; width: 100%; display: block; margin-top: 10px;" data-id="<?php echo $main_menu_items->getId(); ?>">Редактировать</a>
        </li>
    <?php else: ?>
        <li class="sf_admin_action_new modal-activity-info-params">
            <a href="#" class="action-departments-rules-config" data-id="<?php echo $main_menu_items->getId(); ?>">Добавить</a>
        </li>
    <?php endif; ?>
</ul>


