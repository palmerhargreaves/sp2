AgreementModelSpecialistRootController = function (config) {
    // configurable {
    this.modal_selector = ''; // required modal selector
    this.list_selector = ''; // required models list selector
    this.sort_url = ''; // url to sort models
    this.model_controller = null;
    this.report_controller = null;
    this.discussion_controller = null;


    // }
    $.extend(this, config);

    this.mode = false;
}

AgreementModelSpecialistRootController.prototype = {
    start: function () {
        this.initEvents();
        this.checkPath();

        return this;
    },

    initEvents: function () {
        this.getList().on('click', '.model-row', $.proxy(this.onClickRow, this));
        this.model_controller.on('load', this.onLoadModel, this);
        this.report_controller.on('load', this.onLoadReport, this);
        this.getModal().on('close-modal', $.proxy(this.onCloseModal, this));

        this.getReportTab().on('click', $.proxy(this.onActivateReportTab, this));
    },

    checkPath: function () {
        var matches = location.href.match(/#model\/([0-9]+)\/discussion\/([0-9]+)\/(.+)/);
        if (matches) {
            this.load(matches[1]);
            this.discussion_controller.setDiscussion(matches[2]);
            this.mode = matches[3];
        }
    },

    showModal: function () {
        this.getModal().krikmodal('show');
    },

    hideModal: function () {
        this.getModal().krikmodal('hide');
    },

    load: function (id) {
        this.showModal();
        this.model_controller.load(id);
        this.report_controller.load(id);
    },

    activateReportTab: function () {
        this.getTabs().kriktab('activate', this.getReportTab());
    },

    activateModelTab: function () {
        this.getTabs().kriktab('activate', this.getModelTab());
    },

    getModelTab: function () {
        return $('.model-tab', this.getTabs());
    },

    getReportTab: function () {
        return $('.report-tab', this.getTabs());
    },

    getTabs: function () {
        return $('.model-tabs', this.getModal());
    },

    getModal: function () {
        return $(this.modal_selector);
    },

    getList: function () {
        return $(this.list_selector);
    },

    onLoadModel: function (controller) {
        if (controller.getStatus() == 'accepted') {
            this.getReportTab().removeClass('disabled');
        } else {
            this.getReportTab().addClass('disabled');
        }

        if (controller.getStatus() == 'wait_specialist')
            this.activateModelTab();

        this.getModelTab().removeClass('clock ok pencil not_sent').addClass(controller.getStatus());

        if (controller.isConcept())
            this.getModelTab().html('<span>Концепция</span>')
        else
            this.getModelTab().html('<span>Материал</span>')

        this.mode = false;
    },

    onLoadReport: function (controller) {
        if (controller.getStatus() == 'wait_specialist')
            this.activateReportTab();

        this.getReportTab().removeClass('clock ok pencil not_sent').addClass(controller.getCssStatus());
    },

    onCloseModal: function () {
        this.discussion_controller.stopDiscussion();
    },

    onClickRow: function (e) {
        if ($(e.target).closest('a').length > 0)
            return;

        var id = $(e.target).closest('.model-row').data('model');
        this.load(id);
    },

    onActivateReportTab: function() {
        if (this.report_controller.report_file_uploader != undefined) {
            for(var idx = 1; idx <= 2; idx++) {
                var el = '.scroller-specialist-report-files-' + idx;

                if ($(el).length > 0) {
                    $(el).tinyscrollbar({size: 200, sizethumb: 41});
                    setTimeout(function () {
                        $(el).tinyscrollbar_update(0);
                    }, 500);
                }
            }
        }
    }
}
