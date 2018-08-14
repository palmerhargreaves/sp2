<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 07.09.2016
 * Time: 10:38
 */
$dealer = $activity_examples_materials->getDealer();

echo sprintf('[%s] - %s', $dealer->getShortNumber(), $dealer->getName());