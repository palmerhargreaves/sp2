
<?php include_partial('agreement_activity_model_management/menu', array('active' => 'activities', 'year' => $year, 'url' => 'agreement_module_activities', 'budYears' => $budgetYears)) ?>

<div class="activities">

<?php if($is_finished): ?>
          <h1>Завершенные Активности</h1>
<?php else: ?>
<?php endif; ?>

<div id="dealer-list" class="modal">
    <div class="modal-header">Список дилеров</div>
    <div class="modal-close"></div>
    <div class="modal-text">Весення сервисная акция. Не приступали.
        <ul>
            <li>Дилер (000)</li>
            <li>Дилер (000)</li>
            <li>Дилер (000)</li>
            <li>Дилер (000)</li>
            <li>Дилер (000)</li>
            <li>Дилер (000)</li>
            <li>Дилер (000)</li>
        </ul>
    </div>
</div>

<?php if(count($activities) > 0): ?>
    <?php if($is_finished): ?>
    <div id="chBudYears" class="modal-select-wrapper select input krik-select float-left" style="height: 23px; padding-bottom: 1px; padding-right: 18px; width: 140px; margin-right: 10px; margin-top: 10px;">
        <span class="select-value">Активности на <?= $year; ?> г.</span>

        <div class="ico"></div>
        <input type="hidden" name="year" id="year" value="<?php echo $year ?>">

        <div class="modal-input-error-icon error-icon"></div>
        <div class="error message"></div>
        <div class="modal-select-dropdown">
        <?php foreach ($years_range as $y): ?>
            <?php $url = url_for("/activity/module/agreement/activities/finished?year=" . $y); ?>
            <div style='height:auto; padding: 7px;' class="modal-select-dropdown-item select-item"
                 data-year="<?= $y; ?>"
                 data-url="<?php echo $url ?>"><?= "Активности на " . $y . " г."; ?></div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <table id="activities">
        <tr>
            <th>Активность</th>
            <th width="140">Сроки</th>
            <th width="110">Выполнено</th>
            <th width="110">В работе</th>
            <th width="110">Не приступали</th>
        </tr>
  <?php foreach($activities as $id => $activity): ?>
        <tr>
            <td><a href="<?php echo url_for('@agreement_module_activity?id='.$id.'&year='.$year) ?>"><?php echo $activity['activity']->getName(); ?></a></td>
            <td>
    <?php if($activity['activity']->getCustomDate()): ?>
                            <?php echo nl2br($activity['activity']->getCustomDate()) ?>
    <?php else: ?>
                            с <?php echo D::toLongRus($activity['activity']->getStartDate()) ?>
                            <br/>
                            по <?php echo D::toLongRus($activity['activity']->getEndDate()) ?>
    <?php endif; ?>
            </td>
            <td class="complete dealers-list-handler" data-url="<?php echo url_for('@agreement_module_activity_dealers_done?id='.$id.'&year='.$year) ?>"><?php echo $activity['done'], ' ', RusUtils::pluralDealerEnding($activity['done_dealers_count']) ?></td>
            <td class="progress dealers-list-handler" data-url="<?php echo url_for('@agreement_module_activity_dealers_in_work?id='.$id.'&year='.$year) ?>"><?php echo $activity['in_work'], ' ', RusUtils::pluralDealerEnding($activity['in_work']) ?></td>
            <td class="blank dealers-list-handler" data-url="<?php echo url_for('@agreement_module_activity_dealers_no_work?id='.$id.'&year='.$year) ?>"><?php echo $activity['no_work'], ' ', RusUtils::pluralDealerEnding($activity['no_work']) ?></td>
        </tr>
  <?php endforeach; ?>
    </table>

<?php use_javascript('dealers/list_popup') ?>
<script type="text/javascript">
$(function() {
    new DealersListPopup({
        handler_selector: '#activities .dealers-list-handler',
        popup_selector: '#dealer-list'
    }).start();

    $('.modal-select-dropdown-item').on('click', function () {
        window.location.href = $(this).data('url');
    });
});
</script>

<?php endif; ?>
</div>

<?php if(!$is_finished): ?>
<div style="width: 148px; margin-top: 20px;" class="small back button"><a href="<?php echo url_for('@agreement_module_finished_activities?year='.$year) ?>">Посмотреть завершенные</a></div>
<?php endif; ?>

