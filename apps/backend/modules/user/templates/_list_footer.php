<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 22.09.2016
 * Time: 12:34
 */
?>

<div class="modal hide fade full-info-modal" id="user-bind-dealers-modal" style="width: 550px; left: 47%;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Привязка дилеров</h3>
    </div>
    <div class="modal-body">
        <div class="panel-info-left fields-list" style="width: 35%; float:left;"></div>
        <div style="float:right; width: 62%; text-align: center;">
            <div class='inputs-fields panel-info-content'></div>
        </div>
    </div>
</div>

<div class="modal hide fade full-rules-modal" id="user-config-department" style="width: 500px; left: 45%;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Отдел пользователя</h3>
    </div>
    <div class="modal-body">
        <div style="float:left; width: 100%; text-align: center;">
            <div class='inputs-fields panel-info-content'>Нет данных.</div>
        </div>
    </div>
    <div class="modal-footer">
        <a href='#' class='btn action-user-department-save' data-field-type='information' style="float: left;">Принять</a>
        <a href="#" class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</a>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        window.users_binded_dealers = new BindDealers({
            modal: '#user-bind-dealers-modal',
            on_delete_binded_dealer_url: '<?php echo url_for("user_unbind_binded_dealer"); ?>',
            on_add_binded_dealer_url: '<?php echo url_for("user_bind_dealers"); ?>',
            on_load_user_binded_data: '<?php echo url_for("user_load_binded_dealers"); ?>',
            on_reload_user_binded_dealers_row: '<?php echo url_for("user_binded_dealers_reload_row"); ?>'
        }).start();


        window.user_department = new UserDepartment({
            modal: '#user-config-department',
            load_url: '<?php echo url_for("user_department_load_data"); ?>',
            accept_url: '<?php echo url_for("user_department_save"); ?>',
            on_load_child_departments_url: '<?php echo url_for("user_departments_load_data_by_parent"); ?>'
        }).start();
    });
</script>

