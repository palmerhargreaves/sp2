<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 15.08.2017
 * Time: 11:52
 */
?>
<div class="modal hide fade full-extra-rules-modal" id="full-extra-rules-modal" style="width: 500px; left: 45%;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Правила</h3>
    </div>
    <div class="modal-body">
        <div class="panel-info-left fields-list" style="width: 35%; float:left;"></div>
        <div style="float:right; width: 62%; text-align: center;">
            <div class='inputs-fields panel-info-content'>Нет данных.</div>
        </div>
    </div>
    <div class="modal-footer">
        <a href='#' class='btn action-rules-save' data-field-type='information'
           style="float: left; display: none;">Сохранить</a>
        <a href="#" class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</a>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        window.main_menu_items_rules = new ExtraRules({
            modal: '#full-extra-rules-modal',
            load_url: '<?php echo url_for("main_menu_items_rules"); ?>',
            accept_url: '<?php echo url_for("main_menu_items_rules_accept"); ?>',
        }).start();
    });
</script>
