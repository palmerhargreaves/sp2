<table>
    <tbody>
    <?php
    $groups = array();
    $special_activity_users_list = array();
    if ($model->isConcept() && $model->getActivity()->getAllowSpecialAgreement()) {
        $specialist_groups = array($special_importer_groups);

        $special_activity_users = ActivityAgreementByUserTable::getInstance()->createQuery()->where('activity_id = ?', $model->getActivity()->getId())->execute();
        foreach ($special_activity_users as $special_user_item) {
            $special_activity_users_list[] = $special_user_item->getUser()->getId();
        }

        if (!empty($special_activity_users_list)) {
            $special_activity_users_list = UserTable::getInstance()->createQuery()->whereIn('id', $special_activity_users_list)->execute();
        }
    } else {
        foreach ($specialist_groups as $group) {
            $groups[] = $group;
        }
        $specialist_groups = array_reverse($groups);
    }

    foreach ($specialist_groups as $group): ?>
        <?php $active_users = count($special_activity_users_list) > 0 ? $special_activity_users_list : $group->getActiveUsers(); ?>

        <?php if (count($active_users) > 0): ?>
            <tr class="group-row" data-group="<?php echo $group->getId() ?>">
                <td>
                    <input type="checkbox" name="specialist[group][<?php echo $group->getId() ?>]"
                           id="opt-specialist-<?php echo $group->getId() ?>"/>
                    <label for="opt-specialist-<?php echo $group->getId() ?>"><?php echo $group->getName() ?></label>
                </td>
                <td width="60%">
                    <div class="modal-select-wrapper select krik-select input">
                        <span class="select-value"><?php echo $active_users->getFirst()->selectName() ?></span>
                        <input type="hidden" name="specialist[user][<?php echo $group->getId() ?>]"
                               value="<?php echo $active_users->getFirst()->getId() ?>">
                        <div class="modal-input-error-icon error-icon"></div>
                        <div class="error message"></div>
                        <div class="ico"></div>
                        <div class="modal-select-dropdown">
                            <?php foreach ($active_users as $user): ?>
                                <div class="modal-select-dropdown-item select-item"
                                     data-value="<?php echo $user->getId() ?>"><?php echo $user->selectName() ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
</table>
