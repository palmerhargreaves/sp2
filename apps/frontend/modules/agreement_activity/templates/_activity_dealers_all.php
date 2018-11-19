<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 29.03.2016
 * Time: 6:05
 */


?>
<?php
$ind = 0;
foreach ($extendedStats as $q => $data):
    ?>
    <?php foreach ($data as $year => $dealers):
    $total_dealers = count($dealers);
    $in_work = 0;
    $completed = 0;
    $not_work = 0;
    $total_models = 0;
    $models_completed_total_cash = 0;

    $models_completed = 0;
    $models_in_work = 0;
    ?>
    <fieldset class="agreeemnt-activities-dealers-fieldset">
        <legend class="agreeemnt-activities-dealers-legend"
                style="text-align: center; font-weight: bold; margin:10px;"><?php echo $year; ?>
            г.
        </legend>
        <?php foreach ($dealers as $id => $dealer): ?>
            <?php if ($dealer['all'] > 0): ?>
                <div class="group">
                    <div class="group-header">
                        <span class="ico">
                            <?php if ($dealer['done'] && $dealer['all'] > 0): $completed++; ?>
                                <img src="/images/ok-icon-active.png" alt="Выполнено"/>
                            <?php else: ?>
                                <?php if ($dealer['accepted_models'] > 0): $in_work++; ?>
                                    <img src="/images/ok-icon.png" alt="В работе"/>
                                <?php else: $not_work++; ?>
                                    <img src="/images/error-icon.png" alt="Не приступал"/>
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                        <span class="title"><?php echo $dealer['dealer']->getName(), ' (', substr(strval($dealer['dealer']->getNumber()), -3), ')' ?></span>

                        <div class="summary"><?php printf('Согласовано %d %s на сумму %s руб.', $dealer['accepted'], RusUtils::pluralModelsEnding($dealer['accepted']), number_format($dealer['sum'], 0, '.', ' ')) ?></div>
                        <div class="group-header-toggle"></div>
                    </div>
                    <div class="group-content">
                        <div id="accommodation" class="active">
                            <table class="models">
                                <tbody>
                                <?php
                                foreach ($dealer['models'] as $n => $model):
                                    $total_models++;
                                    if ($model->getStatus() == "accepted" && $model->getReport() && $model->getReport()->getStatus() == "accepted") {
                                        $models_completed++;
                                        $models_completed_total_cash += $model->getCost();
                                    } else {
                                        $models_in_work++;
                                    }
                                    ?>
                                    <?php $discussion = $model->getDiscussion() ?>
                                    <?php $new_messages_count = $discussion ? $discussion->countUnreadMessages($sf_user->getAuthUser()->getRawValue()) : 0 ?>
                                    <tr class="sorted-row model-row<?php echo($year ? '-ex' : '') ?> <?php if ($n % 2 == 0) echo ' even' ?>"
                                        data-model="<?php echo $model->getId() ?>"
                                        data-discussion="<?php echo $model->getDiscussionId() ?>"
                                        data-new-messages="<?php echo $new_messages_count ?>">
                                        <td width="75"
                                            data-sort-value="<?php echo $model->getId() ?>">
                                            <div class="num">
                                                № <?php echo $model->getId() ?></div>
                                            <div
                                                    class="date"><?php echo D::toLongRus($model->created_at) ?></div>
                                        </td>
                                        <td width="180"
                                            data-sort-value="<?php echo $model->getName() ?>">
                                            <div><?php echo $model->getName() ?></div>
                                            <div class="sort"></div>
                                        </td>
                                        <td width="146"
                                            class="placement <?php echo $model->getModelType()->getIdentifier() ?>">
                                            <div
                                                    class="address"><?php echo $model->getValueByType('place') ?></div>
                                        </td>
                                        <td width="146"><?php echo $model->getValueByType('period') ?></td>
                                        <td width="81"
                                            data-sort-value="<?php echo $model->getCost() ?>">
                                            <div><?php echo number_format($model->getCost(), 0, '.', ' ') ?>
                                                руб.
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
                                        <td data-sort-value="<?php echo $new_messages_count ?>"
                                            class="darker">
                                            <?php if ($new_messages_count > 0): ?>
                                                <div
                                                        class="message"><?php echo $new_messages_count ?></div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </fieldset>

    <fieldset class="agreeemnt-activities-dealers-fieldset">
        <legend style="text-align: center; font-weight: bold; argin:10px;"
                class="agreeemnt-activities-dealers-legend">Статистика
            за: <?php echo $year; ?>г.
        </legend>

        <table class="models">
            <tbody>
            <tr>
                <td>Всего дилеров:</td>
                <td><?php echo $total_dealers; ?></td>
                <td>Макетов:</td>
                <td><?php echo $total_models;; ?></td>
            </tr>
            <tr>
                <td>Всего в работе:</td>
                <td><?php echo $in_work; ?></td>
                <td>Выполнено:</td>
                <td><?php echo $models_completed; ?></td>
            </tr>
            <tr>
                <td>Выполнили:</td>
                <td><?php echo $completed; ?></td>
                <td>Макет согласован:</td>
                <td><?php echo $models_in_work; ?></td>
            </tr>
            <tr>
                <td>Макет добавлен:</td>
                <td><?php echo $not_work; ?></td>
                <td>Суммарные затраты</td>
                <td><?php echo Utils::format_amount($models_completed_total_cash); ?></td>
            </tr>
            </tbody>
        </table>
    </fieldset>
<?php endforeach; ?>

<?php endforeach; ?>
