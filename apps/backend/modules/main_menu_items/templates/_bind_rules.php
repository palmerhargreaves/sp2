<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 15.08.2017
 * Time: 11:51
 */
?>
<div class="modal hide fade full-rules-modal" id="full-rules-modal" style="width: 500px; left: 45%;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Правила</h3>
    </div>
    <div class="modal-body">
        <div style="float:left; width: 100%; text-align: center;">
            <div class='inputs-fields panel-info-content'>Нет данных.</div>
        </div>
    </div>
    <div class="modal-footer">
        <a href='#' class='btn action-rules-save' data-field-type='information' style="float: left;">Сохранить</a>
        <a href="#" class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</a>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        window.main_menu_items_rules = new Rules({
            modal: '#full-rules-modal',
            load_url: '<?php echo url_for("main_menu_items_rules"); ?>',
            accept_url: '<?php echo url_for("main_menu_items_rules_accept"); ?>',
        }).start();
    });
</script>
