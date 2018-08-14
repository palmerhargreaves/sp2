<div class="discussion">
    <?php
    include_partial($discussions->getLabel(), array('discussions' => $discussions));
    //include_partial(/*$user_messages->getLabel().*/ 'dealer_discussions', array('discussions' => $discussions));
    //include_partial(/*$user_messages->getLabel().*/'admin_discussions', array('discussions' => $discussions));
    //include_partial(/*$user_messages->getLabel().*/'importer_discussions', array('discussions' => $discussions));
    ?>
</div><!-- discussion -->

<div id="discussion-wrapper" class="toggled">
    <div id="sidebar-wrapper">
        <div class="user">
            <div class="user-panel">

            </div>
            <div class="user-info">
                <h4>Последние сообщения пользователей</h4>
            </div>
        </div>

        <div id="scrollable">
            <ul class="chat" id="discussion-rightbar-chat"></ul>
        </div>
    </div>
</div>

<div class="dev-settings">
    <div id="js-chat-messages-counter" data-open="true" class="dev-settings-button">
        <span class="fa container-messages-counter">0</span>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        <?php if (isset($dealer_to_go)): ?>
            var dealer_id = <?php echo $dealer_to_go->getId(); ?>;

            if ($('.discussion__dealer__item__' + dealer_id).length != 0) {
                $('.discussion__dealer__item__' + dealer_id).trigger('click');

                scrollTop('.discussion__dealer__item__' + dealer_id, 'dealers-discussions-container');

                var discussion_type = $('.discussions-messages-types');

                $.each(discussion_type, function (index, item) {
                    var element = $(item);

                    if (!element.hasClass('current') && element.data('type') == 'ask') {
                        element.trigger('click');
                    }
                });
            }
        <?php endif; ?>

        $('.js-file').each(function () {
            var control = $(this);
            control.wrap('<span class="fld-file-wrap" />');

            var wrap = control.parent('.fld-file-wrap');
            wrap.append('<span class="fld-file-val" /><span class="fld-file-btn" />');

            var val = wrap.find('.fld-file-val');
            /*control.on('change', function () {
             var arVal = control.val().split('\\'),
             file = arVal[arVal.length - 1];
             if (file.length)
             val.text(file);
             });*/
        });

        $('.js-select').selectpicker({
            //style: 'btn-info',
            size: 8
        });
    });
</script>
