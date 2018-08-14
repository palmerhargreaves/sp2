Form = function (config) {
    // configurable {
    this.form = ''; // required form selector
    this.success_modal = ''; // modal with a success message
    this.modal_selector = null; // modal selector to auto reset form by open window
    this.default_message_field = false;
    this.default_messages = {}

    this.button_selector = ':submit';
    this.button_selector_cls = '.submit-btn';

    this.enable_loader_image = true;
    this.loader_image = '/images/form-loader.gif';
    this.MAX_FILE_SIZE = 10194304;
    // }

    this.addEvents({
        success: true,
        error: true
    });

    Form.superclass.constructor.call(this, config);

    this.loader_selector = null;
    this.allow_submit_form_with_no_model_changes = false;
}

utils.extend(Form, utils.Observable, {
    start: function () {
        this.initEvents();
        this.initModal();
        this.showWarnings();

        return this;
    },

    initEvents: function () {
        this.getForm().submit($.proxy(this.onSubmit, this));

        $(':input[name=accept_in_model]').live('input', function (e) {
            var regEx = new RegExp(/^[0-9.]+$/);

            if ($(this).val().length == 0)
                $(this).val(0);

            if (!regEx.test($(this).val())) {
                $(this).popmessage('show', 'error', 'Только числа.');
                $(this).val($(this).val().replace(/[^\d]/, ''));

            }
        });
    },

    initModal: function () {
        if (!this.modal_selector)
            return;

        $(this.modal_selector).on('show-modal', $.proxy(this.onOpenModal, this));
    },

    showLoader: function () {
        if (this.enable_loader_image) {
            /*this.getLoader().show();
             this.getButton().hide();*/

            this.getButton().parent().addClass('loader-bg');
        }
    },

    hideLoader: function () {
        if (this.enable_loader_image) {
            /*this.getLoader().hide();
             this.getButton().show();*/
            this.getButton().parent().removeClass('loader-bg');
        }
    },

    send: function () {
        this.showLoader();

        this.getForm().submit();
    },

    showWarnings: function () {
        $(':input', this.getForm()).popmessage('show', 'warning');
    },

    reset: function () {
        this.getForm().get(0).reset();
        $(':input', this.getForm()).popmessage('hide');
        $(':input', this.getForm()).trigger('reset').trigger('update');

        $.each(this.default_messages, $.proxy(function (name, text) {
            $(':input[name="' + name + '"]', this.getForm()).popmessage('show', 'warning', text);
        }, this));
    },

    validate: function () {
        var valid = true;

        var $acceptInModel = $(':input[name=accept_in_model]');
        if ($.trim($acceptInModel.val()).length != 0) {
            if (parseInt($acceptInModel.val()) == NaN) {
                $acceptInModel.popmessage('show', 'error', 'Только числа.');
                valid = false;
            }
        }

        $(':input', this.getForm()).filter(function () {
            return $(this).data('skip-validate') != 'true' && !$(this).hasClass('empty');
        }).each(function () {
            var $field = $(this);
            var value = $.trim($field.val());

            if ($field.data('required') && $field.is(':visible')) {
                if (value == '' || $field.is(':checkbox') && !$field.is(':checked')) {
                    $field.popmessage('show', 'error', 'Поле должно быть заполнено');
                    valid = false;

                    return;
                }
            } else if (value == '') {
                return;
            }

            if (!$field.data('format-expression'))
                return;

            var re = new RegExp($field.data('format-expression'), $field.data('format-expression-flags'));
            if (!re.test(value)) {
                var msg = "Введено неверное значение.";

                if ($field.data('right-format'))
                    msg += '<br/>Пример: ' + $field.data('right-format');

                $field.popmessage('show', 'error', msg);
                valid = false;
            }
        });


        var selModelCategory = $(".select-value-model-category", this.getForm());
        if (selModelCategory.length != 0 && selModelCategory.text().length == 0) {
            selModelCategory.popmessage('show', 'error', 'Выберите категорию');
            valid = false;
        }

        var selModelType = $(".select-value-model-type", this.getForm());
        if (selModelType.length != 0 && selModelType.text().length == 0) {
            selModelType.popmessage('show', 'error', 'Выберите тип модели');
            valid = false;
        }

        var selModelConcept = $(".select-value-model-concept", this.getForm());
        if (selModelConcept.length != 0 && selModelConcept.text().length == 0) {
            selModelConcept.popmessage('show', 'error', 'Выберите мероприятие');
            valid = false;
        }

        $(':input[name*="[size][start]"], :input[name*="[size][end]"]', this.getForm()).filter(function () {
            return parseFloat(this.value).toFixed(1) == 0;
        }).each(function () {
            var $field = $(this);

            $field.popmessage('show', 'error', 'Введено неверное значение.');
            valid = false;
        });

        return valid;
    },

    getButton: function () {
        $bt = $(this.button_selector, this.getForm());
        if ($bt.length == 0) {
            return $(this.button_selector_cls, this.getForm());
        }

        return $bt;
    },

    getLoader: function () {
        if (!this.loader_selector) {
            var $loader = $('<img src="' + this.loader_image + '" class="form-loader" alt="загрузка..." alt="загрузка..."/>').insertAfter(this.getButton());
            this.loader_selector = $loader.getIdSelector();
        }
        return $(this.loader_selector);
    },

    getForm: function () {
        return $(this.form);
    },

    onSubmitNoModelChanges: function() {
        return true;
    },

    onSubmit: function (draft_mode) {
        $(':input', this.getForm()).popmessage('hide');

        if (!this.isConcept()) {
            if (this.getButton().hasClass('accept-from-draft') && !this.calcDateFromPeriod()) {
                return false;
            }

            var valid = this.validate();
            if (valid) {
                this.showLoader();

                return true;
            }
        }
        else {
            var valid = true;

            $('input.dates-field', this.getForm()).each(function () {
                var $field = $(this);
                var value = $.trim($field.val());

                if ($field.data('required')) {
                    if (value == '' || $field.is(':checkbox') && !$field.is(':checked')) {
                        $field.popmessage('show', 'error', 'Поле должно быть заполнено');
                        valid = false;

                        return;
                    }
                } else if (value == '') {
                    return;
                }

                if (!$field.data('format-expression')) {
                    return;
                }

                var re = new RegExp($field.data('format-expression'), $field.data('format-expression-flags'));
                if (!re.test(value)) {
                    var msg = "Введено неверное значение.";
                    if ($field.data('right-format')) {
                        msg += '<br/>Пример: ' + $field.data('right-format');
                    }

                    $field.popmessage('show', 'error', msg);
                    valid = false;
                }
            });

            if (valid) {
                if ($("input[name*=dates_of_service_action_start]").length > 0) {
                    var start_date = new Date($("input[name*=dates_of_service_action_start]").val().replace(/\./g, '-')).getTime(),
                        end_date = new Date($("input[name*=dates_of_service_action_end]").val().replace(/\./g, '-')).getTime();

                    if (end_date <= start_date) {
                        $("input[name*=dates_of_service_action_end]").popmessage('show', 'error', 'Неверный диапазон проведения мероприятия.');
                        valid = false;
                    }
                }
            }

            if (valid) {
                this.showLoader();

                return true;
            }
        }

        return false;
    },

    onResponse: function (data, temp) {
        this.showWarnings();

        if (data.success) {
            //Отправка сообщений в чат
            if (window.discussion_online != undefined) {
                window.discussion_online.onHaveNewMessage(data.message_data);
            }

            this.fireEvent('success', [this, data]);

            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            this.onSuccess();
        } else {
            this.hideLoader();
            this.fireEvent('error', [this, data]);
            this.onError(data.errors);
        }
    },

    onError: function (errors) {
        var fileMsgError = '';

        console.log(errors);
        if (!errors) {
            return;
        }

        if (!$.isArray(errors)) {
            $('#j-alert-login').html('У Вас нет прав для доступа к ресурсу!').fadeIn();
            setTimeout(function () {
                $('#j-alert-login').hide();
            }, 2500);
        } else {
            for (var i = 0; i < errors.length; i++) {
                var name = errors[i].name;
                var message = errors[i].message;
                var $field = $(':input[name=' + name + ']', this.getForm());

                if ($field.length == 0 && this.default_message_field) {
                    $field = $(':input[name=' + this.default_message_field + ']', this.getForm());
                }

                $field.popmessage('show', 'error', message);
                if (name == 'is_valid_data') {
                    addShakeAnim('.scroller', this.getForm());
                }

                if (name == 'is_valid_add_data') {
                    addShakeAnim('.scroller-add-docs', this.getForm());
                }

                if (name == 'is_valid_fin_data') {
                    addShakeAnim('.scroller-add-fin', this.getForm());
                }

                fileMsgError += message + '<br/>';
            }

            if (fileMsgError.length > 0) {
                showAlertPopup('При заполнении формы возникли следующие ошибки:', fileMsgError);
            }
        }
    },

    onSuccess: function () {
        $(this.success_modal).krikmodal('show');
    },

    onOpenModal: function () {
        this.reset();
    },

    isConcept: function () {
        return $('div.concept-form', this.getForm()).is(":visible");
    },

    parseDate: function (date) {
        if (date != undefined) {
            var calc_date = date.split('.').reverse();

            return new Date('20' + calc_date[0], calc_date[1] - 1, calc_date[2]);
        }

        return null;
    },

    calcDateFromPeriod: function () {
        var model_identity_obj = $('input[name=model_category_id]', this.getForm()), identity_from_category = true,
            self = this, result = true;

        if (model_identity_obj.length == 0 || model_identity_obj.val() == 11) {
            model_identity_obj = $('input[name=model_type_id]', this.getForm());
            identity_from_category = false;
        }

        $.ajax({
            url: '/agreement/model/model/type/identity',
            type: 'POST',
            data: {
                id: model_identity_obj.val(),
                identity_from_category: identity_from_category ? 1 : 0
            },
            success: function (result_xhr) {

                if (result_xhr.success) {
                    var input_date = identity_from_category ? $('input[name="period"]').val() : $('input[name="' + result_xhr.model_type_identity + '[period]"]').val(),
                        period_dates = input_date.split('-'),
                        start_date = self.makeTimeFromDate(period_dates[0]),
                        end_date = self.makeTimeFromDate(period_dates[1]);

                    var id = $($('input[type="hidden"][name="id"]')[0]).val(),
                        current_time = new Date().getTime() + (2 * 86400000);

                    if (start_date < current_time || start_date.length == 0 || start_date == null) {
                        self.getPeriodElement('start', result_xhr.model_type_identity, identity_from_category).popmessage('show', 'error', 'Необходимо исправить период размещения');
                        result = false;
                    }

                    if (end_date < current_time || end_date.length == 0 || end_date == null) {
                        self.getPeriodElement('end', result_xhr.model_type_identity, identity_from_category).popmessage('show', 'error', 'Необходимо исправить период размещения');
                        result = false;
                    }

                    if (start_date >= end_date) {
                        self.getPeriodElement('start', result_xhr.model_type_identity, identity_from_category).popmessage('show', 'error', 'Необходимо исправить период размещения');
                        self.getPeriodElement('end', result_xhr.model_type_identity, identity_from_category).popmessage('show', 'error', 'Необходимо исправить период размещения');

                        result = false;
                    }
                }
            },
            error: function () {
                result = false;
            },
            async: false
        });

        return result;
    },

    getPeriodElement: function (field_to_search, model_type_identity, identity_from_category) {
        var found_element = null,
            fields = identity_from_category ? $('input[name*="_period[' + field_to_search + ']"]') : $('input[name*="_' + model_type_identity + '[period][' + field_to_search + ']"]');

        fields.each(function (ind, date_field) {
            if ($(date_field).val() == '') {
                $(date_field).popmessage('show', 'error', 'Необходимо выбрать дату размещения');
                found_element = $(date_field);
            }
            else {
                found_element = $(date_field);
            }
        });

        return found_element;
    },

    makeTimeFromDate: function (date) {
        if (date == '') {
            return null;
        }

        var date_value = this.parseDate(date);
        if (date_value != null) {
            date_value = date_value.getTime();
        }

        return date_value;
    }
});
