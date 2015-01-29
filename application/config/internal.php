<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This file contains configuration parameters for the Yii framework.
 * Do not change these unless you know what you are doing.
 *
 */

if (!file_exists(dirname(__FILE__) .  '/config.php')) {
    $userConfig = require(dirname(__FILE__) . '/config-sample-mysql.php');
} else {
    $userConfig = require(dirname(__FILE__) . '/config.php');
}
@date_default_timezone_set(@date_default_timezone_get());
$internalConfig = array(
    'basePath' => dirname(dirname(__FILE__)),
    'runtimePath' => dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'runtime',
    'name' => 'LimeSurvey',
    'defaultController' => 'surveys',
    'import' => array(
        'application.core.*',
        'application.core.db.*',
        'application.models.*',
        'application.controllers.*',
        'application.modules.*',
    ),
    'preload' => ['log'],
    'components' => array(
        'bootstrap' => array(
            'class' => 'application.core.LSBootstrap',
            'responsiveCss' => false,
            'jqueryCss' => false
        ),
        'clientScript'=>array(
            'class'=>'ext.ExtendedClientScript.ExtendedClientScript',
            'combineCss'=>false,
            'compressCss'=>false,
            'combineJs'=>$userConfig['config']['debug']>0?false:true,
            'compressJs'=>false,
            'packages' => require('third_party.php'),
        ),
        'urlManager' => array(
            'urlFormat' => 'get',
            'rules' => require('routes.php'),
            'showScriptName' => true,
        ),
        'assetManager' => array(
            'baseUrl' => '/tmp/assets',
            'basePath'=> dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'assets'
            
        ),
        'request' => array(
            'class'=>'LSHttpRequest',
            'noCsrfValidationRoutes'=>array(
                'remotecontrol'
            ),

            'enableCsrfValidation'=>true,    // CSRF protection
            'enableCookieValidation'=>false   // Enable to activate cookie protection
        ),
        'user' => array(
            'class' => 'LSWebUser',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                'CWebLogRoute' => array( // Use an associative array allow update in config
                    'class' => 'CWebLogRoute',
                ),
                'trace' => array(
                    'class'                      => 'CWebLogRoute', // you can include more levels separated by commas... trace is shown on debug only
                    'levels'                     => 'trace',        // you can include more separated by commas
                    'categories'                 => 'vardump',      // show in firebug/console
                    'showInFireBug'              => true,
                    'enabled' => defined('YII_DEBUG') && YII_DEBUG
                ),
                'profile' => [
                    'class' => 'CProfileLogRoute'
                ]
            )
        ),
        'cache'=>array(
           'class' => defined('YII_DEBUG') && YII_DEBUG ? 'system.caching.CDummyCache' : 'CFileCache',
        ),
        'db' => array(
            'schemaCachingDuration' => 3600,
            'class' => 'DbConnection',
            'enableProfiling' => isset($userConfig['config']['debugsql']) && $userConfig['config']['debugsql'] >= 1,
            'enableParamLogging' => isset($userConfig['config']['debugsql']) && $userConfig['config']['debugsql'] >= 1
        ),
        'session' => [
            'cookieParams' => array(
                'httponly' => true,
            ),
        ],
        'messages' => array(
            'class' => 'CGettextMessageSource',
            'cachingDuration'=>3600,
            'forceTranslation' => true,
            'useMoFile' => true,
            'basePath' => __DIR__ . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'locale'
        ),
        'pluginManager' => [
            'class' => "\\ls\\pluginmanager\\PluginManager",
            'api' => "\\ls\\pluginmanager\\LimesurveyApi"
        ]
    )
);



$result = CMap::mergeArray($internalConfig, $userConfig);
/**
 * Some workarounds for erroneous settings in user config.php.
 */
$result['defaultController']=($result['defaultController']=='survey') ? $internalConfig['defaultController'] : $result['defaultController'];
/**
 * Allways add needed routes at end
 */
$result['components']['urlManager']['rules']['<_controller:\w+>/<_action:\w+>']='<_controller>/<_action>';

return $result;
/* End of file internal.php */
/* Location: ./application/config/internal.php */
