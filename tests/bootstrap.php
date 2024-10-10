<?php

/**
 * Copied from index.php.
 * Used by phpunit.
 * @since 2017-06-13
 * @author Olle Härstedt
 */

if (!file_exists(__DIR__ . '/../enabletests')) {
    echo ('phpunit disabled. NEVER run tests on a production system - the tests will modify the database. To enable phpunit, run $touch enabletests');
    exit(9);
}

// When running locally, you can get a "session already started" error from Yii. This line prevents this.
ob_start();

/*
 *---------------------------------------------------------------
 * SYSTEM FOLDER NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" folder.
 * Include the path if the folder is not in the same  directory
 * as this file.
 *
 */
$system_path = "vendor/yiisoft/yii/framework";

/*
 *---------------------------------------------------------------
 * APPLICATION FOLDER NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * folder then the default one you can set its name here. The folder
 * can also be renamed or relocated anywhere on your server.  If
 * you do, use a full server path. For more info please see the user guide:
 * http://codeigniter.com/user_guide/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 *
 */
$application_folder = dirname(__FILE__) . "/../application";

/*
 * --------------------------------------------------------------------
 * DEFAULT CONTROLLER
 * --------------------------------------------------------------------
 *
 * Normally you will set your default controller in the routes.php file.
 * You can, however, force a custom routing by hard-coding a
 * specific controller class/function here.  For most applications, you
 * WILL NOT set your routing here, but it's an option for those
 * special instances where you might want to override the standard
 * routing in a specific front controller that shares a common CI installation.
 *
 * IMPORTANT:  If you set the routing here, NO OTHER controller will be
 * callable. In essence, this preference limits your application to ONE
 * specific controller.  Leave the function name blank if you need
 * to call functions dynamically via the URI.
 *
 * Un-comment the $routing array below to use this feature
 *
 */
// The directory name, relative to the "controllers" folder.  Leave blank
// if your controller is not in a sub-folder within the "controllers" folder
// $routing['directory'] = '';

// The controller class file name.  Example:  Mycontroller.php
// $routing['controller'] = '';

// The controller function you wish to be called.
// $routing['function']    = '';


/*
 * -------------------------------------------------------------------
 *  CUSTOM CONFIG VALUES
 * -------------------------------------------------------------------
 *
 * The $assign_to_config array below will be passed dynamically to the
 * config class when initialized. This allows you to set custom config
 * items or override any default config values found in the config.php file.
 * This can be handy as it permits you to share one application between
 * multiple front controller files, with each file containing different
 * config values.
 *
 * Un-comment the $assign_to_config array below to use this feature
 *
 */
// $assign_to_config['name_of_config_item'] = 'value of config item';



// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------




/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */
if (realpath($system_path) !== false) {
    $system_path = realpath($system_path) . '/';
}

// ensure there's a trailing slash
$system_path = rtrim($system_path, '/') . '/';

// Is the system path correct?
if (!is_dir($system_path)) {
    exit("Your system folder path does not appear to be set correctly. Please open the following file and correct this: " . pathinfo(__FILE__, PATHINFO_BASENAME));
}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */


// The name of THIS file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

define('ROOT', dirname(dirname(__FILE__)));

// The PHP file extension
define('EXT', '.php');

// Path to the system folder
define('BASEPATH', str_replace("\\", "/", $system_path));

// Path to the front controller (this file)
define('FCPATH', str_replace(SELF, '', __FILE__));

// Name of the "system folder"
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));


// The path to the "application" folder
if (is_dir($application_folder)) {
    define('APPPATH', $application_folder . '/');
} else {
    if (!is_dir(BASEPATH . $application_folder . '/')) {
        exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: " . self);
    }

    define('APPPATH', BASEPATH . $application_folder . '/');
}
if (file_exists(APPPATH . 'config' . DIRECTORY_SEPARATOR . 'config.php')) {
    $aSettings = include(APPPATH . 'config' . DIRECTORY_SEPARATOR . 'config.php');
} else {
    $aSettings = array();
}
// Set debug : if not set : set to default from PHP 5.3
if (isset($aSettings['config']['debug'])) {
    if ($aSettings['config']['debug'] > 0) {
        define('YII_DEBUG', true);
        if ($aSettings['config']['debug'] > 1) {
            error_reporting(E_ALL);
        } else {
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
        }
    } else {
        define('YII_DEBUG', false);
        error_reporting(0);
    }
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);// Not needed if user doesn't remove their 'debug'=>0, for application/config/config.php (Installation is OK with E_ALL)
}

if (version_compare(PHP_VERSION, '5.3.3', '<')) {
    echo ('This script can only be run on PHP version 5.3.3 or later! Your version: ' . PHP_VERSION . '<br />');
    exit(11);
}


require_once __DIR__ . '/../vendor/autoload.php';

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 *
 */
require_once BASEPATH . 'yii' . EXT;
require_once APPPATH . 'core/LSYii_Application' . EXT;

$config = require_once(APPPATH . 'config/internal' . EXT);

if (!file_exists(APPPATH . 'config/config' . EXT)) {
    // If Yii can not start due to unwritable runtimePath, present an error
    $sDefaultRuntimePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'runtime';
    if (!is_dir($sDefaultRuntimePath) || !is_writable($sDefaultRuntimePath)) {
        // @@TODO: present html page styled like the installer
        echo (sprintf('%s should be writable by the webserver (766 or 776).', $sDefaultRuntimePath));
        exit(10);
    }
}

// NB: This line might be needed if you have PHP in a docker container.
//$config['components']['db']['connectionString'] = 'mysql:host=localhost;port=3306;dbname=ls4;';

// Check that tmp and upload are set to 777 permission (OK on test system).
if (substr(sprintf('%o', fileperms(BASEPATH . '../../../../tmp/')), -4) != '0777') {
    die('tmp folder not set to 777');
}
if (substr(sprintf('%o', fileperms(BASEPATH . '../../../../tmp/runtime/')), -4) != '0777') {
    die('tmp/runtime folder not set to 777');
}
if (substr(sprintf('%o', fileperms(BASEPATH . '../../../../upload/')), -4) != '0777') {
    die('upload folder not set to 777');
}
if (substr(sprintf('%o', fileperms(BASEPATH . '../../../../tests/tmp/')), -4) != '0777') {
    die('tests/tmp folder not set to 777');
}

// Unit tests suddenly started failing with exception "CHttpRequest is unable to determine the request URI."
// - Initialising $_SERVER makes the exception go away (kfoster - 2023-05-22)
$_SERVER['SCRIPT_FILENAME'] = 'index-test.php';
$_SERVER['SCRIPT_NAME'] =  '/index-test.php';
$_SERVER['REQUEST_URI'] = 'index-test.php';

Yii::$enableIncludePath = false;
Yii::createApplication('LSYii_Application', $config);


// TODO: Edit composer.json to add autoloading with proper namespaces.
require_once __DIR__ . '/LimeSurveyWebDriver.php';
require_once __DIR__ . '/TestHelper.php';
require_once __DIR__ . '/TestBaseClass.php';
require_once __DIR__ . '/TestBaseClassWeb.php';
require_once __DIR__ . '/TestBaseClassView.php';
require_once __DIR__ . '/DummyController.php';
require_once __DIR__ . '/unit/helpers/remotecontrol/BaseTest.php';
require_once __DIR__ . '/unit/models/BaseModelTestCase.php';

define('PHP_ENV', 'test');
// TODO: Move this logic to installater test.
$configFile = __DIR__ . '/application/config/config.php';
$configBackupFile = __DIR__ . '/application/config/test-backup.config.php';

// Enable if phpunit fails.
$forceDebug = false;
if ($forceDebug) {
    // Set env variable as to have test cases to enable error reporting.
    // Seems setting it globally here is not enough
    putenv('RUNNER_DEBUG=1');
    fwrite(STDERR, 'Set $forceDebug=false in tests/bootstrap.php to reduce the logging.' . "\n");
}
$isDebug = getenv('RUNNER_DEBUG', false);
fwrite(STDERR, 'Error Reporting and Debug: ' . ($isDebug ? 'Yes' : 'No') . "\n");
if ($isDebug) {
    define('YII_DEBUG', true);
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
   fwrite(STDERR, 'Set $forceDebug=true in tests/bootstrap.php to enable more logging.' . "\n");
}
fwrite(STDERR, "\n");

if (file_exists($configFile)) {
    copy($configFile, $configBackupFile);
}

// Dont use customer error handler in unit-tests
restore_error_handler();

register_shutdown_function(
    function () {
        $configFile = __DIR__ . '/application/config/config.php';
        $configBackupFile = __DIR__ . '/application/config/test-backup.config.php';

        @unlink($configFile);
        @rename($configBackupFile, $configFile);
    }
);
