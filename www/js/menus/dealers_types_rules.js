/**
 * Created by kostet on 16.08.2017.
 */

DealersTypesRules = function(config) {
    this.save_url = '';

    $.extend(this, config);
}

DealersTypesRules.prototype = {
    start: function() {
        this.initEvents();

        return this;
    },

    initEvents: function() {
        this.getDealerRuleItem().click($.proxy(this.onSaveData, this));
    },

    onSaveData: function(event) {
        var menu_item = $(event.target), menu_item_id = menu_item.data('menu-item-id'), result = [];

        $('.dealers-types-checks-' + menu_item_id).each(function(i, el) {
            result.push( {
                dealer_rule: $(el).data('dealer-type-id'),
                value: $(el).is(':checked') ? 1 : 0
            });
        });

        $.post(this.save_url, {
            menu_item_id: menu_item_id,
            items: result
        }, $.proxy(this.onSaveResult, this));
    },

    getDealerRuleItem: function() {
        return $('.dealers-types-check');
    },

    onSaveResult: function(result) {
        //window.location.reload();
    }
}
