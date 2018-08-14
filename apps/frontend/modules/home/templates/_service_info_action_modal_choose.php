<div id="service-action-choose-modal" class="intro modal" data-position="fixed">
    <div class="modal-header">Важная информация:</div>
    <div class="modal-close"></div>
    <div class="modal-text" style="float: left;">
        <div style="display: block; margin-left: 30px: margin-top: 1px;">
        <?php foreach($services as $item): ?>
            <div class='service-dialog-item service-dialog-<?php echo $item->getId(); ?>'>
                <a href='javascript:;' class='show-service-modal-dialog' data-id='<?php echo $item->getId(); ?>' data-dialog-type="service"> <?php echo $item->getHeader(); ?></a>
                <span style='font-size: 11px; font-weight: normal; margin: 5px; float:left; '><?php echo Utils::trim_text($item->getRawValue()->getDescription(), 255); ?></span>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <div class="modal-text" style="float: left;">
        <div style="display: block; margin-left: 30px: margin-top: 1px;">
            <?php foreach($info as $item): ?>
                <div class='info-dialog-item info-dialog-<?php echo $item->getId(); ?>'>
                    <a href='javascript:;' class='show-info-modal-dialog' data-id='<?php echo $item->getId(); ?>' data-dialog-type="info"> <?php echo $item->getHeader(); ?></a>
                    <span style='font-size: 11px; font-weight: normal; margin: 5px; float:left; '><?php echo Utils::trim_text($item->getRawValue()->getDescription(), 255); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php $steps_status = $steps_status->getRawValue(); ?>

    <?php if (!empty($steps_status)): ?>
        <div class="modal-text" style="float: left;">
            <div style="display: block; margin-left: 30px: margin-top: 1px;">
                <div class='steps-dialog-item steps-dialog'>
                    <a href='javascript:;' class='show-steps-modal-dialog'>Заполните статистику</a>
                    <span style='font-size: 11px; font-weight: normal; margin: 5px; float:left; '>
                          Необходимо заполнить статистику по активност(и, ям)
                    </span>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
    $(function() {
        $(document).on('click', '.show-steps-modal-dialog', function() {
            $("#sc-modal").krikmodal('show');
        });

        $('.show-service-modal-dialog, .show-info-modal-dialog').live('click', function() {
            var el = $(this), dialogType = el.data('dialog-type');

            $.post('<?php echo url_for('service_dialogs_show'); ?>',
                {
                    id : $(this).data('id'),
                    dialogType: dialogType
                },
                function(result) {
                    $('#service-action-modal-container').empty().html(result);
                    $('#service-action-modal-container').krikmodal('show');
                }
            );
        });
    });

</script>

