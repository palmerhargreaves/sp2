<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 08.11.2017
 * Time: 11:41
 */

?>

<div class="row-fluid">
    <div class="span12">
        <div class="well sidebar-nav">
            <div class="alert alert-<?php echo !$result ? "error" : "success"; ?>">
                <?php echo $result ? "Тип заявки успешно изменен." : "Ошибка изменения типа заявки."; ?>
            </div>
        </div>
    </div>
</div>

