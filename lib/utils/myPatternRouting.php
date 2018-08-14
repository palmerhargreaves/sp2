<?php

/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 16.04.2016
 * Time: 15:41
 */
class myPatternRouting extends sfPatternRouting
{
    protected function normalizeUrl($url) {
        $pathInfo = parent::normalizeUrl($url);

        return strlen($pathInfo) > 1 ? preg_replace("/\/$/", '', $pathInfo) : $pathInfo;
    }
}