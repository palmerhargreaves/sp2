<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 14.08.2017
 * Time: 19:00
 */
$url = $main_menu_items->getUrl();
$custom_url = $main_menu_items->getCustomCodeUrl();

echo !empty($custom_url) ? '[php code]' : "<a href='".$url."' target='_blank'>".$url."</a>";



