<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 22.07.2016
 * Time: 11:35
 */
$activities_statuses = array('-1' => 'Все', 'completed' => 'Выполнено', 'in_work' => 'В работе', 'not_start' => 'Не начато');
$sort_fields = array('id' => '№', 'created_at' => 'Дата', 'name' => 'Имя');
?>

<?php $n = 0;
foreach ($company_list_data as $key => $data): ?>
    <div class="activity-container-content activity-container-content-key-<?php echo $key; ?>"
         data-company-type-id="<?php echo $key; ?>"
         style="<?php echo ++$n == $filters['filter_by_company'] ? "display: block;" : "display: none;"; ?>">
        <div class="tabs-activity index-tabs-activity d-cb">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="javascript:;" name="notFinished_<?php echo $key; ?>"
                       class="activity-tab-header activity-tab-header_key_<?php echo $key; ?>"
                       data-company-type-id="<?php echo $key; ?>"
                       data-tab="notFinished"
                       data-is-loaded="1">Текущие</a>
                </li>
                <li>
                    <a href="javascript:;" name="finished_<?php echo $key; ?>"
                       class="activity-tab-header activity-tab-header_key_<?php echo $key; ?> js-activity-tab"
                       data-company-type-id="<?php echo $key; ?>"
                       data-tab="finished"
                       data-is-loaded="0">Завершённые</a>
                </li>
                <?php if ($sf_user->getAuthUser()->isDealerUser()): ?>
                    <li>
                        <a href="javascript:;" name="activities_<?php echo $key; ?>"
                           class="activity-tab-header activity-tab-header_key_<?php echo $key; ?> js-activity-tab"
                           data-company-type-id="<?php echo $key; ?>"
                           data-tab="activities"
                           data-is-loaded="0">Статистика</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="tabs-activity-legend">
                <div class="label label-req"><input type="checkbox" name="" value=""
                                                    id="acts-required" <?php echo $filters['filter_by_required'] == 1 ? "checked" : ""; ?> ><label
                        for="acts-required">Обязательные</label></div>
                <div class="label label-own"><input type="checkbox" name="" value=""
                                                    id="acts-owned" <?php echo $filters['filter_by_owned'] == 1 ? "checked" : ""; ?>><label
                        for="acts-owned">Собственные</label></div>
            </div>
        </div>

        <div class="activity-main-page-sort d-cb">
            <?php foreach ($sort_fields as $sort_key => $sort_field): ?>
                <a href="javascript:"
                   class="lnk-sort <?php echo $filters['filter_by_sort']['sort_field'] == $sort_key ? 'active' : ''; ?>"
                   data-field-name="<?php echo $sort_key; ?>"
                   data-sort-direction="<?php echo $filters['filter_by_sort']['sort_direction']; ?>">
                    <?php echo $sort_field; ?>
                </a>
            <?php endforeach; ?>

            <div id="" class="modal-select-wrapper select input krik-select float-right">
                <span class="select-value"><?php echo $activities_statuses[$filters['filter_by_status']]; ?></span>
                <div class="ico"></div>
                <input type="hidden" class="sb_activity_status" value="<?php echo $filters['filter_by_status']; ?>">
                <div class="modal-input-error-icon error-icon"></div>
                <div class="error message"></div>
                <div class="modal-select-dropdown">
                    <?php foreach ($activities_statuses as $status_key => $status_text): ?>
                        <div class="modal-select-dropdown-item select-item"
                             data-value="<?php echo $status_key; ?>"><?php echo $status_text; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane active" id="notFinished_<?php echo $key; ?>"
                 data-company-type-id="<?php echo $key; ?>">
                <?php include_partial('activity/notFinishedActivities', array('activities' => $data['activities']['not_finished'], 'year' => $data['year'], 'filters' => $filters)); ?>
            </div>
            <div class="tab-pane" id="finished_<?php echo $key; ?>" data-company-type-id="<?php echo $key; ?>">
                <div class="spinner">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>
                <?php //include_partial('activity/finished', array('activities' => $data['activities']['finished'], 'year' => $data['year'], 'filters' => $filters)); ?>
            </div>
            <?php if ($sf_user->getAuthUser()->isDealerUser()): ?>
                <div class="tab-pane" id="activities_<?php echo $key; ?>" data-company-type-id="<?php echo $key; ?>">
                    <div class="spinner">
                        <div class="bounce1"></div>
                        <div class="bounce2"></div>
                        <div class="bounce3"></div>
                    </div>
                    <?php //include_partial('activity/dealerStatistics', array('builder' => $data['activities']['dealer_statistic'], 'year' => $data['year'], 'filters' => $filters)); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php endforeach; ?>
