/**
 * Created by kostet on 30.05.2018.
 */

var NoModelChanges = function(config) {
    this.on_model_check_as_viewed = '';

    $.extend(this, config);

    this.messages = new Messages({}).start();
}

NoModelChanges.prototype = {
    start: function() {
        this.initEvents();

        return this;
    },

    initEvents: function() {
        $(document).on('click', '.check-as-viewed', $.proxy(this.onCheckModelAsView, this));


    },

    onCheckModelAsView: function(event) {
        var from = $(event.currentTarget), self = this;

        from.hide();
        $.post(this.on_model_check_as_viewed, {
            model_id: from.data('model-id')
        }, function(result) {
            if (result.success) {
                $model_row = $('.model-row-id-' + from.data('model-id')).clone();

                $('.model-row-id-' + from.data('model-id')).remove();
                $('#models_viewed').prepend($model_row);

                $("#model").krikmodal("hide");
                self.messages.showSuccess("Заявка успешно подтверджена.");
            }
        });
    }
}
