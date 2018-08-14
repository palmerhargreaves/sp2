/**
 * Created by kostet on 26.08.2016.
 */

ActivityMaterialRequestNew = function (config) {
    this.on_send_request = '';
    this.btn_send_request = '';
    this.form = '';

    $.extend(this, config);
}

ActivityMaterialRequestNew.prototype = {
    start: function () {
        this.initEvents();

        return this;
    },

    initEvents: function () {
        this.getBtnSendRequest().click($.proxy(this.onSendRequest, this));
    },

    onSendRequest: function (e) {
        var valid = true;

        e.preventDefault();

        $('div.error-text').hide();
        $('.modal-input-wrapper, .modal-select-wrapper').removeClass('error');
        $('textarea').removeClass('error');

        if (this.getModelTypeId().val().length == 0) {
            this.getModelTypeId().parent().addClass('error').next('div.error-text').html('Поле должно быть заполнено.').fadeIn();
            valid = false;
        }

        $(':input', this.getForm()).filter(function () {
            return $(this).attr('required') != undefined;
        }).each(function (ind, el) {
            if ($(el).val().length == 0) {
                if ($(el).attr('type') != undefined) {
                    $(el).parent().addClass('error').siblings('div.error-text').html('Поле должно быть заполнено.').fadeIn();
                    valid = false;
                } else {
                    $(el).addClass('error').parent().next().eq(0).html('Поле должно быть заполнено.').fadeIn();
                    valid = false;
                }
            }
        });

        if (valid) {
            $.post(this.on_send_request, this.getForm().serialize(), $.proxy(this.onSendResult, this));
        } else {
            /*setTimeout(function () {
                $('div.error-text').fadeOut();
                $('.modal-input-wrapper').removeClass('error');
                $('textarea').removeClass('error');

            }, 2000);*/
        }
    },

    onSendResult: function (data) {
        if (data.success) {
            $('.js-materials-form-toggle').trigger('click');

            this.getForm().trigger('reset');
            this.getInfoDialog().krikmodal('show');
        }
    },

    getBtnSendRequest: function () {
        return $(this.btn_send_request, this.getForm());
    },

    getForm: function () {
        return $(this.form);
    },

    getModelTypeId: function () {
        return $('input[name=model_type_id]', this.getForm());
    },

    getInfoDialog: function () {
        return $('#success-send-request-to-new-material-dialog');
    }
}