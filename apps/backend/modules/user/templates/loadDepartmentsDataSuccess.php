<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 15.08.2017
 * Time: 18:17
 */
?>

<select id="sb_parent_departments">
    <?php foreach ($departments as $department): ?>
        <option value="<?php echo $department->getId(); ?>" <?php echo $department->getId() == $parent_department_id ? "selected" : ""; ?>><?php echo $department->getName(); ?></option>
    <?php endforeach; ?>
</select>

<div id="container-for-child-departments">
    <?php if ($child_departments): ?>
        <?php include_partial('child_departments', array('departments' => $child_departments, 'child_department_id' => $child_department_id)); ?>
    <?php endif; ?>
</div>
