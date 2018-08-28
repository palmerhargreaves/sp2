<?php $totalFiles = sfConfig::get('app_max_files_upload_count'); ?>
<?php if (Utils::allowedIps()): ?>
<form action="/" method="post" enctype="multipart/form-data" target="accept-frame"  id="agreement-model-specialist-form">
    <?php else: ?>
    <form action="/" method="post" enctype="multipart/form-data" target="accept-frame" id="agreement-model-specialist-form">
    <?php endif; ?>
    <input type="hidden" name="id"/>
    <input type="hidden" name="action_type"/>

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

                <?php if (!$model->isConcept()): ?>
                    <tr>
                        <td class="label">
                            Номер
                        </td>
                        <td class="field controls">
                            <?php echo $model->getId() ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td class="label">
                        Дилер
                    </td>
                    <td class="field controls">
                        <?php echo $model->getDealer()->getName() ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">
                        Активность
                    </td>
                    <td class="field controls">
                        <?php echo $model->getActivity()->getName() ?>
                    </td>
                </tr>

                <?php if ($model->isConcept()): ?>
                    <?php include_partial('model_dates', array('concept' => $model)); ?>
                <?php endif; ?>

                <?php if (!$model->isConcept()): ?>
                    <tr>
                        <td class="label">
                            Название
                        </td>
                        <td class="field controls">
                            <?php echo $model->getName() ?>
                        </td>
                    </tr>
                    <?php if ($model->isValidModelCategory()): ?>
                        <tr>
                            <td class="label">Категория</td>
                            <td class="field controls">
                                <?php echo $model->getModelCategory()->getName() ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="label">
                            Тип размещения
                        </td>
                        <td class="field controls">
                            <?php echo $model->getModelType()->getName() ?>
                        </td>
                    </tr>
                    <?php if (!$model->isValidModelCategory()): ?>
                    <tr>
                        <td class="label">
                            Цель
                        </td>
                        <td class="field controls">
                            <?php echo $model->getTarget() ?>
                        </td>
                    </tr>
                        <?php endif; ?>
                <?php endif; ?>

                <?php $fields = $model->isValidModelCategory() ? $model->getModelCategory()->getCategoryFields() : $model->getModelType()->getFields() ; ?>

                <?php foreach ($fields as $field):
                    $val = $model->getValueByType($field->getIdentifier());
                    if (!empty($val)):
                        ?>
                        <tr class="<?php echo $field->getHide() == 1 ? "ext-type-field" : ""; ?> type-fields-<?php echo $field->getModelTypeId(); ?>"
                            data-field-type="<?php echo $field->getModelTypeId(); ?>"
                            data-is-hide="<?php echo $field->getHide(); ?>">
                            <td class="label">
                                <?php echo $field->getName() ?><?php if ($field->getUnits()): ?>, <?php echo $field->getUnits() ?><?php endif; ?>
                            </td>
                            <td class="field controls">
                                <?php echo $model->getValueByType($field->getIdentifier()) ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                <?php endforeach; ?>
                <?php if (!$model->isConcept()): ?>
                    <tr>
                        <td class="label">
                            Сумма
                        </td>
                        <td class="field controls">
                            <?php echo $model->getCost() ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>

            <div class="d-popup-files-wrap scrollbar-inner">
                <div class="d-popup-files-row">
                    <?php foreach ($model->getUploadedFilesSchemaByType() as $model_files_type => $model_type): ?>
                        <?php if ($model_type['show']): ?>
                            <div style="margin-top: 15px;">
                                <?php $header = $model_type['label']; ?>
                                <label><?php echo $header; ?></label>

                                <?php $normalized_header = Utils::normalize($header); ?>
                                <div class="d-popup-uploaded-files d-cb"
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
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="d-popup-col">
            <?php if ($model->getStatus() != 'accepted'): ?>
                <div class="d-popup-req-title">
                    <strong>Файл</strong>
                </div>

                <div class="file">
                    <div class="modal-file-wrapper input">
                        <div id="model-files-progress-bar" class="progress-bar-content progress-bar-full-width"></div>
                    </div>
                </div>

                <div class="scroller scroller-model" style="margin-bottom: 10px;">
                    <div class="scrollbar">
                        <div class="track">
                            <div class="thumb">
                                <div class="end"></div>
                            </div>
                        </div>
                    </div>
                    <div class="viewport scroller-wrapper">
                        <div class="overview scroller-inner">
                            <div class="file">
                                <div class="modal-file-wrapper input">
                                    <div id="container_model_files" class="control dropzone">
                                        <div class="d-popup-files-wrap scrollbar-inner">
                                            <div class="d-popup-files-row">
                                                <div id="model_files" class="d-popup-uploaded-files d-cb"></div>
                                                <input type="file" id="agreement_comments_file"
                                                       name="agreement_comments_file"
                                                       multiple>
                                            </div>
                                        </div>

                                        <div class="d-popup-files-footer d-cb">
                                            <a href="javascript:" id="js-file-trigger-model"
                                               class="button js-d-popup-file-trigger"
                                               data-target="#agreement_comments_file">Прикрепить файл</a>
                                            <div id="model_files_caption" class="caption"
                                                 style="position: relative; text-align: left; width: 60%;">Для выбора
                                                файлов нажмите
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

                <?php if ($sf_user->isSpecialist()): ?>
                    <div class="buttons" style="width: 300px;">
                        <div style="width: 105px;"
                             class="accept green button float-left modal-form-button send-btn accept-accept-btn submit-btn" data-model-type="model_simple">
                            Утвердить
                        </div>
                        <div style="width: 105px;" class="decline gray button float-right modal-form-button cancel-btn" data-model-type="model_simple">
                            Отколнить
                        </div>
                        <div class="clear"></div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</form>
