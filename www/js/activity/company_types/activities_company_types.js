/**
 * Created by kostet on 21.07.2015.
 */
ActivitiesCompanyTypes = function(config) {
    // configurable {
    // }
    this.activities_filter_by_url = '';
    this.activity_tab = '';

    $.extend(this, config);

    this.model_id = 0;

    this.active_tab = '';
    this.active_company_id = 0;
}

ActivitiesCompanyTypes.prototype = {
    start: function() {
        this.initEvents();

        return this;
    },

    initEvents: function() {
        this.getActivitiesContainer().on('click', '.activity-main-page-sum-dummy', $.proxy(this.onCompanyTypeTabClick, this));
        this.getActivitiesContainer().on('click', '.activity-tab-header', $.proxy(this.onActivitiesTabChange, this));

        this.getActivitiesContainer().on('click', '#acts-owned, #acts-required', $.proxy(this.onFilterByOwnedActivities, this));
        this.getActivitiesContainer().on('change', 'input.sb_activity_status', $.proxy(this.onFilterByStatus, this));

        this.getActivitiesContainer().on('click', '.lnk-sort', $.proxy(this.onSortData, this));

        this.getActivitiesContainer().on('click', this.activity_tab, $.proxy(this.onActivityTabClick, this));

        this.initProgressBar();
    },

    onActivityTabClick: function (e) {
        var data = this.getFormData(false), $el = $(e.target);

        if ($el.data('is-loaded') == 0) {
            this.active_tab = $el.data('tab');
            this.active_company_id = $el.data('company-type-id');

            data += '&activities_tab=' + $el.data('tab') + '&activities_by_company=' + this.active_company_id;

            $el.data('is-loaded', 1);
            $.post(this.activities_filter_by_url, data, $.proxy(this.onLoadTabResultSuccess, this));
        }
    },

    onLoadTabResultSuccess: function(result) {
        $('#' + this.active_tab + '_' + this.active_company_id).html(result);

        this.initGroupsHeaders();
    },

    onSortData: function(e) {
        var $from = $(e.target);

        this.getSortLinks().removeClass('active');
        if ($from.data('sort-direction') == 'asc') {
            $from.data('sort-direction', 'desc');
        } else {
            $from.data('sort-direction', 'asc');
        }

        $from.addClass('active');

        this.getSortField().val($from.data('field-name'));
        this.getSortFieldDirection().val($from.data('sort-direction'));

        this.makeQuery(false);
    },

    onFilterByStatus: function(e) {
        var $from = $(e.target), status = $from.val();

        this.getFilterByStatus().val(status);
        this.makeQuery(false);
    },

    onFilterByOwnedActivities: function(e) {
        var $from = $(e.target);

        if ($from.attr('id') == 'acts-owned') {
            $('#acts-required').prop('checked', false);
        } else {
            $('#acts-owned').prop('checked', false);
        }

        this.makeQuery(false);
    },

    makeQuery: function(only_save_filters) {
        if (!only_save_filters) {
            this.showHideLoader(true);
        }

        $.post(this.activities_filter_by_url,
            this.getFormData(only_save_filters),
            $.proxy(this.onLoadResultSuccess, this));
    },

    getFormData: function(only_save_filters) {
        var data = this.getForm().serialize(), data_arr = [];

        data_arr.push('by_type_owned=' + (this.getOwnedCheck().is(':checked') ? 1 : 0));
        data_arr.push('by_type_required=' + (this.getRequiredCheck().is(':checked') ? 1 : 0));
        data_arr.push('save_filters=' + (only_save_filters ? 1 : 0));

        data += '&' + data_arr.join('&');

        return data;
    },

    onLoadResultSuccess: function(data) {
        this.showHideLoader(false);

        if (data.length > 0) {
            this.getActivitiesMainContentContainer().html(data);

            $('.krik-select', this.getActivitiesContainer()).krikselect();

            this.initGroupsHeaders();
        }
    },

    initGroupsHeaders: function () {
        $('.group-header').click(function () {
            $(this).parents('.group').toggleClass('open');
            $(this).parents('.group').find('.group-content').slideToggle();

            if ($(this).parents('.group').hasClass('open'))
                $('html,body').animate({scrollTop: $(this).offset().top}, 500);
        });
    },

    onActivitiesTabChange: function(e) {
        var $from = $(e.target);

        $.each($("a.activity-tab-header"), function(ind, el) {
            if($(el).parent().hasClass('active')) {
                $(el).parent().removeClass('active');
                $('#' + $(el).prop('name')).hide();
            }
        });

        if(!$from.parent().hasClass('active')) {
            $from.parent().addClass('active');
            $('#' + $from.prop('name')).fadeIn();
        }
    },

    onCompanyTypeTabClick: function(e) {
        var $from = $(e.target), $parent = $from.parent();

        if ($parent.hasClass('active')) {
            return;
        }
        this.resetData($from.data('id'));

        $parent.addClass('active');

        this.activateContentById($from.data('id'));
        this.getFilterByCompany().val($from.data('id'));

        this.makeQuery(true);
    },

    activateContentById: function(id) {
        this.getContainerContent().hide();

        $('.tab-pane').hide();

        $('.activity-container-content-key-' + id).fadeIn();
        $('.activity-tab-header_key_' + id + ':first').trigger('click');
    },

    resetData: function() {
        $('.activity-main-page-sum').removeClass('active');

        //$('[data-company-type-id=' + type_id + ']:first').fadeIn();
    },

    getActivitiesContainer: function() {
        return $('.activity-main-page');
    },

    getActivitiesMainContentContainer: function() {
        return $('.activity-main-content', this.getActivitiesContainer());
    },

    getContainerContent: function() {
        return $('.activity-container-content');
    },

    getOwnedCheck: function() {
        return $('#acts-owned', this.getActivitiesContainer());
    },

    getRequiredCheck: function() {
        return $('#acts-required', this.getActivitiesContainer());
    },

    showHideLoader: function(show) {
        if (show) {
            this.getLoadingBlock().fadeIn();
            this.getLoadingProgressBlock().fadeIn();
        } else {
            this.getLoadingBlock().fadeOut();
            this.getLoadingProgressBlock().fadeOut();
        }
    },

    getLoadingBlock: function() {
        return $('#loading-block');
    },

    getLoadingProgressBlock: function() {
        $block = $('.sk-folding-cube');

        $block.css('left', window.innerWidth / 2 + 'px');
        $block.css('top', ($('body').scrollTop() + $block.height()) + 'px');

        return $block;
    },

    getForm: function() {
        return $('#frm-activities-list');
    },

    getSortLinks: function() {
        return $('.lnk-sort', this.getActivitiesContainer())
    },

    getSortField: function() {
        return $('input[name=filter_field_name]');
    },

    getSortFieldDirection: function() {
        return $('input[name=filter_field_direction]');
    },

    getFilterByCompany: function() {
        return $('input[name=filter_by_company]');
    },

    getFilterByStatus: function() {
        return $('input[name=filter_by_status]');
    },

    initProgressBar: function() {
        $.each(this.getProgressBar(), function(i, item) {
            var $progress = $(item), percent_width_complete = $progress.data('percent') * $progress.parent().width() / 100;

            $progress.animate({
                width: percent_width_complete + 'px'
            }, 500);
        });
    },

    getProgressBar: function() {
        return $('.js-progressbar > i', this.getActivitiesContainer());
    }
}
