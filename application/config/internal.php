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
    'localeClass' =>  'LSYii_Locale',
    'defaultController' => 'surveys',
    'import' => array(
        'application.core.*',
        'application.core.db.*',
        'application.models.*',
        'application.controllers.*',
        'application.modules.*',
    ),
    'preload' => array ('log'),
    'components' => array(
        'bootstrap' => array(
            'class' => 'application.core.LSBootstrap',
            'responsiveCss' => false,
            'jqueryCss' => false
        ),
        'clientScript'=>array(
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
            'enableCsrfValidation'=>true,    // CSRF protection
            'enableCookieValidation'=>false,   // Enable to activate cookie protection
            'csrfCookie' => array(
                'secure' => (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off'), // @see session
            ),
            'noCsrfValidationRoutes'=>array(
                'remotecontrol',
                'plugins/unsecure',
            ),
        ),
        'user' => array(
            'class' => 'LSWebUser',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                'vardump' => array(
                    'class' => 'CWebLogRoute',
                    'categories' => 'vardump', // tracevar function
                ),
                'profile' => array(
                    'class' => 'CProfileLogRoute'
                ),
                /* Adding app warning and error to ./tmp/runtime/application.log, can be replaced in config */
                'fileError' => array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'warning, error',
                ),
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
        'session' => array(
            'cookieParams' => array(
                'httponly' => true,
                // Set secure if needed , some dumb server need || $_SERVER['SERVER_PORT'] == 443 . See @link http://stackoverflow.com/a/2886224/2239406
                'secure'=>(!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off'),
            ),
        ),
        'messages' => array(
            'class' => 'CGettextMessageSource',
            'cachingDuration'=>3600,
            'forceTranslation' => true,
            'useMoFile' => true,
            'basePath' => __DIR__ . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'locale'
        ),
        'pluginManager' => array(
            'class' => "\\ls\\pluginmanager\\PluginManager",
            'api' => "\\ls\\pluginmanager\\LimesurveyApi"
        )
    ),
    'config'=>array(
        'updatable'=>false,
    ),
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
