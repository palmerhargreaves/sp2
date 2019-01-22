/**
 * Created by kostet on 22.01.2019.
 */

var ControlPointTermsOfLoading = function (config) {
    $.extend(this, config);

    messages = null;
}

ControlPointTermsOfLoading.prototype = {
    start: function() {
        this.initEvents();

        this.messages = new Messages({}).start();

        return this;
    },

    initEvents: function () {
        $(document).on("change", ".control-point-quarter", $.proxy(this.onChangeControlPointStatus, this));

        $(document).on("change", "#filters form input[name]", $.proxy(this.onChangeYear, this));
    },

    onChangeYear: function(e) {
        var el = $(e.currentTarget);

        this.getDealersListContainer().addClass('slideOutRight');
        $.post(el.closest('form').attr('action'), {
            year: el.val()
        }, $.proxy(this.onSelectYearResult, this));
    },

    onSelectYearResult: function(result) {
        if (result.data.length) {
            this.getDealersListContainer().html(result.data);
            this.getDealersListContainer().removeClass('slideOutRight').addClass('slideInLeft');
        }
    },

    onChangeControlPointStatus: function(e) {
        var el = $(e.currentTarget),
                year = el.data('year'),
                quarter = el.data('quarter'),
                dealer_id = el.data('dealer-id');

        $.post(el.closest('table').data('url'), {
            year: year,
            quarter: quarter,
            dealer_id: dealer_id,
            status: el.is(':checked') ? 1 : 0
        }, $.proxy(this.onChangeStatusResult, this));
    },

    onChangeStatusResult: function(result) {
        if (result.success) {
            this.messages.showSuccess('Статус успешно изменен!');
        } else {
            this.messages.showWarning('Ошибка изменения статуса!');
        }
    },

    getDealersListContainer: function() {
        return $('#dealers-list-container');
    }
}
