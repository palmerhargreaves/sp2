AgreementModelForm = function (config) {
    // configurable {
    this.add_url = '';
    this.cancel_url = '';
    this.cancel_scenario_url = '';
    this.cancel_record_url = '';
    this.delete_url = '';
    this.load_concept_cert_fields_url = '';
    this.load_model_block_url = '';
    this.load_record_block_url = '';
    this.dates_field_url = '';
    this.load_dates_and_certificates = '';
    this.delete_date_field = '';
    this.on_load_model_category_types = '';
    this.on_get_category_field_type_url = '';
    this.change_model_period_url = '';
    this.on_load_model_type_identifier = '';
    this.on_load_model_category_mime_types_forbidden = '';

    this.concept_type_id = 0;
    this.max_ext_files = 10;
    // }
    this.model_id = 0;

    this.model_file_uploader = undefined;
    this.concept_file_uploader = undefined;
    this.model_record_file_uploader = undefined;

    this.init_delete_files_event = false;

    AgreementModelForm.superclass.constructor.call(this, config);

    this.model_scenario_record = null;
    this.values = undefined;

    this.prev_model_type_id = -1;
    this.prev_model_category_id = -1;
    this.allow_submit_form_with_no_model_changes = false;
}

utils.extend(AgreementModelForm, AgreementModelBaseForm, {
    start: function () {
        AgreementModelForm.superclass.start.call(this);

        this.syncModelType();

        return this;
    },

    initEvents: function () {
        AgreementModelForm.superclass.initEvents.call(this);

        this.getTab().on('activated', $.proxy(this.onActivateTab, this));

        //Model categories and types: still here
        if (this.getModelCategoryField().length > 0) {
            $(document).on('change', ':input[name=model_type_id]', $.proxy(this.onSelectModelType, this));
        } else {
            this.getModelTypeField().change($.proxy(this.onSelectModelType, this));
        }

        this.getModelCategoryField().change($.proxy(this.onSelectModelCategory, this));

        this.getDraftButton().click($.proxy(this.onClickDraft, this));

        this.getCancelButton().click($.proxy(this.onClickCancel, this));
        this.getCancelButtonScenario().click($.proxy(this.onClickCancelScenario, this));
        this.getCancelButtonRecord().click($.proxy(this.onClickCancelRecord, this));

        this.getDeleteButton().click($.proxy(this.onDelete, this));

        this.getConceptAddFileLink().click($.proxy(this.onAddConceptModelFile, this));

        this.getNoModelChangesFieldValues().click($.proxy(this.onClickNoModelChange, this));
        this.getMainModelRemoveLink().click($.proxy(this.onRemoveMainModelFile, this));

        this.getDatesPanelFirst().on('click', '.dates-add-field', $.proxy(this.onAddDatesField, this));

        this.getChildFieldAddCtrl().click($.proxy(this.onAddChildCtrlFields, this));

        $('.submit-btn', this.getForm()).on('click', $.proxy(this.onPreSubmitForm, this));

        $('input[name=no_model_changes]', this.getForm()).on('click', $.proxy(this.onChangeNoModelChangesFlag, this));

        /*Work with uploaded model files*/
        //Модуль инициализируется два раза, мы разрешаем инициализзацию только одном из модулей
        if (this.init_delete_files_event) {
            $(document).on('click', '.remove-uploaded-model-file, .remove-uploaded-model-file-category', $.proxy(this.onDeleteUploadedModelFile, this));
            $(document).on('click', '.remove-uploaded-model-record-file, .remove-uploaded-model-record-file-category', $.proxy(this.onDeleteUploadedModelRecordFile, this));
        }
    },

    reset: function () {
        AgreementModelForm.superclass.reset.call(this);

        this.getNoModelChangesFieldValues().attr('disabled', false);

        this.setValue('model_type_id', this.getFirstModelTypeValue());
        this.splitPeriods();
        this.splitSizes(true);
        this.getDraftField().val('false');

        this.getCertificateDatePanel().show();
        this.getDatesPanel().show();
        this.getDatesPanels().remove();

        this.getModelFilesCaption().html('Для выбора файлов нажмите на кнопку или перетащите их сюда');
        this.getModelRecordFilesCaption().html('Для выбора файлов нажмите на кнопку или перетащите их сюда');

        this.getModelFileBlock().html('');
        this.getConceptFileBlock().html('');
        this.getModelRecordBlock().hide();

        this.getModelFileField().prop('disabled', false);
        this.getFileLabel().html("<strong>Макет</strong><span class='upload-info'>(до 10 файлов, каждый весом не более 5 МБ)</span>");

        this.getModelAddFilesButton().show();
        this.getModelAddRecordFilesButton().show();

        this.enableModelTypeSelect();
        this.getModelCategoryTypeFieldSelect().hide();

        this.changeModelTypeViewForciblyShow();

        $(".change-period, .js-change-model-period").data('action', 'change').hide();
        //this.concept_file_uploader.reset();
        if (this.getModelUploader() != undefined) {
            this.getModelUploader().reset();
            this.getConceptUploader().reset();
            this.getModelRecordUploader().reset();
        }

        this.model_scenario_record = undefined;
        this.getNoModelChangesFieldValues().attr('checked', false);
        this.getNoModelChangesFieldValues().attr('disabled', false);

        this.getCancelButtonRecord().hide();
        this.getCancelButtonScenario().hide();
    },

    onPreSubmitForm: function (event) {
        event.preventDefault();

        if (this.allow_submit_form_with_no_model_changes) {
            //Для заявок, у которых стоит галочка В макет не вносились изменения, выводим диалог с сообщением и возможностью выбора, продолжить добавление заявки или отказаться
            var self = this;
            swal({
                    title: "Отправить заявку?",
                    text: "Подтверждаю, что шаблон макета взят с портала Servicepool и в него не вносились никакие изменения (изменение даже одного символа считается внесением правок в шаблон). В случае, если при проверке заявки будет выявлено обратное, заявка засчитана не будет.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Потдвердить",
                    cancelButtonText: "Отменить",
                    closeOnConfirm: true
                },
                function (result) {
                    if (result) {
                        self.getForm().submit();
                    }
                });
        } else {
            this.getForm().submit();
        }
    },

    onChangeNoModelChangesFlag: function (event) {
        this.allow_submit_form_with_no_model_changes = $(event.currentTarget).is(':checked');
    },

    onSelectModelCategory: function () {
        this.deleteUploadedModelFilesIfChangeCategory();
        this.onGetCategoryTypesList();
    },

    onGetCategoryTypesList: function (values) {
        this.values = values;

        if (this.on_load_model_category_types.length > 0) {
            $.post(this.on_load_model_category_types, {
                category_id: this.getModelCategoryField().val(),
                model_id: values != undefined ? values.id : 0
            }, $.proxy(this.onLoadModelCategoryTypeSuccess, this));

            $.post(this.on_load_model_category_mime_types_forbidden, {
                category_id: this.getModelCategoryField().val(),
            }, $.proxy(this.onLoadModelCategoryMimeForbiddenType, this))
        }

        this.syncModelType();
    },

    onLoadModelCategoryMimeForbiddenType: function (result) {
        if (this.model_file_uploader != undefined) {
            this.model_file_uploader.setForbiddenFilesMimeTypes(result);
        }
    },

    onLoadModelCategoryTypeSuccess: function (result) {
        this.getModelCategoryTypesContainer().html(result).closest('tr').show();
        $('.model-category-types-select').krikselect();

        this.enableDisableModelType();
    },

    enableDisableModelType: function () {
        if (this.values != undefined) {
            var model_type_id = this.values.model_category_id != 0 ? this.values.model_category_id : this.values.model_type_id;

            this.disableModelTypeSelect();
            if (this.values.css_status == 'pencil') {
                this.enableModelTypeSelect();
            }

            this.hideShowModelFieldBlocks(model_type_id);
        } else {
            this.enableModelTypeSelect();
        }
    },

    deleteModel: function () {
        $.post(this.delete_url, {
            id: this.getIdField().val()
        }, $.proxy(this.onDeleteSuccess, this));
    },

    showLoader: function () {
        this.getDraftButton().hide();

        AgreementModelForm.superclass.showLoader.call(this);
    },

    hideLoader: function () {
        this.getDraftButton().show();

        AgreementModelForm.superclass.hideLoader.call(this);
    },

    enableTypeSelect: function () {
        this.getModelTypeSelect().removeClass('inactive');
    },

    disableTypeSelect: function () {
        this.getModelTypeSelect().addClass('inactive');
    },

    enableModelTypeSelect: function () {
        this.getModelTypeSelect().removeClass('inactive input');
        this.getModelTypeSelect().next().hide();

        this.getModelCategorySelect().removeClass('inactive input');
        this.getModelCategorySelect().next().hide();
    },

    disableModelTypeSelect: function () {

        this.getModelTypeSelect().addClass('inactive input');
        this.getModelTypeSelect().next().show();

        this.getModelCategorySelect().addClass('inactive input');
        this.getModelCategorySelect().next().show();
    },

    disableModelTypeSelectWithoutValue: function () {
        this.getModelTypeSelect().addClass('inactive input');
        this.getModelCategorySelect().addClass('inactive input');
    },

    syncModelType: function (values) {
        var model_type_id = this.getModelCategoryField().length > 0 ? this.getModelCategoryField().val() : this.getModelTypeField().val();

        if (this.isConceptMode()) {
            this._switchToConceptMode(values);
        }
        else {
            this._switchToModelMode(model_type_id);
        }

        $('.type-fields', this.getForm()).hide().find(':input').data('skip-validate', 'true');
        this.getModelTypeFieldBlocks(model_type_id).show().find(':input').data('skip-validate', 'false');

        this.getModelTypeFieldBlocks(model_type_id).show().find(':input').data('skip-validate', 'false');

        //Установить для полей с типом Size значения по умолчанию если макет создается с помощью редактора макетов
        /*this.getModelTypeFieldBlocks(model_type_id)
         .find(':input.size-field')
         .each(function (ind, el) {
         if ($(el).data('value')) {
         $(el).val($(el).data('value'));
         }
         });*/

        var editorLinkBlock = this.getModelEditorLinkBlock();
        editorLinkBlock.hide();

        if (values == undefined) {
            $.each(this.getModelTypeFieldBlocks(model_type_id), function (ind, el) {
                if ($(el).data('is-hide')) {
                    $(el).hide();
                }
            });

            if (editorLinkBlock.data('link') != '') {
                editorLinkBlock.show();
            }

            if (this.isScenarioRecordModel()) {
                this.changeModelTitle(model_type_id);

                if (this.getNoModelChangesFieldValues().is(':checked')) {
                    this.getModelRecordBlock().show();
                }
            } else {
                this.getModelRecordBlock().hide();
            }
        }
    },

    changeModelTitle: function (model_type_id) {
        if (this.getModelCategoryField().length > 0) {
            this.getFileLabel().html("<strong>" + this.model_scenario_record.label[0] + "</strong><span class='upload-info'>(до 10 файлов, каждый весом не более 5 МБ)</span>");
            this.getFileModelRecordLabel().html("<strong>" + this.model_scenario_record.label[1] + "</strong>");
        } else {
            if (model_type_id == 2) {
                this.getFileLabel().html("<strong>Сценарий радиоролика</strong><span class='upload-info'>(до 10 файлов, каждый весом не более 5 МБ)</span>");
                this.getFileModelRecordLabel().html("<strong>Запись радиоролика</strong><span class='upload-info'>(до 10 файлов, каждый весом не более 5 МБ)</span>");
            }
            else if (model_type_id == 4) {
                this.getFileLabel().html("<strong>Сценарий видеоролика</strong><span class='upload-info'>(до 10 файлов, каждый весом не более 5 МБ)</span>");
                this.getFileModelRecordLabel().html("<strong>Запись видеоролика</strong><span class='upload-info'>(до 10 файлов, каждый весом не более 5 МБ)</span>");
            }
            else {
                this.getFileLabel().html("<strong>Макет</strong><span class='upload-info'>(до 10 файлов, каждый весом не более 5 МБ)</span>");
            }
        }
    },

    splitPeriods: function () {
        this.getPeriodGroups().each(function () {
            var $value_field = $('[type=hidden]', this);
            var value_field_name = $value_field.attr('name');
            var $start_period_field = $('[name="_' + value_field_name + '[start]"]', this);
            var $end_period_field = $('[name="_' + value_field_name + '[end]"]', this);
            var period = $value_field.val().split('-');

            $start_period_field.val(period[0] || "");
            $end_period_field.val(period[1] || "");
        });
    },

    implodePeriods: function () {
        this.getPeriodGroups().each(function () {
            var $value_field = $('[type=hidden]', this);
            var value_field_name = $value_field.attr('name');
            var $start_period_field = $('[name="_' + value_field_name + '[start]"]', this);
            var $end_period_field = $('[name="_' + value_field_name + '[end]"]', this);

            $value_field.val($start_period_field.val() + '-' + $end_period_field.val());
        });
    },

    splitSizes: function (clear) {
        this.getSizeGroups().each(function () {
            var $value_field = $('[type=hidden]', this);
            var value_field_name = $value_field.attr('name');
            var $start_size_field = $('[name="_' + value_field_name + '[start]"]', this);
            var $end_size_field = $('[name="_' + value_field_name + '[end]"]', this);
            var period = $value_field.val().split('x');

            $start_size_field.val(period[0] || "");
            $end_size_field.val(period[1] || "");

            if (clear != undefined && clear) {
                $start_size_field.prop('disabled', false);
                $end_size_field.prop('disabled', false);
            }
        });
    },

    implodeSizes: function () {
        this.getSizeGroups().each(function () {
            var $value_field = $('[type=hidden]', this);
            var value_field_name = $value_field.attr('name');
            var $start_period_field = $('[name="_' + value_field_name + '[start]"]', this);
            var $end_period_field = $('[name="_' + value_field_name + '[end]"]', this);

            $value_field.val($start_period_field.val() + 'x' + $end_period_field.val());
        });
    },

    hideShowModelFieldBlocks: function (model_type_id) {
        $.each(this.getModelTypeFieldBlocks(model_type_id), function (ind, el) {
            var field_element = $(el), v = field_element.find('div.value').text(), field_id = field_element.data('id');

            if (field_id != undefined) {
                if (v.length == 0 && field_element.data('is-hide') == 1) {
                    field_element.hide();
                } else if (v.length > 0) {
                    field_element.show();
                }
            }
        });
    },

    hideModelFieldBlocks: function (model_type_id) {
        $.each(this.getModelTypeFieldBlocks(model_type_id), function (ind, el) {
            var v = $(el).find('div.value').text();

            if (v.length == 0 && $(el).data('is-hide') == 1) {
                $(el).hide();
            }
        });
    },

    setValue: function (name, value) {
        AgreementModelForm.superclass.setValue.call(this, name, value);

        if (name == 'model_type_id') {
            this.syncModelType();
        }
    },

    changeModelPeriod: function (values, model_type_id) {
        var show = true;

        $(".change-period").hide();
        if (values == undefined) {
            return;
        }

        if (values.status == "declined" ||
            (
                values.haveReport != 0 &&
                (
                    values.reportStatus != "not_sent"
                    && values.reportStatus != "declined"
                    && values.reportStatus != "wait"
                    && values.reportStatus != ""
                )
            )
        ) {
            show = false;
        }

        if (values.status == 'accepted' || values.reportStatus == 'accepted') {
            show = true;
        }

        if (show) {
            $(".change-period, .js-change-model-period").show();
        }

        if (values.is_valid_model_category) {
            this.changePeriodByModelTypeId(values, ".js-change-model-period", 'by_model_category');
        } else {
            this.changePeriodByModelTypeId(values, ".change-period-model-type-" + values.model_type_id, 'by_model_type');
        }

    },

    changePeriodByModelTypeId: function (values, element, period_type) {
        var self = this;

        $(element).data("model-id", values.id);
        $(element).live('click', $.proxy(function (el) {
            var bt = $(el.srcElement);
            if (bt.data("action") == "change") {
                bt.data("action", "apply")
                    .closest("td").find("div.value").hide()
                    .closest("td").find("div.input").show();
            }
            else {
                bt.data("action", "change");

                var new_period = "";

                this.implodePeriods();
                this.getPeriodGroups().each(function () {
                    var $value_field = $('[type=hidden]', this);
                    if ($value_field.val() != "-") {
                        new_period = $value_field.val();
                    }
                });

                $.post(self.change_model_period_url,
                    {
                        modelId: bt.data("model-id"),
                        fieldId: bt.data("field-id"),
                        period: new_period,
                        period_type: period_type
                    },
                    function () {
                        bt.closest("td").find("div.value").show().text(new_period);
                        bt.closest("td").find("div.input").hide();

                        //location.reload();
                    });
            }
        }, this));
    },

    showFormModal: function () {
        AgreementModelForm.superclass.showFormModal.call(this);

        this.activateTab();
    },

    resetToAdd: function () {
        this.getForm().removeClass('edit view accepted add').addClass('add');
        this.getForm().attr('action', this.add_url);
        this.reset();
        this.getTab().addClass('pencil');
        this.getNumberFieldBlock().hide();

        $('input.dates-field').val('');

        $('input[name=concept_id]').val('');
        $('.select-value-model-concept').text('');

        $('input[name=task_id]').val('');
        $('.select-value-model-task').text('');

        $('.value-activity').hide();

        if (this.getDatesPanel().length > 0) {
            this.onLoadConceptCertFields();
        }
    },

    onLoadConceptCertFields: function () {
        var self = this;

        $.post(this.load_concept_cert_fields_url, {}, function (result) {
            self.getConceptDatesPeriodAction().empty().html(result);

            //$('input.dates-field').datepicker({dateFormat: "dd.mm.yy"});
            window.concept_dates_limit = new ActivityConceptDateLimit({}).start();
        });
    },

    sendCancel: function () {
        $.post(this.cancel_url, {
            id: this.getIdField().val()
        }, $.proxy(this.onCancelResponse, this));
    },

    applyValues: function (values) {
        var model_type_id = (values.model_category_id != 0 && values.model_category_id != 11) ? values.model_category_id : values.model_type_id,
            fields_idx = 2;

        AgreementModelForm.superclass.applyValues.call(this, values);

        this.syncModelType(values);
        this.splitPeriods();
        this.splitSizes();

        this.model_scenario_record = values.model_type_data;
        if (values.step1 && !values.step2 && values.model_type_data.is_scenario_record) {
            this.getForm().addClass(values.status == 'wait' ? 'view' : 'edit');
        }
        else {
            this.getForm().addClass(values.status == 'not_sent' || values.status == 'declined' ? 'edit' : 'view');
        }

        if (values.status == 'accepted') {
            this.getForm().addClass('accepted');
        }

        this.enable();

        this.getNumberFieldBlock().show();
        this.getNumberFieldValue().html(values.id);

        /*Model changes checker*/
        this.workWithModelChanges(values);
        if (values.model_accepted_in_online_redactor) {
            this.getModelAcceptedInOnlineRedactorFieldValues().attr('checked', 'checked');
            this.getModelAcceptedInOnlineRedactorFieldValues().attr('disabled', 'disabled');
        }

        if (values.model_blocked) {
            this.getForm().removeClass('edit').addClass('view');
            this.getCancelButton().hide();
        }

        $('.what-info').live('click', function () {
            $(this).popmessage('show', 'info', 'В случае, если данный макет был ранее утвержден, укажите в данном поле номер заявки, в которой был согласован макет');

            setTimeout(function () {
                $('.what-info').popmessage('hide');
            }, 5000);
        });

        if (this.getForm().hasClass('view')) {
            this.getModelAddFilesButton().hide();
            this.getModelAddRecordFilesButton().hide();
        } else {
            if (values.step1_value != 'accepted') {
                this.getModelAddFilesButton().show();
                this.getModelAddRecordFilesButton().hide();
            } else if (values.step1_value == 'accepted') {
                this.getModelAddFilesButton().hide();
                this.getModelAddRecordFilesButton().show();

                if (values.model_type_data.is_scenario_record) {
                    this.changeModelTitle(model_type_id);
                    this.onLoadModelRecordFiles(values);
                }
                else {
                    this.getModelRecordBlock().hide();
                }
            }
        }

        this.model_id = values.id;

        this.hideShowModelFieldBlocks(model_type_id);
        $.each(values, $.proxy(function (name, value) {
            if (name == values.model_type_identifier + "[place" + fields_idx + "]") {
                if (value.length != 0) {
                    $("input[name*=place" + fields_idx++ + "]").closest('tr.type-fields-' + model_type_id).show();
                } else {
                    $("input[name*=place" + fields_idx++ + "]").closest('tr.type-fields-' + model_type_id).hide();
                }
            }
        }, this));

        if (values.is_model_scenario) {
            this.getModelUploader().setUploadedFiles(values.model_uploaded_scenario_files);

            if (this.getModelRecordUploader() != undefined) {
                this.getModelRecordUploader().setUploadedFiles(values.model_uploaded_record_files)
            }
        } else {
            this.getModelUploader().setUploadedFiles(values.model_uploaded_files);
            if (this.getConceptUploader() != undefined) {
                this.getConceptUploader().setUploadedFiles(values.model_uploaded_files);
            }
        }

        if (values.status == "declined" || values.status == "not_sent" || values.status == "pencil" || values.status == 'wait') {
            this.disableModelTypeSelect();
            if (values.css_status == 'pencil') {
                this.enableModelTypeSelect();
            }
        }

        var editorLinkBlock = this.getModelEditorLinkBlock();
        editorLinkBlock.hide();

        if (values.editor_link.length != 0) {
            editorLinkBlock.find('a').attr('href', values.editor_link);
            editorLinkBlock.show();
        }

        this.changeModelPeriod(values, model_type_id);
        this.onLoadModelFiles(values);

        this.model_scenario_record = values.model_type_data;
        if (this.isScenarioRecordModel()) {
            this.changeModelTitle(model_type_id);
            this.onLoadModelRecordFiles(values);
        }
        else {
            this.getModelRecordBlock().hide();
        }

        this.loadDatesAndCertificates(values);

        this.prev_model_type_id = values.model_type_id;
        this.prev_model_category_id = values.model_category_id != 0 ? values.model_category_id : -1;

        //Get model category types only if category is selected
        if (values.model_category_id != 0 && values.model_category_id != 11) {
            this.onGetCategoryTypesList(values);
        }

        //Для заяков у которых стоит галочка В макет не вносились изменения и если заявка была отправлена в черновик, устанавливаем принудительно галочку для вывода сообщения
        if (values.no_model_changes && values.is_draft) {
            this.allow_submit_form_with_no_model_changes = true;
        }

        this._showHideActivityField(values);

    },

    changeModelTypeViewForciblyHide: function () {
        this.getModelTypeSelect().hide();
        this.getModelTypeSelect().next().show();
    },

    changeModelTypeViewForciblyShow: function () {
        this.getModelTypeSelect().show();
        this.getModelTypeSelect().next().hide();
    },

    _switchToDraftMode: function () {
        this.getDraftField().val('true');
        $(':input', this.getForm()).not('input[name=name], input[name=model_type_id]').data('skip-validate', 'true');
    },

    _switchToNormalMode: function () {
        this.getDraftField().val('false');
        $(':input', this.getForm()).data('skip-validate', 'false');

        //this.syncModelType();
    },

    _switchToConceptMode: function (values) {
        this.getModelModeFields().hide();
        this.getTab().html('<span>Концепция</span>');
        this.getFileLabel().html('Концепция');
        this.setValue('name', 'Концепция');

        this.getModelFormBlock().hide();
        this.getConceptFormBlock().show();
        this.getModelFileField().prop('disabled', true);

        this.getConceptFileField().prop('disabled', false);

        this.getConceptDatesPeriodAction().find(':input').data('required', 1);

        $(':input', this.getForm()).not('input[name=model_file]').data('skip-validate', 'true');

        this.getModelFileUploaderBlock().hide();
    },

    _switchToModelMode: function () {
        this.getModelModeFields().show();
        this.getTab().html('<span>Материал</span>');
        this.getFileLabel().html("<strong>Макет</strong><span class='upload-info'>(до 10 файлов, каждый весом не более 5 МБ)</span>");

        this.getModelFormBlock().show();
        this.getConceptFormBlock().hide();
        this.getModelFileField().prop('disabled', false);
        this.getConceptFileField().prop('disabled', true);

        this.getConceptDatesPeriodAction().find(':input').data('required', 0);

        $(':input', this.getForm()).data('skip-validate', 'false');

        this.getModelFileUploaderBlock().show();
    },

    _showHideActivityField: function (values) {
        this.getCancelButtonRecord().hide();
        this.getCancelButtonScenario().hide();

        if ($.trim(values.status) == "accepted") {
            $('tr.activity').find('div.krik-select').addClass('input');
            $('tr.activity').find('div.value-activity').show();
        } else {
            $('tr.activity').find('div.krik-select').removeClass('input');
            $('tr.activity').find('div.value-activity').hide();

            if (this.isScenarioRecordModel() && (values.status != 'not_sent' && values.status != 'declined')) {
                if (values.step1_value != "none" && values.step1_value != 'accepted') {
                    this.getCancelButtonScenario().show();
                }

                if (values.step2_value != "none" && values.step1_value == 'accepted') {
                    this.getCancelButtonRecord().show();
                }
                this.getCancelButton().hide();

                if ((values.status == 'wait_specialist' || values.status == 'wait_manager_specialist') && values.designer_status == 'wait') {
                    this.getForm().removeClass('edit').addClass('view delete draft');
                }
            }
        }
    },

    getConceptDatesPeriodAction: function () {
        return $(".model-concept-form", this.getForm());
    },

    isConceptMode: function (model_type_id) {
        if (model_type_id != undefined && model_type_id == 10) {
            return true;
        }

        return this.getModelTypeField().val() == this.concept_type_id;
    },

    getIdField: function () {
        return $('input[name=id]', this.getForm());
    },

    getFirstModelTypeValue: function () {
        return $('.model-type .select-item', this.getForm()).data('value');
    },

    getModelTypeSelect: function () {
        return this.getModelTypeField().parents('.select');
    },

    getModelTypeField: function () {
        return $(':input[name=model_type_id]', this.getForm());
    },

    getModelTypeFieldBlocks: function (id) {
        return $('.type-fields-' + id);
    },

    getDraftField: function () {
        return $('input[name=draft]', this.getForm());
    },

    getDraftButton: function () {
        return $('.draft-btn', this.getForm());
    },

    getDummyMsg: function () {
        return $('.dummy', this.getForm());
    },

    getSubmitButton: function () {
        return $('.submit-btn', this.getForm());
    },

    getDeleteButton: function () {
        return $('.delete-btn', this.getForm());
    },

    getCancelButton: function () {
        return $('.cancel-btn', this.getForm());
    },

    getCancelButtonScenario: function () {
        return $('.cancel-btn-scenario', this.getForm());
    },

    getCancelButtonRecord: function () {
        return $('.cancel-btn-record', this.getForm());
    },

    getPeriodGroups: function () {
        return $('.period-group', this.getForm());
    },

    getSizeGroups: function () {
        return $('.size-group', this.getForm());
    },

    getModelModeFields: function () {
        return $('.model-mode-field', this.getForm());
    },

    getFileLabel: function () {
        return $('.model-title', this.getForm());
    },

    getFileModelRecordLabel: function () {
        return $('.file-label-record', this.getForm());
    },

    getModelFormBlock: function () {
        return $('.model-form', this.getForm());
    },

    getConceptFormBlock: function () {
        return $('.concept-form', this.getForm());
    },

    getModelFileBlock: function () {
        return $(this.isConceptMode() ? '#concept_files' : '#model_files', this.getForm());
    },

    getConceptFileBlock: function () {
        return $('#concept_files', this.getForm());
    },

    getModelRecordFileBlock: function () {
        return $('#model_record_files', this.getForm());
    },

    getModelRecordBlock: function () {
        return $('.model-record-block', this.getForm());
    },

    getModelFileField: function () {
        return $('#model_file', this.getModelFormBlock());
    },

    getConceptFileField: function () {
        return $('input[name=model_file]', this.getConceptFormBlock());
    },

    getNumberFieldBlock: function () {
        return $('.number-field', this.getForm());
    },

    getNumberFieldValue: function () {
        return $('.value', this.getNumberFieldBlock());
    },

    getNoModelChangesFieldValues: function () {
        return $('input[name=no_model_changes]', this.getForm());
    },

    getModelAcceptedInOnlineRedactorFieldValues: function () {
        return $('input[name=model_accepted_in_online_redactor]', this.getForm());
    },

    getModelTypeLabel: function (model_type_id) {
        this.is_model_type_identifier_loaded = true;
        $.post(this.on_load_model_type_identifier, {
            id: model_type_id
        }, $.proxy(this.onLoadModelTypeIdentifierSuccess, this));
    },

    onLoadModelTypeIdentifierSuccess: function (data) {

    },

    onLoadModelFiles: function (values) {
        $.post(this.load_model_block_url, {
            id: values.id,
        }, $.proxy(this.onLoadModelFilesBlock, this));
    },

    getConceptAddFileLink: function () {
        return $('.model-add-ext-concept-file', this.getForm());
    },

    getMainModelRemoveLink: function () {
        return $('.remove-main-model-file', this.getForm())
    },

    getModelEditorLinkBlock: function () {
        return $('.model-editor-link', this.getForm());
    },

    onRemoveMainModelFile: function () {
        $("input[name='model_file']", this.getForm()).trigger('click');
    },

    onSelectModelType: function (e) {
        if (this.on_get_category_field_type_url.length > 0) {
            this.model_scenario_record = null;

            this.prev_model_type_id = this.getModelTypeField().val();
            $.post(this.on_get_category_field_type_url, {
                type_id: this.getModelTypeField().val(),
            }, $.proxy(this.onGetCategoryFieldType, this));
        }

        this.syncModelType();
        this.deleteUploadedModelFiles();
    },

    onGetCategoryFieldType: function (result) {
        this.model_scenario_record = result;

        this.syncModelType(undefined);
    },

    /**
     * Delete uploaded files list if we change model category
     */
    deleteUploadedModelFilesIfChangeCategory: function () {
        var model_type_category_id = this.getModelCategoryField().val();

        if (this.prev_model_category_id != -1 && this.prev_model_category_id != model_type_category_id) {
            var confirm_label = 'Внимание!. При смене категории заявки все загруженные файлы будут удалены. Изменить категорию заявки ?';

            if (this.getModelUploader() != undefined && this.getModelRecordUploader() != undefined && this.getModelUploader().getAlreadyUploadedFilesCount() > 0) {
                confirm_label = 'Внимание!. При смене категории заявки все загруженные файлы будут удалены (сценарий / запись). Изменить категорию заявки ?';
            }

            //If we have uploaded files check this and then delete
            if (this.getModelUploader() != undefined && this.getModelUploader().getAlreadyUploadedFilesCount() > 0) {
                if (confirm(confirm_label)) {
                    this.getModelUploader().deleteUploadedFiles();

                    this.prev_model_category_id = model_type_category_id;
                    this.prev_model_type_id = -1;

                    return true;
                } else {
                    this.getModelCategoryField().val(this.prev_model_category_id);
                    this.getModelItemCategoryByCategoryId(this.prev_model_category_id).trigger('click');

                    this.syncModelType();
                    //this.getModelTypeSelect().trigger('change');

                    return false;
                }
            }
        }
    },

    /**
     * Delete uploaded files list if we change model type
     */
    deleteUploadedModelFiles: function () {
        var model_type_id = this.getModelTypeField().val();

        if (this.prev_model_type_id != -1 && this.prev_model_type_id != model_type_id) {
            var confirm_label = 'Внимание!. При смене типа заявки все загруженные файлы будут удалены. Изменить тип заявки ?';

            if (this.getModelUploader() != undefined && this.getModelRecordUploader() != undefined && this.getModelUploader().getAlreadyUploadedFilesCount() > 0) {
                confirm_label = 'Внимание!. При смене типа заявки все загруженные файлы будут удалены (сценарий / запись). Изменить тип заявки ?';
            }

            //If we have uploaded files check this and then delete
            if (this.getModelUploader() != undefined && this.getModelUploader().getAlreadyUploadedFilesCount() > 0) {
                if (confirm(confirm_label)) {
                    this.getModelUploader().deleteUploadedFiles();

                    this.prev_model_type_id = model_type_id;
                } else {
                    this.getModelTypeField().val(this.prev_model_type_id);
                    this.getModelItemTypeByTypeId(this.prev_model_type_id).trigger('click');

                    this.syncModelType();
                    //this.getModelTypeSelect().trigger('change');
                }
            }
        }
    },

    onClickDraft: function () {
        this._switchToDraftMode();

        if (this.onSubmit(true)) {
            this.send();
        }

        this._switchToNormalMode();
    },

    onClickCancel: function () {
        if (confirm('Вы уверены?'))
            this.sendCancel();

        return false;
    },

    onCancelResponse: function (data) {
        if (data.success) {
            //Отправка сообщений в чат
            if (window.discussion_online != undefined) {
                window.discussion_online.onHaveNewMessage(data.message_data);
            }

            this.loadRowToEdit(this.getIdField().val());
        }
        else
            alert('Ошибка отмены');
    },

    onDelete: function () {
        if (confirm('Вы уверены?'))
            this.deleteModel();

        return false;
    },

    onDeleteSuccess: function () {
        location.href = location.pathname + '?' + Math.random();
    },

    onSubmit: function () {
        this.implodePeriods();
        this.implodeSizes();

        return AgreementModelForm.superclass.onSubmit.call(this);
    },

    onClickNoModelChange: function (el) {
        var $element = $(el.target), model_type_id = this.getModelTypeField().val();

        if (model_type_id == 2 || model_type_id == 4 || (this.model_scenario_record != undefined && this.model_scenario_record.is_scenario_record)) {
            if ($element.is(':checked')) {
                this.onLoadModelRecordFilesByClick();
            }
            else {
                this.getModelRecordBlock().hide();
                //this.getModelRecordBlock().html('');
            }
        }
    },

    //Model files block
    onLoadModelFilesBlock: function (data) {
        this.applyModelFilesBlockData(data);
    },

    applyModelFilesBlockData: function (data) {
        this.getModelFileBlock().html(data);

        var temp_caption = $('#model_files_caption_temp', this.getModelFileBlock());
        if (temp_caption != undefined) {
            this.getModelFilesCaption().html(temp_caption.text());

            temp_caption.remove();

            if (this.isConceptMode() && this.concept_file_uploader != undefined) {
                this.concept_file_uploader.drawFiles();
                this.concept_file_uploader.initScrollBar();
            }

            if (this.model_file_uploader != undefined) {
                this.model_file_uploader.drawFiles();
                this.model_file_uploader.initScrollBar();
            }
        }
        $('div.message', this.getForm()).hide();
    },

    getModelFilesCaption: function () {
        return $(this.isConceptMode() ? '#concept_files_caption' : '#model_files_caption', this.getForm());
    },

    getModelRecordFilesCaption: function () {
        return $('#model_record_files_caption', this.getForm());
    },

    //Certificate dates
    getDatesPanelFirst: function () {
        return $('tr.model-dates-field:first', this.getForm());
    },

    getDatesPanels: function () {
        return $('tr.model-dates-field:not(:first)', this.getForm());
    },

    getDatesPanel: function () {
        return $('tr.model-dates-field:last', this.getForm());
    },

    getCertificateDatePanel: function () {
        return $('tr.model-certificate-field', this.getForm());
    },

    onAddDatesField: function (e) {
        var self = this;

        $.post(this.dates_field_url, {}, function (result) {
            self.getDatesPanel().after(result);
            self.getDatesErrorMessage().hide();

            $('input.dates-field').datepicker({dateFormat: "dd.mm.yy"});

            var i = 1, text = $('tr.model-dates-field:first').find('td.label').text();
            $('tr.model-dates-field:not(:first)').each(function (ind, el) {
                $(el).find('td.label').empty().html(text + '№' + (i++));
            });
        });

    },

    getDatesErrorMessage: function () {
        return $('.dates-error-message', this.getForm());
    },

    loadDatesAndCertificates: function (values) {
        var self = this;

        $.post(this.load_dates_and_certificates, {id: values.id}, function (result) {
            if ($.trim(result).length != 0) {
                self.getCertificateDatePanel().remove();

                self.getDatesPanel().replaceWith(result);

                self.getDatesErrorMessage().hide();
                $('input.dates-field').datepicker({dateFormat: "dd.mm.yy"});

                self.getDatesPanelFirst().on('click', '.dates-add-field', $.proxy(self.onAddDatesField, self));
                self.getDatesPanels().on('click', '.remove-date-field', $.proxy(self.onDeleteDateField, self));
            }
            else {
                /*self.getCertificateDatePanel().hide();
                self.getDatesPanel().hide();*/
            }
        });
    },

    getRemoveDateFieldLink: function () {
        return $('.remove-date-field', this.getForm());
    },

    onDeleteDateField: function (e) {
        var el = $(e.target), id = el.data('id');

        if (confirm('Удалить дату ?')) {
            $.post(this.delete_date_field, {id: id}, function (result) {
                el.closest('tr').remove();
            });
        }
    },

    /*Work with uploaded model files*/
    onDeleteUploadedModelFile: function (e) {
        if (confirm('Удалить файл ?')) {
            $.post(this.model_file_uploader.delete_uploaded_file_url, {id: $(e.target).data('file-id')},
                $.proxy(this.onDeleteModelFileSuccess, this)
            );
        }
    },

    onDeleteUploadedModelRecordFile: function (e) {
        if (confirm('Удалить файл ?')) {
            $.post(this.model_file_uploader.delete_uploaded_file_url, {id: $(e.target).data('file-id')},
                $.proxy(this.onDeleteModelRecordFileSuccess, this)
            );
        }
    },

    /**
     * Delete temporary uploaded files in model / scenario files
     * @param data
     */
    onDeleteModelFileSuccess: function (data) {
        this.applyModelFilesBlockData(data);

        if (this.getModelUploader()) {
            this.getModelUploader().decrementAlreadyUploadedFile();
        }
    },

    /**
     * Delete temporary uploaded files in record files
     * @param data
     */
    onDeleteModelRecordFileSuccess: function (data) {
        this.applyModelRecordFilesBlockData(data);

        if (this.getModelRecordUploader()) {
            this.getModelRecordUploader().decrementAlreadyUploadedFile();
        }
    },

    getModelAddFilesButton: function () {
        return $(this.isConceptMode() ? '#js-file-trigger-concept' : '#js-file-trigger-model', this.getForm());
    },

    getModelAddRecordFilesButton: function () {
        return $('#js-file-trigger-model-record', this.getForm());
    },

    onLoadModelRecordFiles: function (values) {
        if (values != undefined && (values.step1_value == 'accepted' || values.no_model_changes)) {
            $.post(this.load_record_block_url, {
                id: this.model_id,
            }, $.proxy(this.onLoadModelRecordFilesBlockSuccess, this));
        }
    },

    onLoadModelRecordFilesByClick: function () {
        $.post(this.load_record_block_url, {
            id: this.model_id,
        }, $.proxy(this.onLoadModelRecordFilesBlockSuccess, this));
    },

    //Model files block
    onLoadModelRecordFilesBlockSuccess: function (data) {
        this.applyModelRecordFilesBlockData(data);
    },

    applyModelRecordFilesBlockData: function (data) {
        this.getModelRecordFileBlock().html(data);
        this.getModelRecordBlock().show();

        var temp_caption = $('#model_record_files_caption_temp', this.getModelRecordFileBlock());
        if (temp_caption != undefined) {
            this.getModelRecordFilesCaption().html(temp_caption.text());

            if (this.model_record_file_uploader != undefined) {
                this.model_record_file_uploader.decrementAlreadyUploadedFile();
                this.model_record_file_uploader.drawFiles();
                this.model_record_file_uploader.initScrollBar();
            }

            temp_caption.remove();
        }
        $('div.message', this.getForm()).hide();
    },

    getModelFileUploaderBlock: function () {
        return $('.model-file-uploader-block', this.getForm());
    },

    onActivateTab: function () {
        this.model_file_uploader.initScrollBar();
        this.concept_file_uploader.initScrollBar();
        this.model_record_file_uploader.initScrollBar();
    },

    getChildFieldAddCtrl: function () {
        return $('.js-add-child-field', this.getForm());
    },

    onAddChildCtrlFields: function (e) {
        var isHide = false, from = $(e.target);

        $.each($(".type-fields-" + from.data('parent-id'), this.getForm()), function (ind, el) {
            if (!$(el).is(':visible') && !isHide) {
                $(el).fadeIn();
                isHide = true;

                console.log($(el));
            }
        });
    },

    isScenarioRecordModel: function () {
        if (this.getModelCategoryField().length > 0 && this.model_scenario_record != null) {
            return this.model_scenario_record.is_scenario_record;
        }

        return (this.getModelTypeField().val() == 2 || this.getModelTypeField().val() == 4) ? true : false;
    },

    workWithModelChanges: function (values) {
        if (values.no_model_changes) {
            this.getNoModelChangesFieldValues().attr('checked', 'checked');
        }

        if (this.isScenarioRecordModel() && (values.step1_value == 'accepted' || values.no_model_changes)) {
            this.getNoModelChangesFieldValues().attr('disabled', true);
        }
    },

    isModelScenarioRecord: function () {
        if (this.getModelTypeField().val() == 2 || this.getModelTypeField().val() == 4) {
            return true;
        }

        return false;
    },

    onClickCancelScenario: function () {
        if (confirm('Вы уверены?'))
            this.sendCancelScenario();

        return false;
    },

    onClickCancelRecord: function () {
        if (confirm('Вы уверены?'))
            this.sendCancelRecord();

        return false;
    },

    sendCancelScenario: function () {
        $.post(this.cancel_scenario_url, {
            id: this.getIdField().val()
        }, $.proxy(this.onCancelResponse, this));
    },

    sendCancelRecord: function () {
        $.post(this.cancel_record_url, {
            id: this.getIdField().val()
        }, $.proxy(this.onCancelResponse, this));
    },

    getModelUploader: function () {
        return this.model_file_uploader;
    },

    getModelRecordUploader: function () {
        return this.model_record_file_uploader;
    },

    getConceptUploader: function () {
        return this.concept_file_uploader;
    },

    getModelItemTypeByTypeId: function (type_id) {
        return $('.select-model-type-item-' + type_id);
    },

    getModelItemCategoryByCategoryId: function (category_id) {
        return $('.select-model-category-item-' + category_id);
    },

    getModelCategoryField: function () {
        $model_category = $(':input[name=model_category_id]', this.getForm());

        if ($model_category.length > 0 && $model_category.data('is-blank') == 0) {
            return $model_category;
        }

        if ($model_category.data('blank-category-id') != undefined) {
            $model_category.val($model_category.data('blank-category-id'));
        }

        return $('#blank-data');
    },

    getModelCategoryTypesContainer: function () {
        return $('#model-category-types-container', this.getForm());
    },

    loadModelTypeListAndSelectItem: function (category_id, type_id) {
        this.type_id = type_id;

        if (this.on_load_model_category_types.length > 0 && category_id != undefined) {
            $.post(this.on_load_model_category_types, {
                category_id: category_id,
            }, $.proxy(this.onLoadModelCategoryTypeWithAutoSetItemSuccess, this));
        }
    },

    onLoadModelCategoryTypeWithAutoSetItemSuccess: function (result) {
        this.getModelCategoryTypesContainer().html(result).closest('tr').show();
        $('.model-category-types-select').krikselect();

        this.setValue('model_type_id', this.type_id);

        this.disableModelTypeSelectWithoutValue();
    },

    getModelCategorySelect: function () {
        return this.getModelCategoryField().parents('.select');
    },

    getModelCategoryTypeFieldSelect: function () {
        return $('.model-category-type-fields', this.getForm());
    },
});

$.fn.isBound = function (type, fn) {
    var data = this.data('events')[type];

    if (data === undefined || data.length === 0) {
        return false;
    }

    return (-1 !== $.inArray(fn, data));
};
