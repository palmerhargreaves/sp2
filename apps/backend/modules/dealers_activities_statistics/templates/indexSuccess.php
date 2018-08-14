<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    <li class="nav-header">
                        <span>Полная выгрузка</span>
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
                            <select id="sb_year">
                                <?php foreach(range(sfConfig::get('app_min_year'), date('Y')) as $year_item): ?>
                                    <option value="<?php echo $year_item; ?>" <?php echo $year_item == date('Y') ? "selected" : ""; ?>><?php echo $year_item; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </li>

                        <li class="nav-header">Выберите квартал</li>
                        <li>
                            <select id="sb_quarter">
                                <?php for($q = 1; $q <= 4; $q++): ?>
                                    <option><?php echo $q; ?></option>
                                <?php endfor; ?>
                            </select>
                        </li>
                        <li>
                            <label for="consider_next_quarter">Учитывать переход кварталов</label>
                            <input type="checkbox" id="consider_next_quarter" name="consider_next_quarter" value="1" />
                        </li>

                        <li>
                            <label for="only_mandatory_activity">Только обязательные активности</label>
                            <input type="checkbox" id="ch_only_mandatory_activity" name="ch_only_mandatory_activity" value="1" />
                        </li>
                        <li>
                            <input type="submit" id="btDoUnloadingData" class="btn unload-btn" style="margin-top: 15px;"
                                   data-type="by_quarters"
                                   data-file-name="statistic."
                                   value="Квартальная выгрузка"/>
                            <input type="submit" id="btDoUnloadingData2" class="btn unload-btn" style="margin-top: 15px;" data-type="by_year" value="Годовая"/>
                        </li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('#btDoUnloadingData, #btDoUnloadingData2').click(function() {
            var self = $(this);

            $('.unload-btn').attr('disabled', 'disabled');
            $.post('<?php echo url_for('@dealers_activities_statistics_unloading_data'); ?>',
                {
                    quarter: $('#sb_quarter').val(),
                    year: $('#sb_year').val(),
                    mandatory_activity: $('#ch_only_mandatory_activity').is(':checked') ? 1 : 0,
                    consider_next_quarter: $('#consider_next_quarter').is(':checked') ? 1 : 0,
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
