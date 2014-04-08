<?php
	require_once('vendor/autoload.php'); 
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	define('ROOT', dirname(__FILE__));

	// The PHP file extension
	define('EXT', '.php');

	// Path to the front controller (this file)
	define('FCPATH', str_replace(SELF, '', __FILE__));
	define('BASEPATH', true);
	define('APPPATH', __DIR__ . '/application');

	if (file_exists(__DIR__ . '/debug'))
    {
		define('YII_DEBUG', true);
		ini_set('display_errors', 1);
        error_reporting(E_ALL);
    }
    else
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);// Not needed if user don't remove his 'debug'=>0, for application/config/config.php (Installation is OK with E_ALL)
    }

    if (version_compare(PHP_VERSION, '5.3.0', '<'))
        die ('This script can only be run on PHP version 5.3.0 or later! Your version: '.PHP_VERSION.'<br />');


/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 *
 */
require_once 'vendor/yiisoft/yii/framework/yii.php';

require_once 'application/core/LSYii_Application.php';


//if (!file_exists(APPPATH . 'config/config' . EXT)) {
//    // If Yii can not start due to unwritable runtimePath, present an error
//    $runtimePath = $config['runtimePath'];
//    if (!is_dir($runtimePath) || !is_writable($runtimePath)) {
//        // @@TODO: present html page styled like the installer
//        die (sprintf('%s should be writable by the webserver (755 or 775).', $runtimePath));
//    }
//}
Yii::createApplication('LSYii_Application', require_once(__DIR__ . '/application/config/internal.php'))->run();
/* End of file index.php */
/* Location: ./index.php */
