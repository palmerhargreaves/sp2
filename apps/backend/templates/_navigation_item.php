<?php
if (!is_array($role))
    $role = array($role);

$role[] = 'admin';
$params = '';

if (empty($action)) {
    $action = 'index';
}

if (!empty($url_params)) {
    $params = $url_params;
}

?>
<?php if ($sf_user->hasCredential($role, false)): ?>
    <?php if (!empty($params)): ?>
        <li<?php if ($sf_context->getModuleName() == $module): ?> class="active"<?php endif; ?>><a href="<?php echo url_for($module . '/' . $action.'/params?param='.$params) ?>"><?php echo $name ?></a></li>
    <?php else: ?>
        <li<?php if ($sf_context->getModuleName() == $module): ?> class="active"<?php endif; ?>><a href="<?php echo url_for($module . '/' . $action.$params) ?>"><?php echo $name ?></a></li>
    <?php endif; ?>
<?php endif; ?>
