/**
 * Created by kostet on 28.08.2018.
 */

SpecialAgreementConceptBindTargetAndStatistic = function(config) {
    this.activity_id = 0;
    this.on_change_concept = '';
    this.sb_concepts_element = '';
    this.container_concept_targets = '';
    this.container_concept_statistic = '';

    $.extend(this, config);
}

SpecialAgreementConceptBindTargetAndStatistic.prototype = {
    start: function() {
        this.initEvents();

        return this;
    },

    initEvents: function() {
        $(document).on('change', this.sb_concepts_element, $.proxy(this.onChangeConcept, this));
    },

    /**
     * Обработка смены концепции
     */
    onChangeConcept: function(event) {
        var concept_id = parseInt($(event.currentTarget).val());

        if (concept_id != -1) {
            $.post(this.on_change_concept, {
                activity: this.activity_id,
                concept_id: concept_id
            }, $.proxy(this.onSelectConceptResult, this));
        }
    },

    onSelectConceptResult: function(result) {
        this.getContainerConceptTargets().html(result.concept_target);

        $('.special-agreement-concept-target.group.open .group-content').show();

        $('.special-agreement-concept-target .group-header').click(function () {
            $(this).parents('.group').toggleClass('open');
            $(this).parents('.group').find('.group-content').slideToggle();

            if ($(this).parents('.group').hasClass('open'))
                $('html,body').animate({scrollTop: $(this).offset().top}, 500);
        });

        tinymce.remove('#activity-information-text');
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

    getContainerConceptTargets: function() {
        return $(this.container_concept_targets);
    },

    getContainerConceptStatistic: function() {
        return $(this.container_concept_statistic);
    }
}
