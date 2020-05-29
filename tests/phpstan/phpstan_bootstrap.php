<?php

// Code below copied from index.php.
// File also used for Psalm checker.

$system_path = "framework";
$application_folder = dirname(__FILE__) . "/../../application";
if (realpath($system_path) !== false) {
    $system_path = realpath($system_path).'/';
}

// ensure there's a trailing slash
$system_path = rtrim($system_path, '/').'/';

define('BASEPATH', str_replace("\\", "/", $system_path));
define('APPPATH', $application_folder.'/');
define('EXT', '.php');
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('ROOT', dirname(__FILE__ . '../../'));
define('FCPATH', str_replace(SELF, '', __FILE__));
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));
define('YII_DEBUG', true);

require_once __DIR__ . '/../../third_party/autoload.php';
require_once BASEPATH . 'yii' . EXT;
require_once APPPATH . 'core/LSYii_Application' . EXT;
$config = require_once(APPPATH . 'config/internal' . EXT);


Yii::$enableIncludePath = false;
$app = Yii::createApplication('LSYii_Application', $config);
$app->init();

Yii::setPathOfAlias('webroot', __DIR__ . '/../../');

Yii::app()->loadHelper('common');
Yii::import('application.core.*', true);
Yii::import('application.models.Template', true);
Yii::import('application.models.Token', true);
Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.globalsettings_helper', true);
Yii::import('application.helpers.qanda_helper', true);
Yii::import('application.helpers.expressions.em_core_helper', true);
Yii::import('application.helpers.expressions.em_manager_helper', true);
Yii::import('application.helpers.replacements_helper', true);
Yii::import('application.libraries.PluginManager.PluginManager', true);
Yii::import('application.libraries.MenuObjects.*', true);
Yii::import('application.third_party.Twig.TemplateInterface', true);
Yii::import('application.third_party.Twig.*', true);

/** @var PluginManager */
$pluginManager = Yii::app()->getComponent('pluginManager');
$pluginManager->scanPlugins(true);

error_reporting(E_ALL);
