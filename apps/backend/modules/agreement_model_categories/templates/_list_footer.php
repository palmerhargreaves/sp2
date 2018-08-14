<div class="modal hide fade dialog-users-limits-modal" id="dialog-category-mime-types-modal" style="width: 700px; left: 45%; top: 30%;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4>Выберите форматы файлов запрещенных для загрузки</h4>
    </div>
    <div class="modal-body" style="max-height: 650px; ">
        <div class="modal-content-container" style="width: 100%; float:left;"></div>
    </div>
    <div class="modal-footer">

    </div>
</div>

<script>
    $(function() {
        new AgreementModelCategoryFields({
            on_save_category_fields: '<?php echo url_for('@agreement_model_category_field_save'); ?>'
        }).start();

        new AgreementCategoryMimeTypes({
            js_add_mime_type_action: '.js-add-mime-type-to-category',
            on_get_mime_types_list: '<?php echo url_for('@mime_types_list'); ?>',
            on_mime_type_check: '<?php echo url_for('@mime_type_check'); ?>',
            dialog: '#dialog-category-mime-types-modal',
            dialog_content: '.modal-content-container',
            js_mime_type_check: '.js-ch-category-mime-type',
        }).start();
    });

</script>
