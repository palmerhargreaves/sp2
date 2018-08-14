<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    <li class="nav-header">
                        <span>Выгрузка по каналам</span>
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
                        <li class="nav-header">Выберите год</li>
                        <li>
                            <div class="alert alert-info" >По умолчанию используется текущий год</div>
                            <?php $current_year = date('Y'); ?>
                            <select id="sb_year">
                                <option value="">Год ...</option>
                                <?php foreach($years_list as $ind => $year_data): ?>
                                    <option value="<?php echo $year_data['year_created']; ?>" <?php echo $year_data['year_created'] == $current_year ? "selected" : ""; ?>><?php echo $year_data['year_created']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </li>

                        <li class="nav-header">Выберите квартал</li>
                        <li>
                            <select id="sb_quarter">
                                <option value="">Квартал ...</option>
                                <?php for($q = 1; $q <= 4; $q++): ?>
                                    <option><?php echo $q; ?></option>
                                <?php endfor; ?>
                            </select>
                        </li>

                        <li class="nav-header">Привязка:</li>
                        <li>
                            <select id="sb_category_or_type">
                                <option value="categories" selected>Категория</option>
                                <option value="types">Тип</option>
                                <option value="dealers_with_types">Дилеры + Тип</option>
                                <!--<option value="">Категория + Тип</option>-->
                            </select>
                        </li>

                        <li class="nav-header">Раширенная информация (Для Категории):</li>
                        <li>
                            <input type="checkbox" id="ch_extended_info_for_category" name="ch_extended_info_for_category" value="1" />
                        </li>

                        <li class="nav-header">Получаемые данные:</li>
                        <li>
                            <select id="sb_data_type">
                                <option value="amounts" selected>Общая сумма</option>
                                <option value="counts">Количество</option>
                                <!--<option value="">Категория + Тип</option>-->
                            </select>
                        </li>

                        <li>
                            <label for="only_mandatory_activity">Только обязательные активности</label>
                            <input type="checkbox" id="only_mandatory_activity" name="only_mandatory_activity" value="1" />
                        </li>
                        <li>
                            <input type="submit" id="btDoUnloadingData" class="btn unload-btn btn-categories-types" style="margin-top: 15px;"
                                   data-type="by_general"
                                   value="Общая"/>
                            <input type="submit" id="btDoUnloadingData2" class="btn unload-btn btn-categories-types" style="margin-top: 15px;" data-type="by_dealers" value="По дилерам"/>

                            <input type="submit" id="btDoUnloadingDataByDealersAndTypes" class="btn unload-btn btn-dealers-types" style="display: none; margin-top: 15px;" data-type="by_dealers_and_types" value="По дилерам + тип" />
                        </li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $(document).on("change", "#sb_category_or_type", function() {
            var select = $(this);

            if (select.val() == 'dealers_with_types') {
                $('.btn-categories-types').hide();
                $('.btn-dealers-types').show();
            } else {
                $('.btn-categories-types').show();
                $('.btn-dealers-types').hide();
            }
        });

        $('#btDoUnloadingData, #btDoUnloadingData2, #btDoUnloadingDataByDealersAndTypes').click(function() {
            var self = $(this);

            $('.unload-btn').attr('disabled', 'disabled');
            $.post('<?php echo url_for('@dealers_channels_statistics_unloading_data'); ?>',
                {
                    year: $('#sb_year').val(),
                    quarter: $('#sb_quarter').val(),
                    mandatory_activity: $('#only_mandatory_activity').is(':checked') ? 1 : 0,
                    category_or_type: $('#sb_category_or_type').val(),
                    extended_category_info: $('#ch_extended_info_for_category').is(':checked') ? 1 : 0,
                    data_type: $('#sb_data_type').val(),
                    type: self.data('type')
                },
                function(result) {
                    $('.unload-btn').removeAttr('disabled');

                    if (result.success) {
                        window.location.href = result.file_name;
                    }
                }
            );
        });
    });

</script>
