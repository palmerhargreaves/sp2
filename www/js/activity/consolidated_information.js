/**
 * Created by kostet on 10.10.2018.
 */

var ActivityConsolidatedInformation = function(config) {
    this.on_change_manager_url = '';
    this.dealers_information_container = '';
    this.activity = 0;

    $.extend(this, config);
}

ActivityConsolidatedInformation.prototype = {
    start: function() {
        this.initEvents();

        return this;
    },

    initEvents: function() {
        $(document).on('change', 'input[name=regional_manager_or_dealers]', $.proxy(this.onChangeManager, this));

        $(document).on('click', '#js-export-consolidated-information', $.proxy(this.onMakeExport, this));


    },

    onMakeExport: function(event) {
        var element = $(event.currentTarget), quarters = [], self = this;

        //Получаем список кварталов для экспорта
        $('.sum-quarters').each(function(i, element) {
            if ($(element).is(':checked')) {
                quarters.push($(element).data('quarter'));
            }
        });

        if (quarters.length == 0) {
            swal({
                title: "Ошибка экспорта",
                text: "Для продолжения выберите квартал(ы).",
                type: "error",
            });

            return;
        }

        element.hide();
        this.getLoader().show();
        $.post(element.data('url'), {
            activity: element.data('activity'),
            year: $("input[name=year]").val(),
            quarters: quarters,
            regional_manager: $("input[name=regional_manager_or_dealers]").val()
        }, function(result) {
            console.log(result);
            if (result.success) {
                swal({
                    title: "Экспорт",
                    text: "Экспорт успешно завершен. </br><a href='" + result.url + "' target='_blank'>Скачать файл</a>",
                    type: "success",
                    html: true
                });
            } else {
                swal({
                    title: "Ошибка экспорта",
                    text: "Ошибка генерации отчета.",
                    type: "error",
                });
            }
            element.show();
            self.getLoader().hide();
        });
    },

    onChangeManager: function(event) {
        var element = $(event.currentTarget);

        Pace.start();
        $.post(this.on_change_manager_url, {
            regional_manager: element.val(),
            activity: this.activity
        }, $.proxy(this.changeResult, this));
    },

    changeResult: function(data) {
        this.getDealersInformationContainer().html(data.content);
        this.animate();

        Pace.stop();
    },

    animate: function() {
        $('.activity-summary__stats__item').each(function(ind, el) {
            var item = $(el).find('strong');

            item.animateNumber( { number: item.data('value') } );
        });
    },

    getDealersInformationContainer: function() {
        return $(this.dealers_information_container);
    },

    getLoader: function() {
        return $('#loader-spinner');
    }
}
