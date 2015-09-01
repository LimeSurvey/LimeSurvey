<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This file contains configuration parameters for the Yii framework.
 * Do not change these unless you know what you are doing.
 *
 */
if (file_exists(__DIR__ . '/config.php')) {
    $userConfig = require(__DIR__ . '/config.php');
} else {
    $userConfig = [];
}
$internalConfig = array(
	'basePath' => __DIR__ . '/../',
    'timeZone' => 'UTC',
    'sourceLanguage' => 'en',
    'controllerNamespace' => '\\ls\\controllers',
    'supportedLanguages' => include('locales.php'),
    'theme' => 'default',
	'runtimePath' => __DIR__ . '/../runtime',
	'name' => 'LimeSurvey',
    'localeClass' =>  Locale::class,
	'defaultController' => 'surveys',
	'aliases' => array(
		'vendor' => __DIR__ . '/../vendor',
        'bootstrap' => __DIR__ . '/../vendor/crisu83/yiistrap/widgets',
        'yiiwheels' => __DIR__ . '/../vendor/2amigos/yiiwheels'
	),
	'import' => array(
		'application.core.*',
        'application.components.*',
		'application.models.*',
        'application.models.installer.*',
		'application.controllers.*',
		'application.modules.*',
	),
    'preload' => [
        'log', 
        file_exists(__DIR__ . '/config.php') ? 'pluginManager' : null
    ],
	'components' => [
        'surveySessionManager' => [
            'class' => SurveySessionManager::class
        ],
        'migrationManager' => [
            'class' => 'MigrationManager'
        ],
        'bootstrap' => [
            'class' => 'TbApi',
        ],
        'format' => [
            'class' => 'LocalizedFormatter'
        ],
        'clientScript'=> [
//            'class' => 'ext.ExtendedClientScript.ExtendedClientScript',
//            'combineCss' => false,
//            'compressCss' => false,
//            'combineJs'=> YII_DEBUG,
//            'compressJs'=>false,
            'packages' => require('third_party.php'),
        ],


        'urlManager' => [
            'urlFormat' => 'get',
            'rules' => require('routes.php'),
            'showScriptName' => true,
        ],
        'assetManager' => [
            'class' => \AssetManager::class,
            'baseUrl' => '{baseUrl}/tmp/assets',
            'basePath'=> __DIR__ . '/../../tmp/assets'
        ],
        'request' => [
            'class'=>'LSHttpRequest',
            'noCsrfValidationRoutes' => ['remotecontrol'],
            'enableCsrfValidation' => true,    // CSRF protection
            'enableCookieValidation' => false   // Enable to activate cookie protection
        ],
        'user' => [
            'class' => 'WebUser',
            'stateKeyPrefix' => 'LSWebUser',
            'loginUrl' => ['users/login']
        ],
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                'vardump' => [
                    'class' => CWebLogRoute::class,
                    'levels'=>'error, warning, trace, info',
                    'except' => [
                        'system.CModule'
                    ],
                    'enabled' => YII_DEBUG
                ],
                'profile' => [
                    'class' => CProfileLogRoute::class,
                    'enabled' => YII_DEBUG
                ]
            )
        ),
        'cache'=>array(
           'class' => YII_DEBUG ? 'system.caching.CDummyCache' : CFileCache::class,
        ),
        'db' => array(
            'schemaCachingDuration' => 3600,
            'class' => 'DbConnection',
            'enableProfiling' => true,
            'enableParamLogging' => true,
            'charset' => 'utf8',
        ),
        'session' => [
            'cookieParams' => [
                'httponly' => true,
            ],
        ],
        'messages' => [
            'class' => 'CGettextMessageSource',
            'cachingDuration' => 3600,
            'forceTranslation' => true,
            'useMoFile' => true,
            'basePath' => __DIR__ . '/../../locale'
        ],
        'pluginManager' => [
            'class' => "\\ls\\pluginmanager\\PluginManager",
            'apiMap' => [
                "1.0" => "\\ls\\pluginmanager\\LimesurveyApi"
            ],
            'pluginFile' => __DIR__ . '/plugins.php',
            'enabledPluginDir' => __DIR__ . '/plugins',
            'pluginDirs' => [
                __DIR__ . '/../core/plugins',
                __DIR__ . '/../../plugins',
                
            ],
            'loader' => $loader // Composer classloader.
        ],
        'yiiwheels' => [
            'class' => 'YiiWheels'
        ],
        'authManager' => [
            'class' => 'AuthManager'
        ],
        'themeManager' => [
            'class' => ThemeManager::class,
            'basePath' => __DIR__ . '/../themes'
        ]
    ],
    'params' => [
        'version' => require __DIR__ . '/version.php',
        'updateServer' => 'http://lsupdate.befound.nl/updates/',
        'bower-asset' => 'components'
    ]

);



$result = CMap::mergeArray($internalConfig, $userConfig);
return $result;
/* End of file internal.php */
/* Location: ./application/config/internal.php */
