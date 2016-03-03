<?php
$context = [];
// This takes < 5ms.
$context['composerConfig'] = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true);
$context['bowerAssetPath'] = __DIR__ . '/vendor/bower-asset';


if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("Make sure you run composer install.");
}
/**
 * @var \Composer\Autoload\ClassLoader $loader
 */
$context['loader'] = require(__DIR__ . '/vendor/autoload.php');

if (!defined('YII_DEBUG') && file_exists(__DIR__ . '/debug')) {
    define('YII_DEBUG', true);
    define('YII_TRACE_LEVEL', 5);
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
}

if (version_compare(PHP_VERSION, '5.5.0', '<'))
    die('This version of LimeSurvey  requires PHP version 5.5.0 or later! Your version: '.PHP_VERSION);



Yii::$enableIncludePath = false;

require_once(__DIR__ . '/helpers/globals.php');
$config = require_config(__DIR__ . '/config/internal.php', $context);
unset($context);
Yii::createApplication(WebApplication::class, $config)->run();