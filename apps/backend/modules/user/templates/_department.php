<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 13.08.2017
 * Time: 12:01
 */

if ($user->getDepartment()): ?>
    <p style="font-size: 12px;"><?php echo $user->getDepartment()->getUserDepartment()->getName(); ?></p>
<?php endif; ?>

<ul class="sf_admin_actions">
    <li class="sf_admin_action_edit modal-config-user-department" style="border-top: 1px solid #e4e4e4; width: 100%; display: block; margin-top: 10px;">
        <a href="#" class="action-config-user-department" data-id="<?php echo $user->getId(); ?>">Управление</a>
    </li>
</ul>

