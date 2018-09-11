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
                                <div id="container-allow-to-edit" style="display: none;">
                                    <?php if (!$disable_importer): ?>
                                        <button id="bt_on_save_statistic_data_importer" class="button apply-stat-button"
                                                style="width: 25%; margin: 10px; margin-right: -5px; float:right;"
                                                data-id='<?php echo $sf_user->getAuthUser()->getRawValue()->getDealer()->getId(); ?>'
                                                data-concept-id="<?php echo $bindedConcept ? $bindedConcept->getConceptId() : 0; ?>"
                                                data-to-importer='1'>Отправить импортеру
                                        </button>

                                    <?php endif; ?>

                                    <button id="bt_on_save_statistic_data_many" class="button apply-stat-button"
                                            style="width: 25%; margin: 10px; margin-right: -5px; float:right;"
                                            data-id='<?php echo $sf_user->getAuthUser()->getRawValue()->getDealer()->getId(); ?>'>
                                        Сохранить
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($allow_to_cancel): ?>
                            <div id="container-allow-to-cancel" style="display: none;">
                                <button id="bt_on_cancel_statistic_data" class="button gray cancel-stat-button"
                                        style="width: 25%; margin: 10px; margin-right: -5px; float:right;"
                                        data-id='<?php echo $sf_user->getAuthUser()->getRawValue()->getDealer()->getId(); ?>'>
                                    Отменить
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <input type="hidden" name="activity" value="<?php echo $activity->getId(); ?>"/>
                <input type="hidden" name="concept_id" value="0"/>
                <input type="hidden" name="send_to" value=""/>
                <input type="hidden" name="txt_frm_fields_data" id="txt_frm_fields_data"/>
                <input type="hidden" name="quarter" value="<?php echo $current_q; ?>"/>
        </form>
    </div>
</div>

<div id="confirm-send-data-to-specialist-modal" class="modal" style="width:400px;">
    <div class="white modal-header">Отправка данных</div>
    <div class="modal-close"></div>
    После отправки данных Вы не сможете их отредактировать. Вы уверены, что хотите отправить заполненную информацию ?
    <div style="display: block; width: 75%; margin: auto; margin-top: 40px;">
        <button id="bt-send-statistic-data" class="button accept-button" style="width: 45%; float: left; clear: both;">
            Отправить
        </button>
        <button id="bt-close-modal" class="button gray decline-button" style="width: 45%; float: right;"
                data-id='<?php echo $sf_user->getAuthUser()->getId(); ?>'>Отменить
        </button>
    </div>
</div>


<iframe src="/blank.html" width="1" height="1" frameborder="0" hspace="0" marginheight="0" marginwidth="0"
        name="activity_extended_statistic_target_iframe" scrolling="no"></iframe>

<script>
    $(function () {
        window.activity_extended_statistic = new ActivityExtendedStatistic({
            on_apply_concept_to_statistic: '<?php echo url_for('@activity_extended_bind_to_concept'); ?>',
            on_check_allow_to_edit_cancel: '<?php echo url_for('@activity_check_allow_to_edit_cancel_stat_data'); ?>',
            on_activity_extended_change_stats: '<?php echo url_for('@activity_extended_change_stats'); ?>',
            on_activity_extended_change_stats_with_importer: '<?php echo url_for('@activity_extended_change_stats_to_importer'); ?>',
            on_cancel_statistic: '<?php echo url_for('@activity_extended_statistic_data_cancel'); ?>',
            activity_id: <?php echo $activity->getId(); ?>,

        }).start();
    });
</script>
