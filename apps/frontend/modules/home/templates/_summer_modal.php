
<div id="summer-action-modal" class="intro modal" style="width: 740px; left: 40%;">
    <div class="modal-header">Уважаемый дилер!</div>
    <div class="modal-close"></div>
    <div class="modal-text">
        <p>Уважаемый дилер, просим вас подтвердить участие в летней сервисной акции в срок до 15 мая 2014 года.</p>
        <p>Рекомендуемые сроки проведения акции: 1 июня – 31 августа 2014 года.</p>
        <p>В случае подтверждения участия, контактная информация по вашему дилерскому центру будет размещена в разделе «Дилеры-участники» импортерского сайта по летней сервисной акции.</p>
    </div>
    

    <div class='model'>
        <div class="summer-action-form-eror-msg message-error" style="color: red; margin: auto; width: 95%; display: none;">
            <p></p>
        </div>

        <div style="display: block; width: 75%; margin: auto; margin-top: 40px;">

            <button class="button accept-button" style="width: 45%; float: left; clear: both;" >Будем участвовать</button>

            <button class="button gray decline-button" style="width: 45%; float: right;">Не будем участвовать</button>
        </div>
    </div>

    <div id="containerAcceptSummerAction" style='margin-top: 115px; display: none;'>
        <div class="modal-text">
            <p>Подтверждая участие в данной акции, вы обязуетесь заранее согласовать макеты для анонса акции и затем предоставить фотоотчет по вашим активностям в рамках данной акции.</p>
            <p>Укажите сроки проведения летней сервисной акции на вашем предприятии.</p>
        </div>

        <div class='model'>
            <form id='frmDate' action='<?php echo url_for(); ?>'>
                <div style="display: block; width: 75%; margin: auto; margin-top: 10px;">
                    <table style='width: 100%;'>
                        <tr class="model-mode-field">
                            <td class="field controls">
                                <div class="modal-input-wrapper input" style='width: 76%; margin-bottom: 7px;' >
                                    <input name='startDate' type='text' class='with-date' placeholder='Дата начала акции'>
                                    <div class="modal-input-error-icon error-icon"></div>
                                    <div class="error message" style='display: none; z-index: 1;'></div>
                                    
                                </div>
                            </td>
                            <td  class="field controls">
                                <div class="modal-input-wrapper input" style='width: 76%; margin-bottom: 7px; float: right;' >
                                    <input name='endDate' type='text' class='with-date'  placeholder='Дата конца акции' >
                                    <div class="modal-input-error-icon error-icon"></div>
                                    <div class="error message" style='display: none; z-index: 1;'></div>
                                    
                                </div>
                            </td>
                        </tr>
                    
                        <tr>
                            <td><button class="button accept-button2" style="width: 85%; float: left; clear: both;" 
                                            data-id='<?php echo $sf_user->getAuthUser()->getId(); ?>'>Подтвердить участие</button></td>

                            <td><button class="button gray decline-button2" style="width: 85%; float: right;"
                                            data-id='<?php echo $sf_user->getAuthUser()->getId(); ?>'>Отменить</button></td>
                        </tr>

                    </table>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    $(function() {
        $('#frmDate .with-date').datepicker();

        $('#summer-action-modal .model .accept-button').live('click', function() {
            $('#containerAcceptSummerAction').slideDown('fast');
        });

        $('#summer-action-modal .model .decline-button').live('click', function() {
            $("#summer-action-modal").krikmodal('hide');
        });

        $('#containerAcceptSummerAction .model button.accept-button2').live('click', function(e) {
            e.preventDefault();

            var $startDate = $('input[name=startDate]'),
                    $endDate = $('input[name=endDate]'),
                    valid = true;

            if($startDate.val().length == 0) {
                $startDate.popmessage('show', 'error', 'Необходимо исправить период размещения');
                valid = false;
            }

            if($endDate.val().length == 0) {
                $endDate.popmessage('show', 'error', 'Необходимо исправить период размещения');
                valid = false;
            }

            if(!valid) 
                return;

            $startDate.popmessage('hide');
            $endDate.popmessage('hide');

            var t1 = parseDate($startDate.val()).getTime(),
                    t2 = parseDate($endDate.val()).getTime();

            if(t1 >= t2) {
                $endDate.popmessage('show', 'error', 'Конечная дата должна быть больше начальной');

                return;
            }            

            $.post("/home/summerSpecial", 
                    {
                        startDate : $startDate.val(),
                        endDate : $endDate.val()
                    },
                    function(e) {
                        $("#summer-action-modal").krikmodal('hide');
                        $("#summer-action-modal-success").krikmodal('show');
                });
        });

        $('#containerAcceptSummerAction .model button.decline-button2').live('click', function(e) {
            e.preventDefault();

           $("#summer-action-modal").krikmodal('hide'); 
        });

        var parseDate = function(date) {
            if(date != undefined) {
                var tmp = date.split('.').reverse();

                return new Date(tmp[0], tmp[1], tmp[2]);
            }

            return null;
        }
    });

</script>

