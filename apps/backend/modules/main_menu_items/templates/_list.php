<div class="sf_admin_list" style="display: block; width: 100%; float: left;">
    <div class="container-fluid" style="padding: 0px;">
        <div class="row-fluid">
            <div class="span12">
                <div class="well sidebar-nav">

                    <div class="alert alert-info container-success">
                        <ul class="nav nav-list">
                            <li class="nav-header">Правила настройки доступа к элементам меню:</li>
                            <ul>
                                <li>1. Пользователи с правами доступа Отделы имеют наивысший приоритет.</li>
                                <li>2. Пользователи с парвами доступа Основные имеют максимальный доступ, при этом использутся права доступа п.1, если они настроены. </li>
                                <li>3. Права доступа прописанные в [php code] используются по умолчанию (если настроены) и не нарушают правил настроенных в пункте 2.</li>
                            </ul>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!--/row-->
    </div>

    <?php if (!$pager->getNbResults()): ?>
        <p><?php echo __('No result', array(), 'sf_admin') ?></p>
    <?php else: ?>
        <table cellspacing="0" id="menus-list" style="width: 100%;">
            <thead>
            <tr>
                <th id="sf_admin_list_batch_actions"><input id="sf_admin_list_batch_checkbox" type="checkbox"
                                                            onclick="checkAll();"/></th>
                <?php include_partial('main_menu_items/list_th_tabular', array('sort' => $sort)) ?>
                <th id="sf_admin_list_th_actions"><?php echo __('Actions', array(), 'sf_admin') ?></th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <th colspan="4">
                    <?php if ($pager->haveToPaginate()): ?>
                        <?php include_partial('main_menu_items/pagination', array('pager' => $pager)) ?>
                    <?php endif; ?>

                    <?php echo format_number_choice('[0] no result|[1] 1 result|(1,+Inf] %1% results', array('%1%' => $pager->getNbResults()), $pager->getNbResults(), 'sf_admin') ?>
                    <?php if ($pager->haveToPaginate()): ?>
                        <?php echo __('(page %%page%%/%%nb_pages%%)', array('%%page%%' => $pager->getPage(), '%%nb_pages%%' => $pager->getLastPage()), 'sf_admin') ?>
                    <?php endif; ?>
                </th>
            </tr>
            </tfoot>
            <tbody>
            <?php
            foreach ($pager->getResults() as $i => $item): $odd = fmod(++$i, 2) ? 'odd' : 'even' ?>
                <tr id="<?php echo $item->getId(); ?>" class="sf_admin_row <?php echo $odd ?>">
                    <?php include_partial('main_menu_items/list_td_batch_actions', array('main_menu_items' => $item, 'helper' => $helper)) ?>
                    <?php include_partial('main_menu_items/list_td_tabular', array('main_menu_items' => $item)) ?>
                    <?php include_partial('main_menu_items/list_td_actions', array('main_menu_items' => $item, 'helper' => $helper)) ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<script type="text/javascript">
    /* <![CDATA[ */
    function checkAll() {
        var boxes = document.getElementsByTagName('input');
        for (var index = 0; index < boxes.length; index++) {
            box = boxes[index];
            if (box.type == 'checkbox' && box.className == 'sf_admin_batch_checkbox') box.checked = document.getElementById('sf_admin_list_batch_checkbox').checked
        }
        return true;
    }
    /* ]]> */
</script> 
