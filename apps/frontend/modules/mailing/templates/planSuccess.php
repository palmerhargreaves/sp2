<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 04.08.16
 * Time: 12:46
 */
use_stylesheet('mailing.css');
$months = array(1 => 'Янв.', 2 => 'Фев.', 3 => 'Мар.', 4 => 'Апр.', 5 => 'Май', 6 => 'Июн.', 7 => 'Июл.', 8 => 'Авг.', 9 => 'Сен.', 10 => 'Окт.', 11 => 'Ноя.', 12 => 'Дек.');
$chBudYears = D::getYearsRangeList(2015, null, 0);
$color = false;
?>

<div class="mailing-wrapper">
    <form action="/mailing_plan" method="post" enctype="multipart/form-data" id="dealer_stat_form">
        <div style="width:100%; margin-top: 16px;">
            <div id="chBudYears" class="modal-select-wrapper select input krik-select float-right"
                 style="height: 23px; padding-bottom: 1px; padding-right: 18px; width: 120px; margin-right: 10px;  margin-bottom: 20px;">
                <span class="select-value">Бюджет на <?= $year; ?> г.</span>

                <div class="ico"></div>
                <input type="hidden" name="year" id="year" value="<?= $year ?>">

                <div class="modal-input-error-icon error-icon"></div>
                <div class="error message"></div>
                <div class="modal-select-dropdown">
                    <?php
                    foreach ($chBudYears as $y):
                        $url = url_for("@mailing_plan?year=" . $y);
                        ?>
                        <div style='height:auto; padding: 7px;' class="modal-select-dropdown-item select-item"
                             data-year="<?= $y; ?>"
                             data-url="<?php echo $url ?>"><?= "Планы на " . $y . " г."; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <table class="dealet_stat">
                <tbody>
                <!--шапка-->
                <tr>
                    <td class="head" style="width: 10%; text-align: center;">Номер</td>
                    <td class="head" style="width: 20%; text-align: center;">Название дилера</td>
                    <?php foreach ($months as $item): ?>
                        <td class="row_lite" style="width: 5%;"><?= $item; ?></td>
                    <?php endforeach; ?>

                </tr>

                <?php foreach ($report as $number => $item): ?>
                    <tr>
                        <td class="<?= $color ? 'row_dark' : 'row_lite'; ?>"><?= $item['dealer_id']; ?></td>
                        <td class="<?= $color ? 'row_dark' : 'row_lite'; ?>"><?= $item['name']; ?></td>

                        <?php for ($i=1; $i <= 12; ++$i): ?>
                            <td class="<?= $color ? 'row_dark' : 'row_lite'; ?>"><?= isset($item['data'][$i]) ? $item['data'][$i]['plan1'] .'<br>'. $item['data'][$i]['plan2'] : ''; ?></td>
                        <?php endfor; ?>

                        <?php $color = $color ? false : true; ?>
                    </tr>
                <?php endforeach; ?>
                
                </tbody>
            </table>
    </form>
</div>
</div>
<script type="text/javascript">
    $(function () {
        var form = $('#dealer_stat_form');
        $('.modal-select-dropdown-item').on('click', function () {
            $('#year').val($(this).data('year'));
            form.submit();
        });
    });
</script>
