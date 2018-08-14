<div id="bikes-action-modal" class="intro modal" style="width: 740px; left: 40%;">
    <div class="modal-header">Уважаемый дилер!</div>
    <div class="modal-close"></div>
    <div class="modal-text">
        <p>Просим вас подтвердить участие в акции по продвижению велосипедов в срок до 1 июля 2014 года.</p>
        <p>Рекомендуемые сроки проведения акции: 15 июня – 31 августа 2014 года.</p>
        <p>В случае подтверждения участия, вашему дилерскому предприятию будут высланы креативные материалы для анонса акции и подарки для клиентов, купивших велосипеды.</p>
        <p>Подтверждая участие в данной акции, вы обязуетесь заранее согласовать макеты для анонса акции и затем предоставить фотоотчет по вашим активностям в рамках данной акции.</p>
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

    <div id="containerAcceptBikesAction" style='margin-top: 115px; display: none;'>
        <div class="modal-text">
            <p>Для заказа велосипедов воспользуйтесь формой ниже.</p>
            <p>1. За каждый заказанный велосипед из списка ниже в качестве поддержки дилерское предприятие получает один велосипедный шлем:</p>
        </div>

        <div class='model'>
            <form id='frmForm1'>
                <div style="display: block; width: 95%; margin: auto; margin-top: 10px;">
                    <table style='width: 100%;' class="models">
                        <thead>
                            <tr>
                                <td>Наименование</td>
                                <td>Артикул</td>
                                <td>НЕР, без НДС, руб.</td>
                                <td>РРЦ, с НДС, руб.</td>
                                <td></td>
                            </tr>
                        </thead>

                        <tdoby>
                            <?php
                                $n = 1;
                                foreach($bikesFrm1 as $bike):
                            ?>
                            <tr class="model-mode-field sorted-row model-row<?php if($n++ % 2 == 0) echo ' even' ?>" >
                                <td style="width: 300px;"><?php echo $bike['name']; ?></td>
                                <td style="width: 100px;"><?php echo $bike['article']; ?></td>
                                <td><?php echo $bike['nep']; ?></td>
                                <td><?php echo $bike['rrc']; ?></td>
                                <td class="field controls">
                                    <div class="modal-input-wrapper input" style='width: 75px; margin: 7px;' >
                                        <input type='text' class='with-date count-bikes-frm1'  placeholder='0' data-id='<?php echo $bike['id']; ?>'>
                                        <div class="modal-input-error-icon error-icon"></div>
                                        <div class="error message" style='display: none; z-index: 1;'></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tdoby>
                    </table>
                </div>
            </form>
        </div>

        <div class="modal-text" style="margin-top: 15px;">
            <p>2. За каждый заказанный велосипед из списка ниже, в качестве поддержки дилерское предприятие получает один велосипедный шлем и одно крепление для перевозки велосипеда на крыше автомобиля:</p>
        </div>

         <div class='model'>
            <form id='frmForm2'>
                <div style="display: block; width: 95%; margin: auto; margin-top: 10px;">
                    <table style='width: 100%;' class="models">
                        <thead>
                            <tr>
                                <td>Наименование</td>
                                <td>Артикул</td>
                                <td>НЕР, без НДС, руб.</td>
                                <td>РРЦ, с НДС, руб.</td>
                                <td></td>
                            </tr>
                        </thead>

                        <tdoby>
                            <?php
                                $n = 1;
                                foreach($bikesFrm2 as $bike):
                            ?>
                            <tr class="model-mode-field sorted-row model-row<?php if($n++ % 2 == 0) echo ' even' ?>" >
                                <td style="width: 300px;"><?php echo $bike['name']; ?></td>
                                <td style="width: 100px;"><?php echo $bike['article']; ?></td>
                                <td><?php echo $bike['nep']; ?></td>
                                <td><?php echo $bike['rrc']; ?></td>
                                <td class="field controls">
                                    <div class="modal-input-wrapper input" style='width: 75px; margin: 7px;' >
                                        <input type='text' class='with-date count-bikes-frm2'  placeholder='0' data-id='<?php echo $bike['id']; ?>'>
                                        <div class="modal-input-error-icon error-icon"></div>
                                        <div class="error message" style='display: none; z-index: 1;'></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tdoby>
                    </table>
                </div>
            </form>
        </div>

        <div style="display: none; width: 95%; margin: auto; margin-top: 20px; color: red; font-weight: bold;" class="order-error"> 
            <p>Для продолжения необходимо указать количество велесипедов для заказа.</p>
        </div>

        <div style="display: block; width: 95%; margin: auto; margin-top: 20px;"> 
            <p style="font-size: 12px;">
                <strong>После размещения заказа в системе e-parts дилерскому предприятию в период до 15 июля со стороны Фольксваген Груп Рус будут отправлены соответствующие шлемы/крепления посредством компании DHL.</strong>
            </p>
            <br/>
            <button class="button accept-button2" style="width: 40%; float: left; clear: both;" 
                                            data-id='<?php echo $sf_user->getAuthUser()->getId(); ?>'>Утвердить</button>

            <button class="button gray decline-button2" style="width: 40%; float: right;"
                                            data-id='<?php echo $sf_user->getAuthUser()->getId(); ?>'>Отменить</button>
        </div>
    </div>

</div>

<script>
    $(function() {
        $('#frmDate .with-date').datepicker();

        $('#bikes-action-modal .model .accept-button').live('click', function() {
            $('#containerAcceptBikesAction').slideDown('fast');
        });

        $('#bikes-action-modal .model .decline-button').live('click', function() {
            $("#bikes-action-modal").krikmodal('hide');
        });

        $('#containerAcceptBikesAction input[type=text]').live('input', function(){
            var regEx = new RegExp(/^[0-9.]+$/);

            if(!regEx.test($(this).val())) {
                //$(this).popmessage('show', 'error', 'Только числа');
                $(this).val($(this).val().replace(/[^\d]/, ''));
            }
        });

        $('#containerAcceptBikesAction button.accept-button2').live('click', function(e) {
            e.preventDefault();

            var values = [];
            $.each($('#containerAcceptBikesAction input[type=text]'), function(ind, el) {
                if($(el).val() != '') 
                    values.push( { id : $(el).data('id'), value : $(el).val() } );
            });

            if(values.length == 0) {
                $("div.order-error").fadeIn();
                return false;
            }

            $.post("/home/bikesAdd", 
                    {
                        values : values
                    },
                    function(e) {
                        $("#bikes-action-modal").krikmodal('hide');
                        $("#bikes-action-modal-success").krikmodal('show');

                        setTimeout(function(){
                            location.reload();
                        }, 2500);
                });
        });

        $('#containerAcceptBikesAction button.decline-button2').live('click', function(e) {
            e.preventDefault();

           $("#bikes-action-modal").krikmodal('hide'); 
        });

        
    });

</script>

