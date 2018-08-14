<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 09.01.2017
 * Time: 11:28
 */

?>
<table id="category-fields-list" class="table table-bordered table-stripped category-fields-list">
    <thead>
    <tr>
        <th>#</th>
        <th>Название</th>
        <th>Тип</th>
        <th>Добавление полей</th>
        <th>Идентификатор</th>
        <th>Формат</th>
        <th style="text-align: right;">Действия</th>
    </tr>
    </thead>

    <tbody>
    <?php $ind = 1; ?>
    <?php foreach ($agreement_model_categories->getCategoryFieldsListOrderedByPosition() as $item): ?>
        <tr id="<?php echo $item->getId(); ?>" class="category-field-item" data-level="1">
            <td><?php echo $ind++; ?></td>
            <td><a href="<?php echo url_for('agreement_model_categories_fields/edit/?id=' . $item->getId()) ?>"><?php echo $item->getName() ?></a></td>
            <td><?php echo $item->getType(); ?></td>
            <td>
                <?php if ($item->getChildField()): ?>
                    <div style="width: 100%; display: inline-block; float: left;">
                        <img alt="Checked" title="Checked" src="/sfDoctrinePlugin/images/tick.png">
                        <input type="number" id="txt_field_category_field_<?php echo $item->getId(); ?>"
                               class="on-save-category-field" data-field-id="<?php echo $item->getId(); ?>"
                               style="width: 50px; float: right; " placeholder="0"
                               value="<?php echo $item->getChildFields()->count(); ?>"
                               data-def-value="<?php echo $item->getChildFields()->count(); ?>"
                               min="0" max="100" />
                    </div>

                    <div style="width: 100%; display: inline-block; float: left;">
                        <button class="button btn-mini btn-category-add-new-field btn-category-field-add-<?php echo $item->getId(); ?>" data-field-id="<?php echo $item->getId(); ?>" style="display: none;">Добавить</button>
                        <button class="button btn-mini btn-category-remove-field btn-category-field-delete-<?php echo $item->getId(); ?>" data-field-id="<?php echo $item->getId(); ?>" style="float: right; display: none;">Удалить</button>
                    </div>
                <?php endif; ?>
            </td>
            <td><?php echo $item->getIdentifier(); ?></td>
            <td><?php echo $item->getFormatExpression(); ?></td>
            <td style="text-align: right;">
                <a href="<?php echo url_for('agreement_model_categories_fields/delete/?id=' . $item->getId()) ?>"
                    onclick="if(confirm('Вы уверены?')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'post'; f.action = this.href;var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', 'sf_method'); m.setAttribute('value', 'delete'); f.appendChild(m);f.submit(); }; return false; ">
                    удалить
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<ul class="sf_admin_actions">
    <li class="sf_admin_action_new"><a
            href="<?php echo url_for('agreement_model_categories_fields/new?parent_category_id=' . $agreement_model_categories->getId()) ?>">Добавить</a></li>
</ul>
