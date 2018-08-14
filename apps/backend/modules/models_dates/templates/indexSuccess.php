<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    <li class="nav-header">
                        <span>Перенос заявок</span>
                        <a href="javascript:;" class="on-show-model-history-move-actions" style="float: right;">Просмотр истории</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <div class="alert alert-success">
                    Для поиска нескольких заявок необходимо добавить `,` между номерами заявок.
                </div>

                <form action="<?php echo url_for('find_model'); ?>">
                    <ul class="nav nav-list">
                        <li class="nav-header">Поиск заявок</li>
                        <li>
                            Номер заяв(ки, ок):<br/>
                            <textarea type="text" name="model_id" placeholder="Номер заяв(ки, ок)" cols="80" rows="4"
                                      value="" class="input" style="width: 297px;"></textarea>
                        </li>
                        <li>
                            Перенести в:
                            <select name="sbMoveType">
                                <option value="activity">Активность / Дата</option>
                                <option value="dealer">Дилер</option>
                                <option value="designer">Дизайнер</option>
                            </select>
                        </li>
                        <li>
                            <input type="submit" id="btDoFilterData" class="btn" style="margin-top: 15px;"
                                   value="Поиск"/>
                        </li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal hide fade history-models-move-modal" id="history-models-move-modal"
     style="width: 950px; left: 45%; top: 30%;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4>История</h4>
    </div>
    <div class="modal-body" style="max-height: 650px; ">
        <div class="modal-content-container" style="width: 100%; float:left;"></div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</a>
    </div>
</div>

<script>
    $(function() {
        $('.on-show-model-history-move-actions').click(function() {
            $.post('<?php echo url_for('@agreement_model_history_move'); ?>',
                {},
                function(result) {
                    $('.modal-content-container').html(result);
                    $('#history-models-move-modal').modal('show');
                }
            );
        });
    });

</script>
