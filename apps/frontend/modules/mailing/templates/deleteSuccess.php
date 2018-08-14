<?php
/**
 * Created by PhpStorm.
 * User: averinbox
 * Date: 12.01.16
 * Time: 11:15
 */
$str_months = array(1 => 'январь', 2 => 'февраль', 3 => 'март', 4 => 'апрель', 5 => 'май', 6 => 'июнь', 7 => 'июль', 8 => 'август', 9 => 'сентябрь', 10 => 'октябрь', 11 => 'ноябрь', 12 => 'декабрь');
$date = new DateTime();
$date->modify('-1 month');
?>

<form action="/mailing_delete" method="post">
    <?php if ($confirm_deleted): ?>
        <h1>Удаление загруженных данных:</h1><br/>
        <?php if ($removal_allowed): ?>
            <p>
                Выберите месяц за который хотите удалить данные.
            </p>
            <p>
                <select name="year">
                    <?php foreach ($years as $year): ?>
                        <option value="<?= $year; ?>" <?= ($current_year == $year ? 'selected' : ''); ?> ><?= $year; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="month">
                    <?php foreach ($months as $month): ?>
                        <option value="<?= $month; ?>" <?= ($current_month == $month ? 'selected' : ''); ?>><?= $str_months[$month]; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
        <?php else: ?>
            <input type="hidden" name="year" value="<?= $d_year; ?>">
            <input type="hidden" name="month" value="<?= $d_month; ?>">
        <?php endif; ?>
        <input type="hidden" name="confirm_deleted" value="<?= $confirm_deleted; ?>"/>
        <input type="submit" value="Удалить" class="button" style="margin: 0px 0 0px 0px;"/>
        <a href="/" class="button" style="display: inline-block; background: #c7c7c7;">Отмена</a>
    <?php else: ?>
        <p>
            Данные за <?= $str_months[$deleted_month]; ?> успешно удалены.
        </p>
        <a href="/" class="button" style="display: inline-block; background: #c7c7c7;">Вернуться на главную страницу</a>
    <?php endif; ?>
</form>
