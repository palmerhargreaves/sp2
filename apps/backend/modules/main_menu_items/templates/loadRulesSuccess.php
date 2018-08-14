<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 15.08.2017
 * Time: 12:01
 */

$menu_item_rules_ids = array();
if ($menu_item_rules) {
    $menu_item_rules_ids = $menu_item_rules->getRawValue();
}

?>

<select id="sb_rules" multiple size="10">
<?php foreach (UserGroupTable::getInstance()->createQuery()->select()->orderBy('id ASC')->execute() as $rule): ?>
    <option value="<?php echo $rule->getId(); ?>" <?php echo in_array($rule->getId(), $menu_item_rules_ids) ? "selected" : ""; ?>><?php echo $rule->getName(); ?></option>
<?php endforeach; ?>
</select>
