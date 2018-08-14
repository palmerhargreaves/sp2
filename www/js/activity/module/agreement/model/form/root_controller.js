AgreementModelRootControler = function (config) {
    // configurable {
    this.report_form = null;
    this.model_form = null;
    this.discussion_controller = null;

    this.model_categories_form = null;
    this.report_categories_form = null;
    this.discussion_categories_controller = null;
    this.work_with_categories = false;

    this.btn_add_new_concept = '';

    this.model_row = '';
    this.concept_row = '';

    this.modal = ''; // required selector of form modal
    this.list_selector = ''; // required models list selector
    this.sort_url = ''; // url to sort models
    this.add_model_button = null; // required selector of a button to add model
    this.add_many_concepts_url = ''; //add many concepts

    // }
    $.extend(this, config);

    this.mode = false;
}

AgreementModelRootControler.prototype = {
    start: function () {
        this.initEvents();
        this.checkPath();

        return this;
    },

    initEvents: function () {
        this.getAddModelButton().click($.proxy(this.onAddModel, this));

        this.getModelForm().on('load', this.onLoadModel, this);
        this.getModelForm().on('select', this.onSelectRow, this);
        this.getReportForm().on('load', this.onLoadReport, this);

        this.getModal().on('close-modal', $.proxy(this.onCloseModal, this));
//    this.getList().on('click', '.has-sort', $.proxy(this.onSort, this));

        //add many concepts
        if (this.getAddManyConceptsButton().length > 0) {
            this.getAddManyConceptsButton().click($.proxy(this.onAddManyConcepts, this));
            this.getActivityConceptContainer().on('click', this.concept_row, $.proxy(this.onManyConceptRowClick, this));
        }
    },

    checkPath: function () {
        var matches = location.href.match(/#model\/([0-9]+)\/discussion\/([0-9]+)\/(.+)/);
        if (matches) {
            this.getReportForm().loadRowToEdit(matches[1]);
            this.getModelForm().loadRowToEdit(matches[1]);
            this.getDiscussionController().setDiscussion(matches[2]);
            this.mode = matches[3];
        }
    },

    showModal: function () {
        this.getModal().krikmodal('show');
    },

    addModel: function (event) {
        this.getModelForm().setValue('model_category_id', '');

        if (event != undefined) {
            var $from_button = $(event.target);

            if ($from_button.data('model-type-category-id') != 0) {
                this.getModelForm().setValue('model_category_id', $from_button.data('model-type-category-id'));
                this.getModelForm().loadModelTypeListAndSelectItem($from_button.data('model-type-category-id'), $from_button.data('model-type-id'));
            }

            if ($from_button.data('model-type') != undefined) {
                //this.getModelForm().getModelCategoryField().val();
                this.getModelForm().setValue('necessarily_id', $from_button.data('id'));

                this.getModelForm().getModelCategorySelect().trigger('change');
            }
        }

        this.showModal();

        this.getModelForm().resetToAdd();
        this.getModelForm().activateTab();
        this.getReportForm().disable();
        this.getReportForm().reset();
        this.getDiscussionController().disable();

        this.getModelForm().setValue('blank_id', 0);
        this.getModelForm().enableTypeSelect();

        this.getModelForm().getDummyMsg().hide();
        window.localStorage.setItem('isOutOfDate', 0);

    },

    addConcept: function () {
        this.showModal();

        this.getModelForm().resetToAdd();
        this.getModelForm().activateTab();
        this.getReportForm().disable();
        this.getReportForm().reset();
        this.getDiscussionController().disable();

        this.getModelForm().setValue('model_type_id', this.getModelForm().concept_type_id);
        this.getModelForm().enableTypeSelect();
    },

    addModelFromBlank: function ($row) {
        this.addModel();
        this.getModelForm().setValue('blank_id', $row.data('blank'));
        this.getModelForm().setValue('name', $row.data('name'));
        this.getModelForm().setValue('model_type_id', $row.data('type'));
        this.getModelForm().disableTypeSelect();
    },

    getList: function () {
        return $(this.list_selector);
    },

    getAddModelButton: function () {
        return $(this.add_model_button);
    },

    getModal: function () {
        return $(this.modal);
    },

    onAddModel: function (event) {
        this.addModel(event);

        return false;
    },

    getAddManyConceptsButton: function () {
        return $(this.btn_add_new_concept);
    },

    getManyConceptsContainer: function () {
        return $('#activity-concept > tbody');
    },

    onAddManyConcepts: function () {
        $.post(this.add_many_concepts_url, {}, $.proxy(this.onAddManyConceptsSuccess, this));
    },

    onAddManyConceptsSuccess: function (data) {
        this.getManyConceptsContainer().append(data);
    },

    onManyConceptRowClick: function () {
        this.onAddConcept();
    },

    getActivityConceptContainer: function () {
        return $('#activity-concept');
    },

    onLoadModel: function () {
        this.showModal();

        var message_re = /message\/([0-9]+)/;
        if (this.mode && this.mode.match(message_re)) {
            var matches = this.mode.match(message_re);
            this.getDiscussionController().setStartMessage(matches[1]);
            this.getDiscussionController().activateTab();
        } else if (this.mode == 'model' || !this.mode) {
            this.getModelForm().activateTab();
        }

        if (this.getModelForm().getValue('blank_id') == '0')
            this.getModelForm().enableTypeSelect();
        else
            this.getModelForm().disableTypeSelect();
    },

    onLoadReport: function () {
        if (this.mode == 'report') {
            if (this.getReportForm().isEnabled())
                this.getReportForm().activateTab();
            else
                this.getModelForm().activateTab();
        }
    },

    onCloseModal: function () {
        this.getDiscussionController().stopDiscussion();
    },

    onSelectRow: function (form, target) {
        this.mode = false;

        var $row = $(target).closest(this.model_row);
        if ($row.data('blank')) {
            this.onSelectBlank($row);
        }

        if ($(target).closest('.concept-category-row').data('new-concept'))
            this.onAddConcept();
    },

    onAddConcept: function () {
        this.addConcept();
    },

    onSelectBlank: function ($row) {
        this.addModelFromBlank($row);
    },

    onSort: function (e) {
        location.href = this.sort_url + '?sort=' + $(e.target).closest('.has-sort').data('sort');
    },

    getModelForm: function() {
        if (this.work_with_categories) {
            return this.model_categories_form;
        }

        return this.model_form;
    },

    getReportForm: function() {
        if (this.work_with_categories) {
            return this.report_categories_form;
        }

        return this.report_form;
    },

    getDiscussionController: function() {
        if (this.work_with_categories) {
            return this.discussion_categories_controller;
        }

        return this.discussion_controller;
    },
}
