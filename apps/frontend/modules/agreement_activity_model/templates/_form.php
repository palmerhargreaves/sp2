<?php if (Utils::allowedIps()): ?>
<form action="<?php echo url_for("@agreement_module_models_add?activity={$activity->getId()}") ?>"
              class="form-horizontal" method="post" id="model-form" target="model-target">
    <?php else: ?>
<form action="<?php echo url_for("@agreement_module_models_add?activity={$activity->getId()}") ?>"
      class="form-horizontal" method="post" id="model-form" target="model-target">
    <?php endif; ?>

    <input type="hidden" name="id"/>
    <input type="hidden" name="blank_id"/>
    <input type="hidden" name="draft" value="false"/>
    <input type="hidden" name="necessarily_id" value="0"/>

    <?php $blank_category = AgreementModelCategoriesTable::blankCategory(); ?>
    <input type="hidden" name="model_category_id" data-is-blank="1"
           data-blank-category-id="<?php echo $blank_category ? $blank_category->getId() : 0; ?>"
           value="<?php echo $blank_category ? $blank_category->getId() : 0; ?>"/>


    <div class="concept-form">
        <p class="description">Загрузите сюда концепцию проведения данной акции в вашем дилерском центре.<br/>
            Файл должен содержать полную информацию по организации и проведению акции.</p>

        <div class="requirements">
            <ul>
                <li>Схема проведения акции</li>
                <li>Период действия акции</li>
                <li>Рекламные материалы для анонса</li>
                <li>Оформление дилерского центра</li>
                <li>Мотивация сотрудников предприятия в период проведения акции (необязательный пункт)</li>
                <li>Подарки клиентам</li>
                <li>Ожидаемый эффект</li>
            </ul>
        </div>

        <div class="d-popup-cols">
            <div class="d-popup-col">
                <div class="d-popup-req-title number-field"><strong>№</strong>
                    <div class="value"></div>
                </div>

                <div>
                    <?php if ($activity->getAllowCertificate() || $activity->getAllowSpecialAgreement()): ?>
                        <table class="model-concept-form" style="width: 100%;">
                            <?php include_partial('certificate_fields'); ?>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-popup-col">
                <div class="controls field">
                    <div class="d-popup-req-title model-title">
                        <strong>Макет</strong>
                    </div>

                    <div class="file">
                        <div class="modal-file-wrapper input">
                            <div id="concept-files-progress-bar"
                                 class="progress-bar-content progress-bar-full-width"></div>
                            <span id="js-file-trigger-concept" class="btn js-file-trigger">Прикрепить файл</span>
                        </div>
                    </div>

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
                                    <div class="modal-file-wrapper ">
                                        <div id="container_model_files" class="control dropzone">
                                            <div class="d-popup-files-wrap scrollbar-inner">
                                                <div class="d-popup-files-row">
                                                    <div id="concept_files"
                                                         class="d-popup-uploaded-files d-cb"></div>
                                                </div>
                                            </div>

                                            <div id="concept_files_caption" class="caption">Для выбора файлов
                                                нажмите на
                                                кнопку или перетащите
                                                их сюда
                                            </div>
                                            <input type="file" id="concept_file" name="model_file" multiple>
                                        </div>
                                        <div class="modal-input-error-icon error-icon"></div>
                                        <div class="error message"></div>
                                    </div>
                                    <?php if ($sf_user->getAttribute('editor_link')): ?>
                                        <div><a href='<?php echo $sf_user->getAttribute('editor_link'); ?>'
                                                target='_blank'><?php echo $sf_user->getAttribute('editor_link'); ?></a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="value file-name"></div>
                                    <div class="clear"></div>
                                    <div class="modal-form-uploaded-file"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="buttons">
                    <button
                        class="float-left button modal-zoom-button modal-form-button modal-form-submit-button submit-btn accept-from-draft"
                        type="submit"><span>Отправить</span></button>
                    <div class="float-right gray draft button modal-form-button draft-btn">Сохранить как черновик
                    </div>
                    <div style="margin: auto;" class="delete gray button delete-btn">Удалить</div>
                    <div style="margin: auto;" class="cancel gray button cancel-btn">Отменить отправку</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-popup-cols model-form">
        <div class="d-popup-col">
            <div class="d-popup-req-title number-field"><strong>№</strong>
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
                                         data-value="<?php echo $actItem->getId() ?>"><?php echo $actItem->getName() ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="value value-activity"><?php echo $activity->getName(); ?></div>
                    </td>
                </tr>

                <?php $tasks = $activity->getTasks(); ?>
                <tr class="model-mode-field">
                    <td class="label">Задача</td>
                    <?php if (count($tasks) > 1): ?>
                        <td class="field controls model-type">
                            <div class="modal-select-wrapper select input krik-select">
                                <span class="select-value"><?php echo $tasks->getFirst()->getName() ?></span>
                                <div class="ico"></div>
                                <input type="hidden" name="task_id"
                                       value="<?php echo $tasks->getFirst()->getId() ?>">
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
                        </td>
                    <?php else: ?>
                        <?php if (count($tasks)): ?>
                            <td class="field controls">
                                <input type="hidden" name="task_id" value="<?php echo $tasks->getFirst()->getId() ?>">
                                <div class="value-task"><?php echo $tasks->getFirst()->getName() ?></div>
                            </td>
                        <?php endif; ?>
                    <?php endif; ?>
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
                <tr class="model-mode-field">
                    <td class="label">Тип</td>
                    <td class="field controls model-type">
                        <div class="modal-select-wrapper select input krik-select">
                            <span
                                class="select-value select-value-model-type"><?php echo $model_types->getFirst()->getName() ?></span>
                            <div class="ico"></div>
                            <input type="hidden" name="model_type_id"
                                   value="<?php echo $model_types->getFirst()->getId() ?>"
                                   data-is-sys-admin="<?php echo $sf_user->getRawValue()->getAuthUser()->isSuperAdmin() ? 1 : 0; ?>">
                            <div class="modal-input-error-icon error-icon"></div>
                            <div class="error message"></div>
                            <div class="modal-select-dropdown">
                                <?php foreach ($model_types as $type): ?>
                                    <div
                                        class="modal-select-dropdown-item select-item select-model-type-item-<?php echo $type->getId(); ?>"
                                        data-value="<?php echo $type->getId() ?>"><?php echo $type->getName() ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="value"></div>
                    </td>
                </tr>
                <tr class="model-mode-field">
                    <td class="label">Цель</td>
                    <td class="field controls">
                        <div class="modal-input-wrapper input">
                            <input type="text" value="" name="target" placeholder="" data-required="true">
                            <div class="modal-input-error-icon error-icon"></div>
                            <div class="error message"></div>
                        </div>
                        <div class="value"></div>
                    </td>
                </tr>
                <?php $fieldTypes = array(1 => 'Добавить адрес размещения', 2 => 'Добавить станцию', 6 => 'Добавить издание и номер', 3 => 'Добавить место размещения', 4 => 'Добавить канал');
                foreach ($model_types_fields as $id => $fields): ?>
                    <?php foreach ($fields as $renderer):
                        if ($renderer->getField()->getIdentifier() == "size") {
                            $editorLink = $sf_user->getAttribute('editor_link');
                            if (isset($editorLink)) {
                                $renderer->setEditorLink(true);
                            }
                        }
                        ?>
                        <tr class="type-fields type-fields-<?php echo $id ?>"
                            data-is-hide="<?php echo $renderer->getField()->getHide(); ?>">
                            <td class="label">
                                <?php if ($renderer->getField()->getChildField()): ?>
                                    <div class="js-add-child-field d-popup-btn-add"
                                         title="<?php echo $fieldTypes[$renderer->getField()->getModelTypeId()]; ?>"
                                         data-model-id="<?php echo $renderer->getField()->getModelTypeId(); ?>"></div>
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
                                            data-model-id="<?php echo $renderer->getField()->getModelTypeId(); ?>"
                                            data-field-id="<?php echo $renderer->getField()->getId(); ?>">Изменить
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($renderer->getField()->getChildField()): ?>
                                        <div class="d-popup-btn-del" data-action=""
                                             data-model-id="<?php echo $renderer->getField()->getModelTypeId(); ?>"
                                             data-field-id="<?php echo $renderer->getField()->getId(); ?>"
                                             title="Удалить"></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <?php if ($activity->getAllowCertificate() && $sf_user->getAuthUser()->getDealerUsers()->count() > 0):
                    $activityConcepts = AgreementModelDatesTable::getInstance()
                        ->createQuery()
                        ->where('activity_id = ?', $activity->getId())
                        ->andWhere('dealer_id = ?', $sf_user->getAuthUser()->getDealerUsers()->getFirst()->getDealerId())
                        ->execute();

                    $conceptModelId = count($activityConcepts) && $activityConcepts->getFirst()->getModelId() ? $activityConcepts->getFirst()->getModelId() : 0;
                    //if (count($activityConcepts) > 0):
                    ?>
                    <tr class="model-mode-field">
                        <td class="label">Привязать заявку к мероприятию</td>
                        <td class="field controls">
                            <div class="modal-select-wrapper select input krik-select">
                                    <span
                                        class="select-value select-value-model-concept"><?php echo $conceptModelId; ?></span>
                                <div class="ico"></div>
                                <input type="hidden" name="concept_id" value="<?php echo $conceptModelId; ?>">
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

                                            <div class="modal-select-dropdown-item select-item"
                                                 data-value="<?php echo $concept->getModelId() ?>">
                                                Service Clinic <?php echo $formatDate; ?>
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
                    <?php //endif;
                    ?>
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

            <div class="file">
                <div class="modal-file-wrapper input">
                    <div id="model-files-progress-bar" class="progress-bar-content progress-bar-full-width"></div>
                    <?php if (!$sf_user->getAttribute('editor_link')): ?>
                        <span id="js-file-trigger-model" class="btn js-file-trigger">Прикрепить файл</span>
                    <?php endif; ?>
                </div>
            </div>

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
                            <?php if (!$sf_user->getAttribute('editor_link')): ?>
                                <div class="modal-file-wrapper input">
                                    <div id="container_model_files" class="control dropzone"
                                         style="min-height: 158px">
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
                                        <input type="file" id="model_file" name="model_file" style="height: auto;"
                                               multiple title="">
                                    </div>
                                    <div class="modal-input-error-icon error-icon"></div>
                                    <div class="error message"></div>
                                </div>
                            <?php else: ?>
                                <div class="modal-file-wrapper input">
                                    <span class="d-popup-uploaded-file" data-delete="false">
                                        <i><b><img src="<?php echo $sf_user->getAttribute('editor_link'); ?>"/></b></i>
                                        <?php $fileSize = Utils::getRemoteFileSize($sf_user->getAttribute('editor_link')); ?>

                                        <strong>
                                            <a target="_blank"
                                               href="<?php echo $sf_user->getAttribute('editor_link'); ?>">
                                                <?php echo $sf_user->getAttribute('editor_link'); ?>
                                            </a>
                                        </strong>
                                        <em><?php echo $fileSize; ?></em>
                                    </span>
                                </div>
                            <?php endif; ?>
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
                        <span id="js-file-trigger-model-record" class="btn js-file-trigger">Прикрепить файл</span>
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
                <tr>
                    <td><label for="layout-online-editor"><strong>Макет выполнен при помощи
                                онлайн-редактора</strong></label></td>
                    <td><input id="layout-online-editor" type="checkbox" name="model_accepted_in_online_redactor"
                               value="1"
                               data-required="false" <?php echo $sf_user->getAttribute('editor_link') ? "checked" : ""; ?> />
                    </td>
                </tr>
                </tbody>
            </table>

            <div class="buttons">
                <div class="float-left gray draft button modal-form-button draft-btn">Сохранить как черновик</div>
                <button
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
