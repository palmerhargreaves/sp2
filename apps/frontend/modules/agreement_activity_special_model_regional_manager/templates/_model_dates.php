<tr>
    <td class="label">
        Даты проведения мероприятия
    </td>

    <td class="field controls">
        <div class="value">
            <?php
            $dates_paths = array();
            foreach ($concept->getAgreementModelDates() as $dates):
                $datesCount = explode("/", $dates->getDateOf());
                if (count($datesCount) > 1) {
                    list($start, $end) = explode("/", $dates->getDateOf());
                    echo sprintf("с %s до %s", $start, $end) . "<br/>";
                } else {
                    $dates_paths[] = $dates->getDateOf();
                }
            endforeach;

            if (!empty($dates_paths))
                echo sprintf("с %s", implode(" до ", $dates_paths));
            ?>
        </div>
    </td>
</tr>

<tr>
    <td class="label">
        Срок действия сертификата клиента
    </td>

    <td class="field controls">
        <div class="value"><?php echo $concept->getAgreementModelSettings()->getCertificateDateTo(); ?></div>
    </td>
</tr>
