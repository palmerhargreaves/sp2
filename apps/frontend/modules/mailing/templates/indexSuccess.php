<?php
/**
 * Created by PhpStorm.
 * User: Andrey
 * Date: 16.11.2015
 * Time: 12:56
 */
use_stylesheet('mailing.css');
?>
<script type="text/javascript">
    $(document).ready(function(){
        $('#upload-mailing-file').on('change', function(event, files, label){
            var file_name = this.value.replace(/\\/g, '/').replace(/.*\//, '')
            $('.file-name').text(file_name);
        });
    });
</script>
<style type="text/css">
    .first_td, .second_td {height: 40px;}
    .first_td {
        text-align: center;
        font-size: 16px;
        width: 40px;
    }
    .second_td {
        padding: 20px;
    }
</style>
<form action="/mailing_dealer" method="post" enctype="multipart/form-data" style="width:616px; margin:0px auto;">
	<div class="mailing_load">
		<h1>Убедитесь в корректности загружаемого файла:</h1>
        <table>
            <tbody border="0" class="desc_table">
                <tr>
                    <td class="first_td" style="background: url('../images/mailing_list.png') no-repeat center;">1</td>
                    <td class="second_td" ><strong>Откройте файл с адресами клиентов.</strong></td>
                </tr>
                <tr>
                    <td class="first_td" style="background: url('../images/mailing_list.png') no-repeat center;">2</td>
                    <td class="second_td" ><strong>Проверьте, что в файле находятся только e-mail адреса клиентов, собранные в текущем месяце.</strong></td>
                </tr>
                <tr>
                    <td class="first_td" style="background: url('../images/mailing_list.png') no-repeat center;">3</td>
                    <td class="second_td" ><strong>Удостоверьтесь, что файл содержит следующие столбцы:<span style="font-weight: normal;"> Номер дилера, Фамилия, Имя, Отчество, Пол, Тел. мобильный, Адрес эл. почты, Vin-номер автомобиля, Последняя дата посещения сервиса, Дата выгрузки данных.</span></strong></td>
                </tr>
                <tr>
                    <td class="first_td" style="background: url('../images/mailing_list.png') no-repeat center;">4</td>
                    <td class="second_td" ><strong>Убедитесь, что все данные в файле в текстовом формате, файл не содержит ссылок и даты имеют один и тот же формат – <span style="font-weight: normal;">дд.мм.гг</span></strong></td>
                </tr>
                <tr>
                    <td class="first_td" style="background: url('../images/mailing_list.png') no-repeat center;">5</td>
                    <td class="second_td" ><strong>Сохраните файл в формате <span style="font-weight: normal;"> .csv (разделители - запятые)</span><br><a class="button" style="padding: 5px;" href="/dealer_file.csv">Скачать пример файла</a></strong></td>
                </tr>
            </tbody>
        </table>
		<hr>
        <h1>Загрузить файл</h1>
			<div class="file concept-file" style="margin: 5px 5px 0px 5px;">
                <div class="modal-file-wrapper">
                    <div class="control" style="border: 2px dashed rgb(223, 220, 220); padding-top: 20px; border-radius: 3px; width: 100%; min-height: 25px; !important; height: 25px !important;">
                        <div style="font-size: 11px; text-align: center;">Перетащите сюда файлы или нажмите на кнопку для загрузки</div>
                        <div class="green button modal-zoom-button modal-form-button" style="float: right; margin-right: 20px; margin-top: -12px;"></div>
                        <input type="file" name="data_file" size="1" id="upload-mailing-file">
                    </div>
                    <div class="modal-input-error-icon error-icon"></div>
                    <div class="error message"></div>
                </div>
                <div class="value file-name"></div>
                <div class="clear"></div>
                <div class="modal-form-uploaded-file"></div>
            </div>
		    <input type="submit" value="Отправить" class="button button" style="margin: 35px 0 43px 0px;"/><br>
            <h1><?= $error['title']; ?></h1>
            <strong> <?= htmlspecialchars_decode($error['message']); ?></strong>
        <p><?= htmlspecialchars_decode($error['next_step']); ?></p>
        <?php if(($total_result['total_unique'] + $total_result['total_incorrect']) > 0 && !$total_result['date_error']): ?>
            <?php if(MailingList::checkDuplicatePerecnt($dealer->getNumber(), $total_result) < 30): ?>
		    <hr>
                <?php if($display_stat): ?>
                    <div class="result">
                        <p><strong>Информация о файле:</strong></p>
                        <span style="color: #1a3d97;">Всего адресов: <span style="font-size: 16px; font-weight: bold;"><?= $total_result['total_on_file']; ?></span></span></br>
                        <span style="color: #1a3d97;">Количество уникальных адресов: <span style="font-size: 16px; font-weight: bold;"><?= $total_result['total_on_file'] - $total_result['total_duplicate_on_file']; ?></span></span></br>
                        <span style="color: #d90327;">Количество дублирующихся адресов: <span style="font-size: 16px;font-weight: bold;"><?= $total_result['total_duplicate_on_file']; ?></span></span></br>
                        <span style="color: #d90327;">Количество некорректных адресов: <span style="font-size: 16px;font-weight: bold;"><?= $total_result['total_incorrect']; ?></span></span></br>
                        <span style="color: #118e40;">Количество принятых адресов: <span style="font-size: 16px; font-weight: bold;"><?= $total_result['total_added']; ?></span></span></br>
                        <span style="color: #d90327;">Количество удаленных адресов: <span style="font-size: 16px;font-weight: bold;"><?= $total_result['total_incorrect']; ?></span></span></br>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
	</div>
</form>