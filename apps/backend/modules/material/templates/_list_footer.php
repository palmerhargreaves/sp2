<script>
    $(function() {
        $(document).on('change', '.material-new-ci', function(e) {
            var $element = $(e.target);

            $.post($element.data('url'), {
                material_id: $element.data('material-id'),
                material_new_ci_status: $element.is(':checked') ? 1 : 0
            }, function() {

            });
        });

        $(document).on('change', '.material-status', function(e) {
            var $element = $(e.target);

            $.post($element.data('url'), {
                material_id: $element.data('material-id'),
                material_status: $element.is(':checked') ? 1 : 0
            }, function() {

            });
        });
    });
</script>
