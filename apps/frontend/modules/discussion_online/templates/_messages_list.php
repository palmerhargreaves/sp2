<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 03.08.2017
 * Time: 12:43
 */

$message_index = 0;

if (count($messages_list) > 0):
    foreach ($messages_list as $message_item):
        $message = $message_item['data'];

        include_partial('message_item', array('message' => $message, 'message_item' => $message_item, 'message_index' => $message_index++));
    endforeach;
else: ?>
    Нет сообщений
<?php endif;
