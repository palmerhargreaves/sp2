<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 02.07.2018
 * Time: 15:49
 */
?>

<?php include_partial('modal_model', array('decline_reasons' => $decline_reasons, 'decline_report_reasons' => $decline_report_reasons, 'specialist_groups' => $specialist_groups)) ?>

<div id="agreement-models">
    <div class="stats-summary f-vw d-cb">

        <div class="approvement" style="min-height: 55px;">
            <div class="stats-summary__block" style="width: 100%;">

                <table>
                    <thead>
                    <tr class="ttop">
                        <th colspan="4">Согласованные заявки</th>
                    </tr>

                    <tr class="tmid">
                        <th colspan="4">
                            <?php if (isset($paginatorData)): ?>
                                <table width="100%" style="margin-top: 10px;">
                                    <tr>
                                        <td><?php include_partial('global/paginator', $paginatorData); ?></td>
                                    </tr>
                                </table>
                            <?php endif; ?>
                        </th>
                    </tr>

                    <tr>
                        <th width="15%">№ заявки</th>
                        <th>Превью</th>
                        <th style="text-align: right;">Дата согласования</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr class="sorted-row ">
                            <td class="model model-row model-row-id-<?php echo $model->getId(); ?>" data-model="<?php echo $model->getId() ?>"
                                data-discussion="<?php echo $model->getDiscussionId() ?>">
                                <div class="stats-summary__num bc_green">
                                    <?php echo $model->getId(); ?>
                                </div>
                            </td>
                            <td>
                                <div style="float: left; width: 100%; display: inline-block;">
                                    <?php
                                        $preview_images = $model->getPreviewImage();
                                        if (!is_null($preview_images)):
                                            $preview_images = $preview_images->getRawValue();
                                            $preview_img = array_shift($preview_images);
                                    ?>
                                        <a data-fancybox="<?php echo $model->getId(); ?>" href="<?php echo $preview_img; ?>" data-caption="<?php echo $model->getName(); ?>"><img src='<?php echo $preview_img; ?>' style='width: 150px;'></a>
                                        <?php foreach ($preview_images as $preview_image): ?>
                                            <a style="display: none;" data-fancybox="<?php echo $model->getId(); ?>" href="<?php echo $preview_img; ?>" data-caption="<?php echo $model->getName(); ?>"><img src='<?php echo $preview_img; ?>' style='width: 150px;'></a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="d-tar">
                                <div class=""><?php echo $model->getAcceptedDate(); ?></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>

                <?php if (isset($paginatorData)): ?>
                    <table width="100%" style="margin-top: 10px;">
                        <tr>
                            <td><?php include_partial('global/paginator', $paginatorData); ?></td>
                        </tr>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
