<a href="<?php echo url_for('home/index') ?>" class="small back button">Назад</a>
<div style="margin-top: 24px" class="content-header">История событий</div>
<div style="top: 85px; width: 100%; left: 460px; " class="content-search" id="filters">
    <form id="history-search-form">
        <div class="date-input filter">
            <input type="text" placeholder="№ заявки" name="model" value="<?php echo $model_filter ?>"/>
        </div>

        <div class="modal-select-wrapper krik-select select dealer filter" style="width: 300px;">
            <?php if ($dealer_filter): ?>
                <span class="select-value"><?php echo $dealer_filter->getRawValue() ?></span>
                <input type="hidden" name="dealer_id" value="<?php echo $dealer_filter->getId() ?>">
            <?php else: ?>
                <span class="select-value">Все дилеры</span>
                <input type="hidden" name="dealer_id">
            <?php endif; ?>
            <div class="ico"></div>
            <span class="select-filter"><input type="text"></span>

            <div class="modal-input-error-icon error-icon"></div>
            <div class="error message"></div>
            <div class="modal-select-dropdown">
                <div class="modal-select-dropdown-item select-item" data-value="">Все</div>
                <?php foreach ($dealers as $dealer): ?>
                    <div class="modal-select-dropdown-item select-item"
                         data-value="<?php echo $dealer->getId() ?>"><?php echo $dealer->getRawValue() ?></div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="date-input filter">
            <input type="text" placeholder="дата" name="by_date"
                   value="<?php echo $by_date_filter ? date('d.m.Y', $by_date_filter) : '' ?>"
                   class="with-date"/>
        </div>
    </form>
</div>

<div id="history">
    <div class="history-wrapper">
        <?php include_partial('list', array('history' => $history)) ?>
        <div id="history-load-place"></div>
    </div>
    <div class="preloader" id="history-preloader"></div>
</div>

<script type="text/javascript">
    $(function () {
        new AutoPagerSearcher({
            search_form: "#history-search-form",

            pager: new AutoPager({
                markerSelector: '#history-preloader',
                placeHolder: '#history-load-place',
                listUrl: "<?php echo url_for('@history_page') ?>",
                pageLen: <?php echo $page_len ?>
            }).start()
        }).start();

        $('#history .history-wrapper').on('click', '.history-item', function () {
            var $a = $('a', this);
            if ($a.length > 0)
                location.href = $a.attr('href');

            return false;
        });
    });
</script>
