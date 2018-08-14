<script type="text/javascript" src="/js/form/form.js"></script>
<script type="text/javascript" src="/js/activity/module/agreement/model/form/base.js"></script>

<div id="model_categories" class="model modal d-popup-wrap" style="width: 940px;">
    <div class="d-popup-body">
        <div class="modal-header">
            <ul class="pages tabs model-tabs">
                <li class="tab active model-tab" data-pane="model-categories-pane"><span>Материал</span></li>
                <li class="tab report-tab" data-pane="report-categories-pane"><span>Отчет</span></li>
                <li class="tab discussion-tab" data-pane="discussion-categories-pane"><span>Статус</span>
                    <div class="message">&nbsp;</div>
                </li>
            </ul>
        </div>
        <div class="modal-close"></div>
        <div class="modal-form tab-pane model-pane" id="model-categories-pane">
            <?php include_partial('form_categories',
                array
                (
                    'activity' => $activity,
                    'model_types' => $model_types,
                    'model_types_fields' => $model_types_fields,
                    'model_categories' => $model_categories,
                    'model_categories_fields' => $model_categories_fields,
                    'dealer_files' => $dealer_files,
                    'forms_activities' => $forms_activities
                )
            ) ?>
        </div>
        <div class="modal-form tab-pane report-pane" id="report-categories-pane">
            <?php include_partial('agreement_activity_model_report/form_with_categories', array('activity' => $activity)) ?>
        </div>
        <div class="tab-pane chat" id="discussion-categories-pane">
            <?php include_partial('discussion/dealer_panel', array('form_id' => 'discussion_categories_upload_form')); ?>
        </div>
    </div>
</div>

<script type="text/javascript" src="/js/activity/module/agreement/model/form/root_controller.js"></script>
<script type="text/javascript" src="/js/activity/module/agreement/model/form/report.js?1"></script>
<script type="text/javascript" src="/js/activity/module/agreement/model/form/model.js"></script>
<script type="text/javascript" src="/js/activity/module/agreement/model/form/discussion.js"></script>
<script type="text/javascript">
    $(function () {
        var root_controller = new AgreementModelRootControler({

            model_categories_form: new AgreementModelForm({
                button_selector: '#accept-model-button-with-category',
                form: '#model-categories-form',
                models_list: '#agreement-models',
                model_row: '.model-row-with-category',
                tabs_selector: '#model_categories .tabs',
                tab_selector: '#model_categories .model-tab',
                concept_type_id: <?php echo $concept_type ? $concept_type->getId() : 'false' ?>,
                add_url: '<?php echo url_for("@agreement_module_models_add?activity={$activity->getId()}") ?>',
                load_url: '<?php echo url_for("@agreement_module_models_edit?activity=" . $activity->getId()) ?>',
                update_url: '<?php echo url_for("@agreement_module_models_update?activity=" . $activity->getId()) ?>',
                cancel_url: '<?php echo url_for("@agreement_module_models_cancel?activity=" . $activity->getId()) ?>',
                cancel_scenario_url: '<?php echo url_for("@agreement_module_models_scenario_cancel?activity=" . $activity->getId()) ?>',
                cancel_record_url: '<?php echo url_for("@agreement_module_models_record_cancel?activity=" . $activity->getId()) ?>',
                delete_url: '<?php echo url_for('@agreement_module_models_delete?activity=' . $activity->getId()) ?>',
                load_record_block_url: '<?php echo url_for('activity_model_record_block'); ?>',
                load_model_block_url: '<?php echo url_for('activity_model_files_block'); ?>',
                dates_field_url: '<?php echo url_for('agreement_model_dates_field'); ?>',
                on_load_model_category_types: '<?php echo url_for('@agreement_model_category_get_types_list'); ?>',
                on_load_model_category_mime_types_forbidden: '<?php echo url_for('@on_load_model_category_mime_types_forbidden'); ?>',
                on_get_category_field_type_url: '<?php echo url_for('@agreement_model_type_data'); ?>',
                load_dates_and_certificates: '<?php echo url_for('agreement_dates_and_certificates'); ?>',
                delete_date_field: '<?php echo url_for('agreement_dates_delete'); ?>',
                load_concept_cert_fields_url: '<?php echo url_for('agreement_model_load_concept_cert_fields'); ?>',
                change_model_period_url: '<?php echo url_for('@agreement_model_change_model_period'); ?>',
                init_delete_files_event: true,
                model_file_uploader: new JQueryUploader({
                    file_uploader_el: '#model_file_category',
                    max_file_size: '<?php echo sfConfig::get('app_max_upload_size'); ?>',
                    uploader_url: '<?php echo '/upload_ajax.php'; ?>',
                    delete_temp_file_url: '<?php echo url_for('@upload_temp_ajax_delete'); ?>',
                    delete_uploaded_file_url: '<?php echo url_for('@agreement_model_delete_uploaded_file'); ?>',
                    delete_uploaded_files_list_url: '<?php echo url_for('@agreement_model_delete_uploaded_files'); ?>',
                    uploaded_files_container: '#model_files',
                    uploaded_files_caption: '#model_files_caption',
                    el_attach_files_click_bt: '#js-file-trigger-model',
                    el_attach_files_model_field: '#model_file_category',
                    progress_bar: '#model-files-progress-bar',
                    upload_file_object_type: 'model',
                    upload_file_type: 'model',
                    upload_field: 'model_file_category',
                    files_change_event: 'report_files_change',
                    scroller: '.scroller-model',
                    scroller_height: 200,
                    model_form: '#model-categories-form'
                }).start(),
                concept_file_uploader: new JQueryUploader({
                    file_uploader_el: '#concept_file',
                    max_file_size: '<?php echo sfConfig::get('app_max_upload_size'); ?>',
                    uploader_url: '<?php echo '/upload_ajax.php'; ?>',
                    delete_temp_file_url: '<?php echo url_for('@upload_temp_ajax_delete'); ?>',
                    delete_uploaded_file_url: '<?php echo url_for('@agreement_model_delete_uploaded_file'); ?>',
                    uploaded_files_container: '#concept_files',
                    uploaded_files_caption: '#concept_files_caption',
                    el_attach_files_click_bt: '#js-file-trigger-concept',
                    el_attach_files_model_field: '#concept_file',
                    progress_bar: '#concept-files-progress-bar',
                    upload_file_object_type: 'model',
                    upload_file_type: 'model',
                    upload_field: 'model_file',
                    files_change_event: 'report_files_change',
                    scroller: '.scroller-concept',
                    scroller_height: 200,
                    model_form: '#model-categories-form'
                }).start(),
                model_record_file_uploader: new JQueryUploader({
                    file_uploader_el: '#model_record_file',
                    max_file_size: '<?php echo sfConfig::get('app_max_upload_size'); ?>',
                    uploader_url: '<?php echo '/upload_ajax.php'; ?>',
                    delete_temp_file_url: '<?php echo url_for('@upload_temp_ajax_delete'); ?>',
                    delete_uploaded_file_url: '<?php echo url_for('@agreement_model_delete_uploaded_file'); ?>',
                    uploaded_files_container: '#model_record_files',
                    uploaded_files_caption: '#model_record_files_caption',
                    el_attach_files_click_bt: '#js-file-trigger-model-record',
                    el_attach_files_model_field: '#model_record_file',
                    progress_bar: '#model-record-files-progress-bar',
                    upload_file_object_type: 'model',
                    upload_file_type: 'model_record',
                    upload_field: 'model_record_file',
                    upload_files_ids_el: 'upload_files_records_ids',
                    files_change_event: 'mode_record_files_change',
                    scroller: '.scroller-model-record',
                    scroller_height: 200,
                    model_form: '#model-categories-form'
                }).start(),
            }).start(),

            report_categories_form: new AgreementModelReportForm({
                form: '#report-categories-form',
                models_list: '#agreement-models',
                model_row: '.model-row-with-category',
                tabs_selector: '#model_categories .tabs',
                tab_selector: '#model_categories .report-tab',
                load_url: '<?php echo url_for("@agreement_module_models_report_edit?activity=" . $activity->getId()) ?>',
                update_url: '<?php echo url_for("@agreement_module_models_report_update?activity=" . $activity->getId()) ?>',
                cancel_url: '<?php echo url_for("@agreement_module_models_report_cancel?activity=" . $activity->getId()) ?>',
                load_additional_financial_docs_files_url: '<?php echo url_for("@agreement_module_report_load_add_fin_docs_files"); ?>',
                delete_uploaded_add_fin_doc_files_url: '<?php echo url_for("@agreement_module_report_delete_add_find_docs_file"); ?>',
                init_delete_files_event: true,
                report_file_additional_uploader: new JQueryUploader({
                    file_uploader_el: '#additional_file_with_category',
                    max_file_size: '<?php echo sfConfig::get('app_max_upload_size'); ?>',
                    uploader_url: '<?php echo '/upload_ajax.php'; ?>',
                    delete_temp_file_url: '<?php echo url_for('@upload_temp_ajax_delete'); ?>',
                    delete_uploaded_file_url: '<?php echo url_for('@agreement_model_delete_uploaded_file'); ?>',
                    uploaded_files_container: '#report_additional_files',
                    uploaded_files_caption: '#report_additional_files_caption',
                    el_attach_files_click_bt: '#js-file-trigger-report-additional-file',
                    el_attach_files_model_field: '#additional_file_with_category',
                    progress_bar: '#report-additional-files-progress-bar',
                    upload_file_object_type: 'report',
                    upload_file_type: 'report_additional',
                    upload_field: 'additional_file_with_category',
                    upload_files_ids_el: 'upload_files_additional_ids',
                    files_change_event: 'report_files_change',
                    scroller: '.scroller-add-docs',
                    model_form: '#report-categories-form'
                }).start(),
                report_file_financial_uploader: new JQueryUploader({
                    file_uploader_el: '#financial_docs_file_with_category',
                    max_file_size: '<?php echo sfConfig::get('app_max_upload_size'); ?>',
                    uploader_url: '<?php echo '/upload_ajax.php'; ?>',
                    delete_temp_file_url: '<?php echo url_for('@upload_temp_ajax_delete'); ?>',
                    delete_uploaded_file_url: '<?php echo url_for('@agreement_model_delete_uploaded_file'); ?>',
                    uploaded_files_container: '#report_financial_files',
                    uploaded_files_caption: '#report_financial_files_caption',
                    el_attach_files_click_bt: '#js-file-trigger-report-financial-file',
                    el_attach_files_model_field: '#financial_docs_file_with_category',
                    progress_bar: '#report-financial-files-progress-bar',
                    upload_file_object_type: 'report',
                    upload_file_type: 'report_financial',
                    upload_field: 'financial_docs_file_with_category',
                    upload_files_ids_el: 'upload_files_financial_ids',
                    files_change_event: 'report_files_change',
                    scroller: '.scroller-add-fin',
                    model_form: '#report-categories-form'
                }).start(),
                concept_file_uploader: new JQueryUploader({
                    file_uploader_el: '#concept_report_file',
                    max_file_size: '<?php echo sfConfig::get('app_max_upload_size'); ?>',
                    uploader_url: '<?php echo '/upload_ajax.php'; ?>',
                    delete_temp_file_url: '<?php echo url_for('@upload_temp_ajax_delete'); ?>',
                    delete_uploaded_file_url: '<?php echo url_for('@agreement_model_delete_uploaded_file'); ?>',
                    uploaded_files_container: '#concept_report_files',
                    uploaded_files_caption: '#concept_report_files_caption',
                    el_attach_files_click_bt: '#js-file-trigger-concept-report',
                    el_attach_files_model_field: '#concept_report_file',
                    progress_bar: '#concept-report-files-progress-bar',
                    upload_file_object_type: 'report',
                    upload_file_type: 'report_financial',
                    upload_field: 'concept_report_file',
                    upload_files_ids_el: 'upload_files_financial_ids',
                    files_change_event: 'report_files_change',
                    scroller: '.scroller-concept-report',
                    scroller_height: 200,
                    model_form: '#report-categories-form'
                }).start(),
            }).start(),

            discussion_categories_controller: new AgreementModelDiscussionController({
                models_list: '#agreement-models',
                model_row: '.model-row-with-category',
                tabs_selector: '#model_categories .tabs',
                tab_selector: '#model_categories .discussion-tab',
                panel_selector: '#discussion-categories-pane',
                state_url: "<?php echo url_for('@discussion_state') ?>",
                new_messages_url: "<?php echo url_for('@discussion_new_messages') ?>",
                post_url: "<?php echo url_for('@discussion_post') ?>",
                previous_url: "<?php echo url_for('@discussion_previous') ?>",
                search_url: "<?php echo url_for('@discussion_search') ?>",
                online_check_url: "<?php echo url_for('@discussion_online_check') ?>",
                session_name: '<?php echo session_name() ?>',
                session_id: '<?php echo session_id() ?>',
                delete_file_url: "<?php echo url_for('@upload_temp_ajax_delete') ?>",
                load_chat_last_messages_and_files: "<?php echo url_for('@agreement_model_discussion_load_chat_last_messages_and_files'); ?>",
                scroller: '.scroller-discussion-uploaded-files',
                scroller_discussion: '.scroller-discussion-messages',
                discussion_file_uploader: new JQueryUploader({
                    file_uploader_el: '#discussion_comment_file',
                    max_file_size: '<?php echo sfConfig::get('app_max_upload_size'); ?>',
                    uploader_url: '<?php echo '/upload_ajax.php'; ?>',
                    delete_temp_file_url: '<?php echo url_for('@upload_temp_ajax_delete'); ?>',
                    delete_uploaded_file_url: '<?php echo url_for('@agreement_model_delete_uploaded_file'); ?>',
                    uploaded_files_container: '#discussion_files',
                    el_attach_files_model_field: '#discussion_comment_file',
                    progress_bar: '#discussion-files-progress-bar',
                    upload_files_ids_el: 'upload_files_discussion_ids',
                    upload_file_object_type: 'discussion',
                    upload_file_type: 'discussion',
                    upload_field: 'discussion_comment_file',
                    draw_only_labels: true,
                    el_attach_files_click_bt: '#btn-add-discussion-dealer-files',
                    model_form: '#discussion_categories_upload_form'
                }).start()
            }).start(),

            modal: '#model_categories',
            model_row: '.model-row-with-category',
            add_model_button: '#add-model-categories-button, #add-necessarily-model',
            list_selector: '#agreement-models',
            sort_url: '<?php echo url_for('@agreement_module_models_sort?activity=' . $activity->getId()) ?>',
            add_many_concepts_url: '<?php echo url_for('@add_many_concepts'); ?>',
            work_with_categories: true,

        }).start();

        window.agreement_model_with_category_form = root_controller.model_categories_form;
        window.agreement_model_report_with_category_form = root_controller.report_categories_form;
    });
</script>
