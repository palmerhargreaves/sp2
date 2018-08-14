<?php

$k = 0;
$isBlocked = false;

$report = $item->getReport();
$model = $report->getModel();
$activity = $model->getActivity();

$file_item = AgreementModelReportFilesTable::getInstance()->find($item->getFileId());
if ($file_item):
    $file = $file_item->getFile();

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
                <a href="<?php echo url_for('@agreement_model_report_download_additional_file?file=' . $file_item->getId()) ?>"
                   target="_blank">
                    <?php echo $file . ' (' .
                        (
                        $file_item->getFileType() == AgreementModelReport::UPLOADED_FILE_FINANCIAL
                            ? $report->getFinancialDocsFileNameHelperByName($file_item->getFileName())->getSmartSize()
                            : $report->getAdditionalFileNameHelperByName($file_item->getFileName())->getSmartSize()) .
                        ')'
                    ?>
                </a>
            </td>
            <td style='text-align: center;'><img src='/images/delete-icon.png' class='delete-favorite-item'
                                                 data-id='<?php echo $item->getId(); ?>' title='Удалить'
                                                 style='cursor: pointer'/></td>
        </tr>
    <?php endif;

endif;
