<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    <li class="nav-header">
                        <span>Статистика Service Clinic</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <div id="msg-container" class="alert alert-error" style="display: none;"></div>

                <form action="<?php echo url_for('@activity_service_clinic_export'); ?>">
                    <ul class="nav nav-list">
                        <li class="nav-header">Параметры</li>
                        <li>
                            Активность:<br/>
                            <select name="sb_activity" id="sb_activity">
                                <option value="-1">Выберите активность</option>
                                <?php foreach($activities as $activity): ?>
                                    <option value="<?php echo $activity->getId(); ?>"><?php echo sprintf('%d - %s', $activity->getId(), $activity->getName()); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </li>
                        <li>
                            Год:<br/>
                            <select name="sb_year" id="sb_year">
                                <option value="-1">Выберите год</option>
                                <?php foreach ($years as $year): ?>
                                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                <?php endforeach ;?>
                            </select>:
                        </li>

                        <li>
                            Квартал:<br/>
                            <select name="sb_quarter" id="sb_quarter">
                                <option value="-1">Выберите квартал</option>
                                <?php for($q = 1; $q <= 4; $q++): ?>
                                    <option value="<?php echo $q; ?>"><?php echo sprintf('Квартал - %s', $q); ?></option>
                                <?php endfor; ?>
                            </select>
                        </li>

                        <li>
                            <input type="submit" id="btDoExportData" class="btn" style="margin-top: 15px;"
                                   value="Выгрузить"/>
                        </li>
                    </ul>
                </form>

                <div id="msg-info-container" class="alert alert-info" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('#btDoExportData').click(function(e) {
            e.preventDefault();

            var activity = $('#sb_activity').val(), year = $('#sb_year').val(), q = $('#sb_quarter').val(), $bt = $(this);
            if (activity == -1) {
                showMsgContainer('Для продолжения выберите активность.');
                return;
            }

            $bt.fadeOut();
            showMsgInfoContainer('Экспорт статистики ...', -1);
            $.post($bt.closest('form').attr('action'),
                {
                    sb_activity: activity,
                    sb_year: year,
                    sb_quarter: q,
                },
            function(result) {
                result = JSON.parse(result);
                if (result.success) {
                    window.location.href = result.url;
                    showMsgInfoContainer('Экспорт завершен', 1000);
                } else {
                    showMsgContainer('Ошибка при загрузке данных.', 1000);
                }
                $bt.fadeIn();
            });
        });
    });

    function showMsgContainer(msg) {
        $('#msg-container').html(msg).fadeIn();

        setTimeout(function () {
            $('#msg-container').fadeOut();
        }, 3000);
    }

    function showMsgInfoContainer(msg, timer) {
        $('#msg-info-container').html(msg).fadeIn();

        if (timer != -1) {
            setTimeout(function () {
                $('#msg-info-container').fadeOut();
            }, timer);
        }
    }

</script>
