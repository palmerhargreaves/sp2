<?php if (getenv('REMOTE_ADDR') == '46.175.166.61'): ?>
<form action="<?php echo url_for("@agreement_module_models_report_update?activity={$activity->getId()}") ?>"
          class="form-horizontal" method="post" id="report-categories-form" target="report-target">
    <?php else: ?>
    <form action="<?php echo url_for("@agreement_module_models_report_update?activity={$activity->getId()}") ?>"
          class="form-horizontal" method="post" id="report-categories-form" target="report-target">
    <?php endif; ?>

    <input type="hidden" name="id"/>

    <div class="d-popup-cols concept-form">
        <div class="d-popup-col">
            <p class="description">Загрузите сюда отчет по результатам проведения данной акции в вашем дилерском
                центре.</p>
            <div class="requirements">
                Отчет должен содержать полную информацию об активности вашего дилерского центра в рамках проведениях
                данной
                акции и оценку эффективности проведенной кампании.<br/><br/>
                <strong>Внимание! Отчет по итогам акции загружается после ее проведения и согласования всех рекламных
                    материалов.</strong>
                <br>
                Максимальный размер файла <?php echo F::getSmartSize(sfConfig::get('app_max_upload_size'), 0) ?>.
            </div>

            <div class="d-popup-files-wrap scrollbar-inner">
                <div class="d-popup-files-row">
                    <div class="file">
                        <div class="modal-file-wrapper input">
                            <div id="concept-report-files-progress-bar"
                                 class="progress-bar-content progress-bar-full-width"></div>
                        </div>
                    </div>

                    <div class="scroller scroller-add-fin">
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
                                        <div id="container_concept_report_files" class="control dropzone"
                                             style="min-height: 294px">
                                            <div class="d-popup-files-wrap scrollbar-inner">
                                                <div class="d-popup-files-row">
                                                    <div id="concept_report_files"
                                                         class="d-popup-uploaded-files d-cb"></div>
                                                </div>
                                            </div>

                                            <div class="caption">Для выбора файлов нажмите на
                                                кнопку или
                                                перетащите их сюда
                                            </div>
                                            <input type="file" name="concept_report_file" id="concept_report_file" multiple />
                                        </div>
                                        <div class="modal-input-error-icon error-icon"></div>
                                        <div class="error message"></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="d-popup-uploaded-files d-cb" style="padding: 1px; height: 50px; min-height: 50px;">
                        <div class="d-popup-files-footer d-cb">
                            <a href="javascript:" id="js-file-trigger-concept-report"
                               class="button js-d-popup-file-trigger" data-target="concept_report_file">Прикрепить
                                файл</a>

                            <span id="concept_report_files_caption">
                                Прикреплено - 0 файлов.<br/>
                                Общий размер - 0 МБ
                            </span>
                        </div><!-- /d-popup-files-footer -->
                    </div><!-- /d-popup-uploaded-files -->
                </div>
            </div>

        </div>
    </div>

    <div class="d-popup-cols model-form">
        <div class="d-popup-col">
            <div class="d-popup-files-wrap scrollbar-inner">
                <div class="d-popup-files-row">
                    <label>Фотоотчет</label>

                    <div class="file">
                        <div class="modal-file-wrapper input">
                            <div id="report-additional-files-progress-bar"
                                 class="progress-bar-content progress-bar-full-width"></div>
                        </div>
                    </div>

                    <div class="scroller scroller-add-docs">
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
                                        <div id="container_model_files" class="control dropzone"
                                             style="min-height: 294px">
                                            <div class="d-popup-files-wrap scrollbar-inner">
                                                <div class="d-popup-files-row">
                                                    <div id="report_additional_files"
                                                         class="d-popup-uploaded-files d-cb"></div>
                                                </div>
                                            </div>

                                            <div id="model_files_caption" class="caption">Для выбора файлов нажмите на
                                                кнопку или перетащите
                                                их сюда
                                            </div>

                                            <input type="file" name="additional_file_with_category" id="additional_file_with_category" multiple/>
                                        </div>
                                        <div class="modal-input-error-icon error-icon"></div>
                                        <div class="error message"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-popup-uploaded-files d-cb">
                        <div class="d-popup-files-footer d-cb">

                            <a href="javascript:" id="js-file-trigger-report-additional-file"
                               class="button js-d-popup-file-trigger" data-target="additional_file_with_category">Прикрепить
                                файл</a>
                                <span id="report_additional_files_caption">
                                    Прикреплено - 0 файлов.<br/>
                                    Общий размер - 0 МБ
                                </span>
                        </div><!-- /d-popup-files-footer -->
                    </div><!-- /d-popup-uploaded-files -->
                </div>
            </div>
        </div>

        <div class="d-popup-col">
            <div class="d-popup-files-wrap scrollbar-inner financial-file">
                <div class="d-popup-files-row">
                    <label>Финансовые документы</label>

                    <div class="file">
                        <div class="modal-file-wrapper input">
                            <div id="report-financial-files-progress-bar"
                                 class="progress-bar-content progress-bar-full-width"></div>
                        </div>
                    </div>

                    <div class="scroller scroller-add-fin">
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
                                        <div id="container_model_files" class="control dropzone"
                                             style="min-height: 294px">
                                            <div class="d-popup-files-wrap scrollbar-inner">
                                                <div class="d-popup-files-row">
                                                    <div id="report_financial_files"
                                                         class="d-popup-uploaded-files d-cb"></div>
                                                </div>
                                            </div>

                                            <div id="model_files_caption" class="caption">Для выбора файлов нажмите на
                                                кнопку или
                                                перетащите их сюда
                                            </div>
                                            <input type="file" name="financial_docs_file_with_category" id="financial_docs_file_with_category"
                                                   multiple/>
                                        </div>
                                        <div class="modal-input-error-icon error-icon"></div>
                                        <div class="error message"></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="d-popup-uploaded-files d-cb">
                        <div class="d-popup-files-footer d-cb">
                            <a href="javascript:" id="js-file-trigger-report-financial-file"
                               class="button js-d-popup-file-trigger" data-target="financial_docs_file_with_category">Прикрепить
                                файл</a>

                            <span id="report_financial_files_caption">
                                Прикреплено - 0 файлов.<br/>
                                Общий размер - 0 МБ
                            </span>
                        </div><!-- /d-popup-files-footer -->
                    </div><!-- /d-popup-uploaded-files -->
                </div>
            </div>

            <div class="d-popup-report-cost-box">
                <label><strong>Сумма, руб.</strong> (без НДС)</label>
                <input type="text" value="" name="cost" placeholder="0 руб."
                       data-format-expression="^[0-9]+(\.[0-9]+)?$" data-required="true" data-right-format="100.00">

                <div class="modal-input-error-icon error-icon"></div>
                <div class="error message" style="top: 45px;"></div>
            </div><!-- /d-popup-report-cost -->
        </div>
    </div>

    <div class="d-popup-cols">
        <div class="d-popup-col">
            <div class="d-popup-report-cost-box">
                <button class="margin-auto button modal-form-submit-button submit-btn" type="submit">
                    <span>Отправить</span></button>
                <div class="margin-auto gray button cancel cancel-btn">Отменить</div>
            </div>
        </div>
    </div>

    <input type="hidden" name="<?php echo session_name(); ?>" value="<?php echo session_id(); ?>">
    <input type="hidden" name="upload_file_object_type" value=""/>
    <input type="hidden" name="upload_file_type" value=""/>
    <input type="hidden" name="upload_field" value=""/>
    <input type="hidden" name="upload_files_additional_ids" value=""/>
    <input type="hidden" name="upload_files_financial_ids" value=""/>

</form>

<iframe src="/blank.html" width="1" height="1" frameborder="0" hspace="0" marginheight="0" marginwidth="0"
        name="report-target" scrolling="no"></iframe>
