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
    $data = $main_menu_items->getUsersExtendedRules();
    if (count($data)): ?>
        <li class="sf_admin_action_edit modal-activity-info-params">
            <a href="#" class="action-menu-item-extra-rules" data-id="<?php echo $main_menu_items->getId(); ?>">Редактировать</a>
        </li>
    <?php else: ?>
        <li class="sf_admin_action_new modal-activity-info-params">
            <a href="#" class="action-menu-item-extra-rules" data-id="<?php echo $main_menu_items->getId(); ?>">Добавить</a>
        </li>
    <?php endif; ?>
</ul>



