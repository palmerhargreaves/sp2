<?php $default_activity_filter = ''; ?>

<div class="approvement activities-examples">

    <h1>Примеры активностей</h1>

    <div class="activities-examples-form">
        <form action="" method="post">

            <div class="activities-examples-search">
                <input type="text" name="activity_examples_filter_by_name" value="<?php echo $filter_by_text; ?>"
                       placeholder="Найти"/>
                <input type="submit" name="" value="Найти" title="Найти"/>
            </div>

            <div class="modal-select-wrapper krik-select select type">
                <span
                    class="select-value"><?php echo(!empty($filter_by_year) && isset($years[$filter_by_year]) ? $years[$filter_by_year] : ''); ?></span>
                <div class="ico"></div>
                <input type="hidden" name="activity_examples_filter_by_year"
                       value="<?php echo(!empty($filter_by_year) && isset($years[$filter_by_year]) ? $years[$filter_by_year] : ''); ?>">
                <div class="modal-input-error-icon error-icon"></div>
                <div class="error message"></div>
                <div class="modal-select-dropdown">
                    <?php foreach ($years as $key => $year): ?>
                        <div class="modal-select-dropdown-item select-item"
                             data-value="<?php echo $key; ?>"><?php echo $year; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>
    </div><!-- /activities-examples-form -->

    <div class="activities-examples-wrap d-cb">
        <div class="activities-examples-nav js-activities-examples-nav">
            <ul>
                <?php
                $default_ind = 1;

                foreach ($examples as $cat_key => $category_data):
                    ?>
                    <li class=" <?php echo $default_ind == 1 ? "current" : ""; ?>"
                        data-base-category-id="<?php echo $cat_key; ?>"
                        style="display: <?php echo $category_data['active'] ? 'block' : 'none'; ?>">
                        <strong
                            class="js-toggle examples-activity-link filter"
                            data-filter=".example-parent-category-item-<?php echo sprintf('%s', $cat_key); ?>">
                            <?php echo $category_data['data']->getName(); ?>
                        </strong>

                        <?php echo renderCategoriesList($category_data); ?>
                    </li>

                    <?php
                    $default_ind++;
                endforeach;
                ?>

            </ul>
        </div><!-- /activities-examples-nav -->

        <div id="examples-items" class="activities-examples-list container">
            <?php
            $default_ind = 1;
            $item_order = 1; ?>

            <?php foreach ($examples as $category_key => $category): ?>
                <?php echo renderCategoryItems($category_key, $category, $item_order, $default_ind, $default_activity_filter); ?>
            <?php endforeach ?>

        </div><!-- /activities-examples-list -->

    </div><!-- /activities-examples-wrap -->
</div>


<?php

function renderCategoriesList($category)
{
    ?>
    <ul>
        <?php
        $default_cat_ind = 1;

        if (isset($category['categories'])):
            foreach ($category['categories'] as $cat_id => $category_item): ?>
                <li style="display: <?php echo $category_item['active'] ? 'block' : 'none'; ?>">
                    <a href="javascript:"
                       class="examples-activity-category-link filter"
                       class="<?php echo $default_cat_ind++ == 1 ? "active" : ""; ?>"
                       data-filter=".example-category-item-<?php echo sprintf('%s-%s', $category_item['data']->getParentCategoryId(), $cat_id); ?>">
                        <?php echo $category_item['data']->getName(); ?>
                    </a>
                </li>

                <?php if (count($category_item['categories']) > 0) : ?>
                    <?php echo renderCategoriesList($category_item); ?>
                <?php endif; ?>

            <?php endforeach;
        endif; ?>
    </ul>
    <?php
}

function renderCategoryItems($category_key, $category, $item_order, $default_ind, &$default_activity_filter)
{
    if (count($category['items']) > 0):

        foreach ($category['items'] as $item):
            $parent_cat_id = $category['data']->getParentCatId($category_key);

            if (empty($default_activity_filter)) {
                $default_activity_filter = ".example-parent-category-item-" . $parent_cat_id;
            }

            $image_size = array();

            $thumb = $item->getPreviewFileThumbnail();
            $file_path = !empty($thumb) ? sfConfig::get('app_uploads_path') . '/activities/examples/preview/thumbs/' . $thumb
                : sfConfig::get('app_uploads_path') . '/activities/examples/preview/' . $item->getPreviewFile();
            if (file_exists($file_path)) {
                $image_size = getimagesize($file_path);
            }

            ?>
            <div
                class="mix activities-example example-parent-category-item-<?php echo sprintf('%s', $parent_cat_id); ?> example-category-item-<?php echo sprintf('%s-%s', $category['data']->getParentCategoryId(), $category_key); ?> example-category-item-<?php echo $category_key; ?>"
                data-myorder="<?php echo $item_order++; ?>"
                data-category-id="<?php echo $item->getCategoryId(); ?>"
                data-parent-to-show="<?php echo $parent_cat_id; ?>"
            >
                <i class="activities-example-img">
                    <img
                        data-preview-file="/uploads/activities/examples/preview/<?php echo $item->getPreviewFile(); ?>"
                        <?php if (!empty($thumb)): ?>
                            src="/uploads/activities/examples/preview/thumbs/<?php echo $item->getPreviewFileThumbnail(); ?>"
                        <?php else: ?>
                            src="/uploads/activities/examples/preview/<?php echo $item->getPreviewFile(); ?>"
                        <?php endif; ?>

                        style="cursor: pointer; <?php echo !empty($thumb) && $image_size[1] < 154 ? "height: auto;" : ""; ?>"
                        data-title="<?php echo $item->getName(); ?>"
                        alt=""/></i>
                            <span class="activities-example-txt">
                                <strong><?php echo $item->getName(); ?></strong>
                                <?php
                                $descr = $item->getDescription();
                                echo !empty($descr) ? sprintF('"%s"', $descr) : "";
                                ?>
                            </span>
                            <span class="activities-example-meta d-cb">
                                <i class="activities-example-file"><a
                                        href="<?php echo url_for('@activity_examples_download_file?file_id=' . $item->getId()); ?>"
                                        target="_blank">
                                        <img src="/images/examples/<?php echo $item->getMaterialFileExt(); ?>.png"
                                             alt=""/></a></i>
                                <span><?php echo $item->getDealer()->getName(); ?></span>
                            </span>
            </div><!-- /activities-example -->
            <?php
        endforeach;
    endif;

    if (isset($category['categories'])) {
        foreach ($category['categories'] as $cat_key => $cat_item):
            renderCategoryItems($cat_key, $cat_item, $item_order++, $default_ind++, $default_activity_filter);
        endforeach;
    }
}

?>

<div id="examples-modal" class="wide modal" style="width:640px;">
    <div class="white modal-header"></div>
    <div class="modal-close"></div>

    <div style="display: inline-table; width: 100%; text-align: center; margin-bottom: 20px;">
        <img id="examples-modal-img" style="width: 90%;"/>
    </div>
</div>

<script>
    $(function () {
        new ActivityExamples({
            defaultFilter: "<?php echo $default_activity_filter; ?>",
        }).start();
    });
</script>