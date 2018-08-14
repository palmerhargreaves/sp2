<?php
/**
 * Created by PhpStorm.
 * User: Andrey
 * Date: 10.12.2015
 * Time: 16:12
 */
$months = array(1 => 'Янв.', 2 => 'Фев.', 3 => 'Март', 4 => 'Апр.', 5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Авг.', 9 => 'Сен.', 10 => 'Окт.', 11 => 'Ноя.', 12 => 'Дек.');
$duplicate_emails = array();
$duplicate_background = '';
use_stylesheet('mailing.css'); ?>


<?php
$budQuater = array(1 => '1 квартал', 2 => '2 квартал', 3 => '3 квартал', 4 => '4 квартал');
$chBudYears = array(1 => date('Y'), 2 => date('Y') - 1, 3 => date('Y') - 2);
$roman = array(1, 2, 3, 4);
?>

<div class="mailing-wrapper">
    <div style="width: 100%;">
        <div style="display: inline-block; height: 38px; width: 61%">
            <div class="modal-select-wrapper select input krik-select float-right"
                 style="height: 24px; padding-bottom: 1px; width: 80px; margin-right: 10px; float: right; text-align: center;padding-right: 14px;">
                <span class="select-value">Очистить</span>
            </div>
            <div class="modal-select-wrapper select input krik-select float-right"
                 style="height: 24px; padding-bottom: 1px; width: 130px; margin-right: 10px; float: right; text-align: center;padding-right: 14px;">
                <span class="select-value" id="unload_mailing_plan">Выгрузка статитиски</span>
            </div>
            <div id="chBudQuater_stat" class="modal-select-wrapper select input krik-select float-right"
                 style="height: 23px; padding-bottom: 1px; padding-right: 18px; width: 70px; margin-right: 10px;">
                <span class="select-value"><?= $quarter; ?> квартал</span>
                <div class="ico"></div>
                <input type="hidden" name="quater" value="<?php echo $quater ?>">
                <div class="modal-input-error-icon error-icon"></div>
                <div class="error message"></div>
                <div class="modal-select-dropdown">
                    <?php
                    foreach ($roman as $quater):
                        $url = url_for("@mailing_dealer?dealer_id=" . $dealer_id . "&quater=" . $quater . '&year=' . $year);
                        ?>
                        <div style='height:auto; padding: 7px;' class="modal-select-dropdown-item select-item"
                             data-url="<?= url_for("@mailing_dealer?dealer_id=" . $dealer_id . "&quarter=" . $quater . '&year=' . $year); ?>"><?php echo $quater . " квартал"; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="chBudYears" class="modal-select-wrapper select input krik-select float-right"
                 style="height: 23px; padding-bottom: 1px; padding-right: 18px; width: 120px; margin-right: 10px;">
                <span class="select-value"><?= $year; ?></span>
                <div class="ico"></div>
                <input type="hidden" name="year" value="<?php echo $year ?>">
                <div class="modal-input-error-icon error-icon"></div>
                <div class="error message"></div>
                <div class="modal-select-dropdown">
                    <?php foreach ($chBudYears as $b_year): ?>
                        <div style='height:auto; padding: 7px;' class="modal-select-dropdown-item select-item"
                             data-url="<?= url_for("@mailing_dealer?dealer_id=" . $dealer_id . "&quater=" . $quater . '&year=' . $b_year); ?>"><?php echo $b_year . " г."; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!--            <div style="display: inline-block; text-align: left; float: left; font-weight: bold; font-size: 16px;width: auto; padding-top: 8px;">-->
            <!--                Статус загрузки e-mail адресов-->
            <!--            </div>-->
        </div>
        <div style="height: 38px; width: 39%; float: left;">
            <div style="font-weight: normal; text-align: left; ">
                Дилерский центр: "<?= $dealer->getName(); ?>"
            </div>
            <div
                style="display: inline-block; text-align: left; float: left; font-weight: bold; font-size: 16px;width: auto; padding-top: 8px;">
                Статус загрузки e-mail адресов
            </div>
        </div>

        <?php include_component('mailing', 'mailingPanel', array('display_filter' => false)); ?>

        <div style="width:100%; margin-top: 0px;">
            <table class="dealet_stat">
                <tbody>
                <!--шапка-->
                <tr>
                    <td class="head" style="width: 20px;">№</td>
                    <td class="head" style="width: 280px;">Клиент</td>
                    <td class="head" style="width: 60px;">Пол</td>
                    <td class="row_dark" style="width: 126px; text-align: left; padding-left: 14px;">Телефон</td>
                    <td class="row_dark" style="width: 126px; text-align: left; padding-left: 14px;">Vin номер</td>
                    <td class="row_dark" style="text-align: left; padding-left: 14px;">e-mail</td>
                    <td class="row_dark" style="width: 130px;">Дата посещения</td>
                    <td class="row_dark" style="width: 130px;">Дата выгрузки</td>
                    <td class="row_dark" style="width: 130px;">Дата загрузки</td>
                </tr>
                <?php sfProjectConfiguration::getActive()->loadHelpers('Date'); ?>
                <?php foreach ($mailings as $key => $item): ?>
                    <tr>
                        <td class="<?= $color_lite ? 'head_lite' : 'head'; ?>"
                            style="width: 20px; font-weight: normal"><?= $key + 1; ?></td>
                        <td class="<?= $color_lite ? 'head_lite' : 'head'; ?>"
                            style="width: 280px; font-weight: normal"><?= $item->getFirstName() . ' ' . $item->getLastName(); ?></td>
                        <td class="<?= $color_lite ? 'row_lite' : 'row_dark'; ?>"
                            style="width: 126px; text-align: left; padding-left: 14px; font-weight: normal"><?= $item->getGender(); ?></td>
                        <td class="<?= $color_lite ? 'row_lite' : 'row_dark'; ?>"
                            style="width: 126px; text-align: left; padding-left: 14px; font-weight: normal"><?= $item->getPhone(); ?></td>
                        <td class="<?= $color_lite ? 'row_lite' : 'row_dark'; ?>"><?= $item->getVin(); ?></td>
                        <td <?php if (in_array($item->getEmail(), $duplicate_emails)): ?>
                            <?php $duplicate_background = ' background-color: #E4FF91; '; ?>
                        <?php else: ?>
                            class="<?= $color_lite ? 'row_lite' : 'row_dark'; ?>"
                        <?php endif; ?>
                            style="text-align: left; padding-left: 14px; font-weight: normal; <?= $duplicate_background; ?>"><?= $item['email']; ?>
                        </td>
                        <td class="<?= $color_lite ? 'row_lite' : 'row_dark'; ?>"
                            style="width: 130px; font-weight: normal"><?= $item->getLastVisitDate(); ?></td>
                        <td class="<?= $color_lite ? 'row_lite' : 'row_dark'; ?>"
                            style="width: 130px; font-weight: normal"><?= $item->getLastUploadData(); ?></td>
                        <td class="<?= $color_lite ? 'row_lite' : 'row_dark'; ?>"
                            style="width: 130px; font-weight: normal"><?= format_date($item->getAddedDate(), 'd MMMM yyyy', 'ru'); ?></td>
                        <?php $color_lite = $color_lite ? false : true; ?>
                        <?php $duplicate_emails[] = $item['email'];
                        $duplicate_background = ''; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div id="unload_mailing_plan_modal" class="modal">
    <div class="modal-header">Вырузить данные</div>
    <div class="modal-close"></div>
    <form action="/mailing_dealer_stat">
        <label for="month_plnan" style="margin-right: 10px;">Выберите месяц</label>
        <select name="plan_month" id="month_plnan" style="padding: 10px; background: none; border: 1px solid #c7c7c7; border-radius: 5px;">
            <?php foreach ($months as $k => $m): ?>
                <option value="<?= $k; ?>"><?= $m; ?></option>
            <?php endforeach;; ?>
        </select>
        <input type="hidden" name="dealer_id" value="<?= $dealer_id; ?>" >
        <input type="hidden" name="export_plan" value="1" >
        <input type="hidden" name="quarter" value="<?= $quarter; ?>">
        <input type="hidden" name="year" value="<?= $year; ?>">
        <div class="modal-button-wrapper">
            <input id="change-button" type="submit" class="modal-button button" value="Выгрузить данные">
        </div>
    </form>
</div>
<script type="text/javascript">
    $(function () {
        $(document).on("click", "#chBudQuater_stat .select-item, #chBudYears .select-item", function () {
            location.href = $(this).data('url');
        });
        $('#unload_mailing_plan').click(function () {
            $('#unload_mailing_plan_modal').krikmodal('show');
        });
    });
</script>
	
	

