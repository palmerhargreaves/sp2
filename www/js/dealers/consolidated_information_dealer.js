/**
 * Created by kostet on 10.10.2018.
 */

var DealerConsolidatedInformation = function (config) {

    $.extend(this, config);

    this.is_running = false;
}

DealerConsolidatedInformation.prototype = {
    start: function () {
        this.initEvents();
        this.initElements();

        return this;
    },

    initEvents: function () {
        $(document).on('change', '#sb-consolidated-information-regional-manager', $.proxy(this.onChangeRegionalManager, this));

        $(document).on('click', '.btn-export-consolidated-information-by-dealers', $.proxy(this.onMakeExport, this));

    },

    initElements: function () {
        this.createSelectBox('#sb-consolidated-information-activities', 'Выберите активност(ь, и)', 'Выбранные активности', true);
        this.createSelectBox('#sb-consolidated-information-quarters', 'Выберите квартал(ы) кварталы', 'Выбранные кварталы', true);
        this.createSelectBox('#sb-consolidated-information-dealers', 'Выберите дилера', 'Выбранные дилеры', true);
    },

    onChangeRegionalManager: function (event) {
        var element = $(event.currentTarget);

        $.post(element.data('url'), {
            manager_id: element.val()
        }, $.proxy(this.onChangeManagerResult, this));
    },

    onChangeManagerResult: function (data) {
        this.reinitSelectBox('#sb-consolidated-information-dealers', data.dealers_list);
    },

    reinitSelectBox: function (id, data) {
        $(id).multiselect('loadOptions', data);
    },

    createSelectBox: function (id, placeholder, selectedOptionsLabel, selectAll, showCheckBox) {
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

    onMakeExport: function (event) {
        var element = $(event.currentTarget), self = this;

        event.preventDefault();

        //Получаем список выбранных активностей
        //Без выбора активности не пропускаем дальше
        activities = $('select[name*=sb_consolidated_information_activities]').val();
        if (activities == null) {
            swal({
                title: "Ошибка экспорта",
                text: "Для продолжения выберите активност(ь,и).",
                type: "error",
            });

            return;
        }

        quarters = $('select[name*=sb_consolidated_information_quarters]').val();
        if (quarters == null) {
            swal({
                title: "Ошибка экспорта",
                text: "Для продолжения выберите квартал(ы).",
                type: "error",
            });

            return;
        }

        dealers = $('select[name*=sb_consolidated_information_dealers]').val();
        if (dealers == null) {
            swal({
                title: "Ошибка экспорта",
                text: "Для продолжения выберите дилер(а,ов).",
                type: "error",
            });

            return;
        }

        if (this.is_running) {
            swal({
                title: "Экспорт",
                text: "Дождитесь завершения экспорта!",
                type: "error",
            });

            return;
        }

        this.getLoader().show();
        //this.is_running = true;
        $.post(element.data('url'), {
            activities: activities,
            quarters: quarters,
            dealers: dealers,
            regional_manager: $("select[name=sb_consolidated_information_regional_manager]").val()
        }, function (result) {
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
            self.is_running = false;
        });
    },

    getLoader: function () {
        return $('#export-consolidated-information-progress');
    }
}
