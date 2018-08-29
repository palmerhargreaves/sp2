/**
 * Created by kostet on 14.03.2018.
 */

DealerInformationBlock = function(config) {
    this.on_save_data = '';

    $.extend(this, config);

    this.messages = new Messages().start();
}

DealerInformationBlock.prototype = {
    start: function() {
        this.initEvents();
        this.initEditor();

        return this;
    },

    initEvents: function() {
        $(document).on("click", "#js-save-information-block", $.proxy(this.onSaveInformationBlock, this));
    },

    initEditor: function() {
        tinymce.init(
            {
                selector:'#activity-information-text',
                height: 500,
                theme: 'modern',
                plugins: 'print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools contextmenu colorpicker textpattern help',
                toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat',
                image_advtab: true,
                content_css: [
                    '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
                    '//www.tinymce.com/css/codepen.min.css'
                ],
                language: 'ru'
            }
        );
    },

    onSaveInformationBlock: function(event) {
        var button = $(event.currentTarget),
            htmlText = $.trim($(tinymce.get('activity-information-text').getBody()).html()),
            text = $.trim($(tinymce.get('activity-information-text').getBody()).text());

        if (text.length == 0) {
            alert("Заполните информацию о блоке.");
            return;
        }

        $.post(this.on_save_data, {
            activity_id: button.data('activity-id'),
            dealer_id: button.data('dealer-id'),
            concept_id: button.data('concept-id'),
            text: htmlText
        }, $.proxy(this.onSaveInformationBlockResult, this) );
    },

    onSaveInformationBlockResult: function(result) {
        //window.location.reload();
        this.messages.showSuccess('Данные успешно сохранены.');
        this.getConceptTargetDescription().html(result.text);
    },

    getConceptTargetDescription: function() {
        return $('#concept-target-description');
    }
}
