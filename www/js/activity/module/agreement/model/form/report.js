AgreementModelReportForm = function (config) {
    // configurable {
    this.add_url = '';
    this.cancel_url = '';
    this.tabs_selector = '';
    this.tab_selector = '';

    this.load_additional_financial_docs_files_url = '';

    this.delete_uploaded_add_fin_doc_files_url = '';
    // }
    this.report_file_additional_uploader = undefined;
    this.report_file_financial_uploader = undefined;
    this.concept_file_uploader = undefined;

    this.init_delete_files_event = false;

    AgreementModelReportForm.superclass.constructor.call(this, config);

    this.files_container = '';
    this.values = null;

    this.uploaded_additional_files_count = 0;
    this.uploaded_financial_files_count = 0;

    this.is_concept = false;
}


utils.extend(AgreementModelReportForm, AgreementModelBaseForm, {
    initEvents: function () {
        AgreementModelReportForm.superclass.initEvents.call(this);

        this.getTab().on('activated', $.proxy(this.onActivateTab, this));

        if (this.init_delete_files_event) {
            $(document).on('click', '.remove-uploaded-report-file-category', $.proxy(this.onDeleteUploadedFile, this));
        } else {
            $(document).on('click', '.remove-uploaded-report-file', $.proxy(this.onDeleteUploadedFile, this));
        }

        this.report_file_additional_uploader.on('report_files_change', this.onFilesChange, this);

        this.getSubmitButton().click($.proxy(this.onSubmitReport, this));
        this.getCancelButton().click($.proxy(this.onClickCancel, this));
        this.initCheckCostTimer();
    },

    initCheckCostTimer: function () {
        setInterval($.proxy(this.onCheckCost, this), 500);
    },

    reset: function () {
        AgreementModelBaseForm.superclass.reset.call(this);

        this.getTab().removeClass('clock ok pencil none');

        this.getReportBlockedInfo().hide();
        this.getCancelButton().hide();
        this.getSubmitButton().hide();

        this.getPopupFileTriggerButton().show();
        this.getCostField().removeAttr('disabled');

        this.getAdditionalReportField().removeAttr('disabled');
        this.getFinancialReportField().removeAttr('disabled');
        this.getConceptReportField().removeAttr('disabled');

        if (this.getReportAdditionalFileUploader() != undefined) {
            this.getReportAdditionalFileUploader().reset();
            this.getReportFinancialFileUploader().reset();
            this.getReportConceptFileUploader().reset();
        }
    },

    onSubmitReport: function(event) {
        var valid = true;

        event.preventDefault();
        if (this.getReportAdditionalFileUploader() != undefined && this.values != null) {
            if (this.values.places_count != 0 && this.values.places_count > (this.uploaded_additional_files_count + this.getReportAdditionalFileUploader().getUploadedFilesCount())) {
                showAlertPopup('Ошибка при отправке отчета:', 'Необходимо загрузить ' + this.values.places_count + ' файл(а,лов). Загружено ' + (this.uploaded_additional_files_count + this.getReportAdditionalFileUploader().getUploadedFilesCount()));
                valid = false;
            }
        }

        if (valid) {
            this.getForm().submit();
        }
    },

    onDeleteUploadedFile: function(e) {
        var $from = $(e.target);

        this.by_type = $from.data('by-type');
        this.report_id = $from.data('report-id');
        this.is_concept = $from.data('is-concept') == 1 ? true : false;

        console.log(this.is_concept);
        if (confirm('Удалить файл ?')) {
            $.post(this.delete_uploaded_add_fin_doc_files_url,
                {
                    id: $from.data('file-id'),
                    by_type: this.by_type
                },
                $.proxy(this.onDeleteFileSuccess, this));
        }
    },

    onDeleteFileSuccess: function(result) {
        //if (result.success)
        {
            this.loadFilesBlock(this.report_id, this.by_type,
                this.by_type == 'report_additional'
                ? this.onLoadAdditionalFilesBlocksSuccess
                : this.onLoadFinancialFilesBlocksSuccess
            );

            if (this.by_type == 'report_additional') {
                this.getReportAdditionalFileUploader().decrementAlreadyUploadedFile();
            } else {
                this.getReportFinancialFileUploader().decrementAlreadyUploadedFile();
            }

            this.uploaded_additional_files_count--;
        }
    },

    sendCancel: function () {
        $.post(this.cancel_url, {
            id: this.getIdField().val()
        }, $.proxy(this.onCancelResponse, this));
    },

    syncCostAndFinancialFile: function () {
        if (this.getCost()) {
            this.getFinacialFileBlock().show();
        }
        else {
            this.getFinacialFileBlock().hide();
        }
    },

    applyValues: function (values) {
        AgreementModelReportForm.superclass.applyValues.call(this, values);

        this.getForm().addClass(values.status == 'not_sent' || values.status == 'declined' ? 'edit' : 'view');
        if (values.model_status == "accepted") {
            this.enable();
        }
        else {
            this.disable();
        }

        this.uploaded_additional_files_count = values.report_additional_uploaded_files_count;
        this.uploaded_financial_files_count = values.report_financial_uploaded_files_count;

        if (values.isOutOfDate) {
            this.getCancelButton().hide();
            this.getSubmitButton().hide();

            this.getReportBlockedInfo().show();
        } else if (values.status == 'not_sent' || values.status == 'declined') {
            this.getSubmitButton().show();
        }

        values.is_concept ? this._switchToConceptMode() : this._switchToModelMode();
        values.cost == 0 ? $("input[name=cost]", this.getForm()).val('') : '';

        this.is_concept = values.is_concept;

        this.values = values;
        if (values && values.status != 'not_sent' && values.status != 'declined') {
            this.getCancelButton().show();
        }

        if (values.status == 'accepted') {
            this.getForm().addClass('accepted');
            this.getCancelButton().hide();
        }

        if (this.getForm().hasClass('view')) {
            this.getPopupFileTriggerButton().hide();
            this.getCostField().attr('disabled', true);

            if (values.is_concept) {
                this.getConceptReportField().attr('disabled', true);
            } else {
                this.getAdditionalReportField().attr('disabled', true);
                this.getFinancialReportField().attr('disabled', true);
            }
        }

        this.loadAdditionalFinancialDocs(values);
    },

    _switchToConceptMode: function () {
        this.getCostBlock().hide();

        this.getConceptFormBlock().show();
        this.getModelFormBlock().hide();
    },

    _switchToModelMode: function () {
        this.getCostBlock().show();

        this.getConceptFormBlock().hide();
        this.getModelFormBlock().show();
    },

    getModelConceptAddFinancialDocsFileLink: function () {
        return $('a.model-concept-report-add-financial-file', this.getForm());
    }
    ,

    getModelFormBlock: function () {
        return $('.model-form', this.getForm());
    }
    ,

    getConceptFormBlock: function () {
        return $('.concept-form', this.getForm());
    }
    ,

    getIdField: function () {
        return $('input[name=id]', this.getForm());
    }
    ,

    getCost: function () {
        return parseFloat(this.getCostField().val());
    }
    ,

    getCostField: function () {
        return $('input[name=cost]', this.getForm());
    }
    ,

    getCostBlock: function () {
        return $('.cost', this.getForm());
    }
    ,

    getFinacialFileBlock: function () {
        return $('.financial-file', this.getForm());
    }
    ,

    getCancelButton: function () {
        return $('.cancel-btn', this.getForm());
    }
    ,

    getSubmitButton: function () {
        return $('.submit-btn', this.getForm());
    }
    ,

    getReportBlockedInfo: function () {
        return $('.report-blocked-info', this.getForm());
    },

    onSelectModelType: function () {
        this.syncModelType();
    }
    ,

    onClickCancel: function () {
        if (confirm('Вы уверены?'))
            this.sendCancel();

        return false;
    }
    ,

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
    }
    ,

    onCheckCost: function () {
        this.syncCostAndFinancialFile();
    },

    loadAdditionalFinancialDocs: function(values) {
        this.loadFilesBlock(values.report_id, 'report_additional', this.onLoadAdditionalFilesBlocksSuccess);
        this.loadFilesBlock(values.report_id, 'report_financial', this.onLoadFinancialFilesBlocksSuccess);

        this.getReportAdditionalFileUploader().setUploadedFilesCount(this.uploaded_additional_files_count);
        this.getReportFinancialFileUploader().setUploadedFilesCount(this.uploaded_financial_files_count);
        this.getReportConceptFileUploader().setUploadedFilesCount(this.uploaded_additional_files_count);
    },

    loadFilesBlock: function(report_id, type, callback) {
        $.post(this.load_additional_financial_docs_files_url,
            {
                id: report_id,
                by_type: type,
            },
            $.proxy(callback, this));
    },

    onLoadAdditionalFilesBlocksSuccess: function(result) {
        this.getAdditionalFilesContainer().html(result);
        this.getReportAdditionalCaptionContainer().html(this.getTempCaption('report_additional'));

        if (this.getReportAdditionalFileUploader() != undefined) {
            this.getReportAdditionalFileUploader().drawFiles();
        }
    },

    onLoadFinancialFilesBlocksSuccess: function(result) {
        this.getFinancialDocsFilesContainer().html(result);
        this.getReportFinancialCaptionContainer().html(this.getTempCaption('report_financial'));

        if (this.getReportFinancialFileUploader() != undefined) {
            this.getReportFinancialFileUploader().drawFiles();
        }
    },

    getAdditionalReportField: function() {
        return $('#additional_file', this.getForm());
    },

    getFinancialReportField: function() {
        return $('#financial_docs_file', this.getForm());
    },

    getConceptReportField: function() {
        return $('#concept_report_file', this.getForm());
    },

    getAdditionalFilesContainer: function() {
        return $('#report_additional_files', this.getForm());
    },

    getFinancialDocsFilesContainer: function() {
        if (this.is_concept) {
            return $('#concept_report_files', this.getForm());
        }

        return $('#report_financial_files', this.getForm());
    },

    getReportFinancialCaptionContainer: function() {
        if (this.is_concept) {
            return $('#concept_report_files_caption', this.getForm());
        }

        return $('#report_financial_files_caption', this.getForm());
    },

    getReportAdditionalCaptionContainer: function() {
        return $('#report_additional_files_caption', this.getForm());
    },

    getTempCaption: function(by_type) {
        return $('#report_files_caption_' + by_type +'_temp', this.getForm());
    },

    getPopupFileTriggerButton: function() {
        return $('.js-d-popup-file-trigger', this.getForm());
    },

    getTab: function () {
        return $(this.tab_selector);
    },

    getTabs: function () {
        return $(this.tabs_selector);
    },

    activateTab: function () {
        this.getTabs().kriktab('activate', this.getTab());
    },

    onActivateTab: function () {
        this.getReportAdditionalFileUploader().initScrollBar();
        this.getReportFinancialFileUploader().initScrollBar();
        this.getReportConceptFileUploader().initScrollBar();
    },

    onFilesChange: function() {
        //this.updateScrollBar();
    },

    getReportAdditionalFileUploader: function() {
        return this.report_file_additional_uploader;
    },

    getReportFinancialFileUploader: function() {
        return this.report_file_financial_uploader;
    },

    getReportConceptFileUploader: function() {
        return this.concept_file_uploader;
    }
});
