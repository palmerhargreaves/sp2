<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 09.01.2017
 * Time: 11:28
 */

?>
<table id="types-list" class="table table-bordered table-stripped types-list">
    <thead>
        <tr>
            <th>#</th>
            <th>Название</th>
            <th>Способ согласования</th>
            <th style="text-align: right;">Действия</th>
        </tr>
    </thead>

    <tbody>
    <?php $ind = 1; ?>
        <?php foreach ($agreement_model_categories->getCategoryTypesListOrderedByPosition() as $item): ?>
        <tr id="<?php echo $item->getId(); ?>" class="category-type-item" data-level="2">
            <td><?php echo $ind++; ?></td>
            <td><a href="<?php echo url_for('agreement_model_categories_types/edit/?id=' . $item->getId()) ?>"><?php echo $item->getName() ?></a></td>
            <td><?php echo $item->getAgreementTypeLabel(); ?></td>
            <td style="text-align: right;"><a href="<?php echo url_for('agreement_model_categories_types/delete/?id=' . $item->getId()) ?>"
                   onclick="if(confirm('Вы уверены?')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'post'; f.action = this.href;var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', 'sf_method'); m.setAttribute('value', 'delete'); f.appendChild(m);f.submit(); }; return false; ">
                   удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<ul class="sf_admin_actions">
    <li class="sf_admin_action_new"><a
        href="<?php echo url_for('agreement_model_categories_types/new?parent_category_id=' . $agreement_model_categories->getId()) ?>">Добавить</a></li>
</ul>
