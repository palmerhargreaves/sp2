<?php
/**
 * Created by PhpStorm.
 * User: kostig51
 * Date: 30.01.2018
 * Time: 3:38
 */

?>

<div class="contacts-wrapper">
    <div class="contacts">

        <h1 style="height: 38px;">Если у вас появились вопросы, которые требуют оперативного ответа сотрудников агентства, напишите нам напрямую.</h1>

        <div class="quarter-tabs tabs">
            <?php foreach ($contacts as $contact): ?>
            <div class="tab " data-pane="contact-pane<?php echo $contact->getId(); ?>"
                 data-user-id="<?php echo $contact->getId(); ?>"
                 data-user-name="<?php echo $contact->getUserName(); ?>"
            >
                <div class="tab-header">
                    <div class="required-activities">
                        <img src="images/check-icon.png" alt=""/>
                    </div>
                    <div class="photo">
                        <img src="//dm-ng.palmer-hargreaves.ru/admin/files/<?php echo $contact->getPhoto(); ?>" alt="" />
                    </div>
                    <span><?php echo implode("<br/>", explode(" ", $contact->getName())); ?></span><br/><?php echo $contact->getDuties(); ?>
                </div>
                <div class="tab-body">
                    <div>
                        <?php echo $contact->getRawValue()->getDescription(); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <form id="frm-contact-message">
        <div class="textarea-wrapper">
            <textarea name="message" placeholder="Выберите сотрудника"></textarea>
        </div>

        <?php if ($sf_user->getAuthUser()->getDealer()): ?>
        <div class="send-button-wrapper">
            <button id="send-button" class="button gray2" type="submit"
                    data-dealer-discussion-id="<?php echo $sf_user->getAuthUser()->getDealerDiscussion()->getId(); ?>">
                Отправить заявку
            </button>
        </div>
        <?php endif; ?>
    </form>

</div>

<script>
    $(function() {
        window.contacts = new Contacts({
            on_send_message_url: '<?php echo  url_for("@contact_send_message"); ?> '
        }).start();
    });
</script>
