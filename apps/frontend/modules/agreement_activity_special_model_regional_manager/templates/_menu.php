<div id="submenu">
    <?php if ($sf_user->isManager() || $sf_user->isRegionalManager() || $sf_user->isImporter()) : ?>
        <ul>
            <?php if ($sf_user->isManager() || $sf_user->isImporter()): ?>
            <li><a<?php if ($active == 'agreement') echo ' class="active"' ?>
                    href="<?php echo url_for('@agreement_module_management_models') ?>">Согласование</a></li>
            <?php endif; ?>
            <li><a<?php if ($active == 'activities') echo ' class="active"' ?>
                    href="<?php echo url_for('@agreement_module_activities') ?>">Активности</a></li>
            <li><a<?php if ($active == 'dealers') echo ' class="active"' ?>
                    href="<?php echo url_for('@agreement_module_dealers') ?>">Дилеры</a></li>
            <li><a<?php if ($active == 'activities_status') echo ' class="active"' ?>
                    href="<?php echo url_for('@agreement_module_activities_status') ?>">Статус выполнения
                    активностей</a></li>
            <li><a<?php if ($active == 'activities_statistic') echo ' class="active"' ?>
                    href="<?php echo url_for('@activity_statistic_info') ?>">Статистика по активностям</a></li>
            <li><a<?php if ($active == 'activities_efficiency_info') echo ' class="active"' ?>
                    href="<?php echo url_for('@activities_efficiency_info') ?>">Эффективность</a></li>
            <li><a<?php if ($active == 'activities_dealers_work_statistics') echo ' class="active"' ?>
                        href="<?php echo url_for('@activities_dealers_work_statistics') ?>">Выгрузка</a></li>
        </ul>

        <?php
        $show = false;
        if (!empty($budYears) && $show):
            ?>
            <div id="chBudYears" class="modal-select-wrapper select input krik-select float-right"
                 style="height: 20px; padding-bottom: 5px; padding-right: 18px; width: 120px; margin-right: 10px;">
                <span class="select-value">Выберите год</span>

                <div class="ico"></div>
                <input type="hidden" name="year" value="<?php echo $year ?>">

                <div class="modal-input-error-icon error-icon"></div>
                <div class="error message"></div>
                <div class="modal-select-dropdown">
                    <?php

                    foreach ($budYears as $year):
                        $url_temp = url_for("@" . $url . "?year=" . $year);
                        ?>
                        <div style='height:auto; padding: 7px;' class="modal-select-dropdown-item select-item"
                             data-url="<?php echo $url_temp ?>"><?php echo "Бюджет на " . $year . " г."; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div style="clear: both;"></div>
    <?php endif; ?>
</div>

<script>
    $(function () {
        $(document).on("click", "#chBudYears .select-item", function () {
            location.href = $(this).data('url');
        });
    });
</script>
