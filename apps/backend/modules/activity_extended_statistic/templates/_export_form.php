<ul class="nav nav-list">
    <li class="nav-header">Экспорт данных</li>
</ul>

<form class="form-horizontal" id='frmExportForm'>
    <div class="control-group">
        <label class="control-label" for="sbYear" style="width: inherit;">Год</label>

        <div class="controls" style="margin-left: 0px;">
            <?php
            //$quarters = ActivityQuartersTable::getInstance()->createQuery()->orderBy('id ASC')->where('activity_id = ?', $activity)->execute();
            $years = array();
            $years_list = AgreementModelTable::getInstance()
                ->createQuery()
                ->select('year(created_at) year_created')
                ->where('activity_id = ?', $activity)
                ->andWhere('model_type_id = ?', AgreementModel::CONCEPT_TYPE_ID)
                ->orderBy('year_created ASC')
                ->groupBy('year_created')
                ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            ?>
            <select id='sbYear' name='sbYear'>
                <option value='-1'>Выберите год</option>
                <?php foreach ($years_list as $year_item): ?>
                    <option value='<?php echo $year_item['year_created']; ?>'><?php echo sprintf('Год - %s', $year_item['year_created']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="sbQuarter" style="width: inherit;">Квартал</label>

        <div class="controls" style="margin-left: 0px;">
            <?php
            //$quarters = ActivityQuartersTable::getInstance()->createQuery()->orderBy('id ASC')->where('activity_id = ?', $activity)->execute();
                $quartersResult = ActivityStatisticPeriodsTable::getInstance()
                                    ->createQuery()
                                    ->where('activity_id = ?', $activity)
                                    ->orderBy('year ASC')
                                        ->fetchOne();
                $quarters = explode(":", $quartersResult->getQuarters());
            ?>
            <select id='sbQuarter' name='sbQuarter'>
                <option value='-1'>Выберите квартал</option>
                <?php foreach ($quarters as $q): ?>
                    <option
                        value='<?php echo $q; ?>'><?php echo sprintf('Квартал - [%s]', $q); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="control-group">
        <button id="btExportStatisticData" type="submit" class="btn">Экспортировать</button>
        <img id="img-loader" src="/images/loader.gif" style="display: none;"/>
    </div>
</form>

<script>
    $(function () {
        $(document).on('click', '#btExportStatisticData', function (e) {
            var $bt = $(this), quarter = $('#sbQuarter').val(), year = $('#sbYear').val();

            e.preventDefault();

            $bt.prop('disabled', true);
            $('#img-loader').show();
            $.post('<?php echo url_for("@activity_extended_statistic_export_to_excel"); ?>',
                {
                    quarter: quarter,
                    year: year,
                    activity: '<?php echo $activity; ?>'
                },
                function (result) {
                    window.location.href = result;

                    $('#img-loader').hide();
                    $bt.prop('disabled', false);
                }
            )
        });
    });
</script>
