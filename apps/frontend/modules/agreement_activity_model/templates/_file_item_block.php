<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 18.07.2017
 * Time: 10:14
 */

$file_link = $sf_user->getAttribute('editor_link');

$file_ext = F::getFileExt($file_link);
$file_name = F::getFileName($file_link);
$is_image = Utils::isImage($file_link);
?>
<div>
    <span class="d-popup-uploaded-file <?php echo !$is_image ? 'odd ' . $file_ext : ''; ?>" data-delete="false">
        <?php if ($is_image): ?>
            <i><b><img src="<?php echo $file_link; ?>" /></b></i>
            <?php else: ?>
            <i></i>
        <?php endif; ?>

        <strong><a href="<?php echo $file_link; ?>" target="_blank"><?php echo $file_name; ?></a></strong>
        <em><?php echo F::getExternalFileSizeHelper($file_link)->getSmartSizeExternal(); ?></em>
    </span>
</div>
