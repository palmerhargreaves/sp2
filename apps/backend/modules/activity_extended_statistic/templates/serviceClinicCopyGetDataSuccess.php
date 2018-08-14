<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 11.01.2017
 * Time: 15:40
 */
?>

<ul class="nav nav-list">
    <li>
        В активность:<br/>
        <select id='sbToActivity' name='sbToActivity'>
            <option value='-1'>Выберите активность ...</option>
            <?php foreach ($activities as $act): ?>
                <option
                    value='<?php echo $act->getId(); ?>'><?php echo sprintf('[%s] - %s', $act->getId(), $act->getName()); ?></option>
            <?php endforeach; ?>
        </select>
    </li>

    <li>
        Разделы
        <table class="table table-hover table-condensed table-bordered table-striped">
            <thead>
            <tr>
                <th style='width: 1%;'>#</th>
                <th>Название</th>
            </tr>
            </thead>

            <tbody>
            <?php
            $ind = 1;
            foreach ($service_clinic_headers as $item):
                ?>
                <tr>
                    <td><input type="checkbox" class="ch-statistic-header" data-id="<?php echo $item->getId(); ?>" checked /></td>
                    <td><?php echo $item->getHeader(); ?></td>
                </tr>
                <?php $ind++; endforeach; ?>
            </tbody>
        </table>
    </li>

    <li>
        Поля
        <table class="table table-hover table-condensed table-bordered table-striped">
            <thead>
            <tr>
                <th style='width: 1%;'>#</th>
                <th>Название</th>
                <th>Тип поля</th>
            </tr>
            </thead>

            <tbody>
            <?php
            $ind = 1;
            foreach ($service_clinic_fields as $item):
                ?>
                <tr>
                    <td><input type="checkbox" checked class="ch-statistic-field" data-id="<?php echo $item->getId(); ?>" /></td>
                    <td><?php echo $item->getHeader(); ?></td>
                    <td><?php echo $item->getFieldType(); ?></td>
                </tr>
                <?php $ind++; endforeach; ?>
            </tbody>
        </table>
    </li>
</ul>

<ul class="nav nav-list">
    <li>
        <input type="submit" id="btMakeCopy" class="btn" style="margin-top: 15px;" value="Копировать"/>
    </li>
</ul>
