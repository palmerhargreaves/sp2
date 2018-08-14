<?php

$f = 'getAdditionalFile';
if ($item->getFileIndex() != 0) {
    $f = 'getAdditionalFileExt' . $item->getFileIndex();
}

$report = $item->getReport();
$model = $report->getModel();
$activity = $model->getActivity();

$file = $report->$f();
if (!empty($file)):
    ?>
    <tr class='favorite-item-<?php echo $item->getId(); ?>'>
        <td>
            <?php if ($item->isImage($file)): ?>
                <input type="checkbox" class="ch-check-uncheck-fav-report-item"
                       id="chFavoriteReportItem<?php echo $item->getId(); ?>"
                       name="chFavoriteReportItem<?php echo $item->getId(); ?>"
                       data-id="<?php echo $item->getId(); ?>"/>

            <?php endif; ?>
        </td>
        <td><?php echo sprintf('%s - %s', $activity->getId(), $activity->getName()); ?></td>
        <td style='text-align: center;'><?php echo $model->getModelType()->getName(); ?></td>
        <td style='text-align: center;'><?php echo $model->getId(); ?></td>
        <td><?php echo $model->getDealer()->getName() ?></td>
        <td style='text-align: center;'><?php echo D::toDb($item['report_added']); ?></td>
        <td>
            <a href="<?php echo url_for('@agreement_model_report_download_additional_file?file=' . $file) ?>"
               target="_blank"><?php echo $file, ' (', $report->getAdditionalFileNameHelperByName($file)->getSmartSize() . ')' ?></a>
        </td>
        <td style='text-align: center;'><img src='/images/delete-icon.png' class='delete-favorite-item'
                                             data-id='<?php echo $item->getId(); ?>' title='Удалить'
                                             style='cursor: pointer'/></td>
    </tr>
<?php endif; ?>

