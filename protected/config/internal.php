<?php

/**
 * This file contains configuration parameters for the Yii framework.
 * Do not change these unless you know what you are doing.
 *
 *
 */
use ls\components\SurveySessionManager;
use ls\components\WebUser;

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
    'name' => 'LimeSurvey',
    'loader' => $loader,
    'localeClass' =>  \ls\core\Locale::class,
    'defaultController' => 'surveys',
    'aliases' => array(
        'vendor' => __DIR__ . '/../vendor',
        'bootstrap' => __DIR__ . '/../vendor/crisu83/yiistrap/widgets',
        'yiiwheels' => __DIR__ . '/../vendor/2amigos/yiiwheels',

        'coreTemplates' => $public . '/templates',
        'userTemplates' => $public . '/upload/templates',
        'bower' => $bowerAssetPath,

        // A relative URL for the public folder.
        'public' => str_replace($webroot, '', $public),
        'publicPath' => $public

    ),
    'import' => array(
        'application.core.*',
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
            'class' => 'ls\components\MigrationManager'
        ],
        'bootstrap' => [
            'class' => 'TbApi',
        ],
        'format' => [
            'class' => 'ls\components\LocalizedFormatter'
        ],
        'clientScript'=> [
            'class' => CClientScript::class,
            'packages' => require('packages.php'),
        ],


        'urlManager' => [
            'urlFormat' => 'get',
            'rules' => require('routes.php'),
            'showScriptName' => true,
        ],
        'assetManager' => [
            'basePath' => __DIR__ . '/../../public/tmp/assets',
            'baseUrl' => str_replace($webroot . '/', '', realpath(__DIR__ . '/../../public/tmp/assets'))

        ],
        'request' => [
            'class'=> HttpRequest::class,
            'noCsrfValidationRoutes' => ['remotecontrol'],
            // CSRF protection
            'enableCsrfValidation' => true,
            // Enable to activate cookie protection
            'enableCookieValidation' => false
        ],
        'user' => [
            'class' => WebUser::class,
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
            'class' => CGettextMessageSource::class,
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
            'class' => \YiiWheels::class
        ],
        'authManager' => [
            'class' => \ls\components\AuthManager::class
        ],
        'themeManager' => [
            'class' => \ls\components\ThemeManager::class,
            'basePath' => __DIR__ . '/../themes'
        ]
    ],
    'params' => [
        'version' => require __DIR__ . '/version.php',
        'updateServer' => 'http://lsupdate.befound.nl/updates/',
    ]

);



$result = CMap::mergeArray($internalConfig, $userConfig);
return $result;