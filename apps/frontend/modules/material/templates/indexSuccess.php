<div class="activity">
    <?php include_partial('activity/activity_head', array('activity' => $activity, 'quartersModels' => $quartersModels, 'current_q' => $current_q)) ?>
    <div class="content-wrapper">
        <?php include_partial('activity/activity_tabs', array('activity' => $activity, 'active' => 'materials')) ?>

        <div class="pane clear">

            <div id="materials" class="active">
                <?php $cat_ids = array_keys($activities->getRawValue()->getMaterials()); ?>

                <div class="nav-materials js-nav-materials"><ul>
                        <?php foreach ($activities->getMaterials() as $id => $category): ?>
                            <li<?php if ($id == $cat_ids[0]) echo ' class="current"' ?> data-href="#material-group-<?php echo $id ?>"><?php echo $category['category'] ?></li>
                        <?php endforeach; ?>
                    </ul></div>

                <div class="tabs-materials">
                    <?php foreach ($activities->getMaterials() as $id => $category): ?>
                        <div class="material-group js-material-group<?php if ($id == $cat_ids[0]) echo ' current' ?>" id="material-group-<?php echo $id ?>">
                            <h2><?php echo $category['category'] ?></h2>
                            <div class="material-group-i">
                                <?php foreach ($category['materials'] as $n => $m): ?>
                                    <div class="material-cell banner<?php if ($activities->isViewed($m->getId())) echo ' closed' ?> banner-<?php echo $m->getId() ?>" data-material="<?php echo $m->getId() ?>">
                                        <?php if ($m->getNewCi()): ?>
                                            <img src="/images/new_ci_blue.png" class="new_ci" />
                                        <?php endif; ?>

                                        <?php if ($m->getFirstPreview()): ?>
                                            <i><b><img src="/uploads/materials/web_preview/preview/<?php echo $m->getFirstPreview()->getFile() ?>"<?php if ($m->getFirstPreview()->isLandscape()) echo ' class="landscape"' ?> alt="<?php echo $m->getName() ?>"></b></i>
                                        <?php endif; ?>
                                        <?php echo $m->getName() ?>


                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="materials-form-wrap">
                        <span class="btn js-materials-form-toggle">Не нашли нужный материал?</span>
                        <form id="frm_send_request_to_new_material" action="" method="post">
                            <h3>Запрос на разработку материала</h3>
                            <div class="d-popup-cols">
                                <div class="d-popup-col">
                                    <table class="model-form d-popup-tbl-params"><tbody>
                                        <tr>
                                            <td class="label">Тип</td>
                                            <td class="field">
                                                <div class="modal-select-wrapper select input krik-select">
                                                    <span class="select-value select-value-model-type"></span>
                                                    <div class="ico"></div>
                                                    <input type="hidden" name="model_type_id" value="" data-is-sys-admin="1">
                                                    <div class="modal-select-dropdown">
                                                        <?php foreach (MaterialCategoryTable::getInstance()->createQuery()->where('show_in_new_material_request = ?', true)->execute() as $category): ?>
                                                            <div class="modal-select-dropdown-item select-item" data-value="<?php echo $category->getId(); ?>"><?php echo $category->getName(); ?></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <div class="error-text" style="display: none;"></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label">Название</td>
                                            <td class="field controls">
                                                <div class="modal-input-wrapper input">
                                                    <input type="text" value="" name="material_name" placeholder="Листовка" required />
                                                </div>
                                                <div class="error-text" style="float: left;"></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label">Размер, мм</td>
                                            <td class="field">
                                                <div class="input">
                                                    <div class="modal-input-wrapper modal-short-input-wrapper float-left">
                                                        <input type="text" name="material_width" class="size-field" value="" placeholder="Ширина" required/>
                                                    </div>
                                                    <div class="modal-input-wrapper modal-short-input-wrapper float-right">
                                                        <input type="text" name="material_height" class="size-field" value="" placeholder="Высота" required />
                                                    </div>
                                                    <div class="error-text" style="float: left;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label">Формат</td>
                                            <td class="field">
                                                <div class="modal-input-wrapper input">
                                                    <input type="text" value="" name="material_format" placeholder="" required />
                                                </div>
                                                <div class="error-text"></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label">Объем</td>
                                            <td class="field">
                                                <div class="modal-input-wrapper input">
                                                    <input type="text" value="" name="material_volume" placeholder="Кол-во знаков текста без пробелов" required />
                                                </div>
                                                <div class="error-text"></div>
                                            </td>
                                        </tr>
                                        </tbody></table>
                                </div>
                                <div class="d-popup-col">
                                    <div class="modal-input-wrapper materials-form-text">
                                        <div class="label">Обязательный текст</div>
                                        <textarea name="material_required_info" placeholder="Какую информацию необходимо использовать" required></textarea>
                                    </div>
                                    <div class="error-text"></div>
                                    <div class="modal-input-wrapper materials-form-text">
                                        <div class="label">Ваши пожелания</div>
                                        <textarea name="material_suggestions" placeholder="Укажите, что необходимо учесть при разработке"></textarea>
                                    </div>
                                    <div class="error-text"></div>

                                    <input type="submit" name="btn-send-request-new-material" value="Отправить" class="btn" />
                                    <input type="reset" name="" value="Отменить" class="btn" />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div style="clear:both"></div></div>
        </div>
    </div>
</div>

<div id="success-send-request-to-new-material-dialog" class="modal" style="width:350px;">
    <div class="white modal-header">Отправка запроса</div>
    <div class="modal-close"></div>
    Спасибо за Вас запрос, он будет отправлен специалистам.
</div>

<?php include_partial('window') ?>

<script type="text/javascript">
    $(function(){

        $('.js-nav-materials li').on('click',function(){
            var href = $(this).data('href');
            $('.js-nav-materials li').removeClass('current');
            $(this).addClass('current');
            $('.js-material-group').removeClass('current');
            $(href).addClass('current');
        });

        $('.js-materials-form-toggle').on('click',function(){
            $('.materials-form-wrap form').slideToggle(500);
        });

        new MaterialsListController({
            list_selector: '#materials',
            win: new MaterialWindow({
                selector: '#zoom',
                url: '<?php echo url_for("@activity_materials_item?activity=" . $activity->getId())?>'
            }).start()
        }).start();


        new ActivityMaterialRequestNew({
            on_send_request: '<?php echo url_for('@activity_material_send_to_request_new_material'); ?>',
            btn_send_request: 'input[name=btn-send-request-new-material]',
            form: '#frm_send_request_to_new_material'
        }).start();
    });
</script>