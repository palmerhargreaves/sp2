var ActivityConceptDateLimit = function(config) {

    $.extend(this, config);
}

ActivityConceptDateLimit.prototype = {
    start: function() {
        this.initEvents();
        this.initValues();

        return this;
    },

    initEvents: function() {

    },

    initValues: function() {
        this.initDatePicker('input.dates-concept-field', 1, '-');
        this.initDatePicker('input.dates-field', 2, '+');
    },

    initDatePicker: function(element, plus_days, symbol, callback) {
        $(element).datepicker({
            dateFormat: "dd.mm.yy",
            beforeShowDay: function (date) {
                var allow_date = true, check_date = '';

                getCalendarDates();

                check_date = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
                window.dates_in_calendar.forEach(function(el) {
                    if (el == check_date) {
                        allow_date = false;
                    }
                });

                //Если дата попадает в кадендарный день (праздничный, запрещаем выбор даты)
                if (!allow_date) {
                    return [false];
                }

                //Получаем текущую дату
                var today_date_plus = symbol == '-' ? new Date().getTime() - (plus_days * 86400000) : new Date().getTime() + (plus_days * 86400000),
                    today_date = new Date(today_date_plus);

                if (date.getTime() > today_date.getTime()) {
                    return [true];
                }

                if (parseInt(date.getMonth()) == parseInt(today_date.getMonth())) {
                    if (parseInt(date.getDate()) > parseInt(today_date.getDate()))
                        return [true];
                    else
                        return [false];
                }

                return [false];
            }
        }).on("change", function() {
            /*var self = this;

            try {
                $.datepicker.parseDate('dd.mm.yy', this.value);
            } catch (e) {
                self.value = '';
                $(self).popmessage('show', 'error', 'Неправильный формат даты');

                setTimeout(function() {
                    $(self).popmessage('hide');
                }, 3000);
            }

            if (callback != undefined) {
                callback();
            }*/
        });
    }
};
