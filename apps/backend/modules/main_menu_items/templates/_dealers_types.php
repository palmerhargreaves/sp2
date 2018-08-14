
<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 16.08.2017
 * Time: 19:20
 */
?>
<ul>
    <?php foreach (DealersTypesTable::getInstance()->createQuery()->execute() as $dealer_type): ?>
    <li>
        <label for="dealer_type_<?php echo $main_menu_items->getId(); ?>_<?php echo $dealer_type->getId(); ?>" style="font-size: 12px;"><?php echo ucfirst($dealer_type->getName()); ?></label>
        <input type="checkbox" id="dealer_type_<?php echo $main_menu_items->getId(); ?>_<?php echo $dealer_type->getId(); ?>"
               style="float: left;"
               class="dealers-types-check dealers-types-checks-<?php echo $main_menu_items->getId(); ?>"
               data-menu-item-id="<?php echo $main_menu_items->getId(); ?>"
               data-dealer-type-id="<?php echo $dealer_type->getId(); ?>"
               value="<?php echo $dealer_type->getId(); ?>"
               <?php echo $main_menu_items->checkDealerTypeRule($dealer_type->getId()) ? "checked" : ""; ?>
        />
    </li>
<?php endforeach; ?>
</ul>


