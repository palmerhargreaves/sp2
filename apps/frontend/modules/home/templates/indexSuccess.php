<?php $bStr = 'Бюджет на ' . $year . ' г.'; ?>

<?php if ($sf_user->getAuthUser()->isDealerUser()): ?>
    <?php include_component('budget_by_points', 'budgetPanel', array(
        'dealer' => $sf_user->getAuthUser()->getDealer(),
        'header' => $bStr,
        'year' => $year,
        'budYears' => $budgetYears,
        'filter_by_year' => $filter_by_year
    )); ?>

    <?php include_component('mailing', 'mailingPanel', array('display_filter' => true)); ?>
<?php endif; ?>

<div class="clear"></div>

<div class="actions-wrapper">
    <?php include_component('news', 'lastNews'); ?>

    <div id="container-activities-list">
        <div class="wrap">
            <div class="loader"></div>
            <div class="loaderbefore"></div>
            <div class="circular"></div>
            <div class="circular another"></div>
            <div class="text">Загрузка</div>
        </div>
    </div>

</div>
<div class="clear"></div>
<a>&nbsp;</a>

<?php

include_partial('intro_modal');
$userCertificate = false;
/*if ($sf_user->getRawValue()->getAuthUser()->getIsFirstLogin()) {

    $infoDialog = DialogsTable::getLastActiveInfoDialog(true);

    $sf_user->getRawValue()->getAuthUser()->setIsFirstLogin(false);
    $sf_user->getRawValue()->getAuthUser()->save();

    include_partial('info_modal', array('data' => $infoDialog));
}
else*/

if (($infoDialog = DialogsTable::getBindedDialog($sf_user->getRawValue()->getAuthUser())) != null) {
    include_partial('info_modal', array('data' => $infoDialog));
} else {
    //Проверка на завершение статисики дилером
    $steps_status = ActivityExtendedStatisticStepsTable::checkDealerMustCompleteStatistics($sf_user->getRawValue()->getAuthUser());

    if (!empty($steps_status)) {
        include_partial('sc_statistic_info', array( 'data' => $steps_status ));
    }

    $infoDialog = DialogsTable::getLastActiveInfoDialog();
    $serviceActive = DealerServicesDialogsTable::isActiveForUser($sf_user->getRawValue()->getAuthUser());

    if (!empty($serviceActive) && count($serviceActive) > 0 && (count($infoDialog) > 0 || !empty($steps_status))) {
        include_partial('service_info_action_modal_choose', array( 'services' => $serviceActive, 'info' => $infoDialog, 'steps_status' => $steps_status));

        include_partial('service_action_modal', array( 'data' => $serviceActive[ 0 ], 'cls' => 'service-action-modal-container' ));
        include_partial('service_action_modal_success', array( 'data' => null, 'cls' => 'service-action-modal-choose-success' ));
    } else {

        if (!empty($serviceActive) && count($serviceActive) > 0) {
            if (count($serviceActive) > 1) {
                include_partial('service_action_modal_choose', array( 'data' => $serviceActive ));

                include_partial('service_action_modal', array( 'data' => $serviceActive[ 0 ], 'cls' => 'service-action-modal-container' ));
                include_partial('service_action_modal_success', array( 'data' => null, 'cls' => 'service-action-modal-choose-success' ));
            } else {
                include_partial('service_action_modal', array( 'data' => $serviceActive[ 0 ], 'cls' => null ));
                include_partial('service_action_modal_success', array( 'data' => $serviceActive[ 0 ] ));
            }
        } else if ($infoDialog && count($infoDialog) > 0) {
            include_partial('info_modal', array( 'data' => $infoDialog->getFirst() ));
        } else if (!$sf_user->getAuthUser()->checkForFillExtendedStatistic()) {
            $userCertificate = true;
            include_partial('msg_modal');
        }
    }
}

if (!$sf_user->getRawValue()->getAuthUser()->isPostSelected()):
    include_partial('users_post_modal');
endif;
?>

<?php if (isset($approved_by_email) && $approved_by_email): ?>
    <div id="user-approve-by-email-modal" class="intro modal">
        <div class="modal-header">Подтверждение аккаунта!</div>
        <div class="modal-close"></div>
        <div class="modal-text">
            <div style="display: block; margin-left: 30px: margin-top: 1px;">
                Ваш аккаунт подтвержден.
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    $(function () {
        $.post('<?php echo url_for('@activities_load_data'); ?>', function (result) {
            $('#container-activities-list').html(result);

            $('.krik-select', $('#container-activities-list')).krikselect();
            $('.group-header').click(function () {
                $(this).parents('.group').toggleClass('open');
                $(this).parents('.group').find('.group-content').slideToggle();

                if ($(this).parents('.group').hasClass('open'))
                    $('html,body').animate({scrollTop: $(this).offset().top}, 500);
            });
        });

        <?php if (isset($approved_by_email) && $approved_by_email): ?>
            $("#user-approve-by-email-modal").krikmodal('show');
        <?php endif; ?>
    });
</script>

<?php if (!empty($not_completed)): ?>
    <script>
        $(function () {
            if (RegExp('steps', 'gi').test(window.location.search)) {
                $("#sc-modal").krikmodal('show');
            }
        });
    </script>
<?php endif; ?>

<?php if ($userCertificate): ?>
    <script>
        $(function () {
            if (RegExp('msg', 'gi').test(window.location.search)) {
                $("#msg-modal").krikmodal('show');
            }
        });
    </script>
<?php endif; ?>


<?php if (count($serviceActive) > 0 && count($infoDialog) > 0): ?>
    <script>
        $(function () {
            if (RegExp('service', 'gi').test(window.location.search)) {
                $("#service-action-choose-modal").krikmodal('show');
            }
        });
    </script>
<?php endif; ?>

<?php if ($infoDialog): ?>
    <script>
        $(function () {
            if (RegExp('info', 'gi').test(window.location.search)) {
                $("#info-modal").krikmodal('show');
            }
        });
    </script>
<?php endif; ?>

<?php if (count($serviceActive) > 1): ?>
    <script>
        $(function () {
            if (RegExp('service', 'gi').test(window.location.search)) {
                $("#service-action-choose-modal").krikmodal('show');
            }
        });
    </script>
<?php else: ?>
    <script>
        $(function () {
            if (RegExp('service', 'gi').test(window.location.search)) {
                $("#service-action-modal").krikmodal('show');
            }
        });
    </script>
<?php endif; ?>

<?php if (!$sf_user->getRawValue()->getAuthUser()->isPostSelected()): ?>
    <script>
        $(function () {
            $('#post-bg').css('height', $(document).height());
            $('#post-bg').show();

            $("#users-post-modal").krikmodal('show');
        });
    </script>
<?php endif; ?>

