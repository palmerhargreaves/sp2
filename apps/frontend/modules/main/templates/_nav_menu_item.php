<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 27.07.2017
 * Time: 9:41
 */

if ($menu_item->canAccess($dealer, $user)): ?>
    <?php $url = $menu_item->getMenuUrl($dealer, $user); ?>
    <div class="item">
        <a target="_blank" href="<?php echo $url; ?>">
            <i><img src="uploads/images/menu/<?php echo $menu_item->getImage(); ?>" alt="<?php echo $menu_item->getName(); ?>"></i>
            <span>
                <em class="<?php echo !$menu_item->getUrlName() ? "single" : ""; ?>"><?php echo $menu_item->getName(); ?></em>
                <?php if ($menu_item->getUrlName()): ?>
                    <em>(<?php echo $menu_item->getUrlName(); ?>)</em>
                <?php endif; ?>
            </span>
        </a>
    </div>
<?php endif; ?>
