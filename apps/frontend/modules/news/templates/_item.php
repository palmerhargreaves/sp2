<?php
$img = $info->getImgBig();
if (empty($img))
    $img = $info->getImgSmall();
?>

<div class="item <?php echo $item['isNew'] ? 'new' : ''; ?>">
    <div class="date"><?php echo $info->getCreatedAt(); ?></div>

    <div class="content">
        <div class="anons">
            <?php if ($item['isNew']): ?>
                <img class='anons-new' src='/images/news_55x43.png' title='<?php echo $info->getName(); ?>'/>
            <?php endif; ?>
            <h3><span><?php echo $info->getName(); ?></span></h3>
            <p class="text"><?php echo $info->getRawValue()->getAnnouncement(); ?></p>
        </div>
        <div class="full">
            <?php if ($item['isNew']): ?>
                <img class='anons-new' src='/images/news_64x50.png' title='<?php echo $info->getName(); ?>'/>
            <?php endif; ?>
            <div class="preview"><img src="/uploads/news/images/<?php echo $img; ?>"
                                      alt="<?php echo $info->getName(); ?>"></div>
            <h3><span><?php echo $info->getName(); ?></span></h3>
            <p class="text"><?php echo $info->getRawValue()->getText(); ?></p>
        </div>
    </div>
    <div class="more"><span></span></div>
</div>
