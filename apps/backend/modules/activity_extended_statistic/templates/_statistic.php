<table class="table table-hover table-condensed table-bordered table-striped table-export-dealers-stats-data">
    <thead>
    <tr>
        <th style="width: 30%;">Дилеры</th>
        <!--<th>Процент выполнения</th>-->
        <th>Сроки выполнения</th>
    </tr>
    </thead>

    <?php
    $statistic->buildDealerStats();
    $stats = $statistic->getDealerStats();
    //$fields = ActivityExtendedStatisticFieldsTable::getInstance()->createQuery()->where('activity_id = ?', array($activity))->orderBy('order ASC')->execute();
    ?>
    <tbody>
    <?php foreach ($stats as $dealerId => $item):
        $concepts = AgreementModelTable::getInstance()
            ->createQuery('am')
            ->innerJoin('am.AgreementModelSettings ams')
            ->where('activity_id = ? and model_type_id = ?', array($activity, 10))
            //->andWhere('ams.certificate_date_to >= ?', date('Y-m-d'))
            ->andWhere('am.dealer_id = ?', $dealerId)
            ->orderBy('ams.id ASC')
            ->execute();

        if ($concepts && $concepts->count() > 0):
            ?>
            <tr id="dealer-concept-cetrificate-<?php echo $dealerId; ?>">
                <td><?php echo sprintf('[%s] %s', $item['dealerNumber'], $item['dealerName']); ?></td>
                <!--<td><?php echo $item['percentOfComplete'] . "%"; ?></td>-->
                <td>
                    <ul style="float: left;">
                        <?php
                        foreach ($concepts as $concept):
                            $isBinded = ActivityExtendedStatisticFieldsTable::checkUserConcept($dealerId, $concept);
                            ?>
                            <li style="list-style-type: none;">
                                <?php if ($isBinded):
                                    $query = ActivityExtendedStatisticFieldsDataTable::getInstance()
                                        ->createQuery('f')
                                        ->leftJoin('f.Field pf')
                                        //->where('f.value != ?', array(''))
                                        ->where('pf.value_type != ?', 'date')
                                        ->andWhere('f.value != ?', '')
                                        ->andWhere('f.dealer_id = ? and f.concept_id = ?', array($dealerId, $concept->getId()))
                                        ->andWhere('pf.activity_id = ?', $activity)
                                        ->orderBy('pf.position ASC');

                                    $calcDate = $concept->getModelQuarterDate();
                                    $quarter = D::getQuarter($calcDate);
                                    $calcYear = D::getYear($calcDate);

                                    $filledFieldsCount = $query->count();
                                    ?>

                                    <?php if ($filledFieldsCount == 0): ?>
                                        <span class="badge badge-warning" title="Данные не внесены">-</span>
                                    <?php else: ?>
                                        <span class="badge badge-info" title="Данные введены">+</span>
                                    <?php endif; ?>

                                    <span class="badge badge-success">Привязка к: <?php echo $quarter; ?>
                                        кварталу (<?php echo $calcYear; ?>г.)</span>
                                <?php endif; ?>
                                <span
                                    class="dealer-certificate-item-<?php echo $concept->getId(); ?>"
                                    style=""><?php echo sprintf("Концепция [%s]: %s", $concept->getId(), date('d-m-Y', strtotime($concept->getAgreementModelSettings()->getCertificateDateTo()))); ?></span>
                            <span
                                class="dealer-certificate-item-<?php echo $concept->getId(); ?>"
                                style=""> ( <img style="cursor: pointer;"
                                                 class="on-delete-dealer-concept-certificate"
                                                 data-id="<?php echo $concept->getId(); ?>"
                                                 src="/images/delete-icon.png" title="Удалить"/> ) </span>
                            </li>
                            <?php
                        endforeach;
                        ?>
                    </ul>

                    <img src="/images/plus-icon.png" style="cursor: pointer;" class="on-add-new-concept pull-right tip"
                         title="Добавить новый срок выполнения"
                         data-dealer-id="<?php echo $dealerId; ?>"
                         data-activity-id="<?php echo $activity; ?>"/>
                </td>
            </tr>
            <?php
        endif;
    endforeach;
    ?>
    </tbody>

</table>

<script>

    $(function () {
        var table = $('.table-export-dealers-stats-data').dataTable({
            "bJQueryUI": false,
            "bAutoWidth": false,
            "bPaginate": true,
            "bLengthChange": false,
            "bInfo": false,
            "bDestroy": true,
            "iDisplayLength": 100,
            "sPaginationType": "full_numbers",
            //"sDom": '<"datatable-header"flp>t<"datatable-footer"ip>',
            "oLanguage": {
                "sSearch": "<span>Фильтр:</span> _INPUT_",
                "sLengthMenu": "<span>Отоброжать по:</span> _MENU_",
                "oPaginate": {"sFirst": "Начало", "sLast": "Посл", "sNext": ">", "sPrevious": "<"}
            },
            "aoColumnDefs": [
                {"bSortable": false, "aTargets": [[1]]}
            ]
        });
    });
</script>
