<?php

// Code below copied from index.php.

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

require_once __DIR__ . '/../../third_party/autoload.php';
require_once BASEPATH . 'yii' . EXT;
require_once APPPATH . 'core/LSYii_Application' . EXT;
$config = require_once(APPPATH . 'config/internal' . EXT);

Yii::$enableIncludePath = false;
Yii::createApplication('LSYii_Application', $config);

Yii::app()->loadHelper('common');
Yii::import('application.helpers.common_helper', true);
Yii::import('application.libraries.PluginManager.*', true);
