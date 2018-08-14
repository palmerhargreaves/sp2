AutoPagerSearcher = function (config) {
    // configurable {
    this.search_form = ''; // required selector of seacrh form
    this.pager = null; // required an auto pager
    // }
    $.extend(this, config);
}

AutoPagerSearcher.prototype = {
    start: function () {
        this.initEvents();

        return this;
    },

    initEvents: function () {
        //this.getSearchForm().submit($.proxy(this.onSearch, this));

        $('#filters :input[name]').change($.proxy(this.onSearch, this));
        $('#filters .with-date').datepicker();
    },

    search: function () {
        this.pager.setParam('search_by_id', this.getSearchById().val());
        this.pager.setParam('search_by_dealer', this.getSearchByDealer().val());
        this.pager.setParam('search_by_date', this.getSearchByDate().val());

        this.pager.reload();
    },

    getSearchById: function() {
        return $('input[name=model]', this.getSearchForm());
    },

    getSearchByDealer: function() {
        return $('input[name=dealer_id]', this.getSearchForm());
    },

    getSearchByDate: function() {
        return $('input[name=by_date]', this.getSearchForm());
    },

    getSearchForm: function () {
        return $(this.search_form);
    },

    onSearch: function () {
        this.search();

        return false;
    }
}
