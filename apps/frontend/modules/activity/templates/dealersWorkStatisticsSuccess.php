<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 18.07.2017
 * Time: 14:43
 */

include_partial('agreement_activity_model_management/menu', array( 'active' => 'activities_dealers_work_statistics' ));

$current_q = D::getQuarter(time());
?>

<table class="dealers-table" style="z-index:9; width: 100%;">
    <thead>
    <tr>
        <td class="header" style="height: 185px; ">
            <h1>Выгрузка</h1>

            <?php $current_year = date('Y'); ?>
            <form action="" method="get">
                <select id="sb-years-list" class="">
                    <option value="">Выберите год</option>
                    <?php foreach (range($current_year - 5, $current_year) as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo $year == $current_year ? "selected" : ""; ?>><?php echo $year; ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="sb-quarters-list" class="">
                    <option value="">Выберите квартал</option>
                    <?php foreach ($quarters as $q): ?>
                        <option value="<?php echo $q; ?>" <?php echo $q == $current_q ? "selected" : ""; ?>><?php echo $q; ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="checkbox" id="ch-only-mandatory-activities" style="width: 15px;">
                <label for="ch-only-mandatory-activities">Обязательные активности</label>
            </form>

            <button class="btn-export-data btn btn-mini " data-unload-type="by-quarters"
                    style="margin-top: 10px; margin-right: 10px; float: left;">Подробная выгрузка
            </button>

            <button class="btn-export-data btn btn-mini " data-unload-type="by-year"
                    style="margin-top: 10px; float: left;">Квартальная выгрузка
            </button>

            <img id="export-efficiency-progress" src="/images/loader.gif"
                 style="display: none; margin-left: 10px; margin-top: 19px; float: left;"/>
        </td>
    </tr>
    </thead>

    <tbody>

    </tbody>
</table>
<br/>

<table class="dealers-table" style="z-index:9; width: 100%;">
    <thead>
    <tr>
        <td class="header" style="height: 185px; ">
            <h1>Выгрузка данных по сервисным акциям</h1>

            <form action="" method="get">
                <select id="sb-activities-list" class="">
                    <option value="">Выберите активность...</option>

                    <?php foreach (DealerServicesDialogsTable::getInstance()->createQuery()->orderBy('id DESC')->execute() as $service): ?>
                        <?php if ($service->getActivity()->getId()): ?>
                            <option value="<?php echo $service->getActivity()->getId(); ?>">
                                <?php echo sprintf('[%d] - %s', $service->getActivity()->getId(), $service->getActivity()->getName()); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </form>

            <button class="btn-export-dialog-services-data btn btn-mini " style="margin-top: 10px; float: left;">
                Выгрузить
            </button>
            <img id="export-efficiency-progress" src="/images/loader.gif"
                 style="display: none; margin-left: 10px; margin-top: 19px; float: left;"/>
        </td>
    </tr>
    </thead>

    <tbody>

    </tbody>
</table>
<br/>

<table class="dealers-table" style="z-index:9; width: 100%;">
    <thead>
    <tr>
        <td class="header" style="height: 185px; ">
            <h1>Выгрузка данных по ServiceClinic (выполнение по шагам)</h1>

            <form action="" method="get">
                <select id="sb-activities-steps-list" class="">
                    <option value="">Выберите активность...</option>

                    <?php $activities_ids = array_map(function ( $item ) {
                        return $item[ 'activity_id' ];
                    }, ActivityExtendedStatisticStepsTable::getInstance()->createQuery()->select('activity_id')->groupBy('activity_id')->execute(array(), Doctrine_Core::HYDRATE_ARRAY));
                    ?>

                    <?php foreach (ActivityTable::getInstance()->createQuery()->whereIn('id', $activities_ids)->execute() as $activity): ?>
                        <option value="<?php echo $activity->getId(); ?>">
                            <?php echo sprintf('[%d] - %s', $activity->getId(), $activity->getName()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select id="sb-activity-steps-list" style="display: none;"></select>

                <select id="sb-quarters-steps-list" class="">
                    <option value="">Все кварталы</option>
                    <?php foreach ($quarters as $q): ?>
                        <option value="<?php echo $q; ?>" <?php echo $q == $current_q ? "selected" : ""; ?>><?php echo $q; ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <button class="btn-export-activities-steps-data btn btn-mini " style="margin-top: 10px; float: left;">
                Выгрузить
            </button>
            <img id="export-statistics-data-by-steps-progress" src="/images/loader.gif"
                 style="display: none; margin-left: 10px; margin-top: 19px; float: left;"/>
        </td>
    </tr>
    </thead>

    <tbody>

    </tbody>
</table>

<script type="text/javascript">
    $(function () {
        $(document).on('click', '.btn-export-data', function () {
            var q = $('#sb-quarters-list').val(),
                year = $('#sb-years-list').val(),
                mandatory_activities = $('#ch-only-mandatory-activities').is(":checked") ? 1 : 0;

            if (q.length == 0) {
                window.messages.showError('Для продолжения выберите квартал.',);
                return;
            }

            window.messages.showInfo('Выгрузка данных согласно выбранного типа ...');

            $('.btn-export-data').attr('disabled', true);
            $('#export-efficiency-progress').fadeIn();

            $.post('<?php echo url_for('@activities_dealers_export_work_statistics'); ?>', {
                quarter: q,
                year: year,
                mandatory_activity: mandatory_activities,
                unload_type: $(this).data('unload-type')
            }, function (result) {
                if (result.success) {
                    window.messages.showSuccess('Выгрузка данных успешно завершена.');
                    window.location.href = result.file_url;
                } else {
                    window.messages.showError('Ошибка выгрузки данных.');
                }

                $('.btn-export-data').attr('disabled', false);
                $('#export-efficiency-progress').fadeOut();
            });
        });

        $(document).on('click', '.btn-export-dialog-services-data', function (event) {
            var element = $('#sb-activities-list');

            if (element.val().length == 0) {
                window.messages.showError('Для продолжения выберите активность.',);
                return;
            }

            window.messages.showInfo('Выгрузка данных согласно выбранным данным ...');

            $('.btn-export-data').attr('disabled', true);
            $('#export-efficiency-progress').fadeIn();

            $.post('<?php echo url_for('@activities_dealers_export_services_dialogs_statistics'); ?>', {
                activity: element.val()
            }, function (result) {
                if (result.success) {
                    window.messages.showSuccess('Выгрузка данных успешно завершена.');
                    window.location.href = result.file_url;
                } else {
                    window.messages.showError('Ошибка выгрузки данных.');
                }

                $('.btn-export-data').attr('disabled', false);
                $('#export-efficiency-progress').fadeOut();
            });
        });

        //Экспорт статистики привязанных к шагам заполнения полей
        $(document).on("change", '#sb-activities-steps-list', function (event) {
            $.post("<?php echo url_for('@activity_get_steps_list'); ?>", {
                activity_id: $(this).val()
            }, function (result) {
                var steps = result.steps;

                $("#sb-activity-steps-list").html('');
                $("#sb-activity-steps-list").append("<option value=''>Все шаги</option>");
                $.each(steps, function (ind, item) {
                    $("#sb-activity-steps-list").append("<option value='" + ind + "'>" + item + "</option>");
                });
                $("#sb-activity-steps-list").show();
            });
        });

        $(document).on('click', '.btn-export-activities-steps-data', function (event) {
            var element = $('#sb-activities-steps-list'),
                q = $('#sb-quarters-steps-list').val();

            if (element.val().length == 0) {
                window.messages.showError('Для продолжения выберите активность.',);
                return;
            }

            window.messages.showInfo('Выгрузка данных согласно выбранным данным ...');

            $('.btn-export-activities-steps-data').attr('disabled', true);
            $('#export-statistics-data-by-steps-progress').fadeIn();

            $.post('<?php echo url_for('@activities_dealers_export_steps_statistics'); ?>', {
                activity: element.val(),
                q: q,
                step: $("#sb-activity-steps-list").val()
            }, function (result) {
                if (result.success) {
                    window.messages.showSuccess('Выгрузка данных успешно завершена.');
                    window.location.href = result.file_url;
                } else {
                    window.messages.showError('Ошибка выгрузки данных.');
                }

                $('.btn-export-activities-steps-data').attr('disabled', false);
                $('#export-statistics-data-by-steps-progress').fadeOut();
            });
        });

        window.messages = new Messages({}).start();
    });
</script>
