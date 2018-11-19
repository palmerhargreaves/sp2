<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 08.10.2018
 * Time: 14:28
 */

$roman = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV');

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
                            <?php foreach ($consolidated_information->getQuarters() as $quarter): ?>
                                <div class="radio-control">
                                    <input type="checkbox" name="sum-quart-<?php echo $quarter; ?>"
                                           value="<?php echo $quarter; ?>" data-quarter="<?php echo $quarter; ?>"
                                           id="sum-quart-<?php echo $quarter; ?>" class="sum-quarters"/>
                                    <label for="sum-quart-<?php echo $quarter; ?>"><?php echo $roman[$quarter]; ?>
                                        квартал</label>
                                </div>
                            <?php endforeach; ?>
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
                                    <div class="modal-select-dropdown-item select-item"
                                         data-value="<?php echo $year_item; ?>"><?php echo $year_item; ?> год
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div id="" class="modal-select-wrapper select input krik-select"
                             style="padding-right: 18px; width: 170px;">
                            <span class="select-value">Все дилеры</span>
                            <div class="ico"></div>
                            <input type="hidden" name="regional_manager_or_dealers" value="-1">
                            <div class="modal-input-error-icon error-icon"></div>
                            <div class="error message"></div>
                            <div class="modal-select-dropdown">
                                <?php foreach (UserTable::getInstance()
                                                   ->createQuery('u')
                                                   ->where('u.group_id = ?', User::USER_GROUP_REGIONAL_MANAGER)
                                                   ->andWhere('u.active = ?', true)
                                                   ->andWhere('u.company_type = ? and u.company_department != ?', array('regional_manager', 0))
                                                   ->orderBy('u.name ASC')
                                                   ->execute() as $user): ?>
                                    <div class="modal-select-dropdown-item select-item"
                                         data-value="<?php echo $user->getNaturalPersonId(); ?>"><?php echo $user->selectName(); ?></div>
                                <?php endforeach; ?>

                                <div class="modal-select-dropdown-item select-item" data-value="-1">Все дилеры</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="activity-summary__descr">
                <?php
                $company_type_image = ActivityTypeCompanyImagesTable::getInstance()
                    ->createQuery()
                    ->where('company_type_id = ? and activity_id = ?', array($activity->getCompanyType()->getId(), $activity->getId()))
                    ->fetchOne();
                ?>

                <?php if ($company_type_image): ?>
                    <div class="activity-summary__descr__img"
                         style="background-image:url(http://dm.vw-servicepool.ru/images/company/<?php echo $company_type_image->getPath(); ?>"></div>
                <?php endif; ?>

                <div class="activity-summary__descr__txt">
                    <div class="activity-summary__descr__label">
                        <span><?php echo $activity->getCompanyType()->getName(); ?></span>
                    </div>
                    <div class="activity-summary__descr__title">
                        <?php echo $activity->getName(); ?>
                    </div>
                    <div class="activity-summary__descr__text">
                        <?php echo $activity->getRawValue()->getBrief(); ?>
                    </div>
                    <div class="activity-summary__descr__date">
                        <?php echo D::toLongRus($activity->getStartDate()); ?>
                        — <?php echo D::toLongRus($activity->getEndDate()); ?>
                    </div>
                </div>
            </div>

            <div class="activity-secton-header activity-secton-header_stats">
                <div class="activity-secton-title">Общая статистика</div>
            </div>

            <div class="activity-summary__stats">
                <?php include_partial('dealers_information', array('consolidated_information' => $consolidated_information)); ?>
            </div>


            <?php
            $effectiveness = $consolidated_information->getActivityEffectivenessCost();
            if ($effectiveness != 0):

                ?>
                <div class="activity-secton-header activity-secton-header_eff">
                    <div class="activity-secton-title">Эффективность акции*</div>
                </div>

                <div class="activity-summary__eff">
                    <span style="width: 100%;">Результативность акции: <?php echo round($effectiveness, 0); ?>%</span>
                    <strong></strong>
                </div>
            <?php endif; ?>

            <div class="activity-summary__actions">
                <div>Данные дилерских центров, заполнивших статистику на портале dm.vw-servicepool.ru.</div>
                <div>
                    <a id="js-export-consolidated-information" data-activity="<?php echo $activity->getId(); ?>"
                       data-url="<?php echo url_for('@activity_consolidated_information_export'); ?>" target="_blank"
                       href="javascript:;" class="btn btn_light btn_download">Выгрузить в файл</a>
                    <div id="loader-spinner" class="spinner" style="display: none; margin-top: 1px;">
                        <div class="bounce1"></div>
                        <div class="bounce2"></div>
                        <div class="bounce3"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $(function () {
        window.activity_consolidated_information = new ActivityConsolidatedInformation({
            on_change_manager_url: '<?php echo url_for('@on_consolidated_information_change_manager'); ?>',
            dealers_information_container: '.activity-summary__stats',
            activity: <?php echo $activity->getId(); ?>
        }).start();
    });
</script>
