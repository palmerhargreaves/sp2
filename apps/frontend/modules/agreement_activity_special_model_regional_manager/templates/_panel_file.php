<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 22.09.2015
 * Time: 11:35
 */
?>
<div class="file">
    <div class="modal-file-wrapper input modal-file-model-report">
        <div class="control" style="border: 2px dashed rgb(223, 220, 220); padding: 5px; border-radius: 3px; width: 230px; height: 70px;">
            <div style="font-size: 11px; text-align: center;">Перетащите сюда файлы или нажмите на кнопку для загрузки</div>
            <div class="green button modal-zoom-button modal-form-button model-main-file" style="margin-left: 42%; margin-top: 10px;"></div>

            <input type="file" data-name="agreement_comments_file"
                   name="agreement_comments_file[]" multiple size="1"
                   data-container-cls="model-form-selected-files-to-upload">
        </div>
        <div class="file-name"></div>
        <div class="cl"></div>
        <div class="modal-input-error-icon error-icon"></div>
        <div class="error message"></div>
        <!--<div class="modal-form-requirements">Допустимый формат: jpg</div>-->
    </div>

    <div class="modal-form-uploaded-file"></div>
</div>
