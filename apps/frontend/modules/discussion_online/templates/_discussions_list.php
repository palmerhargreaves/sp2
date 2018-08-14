<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 07.08.2017
 * Time: 12:15
 */

$msg_index = 0; ?>
<?php $first_message = null; ?>

<?php foreach ($messages_data['messages_list'] as $message): ?>
    <?php $message = $message->getRawValue(); ?>

    <?php if ($msg_index == 0) {
        $first_message = $message;
    }
    ?>

    <div class="discussion__list__item discussion__list__item__<?php echo $message['model']['discussion_id']; ?> <?php echo $msg_index == 0 ? 'active' : ''; ?> js-dealer-discussion-list-item"
            data-discussion-id="<?php echo $message['model']['discussion_id']; ?>"
            data-model-id="<?php echo $message['model']['id']; ?>"
            data-dealer-id="<?php echo $message['model']['dealer_id']; ?>">
        <em><?php echo D::formatMessagesDate($message['created_at']); ?></em>
        <strong>№ <?php echo $message['model']['id']; ?></strong>
        <span><?php echo $message['model']['name']; ?></span>
    </div>
    <?php $msg_index++; ?>
<?php endforeach; ?>
