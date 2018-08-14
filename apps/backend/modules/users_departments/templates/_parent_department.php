<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 12.08.2017
 * Time: 17:48
 */

if ($users_departments->getParentId() != 0) {
    echo $users_departments->getUserDepartment()->getName();
} else {
    echo '-';
}
