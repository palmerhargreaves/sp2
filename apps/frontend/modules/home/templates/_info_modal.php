<div id="info-modal" class="modal" style="width: <?php echo $data->getWidth() ? $data->getWidth() : '500'; ?>px;">
    <?php include_partial('info_modal_data', array('data' => $data)); ?>
</div>
