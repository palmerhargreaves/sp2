<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 11.01.2017
 * Time: 12:25
 */

function include_stylesheets_versioned()
{
    $response = sfContext::getInstance()->getResponse();
    sfConfig::set('symfony.asset.stylesheets_included', true);
    $html = '';
    foreach ($response->getStylesheets() as $file => $options) {
        if ((strpos($file, '?') === false) && (stripos($file, 'http') !== 0) ) {
            $file .= '?v='.date_create()->format('Ymd');
        }
        $html .= stylesheet_tag($file, $options);
    }
    echo $html;
}

function include_javascripts_versioned()
{
    $response = sfContext::getInstance()->getResponse();
    sfConfig::set('symfony.asset.javascripts_included', true);
    $html = '';
    foreach ($response->getJavascripts() as $file => $options) {
        if ((strpos($file, '?') === false) && (stripos($file, 'http') !== 0) ) {
            $file .= '?v='.date_create()->format('YmdHm');
        }

        $html .= javascript_include_tag($file, $options);
    }

    echo $html;
}
