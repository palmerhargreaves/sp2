/**
 * Created by kostet on 18.07.2017.
 */

Messages = function(config) {
    $.extend(this, config);

    this.default_show_duration = 300;
    this.default_hide_duration = 300;
    this.default_out_duration = 3000;
}

Messages.prototype = {
    start: function() {
        this.initParams();
        this.initEvents();

        return this;
    },

    initParams: function() {
        toastr.options.hideDuration = 0;
        toastr.clear();

        toastr.options.closeButton = false;
        toastr.options.progressBar = false;
        toastr.options.debug = false;
        toastr.options.positionClass = "toast-top-right";
        toastr.options.showDuration = this.default_show_duration;
        toastr.options.hideDuration = this.default_hide_duration;
        toastr.options.timeOut = this.default_out_duration;
        toastr.options.extendedTimeOut = 500;
        toastr.options.showEasing = "swing";
        toastr.options.hideEasing = "swing";
        toastr.options.showMethod = "slideDown";
        toastr.options.hideMethod = "slideUp";
    },

    initEvents: function() {

    },

    showInfo: function(msg, duration) {
        this.show('info', msg, duration);
    },

    showWarning: function(msg, duration) {
        this.show('warning', msg, duration);
    },

    showError: function(msg, duration) {
        this.show('error', msg, duration);
    },

    showSuccess: function(msg, duration) {
        this.show('success', msg, duration);
    },

    show: function(type, msg, duration) {
        toastr.options.timeOut = duration != undefined ? duration : this.default_out_duration;

        toastr[type](msg);
    },

    setPosition: function(position) {
        toastr.options.positionClass = position;
    }
}
