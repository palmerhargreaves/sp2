<?php echo $activity->getName() ?>
<?php
$configurable_modules = array();
foreach ($activity->getModules() as $module) {
    $descriptor = $activity->getModuleDescriptor($module->getRawValue(), $sf_user->getAuthUser()->getRawValue());
    if ($descriptor->hasAdditionalConfiguration())
        $configurable_modules[] = array('module' => $module, 'descriptor' => $descriptor);
}
?>
<?php if ($configurable_modules): ?>
    <table cellspacing="0">
        <thead>
        <tr>
            <th>
                Настройки модулей:
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <?php foreach ($configurable_modules as $m): ?>
                    <ul class="sf_admin_actions">
                        <li class="sf_admin_action_edit">
                            <a href="<?php echo url_for($m['descriptor']->getAdditionalConfigurationUri()) ?>"><?php echo $m['module']->getName() ?></a>
                        </li>
                    </ul>
                <?php endforeach; ?>
            </td>
        </tr>
        </tbody>
    </table>
<?php endif; ?>

<table cellspacing="0">
    <thead>
    <tr>
        <th>
            Тип кампании
        </th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>
            <?php echo $activity->getCompanyType()->getName(); ?>
        </td>
    </tr>
    </tbody>
</table>

<table cellspacing="0">
    <thead>
    <tr>
        <th>
            Обязательные заявки
        </th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>
            <ul class="sf_admin_actions">
                <?php foreach ($activity->getModelsTypesNecessarilyList() as $item): ?>
                    <li>
                        <a href="<?php echo url_for('activity_models_types_necessarily/edit/?id=' . $item->getId()) ?>"><?php echo $item->getAgreementModelType()->getName(); ?></a>
                        <ul>
                            <li class="sf_admin_action_delete">
                                <a href="<?php echo url_for('activity_models_types_necessarily/delete/?id=' . $item->getId()) ?>"
                                   onclick="if(confirm('Вы уверены?')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'post'; f.action = this.href;var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', 'sf_method'); m.setAttribute('value', 'delete'); f.appendChild(m);f.submit(); }; return false; ">удалить</a>
                            </li>
                        </ul>
                    </li>
                <?php endforeach; ?>

                <li class="sf_admin_action_new"><a
                        href="<?php echo url_for('activity_models_types_necessarily/new?activity_id=' . $activity->getId()) ?>">Добавить</a></li>
            </ul>
        </td>
    </tr>
    </tbody>
</table>

<?php if ($activity->getMandatoryActivity()): ?>
    <table cellspacing="0">
        <thead>
        <tr>
            <th>
                Кварталы
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <ul class="sf_admin_actions">
                    <?php /** @var Activity @activity */ ?>
                    <?php foreach ($activity->getMandatoryQuartersList() as $item): ?>
                        <li>
                            <a href="<?php echo url_for('mandatory_activity_quarters/edit/?id=' . $item->getId()) ?>"><?php echo sprintf('%s (%s)', $item->getYear(), $item->getQuarters()) ?></a>
                            <ul>
                                <li class="sf_admin_action_delete">
                                    <a href="<?php echo url_for('mandatory_activity_quarters/delete/?id=' . $item->getId()) ?>"
                                       onclick="if(confirm('Вы уверены?')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'post'; f.action = this.href;var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', 'sf_method'); m.setAttribute('value', 'delete'); f.appendChild(m);f.submit(); }; return false; ">удалить</a>
                                </li>
                            </ul>
                        </li>
                    <?php endforeach; ?>

                    <li class="sf_admin_action_new"><a
                                href="<?php echo url_for('mandatory_activity_quarters/new?activity_id=' . $activity->getId()) ?>">Добавить</a></li>
                </ul>
            </td>
        </tr>
        </tbody>
    </table>
<?php endif; ?>
