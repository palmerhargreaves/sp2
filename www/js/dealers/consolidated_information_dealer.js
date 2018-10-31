/**
 * Created by kostet on 10.10.2018.
 */

var DealerConsolidatedInformation = function(config) {

    $.extend(this, config);
}

DealerConsolidatedInformation.prototype = {
    start: function() {
        this.initEvents();
        this.initElements();

        return this;
    },

    initEvents: function() {
        $(document).on('change', '#sb-consolidated-information-regional-manager', $.proxy(this.onChangeRegionalManager, this));

    },

    initElements: function() {
        this.createSelectBox('#sb-consolidated-information-activities', 'Выберите активност(ь, и)', 'Выбранные активности', true);
        this.createSelectBox('#sb-consolidated-information-quarters', 'Выберите квартал(ы) кварталы', 'Выбранные кварталы', true);
        this.createSelectBox('#sb-consolidated-information-dealers', 'Выберите дилера', 'Выбранные дилеры', true);
    },

    onChangeRegionalManager: function(event) {
        var element = $(event.currentTarget);

        $.post(element.data('url'), {
            manager_id: element.val()
        }, $.proxy(this.onChangeManagerResult, this));
    },

    onChangeManagerResult: function(data) {
        this.reinitSelectBox('#sb-consolidated-information-dealers', data.dealers_list);
    },

    reinitSelectBox: function(id, data) {
        $(id).multiselect('loadOptions', data);
    },

    createSelectBox: function(id, placeholder, selectedOptionsLabel, selectAll, showCheckBox) {
        $(id).multiselect({
            search: true,
            columns: 2,
            selectAll: selectAll != undefined ? selectAll : false,
            texts: {
                placeholder: placeholder,
                search: 'Поиск ...',
                selectedOptions: ' ' + (selectedOptionsLabel != undefined ? selectedOptionsLabel : 'Выбранные опции'),
                selectAll: 'Выбрать все',
                unselectAll: 'Снять выделение',
                noneSelected: 'Ничего не выбрано'
            },
            showCheckbox: showCheckBox != undefined ? showCheckBox : true
        });
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
            if (result.success) {
                swal({
                    title: "Экспорт",
                    text: "Экспорт успешно завершен. </br><a href='" + result.url + "' target='_blank'>Скачай меня</a>",
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

    getLoader: function() {
        return $('#loader-spinner');
    }
}
