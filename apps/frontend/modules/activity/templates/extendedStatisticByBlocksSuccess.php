<div class="activity">
    <?php
    include_partial('activity/activity_head', array( 'activity' => $activity, 'quartersModels' => $quartersModels, 'current_q' => $current_q, 'current_year' => $current_year, 'show_quarters_tabs' => true ));
    ?>
    <div class="content-wrapper">
        <?php include_partial('activity/activity_tabs', array( 'activity' => $activity, 'active' => 'extended_statistic' )) ?>

        <div class="pane-shadow"></div>
        <form id='frmStatistics' enctype="multipart/form-data" method="post"
              target="activity_extended_statistic_target_iframe">
            <div id="agreement-models" class="pane clear">
                <div id="approvement" class="active">
                    <?php
                    $concepts = AgreementModelTable::getInstance()
                        ->createQuery('am')
                        ->innerJoin('am.AgreementModelSettings ams')
                        ->where('activity_id = ? and model_type_id = ?', array( $activity->getId(), 10 ))
                        //->andWhere('ams.certificate_date_to >= ?', date('Y-m-d'))
                        ->andWhere('am.dealer_id = ?', $sf_user->getAuthUser()->getDealer()->getId())
                        ->orderBy('ams.id ASC')
                        ->execute();
                    if ($concepts && $concepts->count() > 0):
                        ?>
                        <div style="float: left; margin: 10px 0px 0px 10px; ">
                            <h6 style="font-weight: bold;">Срок действия сертификата:</h6>
                            <select id="sbActivityCertificates" name="sbActivityCertificates"
                                    style="width: 168px; border: 1px solid #d3d3d3; border-radius: 3px; height: 22px; padding: 0 0 0 10px; margin-top: 15px;">
                                <option value="-1">Выберите сертификат ...</option>
                                <?php
                                foreach ($concepts as $concept):
                                    $isBinded = ActivityExtendedStatisticFieldsTable::checkUserConcept($sf_user->getRawValue(), $concept);
                                    ?>
                                    <option value="<?php echo $concept->getId(); ?>">
                                        <?php echo sprintf("%s%s: %s [%d]", $isBinded ? "+ " : "", $activity->getName(), $concept->getAgreementModelSettings()->getCertificateDateTo(), $concept->getId()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button id="btApplyConceptToStatistic" class="button small"
                                    data-activity-id="<?php echo $activity->getId(); ?>">Принять
                            </button>
                            <img id="imgLoader" src="/images/loader.gif" style="display: none;"/>
                        </div>
                    <?php endif; ?>

                    <div id="materials" style="float: left; width: 99%;">
                        <div id="accommodation" class="active">
                            <?php
                            include_partial('statistic_by_blocks', array( 'activity' => $activity,
                                'current_q' => $current_q,
                                'allow_to_edit' => $allow_to_edit_fields,
                                'allow_to_cancel' => $allow_to_cancel,
                                'disable_importer' => $disable_importer,
                                'quartersModels' => $quartersModels,
                                'current_year' => $current_year,
                                'concept' => $bindedConcept,
                                'pre_check_statistic' => isset($pre_check_statistic) ? $pre_check_statistic : null ));
                            ?>
                        </div>
                    </div>
                </div>

                <table class="models">
                    <tbody></tbody>
                </table>

                <div class="info-save-complete"
                     style="display: none; width: 99%; margin: 10px; padding: 10px; color: red; text-align: center; font-weight: bold;">
                    Параметры статистики успешно сохранены !
                </div>

                <div id="bts-container" style="display: block; width: 99%; height: 55px;">
                    <?php if (isset($pre_check_statistic) && !is_null($pre_check_statistic) && ( $pre_check_statistic == ActivityStatisticPreCheckAbstract::CHECK_STATUS_IN_PROGRESS )
                        && ( $sf_user->getAuthUser()->isImporter() || $sf_user->getAuthUser()->isManager() )
                    ): ?>
                        <div id="bts-container" style="display: block; width: 99%; height: 55px;">
                            <button id="bt_on_decline_statistic" class="button apply-stat-button"
                                    style="width: 25%; margin: 10px; margin-right: -5px; float:right;"
                                    data-id='<?php echo $sf_user->getAuthUser()->getRawValue()->getDealer()->getId(); ?>'>
                                Отклонить
                            </button>

                            <button id="bt_on_accept_statistic" class="button apply-stat-button"
                                    style="width: 25%; margin: 10px; margin-right: -5px; float:right;"
                                    data-id='<?php echo $sf_user->getAuthUser()->getRawValue()->getDealer()->getId(); ?>'>
                                Согласовать
                            </button>
                        </div>
                    <?php else: ?>
                        <?php if ($allow_to_edit): ?>
                            <div id="bts-container" style="display: block; width: 99%; height: 55px;">
                                <?php if (!$disable_importer): ?>
                                    <button id="bt_on_save_statistic_data_once" class="button apply-stat-button"
                                            style="width: 25%; margin: 10px; margin-right: -5px; float:right;"
                                            data-id='<?php echo $sf_user->getAuthUser()->getRawValue()->getDealer()->getId(); ?>'>
                                        Отправить импортеру
                                    </button>

                                <?php endif; ?>

                                <button id="bt_on_save_statistic_data_many" class="button apply-stat-button"
                                        style="width: 25%; margin: 10px; margin-right: -5px; float:right;"
                                        data-id='<?php echo $sf_user->getAuthUser()->getRawValue()->getDealer()->getId(); ?>'>
                                    Сохранить
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php if ($allow_to_cancel): ?>
                            <button id="bt_on_cancel_statistic_data" class="button gray cancel-stat-button"
                                    style="width: 25%; margin: 10px; margin-right: -5px; float:right;"
                                    data-id='<?php echo $sf_user->getAuthUser()->getRawValue()->getDealer()->getId(); ?>'>
                                Отменить
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <input type="hidden" name="quarter"
                       value="<?php echo !empty($statisticQuarter) ? $statisticQuarter : $current_q; ?>"/>
                <input type="hidden" name="year" value="<?php echo $current_year; ?>"/>
                <input type="hidden" name="activity" value="<?php echo $activity->getId(); ?>"/>
                <input type="hidden" name="txt_frm_fields_data" id="txt_frm_fields_data"/>
                <input type="hidden" name="concept_id" value="0" />
        </form>
    </div>
</div>

<div id="confirm-send-data-to-specialist-modal" class="modal" style="width:400px;">
    <div class="white modal-header">Отправка данных</div>
    <div class="modal-close"></div>
    После отправки данных Вы не сможете их отредактировать. Вы уверены, что хотите отправить заполненную информацию ?
    <div style="display: block; width: 75%; margin: auto; margin-top: 40px;">
        <button id="bt-send-video-record-statistic-data" class="button accept-button"
                style="width: 45%; float: left; clear: both;">Отправить
        </button>
        <button id="bt-cancel-send-video-record-statistic" class="button gray decline-button"
                style="width: 45%; float: right;"
                data-id='<?php echo $sf_user->getAuthUser()->getId(); ?>'>Отменить
        </button>
    </div>
</div>

<?php if ($activity->getActivityVideoStatistics() && $activity->getActivityVideoStatistics()->getFirst()->getAllowStatisticPreCheck()): ?>
    <div id="activity-accept-cancel-info-modal" class="modal" style="width:400px;">
        <div class="white modal-header">Согласование статистики</div>
        Статистика отправлена на согласование. Дождитесь ответа импортера.

        <div style="display: block; width: 75%; margin: auto; margin-top: 40px;">
            <button id="bt-activity-close-pre-check" class="button gray" style="width: 45%; float: right;"
                    data-id='<?php echo $sf_user->getAuthUser()->getId(); ?>'>Закрыть
            </button>
        </div>
    </div>
<?php endif; ?>


<div id="confirm-send-data-to-specialist-modal" class="modal" style="width:400px;">
    <div class="white modal-header">Отправка данных</div>
    <div class="modal-close"></div>
    После отправки данных Вы не сможете их отредактировать. Вы уверены, что хотите отправить заполненную информацию ?
    <div style="display: block; width: 75%; margin: auto; margin-top: 40px;">
        <button id="bt-send-video-record-statistic-data" class="button accept-button"
                style="width: 45%; float: left; clear: both;">Отправить
        </button>
        <button id="bt-cancel-send-video-record-statistic" class="button gray decline-button"
                style="width: 45%; float: right;"
                data-id='<?php echo $sf_user->getAuthUser()->getId(); ?>'>Отменить
        </button>
    </div>
</div>


<iframe src="/blank.html" width="1" height="1" frameborder="0" hspace="0" marginheight="0" marginwidth="0"
        name="activity_extended_statistic_target_iframe" scrolling="no"></iframe>

<script>
    $(function () {
        window.activity_video_record_statistic = new ActivityVideoRecordStatistic({
            on_add_new_fields: '<?php echo url_for('@on_add_new_video_record_statistic_fields');?>',
            on_save_data: '<?php echo url_for('@on_save_video_record_statistic'); ?>',
            on_save_importer_data: '<?php echo url_for('@on_save_importer_video_record_statistic');?>',
            on_delete_field: '<?php echo url_for('@on_delete_video_record_field'); ?>',
            on_cancel_url: '<?php echo url_for('@on_cancel_statistic_data'); ?>',
            on_accept_statistic_data_by_user_url: '<?php echo url_for('@on_accept_statistic_data_by_user'); ?>',
            on_cancel_statistic_data_by_user: '<?php echo url_for('@on_cancel_statistic_data_by_user'); ?>',
            activity_id: <?php echo $activity->getId(); ?>,
            quarter: <?php echo $sf_user->getCurrentQuarter(); ?>,
            year: <?php echo $current_year; ?>,
        }).start();

        new SpecialAgreementConceptBindTargetAndStatistic({
            on_change_concept: '<?php echo url_for('@on_special_agreement_change_concept_bind_target_and_statistic'); ?>',
            activity_id: '<?php echo $activity->getId(); ?>',
            sb_concepts_element: '#sb_concept_targets',
            container_concept_targets: '#container_concept_targets',
            container_concept_statistic: '#container_concept_statistic'
        }).start();

        $('#frmStatistics .with-date').datepicker();
    });
</script>
