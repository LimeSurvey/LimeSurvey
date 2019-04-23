<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/**
 * Font packages
 * @license GPL v3
 * core path is application/core/packages
 *
 * Note: When debug mode, asset manager is turned off by default.
 * To enjoy this feature, add to your package definition a 'devBaseUrl' with the relative url to your package
 *
 */
$debug = isset($userConfig['config']['debug']) ? $userConfig['config']['debug'] : 0;
/* To add more easily min version : config > 2 , seems really an core dev issue to fix bootstrap.js ;) */
$minVersion = ($debug > 0) ? "" : ".min";
/* needed ? @see third_party.php */
if (isset($_GET['isAjax'])) {
    return array();
}
return array(
    
    'fontawesome' => array(
        //'basePath' => 'third_party.bootstrap', // Need fix third_party alias
        'devBaseUrl' => 'assets/fonts/font-src/fontawesome/',
        'basePath' => 'fonts.font-src.fontawesome',
        'css'=> array(
            'css/font-awesome'.$minVersion.'.css',
        ),
    ),

    'font-roboto' => array(
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'roboto.css',
        ),
    ),

    'font-icomoon' => array(
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'icomoon.css',
        ),
    ),

    'font-noto' => array(
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'noto.css',
        ),
    ),

    'font-news_cycle' => array(
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'news_cycle.css',
        ),
    ),

    'font-ubuntu' => array(
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'ubuntu.css',
        ),
    ),

    'font-lato' => array(
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'lato.css',
        ),
    ),

    // see: https://www.w3schools.com/cssref/css_websafe_fonts.asp
    'font-websafe' => array(
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'websafe.css',
        ),
    ),

);
