SpecialDiscussion = function (config) {
    SpecialDiscussion.superclass.constructor.call(this, config);

    this.userId = 0;
    this.discussion_id = 0;
    this.markAsRead = false;
    this.form = '';
}

utils.extend(SpecialDiscussion, Discussion, {
    initEvents: function () {
        this.getSpecialDiscussionButton().click($.proxy(this.onShowSpecialDialogSuccess, this));
        this.getSpecialMessagesPanel().on('click', '.special-discussion-button-submit', $.proxy(this.onSubmitSpecialMessage, this));
        this.getSpecialMessagesPanel().on('click', '.special-discussion-button-submit-read', $.proxy(this.onSubmitSpecialMessageAsRead, this));
        this.getSpecialMessagesPanel().on('click', '.special-discussion-button-close', $.proxy(this.onCloseButton, this));
    },

    sendMessage: function () {
        var $self = this;

        this.posting = true;

        this.saveFilesToSend();
        $.post($self.post_url, $.extend(this.getFilesParam(), {
                id: $self.discussion_id,
                message: $self.getSpecialMessageText().val(),
                userId: $self.user_id,
                markAsRead: $self.markAsRead,
            }))
            .success($.proxy(this.onPostSuccess, this))
            .error($.proxy(this.onPostError, this))
            .complete($.proxy(this.onPostComplete, this));
    },

    getPostForm: function () {
        return $('form.post-special', this.getPanel());
    },

    setUserId: function (uId) {
        this.userId = uId;
    },

    setDiscussionId: function (dId) {
        this.disussion_id = dId;
    },

    setMarkAsRead: function (mark) {
        this.maskAsRead = mark;
    },

    onPostSuccess: function () {
        this.getSpecialMessageText().val('');
        this.deleteSentFiles();

        this.onLoadMessagesList();
    },

    onPostComplete: function () {
        this.posting = false;
    },

    showDialog: function ($el) {
        var dId = $el.data('discussion-id'),
            uId = $el.data('user-id');

        this.form = $el.closest('form');
        this.discussion_id = dId;
        this.user_id = uId;

        this.getSpecialMessageText().removeClass('discussion-special-panel-error');

        this.getSpecialModal().krikmodal('show');
        this.onLoadMessagesList();
    },

    onLoadMessagesList: function () {
        $.post(this.form.data('url-list'),
            {id: this.discussion_id},
            $.proxy(this.onSuccessSpecialDiscussionLoad, this)
        );
    },

    onShowSpecialDialogSuccess: function (el) {
        this.showDialog($(el.target));
    },

    onSuccessSpecialDiscussionLoad: function (result) {
        this.getSpecialMessages().empty().html(result);
        this.makeScroller();
    },

    onSubmitSpecialMessage: function (el) {
        this.markAsRead = 0;
        this.onSubmitForm();
    },

    onSubmitSpecialMessageAsRead: function () {
        this.markAsRead = 1;

        this.onSubmitForm();
    },

    onCloseButton: function () {
        this.getSpecialModal().hide();
    },

    onSubmitForm: function () {

        var text = $.trim(this.getSpecialMessageText().val());
        if (text.length == 0) {
            this.getSpecialMessageText().addClass("discussion-special-panel-error");
            return;
        }

        this.sendMessage();

        /*this.getSpecialMessageText().val('');
         $.post(this.post_url,
         {
         id : this.discussion_id,
         userId : this.user_id,
         text : text,
         markAsRead : this.markAsRead
         },
         $.proxy(this.onSpecialMessageAddSuccess, this));              */
    },

    onSpecialMessageAddSuccess: function () {
        this.getSpecialMessages().empty().html(result);
        this.getSpecialMessageText().removeClass("discussion-special-panel-error");

        this.makeScroller();
    },

    getSpecialDiscussionButton: function () {
        return $('.special-discussion-button');
    },

    getSpecialDiscussionButtonSubmit: function (el) {
        return $('.special-discussion-button-submit', this.getPostForm());
    },

    getSpecialDiscussionButtonSubmitRead: function () {
        return $('.special-discussion-button-submit-read', this.getPostForm());
    },

    getSpecialDiscussionButtonClose: function () {
        return $('.special-discussion-button-close', this.getPostForm());
    },

    getSpecialMessages: function () {
        return $('.special-messages');
    },

    getSpecialMessagesPanel: function () {
        return $('.panel-special-message');
    },

    makeScroller: function () {
        $('.scroller').tinyscrollbar({size: 336, sizethumb: 41});
    },

    getSpecialModal: function () {
        return $("#special-modal");
    },

    getSpecialMessageText: function () {
        return $('textarea[name=special-message]');
    },


});
