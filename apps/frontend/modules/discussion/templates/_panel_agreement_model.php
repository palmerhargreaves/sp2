<div class="scroller" style="margin-bottom: 10px; width: 625px;" >
        <div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
        <div class="viewport scroller-wrapper" style="width: 620px;">
                <div class="overview scroller-inner">
<?php /*
                        <div class="show-old-messages">
                                <div class="links">Показать сообщения: <span class="seven">7 дней</span>, <span class="thirty">30 дней</span>, <span class="all">все</span></div>
                                <div class="line"></div>
                        </div>
 * 
 */ ?>

                        <div class="messages"></div>
                </div>
        </div>
</div>

<div class="message-send-wrapper" data-not-hide='1'>
    <form action="<?php url_for('@discussion_post') ?>" method="post" class="post ">
        <div class="textarea-wrapper">
                <textarea name="message"></textarea>
        </div>
        <div class="message-button-wrapper" style="margin-lefT: 20px;"><input type="submit" class="message-button" value="Отправить" title="Ctrl+Enter"></div>
    </form>
<?php //if(!isset($disable_upload) || !$disable_upload): ?>
        <div class="message-upload-wrapper message-upload">
                <div class="message-upload-button"><div></div></div>
                <div class="files"></div>
                <div class="clear"></div>
        </div>
<?php //else: ?>
        <!--<div class="message-upload-wrapper">
        </div>-->
<?php //endif; ?>
</div>

