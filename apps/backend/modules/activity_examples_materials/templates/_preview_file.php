<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 07.09.2016
 * Time: 10:39
 */

$preview_file = $activity_examples_materials->getPreviewFile();
if (!empty($preview_file)):
    ?>
    <img style="width: 32px;"
         src="/uploads/<?php echo ActivityExamplesMaterials::FILE_PREVIEW_PATH . $activity_examples_materials->getPreviewFile(); ?>"/>
<?php endif; ?>