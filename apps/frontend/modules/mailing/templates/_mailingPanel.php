<?php
/**
 * Created by PhpStorm.
 * User: Andrey
 * Date: 26.11.2015
 * Time: 17:11
 *
 * echo "Здесь вёрстка панели";
 */
use_stylesheet('mailing.css');
?>

<?php
$months = array(1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель', 5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август', 9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь');
$budQuater = array(1 => '1 квартал', 2 => '2 квартал', 3 => '3 квартал', 4 => '4 квартал');
$roman = array(1, 2, 3, 4);
$years = array(2015, 2016, 2017, 2018);

$dt = new DateTime();
$dt->modify('-1 month');

?>
<div class="mailing-wrapper">
    <div class="mailing">
        <?php if ($display_filter): ?>
            <h1 style="height: 38px;">
                <div id="chBudQuater" class="modal-select-wrapper select input krik-select float-right"
                     style="height: 23px; padding-bottom: 1px; padding-right: 18px; width: 120px; margin-right: 10px;">
                    <span class="select-value"><?= $quarter; ?> квартал</span>
                    <div class="ico"></div>
                    <input type="hidden" name="quater" value="<?= $quarter ?>">
                    <div class="modal-input-error-icon error-icon"></div>
                    <div class="error message"></div>
                    <div class="modal-select-dropdown">
                        <?php
                        foreach ($roman as $quater):
                            $url = !empty($fromDealer) ? url_for("@agreement_module_dealer?id=" . $dealer->getId() . "&quarter=" . $quater) : url_for("@homepage?quarter=" . $quater . "&year=" . $year);
                            ?>
                            <div style='height:auto; padding: 7px;' class="modal-select-dropdown-item select-item"
                                 data-url="<?php echo $url ?>"><?php echo $quater . " квартал"; ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div id="chBudQuater" class="modal-select-wrapper select input krik-select float-right"
                     style="height: 23px; padding-bottom: 1px; padding-right: 18px; width: 120px; margin-right: 10px;">
                    <span class="select-value"><?= $year; ?> год</span>
                    <div class="ico"></div>
                    <input type="hidden" name="year" value="<?= $year ?>">
                    <div class="modal-input-error-icon error-icon"></div>
                    <div class="error message"></div>
                    <div class="modal-select-dropdown">
                        <?php
                        foreach ($years as $y):
                            $url = url_for("@homepage?quarter=" . $quater . "&year=" . $y);
                            ?>
                            <div style='height:auto; padding: 7px;' class="modal-select-dropdown-item select-item"
                                 data-url="<?php echo $url ?>"><?php echo $y . " год"; ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                Загрузка e-mail адресов
            </h1>
        <?php endif; ?>

        <div class="month-tabs tabs">
            <?php $month_number2 = $month_number; ?>
            <?php for ($month_number; $month_number <= $end_month; $month_number++): ?>
                <div data-pane="<?= $month_number; ?>"
                     class="tab mailing-month-pane<?= $month_number == $current_month ? ' active' : ''; ?>">
                    <div class="tab-header">
                        <div class="required-activities">
                            <span>
                                <?= isset($quater_month[$month_number]) && isset($dealer_mailings[$month_number])
                                    ? sprintf('%s %%', round($dealer_mailings[$month_number] / ($quater_month[$month_number] / 100)))
                                    : ''; 
                                ?></span>
                        </div>
                        <span><?= $months[$month_number]; ?></span>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <?php
            //Среднее количество загруженных писем за квартал
            $quarters_mails_loaded = 0;
            $avg_per_quarter = 0;

            $month_start = $month_number2;
            for ($month_start; $month_start <= $end_month; $month_start++) {
                if (isset($dealer_mailings[$month_start]) && $dealer_mailings[$month_start] > 0) {
                    $quarters_mails_loaded++;
                    $avg_per_quarter += $dealer_mailings[$month_start] / ($quater_month[$month_start] / 100);
                }
            }

            //Если загружено за три месяца
            if ($quarters_mails_loaded == 3) {
                $avg_per_quarter = ceil($avg_per_quarter / 3);
            } else {
                $avg_per_quarter = 0;
            }
        ?>

        <?php for ($month_number2; $month_number2 <= $end_month; $month_number2++): ?>
            <div class="month-pane" id="mailing-pane<?= $month_number2; ?>"
                 style="top: 74px; <?= $month_number2 == $current_month ? ' display: block;' : ' display: none; '; ?>">
                <div class="progressbar-wrapper" style="border-bottom: 2px solid #ffffff;">
                    <div class="sum-mail"></div>
                    <div class="progressbar">
                        <div class="white"></div>
                        <?php $display_percent = isset($dealer_mailings[$month_number2]) ? $dealer_mailings[$month_number2] / ($quater_month[$month_number2] / 100) : 0; ?>
                        <div class="blue" data-percent="21.034949090909"
                             style="display: block; width:<?= $display_percent > 100 ? 100 : $display_percent; ?>%;">
                            <img src="/images/blue-end.png" alt=""></div>
                    </div>
                    <div class="done">
                        Загружено за месяц:
                        <span><?= isset($dealer_mailings[$month_number2]) ? number_format($dealer_mailings[$month_number2], 0, '.', ' ') : 0; ?></span>
                        шт.
                        <?php if (($display_load_panel && $month_number2 == $dt->format('m') && $year == $dt->format('Y')) || $removal_allowed): ?>
                            <a href="/mailing_delete" class="button-odd" id="remove-mailings">Удалить</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="progressbar-wrapper">
                    <div class="labels">
                        <div class="label">&nbsp;</div>
                        <div class="label">
                            <?= $budQuater[$quarter]; ?>
                            <?php if ($avg_per_quarter != 0):
                                echo sprintf('(%s%%)', $avg_per_quarter);
                            elseif (count($dealer_mailings_plan->getRawValue()) > 2): ?>
                                <?php if (isset($dealer_mailings_plan[$quarter]) && $dealer_mailings_plan[$quarter]): ?>
                                    (<?= (!empty($total_dealer_mailings) && !empty($total_plan) ? round($total_dealer_mailings / ($total_plan / 100)) : 0); ?>%)
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="sum-diag"></div>
                    <div class="progressbar">
                        <div class="white"></div>
                        <?php $quarter_diaplay_percent = !empty($total_dealer_mailings) ? $total_dealer_mailings / ($total_plan / 100) : 0; ?>
                        <div class="blue" data-percent="21.034949090909"
                             style="display: block; width: <?= $quarter_diaplay_percent > 100 ? 100 : $quarter_diaplay_percent; ?>%;">
                            <img src="/images/blue-end.png" alt=""></div>
                    </div>
                    <div class="done">Загружено за квартал:
                        <span><?= isset($total_dealer_mailings) ? $total_dealer_mailings : 0; ?></span> шт.
                    </div>
                </div>

            </div>
        <?php endfor; ?>

    </div>
    <div class="year-summary">
        <h1>&nbsp;</h1>
        <div class="diagram-mailing-wrapper">
            <?php if ($display_load_panel): ?>
                <div class="modal-select-wrapper select input krik-select float-right"
                     style="position: absolute; top: 45%; left: 41%; width: 138px; margin: -15% 0 0 -25%; background: #fff;">
                    <a href="/mailing_dealer" class="select-value"
                       style="float: left; padding-left: 31px;">Загрузить</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $(document).on("click", "#chBudQuater .select-item", function () {
            location.href = $(this).data('url');
        });
        $('.mailing-month-pane').on('click', function (e) {
            e.preventDefault();
            var pane_id = $(this).data('pane');
            $('.month-pane').css('display', 'none');
            $('#mailing-pane' + pane_id).css('display', 'block');
        });

        $('remove-mailings').on('click', function (e) {

        });
    });
</script>

