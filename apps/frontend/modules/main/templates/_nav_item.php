<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 27.07.2017
 * Time: 9:41
 */

$can_access = true;
if (isset($access) && is_object($access->getRawValue()) && $access->getRawValue() instanceof Closure) {
    $can_access = call_user_func($access->getRawValue());
}

if ($can_access): ?>
    <div class="item">
        <a target="_blank" href="<?php echo $href; ?>">
            <i><img src="images/menu/<?php echo $img; ?>" alt=""></i>
            <span><em><?php echo $label; ?></em></span>
        </a>
    </div>
<?php endif; ?>
