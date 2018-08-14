<div class="news" style="float: left;">
    <h3>Последние новости <a href="<?php echo url_for("news"); ?>" class="all-news">Все новости</a></h3>
    <div class="items">
        <?php
        foreach ($lastNews as $i):
            $item = $i['item']->getRawValue();
            ?>
            <div>
                <?php if ($i['isNew']): ?>
                    <img class='news-new-main' src='/images/news_55x43.png' title='<?php echo $item->getName(); ?>'/>
                <?php endif; ?>
                <div class="preview"><a href="<?php echo url_for("@news_info?id=" . $item->getId()); ?>"><img
                                style="width 85px; height: 48px;"
                                src="<?php echo '/uploads/news/images/' . $item->getImgSmall(); ?>"
                                alt="<?php echo $item->getName(); ?>"></a></div>
                <div class="text"><?php echo $item->getName();//echo Utils::trim_text($item->getAnnouncement(), 103);
                    ?></div>
                <div class="more"><a href="<?php echo url_for("@news_info?id=" . $item->getId()); ?>">Подробнее</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
