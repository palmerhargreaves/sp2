/**
 * Created by kostet on 15.08.2017.
 */

Rules = function(config) {
    // configurable {
    // }
    this.modal = '';

    this.load_fields_url = '';
    this.load_field_data = '';

    this.accept_url = '';
    this.delete_url = '';

    this.menu_item_id = 0;

    $.extend(this, config);

    this.fieldId = 0;
}

Rules.prototype = {
    start: function() {
        this.initEvents();

        return this;
    },

    initEvents: function() {
        this.getActionConfigInfoParam().click($.proxy(this.configActivityInfoParams, this));

        this.getPanelSaveFieldBt().click($.proxy(this.onSaveFieldsData, this));
    },

    configActivityInfoParams: function(e) {
        this.resetParams();

        this.menu_item_id = $(e.target).data('id');

        this.getModal().modal('show');
        this.onLoadData();
    },

    resetParams: function() {

    },

    onLoadData: function() {
        $.post(this.load_url,
            {
                menu_item_id : this.menu_item_id
            },
            $.proxy(this.onAddRulesList, this));
    },

    onAddRulesList: function(data) {
        this.getPanelContentContainer().html(data);
    },

    onSaveFieldsData: function(e) {
        var rules_list = $('#sb_rules', this.getModal()).val();

        /*if (rules_list == null) {
            alert('Выберите правила для продолжения.');
            return;
        }*/

        $.post(this.accept_url,{
            menu_item_id: this.menu_item_id,
            rules_list: rules_list,
            task: 'users_rules'
            },
            $.proxy(this.onResult ,this));
    },

    onResult: function(data) {
        window.location.reload();
    },

    getModal: function() {
        return $(this.modal);
    },

    getPanelLeftContainer: function() {
        return $('.panel-info-left', this.getModal());
    },

    getPanelContentContainer: function() {
        return $('.panel-info-content', this.getModal());
    },

    getPanelFieldsContainer: function() {
        return $('.inputs-fields', this.getModal());
    },

    getPanelSaveFieldBt: function() {
        return $('.action-rules-save', this.getModal());
    },

    getActionConfigInfoParam: function() {
        return $('.action-menu-item-rules');
    },

    getFieldsList: function() {
        return $('.fields-list');
    }
}
