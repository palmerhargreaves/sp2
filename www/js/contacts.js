Contacts = function(config) {
    this.on_send_message_url = '';

    $.extend(this, config);

    this.selected_user = 0;
}

Contacts.prototype = {
    start: function() {
        window.messages = new Messages({}).start();

        this.initEvents();

        return this;
    },

    initEvents: function() {
        this.getSubmitButton().click($.proxy(this.onSendMessage, this));

        $(document).on('click', '.tabs .tab', $.proxy(this.onSwitchUser, this));
    },

    onSwitchUser: function(e) {
        var tab = $(e.currentTarget);

        this.setDefaultIcons();
        tab.find('.required-activities > img').attr('src', 'images/check-icon-active.png')

        this.selected_user = tab.data('user-id');

        this.getMessageField().attr('placeholder', 'Текст сообщения');
        this.getSubmitButton().removeClass('gray2').html('Отправить заявку (' + tab.data('user-name') + ')');
    },

    onSendMessage: function(e) {
        var message = $.trim(this.getMessageField().val()), btn = $(e.currentTarget);

        e.preventDefault();
        if (message.length < 10) {
            window.messages.showError('Сообщение должно содержать более 10-ти символов.');
            return;
        }

        if (this.selected_user == 0) {
            window.messages.showError('Выберите пользователя для отправкии сообщения.');
            return;
        }

        $.post(this.on_send_message_url, {
            message: message,
            user_who_get: this.selected_user,
            discussion_id: btn.data('dealer-discussion-id')
        }, $.proxy(this.onSendMessageResult, this));
    },

    onSendMessageResult: function(result) {
        if (result.success) {
            this.getMessageField().val('');

            window.messages.showSuccess(result.msg);
        }
    },

    getMessageField: function() {
        return $('textarea[name=message]', this.getForm());
    },

    getSubmitButton: function() {
        return $('#send-button', this.getForm());
    },

    getForm: function() {
        return $('#frm-contact-message');
    },

    setDefaultIcons: function() {
        $('.required-activities img').attr('src', 'images/check-icon.png');
    }
}
