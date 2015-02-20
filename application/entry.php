<?php
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        die("Make sure you run composer install.");
    } 
	$loader = require_once(__DIR__ . '/vendor/autoload.php'); 
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	define('ROOT', dirname(__FILE__));

	// The PHP file extension
	define('EXT', '.php');

	// Path to the front controller (this file)
	define('BASEPATH', true);
	define('APPPATH', __DIR__);

	if (file_exists(__DIR__ . '/debug')) {
		define('YII_DEBUG', true);
		ini_set('display_errors', 1);
        error_reporting(E_ALL);
    } else {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);// Not needed if user don't remove his 'debug'=>0, for application/config/config.php (Installation is OK with E_ALL)
    }

    if (version_compare(PHP_VERSION, '5.4.0', '<'))
        die('This version of LimeSurvey  requires PHP version 5.4.0 or later! Your version: '.PHP_VERSION);


class_exists('Yii');
Yii::$enableIncludePath = false;
$config = require_once(__DIR__ . '/config/internal' . EXT);
$config['loader'] = $loader;
unset($loader);
//var_dump($config['components']['themeManager']); die();

Yii::createApplication('WebApplication', $config)->run();
