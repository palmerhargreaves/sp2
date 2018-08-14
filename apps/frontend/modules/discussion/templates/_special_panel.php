<div class="scroller" style="margin-bottom: 10px; width: 630px;">
        <div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
        <div class="viewport scroller-wrapper">
                <div class="overview scroller-inner">
<?php /*
                        <div class="show-old-messages">
                                <div class="links">Показать сообщения: <span class="seven">7 дней</span>, <span class="thirty">30 дней</span>, <span class="all">все</span></div>
                                <div class="line"></div>
                        </div>
 * 
 */ ?>

                        <div class="special-messages"></div>
                </div>
        </div>
</div>

<div class="message-send-wrapper panel-special-message" style="min-height: 176px;">
    <form action="<?php url_for('@discussion_post') ?>" method="post" class="post post-special">
        <div class="textarea-wrapper" style="margin-bottom: 10px; height: 140px; width: 470px;">
                <textarea name="special-message" style="height: 130px; width: 460px !important;"></textarea>
        </div>
        <div class="message-button-wrapper" style="float: none;">
            <input type="button" class="message-button special-discussion-button-submit" style="margin-top: 10px; margin-bottom: 5px;" value="Отправить" title="Ctrl+Enter">
            <input type="button" class="message-button special-discussion-button-submit-read" style="margin-top: 10px; margin-bottom: 5px;" value="Прочитано" title="Отметить как прочитанное">
            <input type="button" class="message-button special-discussion-button-close" value="Отменить">
        </div>

        <div class="message-upload-wrapper message-upload">
            <div class="message-upload-button"><div></div></div>
            <div class="files"></div>
            <div class="clear"></div>
        </div>
    </form>
</div>

