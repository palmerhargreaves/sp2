<?php
$totalFiles = sfConfig::get('app_max_files_upload_count');
if ($report):
    $model_type = $report->getModel()->getModelType();
    $model = $report->getModel();

    if ($report->getStatus() != 'not_sent' || $report->getLastLogAction()):
        ?>
        <form method="post" action="/" target="accept-decline-report-frame" id="agreement-model-report-form">
            <input type="hidden" name="id" value="<?php echo $model->getId(); ?>"/>
            <input type="hidden" name="action_type" value=""/>

            <input type="hidden" name="<?php echo session_name(); ?>" value="<?php echo session_id(); ?>">
            <input type="hidden" name="upload_file_object_type" value=""/>
            <input type="hidden" name="upload_file_type" value=""/>
            <input type="hidden" name="upload_field" value=""/>
            <input type="hidden" name="upload_files_ids" value=""/>

            <div class="model-data"
                data-model-status="<?php echo $model->getStatus() ?>"
                data-css-status="<?php echo $model->getReportCssStatus() ?>"
                data-is-concept="<?php echo $model->isConcept() ? 'true' : 'false' ?>" />

            <div class="d-popup-cols">
                <div class="d-popup-col">
                    <div class="d-popup-files-wrap scrollbar-inner">
                        <div class="d-popup-files-row">
                            <?php $idx = 1; ?>
                            <?php foreach ($report->getUploadedFilesSchemaByType() as $model_files_type => $model_type): ?>
                                <?php if ($model_type['show']): ?>
                                    <div style="margin-top: 15px;">
                                        <?php $header = $model_type['label']; ?>
                                        <label><?php echo $header; ?></label>

                                        <div class="scroller scroller-report-files-<?php echo $idx++; ?>" style="margin-bottom: 10px; height: 200px;">
                                            <div class="scrollbar" style="height: 200px;">
                                                <div class="track" style="height: 200px;">
                                                    <div class="thumb">
                                                        <div class="end"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="viewport scroller-wrapper" style="height: 200px;">
                                                <div class="overview scroller-inner">

                                                    <?php $normalized_header = Utils::normalize($header); ?>
                                                    <div class="d-popup-uploaded-files d-cb"
                                                         data-toggled="toggle-view-box-<?php echo $normalized_header; ?>">
                                                        <?php foreach (ModelReportFiles::getModelFilesTypes() as $f_type): ?>
                                                            <?php
                                                            ModelReportFiles::sortFileList(function ($files_list) use ($report, $f_type, $model_type) {
                                                                include_partial('agreement_activity_model_report/report_uploaded_files/_report_' . $f_type . '_file',
                                                                    array
                                                                    (
                                                                        'files_list' => $files_list,
                                                                        'report' => $report,
                                                                        'allow_remove' => false,
                                                                        'allow_add_file_to_favorites' => $model_type['allow_add_to_favorites']
                                                                    )
                                                                );
                                                            },
                                                                $report,
                                                                $model_type['type'],
                                                                $model_files_type,
                                                                $f_type
                                                            );
                                                            ?>
                                                        <?php endforeach; ?>

                                                        <div class="d-popup-files-footer d-cb">
                                                            <a href="<?php echo url_for('@agreement_model_report_download_all_uploaded_files?id=' . $report->getId() . '&model_file_type=' . $model_files_type); ?>"
                                                               class="lnk-download">Скачать все</a>
                                                            <div class="toggle-view js-toggle-view"
                                                                 data-toggle="toggle-view-box-<?php echo $normalized_header; ?>">
                                                                <i class="view-list" data-view="list"></i>
                                                                <i class="view-grid" data-view="grid"></i>
                                                            </div>
                                                        </div><!-- /d-popup-files-footer -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="d-popup-col">
                    <div class="d-popup-req-title">
                        <strong>Файл</strong>
                    </div>

                    <div class="file">
                        <div class="modal-file-wrapper input">
                            <div id="report-files-progress-bar"
                                 class="progress-bar-content progress-bar-full-width"></div>
                        </div>
                    </div>

                    <div class="scroller scroller-report" style="margin-bottom: 10px; height: 200px;">
                        <div class="scrollbar">
                            <div class="track">
                                <div class="thumb">
                                    <div class="end"></div>
                                </div>
                            </div>
                        </div>
                        <div class="viewport scroller-wrapper" style="height: 200px;">
                            <div class="overview scroller-inner">
                                <div class="file">
                                    <div class="modal-file-wrapper input">

                                        <div id="container_model_files" class="control dropzone">
                                            <div class="d-popup-files-wrap scrollbar-inner">
                                                <div class="d-popup-files-row">
                                                    <div id="model_report_files"
                                                         class="d-popup-uploaded-files d-cb"></div>
                                                    <input type="file" id="agreement_report_comments_file"
                                                           name="agreement_comments_file"
                                                           multiple>
                                                </div>
                                            </div>

                                            <div class="d-popup-files-footer d-cb">
                                                <a href="javascript:" id="js-file-trigger-model-report"
                                                   class="button js-d-popup-file-trigger"
                                                   data-target="#agreement_report_comments_file">Прикрепить файл</a>

                                                <div id="model_report_files_caption" class="caption"
                                                     style="position: relative; text-align: left; width: 60%;">Для
                                                    выбора файлов
                                                    нажмите
                                                    на
                                                    кнопку
                                                </div>
                                            </div><!-- /d-popup-files-footer -->

                                        </div>

                                        <div class="modal-input-error-icon error-icon"></div>
                                        <div class="error message" style="display: none;"></div>
                                    </div>
                                    <div class="value file-name" style="margin: 5px 0 0;padding:0;border:0"></div>
                                    <div class="modal-form-uploaded-file"></div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="container-agreement-model-upload-file" style="width: 250px; overflow: hidden;"></div>

                    <div class="d-popup-message-wrap">
                        <label>Сообщение</label>
                        <textarea name="agreement_comments"></textarea>
                    </div>

                    <?php if ($report->getStatus() != 'accepted' && $report->getStatus() != 'wait_specialist' && $report->getStatus() != 'declined'): ?>
                        <div class="specialists-panel-wrap">
                            <?php if ($model->getStatus() == "accepted" && $report->getStatus() != "accepted"): ?>
                                <input type="checkbox" id="designer_approve"
                                       name="designer_approve" <?php echo $model->getDesignerApprove() ? "checked" : ""; ?>
                                       data-required="false"/>
                                <label for="designer_approve">С утверждением сотрудника</label>
                            <?php elseif ($model->getDesignerApprove() && $model->getStatus() == "accepted"): ?>
                                <img src='/images/ok-icon-active.png' title=''/>
                                <label for="designer_approve">С утверждением сотрудника</label>
                            <?php endif; ?>

                            <div class="specialists-panel-container hide">
                                <div class="specialists-panel">
                                    <?php include_partial('agreement_activity_special_model_regional_manager/panel_specialists_block', array('specialist_groups' => $specialist_groups, 'special_importer_groups' => $special_importer_groups, 'model' => $model)) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="buttons">

                        <?php
                        if (!$report->getModel()->getIsBlocked() || $report->getModel()->getAllowUseBlocked()): ?>
                            <?php if ($report->getStatus() != 'accepted' && $report->getStatus() != 'wait_specialist' && $report->getStatus() != 'declined' || $report->getStatus() == 'declined'): ?>
                                <div class="accept green button float-right modal-form-button"
                                     data-model-type="report_accept"><a href="#"
                                                                        class="accept">Согласовать</a>
                                </div>
                            <?php endif; ?>

                            <?php if ($report->getStatus() != 'declined'): ?>
                                <div class="decline gray button float-left modal-form-button"
                                     data-model-type="report_decline"><a href="#"
                                                                         class="decline">Отклонить</a>
                                </div>
                            <?php endif; ?>

                            <div class="clear"></div>
                        <?php else: ?>
                            <?php
                            $blocked_to = strtotime($report->getModel()->getUseBlockedTo());
                            if (!empty($blocked_to) && strtotime(date('d-m-Y H:i:s')) < $blocked_to): ?>
                                <div class="msg" style="background: #c4e6c8; width: 100%; margin: 10px;">Заявка
                                    разблокирована
                                    до: <?php echo date('d-m-Y H:i:s', $blocked_to); ?></div>
                            <?php else: ?>
                                <div class="dummy gray msg modal-form-button">Отчет заблокирован</div>
                                <div class='out-of-date' data-out='true'></div>
                            <?php endif; ?>

                            <div style="margin: auto; text-align: center; padding-top: 27px;">
                                <a style="font-size: 11px; color: black;"
                                   href="<?php echo url_for('@discussion_switch_to_dealer?dealer=' . $report->getModel()->getDealerId() . '&activityId=' . $report->getModel()->getActivityId() . '&modelId=' . $report->getModel()->getId()); ?>"
                                   target='_blank'>
                                    Перейти в активность
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="dummy gray msg modal-form-button">Отчет не добавлен</div>
    <?php endif; ?>

<?php else: ?>
    <div class="d-popup-cols">
        <div class="d-popup-col">
            <div style="text-align:center;">Отчёт не загружен</div>

            <?php
            $blocked_to = strtotime($model->getUseBlockedTo());
            if (!empty($blocked_to) && strtotime(date('d-m-Y H:i:s')) < $blocked_to): ?>
                <div class="msg" style="background: #c4e6c8; width: 100%; margin: 10px;">Заявка разблокирована
                    до: <?php echo date('d-m-Y H:i:s', $blocked_to); ?></div>
            <?php elseif ($model->getIsBlocked() && !$model->getAllowUseBlocked()): ?>
                <div class="dummy gray msg modal-form-button" style="margin-top: 10px;">Заявка заблокирована</div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

