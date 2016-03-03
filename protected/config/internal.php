<?php

/**
 * This file contains configuration parameters for the Yii framework.
 * Do not change these unless you know what you are doing.
 *
 *
 */
use ls\components\SurveySessionManager;
use ls\components\WebUser;

// Make sure we set default values for passed in parameters so we pass SideEffects testing.
$bowerAssetPath = !isset($bowerAssetPath)?: $bowerAssetPath;
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

        'coreTemplates' => __DIR__ . '/../../public/templates',
        'userTemplates' => __DIR__ . '/../../public/upload/templates',
        'bower' => $bowerAssetPath,

        'publicPath' => dirname(get_included_files()[0])

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
            'class' => \ls\components\MigrationManager::class
        ],
        'bootstrap' => [
            'class' => \TbApi::class,
        ],
        'format' => [
            'class' => \ls\components\LocalizedFormatter::class
        ],
        'clientScript'=> [
            'class' => \CClientScript::class,
            'packages' => require('packages.php'),
        ],


        'urlManager' => [
            'urlFormat' => 'get',
            'rules' => require('routes.php'),
            'showScriptName' => true,
        ],
        'assetManager' => [
            'class' => \ls\components\AssetManager::class,
            'basePath' => __DIR__ . '/../../public/tmp/assets',
            'relativeUrl' => 'tmp/assets'
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
            'class' => \ls\pluginmanager\PluginManager::class,
            'apiMap' => [
                "1.0" => ls\pluginmanager\LimesurveyApi::class
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