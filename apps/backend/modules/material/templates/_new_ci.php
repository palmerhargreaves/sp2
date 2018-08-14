<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 18.04.2017
 * Time: 11:34
 */
?>

<input type="checkbox" name="material_new_ci_<?php echo $material->getId(); ?>" <?php echo $material->getNewCi() ? "checked" : ""; ?>
        class="material-new-ci"
        data-url="<?php echo url_for('@material_change_new_ci_status'); ?>"
        data-material-id="<?php echo $material->getId(); ?>"
        data-status="<?php echo $material->getNewCi() ? true : false; ?>"
/>
