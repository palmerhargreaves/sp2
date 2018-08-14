<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 15.08.2017
 * Time: 18:27
 */
?>

<select id="sb_child_departments">
    <?php foreach ($departments as $department): ?>
        <option value="<?php echo $department->getId(); ?>" <?php echo $department->getId() == $child_department_id ? "selected" : ""; ?>><?php echo $department->getName(); ?></option>
    <?php endforeach; ?>
</select>
