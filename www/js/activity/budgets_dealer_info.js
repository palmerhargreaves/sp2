/**
 * Created by kostet on 19.06.2016.
 */

BudgetsDealerInfo = function(config) {
    this.on_show_item_info = '';
    this.on_compare_items = '';
    this.on_show_budget_item_data = '';

    $.extend(this, config);

    this.selected_item = null;
}

BudgetsDealerInfo.prototype = {
    start: function() {
        this.initEvents();

        return this;
    },

    initEvents: function() {
        this.getContainer().on('click', 'img.show-comparison-item-data', $.proxy(this.onShowItemInfo, this));
        //this.getContainer().on('click', '.show-dealer-budget-data', $.proxy(this.onShowBudgetItemData, this));
        this.getContainer().on('change', '.make-comparison', $.proxy(this.onCompareBudgetItems, this));
        this.getModalBox().on('click', '.show-extended-info', $.proxy(this.onShowComparedInfo, this));
    },

    onShowComparedInfo: function(e) {
        var $bt = $(e.target);

        $('.' + $bt.data('cls')).slideDown();
        $bt.fadeOut();
    },

    onCompareBudgetItems: function(e) {
        var checked_items = this.getCheckedItems();

        if (checked_items.length > 1) {
            $.post(this.on_compare_items, { items: checked_items.join(',')}, $.proxy(this.onCheckedItemsCompareResult, this));
        }
    },

    onCheckedItemsCompareResult: function(data) {
        this.getModalBoxContentContainer().html(data);
        this.getModalBox().modal('show');
    },

/*    onShowBudgetItemData: function(e) {
        var $el = $(e.target);

        $.post(this.on_show_budget_item_data, { item_id: $el.data('item-id'), 'act' : $el.data('act')}, $.proxy(this.onShowBudgetItemDataSuccess, this));
    },

    onShowBudgetItemDataSuccess: function(data) {

    },*/

    onShowItemInfo: function(e) {
        this.selected_item = $(e.target);

        this.selected_item.fadeOut();
        $.post(this.on_show_item_info, { item_id : this.selected_item.data('item-id')}, $.proxy(this.onShowItemInfoResult, this));
    },

    onShowItemInfoResult: function(data) {
        this.selected_item.closest('tr').after(data);

        this.getNewAddInfoItem().slideDown('slow');
    },

    getContainer: function() {
        return $('table#tbl-budgets-dealer-list');
    },

    getNewAddInfoItem: function() {
        return $('.budget-dealer-item-info-' + this.selected_item.data('item-id'), this.getContainer());
    },

    getModalBoxContentContainer: function() {
        return $('.modal-content-container', this.getModalBox());
    },

    getModalBox: function() {
        return $('#budget-items-compare-modal');
    },

    getCheckedItems: function() {
        var items = [];

        $('.make-comparison').each(function(ind, el) {
            if ($(el).is(':checked')) {
                items.push($(el).data('item-id'));
            }
        });

        return items;
    },
}
