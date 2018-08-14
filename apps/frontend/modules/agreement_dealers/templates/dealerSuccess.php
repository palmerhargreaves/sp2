<?php include_partial('agreement_activity_model_management/modal_model', array('decline_reasons' => $decline_reasons, 'decline_report_reasons' => $decline_report_reasons, 'specialist_groups' => $specialist_groups)) ?>
<?php include_component('budget', 'budgetPanel', array(
    'dealer' => $builder->getDealer(),
    'header' => '<a href="' . url_for('@agreement_module_dealers') . '">Дилеры</a> / ' . $builder->getDealer()->getName() . ' ' . $builder->getYear() . ' г.',
    'fromDealer' => true,
    'dealer' => $builder->getDealer(),
    'year' => $year,
    'budYears' => $budgetYears
));
?>

<div class="clear"></div>
<div class="actions-wrapper">
    <div class="activities dealer-activities-statistics" id="agreement-models">
        <h1 style="margin-top: 30px;">Статистика дилера в активностях по кварталам / кампаниям</h1>
        <?php $quarters = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV') ?>
        <div id="materials" class="active">
            <div id="accommodation" class="active">
                <?php foreach ($dealers_statistics as $q_key => $item_data): ?>
                    <?php $builder_data = $item_data['companies']; ?>

                    <h2><?php echo $quarters[$q_key] ?> Квартал</h2>
                    <?php foreach ($builder_data as $c_key => $stat_item): ?>
                        <?php
                        $stat = $stat_item['stat'];
                        $company_statistic = $stat_item['company_statistic_by_quarters'];
                        ?>
                        <div class="drop-shadow perspective">
                            <p style="font-size: 14px;"><?php echo $stat_item['company_type']; ?>.</p>
                            <p><?php echo sprintf('Выполнено %s%% на сумму %s', $stat_item['company_statistic']['completed'],
                                    Utils::numberFormat($stat_item['company_statistic']['total_cash'])); ?></p>
                            <p><?php echo sprintf('Заявок - %s', $company_statistic[$q_key][$c_key]['total_models']); ?></p>
                            <?php if ($company_statistic[$q_key][$c_key]['total_moved_models'] > 0): ?>
                                <p>
                                    <?php echo sprintf('Перешло в сл. квартал - %s, %s%%',
                                        Utils::numberFormat($company_statistic[$q_key][$c_key]['total_moved_models_cash']),
                                        $company_statistic[$q_key][$c_key]['total_moved_models_percent']);
                                    ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <?php $statistics_models = $company_statistic[$q_key][$c_key]['models']->getRawValue(); ?>
                        <?php foreach ($stat['activities'] as $activity): ?>
                            <div class="group" style="width: 97%; margin-left: 17px;">
                                <div
                                    class="<?php echo in_array($activity['activity']->getId(), $company_statistic[$q_key][$c_key]['activities_moved']->getRawValue()) ? 'group-header-alert' : ''; ?> group-header">
                                        <span
                                            class="title"><?php echo sprintF('[%s] %s', $activity['activity']->getId(), $activity['activity']->getName()) ?></span>
                                    <div
                                        class="summary"><?php echo number_format($activity['sum'], 0, '.', ' ') ?>
                                        руб.
                                    </div>
                                    <div class="group-header-toggle"></div>
                                </div>
                                <div class="group-content">
                                    <table class="models">
                                        <tbody>
                                        <?php foreach ($activity['models'] as $n => $model): $move_to_next_quarter = false; ?>
                                            <?php $discussion = $model->getDiscussion() ?>
                                            <?php $new_messages_count = $discussion ? $discussion->countUnreadMessages($sf_user->getAuthUser()->getRawValue()) : 0 ?>

                                            <?php if (array_key_exists($model->getId(), $statistics_models)): ?>
                                                <?php
                                                $statistic_model = $statistics_models[$model->getId()];
                                                if ($statistic_model['next_quarter']) {
                                                    $move_to_next_quarter = true;
                                                }
                                                ?>
                                            <?php endif; ?>

                                            <tr class="sorted-row model-row<?php echo !empty($year) ? '-ex' : '' ?><?php if ($n % 2 == 0) echo ' even' ?>"
                                                data-model="<?php echo $model->getId() ?>"
                                                data-discussion="<?php echo $model->getDiscussionId() ?>"
                                                data-new-messages="<?php echo $new_messages_count ?>"
                                                style="<?php echo $move_to_next_quarter ? '-webkit-box-shadow: -5px 0px 5px -2px rgba(255,0,0,1); -moz-box-shadow: -5px 0px 5px -2px rgba(255,0,0,1); box-shadow: -5px 0px 5px -2px rgba(255,0,0,1);' : ''; ?>">
                                                <td width="75" data-sort-value="<?php echo $model->getId() ?>">
                                                    <div class="num">№ <?php echo $model->getId() ?></div>
                                                    <div
                                                        class="date"><?php echo D::toLongRus($model->created_at) ?></div>
                                                </td>
                                                <td width="180" data-sort-value="<?php echo $model->getName() ?>">
                                                    <div><?php echo $model->getName() ?></div>
                                                    <div class="sort"></div>
                                                </td>
                                                <td width="146"
                                                    class="placement <?php echo $model->getModelType()->getIdentifier() ?>">
                                                    <div
                                                        class="address"><?php echo $model->getValueByType('place') ?></div>
                                                    <div
                                                        class="address"><?php echo $model->getValueByType('period') ?></div>
                                                </td>
                                                <td width="81" data-sort-value="<?php echo $model->getCost() ?>">
                                                    <div><?php echo number_format($model->getCost(), 0, '.', ' ') ?>руб.
                                                    </div>
                                                    <div class="sort"></div>
                                                </td>
                                                <td width="181" class="darker">
                                                    <div><?php $model->getSpecialistActionText() ?></div>
                                                    <div class="sort"></div>
                                                </td>
                                                <?php $waiting_specialists = $model->countWaitingSpecialists(); ?>
                                                <td class="darker">
                                                    <div
                                                        class="<?php echo $model->getCssStatus() ?>"><?php echo $waiting_specialists ? 'x' . $waiting_specialists : '' ?></div>
                                                </td>
                                                <?php $waiting_specialists = $model->countReportWaitingSpecialists(); ?>
                                                <td class="darker">
                                                    <div
                                                        class="<?php echo $model->getReportCssStatus() ?>"><?php echo $waiting_specialists ? 'x' . $waiting_specialists : '' ?></div>
                                                </td>
                                                <td data-sort-value="<?php echo $new_messages_count ?>" class="darker">
                                                    <?php if ($new_messages_count > 0): ?>
                                                        <div class="message"><?php echo $new_messages_count ?></div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
