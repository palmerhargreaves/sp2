<?php

$k = 0;
$isBlocked = false;
foreach ($favorites as $item):
    if ($item->getFileId() != 0) {
        include_partial('favorites_items_by_categories', array('item' => $item));
    } else {
        include_partial('favorites_items_by_type', array('item' => $item));
    }
    $k++;
endforeach;
