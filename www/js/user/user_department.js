/**
 * Created by kostet on 15.08.2017.
 */

UserDepartment = function(config) {
    // configurable {
    // }
    this.modal = '';

    this.load_url = '';
    this.accept_url = '';
    this.on_load_child_departments_url = '';

    this.user_id = 0;

    $.extend(this, config);

    this.fieldId = 0;
}

UserDepartment.prototype = {
    start: function() {
        this.initEvents();

        return this;
    },

    initEvents: function() {
        this.getActionConfigInfoParam().click($.proxy(this.config, this));
        this.getPanelSaveFieldBt().click($.proxy(this.onSaveData, this));

        $(document).on('change', '#sb_parent_departments', $.proxy(this.onChangeParentDepartment, this));
    },

    onChangeParentDepartment: function(event) {
        var element = $(event.target);

        $.post(this.on_load_child_departments_url, {
            user_id: this.user_id,
            parent_id: element.val()
        }, $.proxy(this.onLoadChildDepartmentsResult, this));
    },

    onLoadChildDepartmentsResult: function(result) {
        $('#container-for-child-departments').html(result);
    },

    config: function(e) {
        this.resetParams();

        this.user_id = $(e.target).data('id');

        this.getModal().modal('show');
        this.onLoadData();
    },

    resetParams: function() {

    },

    onLoadData: function() {
        $.post(this.load_url, {
                user_id : this.user_id
            }, $.proxy(this.onSuccess, this));
    },

    onSuccess: function(data) {
        this.getPanelContentContainer().html(data);
    },

    onSaveData: function(e) {
        $.post(this.accept_url,
            {
                user_id: this.user_id,
                department_id: this.getChildDepartment().val(),
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
        return $('.action-user-department-save', this.getModal());
    },

    getActionConfigInfoParam: function() {
        return $('.action-config-user-department');
    },

    getFieldsList: function() {
        return $('.fields-list');
    },

    getChildDepartment: function() {
        return $('#sb_child_departments');
    }
}
