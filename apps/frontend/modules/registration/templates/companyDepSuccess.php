<?php
    $def_value = UsersDepartmentsTable::getInstance()->createQuery()->where('parent_id = ?', $companyDep)->orderBy('id ASC')->fetchOne();
?>
<div class="modal-select-wrapper select krik-select company-post-krik-select">
    <span class="select-value"><?php echo $def_value->getName(); ?></span>
    <div class="ico"></div>
    <input type="hidden" name="post" value="<?php echo $def_value->getId(); ?>">

    <div class="modal-select-dropdown">
        <?php foreach (UsersDepartmentsTable::getDepartments($companyDep)->execute() as $department): ?>
            <div class="modal-select-dropdown-item select-item" data-value="<?php echo $department->getId(); ?>"><?php echo $department->getName(); ?></div>
        <?php endforeach; ?>
    </div>
</div>
