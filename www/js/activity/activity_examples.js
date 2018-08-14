/**
 * Created by kostet on 07.09.2016.
 */
ActivityExamples = function(config) {
    this.defaultFilter = '';
    this.download_file_handler = '';
    this.download_file_url = '';

    $.extend(this, config);
}

ActivityExamples.prototype = {
    start: function() {
        this.initEvents();
    },

    initEvents: function() {
        $('#examples-items').mixItUp({
            load: {
                filter: this.defaultFilter
            }
        });

        $(document).on('click', '.examples-activity-link', $.proxy(this.onExampleActivityItemClick, this));
        $(document).on('click', '.activities-example-img img', $.proxy(this.onShowExamplesMaterial, this));
        $(document).on('click', 'input[type=submit]', $.proxy(this.onSubmitForm, this));
        $(document).on('click', this.download_file_handler, $.proxy(this.downloadFile, this));

        $('.activities-examples-form form :input[name]').change($.proxy(this.onExamplesFilterByYear, this));

        $('[data-parent-to-show]').each(function(i, el) {
            $('[data-base-category-id=' + $(el).data('parent-to-show') + ']').show();
        });
    },

    downloadFile: function(e) {
        $.post(this.download_file_url, { file_id : $(e.target).data('image-file-id')}, $.proxy(this.onDownloadFileSuccess, this));
    },

    onDownloadFileSuccess: function(result) {
        if (result.success) {
            window.location.href = result.url;
        }
    },

    onShowExamplesMaterial: function(e) {
        var $from = $(e.target);

        this.getExamplesModalImg().attr('src', $from.data('preview-file'));
        this.getExampleModelHeader().html($from.data('title'));

        this.getExamplesModal().krikmodal('show');
    },

    onSubmitForm: function(e) {
        e.preventDefault();

        if ($.trim(this.getExamplesFilterByName().val()).length != 0) {
            this.getForm().submit();
        }
    },

    onExampleActivityItemClick: function(e) {
        var $from = $(e.target);

        this.clearExamplesActivityLinkEls();
        $from.parent().addClass('current');
    },

    getExamplesActivityLinkEls: function() {
        return $('.examples-activity-link');
    },

    clearExamplesActivityLinkEls: function() {
        this.getExamplesActivityLinkEls().each(function(i, el) {
            $(el).parent().removeClass('current');
        });
    },

    onExamplesFilterByYear: function() {
        this.getForm().submit();
    },

    getForm: function() {
        return $('.activities-examples-form form');
    },

    getExamplesFilterByName: function() {
        return $('.activities-examples-form input[name=activity_examples_filter_by_name]');
    },

    getExamplesModal: function() {
        return $('#examples-modal');
    },

    getExamplesModalImg: function() {
        return this.getExamplesModal().find('#examples-modal-img');
    },

    getExampleModelHeader: function() {
        return this.getExamplesModal().find('.modal-header');
    }
}
