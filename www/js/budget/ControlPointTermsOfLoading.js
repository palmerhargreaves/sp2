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
                dealer_id = el.data('dealer-id'),
                def_value = el.data('def-value'),
                status = el.is(':checked') ? 1 : 0;

        if (status != def_value) {
            el.closest('td').css({'border-bottom': '1px solid red'});
        } else {
            el.closest('td').css({'border-bottom': '1px solid #d6d6d6'});
        }

        $.post(el.closest('table').data('url'), {
            year: year,
            quarter: quarter,
            dealer_id: dealer_id,
            status: status
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
