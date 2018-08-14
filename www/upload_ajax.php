<?php
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$_SERVER['REQUEST_URI'] = 'temp_file_ajax/upload';
$_COOKIE['user_remember'] = '1'; // чтобы сохранить авторизацию

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'upload', true);
sfContext::createInstance($configuration)->dispatch();
