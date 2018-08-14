<?php
    $form_actions = array(
        models_datesActions::MOVE_TYPE_ACTIVITIES => url_for('model_date'),
        models_datesActions::MOVE_TYPE_DEALERS => url_for('model_move_to_dealer'),
        models_datesActions::MOVE_TYPE_DESIGNER => url_for('model_move_to_designer'),
    );
?>
<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    <li class="nav-header">Результат поиска, найдено - [<?php echo count($models); ?>]</li>
                </ul>
            </div>
        </div>
    </div>

    <?php if ($models): ?>
        <div class="row-fluid">
            <div class="span12">
                <div class="well sidebar-nav">
                    <div class="alert alert-warning">
                        Список заявок
                    </div>
                    <table class="table table-hover table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style='width: 1%;'>#</th>
                            <th>№ Заявки</th>
                            <th>Название</th>
                            <th>Активность</th>
                            <th>Дилер</th>
                            <th>Дизайнер</th>
                            <th>История</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php
                        $ind = 1;
                        foreach ($models as $model):
                            ?>
                            <tr>
                                <td><input type="checkbox" class="model-index"
                                           data-model-id="<?php echo $model->getId(); ?>"/></td>
                                <td><?php echo $model->getId(); ?></td>
                                <td><?php echo $model->getName(); ?></td>
                                <td><?php echo $model->getActivity()->getName(); ?></td>
                                <td><?php echo $model->getDealer()->getName(); ?></td>
                                <td>
                                    <?php
                                        $check_by_designer = AgreementModelCheckByDesignerTable::getInstance()->createQuery()->where('model_id = ?', $model->getId())->fetchOne();
                                        if ($check_by_designer) {
                                            echo sprintf('%s %s', $check_by_designer->getUser()->getSurname(), $check_by_designer->getUser()->getName());
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $log_entry = LogEntryTable::getInstance()->createQuery()
                                        ->where('object_id = ?', $model->getId())
                                        ->andWhereIn('action', array('model_move_to_activity_date', 'model_move_to_dealer', 'model_move_to_designer'))
                                        ->execute();

                                    if (count($log_entry) > 0) {
                                        echo "<a href='javascript:;' class='on-show-model-history-move-actions' data-model-id='".$model->getId()."'>История</a>";
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span12">
                <div class="well sidebar-nav">
                    <div class="alert alert-warning">
                        Перенос заяв(ки,ок) в:
                    </div>

                    <div class="alert container-move-result" style="display: none;"></div>

                    <form
                        action="<?php echo $form_actions[$moveType]; ?>"
                        method="get" class="form-inline" id="model-dates-form">
                        <?php if ($moveType == models_datesActions::MOVE_TYPE_ACTIVITIES): ?>
                            <select name='sbActivity'>
                                <option value='-1'>Выберите активность ...</option>
                                <?php foreach ($activities as $activity): ?>
                                    <option
                                        value='<?php echo $activity->getId(); ?>'><?php echo sprintf('[%s] %s', $activity->getId(), $activity->getName()); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="model_date" placeholder="дата выполнения заявки" value=""
                                   class="input date">
                        <?php elseif ($moveType == models_datesActions::MOVE_TYPE_DEALERS): ?>
                            <select name='sbDealer'>
                                <option value='-1'>Выберите дилера ...</option>
                                <?php foreach ($dealers as $dealer): ?>
                                    <option
                                        value='<?php echo $dealer->getId(); ?>'><?php echo sprintf('[%s] %s', $dealer->getNumber(), $dealer->getName()); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ($moveType == models_datesActions::MOVE_TYPE_DESIGNER): ?>
                            <select name='sbDesigner'>
                                <option value='-1'>Выберите дизайнера ...</option>
                                <?php foreach ($designers as $designer): ?>
                                    <option
                                            value='<?php echo $designer->getId(); ?>'><?php echo sprintf('%s %s', $designer->getSurname(), $designer->getName()); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                        <input type="submit" value="Изменить" class="btn">
                        <input type="hidden" name="moveType" value="<?php echo $moveType; ?>"/>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
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

<script type="text/javascript">
    $('#model-dates-form input.date').datepicker({dateFormat: "dd-mm-yy"});

    $('.on-show-model-history-move-actions').click(function() {
        $.post('<?php echo url_for('@agreement_model_history_move'); ?>',
            {
                model_id: $(this).data('model-id')
            },
            function(result) {
                $('.modal-content-container').html(result);
                $('#history-models-move-modal').modal('show');
            }
        );
    });

    $("input[type=submit]").click(function (e) {
        e.preventDefault();

        var moveType = $("input[name=moveType]").val(), moveTo = '', modelsIds = [], data = {}, valid = true, $bt = $(this);

        if (moveType == 'activity') {
            data.moveTo = $('select[name=sbActivity]').val();
            data.modelToDate = $('input[name=model_date]').val();

            if (data.modelToDate.length == 0 && data.moveTo == -1) {
                alert('Выбрите активность или дату для продолжения.');
                valid = false;
            }
        } else if (moveType == 'dealer') {
            data.moveTo = $('select[name=sbDealer]').val();
            if (data.moveTo == -1) {
                valid = false;

                alert('Выберите дилера для продолжения.');
            }
        } else if (moveType == 'designer') {
            data.moveTo = $('select[name=sbDesigner]').val();
            if (data.moveTo == -1) {
                valid = false;

                alert('Выберите дизайнера для продолжения.');
            }
        }

        if (!valid) {
            return;
        }

        data.modelsIds = getCheckedModels();
        if (data.modelsIds.length == 0) {
            alert('Для продолжения необходимо выбрать заявк(у,и)');
        } else {
            $bt.fadeOut();
            $.post($(this).parent('form').attr('action'),
                data,
                function (result) {
                    var res = JSON.parse(result);

                    $bt.fadeIn();
                    if (res.success) {
                        $('.container-move-result').addClass('alert-info').html(res.msg).fadeIn();
                    }
                    else {
                        $('.container-move-result').addClass('alert-error').html(res.msg).fadeIn();
                    }
                });
        }
    });

    var getCheckedModels = function () {
        var result = [];

        $('.model-index').each(function (ind, el) {
            if ($(el).is(':checked')) {
                result.push($(el).data('model-id'));
            }
        });

        return result
    }
</script>
