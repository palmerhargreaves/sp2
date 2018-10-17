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
        var element = $(event.currentTarget), quarters = [];

        $.post(element.data('url'), {
            activity: element.data('activity'),
            year: $("input[name=year]").val(),
            quarters: quarters,
        }, function(result) {
            if (result.success) {
                //window.location.href = result.url;
            }
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
}
