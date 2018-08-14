<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 19.05.2017
 * Time: 12:51
 */
?>
    <ul class="sf_admin_actions">
        <li class="sf_admin_action_new">
            <a href="javascript:;" class="js-add-mime-type-to-category" data-id="<?php echo $agreement_model_categories->getId(); ?>">Добавить</a>
        </li>
    </ul>

<?php
$mime_count = $agreement_model_categories->getMimeTypes()->count();
if ($mime_count) {
    echo sprintf("Запрещено: %s", $mime_count);
}
