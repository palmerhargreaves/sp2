<?php
/**
 * Created by PhpStorm.
 * User: Andrey
 * Date: 10.12.2015
 * Time: 14:13
 */

use_stylesheet('mailing.css'); ?>

<?php
$months = array(1 => 'Янв.', 2 => 'Фев.', 3 => 'Март', 4 => 'Апр.', 5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Авг.', 9 => 'Сен.', 10 => 'Окт.', 11 => 'Ноя.', 12 => 'Дек.');
$quaters = array(1 => "1 кв.", 2 => "2 кв.", 3 => "3 кв.", 4 => "4 кв.");
?>

<?php
$budQuater = array(1 => '1 квартал', 2 => '2 квартал', 3 => '3 квартал', 4 => '4 квартал');
$chBudYears = Utils::getYearsList(D::START_YEAR);
$roman = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV');
?>
<div class="mailing-wrapper">
    <form action="/mailing_plan_stat" method="post" enctype="multipart/form-data" id="dealer_stat_form">
        <h1 style="height: 38px;">
            <div id="chBudYears" class="modal-select-wrapper select input krik-select float-right"
                 style="height: 23px; padding-bottom: 1px; padding-right: 18px; width: 120px; margin-right: 10px;">
                <span class="select-value">Бюджет на <?= $year; ?> г.</span>

                <div class="ico"></div>
                <input type="hidden" name="year" id="year" value="<?php echo $year ?>">

                <div class="modal-input-error-icon error-icon"></div>
                <div class="error message"></div>
                <div class="modal-select-dropdown">
                    <?php
                    foreach ($chBudYears as $y):
                        $url = url_for("@mailing_stat?year=" . $y);
                        ?>
                        <div style='height:auto; padding: 7px;' class="modal-select-dropdown-item select-item"
                             data-year="<?= $y; ?>"
                             data-url="<?php echo $url ?>"><?= "Бюджет на " . $y . " г."; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            Статус загрузки e-mail адресов
        </h1>
        <h1 style="height: 38px;">

            <div class="search_dealer">
                <input id="dealer_name" class="search" name="dealer_name" type="search" value="<?= $dealer_name; ?>"
                       placeholder="Введите имя дилера">
                <input class="search_ico" type="image" src="../images/ico_search.png" style="margin-top: 2px;">
            </div>
            <div id="dealer_search_btn" class="modal-select-wrapper select input krik-select float-right"
                 style="height: 24px; padding-bottom: 1px; width: 60px; margin-right: 10px; float: left; text-align: center;padding-right: 14px;">
                <span class="select-value">Искать</span>
            </div>
            <div class="modal-select-wrapper select input krik-select float-right"
                 style="height: 24px; padding-bottom: 1px; width: 80px; margin-right: 10px; float: left; text-align: center;padding-right: 14px;">
                <a href="<?= url_for("@mailing_stat"); ?>" class="select-value"
                   style="text-decoration: none; color: #333;">Очистить</a>
            </div>


                <div class="modal-select-wrapper select input krik-select float-right"
                     style="height: 23px; padding-bottom: 1px; width: 100px; margin-right: 10px; float: left; text-align: center; padding-right: 14px;">
                    <label class="select-value" id="load_plan">Загрузить план</label>
                    <!--- <input id="importer_file" type="file" name="data_file" style="display: none;"/> -->
                </div>
           
            <div class="modal-select-wrapper select input krik-select float-right"
                 style="height: 24px; padding-bottom: 1px; width: 90px; margin-right: 10px; float: left; text-align: center;padding-right: 14px; background: #7CAED3;">
                <a href="<?= url_for("@mailing_stat?year=" . $year . "&export=xls"); ?>" class="select-value"
                   style="text-decoration: none; color: #fff;">Зкспорт в XLS</a>
            </div>
            <div class="modal-select-wrapper select input krik-select float-right"
                 style="height: 24px; padding-bottom: 1px; width: 60px; margin-right: 10px; float: left; text-align: center;padding-right: 14px;">
                <a href="<?= url_for("@mailing_plan"); ?>" target="_blank" class="select-value"
                   style="text-decoration: none; color: #000;">Проходы</a>
            </div>
            <div class="modal-select-wrapper select input krik-select float-right"
                 style="height: 24px; padding-bottom: 1px; width: 90px; margin-right: 10px; float: left; text-align: center;padding-right: 14px;">
                <a href="http://dm.vw-servicepool.ru/ext_file/plan_example.xlsx" id="" class="select-value"
                   style="text-decoration: none; color: #000;">Пример файла</a>
            </div>
        </h1>
        <div style="width:100%; margin-top: 16px;">
            <?php if ($report): ?>
                <table class="dealet_stat">
                    <tbody>
                    <!--шапка-->
                    <tr>
                        <td class="row_dark" width="5%">Номер</td>
                        <td class="head">Название дилера</td>
                        <td class="row_dark">Янв.</td>
                        <td class="row_dark">Фев.</td>
                        <td class="row_dark">Март.</td>
                        <td class="row_quater_dark">1кв.</td>
                        <td class="row_dark">Апр.</td>
                        <td class="row_dark">Май.</td>
                        <td class="row_dark">Июн.</td>
                        <td class="row_quater_dark">2кв.</td>
                        <td class="row_dark">Июл.</td>
                        <td class="row_dark">Авг.</td>
                        <td class="row_dark">Сент.</td>
                        <td class="row_quater_dark">3кв.</td>
                        <td class="row_dark">Окт.</td>
                        <td class="row_dark">Ноя.</td>
                        <td class="row_dark">Дек.</td>
                        <td class="row_quater_dark">4кв.</td>
                    </tr>
                    <!--Вывод результатов-->
                    <?php
                    $color_lite = True;
                    foreach ($report as $dealer_id => $item): ?>
                        <tr>
                            <?php if ($color_lite == False): ?>
                        <td class="row_dark">
                        <?php else: ?>
                            <td class="head_lite">
                                <?php endif; ?>
                                <?= mb_substr($dealer_id, -3, 3); ?>
                            </td>
                            <?php if ($color_lite == False): ?>
                            <td class="head">
                                <?php else: ?>
                            <td class="head_lite">
                                <?php endif; ?>
                                <a href="/mailing_dealer_stat?dealer_id=<?= $dealer_id; ?>"><?= $item['name']; ?></a>
                            </td>
                            <td class="row_lite"><?= $item[1]; ?>%</td>
                            <td class="row_lite"><?= $item[2]; ?>%</td>
                            <td class="row_lite"><?= $item[3]; ?>%</td>
                            <td class="row_lite"><?= $item['1qr']; ?>%</td>
                            <td class="row_lite"><?= $item[4]; ?>%</td>
                            <td class="row_lite"><?= $item[5]; ?>%</td>
                            <td class="row_lite"><?= $item[6]; ?>%</td>
                            <td class="row_lite"><?= $item['2qr']; ?>%</td>
                            <td class="row_lite"><?= $item[7]; ?>%</td>
                            <td class="row_lite"><?= $item[8]; ?>%</td>
                            <td class="row_lite"><?= $item[9]; ?>%</td>
                            <td class="row_lite"><?= $item['3qr']; ?>%</td>
                            <td class="row_lite"><?= $item[10]; ?>%</td>
                            <td class="row_lite"><?= $item[11]; ?>%</td>
                            <td class="row_lite"><?= $item[12]; ?>%</td>
                            <td class="row_lite"><?= $item['4qr']; ?>%</td>
                        </tr>
                        <?php $color_lite = !$color_lite; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="padding: 10px; font-weight: bold;">Извините, но по вашему запросу нет совпадений.</div>
            <?php endif; ?>
        </div>
    </form>
</div>

<div id="load_plan_modal" class="modal">
    <div class="modal-header">Загрузка плана</div>
    <div class="modal-close"></div>
    <form action="/mailing_plan_stat" method="post" enctype="multipart/form-data">
        <label for="month_plnan" style="margin-right: 10px;">Выберите месяц</label>
        <select name="plan_month" id="month_plnan" style="padding: 10px; background: none; border: 1px solid #c7c7c7; border-radius: 5px;">
            <?php foreach ($months as $k => $m): ?>
                <option value="<?= $k; ?>"><?= $m; ?></option>
            <?php endforeach;; ?>
        </select>
        <label class="select-value" for="importer_file" style="padding: 10px; margin-left: 5px; background: none; border: 1px solid #c7c7c7; border-radius: 5px;">Выбрать файл</label>
        <input id="importer_file" type="file" name="data_file" style="display: none;"/>
        <div class="modal-button-wrapper">
            <input id="change-button" type="submit" class="modal-button button" value="Загрузить">
        </div>
    </form>
</div>

<script type="text/javascript">
    $(function () {
        var form = $('#dealer_stat_form');
//        $('#importer_file').on('change', function () {
//            form.submit();
//        });

        $('.modal-select-dropdown-item').on('click', function () {
            $('#year').val($(this).data('year'));
            form.submit();
        });

        $('#dealer_name').on('change', function () {
            form.submit();
        });

        $('#dealer_search_btn').on('click', function () {
            form.submit();
        });

        $('#load_plan').click(function () {
            $('#load_plan_modal').krikmodal('show');
        });
    });
</script>
