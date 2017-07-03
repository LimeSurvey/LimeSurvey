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
        'core' => realpath(__DIR__ . '/../../assets/packages'),
        'fonts' => realpath(__DIR__ . '/../../fonts'),

        // yiistrap configuration
        'bootstrap' => realpath(__DIR__ . '/../extensions/bootstrap'),
        'questiontypes' => realpath(__DIR__ . '/../extensions/questionTypes'),
        'vendor.twbs.bootstrap.dist' => realpath(__DIR__ . '/../extensions/bootstrap'),
        // yiiwheels configuration
        'yiiwheels' => realpath(__DIR__ . '/../extensions/yiiwheels'),
        'vendor.twbs.bootstrap.dist',

        // Twig aliases. We don't want to change the file ETwigViewRenderer, so we can update it without difficulties
        // However, LimeSurvey directory tree is not a standard Yii Application tree.
        // we use 'third_party' instead of 'vendor'
        // This line just point application.vendor.Twig to application/third_party/Twig
        // @see: ETwigViewRenderer::$twigPathAlias
        'application.vendor.Twig'=>'application.third_party.Twig',
        // 'CaptchaExtendedAction' => realpath(__DIR__ . '/../extensions/captchaExtended/CaptchaExtendedAction.php'),
        // 'CaptchaExtendedValidator' => realpath(__DIR__ . '/../extensions/captchaExtended/CaptchaExtendedValidator.php')
    ),

    'modules'=>array(
            'gii'=>array(
                'class'=>'system.gii.GiiModule',
                'password'=>'toto',
                 'newFileMode'=>0666,
                 'newDirMode'=>0777,
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
        'third_party.Twig.*',
        'ext.captchaExtended.CaptchaExtendedAction',
        'ext.captchaExtended.CaptchaExtendedValidator'

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
            'packages' => array_merge(
                require('third_party.php'),
                require('packages.php')
            ),
            'class' => 'application.core.LSYii_ClientScript'
        ),

        'urlManager' => array(
            'urlFormat' => 'get',
            'rules' => require('routes.php'),
            'showScriptName' => true,
        ),
        // These are defaults and are later overwritten in LSYii_Application by a path based on config tempdir/tempurl
        'assetManager' => array(
            'excludeFiles' => array("config.xml" ),
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

                // Log file saved in /tmp/runtime/plugin.log
                'plugin' => array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'trace, info, error, warning',
                    'logFile' => 'plugin.log',
                    'categories' => 'plugin.*'  // The category will be the name of the plugin
                )
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

        'twigRenderer' => array(
            'class' => 'application.core.LSETwigViewRenderer',

            // All parameters below are optional, change them to your needs
            'fileExtension' => '.twig',
            'options' => array(
                'debug' => defined('YII_DEBUG') && YII_DEBUG ?true:false,
            ),
            'extensions' => array(
                'LS_Twig_Extension',
                'Twig_Extension_Sandbox',
                'Twig_Extension_StringLoader',
                'Twig_Extension_Debug',
                // 'Twig_Extension_Escaper' // In the future, this extenstion could be use to build a powerfull XSS filter
            ),
            'globals' => array(
                'html' => 'CHtml'
            ),
            'functions' => array(
                'flatEllipsizeText'       => 'viewHelper::flatEllipsizeText',
                'getLanguageData'         => 'viewHelper::getLanguageData',
                'array_flip'              => 'array_flip',
                'array_intersect_key'     => 'array_intersect_key',
                'registerPublicCssFile'   => 'LS_Twig_Extension::registerPublicCssFile',
                'registerTemplateCssFile' => 'LS_Twig_Extension::registerTemplateCssFile',
                'registerGeneralScript'   => 'LS_Twig_Extension::registerGeneralScript',
                'registerTemplateScript'  => 'LS_Twig_Extension::registerTemplateScript',
                'registerScript'          => 'LS_Twig_Extension::registerScript',
                'unregisterPackage'       => 'LS_Twig_Extension::unregisterPackage',
                'listCoreScripts'         => 'LS_Twig_Extension::listCoreScripts',
                'getAllQuestionClasses'   => 'LS_Twig_Extension::getAllQuestionClasses',
                'intval'                  => 'intval',
                'empty'                   => 'empty',
                'count'                   => 'count',
                'reset'                   => 'reset',
                'renderCaptcha'           => 'LS_Twig_Extension::renderCaptcha',
                'getPost'                 => 'LS_Twig_Extension::getPost',
                'getParam'                => 'LS_Twig_Extension::getParam',
                'getQuery'                => 'LS_Twig_Extension::getQuery',
                'isset'                   => 'isset',
                'str_replace'             => 'str_replace',
                'assetPublish'            => 'LS_Twig_Extension::assetPublish',
                'image'                   => 'LS_Twig_Extension::image',
                'sprintf'                 => 'sprintf',
                'gT'                      => 'gT',
            ),
            'filters' => array(
                'jencode' => 'CJSON::encode',
                't'     => 'eT',
                'gT'    => 'gT',
            ),

            'sandboxConfig' => array(
                'tags' => array('if', 'for', 'set', 'autoescape', 'block'),
                'filters' => array('escape', 'raw', 't', 'merge', 'length', 'gT', 'keys'),
                'methods' => array(
                    'ETwigViewRendererStaticClassProxy' =>  array("encode", "textfield", "form", "link", "emailField", "beginForm", "endForm", "dropDownList", "htmlButton", "passwordfield" ),
                    'Survey'                            =>  array("getAllLanguages"),
                    'LSHttpRequest'                     =>  array("getParam"),
                ),
                'properties' => array(
                    'ETwigViewRendererYiiCoreStaticClassesProxy' => array("Html"),
                    'LSYii_Application'                          => array("request"),
                ),
                'functions' => array('include', 'dump', 'flatEllipsizeText', 'getLanguageData', 'array_flip', 'array_intersect_key', 'registerPublicCssFile', 'registerTemplateCssFile', 'registerGeneralScript', 'registerTemplateScript', 'registerScript', 'unregisterPackage', 'listCoreScripts', 'getAllQuestionClasses','intval', 'count', 'empty', 'reset', 'renderCaptcha', 'getPost','getParam', 'getQuery', 'isset', 'str_replace', 'assetPublish', 'image', 'sprintf', 'gT' ),
            ),

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
