<?php

ob_start();

class TmpClass
{
    public function init()
    {
    }

    public function handle($event)
    {
        echo $event->message . PHP_EOL;
    }
}

// Code below copied from index.php.
// File also used for Psalm checker.

$system_path = "vendor/yiisoft/yii/framework";
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

require_once __DIR__ . '/../../vendor/autoload.php';
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
Yii::import('application.helpers.ldap_helper', true);
Yii::import('application.helpers.export_helper', true);
Yii::import('application.helpers.remotecontrol.*');
Yii::import('application.helpers.admin.import_helper', true);
Yii::import('application.helpers.admin.exportresults_helper', true);
Yii::import('application.helpers.admin.export.*');
Yii::import('application.helpers.admin.statistics_helper', true);
Yii::import('application.helpers.admin.template_helper', true);
Yii::import('application.helpers.admin.label_helper', true);
Yii::import('application.helpers.admin.backupdb_helper', true);
Yii::import('application.helpers.admin.activate_helper', true);
Yii::import('application.helpers.admin.htmleditor_helper', true);
Yii::import('application.helpers.admin.permission_helper', true);
Yii::import('application.helpers.admin.token_helper', true);
Yii::import('application.libraries.PluginManager.PluginManager', true);
Yii::import('application.libraries.MenuObjects.*', true);
Yii::import('application.libraries.jsonRPCClient', true);
Yii::import('application.libraries.admin.quexmlpdf', true);  // Problem with AdminTheme, constants and session
Yii::import('application.helpers.update.update_helper', true);
Yii::import('application.helpers.update.updatedb_helper', true);
Yii::import('application.helpers.admin.ajax_helper', true);
Yii::import('application.controllers.admin.ExpressionValidate', true);
Yii::import('webroot.installer.create-database', true);
Yii::import('ext.GeneralOptionWidget.settings.*');
Yii::import('zii.widgets.grid.*');
Yii::import('zii.widgets.*');
Yii::import('zii.widgets.jui.*');
// TODO: Replace with autoload
LoadQuestionTypes::loadAll();

// TODO: PATH_SEPARATOR for Windows
set_include_path(get_include_path() . ':' . APPPATH . 'helpers');
Yii::import('application.libraries.LSZend_XmlRpc_Response_Http', true);
Yii::import('application.libraries.LSjsonRPCServer', true);

/** @var PluginManager */
$pluginManager = Yii::app()->getComponent('pluginManager');
$pluginManager->scanPlugins(true);

error_reporting(E_ALL);

//define("LOGO_URL", "ANYTHING");
// Needed for LOGO_URL constant. TODO: Why is this defined in a class...? Should be Yii config?
//$adminTheme = new AdminTheme();
//$adminTheme->setAdminTheme();
