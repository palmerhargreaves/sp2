AgreementModelDiscussionController = function (config) {
    // configurable {
    this.tabs_selector = '';
    this.tab_selector = '';
    this.models_list = ''; // required selector of models list table
    this.panel_selector = '#discussion-pane';
    this.state_url = '';
    this.new_messages_url = '';
    this.post_url = '';
    this.previous_url = '';
    this.search_url = '';
    this.online_check_url = '';
    this.session_name = '';
    this.session_id = '';
    this.delete_file_url = '';
    this.load_chat_last_messages_and_files = '';
    this.scroller = '';
    this.scroller_discussion = '';
    this.discussion_file_uploader = null;
    this.model_row = '';
    // }
    $.extend(this, config);

    this.discussion_id = 0;
    this.discussion = null;
    this.start_message = false;
}

AgreementModelDiscussionController.prototype = {
    start: function () {
        this.initEvents();

        return this;
    },

    stopDiscussion: function () {
        if (this.discussion)
            this.discussion.stopDiscussion();
    },

    initEvents: function () {
        this.getTab().on('activated', $.proxy(this.onActivateTab, this));
        $(this.model_row, this.getModelsList()).click($.proxy(this.onClickModel, this));

        $(document).on('click', '.js-view-toggle', $.proxy(this.onToggleLastMessageFilesView, this));
        $(document).on('click', '.download-discussion-message-files', $.proxy(this.onDownloadDiscussionMessageFiles, this));
    },

    disable: function () {
        this.getTab().addClass('disabled');
        this.hideNewMessageIndicator();
    },

    enable: function () {
        this.getTab().removeClass('disabled');
    },

    setDiscussion: function (discussion_id, unread) {
        this.discussion_id = discussion_id;
        this.enable();
        this.showNewMessageIndicatorIf(unread);
    },

    setStartMessage: function (start_message) {
        this.start_message = start_message;
    },

    showNewMessageIndicatorIf: function (count) {
        if (count > 0)
            this.showNewMessageIndicator(count);
        else
            this.hideNewMessageIndicator();
    },

    showNewMessageIndicator: function (count) {
        this.getNewMessageIndicator().show().html(count + "");
    },

    hideNewMessageIndicator: function () {
        this.getNewMessageIndicator().hide();
    },

    getDiscussion: function () {
        if (!this.discussion) {
            var $upload_panel = $('.message-upload', this.getPanel());

            this.discussion = new Discussion({
                panel: this.getPanel().getIdSelector(),
                state_url: this.state_url,
                new_messages_url: this.new_messages_url,
                post_url: this.post_url,
                previous_url: this.previous_url,
                search_url: this.search_url,
                online_check_url: this.online_check_url,
                scroller_uploaded_files: this.scroller,
                discussion_file_uploader: this.discussion_file_uploader
                /*uploader: $upload_panel.length > 0
                 ? new Uploader({
                 selector: $upload_panel.getIdSelector(),
                 session_name: this.session_name,
                 session_id: this.session_id,
                 upload_url: '/upload.php',
                 delete_url: this.delete_file_url
                 }).start()
                 : null*/
            }).start();
        }

        return this.discussion;
    },

    getPanel: function () {
        return $(this.panel_selector);
    },

    getNewMessageIndicator: function () {
        return $('.message', this.getTab());
    },

    activateTab: function () {
        this.getTabs().kriktab('activate', this.getTab());
    },

    getTab: function () {
        return $(this.tab_selector);
    },

    getTabs: function () {
        return $(this.tabs_selector);
    },

    getModelsList: function () {
        return $(this.models_list);
    },

    onClickModel: function (e) {
        var $model_row = $(e.target).closest(this.model_row);

        this.isBlocked = $model_row.data('is-blocked') == 1 ? true : false;
        this.discussion_id = $model_row.data('discussion');

        if (this.discussion_id) {
            this.enable();
            this.showNewMessageIndicatorIf($model_row.data('new-messages'));
        } else {
            this.disable();
            this.hideNewMessageIndicator();
        }
    },

    onActivateTab: function () {
        this.getDiscussion().startDiscussion(this.discussion_id, this.start_message);
        this.start_message = false;
        this.hideNewMessageIndicator();

        /*$('.message-send-wrapper').show();
         if (this.isBlocked) {
         $('.message-send-wrapper').hide();
         }*/

        this.onLoadChatLastMessages();
    },

    onLoadChatLastMessages: function() {
        if (this.load_chat_last_messages_and_files.length != 0) {
            $.post(this.load_chat_last_messages_and_files, {id: this.discussion_id}, $.proxy(this.onLoadChatMessagesSuccess, this));
        }
    },

    onLoadChatMessagesSuccess: function(result) {
        this.getChatPanelLastComments().html(result);

        this.initScrollBarUploadedFiles();

        $('[data-toggle="tooltip"]').tooltip();
    },

    getChatPanelLastComments: function() {
        return $('.chat-last-comment', this.getPanel());
    },

    onToggleLastMessageFilesView: function(e) {
        var $from = $(e.target);

        $("[data-toggled=" + $from.data('toggle') + "]").removeClass('is-grid-mode is-list-mode').addClass('is-' + $from.data('view') + '-mode');
    },

    onDownloadDiscussionMessageFiles: function(e) {
        var $from = $(e.target);

        $.post($from.data('url'),
            { messages_ids: $from.data('from-messages') },
            $.proxy(this.onDownloadDiscussionMessageFilesSuccess, this)
        );
    },

    onDownloadDiscussionMessageFilesSuccess: function(result) {
        if (result.success) {
            window.location.href = '/uploads/messages/' + result.file_name;
        } else {
            showAlertPopup('Ошибка загрузки', 'Ошибка загрузки файлов сообщени(я, й).');
        }
    },

    initScrollBarUploadedFiles: function() {
        var self = this;

        if (this.getScrollBarUploadedFiles().length > 0) {
            this.getScrollBarUploadedFiles().tinyscrollbar({size: 200, sizethumb: 41});
            this.getScrollBarDiscussion().tinyscrollbar({size: 135, sizethumb: 41});

            setTimeout(function () {
                self.getScrollBarUploadedFiles().tinyscrollbar_update(0);
                self.getScrollBarDiscussion().tinyscrollbar_update(0);
            }, 500);
        }
    },

    getScrollBarUploadedFiles: function() {
        return $(this.scroller);
    },

    getScrollBarDiscussion: function() {
        return $(this.scroller_discussion);
    }
}
