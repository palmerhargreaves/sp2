<?php
    $roman = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV');
?>
<div class="custom-tabs f-vw">
    <div class="custom-tabs__wrap">
        <div class="custom-tabs__list">
            <?php foreach ($plan as $q => $plan_item): ?>
                <div class="js-custom-tab custom-tabs__item <?php echo $current_quarter == $q ? 'current' : ''; ?>" data-quarter="<?php echo $q; ?>">
                    <div class="custom-tabs__item-header"><?php echo $year; ?> - <?php echo $roman[$q]; ?> квартал</div>
                    <div>План: <strong><?php echo Utils::numberFormat($plan[$q]->getPlan(), ''); ?></strong> руб.</div>
                    <div>Факт: <strong><?php echo Utils::numberFormat($real[$q], ''); ?></strong> руб.</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
