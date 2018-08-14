<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    <li class="nav-header">Статистика по скачиванию материалов</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="activity-statistic-data" class="row-fluid" style="display: none;"></div>
    <!--/row-->
</div>

<?php if (isset($materials)): ?>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="well sidebar-nav">
                    <ul class="nav nav-list">
                        <li class="nav-header">Фильтр:</li>
                    </ul>

                    <form class="form-horizontal" id='frmFilterData'
                          action='<?php echo url_for('material/downloads'); ?>'
                          method='post'>
                        <div class="control-group">
                            <label class="control-label" for="start_date" style="width: 100px;">Дата с</label>
                            <div class="controls" style="margin-left: 130px;">
                                <input type='text' name='start_date' id="start_date" class='date'
                                       value='<?php echo $startDateFilter; ?>'>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="end_date" style="width: 100px;">Дата до</label>
                            <div class="controls" style="margin-left: 130px;">
                                <input type='text' name='end_date' id="end_date" class='date'
                                       value='<?php echo $endDateFilter; ?>'>
                            </div>
                        </div>

                        <div class="control-group">
                            <div class="controls">
                                <input type='button' class='btn' style='float: left; margin-right: 10px;'
                                       value='Очистить'
                                       data-url='<?php echo url_for('material_clear_download_filters'); ?>'>
                                <input type='submit' class='btn' style='float: left; margin-right: 10px;'
                                       value='Фильтр'>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="well">
                    <div style='display: block; margin-bottom: 10px; padding: 5px;'>
                        <ul class="nav nav-list">
                            <li class="nav-header">Список загруженных материалов за выбранный период:</li>
                        </ul>

                        <table class="table table-striped table-bordered table-checks table-downloads-materails"
                               cellspacing="0">
                            <thead>
                            <tr>
                                <th>№ Активности</th>
                                <th>Активности</th>
                                <th>Материал</th>
                                <th>Формат файла</th>
                                <th>Количество скачиваний</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($materials as $key => $item): ?>
                                <?php $material = $item['material']; ?>
                                <tr>
                                    <td class="span3">
                                        <?php
                                        echo implode('<br />', array_map(function ($item) {
                                            return $item['id'];
                                        },
                                            $item['activities']->getRawValue()));
                                        ?>
                                    </td>
                                    <td class="span3">
                                        <?php
                                        echo implode('<br />', array_map(function ($item) {
                                            return $item['name'];
                                        },
                                            $item['activities']->getRawValue()));
                                        ?>
                                    </td>
                                    <td class="span4"><?php echo $material->getName(); ?></td>
                                    <td class="span2">
                                        <?php echo pathinfo($item['material_source']['file'], PATHINFO_EXTENSION); ?>
                                    </td>
                                    <td class="span1">
                                        <span><?php echo $item['count'] ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<script>
    $(function () {
        $('#frmFilterData input.date').datepicker({dateFormat: "yy-mm-dd"});

        var table = $('.table-downloads-materails').dataTable({
            "bJQueryUI": false,
            "bAutoWidth": false,
            "bPaginate": true,
            "bLengthChange": false,
            "bInfo": false,
            "bDestroy": true,
            "iDisplayLength": 25,
            "sPaginationType": "full_numbers",
            "sDom": '<"datatable-header"flp>t<"datatable-footer"ip>',
            "oLanguage": {
                "sSearch": "<span>Фильтр:</span> _INPUT_",
                "sLengthMenu": "<span>Отоброжать по:</span> _MENU_",
                "oPaginate": {"sFirst": "Начало", "sLast": "Посл", "sNext": ">", "sPrevious": "<"}
            },
            "aoColumnDefs": [
                {"bSortable": false, "aTargets": []}
            ]
        });

        table.fnSort([[0, 'desc']]);

        $('input[type=button]').click(function (e) {
            e.stopPropagation();

            $('#frmFilterData').attr('action', $(this).data('url')).submit();
        });
    });
</script>
