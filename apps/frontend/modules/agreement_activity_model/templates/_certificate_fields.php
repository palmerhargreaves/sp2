
<tr class="model-dates-field">
    <td class="label">
        Даты проведения мероприятия
    </td>
    <td class="field controls">
        <div class="modal-input-group-wrapper period-concept-group">
            <div class="modal-input-wrapper modal-short-input-wrapper">
                <input type="text" name="dates_of_service_action_start[]" class="dates-field" placeholder="от" data-format-expression="^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$" data-required="1" data-right-format="21.01.2013"/>
                <div class="modal-input-error-icon error-icon"></div>
            </div>
            <div class="modal-input-wrapper modal-short-input-wrapper">
                <input type="text" name="dates_of_service_action_end[]" class="dates-field" placeholder="до" data-format-expression="^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$" data-required="1" data-right-format="21.01.2013"/>
                <div class="modal-input-error-icon error-icon"></div>
                <div class="error message dates-error-message"></div>
            </div>
        </div>
    </td>
</tr>

<tr class="model-certificate-field">
    <td class="label">
        Срок окончания действия сертификата<br/>
        <div class="modal-input-wrapper" style="border: none;">
            <span style='float: right; cursor: pointer; font-weight: normal; font-size: 11px; text-decoration: underline; position: relative; margin-top: 5px;' class='what-info-conception'>Что это?</span>
            <div class="modal-input-error-icon error-icon"></div>
            <div class="error message" style="display: none; z-index: 999;"></div>
        </div>
    </td>
    <td class="field controls">
        <div class="modal-input-wrapper">
            <input type="text" name="date_of_certificate_end" id="" class="dates-field" placeholder="" data-format-expression="^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$" data-required="1" data-right-format="21.01.13"/>
            <div class="modal-input-error-icon error-icon"></div>
            <div class="error message"></div>
        </div>
    </td>
</tr>

<script>
    $(function() {
        $('input.dates-field').datepicker({
            dateFormat: "dd.mm.yy",
            beforeShowDay: function (date) {
                var allow_date = true, check_date = '';

                check_date = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
                window.dates_in_calendar.forEach(function(el) {
                    if (el == check_date) {
                        allow_date = false;
                    }
                });

                if (!allow_date) {
                    return [false];
                }

                var today = new Date().getTime() + (2 * 86400000),
                    tmp = new Date(today);

                //Убираем нерабочие дни
                /*if (date.getDay() == 0 || date.getDay() == 6) {
                 return [false];
                 }*/

                if (date.getTime() > tmp.getTime())
                    return [true];

                if (parseInt(date.getMonth()) == parseInt(tmp.getMonth())) {
                    if (parseInt(date.getDate()) > parseInt(tmp.getDate()))
                        return [true];
                    else
                        return [false];
                }

                return [false];
            }
        });
    });
</script>
