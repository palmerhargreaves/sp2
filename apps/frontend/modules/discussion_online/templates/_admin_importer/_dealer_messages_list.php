<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 18.08.2017
 * Time: 12:09
 */

$msg_index = 0;
foreach ($default_dealer_data as $message):
    $message = $message->getRawValue();
?>

<div class="discussion__list__item discussion__list__item__<?php echo $message['model']['discussion_id']; ?> <?php echo $msg_index++ == 0 ? 'active' : ''; ?> js-dealers-discussion-list-item"
     data-discussion-id="<?php echo $message['model']['discussion_id']; ?>"
     data-model-id="<?php echo $message['model']['id']; ?>">
    <em><?php echo D::formatMessagesDate($message['created_at']); ?></em>
    <strong>№ <?php echo $message['model']['id']; ?></strong>
    <span><?php echo $message['model']['name']; ?></span>
</div>
<?php endforeach;
