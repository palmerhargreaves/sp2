<!DOCTYPE html>
<html>
<head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="/favicon.ico"/>
    <?php include_stylesheets_versioned() ?>
    <?php include_javascripts_versioned() ?>
    <?php if ($sf_user->isDealerUser() || $sf_user->isManager()): ?>
        <script type="text/javascript">
            $(function () {
                window.common_discussion = new DealerDiscussion({
                    panel: '#chat-modal',
                    state_url: "<?php echo url_for('@discussion_state') ?>",
                    new_messages_url: "<?php echo url_for('@discussion_new_messages') ?>",
                    post_url: "<?php echo url_for('@discussion_post') ?>",
                    dealer_discussion_url: "<?php echo url_for('@get_dealer_discussion') ?>",
                    previous_url: "<?php echo url_for('@discussion_previous') ?>",
                    search_url: "<?php echo url_for('@discussion_search') ?>",
                    online_check_url: "<?php echo url_for('@discussion_online_check') ?>",
                    uploader: new Uploader({
                        selector: '#chat-modal .message-upload',
                        session_name: '<?php echo session_name() ?>',
                        session_id: '<?php echo session_id() ?>',
                        upload_url: '/upload.php',
                        delete_url: "<?php echo url_for('@upload_temp_delete') ?>"
                    }).start()
                }).start();

                window.service_clinic_stats = new ServiceClinicStats({
                    modal: '#service-clinic-stats-modal',
                    show_url: '<?php echo url_for('@service_clinic_stats_show'); ?>'
                }).start();
            });
        </script>

        <script>
            (function (i, s, o, g, r, a, m) {
                i['GoogleAnalyticsObject'] = r;
                i[r] = i[r] || function () {
                        (i[r].q = i[r].q || []).push(arguments)
                    }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                    m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m)
            })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

            ga('create', 'UA-5542235-15', 'auto');
            ga('send', 'pageview');

        </script>
    <?php endif; ?>

    <!--[if lte IE 8]>
    <link rel="stylesheet" type="text/css" media="screen" href="/css/ie.css"/>
    <![endif]-->

</head>
<?php $dealer_user = $sf_user->getAuthUser()->getDealerUsers()->getFirst(); ?>

<body class="authorized" data-auth-user-id="<?php echo $sf_user->getAuthUser()->getId(); ?>">
<div id="swfupload"></div>

<div id="discussion-live-chat"
     data-load-chat-url="<?php echo url_for('@discussion_online_get_chat_messages_list'); ?>"
     data-open-model-url="<?php echo $sf_user->getAuthUser()->isAdmin() || $sf_user->getAuthUser()->isManager() || $sf_user->getAuthUser()->isImporter()
         ? url_for('@agreement_model_management_open_from_chat')
         : url_for('@agreement_model_open_from_chat');
     ?>"
     data-dealer-url="<?php echo url_for('@agreement_model_open_from_chat'); ?>">
    <header class="clearfix">
        <!--<a href="#" class="chat-close">x</a>-->
        <h4 id="discussion-chat-user-name"></h4>
        <span class="chat-message-counter">0</span>
    </header>

    <div class="discussion-chat">
        <div class="discussion-chat-users">
            <div class="chat-message clearfix">
                <div class="chat-message-content clearfix">

                </div>
            </div>
        </div>

        <div id="chat-history" class="chat-history"></div> <!-- end chat-history -->

        <p class="chat-feedback">Пользователь вводит текст…</p>
        <form id="frm-discussion-model-messages-chat" method="post" enctype="multipart/form-data" target=""
              data-send-url="<?php echo url_for('@discussions_online_post_message'); ?>">
            <input type="hidden" name="<?php echo session_name(); ?>" value="<?php echo session_id(); ?>">
            <input type="hidden" name="upload_file_object_type" value=""/>
            <input type="hidden" name="upload_file_type" value=""/>
            <input type="hidden" name="upload_field" value=""/>
            <input type="hidden" name="upload_files_discussion_ids" value=""/>

            <div class="file">
                <div class="modal-file-wrapper input">
                    <div id="discussion-messages-files-progress-bar-chat" style="top: 5px;"
                         class="progress-bar-content progress-bar-full-width"></div>
                </div>
            </div>

            <fieldset>
                <textarea id="frm-message-chat" name="frm-message-chat" rows="4"
                          placeholder="Введите ваше сообщение" autofocus></textarea>
                <input type="file" id="discussion_message_files_chat" name="discussion_message_files_chat"
                       class="js-file-chat" multiple/>
                <input type="submit" id="frm-discussion-chat-send-button" name="frm-discussion-chat-send-button" value="Отправить" class="btn">
                <input type="hidden">
            </fieldset>

            <div class="d-popup-files-wrap scrollbar-inner">
                <div class="d-popup-files-row">
                    <div id="discussion_files_chat" class="d-popup-uploaded-files d-cb"
                         style="padding: 0px; min-height: 5px;"></div>
                </div>
            </div>
        </form>
    </div> <!-- end chat -->
</div> <!-- end live-chat -->

<div class="alert-popup fancybox-margin" id="j-alert-global" style="display: none;">
    <div class="alert-popup__content">
        <div class="alert j-wrap alert-error">
            <button type="button" class="close"><i class="fa fa-times"></i></button>
            <div class="alert-title j-title"></div>
            <p class="alert-message j-message"></p>
        </div>
    </div>
</div>

<div id="loading-block"></div>
<div class="sk-folding-cube" style="display: none;">
    <div class="sk-cube1 sk-cube"></div>
    <div class="sk-cube2 sk-cube"></div>
    <div class="sk-cube4 sk-cube"></div>
    <div class="sk-cube3 sk-cube"></div>
</div>

<?php if ($sf_user->isDealerUser() || $sf_user->isManager()): ?>
    <div id="chat-modal" style="width:640px;"
         class="chat wide modal"<?php if ($sf_user->isDealerUser()) echo ' data-dealer-discussion="' . $sf_user->getAuthUser()->getDealerDiscussion()->getId() . '"'; ?><?php if ($sf_user->isManager()) echo ' data-manager-discussion="yes"'; ?>>
        <div class="white modal-header">Задать вопрос
            <?php include_partial('discussion/form_search'); ?>
        </div>
        <div class="modal-close"></div>
        <?php if ($sf_user->isManager()): ?>
            <select name="dealer" style="margin-left: 16px;">
                <option value="">-- выберите дилера --</option>
                <?php foreach (DealerTable::getVwDealersQuery()->execute() as $dealer): ?>
                    <option value="<?php echo $dealer->getId() ?>"><?php echo $dealer ?></option>
                <?php endforeach; ?>

            </select>
        <?php endif; ?>
        <?php include_partial('discussion/panel') ?>
    </div>
<?php endif;

if ($sf_user->isManager() || $sf_user->isImporter()):
    ?>
    <div id="service-clinic-stats-modal" class="chat wide modal" style="width:480px;">
        <div class="white modal-header">Статистика Service Clinic

        </div>
        <div class="modal-close"></div>

        <div class="modal-service-clinic-stats-content-container"></div>
        <?php //include_partial('activity/')
        ?>
    </div>
<?php endif;

if ($sf_user->getAuthUser()->isSuperAdmin()) {
    ?>
    <div id="special-modal" class="chat wide modal" style="width:640px;">
        <div class="white modal-header">Комментарии
            <?php include_partial('discussion/form_search'); ?>
        </div>
        <div class="modal-close"></div>

        <?php include_partial('discussion/special_panel') ?>
    </div>

<?php } ?>

<?php include_partial('global/modal_confirm_delete') ?>
<div id="site">
    <div id="header">
        <a href="<?php echo url_for('@homepage') ?>" class="logo"></a>
        <a href="<?php echo url_for('@homepage') ?>" class="header"></a>

        <div id="menu-wrapper">
            <?php include_partial('global/menu_service') ?>
            <?php //include_component('history', 'unread') ?>

            <a href="<?php echo url_for('@homepage') . "main" ?>">
                <div id="user-messages">
                    <div class="num-bg"></div>
                    <div class="num" style="font-size: 10px; font-weight: normal; color: #9999a3; ">Вернуться на
                        главную
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div id="content" style="padding-top: 15px;">
        <?php if (!$sf_user->getAuthUser()->isSuperAdmin() && $sf_user->getAuthUser()->isDealerUser()): ?>
            <a href="<?php echo url_for('@discussion_messages'); ?>">
                <div id="special-button" style="top: 10px;">
                    <div class="ico"></div>
                    Задать вопрос
                </div>
            </a>
        <?php elseif ($sf_user->getAuthUser()->isManager()): ?>
            <a href="<?php echo url_for('@discussion_messages'); ?>">
                <div id="special-button" style="top: 10px;">
                    <div class="ico"></div>
                    Сообщения
                </div>
            </a>
        <?php endif; ?>

        <div class="nav-main">
            <a href="<?php echo url_for('@homepage') . "main" ?>">Главная</a>
            <a href="/">Активности</a>
            <a href="<?php echo url_for('@activities_statistic'); ?>">Статистика</a>

            <?php if ($sf_user->isDealerUser()): ?>
                <?php if ($dealer_user): ?>
                    <a href="<?php echo url_for('@agreement_module_model_activities') ?>">Мои заявки</a>
                <?php endif; ?>
            <?php endif; ?>

            <a href="<?php echo url_for('@activities_examples') ?>">Примеры активностей</a>

            <a href="<?php echo url_for('@contacts') ?>">Контакты</a>

            <a href="<?php echo url_for('news') ?>">Новости</a>
            <a href="<?php echo url_for('faqs') ?>">FAQ</a>
        </div><!-- /nav-main -->

        <?php echo $sf_content ?>
    </div>

    <?php include_partial('global/footer') ?>
</div>

<div id='post-bg'
     style='position: absolute; display: none; width: 100%; height: 100%; top: 0px; left: 0px; background: rgba(128, 128, 128, 0.38); z-index: 1000;'></div>

<script>
    $(function () {
        window.main_menu = new MainMenu({}).start();

        $('.js-file-chat').each(function () {
            var control = $(this);
            control.wrap('<span class="fld-file-wrap" />');

            var wrap = control.parent('.fld-file-wrap');
            wrap.append('<span class="fld-file-val" /><span class="fld-file-btn" />');
        });
    });
</script>

</body>
</html>
