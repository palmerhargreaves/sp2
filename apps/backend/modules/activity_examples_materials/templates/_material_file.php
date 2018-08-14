<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 30.09.2016
 * Time: 9:26
 */
?>

<a href="<?php echo url_for('@activity_examples_download_file?file_id='.$activity_examples_materials->getId()); ?>" target="_blank">
    <?php echo $activity_examples_materials->getMaterialFile(); ?>
</a>