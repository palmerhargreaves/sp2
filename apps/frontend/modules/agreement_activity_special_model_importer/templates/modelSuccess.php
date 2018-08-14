<?php $totalFiles = sfConfig::get('app_max_files_upload_count'); ?>
<?php if (Utils::allowedIps()): ?>
    <form method="post" action="/" target="accept-decline-model-frame" id="agreement-model-form">
    <?php else: ?>
        <form method="post" action="/" target="accept-decline-model-frame" id="agreement-model-form">
    <?php endif; ?>

    <input type="hidden" name="id" value="<?php echo $model->getId(); ?>"/>
    <input type="hidden" name="action_type" value=""/>
    <input type="hidden" name="step" value=""/>

    <input type="hidden" name="<?php echo session_name(); ?>" value="<?php echo session_id(); ?>">
    <input type="hidden" name="upload_file_object_type" value=""/>
    <input type="hidden" name="upload_file_type" value=""/>
    <input type="hidden" name="upload_field" value=""/>
    <input type="hidden" name="upload_files_ids" value=""/>

    <div class="d-popup-cols">
        <div class="d-popup-col">

            <div class="d-popup-req-title number-field">
                <b><?php echo $model->getDealer()->getName() ?></b>
                <?php if (!$model->isConcept()): ?>
                    <strong>№</strong>
                    <div class="value"><?php echo $model->getId() ?></div>
                <?php endif; ?>
            </div>

            <table class="model-data d-popup-tbl-params odd" data-model-status="<?php echo $model->getStatus() ?>"
                   data-css-status="<?php echo $model->getCssStatus() ?>"
                   data-is-concept="<?php echo $model->isConcept() ? 'true' : 'false' ?>">

                <tr>
                    <td class="label">Активность</td>
                    <td class="field controls">
                        <div class="value"><?php echo $model->getActivity()->getName() ?></div>
                    </td>
                </tr>
                <?php if (!$model->isConcept()): ?>
                    <tr>
                        <td class="label">Название материала</td>
                        <td class="field controls">
                            <div class="value"><?php echo $model->getName() ?></div>
                        </td>
                    </tr>
                    <?php if ($model->isValidModelCategory()): ?>
                        <tr>
                            <td class="label">Категория</td>
                            <td class="field controls">
                                <div class="value"><?php echo $model->getModelCategory()->getName() ?></div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="label">Тип размещения</td>
                        <td class="field controls">
                            <div class="value"><?php echo $model->getModelType()->getName() ?></div>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php $fields = $model->isValidModelCategory() ? $model->getModelCategory()->getCategoryFields() : $model->getModelType()->getFields(); ?>

                <?php foreach ($fields as $field):
                    $val = $model->getValueByType($field->getIdentifier());
                    if (!empty($val)):?>
                        <tr class="<?php echo $field->getHide() == 1 ? "ext-type-field" : ""; ?> type-fields-<?php echo $field->getModelTypeId(); ?>"
                            data-field-type="<?php echo $field->getModelTypeId(); ?>"
                            data-is-hide="<?php echo $field->getHide(); ?>">
                            <td class="label"><?php echo $field->getName() ?><?php if ($field->getUnits()): ?>, <?php echo $field->getUnits() ?><?php endif; ?></td>
                            <td class="field controls">
                                <div class="value"><?php echo $model->getValueByType($field->getIdentifier()); ?></div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php $model_period = $model->getPeriod(); ?>
                <?php if (!$model->isConcept() && !empty($model_period)): ?>
                    <tr>
                        <td class="label">Период</td>
                        <td class="field controls">
                            <div class="value"><?php echo $model_period; ?></div>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if (!$model->isConcept()): ?>
                    <tr>
                        <td class="label">Сумма</td>
                        <td class="field controls">
                            <div class="value"><?php echo $model->getCost() ?></div>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if ($model->getAcceptInModel() != 0) { ?>
                    <tr>
                        <td class="label">Пролонгация заявки №</td>
                        <td class="field controls">
                            <div class="value"><?php echo $model->getAcceptInModel(); ?></div>
                        </td>
                    </tr>
                <?php } ?>

                <?php
                if ($model->getActivity()->getAllowCertificate() && $model->getConceptId() != 0) {
                    $concept = $model->getConcept() ? $model->getConcept() : $model;
                    if ($concept) {
                        include_partial('model_dates', array('concept' => $concept));
                    }
                } else if ($model->getAgreementModelDates()->count() > 0) {
                    include_partial('model_dates', array('concept' => $model));
                }
                ?>

                <?php

                $editor_link = $model->getEditorLink();
                if ($editor_link): ?>
                    <tr>
                        <td class="label">Внешняя ссылка</td>
                        <td class="field controls">
                            <?php
                            if (strrpos($model->getEditorLink(), 'http') !== false) {
                                $fileSize = Utils::getRemoteFileSize($model->getEditorLink());
                            }
                            ?>

                            <a href="<?php echo $model->getEditorLink(); ?>"
                               target="_blank"><?php echo $model->getEditorLink() . ' (' . $fileSize . ')' ?></a>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>

            <?php //if (empty($editor_link)): ?>
            <div class="d-popup-files-wrap scrollbar-inner">
                <div class="d-popup-files-row">
                    <?php $idx = 1; ?>
                    <?php foreach ($model->getUploadedFilesSchemaByType() as $model_files_type => $model_type): ?>
                        <?php if ($model_type['show']): ?>
                            <div style="margin-top: 15px;">
                                <?php $header = $model_type['label']; ?>
                                <div class="label-container">
                                    <?php if ($model->isModelScenario() && $model_type['file_type'] == AgreementModel::UPLOADED_FILE_SCENARIO_TYPE && $model->isModelScenarioCompleted()): ?>
                                        <img src="/images/accepted.png"/>
                                    <?php endif; ?>

                                    <?php if ($model->isModelScenario() && $model_type['file_type'] == AgreementModel::UPLOADED_FILE_RECORD_TYPE && $model->isModelRecordCompleted()): ?>
                                        <img src="/images/accepted.png"/>
                                    <?php endif; ?>

                                    <label><?php echo $header; ?></label>
                                </div>

                                <div class="scroller scroller-model-files-<?php echo $idx++; ?>"
                                     style="margin-bottom: 10px; height: 220px;">
                                    <div class="scrollbar">
                                        <div class="track">
                                            <div class="thumb">
                                                <div class="end"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="viewport scroller-wrapper" style="min-height: 220px;">
                                        <div class="overview scroller-inner">

                                            <?php $normalized_header = Utils::normalize($header); ?>
                                            <div class="d-popup-uploaded-files d-cb" style="min-height: 137px;"
                                                 data-toggled="toggle-view-box-<?php echo $normalized_header; ?>">
                                                <?php foreach (ModelReportFiles::getModelFilesTypes() as $f_type): ?>
                                                    <?php
                                                    ModelReportFiles::sortFileList(function ($files_list) use ($model, $f_type) {
                                                        include_partial('agreement_activity_model_management/model_uploaded_files/_model_' . $f_type . '_file',
                                                            array
                                                            (
                                                                'files_list' => $files_list,
                                                                'model' => $model,
                                                                'allow_remove' => false
                                                            )
                                                        );
                                                    },
                                                        $model,
                                                        $model_type['type'],
                                                        $model_files_type,
                                                        $f_type
                                                    );
                                                    ?>
                                                <?php endforeach; ?>

                                                <div class="d-popup-files-footer d-cb">
                                                    <a href="<?php echo url_for('@agreement_model_download_all_uploaded_files?id=' . $model->getId() . '&model_file_type=' . $model_files_type); ?>"
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
            <?php //endif; ?>

            <div class="d-popup-layout-meta">
                <?php if (!$model->isConcept()) { ?>
                    <div class="float-left">
                        <?php if ($model->getModelAcceptedInOnlineRedactor()): ?>
                            Выполнен при помощи онлайн-редактора
                        <?php endif; ?>
                    </div>
                <?php } ?>
                <div style="clear:both"></div>
            </div>

            <div class="d-popup-layout-meta">
                <?php if (!$model->isConcept()) { ?>
                    <?php if ($model->getNoModelChanges()): ?>
                        Изменения не вносились
                    <?php endif; ?>
                <?php } ?>
                <div style="clear:both"></div>
            </div>

        </div>

        <div class="d-popup-col">

            <div class="d-popup-req-title">
                <strong>Файл</strong>
            </div>

            <div class="file">
                <div class="modal-file-wrapper input">
                    <div id="model-files-progress-bar" class="progress-bar-content progress-bar-full-width"></div>
                </div>
            </div>

            <div class="scroller scroller-model" style="margin-bottom: 10px; height: 250px;">
                <div class="scrollbar">
                    <div class="track">
                        <div class="thumb">
                            <div class="end"></div>
                        </div>
                    </div>
                </div>
                <div class="viewport scroller-wrapper" style="height: 220px;">
                    <div class="overview scroller-inner">

                        <div class="file">
                            <div class="modal-file-wrapper input">
                                <div id="container_model_files" class="control dropzone" style="min-height: 150px;">
                                    <div class="d-popup-files-wrap scrollbar-inner">
                                        <div class="d-popup-files-row">
                                            <div id="model_files" class="d-popup-uploaded-files d-cb"></div>
                                            <input type="file" id="agreement_comments_file"
                                                   name="agreement_comments_file" multiple>
                                        </div>
                                    </div>

                                    <div class="d-popup-files-footer d-cb">
                                        <a href="javascript:" id="js-file-trigger-model"
                                           class="button js-d-popup-file-trigger"
                                           data-target="#agreement_comments_file">Прикрепить файл</a>
                                        <div id="model_files_caption" class="caption"
                                             style="position: relative; text-align: left; width: 60%;">Для выбора файлов
                                            нажмите на
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

            <?php $show = false; if ($show && !$model->isValidModelCategory() || $model->getModelCategory()->getWorkType() == AgreementModelCategories::WORK_TYPE_MANAGER): ?>
                <div class="specialists-panel-wrap">
                    <?php if ($model->getStatus() != "accepted"): ?>
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
                            <?php include_partial('agreement_activity_model_management/panel_specialists_block', array('specialist_groups' => $specialist_groups)) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="buttons">
                <?php if (!$model->getIsBlocked() || $model->getAllowUseBlocked()): ?>
                    <?php if ($model->getStatus() != 'accepted'): ?>
                        <!--<div class="specialists button float-left modal-form-button" style="margin-bottom: 5px;" data-model-type="model_simple"><a href="#" class="specialists">Отправить специалистам</a></div>-->
                    <?php endif; ?>

                    <?php if ($model->getStatus() != 'accepted' && ($model->getStatus() == "wait" || $model->getStatus() == "wait_specialist" || $model->getStatus() == 'wait_manager_specialist')): ?>
                        <?php if (!$model->isModelScenario()): ?>
                            <div class="accept green button modal-form-button float-right"
                                 data-model-type="model_simple"><a href="#" class="accept">Согласовать</a></div>
                        <?php else:

                            if (($model->getStatus() == "wait" || $model->getStatus() == "wait_specialist" || $model->getStatus() == 'wait_manager_specialist') && ($model->getStep1() == "wait" || $model->getStep1() == "none")): ?>
                                <div class="accept green button modal-form-button float-right"
                                     data-model-type="model_scenario"><a href="#" class="accept">Согласовать
                                        сценарий</a></div>
                            <?php endif;

                            if (($model->getStatus() == "wait" || $model->getStatus() == 'wait_manager_specialist') && $model->getStep1() == "accepted" && ($model->getStep2() == "wait" || $model->getStep2() == "none")): ?>
                                <div class="accept green button modal-form-button float-right"
                                     data-model-type="model_record"><a href="#" class="accept">Согласовать запись</a>
                                </div>
                            <?php endif; ?>

                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($model->getStatus() != 'declined'): ?>
                        <?php if (!$model->isModelScenario()): ?>
                            <div class="decline gray button modal-form-button float-left"
                                 data-model-type="model_simple"><a href="#" class="decline">Отклонить</a></div>
                        <?php else: ?>

                            <?php if ($model->getStatus() == "accepted" && $model->getStep1() == "accepted" && ($model->getStep2() == "accepted" || $model->getStep2() == "none")): ?>
                                <div class="decline gray button modal-form-button float-left"
                                     data-model-type="model_simple"><a href="#" class="decline">Отклонить</a></div>
                            <?php endif; ?>

                            <?php if ($model->getStatus() != "accepted" && ($model->getStep1() == "wait" || $model->getStatus() == 'wait_manager_specialist' || $model->getStep1() == "none")): ?>
                                <div class="decline gray button  modal-form-button float-left" data-step="first"
                                     data-model-type="model_scenario"><a href="#" class="decline">Отклонить сценарий</a>
                                </div>
                            <?php endif;

                            if (($model->getStatus() == "wait" || $model->getStatus() == 'wait_specialist') && $model->getStep1() == "accepted" && ($model->getStep2() == "wait" || $model->getStatus() == 'wait_manager_specialist' || $model->getStep2() == "none")): ?>
                                <div class="decline gray button  modal-form-button float-left" data-step="second"
                                     style="margin-top: 5px;"
                                     data-model-type="model_record"><a href="#" class="decline">Отклонить запись</a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div style="text-align:center;">
                        <a href="<?php echo url_for('@discussion_switch_to_dealer?dealer=' . $model->getDealerId() . '&activityId=' . $model->getActivityId() . '&modelId=' . $model->getId()); ?>"
                           target='_blank' class="link">Перейти <br/>в активность</a></div>

                <?php else: ?>
                    <?php
                    $blocked_to = strtotime($model->getUseBlockedTo());
                    if (!empty($blocked_to) && strtotime(date('d-m-Y H:i:s')) < $blocked_to): ?>
                        <div class="msg" style="background: #c4e6c8; width: 100%; margin: 10px;">Заявка разблокирована
                            до: <?php echo date('d-m-Y H:i:s', $blocked_to); ?></div>
                    <?php else: ?>
                        <div class="dummy gray msg modal-form-button">Заявка заблокирована</div>
                        <div class='out-of-date' data-out='true'></div>
                    <?php endif; ?>

                    <div style="text-align:center;"><a
                            href="<?php echo url_for('@discussion_switch_to_dealer?dealer=' . $model->getDealerId() . '&activityId=' . $model->getActivityId() . '&modelId=' . $model->getId()); ?>"
                            target='_blank' class="link">Перейти <br/>в активность</a></div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</form>

