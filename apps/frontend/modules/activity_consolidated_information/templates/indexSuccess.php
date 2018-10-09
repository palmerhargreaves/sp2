<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 08.10.2018
 * Time: 14:28
 */
?>

<div class="activity">
    <?php include_partial('activity/activity_head', array('activity' => $activity, 'year' => $year, 'active' => 'settings', 'current_q' => $current_q, 'current_year' => $current_year, 'quartersModels' => $quartersModels)); ?>
    <div class="content-wrapper">
        <?php include_partial('activity/activity_tabs', array('activity' => $activity, 'active' => 'consolidated')) ?>

        <div class="activity-summary active">

            <div class="activity-secton-header">
                <div class="activity-secton-filter">
                    <div class="activity-secton-filter__radio">
                        <p><strong>Выберите квартал(ы), по которым вы бы хотели сделать выгрузку:</strong></p>
                        <div class="fieldset-radios fieldset-radios_wide">
                            <div class="radio-control">
                                <input type="checkbox" name="" value="" id="sum-quart-1"/>
                                <label for="sum-quart-1">I квартал</label>
                            </div>
                            <div class="radio-control">
                                <input type="checkbox" name="" value="" id="sum-quart-2"/>
                                <label for="sum-quart-2">II квартал</label>
                            </div>
                            <div class="radio-control">
                                <input type="checkbox" name="" value="" id="sum-quart-3"/>
                                <label for="sum-quart-3">III квартал</label>
                            </div>
                            <div class="radio-control">
                                <input type="checkbox" name="" value="" id="sum-quart-4"/>
                                <label for="sum-quart-4">IV квартал</label>
                            </div>
                        </div>
                    </div>

                    <div class="activity-secton-filter__select">
                        <div id="" class="modal-select-wrapper select select_custom input krik-select"
                             style="padding-right: 18px; width: 85px;">
                            <span class="select-value"><?php echo $year; ?> год</span>
                            <div class="ico"></div>
                            <input type="hidden" name="year" value="<?php echo $year; ?>">
                            <div class="modal-input-error-icon error-icon"></div>
                            <div class="error message"></div>

                            <div class="modal-select-dropdown">
                                <?php foreach (Utils::getYearsList(2015, 1) as $year_item): ?>
                                    <div class="modal-select-dropdown-item select-item" data-value="<?php echo $year_item; ?>"><?php echo $year_item; ?> год</div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div id="" class="modal-select-wrapper select input krik-select" style="padding-right: 18px; width: 170px;">
                            <span class="select-value">Все дилеры</span>
                            <div class="ico"></div>
                            <input type="hidden" name="regional_manager_or_dealers" value="-1">
                            <div class="modal-input-error-icon error-icon"></div>
                            <div class="error message"></div>
                            <div class="modal-select-dropdown">
                                <?php foreach (UserTable::getInstance()
                                                   ->createQuery('u')
                                                   ->innerJoin('u.Group g')
                                                   ->where('g.id = ?', User::USER_GROUP_REGIONAL_MANAGER)
                                                    ->orderBy('u.name ASC')
                                                   ->execute() as $user): ?>
                                    <div class="modal-select-dropdown-item select-item" data-value="<?php echo $user->getId(); ?>"><?php echo $user->selectName(); ?></div>
                                <?php endforeach; ?>

                                <div class="modal-select-dropdown-item select-item">Все дилеры</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="activity-summary__descr">
                <div class="activity-summary__descr__img" style="background-image:url(/images/logo.png);"></div>
                <div class="activity-summary__descr__txt">
                    <div class="activity-summary__descr__label">
                        <span><?php echo $activity->getCompanyType()->getName(); ?></span>
                    </div>
                    <div class="activity-summary__descr__title">
                        <?php echo $activity->getName(); ?>
                    </div>
                    <div class="activity-summary__descr__text">
                        <?php echo $activity->getDescription(); ?>
                    </div>
                    <div class="activity-summary__descr__date">
                        <?php echo D::toLongRus($activity->getStartDate()); ?> — <?php echo D::toLongRus($activity->getEndDate()); ?>
                    </div>
                </div>
            </div>

            <div class="activity-secton-header activity-secton-header_stats">
                <div class="activity-secton-title">Общая статистика</div>
            </div>

            <div class="activity-summary__stats">
                <div class="activity-summary__stats__item">
                    <strong>13</strong>
                    Все дилеры
                </div>
                <div class="activity-summary__stats__item">
                    <strong>10</strong>
                    Дилеры-участники акции
                </div>
                <div class="activity-summary__stats__item">
                    <strong>7</strong>
                    Дилеры, приступившие к&nbsp;активности
                </div>
                <div class="activity-summary__stats__item">
                    <strong>2</strong>
                    Дилеры, заполнившие статистику
                </div>
            </div>

            <div class="activity-secton-header activity-secton-header_eff">
                <div class="activity-secton-title">Эффективность акции*</div>
            </div>

            <div class="activity-summary__eff">
                <span>Результативность акции (выручка — затраты, руб.)</span>
                <strong>2 041 110</strong>
            </div>

            <div class="activity-summary__actions">
                <div>Данные дилерских центров, заполнивших статистику на портале dm.vw-servicepool.ru.</div>
                <div>
                    <a href="javascript:" class="btn btn_light btn_download">Выгрузить в файл</a>
                </div>
            </div>

        </div>
    </div>
</div>
