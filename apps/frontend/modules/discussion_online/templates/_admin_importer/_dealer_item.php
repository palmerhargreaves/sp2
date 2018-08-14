<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 18.08.2017
 * Time: 11:04
 */

$first_dealer = $first_dealer->getRawValue();

foreach ($dealers_list as $item): ?>
    <?php
    $item = $item->getRawValue();

    $dealer = $item['dealer'];
    $user = $item['user'];
    if (!empty($dealer)): ?>
        <div class="discussion__list__item dealer_model_item <?php echo !empty($first_dealer) && $first_dealer['dealer']['id'] == $item['dealer']['id'] ? "active" : ""; ?>
                discussion__dealer__list__item__<?php echo $item['message']['discussion_id']; ?> discussion__list__item__<?php echo $item['message']['discussion_id']; ?>
                discussion__dealer__item__<?php echo $dealer['id']; ?>"
             data-user-id="<?php echo $user['id']; ?>"
             data-dealer-id="<?php echo $dealer['id']; ?>"
             data-discussion-id="<?php echo $item['message']['discussion_id']; ?>"
             data-user-name="<?php echo $user['name']; ?>"
             data-dealer-name="<?php echo $dealer['name']; ?>"
             data-dealer-number="<?php echo $dealer['number']; ?>"
        >
            <strong><?php echo $dealer['number']; ?> <?php echo $dealer['name']; ?></strong>
            <span><?php echo $user['name']; ?></span>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
