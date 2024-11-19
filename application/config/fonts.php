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
$debug = $userConfig['config']['debug'] ?? 0;
/* To add more easily min version : config > 2 , seems really an core dev issue to fix bootstrap.js ;) */
$minVersion = ($debug > 0) ? "" : ".min";
/* needed ? @see vendor.php */
if (isset($_GET['isAjax'])) {
    return array();
}

// When adding new fonts, please keep fonts ordered alphabetically
$coreFonts = array(

    'font-ibm-sans' => array(
        'title' => 'IBM Sans',
        'type' => 'core',
        'devBaseUrl' => 'assets/fonts/ibm-plex-sans',
        'basePath' => 'fonts.ibm-plex-sans',
        'css' => array(
            'ibm-sans.css',
        ),
    ),

    'font-ibm-serif' => array(
        'title' => 'IBM Plex Serif',
        'type' => 'core',
        'devBaseUrl' => 'assets/fonts/ibm-plex-serif',
        'basePath' => 'fonts.ibm-plex-serif',
        'css' => array(
            'ibm-serif.css',
        ),
    ),

    'remix' => array(
        'title' => 'Remix Icon',
        'type' => 'core',
        //'basePath' => 'vendor.bootstrap', // Need fix vendor alias
        'devBaseUrl' => 'assets/fonts/font-src/remix',
        'basePath' => 'fonts.font-src.remix',
        'css' => array(
            'remixicon.css',
        ),
    ),

    'fontawesome' => array(
        'title' => 'Font Awesome',
        'type' => 'core',
        //'basePath' => 'vendor.bootstrap', // Need fix vendor alias
        'devBaseUrl' => 'assets/fonts/font-src/fontawesome/',
        'basePath' => 'fonts.font-src.fontawesome',
        'css' => array(
            'css/font-awesome' . $minVersion . '.css',
        ),
    ),

    'font-icomoon' => array(
        'title' => 'IcoMoon',
        'type' => 'core',
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'icomoon.css',
        ),
    ),

    'font-lato' => array(
        'title' => 'Lato',
        'type' => 'core',
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'lato.css',
        ),
    ),

    'font-news_cycle' => array(
        'title' => 'News Cycle',
        'type' => 'core',
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'news_cycle.css',
        ),
    ),

    'font-noto' => array(
        'title' => 'Noto',
        'type' => 'core',
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'noto.css',
        ),
    ),

    'font-roboto' => array(
        'title' => 'Roboto',
        'type' => 'core',
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'roboto.css',
        ),
    ),


    // see: https://www.w3schools.com/cssref/css_websafe_fonts.asp
    'font-websafe' => array(
        'title' => 'Websafe',
        'type' => 'core',
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'websafe.css',
        ),
    ),

    'font-opensans' => array(
        'title' => 'Open Sans',
        'type' => 'core',
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'opensans.css',
        ),
    ),

    'font-source-sans-pro' => array(
        'title' => 'Source Sans Pro',
        'type' => 'core',
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'source-sans-pro.css',
        ),
    ),

    'font-raleway' => array(
        'title' => 'Raleway',
        'type' => 'core',
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'raleway.css',
        ),
    ),

    'font-ubuntu' => array(
        'title' => 'Ubuntu',
        'type' => 'core',
        'devBaseUrl' => 'assets/fonts/',
        'basePath' => 'fonts',
        'css' => array(
            'ubuntu.css',
        ),
    ),


);

// get user fonts configuration from /upload/fonts directory
// simple implementation
// TODO: move this section to new fonts model once it become needed
$userFonts = array();
$config = require(__DIR__ . '/../config/config-defaults.php');
$configUserFontsDir = $config['userfontsrootdir'];
$configUserFontsUrl = $config['userfontsurl'];
if (is_dir($configUserFontsDir)) {
    foreach (new \DirectoryIterator($configUserFontsDir) as $userFont) {
        if (!$userFont->isDot() && $userFont->isDir()) {
            $userFontDir = $userFont->getFilename();
            $configFile = $configUserFontsDir . DIRECTORY_SEPARATOR . $userFontDir . DIRECTORY_SEPARATOR . 'config.xml';
            if (function_exists('simplexml_load_file') && file_exists($configFile)) {
                if (\PHP_VERSION_ID < 80000) {
                    libxml_disable_entity_loader(false); // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
                }
                $xml = simplexml_load_file($configFile);
                $cssFiles = array();
                foreach ($xml->files->css as $file) {
                    if (!empty((string)$file)) {
                        $cssFiles[] = (string)$file;
                    }
                }

                $userFonts['font-' . $xml->metadata->name] = array(
                    'title' => $xml->metadata->title,
                    'type' => 'user',
                    'devBaseUrl' => $configUserFontsUrl . DIRECTORY_SEPARATOR . $xml->metadata->name . DIRECTORY_SEPARATOR,
                    'basePath' => 'fonts',
                    'css' => $cssFiles,
                );
                if (\PHP_VERSION_ID < 80000) {
                    libxml_disable_entity_loader(true);
                }
            }
        }
    }
}

return array_merge($coreFonts, $userFonts);
