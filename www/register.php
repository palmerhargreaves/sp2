<?php
if (!in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', '90.156.236.35')))
{
  die('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'register_user', false);
sfContext::createInstance($configuration);
sfContext::getInstance()->set('register_user', true);
sfContext::getInstance()->dispatch();
