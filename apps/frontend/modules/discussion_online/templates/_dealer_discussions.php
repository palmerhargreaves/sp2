<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 02.08.2017
 * Time: 14:50
 */

$messages_data = $discussions->getMessagesData();

$first_discussion = $discussions->getFirstDiscussion();
$activities_list = $messages_data['activities_list'];

?>
<div class="discussion__item is-wide container_discussion_panel" data-panel="left">
    <div class="discussion__header">
        <h2>Вопросы по заявкам</h2>
    </div>
    <div class="discussion__body">
        <div class="discussion__list">
            <div class="discussion__list__header">
                <select id="js-discussions-by-activity" class="js-select">
                    <option value="0">Все активности ...</option>
                    <?php foreach ($activities_list as $activity): ?>
                        <option value="<?php echo $activity['id']; ?>"><?php echo sprintf('[%d] %s', $activity['id'], $activity['name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="discussion__list__filter">
                    <a href="javascript:" class="current js-filter-visibility-discussion" data-show="all">Все</a>
                    <span></span>
                    <a href="javascript:" class="js-filter-visibility-discussion" data-show="unread">Непрочитанные</a>
                </div>
            </div>
            <div id="dealer-discussions-container" class="discussion__list__body">
                <?php include_partial('discussions_list', array('messages_data' => $messages_data)); ?>
            </div>
        </div>
        <div class="discussion__comments">
            <div id="container-model-messages" class="discussion__comments__body">

            </div>
            <div class="discussion__comments__footer">
                <form id="frm-discussion-model-messages" method="post" enctype="multipart/form-data" target="">
                    <input type="hidden" name="<?php echo session_name(); ?>" value="<?php echo session_id(); ?>">
                    <input type="hidden" name="upload_file_object_type" value=""/>
                    <input type="hidden" name="upload_file_type" value=""/>
                    <input type="hidden" name="upload_field" value=""/>
                    <input type="hidden" name="upload_files_discussion_ids" value=""/>

                    <div class="file">
                        <div class="modal-file-wrapper input">
                            <div id="discussion-messages-files-progress-bar" style="top: 5px;"
                                 class="progress-bar-content progress-bar-full-width"></div>
                        </div>
                    </div>

                    <div id="frm-discussion-message" data-message-field="message" class="discussion__comments_text"></div>

                    <div class="discussion__comments__btns">
                        <input type="file" id="discussion_message_files" name="discussion_message_files" class="js-file"
                               multiple/>
                        <input type="submit" name="frm-discussion-send-button" value="Отправить" class="btn"/>
                    </div>

                    <div class="d-popup-files-wrap scrollbar-inner">
                        <div class="d-popup-files-row">
                            <div id="discussion_files" class="d-popup-uploaded-files d-cb" style="padding: 0px;"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="discussion__item is-thin container_discussion_panel" data-panel="right">
    <div class="discussion__header">
        <h2>Общие вопросы</h2>
    </div>
    <div class="discussion__body">
        <div class="discussion__comments">
            <div id="container-discussion-ask-messages" class="discussion__comments__body">
                <?php include_partial('messages_list', array('messages_list' => $discussions->getAskMessagesList($first_discussion))); ?>
            </div>

            <div class="discussion__comments__footer">
                <form id="frm-discussion-ask" method="post" enctype="multipart/form-data" target="">
                    <input type="hidden" name="<?php echo session_name(); ?>" value="<?php echo session_id(); ?>">
                    <input type="hidden" name="upload_file_object_type" value=""/>
                    <input type="hidden" name="upload_file_type" value=""/>
                    <input type="hidden" name="upload_field" value=""/>
                    <input type="hidden" name="upload_files_discussion_ids" value=""/>

                    <div class="file">
                        <div class="modal-file-wrapper input">
                            <div id="discussion-all-progress-bar" style="top: 5px;"
                                 class="progress-bar-content progress-bar-full-width"></div>
                        </div>
                    </div>

                    <div id='frm-discussion-ask-message' data-message-field="ask" class="discussion__comments_text"></div>
                    <div class="discussion__comments__btns">
                        <input type="file" id="discussion_ask_files" name="discussion_ask_files" class="js-file"
                               multiple/>
                        <input type="submit" name="frm-discussion-ask-button" value="Отправить" class="btn"/>
                    </div>

                    <div class="d-popup-files-wrap scrollbar-inner">
                        <div class="d-popup-files-row">
                            <div id="discussion_files" class="d-popup-uploaded-files d-cb" style="padding: 0px;"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        window.discussion_online.startDealer({
            default_model_id: <?php echo isset($first_discussion['model']['id']) ? $first_discussion['model']['id'] : 0; ?>,
            dealer_id: <?php echo isset($first_discussion['model']['id']) ? $first_discussion['model']['dealer_id'] : 0; ?>,

            on_load_messages_list_by_model_url: '<?php echo url_for('@discussion_online_load_messages_by_dealer'); ?>',
            on_load_ask_messages_url: '<?php echo url_for('@discussion_online_load_ask_messages_by_dealer'); ?>',
            on_load_discussions_url: '<?php echo url_for('@discussions_online_load'); ?>',
            on_load_discussion_unread_url: '<?php echo url_for('@discussion_online_unread_count'); ?>',
            on_filter_discussions_visibility_url: '<?php echo url_for('@discussion_online_visibility'); ?>',
            on_post_new_message_url: '<?php echo url_for('@discussions_online_post_message'); ?>',

            container_discussion_panel: '.container_discussion_panel',
            on_show_discussion_messages: '.js-dealer-discussion-list-item',
            on_models_by_activity: '#js-discussions-by-activity',
            model_messages_container: '#container-model-messages',
            discussion_ask_messages_container: '#container-discussion-ask-messages',
            filter_visibility_messages: '.js-filter-visibility-discussion',
            container_discussion_list: '.discussion__list__body',
            frm_discussion_files: 'input[name=frm-discussion-files]',

            frm_discussion_send_button: 'input[name=frm-discussion-send-button]',
            frm_discussion_send_ask_button: 'input[name=frm-discussion-ask-button]',

            frm_discussion_message_element: 'frm-discussion-message',
            frm_discussion_ask_message_element: 'frm-discussion-ask-message',

            discussion_model_messages: new JQueryUploader({
                file_uploader_el: '#discussion_message_files',
                max_file_size: '<?php echo sfConfig::get('app_max_upload_size'); ?>',
                uploader_url: '<?php echo '/upload_ajax.php'; ?>',
                delete_temp_file_url: '<?php echo url_for('@upload_temp_ajax_delete'); ?>',
                delete_uploaded_file_url: '<?php echo url_for('@agreement_model_delete_uploaded_file'); ?>',
                uploaded_files_container: '#discussion_files',
                el_attach_files_model_field: '#discussion_message_files',
                progress_bar: '#discussion-messages-files-progress-bar',
                upload_files_ids_el: 'upload_files_discussion_ids',
                upload_file_object_type: 'discussion',
                upload_file_type: 'discussion',
                upload_field: 'discussion_message_files',
                draw_only_labels: true,
                //el_attach_files_click_bt: '#discussion_message_files',
                disabled_files_extensions: ['js'],
                model_form: '#frm-discussion-model-messages'
            }).start(),

            discussion_ask: new JQueryUploader({
                file_uploader_el: '#discussion_ask_files',
                max_file_size: '<?php echo sfConfig::get('app_max_upload_size'); ?>',
                uploader_url: '<?php echo '/upload_ajax.php'; ?>',
                delete_temp_file_url: '<?php echo url_for('@upload_temp_ajax_delete'); ?>',
                delete_uploaded_file_url: '<?php echo url_for('@agreement_model_delete_uploaded_file'); ?>',
                uploaded_files_container: '#discussion_files',
                el_attach_files_model_field: '#discussion_ask_files',
                progress_bar: '#discussion-all-progress-bar',
                upload_files_ids_el: 'upload_files_discussion_ids',
                upload_file_object_type: 'discussion',
                upload_file_type: 'discussion',
                upload_field: 'discussion_ask_files',
                draw_only_labels: true,
                //el_attach_files_click_bt: '#discussion_message_files',
                disabled_files_extensions: ['js'],
                model_form: '#frm-discussion-ask'
            }).start()
        });
    });
</script>

<iframe style="position: absolute;" src="/blank.html" width="1" height="1" frameborder="0" hspace="0" marginheight="0"
        marginwidth="0" name="discussion-frm" scrolling="no"></iframe>
