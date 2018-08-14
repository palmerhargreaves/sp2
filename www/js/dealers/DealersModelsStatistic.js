/**
 * Created by kostet on 30.11.2016.
 */

DealersModelsStatistic = function (config) {
    this.data_by_filter_url = '';
    this.data_by_year_filter_url = '';
    this.content_container = '';
    this.dealer_id = 0;

    $.extend(this, config);
};

DealersModelsStatistic.prototype = {
    start: function () {
        this.initEvents();

        return this;
    },

    initEvents: function () {
        this.getCustomTabs().click($.proxy(this.onTabClick, this));

        $(document).on('change', 'input[name=model_status], input[name=selected_activity]', $.proxy(this.onFilterDataBy, this));
        $(document).on('change', 'input[name=selected_year]', $.proxy(this.onFilterByYear, this));
    },

    onFilterDataBy: function () {
        this.makeFilterReq();
    },

    onFilterByYear: function () {
        var data = this.makeFilterData();

        console.log(data);
        data.quarter = this.getCurrentCustomTab().data('quarter');

        this.showHideLoader(true);

        $.post(this.data_by_year_filter_url, data, $.proxy(this.onLoadFilterDataByYearSuccess, this));
    },

    onTabClick: function (e) {
        var $from = $(e.target), $tab = $from.is('div.js-custom-tab') ? $from : $from.closest('.js-custom-tab');

        if (!$tab.hasClass('current')) {
            this.getCustomTabs().removeClass('current');
            $tab.addClass('current');

            this.makeFilterReq();
        }
    },

    makeFilterReq: function () {
        var data = this.makeFilterData();

        data.quarter = this.getCurrentCustomTab().data('quarter');

        this.showHideLoader(true);
        $.post(this.data_by_filter_url, data, $.proxy(this.onLoadFilterDataSuccess, this));
    },

    onLoadFilterDataByYearSuccess: function(result) {
        this.getBudgetPanel().html(result.budget_panel);

        this.onLoadFilterDataSuccess(result.filter_data);
    },

    onLoadFilterDataSuccess: function (data) {
        this.getAjaxContainer().html(data);

        $('#sb-model-status').krikselect();
        this.showHideLoader(false);
    },

    getCustomTabs: function () {
        return $('.js-custom-tab', this.getMainContentContainer());
    },

    getCurrentCustomTab: function () {
        return $('div.current', this.getMainContentContainer());
    },

    getAjaxContainer: function () {
        return $('#content_container', this.getMainContentContainer());
    },

    getMainContentContainer: function () {
        return $('#activities-stats-container');
    },

    getSelectedActivity: function () {
        return $('input[name=selected_activity]', this.getMainContentContainer());
    },

    getSelectedYear: function () {
        return $('input[name=selected_year]', this.getMainContentContainer());
    },

    makeFilterData: function () {
        return {
            activity: this.getSelectedActivity().val(),
            year: this.getSelectedYear().val(),
            dealer_id: this.dealer_id,
            model_status: $('input[name=model_status]').val(),
        }
    },

    getLoadingBlock: function () {
        return $('#loading-block');
    },

    showHideLoader: function (show) {
        if (show) {
            this.getLoadingBlock().fadeIn();
            this.getLoadingProgressBlock().fadeIn();
        } else {
            this.getLoadingBlock().fadeOut();
            this.getLoadingProgressBlock().fadeOut();
        }
    },

    getLoadingProgressBlock: function () {
        $block = $('.sk-folding-cube');

        $block.css('left', window.innerWidth / 2 + 'px');
        $block.css('top', ($('body').scrollTop() + $block.height()) + 'px');

        return $block;
    },

    getBudgetPanel() {
        return $('#container-budget-panel', this.getMainContentContainer());
    },
};
