<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    <li class="nav-header">Выгрузка данных по дилеру</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row-fluid">
        <div class="span12">
            <div class="well sidebar-nav">
                <div class="alert alert-success">
                    Выберите дилера для выгрузки данных по заявкам
                </div>

                <form action="<?php echo url_for('find_model'); ?>">
                    <ul class="nav nav-list">
                        <li>
                            Дилер:
                            <select name="sb_dealer">
                                <?php foreach ($dealers as $dealer): ?>
                                    <option value="<?php echo $dealer->getId(); ?>"><?php echo $dealer->getNameAndNumber(); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </li>
                        <li>
                            Год:
                            <select name="sb_year">
                                <option value="-1">Без года</option>
                                <?php foreach ($years as $year): ?>
                                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </li>
                        <li>
                            <input type="submit" id="btDoExportData" class="btn" style="margin-top: 15px;"
                                   value="Выгрузить"/>
                        </li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).on('click', '#btDoExportData', function () {
        var $bt = $(this), dealer = $('select[name=sb_dealer]').val(), year = $('select[name=sb_year]').val();

        $bt.prop('disabled', true);
        $.post('<?php echo url_for('agreement_models_export_data'); ?>',
            {
                by_dealer: dealer,
                by_year: year
            },
            function (result) {
                $bt.prop('disabled', false);

                window.location.href = 'http://dm.vw-servicepool.ru/uploads/dealer_agreement_models_docs.zip';
            });
    });
</script>