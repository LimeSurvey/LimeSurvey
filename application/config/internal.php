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

if (! date_default_timezone_set(@date_default_timezone_get()))
{

    date_default_timezone_set('Europe/London');
}


if (function_exists('mb_internal_encoding')) {
    // Needed to substring arabic etc
    mb_internal_encoding('UTF-8');
    if (ini_get('mbstring.internal_encoding'))
    {
        ini_set('mbstring.internal_encoding','UTF-8');
    }

}
else {
    // Do nothing, will be checked in installation
}

$internalConfig = array(
    'basePath' => dirname(dirname(__FILE__)),

    'name' => 'LimeSurvey',
    'localeClass' =>  'LSYii_Locale',
    'defaultController' => 'surveys',

    'aliases' => array(
        // Third party path
        'third_party' => realpath(__DIR__ . '/../../third_party'),

        // yiistrap configuration
        'bootstrap' => realpath(__DIR__ . '/../extensions/bootstrap'),
        'questiontypes' => realpath(__DIR__ . '/../extensions/questionTypes'),
        'vendor.twbs.bootstrap.dist' => realpath(__DIR__ . '/../extensions/bootstrap'),
        // yiiwheels configuration
        'yiiwheels' => realpath(__DIR__ . '/../extensions/yiiwheels'),
        'vendor.twbs.bootstrap.dist',
    ),

    'modules'=>array(
            'gii'=>array(
                //'class'=>'system.gii.GiiModule',
                //'password'=>'toto',
                // 'ipFilters'=>array(...a list of IPs...),
                // 'newFileMode'=>0666,
                // 'newDirMode'=>0777,
            ),
        ),

    'params'=>array(
        'defaultPageSize'=>10	,                                                                                                                     // Default page size for most of the grids
        'pageSizeOptions'=>array(5=>5,10=>10,20=>20,50=>50,100=>100),                                                                                 // Default page size options for most of the grids
        'pageSizeOptionsTokens'=>array(5=>5,10=>10,25=>25,50=>50,100=>100, 250=>250, 500=>500, 1000=>1000, 2500=>2500, 5000=>5000, 10000=>10000),     // Tokens needs different options
        'defaultEllipsizeHeaderValue'=>30,                                                                                                            // Default max characters before ellipsizing the headers of responses grid
        'defaultEllipsizeQuestionValue'=>50,                                                                                                           // Default max characters before ellipsizing the questions inside responses grid
    ),

    'import' => array(
        'application.core.*',
        'application.core.db.*',
        'application.models.*',
        'application.controllers.*',
        'application.modules.*',

        'bootstrap.helpers.*',
        'bootstrap.widgets.*',
        'bootstrap.behaviors.*',
        'yiiwheels.widgets.select2.WhSelect2',

    ),
    'preload' => array ('log'),
    'components' => array(
      // yiistrap configuration
        'bootstrap' => array(
            'class' => 'bootstrap.components.TbApi',
        ),
        // yiiwheels configuration
        'yiiwheels' => array(
            'class' => 'yiiwheels.YiiWheels',
        ),

        'clientScript'=>array(
            'packages' => require('third_party.php'),
        ),

        'urlManager' => array(
            'urlFormat' => 'get',
            'rules' => require('routes.php'),
            'showScriptName' => true,
        ),
        // These are defaults and are later overwritten in LSYii_Application by a path based on config tempdir/tempurl
        'assetManager' => array(
            'excludeFiles' => array("config.xml", "assessment.pstpl", "clearall.pstpl",  "completed.pstpl",  "endgroup.pstpl",  "endpage.pstpl",  "groupdescription.pstpl",  "load.pstpl",  "navigator.pstpl",  "printanswers.pstpl",  "print_group.pstpl",  "print_question.pstpl",  "print_survey.pstpl",  "privacy.pstpl",  "question.pstpl",  "register.pstpl",  "save.pstpl",  "startgroup.pstpl",  "startpage.pstpl",  "surveylist.pstpl",  "survey.pstpl",  "welcome.pstpl" ),
            'baseUrl' => '/tmp/assets',
            'basePath'=> dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'assets',
        ),

        'request' => array(
            'class'=>'LSHttpRequest',
            'enableCsrfValidation'=>true,    // CSRF protection
            'enableCookieValidation'=>false,   // Enable to activate cookie protection
            'noCsrfValidationRoutes'=>array(
                'remotecontrol',
                'plugins/unsecure',
            ),
            'csrfCookie' => array(
                'secure' => ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']== 443))
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
                'secure' => ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']== 443))
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
        ),
        'format'=>array(
            'class'=>'application.extensions.CustomFormatter'
        ),
    )
);



$result = CMap::mergeArray($internalConfig, $userConfig);
/**
 * Some workarounds for erroneous settings in user config.php.
 * seems not to be used anymore...
 */
$result['defaultController']=($result['defaultController']=='survey') ? $internalConfig['defaultController'] : $result['defaultController'];
/**
 * Allways add needed routes at end
 */
$result['components']['urlManager']['rules']['<_controller:\w+>/<_action:\w+>']='<_controller>/<_action>';

return $result;
/* End of file internal.php */
/* Location: ./application/config/internal.php */
