<div class="ask-q">
    <?php include_partial('discussion/form_search'); ?>

    <div class="d-popup-cols">
        <div class="d-popup-col wide">
            <div class="show-old-messages">
                <div class="links"><span class="all">Показать предыдущие сообщения</span></div>
            </div>

            <div class="scroller">
                <div class="scrollbar">
                    <div class="track">
                        <div class="thumb">
                            <div class="end"></div>
                        </div>
                    </div>
                </div>
                <div class="viewport scroller-wrapper">
                    <div class="overview scroller-inner">
                        <div class="messages"></div>
                    </div>
                </div>
            </div>

            <div class="message-send-wrapper">
                <form action="<?php echo url_for('@discussion_post') ?>" method="post" class="post"
                      enctype="multipart/form-data" id="discussion_upload_form" name="discussion_upload_form">
                    <input type="hidden" name="<?php echo session_name(); ?>" value="<?php echo session_id(); ?>">
                    <input type="hidden" name="upload_file_object_type" value=""/>
                    <input type="hidden" name="upload_file_type" value=""/>
                    <input type="hidden" name="upload_field" value=""/>
                    <input type="hidden" name="upload_files_discussion_agreement_ids" value=""/>

                    <div class="message-send-buttons">
                        <div class="message-button-wrapper">
                            <input type="submit" class="message-button" value="Отправить" title="Ctrl+Enter"/>
                        </div>

                        <div class="message-button-wrapper btn-add-file" id="btn-add-discussion-agreement-dealer-files">
                            <div style="margin: auto;" class="gray button">Добавить файл</div>
                        </div>
                    </div>

                    <div class="textarea-wrapper">
                        <textarea name="message" placeholder="Введите сообщение"></textarea>
                    </div>

                    <div class="message-upload-wrapper message-upload">
                        <div class="file">
                            <div class="modal-file-wrapper input">
                                <div id="discussion-agreement-files-progress-bar"
                                     class="progress-bar-content progress-bar-full-width"></div>
                            </div>
                        </div>

                        <div class="file" style="min-height: 50px;">
                            <div class="modal-file-wrapper input">
                                <div class="d-popup-files-wrap scrollbar-inner">
                                    <div class="d-popup-files-row">
                                        <div id="discussion_agreement_files"
                                             class="d-popup-uploaded-files d-cb" style="padding: 0px;"></div>
                                    </div>
                                </div>

                                <div id="container_discussion_files" class="control dropzone" style="min-height: 0px; height: 0px !important; border: none !important;">
                                    <input type="file" id="discussion_agreement_comment_file" name="discussion_agreement_comment_file"
                                           style="height: 0px;" multiple>
                                </div>
                            </div>
                        </div>
                        <div class="files"></div>
                        <div class="clear"></div>
                    </div>
                </form>

            </div>

        </div>

        <div class="d-popup-col thin">
            <div class="chat-last-comment"></div>
        </div>
    </div>

</div>
