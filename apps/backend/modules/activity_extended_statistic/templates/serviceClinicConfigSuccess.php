<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 11.01.2017
 * Time: 15:06
 */
?>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    <li class="nav-header">
                        <span>Копирование статистики Service Clinic</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <div id="msg-container" class="alert alert-error" style="display: none;"></div>


                <ul class="nav nav-list">
                    <li class="nav-header">Параметры копирования</li>
                    <li>
                        Из активности:<br/>
                        <select id='sbFromActivity' name='sbFromActivity'>
                            <option value='-1'>Выберите активность ...</option>
                            <?php foreach ($activities as $act): ?>
                                <option
                                    value='<?php echo $act->getId(); ?>'><?php echo sprintf('[%s] - %s', $act->getId(), $act->getName()); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </li>
                </ul>

                <div id="container-service-clinic-copy-data"></div>

                <div id="msg-info-container" class="alert alert-info" style="display: none; margin-top: 15px;"></div>
            </div>
        </div>
    </div>
</div>

<script>
    new ServiceClinicCopy({
        make_copy_url: '<?php echo url_for('@activity_service_clinic_make_copy'); ?>',
        on_get_activity_data_url: '<?php echo url_for('@activity_service_clinic_copy_get_data'); ?>'
    }).start();
</script>
