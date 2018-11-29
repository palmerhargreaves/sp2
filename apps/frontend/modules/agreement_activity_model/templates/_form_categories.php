<?php if (Utils::allowedIps()): ?>
<form action="<?php echo url_for("@agreement_module_models_add?activity={$activity->getId()}") ?>"
      class="form-horizontal" method="post" id="model-categories-form" target="model-target">
    <?php else: ?>
    <form action="<?php echo url_for("@agreement_module_models_add?activity={$activity->getId()}") ?>"
          class="form-horizontal" method="post" id="model-categories-form" target="model-target">
        <?php endif; ?>
        <input type="hidden" name="id"/>
        <input type="hidden" name="blank_id"/>
        <input type="hidden" name="draft" value="false"/>
        <input type="hidden" name="necessarily_id" value="0"/>

        <div class="d-popup-cols model-form">
            <div class="d-popup-col">
                <div class="d-popup-req-title number-field"><strong style="width: 3%; margin-top: 7px;">№</strong>
                    <div class="value"></div>
                </div>

                <table class="d-popup-tbl-params">
                    <tbody>
                    <tr class="model-mode-field activity">
                        <td class="label">Активность</td>
                        <td class="field controls model-type">
                            <div class="modal-select-wrapper select krik-select">
                                <span class="select-value"><?php echo $activity->getName() ?></span>
                                <div class="ico"></div>
                                <input type="hidden" name="activity_id" value="<?php echo $activity->getId() ?>">
                                <div class="modal-input-error-icon error-icon"></div>
                                <div class="error message"></div>
                                <div class="modal-select-dropdown">
                                    <?php foreach ($forms_activities as $actItem):
                                        if (!$actItem->isActiveForUser($sf_user->getRawValue()->getAuthUser()))
                                            continue;
                                        ?>
                                        <div style='height:auto; padding: 7px;'
                                             class="modal-select-dropdown-item select-item"
                                             data-value="<?php echo $actItem->getId() ?>"><?php echo sprintf('%s', $actItem->getName()); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="value value-activity"><?php echo $activity->getName(); ?></div>
                        </td>
                    </tr>
                    <tr class="model-mode-field">
                        <td class="label">Задача</td>
                        <td class="field controls model-type">
                            <?php $tasks = $activity->getTasks(); ?>

                            <?php if (count($tasks)): ?>
                            <div class="modal-select-wrapper select input krik-select">
                                <span class="select-value select-value-model-task"><?php echo $tasks->getFirst()->getName() ?></span>
                                <div class="ico"></div>
                                <input type="hidden" name="task_id" value="<?php echo $tasks->getFirst()->getId() ?>">
                                <div class="modal-input-error-icon error-icon"></div>
                                <div class="error message"></div>
                                <div class="modal-select-dropdown">
                                    <?php foreach ($tasks as $task): ?>
                                        <div style='height:auto; padding: 7px;'
                                             class="modal-select-dropdown-item select-item"
                                             data-value="<?php echo $task->getId() ?>"><?php echo $task->getName(); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="value"></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($activity->getAllowShareName()): ?>
                        <tr class="model-mode-field">
                            <td class="label">Название акции</td>
                            <td class="field controls">
                                <div class="modal-input-wrapper input">
                                    <input type="text" value="" name="share_name" placeholder="Название акции"
                                           data-required="true">
                                    <div class="modal-input-error-icon error-icon"></div>
                                    <div class="error message"></div>
                                </div>
                                <div class="value"></div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="label">Категория</td>
                        <td class="field controls model-type">
                            <div class="modal-select-wrapper select input krik-select">
                            <span
                                    class="select-value select-value-model-category"></span>
                                <div class="ico"></div>
                                <input type="hidden" name="model_category_id" data-is-blank="0" value="">
                                <div class="modal-input-error-icon error-icon"></div>
                                <div class="error message"></div>
                                <div class="modal-select-dropdown">
                                    <?php foreach ($model_categories as $category): ?>
                                        <div class="modal-select-dropdown-item select-item select-model-category-item-<?php echo $category->getId(); ?>"
                                             data-value="<?php echo $category->getId() ?>"><?php echo $category->getName() ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="value"></div>
                        </td>
                    </tr>

                    <tr class="model-category-type-fields" style="display: none;">
                        <td class="label">Тип</td>
                        <td class="field controls model-type">
                            <div id="model-category-types-container">

                            </div>
                        </td>
                    </tr>

                    <tr class="model-mode-field">
                        <td class="label">Название материала</td>
                        <td class="field controls">
                            <div class="modal-input-wrapper input">
                                <input type="text" value="" name="name" placeholder="Листовка" data-required="true">
                                <div class="modal-input-error-icon error-icon"></div>
                                <div class="error message"></div>
                            </div>
                            <div class="value"></div>
                        </td>
                    </tr>

                    <?php foreach ($model_categories_fields as $id => $fields): ?>
                        <?php foreach ($fields as $renderer):
                            if ($renderer->getField()->getIdentifier() == "size") {
                                $editorLink = $sf_user->getAttribute('editor_link');
                                if (isset($editorLink)) {
                                    $renderer->setEditorLink(true);
                                }
                            }

                            ?>
                            <tr class="type-fields type-fields-<?php echo $id ?>"
                                data-id="<?php echo $renderer->getField()->getId(); ?>"
                                data-is-hide="<?php echo $renderer->getField()->getHide(); ?>"
                                <?php echo $renderer->getField()->getHide() ? "data-hide-field=" . $id : ''; ?>>
                                <td class="label">
                                    <?php if ($renderer->getField()->getChildField() && $renderer->getField()->canAddChildFields()): ?>
                                        <div class="js-add-child-field d-popup-btn-add"
                                             data-parent-id="<?php echo $renderer->getField()->getParentCategoryId(); ?>"></div>
                                    <?php endif; ?>

                                    <?php echo $renderer->getField()->getName() ?><?php if ($renderer->getField()->getUnits()): ?>, <?php echo $renderer->getField()->getUnits() ?><?php endif; ?>
                                </td>
                                <td class="field controls">
                                    <div class="d-pr">
                                        <div class="input">
                                            <?php echo $renderer->getRawValue()->render(); ?>
                                        </div>
                                        <div class="value"></div>
                                        <?php if ($renderer->getField()->getType() == "period" && $sf_user->getRawValue()->getAuthUser()->isSuperAdmin()): ?>
                                            <div
                                                    class="d-popup-btn-edit change-period change-period-model-type-<?php echo $renderer->getField()->getModelTypeId(); ?>"
                                                    data-action="change"
                                                    data-parent-id="<?php echo $renderer->getField()->getModelTypeId(); ?>"
                                                    data-field-id="<?php echo $renderer->getField()->getId(); ?>">
                                                Изменить
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($renderer->getField()->getChildField()): ?>
                                            <div class="d-popup-btn-del" data-action=""
                                                 data-parent-id="<?php echo $renderer->getField()->getModelTypeId(); ?>"
                                                 data-field-id="<?php echo $renderer->getField()->getId(); ?>"
                                                 title="Удалить"></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>

                    <tr>
                        <td class="label">Период</td>
                        <td class="field controls">
                            <div class="d-pr">
                                <div class="input">
                                    <div class="modal-input-group-wrapper period-group" id="period">
                                        <input type="hidden" name="period"/>
                                        <div class="modal-input-wrapper modal-short-input-wrapper">
                                            <input type="text" name="_period[start]" class="date-ext date"
                                                   placeholder="от"
                                                   data-format-expression="^[0-9]{2}(\.[0-9]{2}){2}$" data-required="1"
                                                   data-right-format="21.01.13"
                                                   data-date-field="true"
                                                   data-message-selector="#end_field_id_1"/>
                                            <div class="modal-input-error-icon error-icon"></div>
                                        </div>
                                        <div class="modal-input-wrapper modal-short-input-wrapper">
                                            <input type="text" name="_period[end]" class="date-ext date"
                                                   placeholder="до"
                                                   data-format-expression="^[0-9]{2}(\.[0-9]{2}){2}$" data-required="1"
                                                   data-date-field="true"
                                                   data-right-format="21.01.13"/>
                                            <div class="modal-input-error-icon error-icon"></div>
                                            <div class="error message" id="end_field_id_1"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="value"></div>
                                <div
                                        class="d-popup-btn-edit js-change-model-period"
                                        data-action="change"
                                        data-model-id="">Изменить
                                </div>
                            </div>
                        </td>
                    </tr>

                    <?php if ($activity->getAllowCertificate() && $sf_user->getAuthUser()->getDealerUsers()->count() > 0):
                        $activityConcepts = AgreementModelDatesTable::getInstance()
                            ->createQuery()
                            ->where('activity_id = ?', $activity->getId())
                            ->andWhere('dealer_id = ?', $sf_user->getAuthUser()->getDealerUsers()->getFirst()->getDealerId())
                            ->execute();

                        if (count($activityConcepts) > 0): ?>
                            <tr class="model-mode-field">
                                <td class="label">Привязать заявку к мероприятию</td>
                                <td class="field controls">
                                    <div class="modal-select-wrapper select input krik-select">
                                    <span
                                            class="select-value select-value-model-concept"><?php echo $activityConcepts->getFirst()->getModelId() ?></span>
                                        <div class="ico"></div>
                                        <input type="hidden" name="concept_id"
                                               value="<?php echo $activityConcepts->getFirst()->getModelId() ?>">
                                        <div class="modal-input-error-icon error-icon"></div>
                                        <div class="error message"></div>
                                        <div class="modal-select-dropdown">
                                            <?php foreach ($activityConcepts as $concept):
                                                $formatDate = '';

                                                $dateOf = $concept->getDateOf();
                                                $dateOf = explode('/', $dateOf);
                                                if (count($dateOf) > 1) {
                                                    $formatDate = sprintf(': %s - %s',
                                                        date('d-m-Y', strtotime($dateOf[0])),
                                                        date('d-m-Y', strtotime($dateOf[1])));

                                                    ?>

                                                    <?php
                                                    $event_name = $activity->getEventName();
                                                    if (empty($event_name)) {
                                                        $event_name = $activity->getName();
                                                    }
                                                    ?>
                                                    <div class="modal-select-dropdown-item select-item"
                                                         data-value="<?php echo $concept->getModelId() ?>">
                                                        <?php echo $event_name; ?><?php echo $formatDate; ?>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="value"></div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>

                    <tr class="model-mode-field">
                        <td class="label">Сумма, руб. (без НДС)</td>
                        <td class="field controls">
                            <div class="modal-input-wrapper modal-short-input-wrapper input">
                                <input type="text" value="" name="cost" placeholder="40 000"
                                       data-format-expression="^[0-9]+(\.[0-9]+)?$" data-required="true"
                                       data-right-format="100.00">
                                <div class="modal-input-error-icon error-icon"></div>
                                <div class="error message"></div>
                            </div>
                            <div class="value"></div>
                        </td>
                    </tr>
                    <tr class="model-mode-field">
                        <td class="label">Пролонгация заявки<br/>
                            <? /*
						<div class="modal-input-wrapper" style="border: none;">
							<span style='float: right; cursor: pointer; font-weight: normal; font-size: 11px; text-decoration: underline;' class='what-info'>Что это?</span>
							<div class="modal-input-error-icon error-icon"></div>
							<div class="error message" style="display: none; z-index: 999;"></div>
						</div>
*/ ?>
                        </td>
                        <td class="field controls">
                            <div class="modal-input-wrapper modal-short-input-wrapper input">
                                <input type="text" value="" name="accept_in_model" placeholder="Номер" maxlength="5"
                                       data-format-expression="^[0-9]+(\.[0-9]+)?$" data-required="false">
                                <div class="modal-input-error-icon error-icon"></div>
                                <div class="error message"></div>
                            </div>
                            <div class="value"></div>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>

            <div class="d-popup-col model-file-uploader-block">
                <div class="d-popup-req-title model-title">
                    <strong>Макет</strong>
                </div>

                <?php if (!$sf_user->getAttribute('editor_link')): ?>
                    <div class="file">
                        <div class="modal-file-wrapper input">
                            <div id="model-files-progress-bar"
                                 class="progress-bar-content progress-bar-full-width"></div>
                            <span id="js-file-trigger-model" class="btn js-file-trigger">Прикрепить файл</span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="scroller scroller-model">
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
                                    <div id="container_model_files" class="control dropzone" style="min-height: 158px">
                                        <?php if (!$sf_user->getAttribute('editor_link')): ?>
                                            <div class="d-popup-files-wrap scrollbar-inner">
                                                <div class="d-popup-files-row">
                                                    <div id="model_files" class="d-popup-uploaded-files d-cb"></div>
                                                </div>
                                            </div>

                                            <div id="model_files_caption" class="caption">Для выбора файлов нажмите на
                                                кнопку
                                                или перетащите
                                                их сюда
                                            </div>
                                            <input type="file" id="model_file_category" name="model_file_category"
                                                   style="height: auto;" multiple
                                                   title="">
                                        <?php elseif ($sf_user->getAttribute('editor_link')): ?>
                                            <?php include_partial('file_item_block'); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-input-error-icon error-icon"></div>
                                    <div class="error message"></div>
                                </div>

                                <div class="value file-name"></div>
                                <div class="clear"></div>
                                <div class="modal-form-uploaded-file"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="model-record-block" style="display: none;">
                    <div class="d-popup-req-title file-label-record">
                        <strong>Макет</strong>
                    </div>

                    <div class="file">
                        <div class="modal-file-wrapper input">
                            <div id="model-record-files-progress-bar"
                                 class="progress-bar-content progress-bar-full-width"></div>
                            <span id="js-file-trigger-model-record"
                                  class="btn js-file-trigger">Прикрепить файл</span>
                        </div>
                    </div>

                    <div class="scroller scroller-model-record">
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
                                        <div id="container_model_record_files" class="control dropzone"
                                             style="min-height: 158px">
                                            <div class="d-popup-files-wrap scrollbar-inner">
                                                <div class="d-popup-files-row">
                                                    <div id="model_record_files"
                                                         class="d-popup-uploaded-files d-cb"></div>
                                                </div>
                                            </div>

                                            <div id="model_record_files_caption" class="caption">Для выбора файлов
                                                нажмите
                                                на кнопку или
                                                перетащите их сюда
                                            </div>
                                            <input type="file" id="model_record_file" name="model_record_file" title=""
                                                   style="height: auto;" multiple>
                                        </div>
                                        <div class="modal-input-error-icon error-icon"></div>
                                        <div class="error message"></div>
                                    </div>

                                    <div class="value file-name"></div>
                                    <div class="clear"></div>
                                    <div class="modal-form-uploaded-file"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <table class="d-popup-tbl-opts">
                    <tbody>
                    <tr>
                        <td><label for="layout-not-edited"><strong>Я скачал (-а) макет и не менял (-а) в нём
                                    информацию</strong></label></td>
                        <td><input id="layout-not-edited" type="checkbox" name="no_model_changes"
                                   data-required="false"/>
                        </td>
                    </tr>

                    <?php if ($sf_user->getAttribute('editor_link')): ?>
                        <tr>
                            <td><label for="layout-online-editor"><strong>Макет выполнен при помощи
                                        онлайн-редактора</strong></label></td>
                            <td><input id="layout-online-editor" type="checkbox"
                                       name="model_accepted_in_online_redactor"
                                       data-required="false" <?php echo $sf_user->getAttribute('editor_link') ? "checked" : ""; ?> <?php echo $sf_user->getAttribute('editor_link') ? "disabled='disabled'" : ""; ?> />
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>

                <div class="buttons">
                    <div class="float-left gray draft button modal-form-button draft-btn">Сохранить как черновик</div>
                    <button id="accept-model-button-with-category"
                            class="float-right button modal-zoom-button modal-form-button modal-form-submit-button submit-btn accept-from-draft"
                            type="submit"><span>Отправить</span></button>
                    <div style="margin: auto;" class="delete gray button delete-btn">Удалить заявку</div>
                    <div style="margin: auto;" class="cancel gray button cancel-btn">Отменить отправку</div>

                    <div style="margin: auto; margin-top: 5px; width: 195px !important;"
                         class="cancel gray button cancel-btn-scenario">Отменить отправку сценария
                    </div>
                    <div style="margin: auto; margin-top: 5px; width: 195px !important;"
                         class="cancel gray button cancel-btn-record">Отменить отправку записи
                    </div>

                    <div class="dummy gray msg modal-form-button" style="display: none;">Заявка заблокирована</div>
                </div>

            </div>

        </div>

        <input type="hidden" name="<?php echo session_name(); ?>" value="<?php echo session_id(); ?>">
        <input type="hidden" name="upload_file_object_type" value=""/>
        <input type="hidden" name="upload_file_type" value=""/>
        <input type="hidden" name="upload_field" value=""/>
        <input type="hidden" name="upload_files_ids" value=""/>
        <input type="hidden" name="upload_files_records_ids" value=""/>
    </form>

    <iframe src="/blank.html" width="1" height="1" frameborder="0" hspace="0" marginheight="0" marginwidth="0"
            name="model-target" scrolling="no"></iframe>
