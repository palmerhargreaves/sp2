<?php
/**
 * Created by PhpStorm.
 * User: Andrey
 * Date: 16.11.2015
 * Time: 12:56
 */
$months = array(1 => 'Янв.', 2 => 'Фев.', 3 => 'Март', 4 => 'Апр.', 5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Авг.', 9 => 'Сен.', 10 => 'Окт.', 11 => 'Ноя.', 12 => 'Дек.');
use_stylesheet('mailing.css');
?>

<form action="/mailing_dealer" method="post" enctype="multipart/form-data" style="width:616px; margin:0px auto;">

    <div class="mailing_load">
        <h1>Убедитесь в корректности загружаемого файла:</h1>
        <div class="mailinglist">
            <div class="pic">
                <div><img src="../images/mailing_list.png" alt=""></div>
                <span>1<span></div>
            <div class="mailinglist_descr">
                <div>Откройте файл с адресами клиентов.</div>
            </div>
            <div class="pic">
                <div><img src="../images/mailing_list.png" alt=""></div>
                <span>2<span></div>
            <div class="mailinglist_descr">
                <div>Проверьте, что в файле находятся только e-mail адреса клиентов, собранные в текущем месяце.</div>
            </div>
            <div class="pic">
                <div><img src="../images/mailing_list.png" alt=""></div>
                <span>3<span></div>
            <div class="mailinglist_descr">
                <div>Удостоверьтесь, что файл содержит следующие столбцы:<span style="font-weight: normal;"> Номер дилера, Фамилия, Имя, Отчество, Тел. мобильный, Адрес эл. почты, Последняя дата посещения сервиса, Дата выгрузки данных.</span>
                </div>
            </div>
            <div class="pic">
                <div><img src="../images/mailing_list.png" alt=""></div>
                <span>4<span></div>
            <div class="mailinglist_descr">
                <div>Убедитесь, что все данные в файле в текстовом формате, файл не содержит ссылок и даты имеют один и
                    тот же формат – <span style="font-weight: normal;">дд.мм.гг</span></div>
            </div>
            <div class="pic">
                <div><img src="../images/mailing_list.png" alt=""></div>
                <span>5<span></div>
            <div class="mailinglist_descr">
                <div>Сохраните файл в формате
                    <span style="font-weight: normal;">.xls (Microsoft Excel 97/2000/XP) или .xlsx.</span>
                    <br><a class="button" style="padding: 5px;" href="/dealer_file.xlsx">Скачать пример файла</a></div>
            </div>
        </div>
        <hr>
        <?php if ($display_load_panel || ($authUserId == 1 || $authUserId == 671)): ?>
            <h1>Загрузить файл</h1>
            <div class="file concept-file" style="margin: 5px 5px 0px 5px;">
                <div class="modal-file-wrapper input">
                    <div class="control"
                         style="border: 2px dashed rgb(223, 220, 220); padding-top: 20px; border-radius: 3px; width: 100%; height: 52px;">
                        <div style="font-size: 11px; text-align: center;">Перетащите сюда файлы или нажмите на кнопку
                            для загрузки
                        </div>
                        <div class="green button modal-zoom-button modal-form-button"
                             style="float: right; margin-right: 20px; margin-top: -12px;"></div>
                        <input type="file" name="data_file" size="1">
                    </div>
                    <div class="modal-input-error-icon error-icon"></div>
                    <div class="error message"></div>
                </div>
                <div class="value file-name"></div>
                <div class="clear"></div>
                <div class="modal-form-uploaded-file"></div>
            </div>

            <input type="submit" value="Отправить" class="button button" style="margin: 35px 0 43px 0px;"/>
            <hr>
            <h2><?= $approve ?></h2>
        <?php endif; ?>

        <?= '<strong>' . htmlspecialchars_decode($error_message) . '</strong>'; ?><br>


        <?php if ($display_stat): ?>
            <div class="result">
                <strong>Информация о файле:</strong><br>
                <span style="color: #1a3d97;">Всего адресов: <span
                        style="font-size: 16px; font-weight: bold;"><?= isset($total_result['total_file_count']) ? $total_result['total_file_count'] : 0; ?></span></span></br>
                <span style="color: #1a3d97;">Количество уникальных адресов: <span
                        style="font-size: 16px; font-weight: bold;"><?= isset($total_result['total_unique']) ? $total_result['total_unique'] : 0; ?></span></span></br>
                <span style="color: #d90327;">Количество дублирующихся адресов: <span
                        style="font-size: 16px;font-weight: bold;"><?= isset($total_result['total_file_duplicate']) ? $total_result['total_file_duplicate'] : 0; ?></span></span></br>
                <span style="color: #d90327;">Количество некорректных адресов: <span
                        style="font-size: 16px;font-weight: bold;"><?= isset($total_result['total_incorrect']) ? $total_result['total_incorrect'] : 0; ?></span></span></br>
                <br>
                <span style="color: #118e40;">Количество принятых адресов: <span
                        style="font-size: 16px; font-weight: bold;"><?= isset($total_result['total_added']) ? $total_result['total_added'] : 0; ?></span></span></br>
                <span style="color: #d90327;">Количество удаленных адресов: <span
                        style="font-size: 16px;font-weight: bold;"><?= isset($total_result['total_incorrect']) ? $total_result['total_incorrect'] : 0; ?></span></span></br>
            </div>
        <?php endif; ?>
        <br>
        <?= htmlspecialchars_decode($next_step); ?>
    </div>


</form>