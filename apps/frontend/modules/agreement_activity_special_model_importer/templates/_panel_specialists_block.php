<table><tbody>
    <?php
    $groups = arraY();
    foreach ($specialist_groups as $group) {
        $groups[] = $group;
    }
    $specialist_groups = array_reverse($groups);

    foreach ($specialist_groups as $group): ?>
        <?php $active_users = $group->getActiveUsers() ?>
        <?php if ($active_users->count() > 0): ?>
            <tr class="group-row" data-group="<?php echo $group->getId() ?>">
                <td>
                    <input type="checkbox" name="specialist[group][<?php echo $group->getId() ?>]" id="opt-specialist-<?php echo $group->getId() ?>" />
                    <label for="opt-specialist-<?php echo $group->getId() ?>"><?php echo $group->getName() ?></label>
                </td>
                <td width="60%">
                    <div class="modal-select-wrapper select krik-select input">
                        <span class="select-value"><?php echo $active_users->getFirst()->selectName() ?></span>
                        <input type="hidden" name="specialist[user][<?php echo $group->getId() ?>]" value="<?php echo $active_users->getFirst()->getId() ?>">
                        <div class="modal-input-error-icon error-icon"></div>
                        <div class="error message"></div>
                        <div class="ico"></div>
                        <div class="modal-select-dropdown">
                            <?php foreach ($active_users as $user): ?>
                                <div class="modal-select-dropdown-item select-item" data-value="<?php echo $user->getId() ?>"><?php echo $user->selectName() ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
    </tbody></table>
