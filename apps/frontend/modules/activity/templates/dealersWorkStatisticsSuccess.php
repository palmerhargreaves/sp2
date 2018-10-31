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
            <h1>Выгрузка статуса выполнения обязательных требований</h1>

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
            <h1>Выгрузка данных (участие в активности)</h1>

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
            <h1>Выгрузка ServiceClinic (Статистика (дилеры), по шагам)</h1>

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
<br/>

<table style="z-index:9; width: 100%;">
    <thead>
    <tr>
        <td class="header">
            <h1>Сводная маркетинговая выгрузка</h1>

            <form action="" method="get">

                <div class="d-grid" style="padding-left: 1px; padding-top: 15px;">
                    <div class="d-row">
                        <div class="d-col d-col_sm_8 d-col_md_8 d-col_lg_8">
                            <select id="sb-consolidated-information-activities" class="form-control" multiple>
                                <?php foreach (ActivityTable::getInstance()->createQuery()->where('finished = ?', false)->orderBy('id DESC')->execute() as $activity): ?>
                                    <option value="<?php echo $activity->getId(); ?>">
                                        <?php echo sprintf('[%d] - %s', $activity->getId(), $activity->getName()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-col d-col_sm_3 d-col_md_3 d-col_lg_3">
                            <select id="sb-consolidated-information-quarters" class="form-control" multiple>
                                <?php foreach ($quarters as $q): ?>
                                    <option value="<?php echo $q; ?>"><?php echo $q; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-row" style="margin-top: 15px;">
                        <div class="d-col d-col_sm_2 d-col_md_2 d-col_lg_2">
                            <select id="sb-consolidated-information-regional-manager"
                                    data-url="<?php echo url_for('@on_consolidated_information_dealer_change_manager'); ?>"
                                    style="width: 168px; border: 1px solid #d3d3d3; border-radius: 3px; height: 24px; padding: 0 0 0 10px; font-size: 11px;">
                                <option value="999">Все менеджеры</option>
                                <?php
                                    //Сортируем список региональных менеджер по количеству привязанных дилеров
                                    $regional_managers = UserTable::getInstance()
                                        ->createQuery('u')
                                        ->innerJoin('u.Group g')
                                        ->where('g.id = ?', User::USER_GROUP_REGIONAL_MANAGER)
                                        ->orderBy('u.name ASC')
                                        ->execute();
                                    $sorted_managers = array();
                                    foreach ($regional_managers as $manager) {
                                        $sorted_managers[DealerTable::getInstance()->createQuery()->where('regional_manager_id = ?', $manager->getNaturalPersonId())->count()] = array('name' => $manager->selectName(), 'id' => $manager->getNaturalPersonId());
                                    }

                                    krsort($sorted_managers);
                                ?>
                                <?php foreach ($sorted_managers as $dealers_count => $data): ?>
                                    <option value="<?php echo $data['id']; ?>">
                                        <?php echo sprintf('[%d] %s', $dealers_count, $data['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php
                        $dealers_by_type_list = array();
                        foreach (DealerTable::getInstance()->createQuery()->where('status = ?', true)->andWhere('dealer_type = ? or dealer_type = ?', array(Dealer::TYPE_PKW, Dealer::TYPE_NFZ_PKW))->orderBy('number ASC')->execute() as $dealer) {
                            $dealers_by_type_list[$dealer->getDealerTypeLabel()][] = $dealer;
                        }

                        ?>
                        <div class="d-col d-col_sm_9 d-col_md_9 d-col_lg_9">
                            <select id="sb-consolidated-information-dealers" multiple>
                                <?php foreach ($dealers_by_type_list as $label => $dealers): ?>
                                    <optgroup label="<?php echo $label; ?>">
                                        <?php foreach ($dealers as $dealer): ?>
                                            <option value="<?php echo $dealer->getId(); ?>"><?php echo $dealer->getNameAndNumber(); ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-row">
                        <div class="d-col d-col_sm12 d-col_md_12 d-col_lg_12">
                            <button class="btn-export-activities-steps-data btn btn-mini " style="margin-top: 10px; float: left;">
                                Выгрузить
                            </button>
                            <img id="export-statistics-data-by-steps-progress" src="/images/loader.gif"
                                 style="display: none; margin-left: 10px; margin-top: 19px; float: left;"/>
                        </div>
                    </div>
                </div>
            </form>


        </td>
    </tr>
    </thead>

    <tbody>

    </tbody>
</table>

<script type="text/javascript">
    $(function () {

        new DealerConsolidatedInformation({}).start();

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
