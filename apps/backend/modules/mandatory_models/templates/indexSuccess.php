<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    <li class="nav-header">
                        <span>Управление обязательными заявками</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <div class="alert alert-success" style="display: none;">

                </div>

                <form action="">
                    <ul class="nav nav-list">
                        <li class="nav-header">Номер заявки</li>
                        <li>
                            <input type="text" name="txt_model_index" id="txt_model_index" placeholder="Введите номер заявки" />
                        </li>

                        <li>
                            <input type="submit" id="btSearchModel"
                                   data-search-url="<?php echo url_for('@mandatory_model_search'); ?>"
                                   class="btn unload-btn" style="margin-top: 15px;"
                                   value="Поиск"/>
                        </li>
                    </ul>
                </form>
            </div>
        </div>
    </div>

    <div id="container-search-result"></div>

    <div id="container-change-result"></div>

</div>

<script>
    $(function() {
        $(document).on('click', '#btSearchModel', function(event) {
            var element = $(event.target), model_index = $.trim($("#txt_model_index").val());

            event.preventDefault();

            if (model_index.length == 0) {
                alert('Введите номер заявки.');
                return;
            }

            $.post(element.data('search-url'), {
                model_index: model_index
            }, function(result) {
                $("#container-search-result").html(result.content);
            });
        });

        $(document).on('change', '.js-change-model-type', function(event) {
            var element = $(event.target);

            $.post(element.data('url'), {
                id: element.data('id'),
                activity_id: element.data('activity-id'),
                type_index: element.data('type-id'),
                model_index: element.data('model-index')
            }, function(result) {
                $("#container-search-result").html(result.content);
                $('#container-change-result').html(result.result_content);

                offset = $('#container-change-result').offset().top - 10;
                $("body, html").animate({
                        scrollTop: offset + "px"
                    },
                    {duration: 500});

                setTimeout(function() {
                    offset = $('#container-change-result').html('');
                }, 2000);
            });
        });
    });

</script>
