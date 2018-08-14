<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 19.05.2017
 * Time: 13:17
 */
?>

<div class="row-fluid">
    <table class="table table-hover table-bordered table-striped">
        <thead>
        <tr>
            <th style='width: 1%;'>#</th>
            <th>Тип</th>
            <th>Расширение файла</th>
            <th>Описание</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($mime_types as $type_item): ?>
            <?php $item = $type_item['mime_type']; ?>
            <tr>
                <td>
                    <input type="checkbox" class="js-ch-category-mime-type" <?php echo $type_item['checked'] ? "checked" : "";?>
                           data-mime-id="<?php echo $item->getId(); ?>"
                           data-category-id="<?php echo $category_id; ?>" />
                </td>
                <td><?php echo $item->getName(); ?></td>
                <td><?php echo $item->getExtension(); ?></td>
                <td><?php echo $item->getDescription(); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
