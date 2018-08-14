<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 02.08.2017
 * Time: 14:50
 */

$data = $discussions->getMessagesData();

$first_item = $data['first_dealer']->getRawValue();

$default_dealer_data = $data['default_dealer_data'];
$default_messages_list = $data['default_messages_list'];
$default_ask_messages_list = $data['default_ask_messages_list'];

?>
<div class="discussion__item is-aside container_discussion_panel" data-panel="left">
    <div class="discussion__body">
        <div class="discussion__list">
            <div class="discussion__list__header">
                <span class="discussion__list__sort js-sort-dealers" data-sort-direction="desc" data-sort-by="dealer_name">Дилер / Имя</span>
                <div class="discussion__list__filter">
                    <a href="javascript:" class="current js-filter-visibility-discussion" data-show="all">Все</a>
                    <span></span>
                    <a href="javascript:" class="js-filter-visibility-discussion" data-show="unread">Непрочитанные</a>
                </div>
            </div>
            <div class="discussion__list__search">
                <form action="">
                    <input type="text" id="txt_discussions_filter_by_name" name="txt_discussions_filter_by_name" value=""/>
                    <button type="submit"></button>
                </form>
            </div>
            <div id="dealers-discussions-container" class="discussion__list__body">
                <?php include_partial('discussion_online/admin_importer/_dealer_item', arraY('dealers_list' => $data['dealers_list'], 'first_dealer' => $data['first_dealer'])); ?>
            </div>
        </div>
    </div>
</div>

<div class="discussion__item is-chat container_discussion_panel" data-panel="right">
    <div class="discussion__header">
        <div class="discussion__header__body">
            <div class="discussion__header__nav">
                <a href="javascript:" class="current discussions-messages-types" data-type="all">Заявки</a>
                <a href="javascript:" class="discussions-messages-types" data-type="ask">Общие</a>
            </div>

            <div data-container-type="all" class="discussion__header__tabs discussion-messages-list-by-type">
                <a href="javascript:" class="current discussions-messages-status" data-type="all">Все</a>
                <a href="javascript:" class="discussions-messages-status" data-type="unread">Непрочитанные</a>
            </div>
        </div>
    </div>

    <div data-container-type="all" class="discussion-messages-list-by-type discussion__body" >
        <div class="discussion__list">
            <div id="container-dealer-discussions" class="discussion__list__body">
                <?php include_partial('discussion_online/admin_importer/_dealer_messages_list', array('default_dealer_data' => $default_dealer_data)); ?>
            </div>
        </div>
        <div class="discussion__comments">
            <div id="container-discussion-messages" class="discussion__comments__body">
                <?php include_partial('messages_list', array('messages_list' => $default_messages_list)); ?>
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

                    <div id="frm-discussion-message" data-message-field="message" class="discussion__comments_text" style="height: 60px;"></div>

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

    <div data-container-type="ask" class="discussion-messages-list-by-type discussion__body" style="display: none;">
        <div class="discussion__comments" style="border-left: 0px; margin-left: 0px;">
            <div id="container-discussion-messages-ask" class="discussion__comments__body">
                <?php include_partial('messages_list', array('messages_list' => $default_ask_messages_list)); ?>
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

                    <div id='frm-discussion-ask-message' data-message-field="ask" class="discussion__comments_text" style="height: 60px;"></div>
                    <div class="discussion__comments__btns">
                        <input type="checkbox" name="" value="" id="send-chat-0"><label for="send-chat-0">Отправить
                            импортеру</label><br/>
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
        window.discussion_online.startImporter({
            model_id: '<?php echo $data['default_model_id']; ?>',

            on_load_discussion_unread_url: '<?php echo url_for('@discussion_online_unread_count'); ?>',
            on_load_ask_messages_url: '<?php echo url_for('@discussion_online_load_ask_messages_by_dealer'); ?>',
            on_load_messages_list_by_model_url: '<?php echo url_for('@discussion_online_load_messages_by_dealer'); ?>',
            on_load_dealer_discussions_url: '<?php echo url_for('@discussions_list_by_dealer'); ?>',
            on_post_new_message_url: '<?php echo url_for('@discussions_online_post_message'); ?>',

            on_dealer_show_discussions: '.dealer_model_item',

            container_discussion_panel: '.container_discussion_panel',
            container_dealer_discussions: '#container-dealer-discussions',
            container_discussion_messages: '#container-discussion-messages',
            container_discussion_ask_messages: '#container-discussion-messages-ask',

            frm_discussion_send_button: 'input[name=frm-discussion-send-button]',
            frm_discussion_send_ask_button: 'input[name=frm-discussion-ask-button]',

            frm_discussion_message_element: 'frm-discussion-message',
            frm_discussion_ask_message_element: 'frm-discussion-ask-message',

            model_messages_container: '#container-discussion-messages',

            filter_visibility_messages: '.js-filter-visibility-discussion',
            on_show_discussion_messages: '.js-dealers-discussion-list-item',

            dealer_discussions_container: '#container-dealer-discussions',

            on_switch_discussions_messages_type: '.discussions-messages-types',
            on_switch_discussions_messages_status: '.discussions-messages-status',

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
