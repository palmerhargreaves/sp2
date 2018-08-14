<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 05.01.2017
 * Time: 13:05
 */

if ($file):
?>
<div
    class="fav-item-remove model-report-remove-from-favorites report-favorites-add-file-<?php echo $file->getId(); ?>"
    data-report-id='<?php echo $report->getId(); ?>'
    data-file-id='<?php echo $file->getId(); ?>'
    data-type-id='<?php echo $model_type_id; ?>'
    title="Удалить" />
<?php else: ?>

<?php endif; ?>
