<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 13.08.2017
 * Time: 11:31
 */

if ($users_departments->getParentId() != 0) {
    echo "<small>".$users_departments->getUser()->count()."</small>";
} else {
    $child_departments = $users_departments->getChilds();
    $total_count = 0;

    foreach ($child_departments as $department) {
        $total_count += $department->getUser()->count();
    }

    echo "<strong>".$total_count."</strong>";
}
