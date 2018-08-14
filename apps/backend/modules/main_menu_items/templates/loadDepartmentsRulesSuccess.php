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

<select id="sb_rules" multiple size="10" style="width: 350px;">
    <?php foreach (UsersDepartmentsTable::getInstance()->createQuery()->select()->where('parent_id = ?', 0)->orderBy('id ASC')->execute() as $department): ?>
        <option value="<?php echo $department->getId(); ?>" <?php echo in_array($department->getId(), $menu_item_rules_ids) ? "selected" : ""; ?>><?php echo $department->getName(); ?></option>
    <?php endforeach; ?>
</select>
