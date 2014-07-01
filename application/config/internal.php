<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This file contains configuration parameters for the Yii framework.
 * Do not change these unless you know what you are doing.
 * 
 */
@date_default_timezone_set(@date_default_timezone_get());
$internalConfig = array(
	'basePath' => dirname(dirname(__FILE__)),
	'runtimePath' => dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'runtime',
	'name' => 'LimeSurvey',
	'defaultController' => 'surveys',
	'import' => array(
		'application.core.*',
		'application.models.*',
		'application.controllers.*',
		'application.modules.*',
	),
	'components' => array(
        'bootstrap' => array(
            'class' => 'application.core.LSBootstrap',
            'responsiveCss' => false,
            'jqueryCss' => false
        ),
		'urlManager' => array(
			'urlFormat' => 'get',
			'rules' => require('routes.php'),
			'showScriptName' => true,
		),
        
        'clientScript' => array(
            'packages' => require('third_party.php')
        ),
        'assetManager' => array(
            'baseUrl' => '/tmp/assets'
        ),
		'request' => array(
            'class'=>'LSHttpRequest',
            'noCsrfValidationRoutes'=>array(
//              '^services/wsdl.*$'   // Set here additional regex rules for routes not to be validate
                'remotecontrol'
            ),
            'enableCsrfValidation'=>true,    // CSRF protection
            'enableCookieValidation'=>false   // Enable to activate cookie protection
        ),
        'user' => array(
            'class' => 'LSWebUser',
        ),
		'log' => array(
			'class' => 'CLogRouter'
		),
        'cache'=>array(
           'class'=>'system.caching.CFileCache',
        ),
        'db' => array(
                'schemaCachingDuration' => 3600,
        )
	)
);

if (!file_exists(dirname(__FILE__) .  '/config.php')) {
    $userConfig = require(dirname(__FILE__) . '/config-sample-mysql.php');
} else {
    $userConfig = require(dirname(__FILE__) . '/config.php');
}

$result = CMap::mergeArray($internalConfig, $userConfig);
/**
 * Some workarounds for erroneous settings in user config.php.
 */
$result['defaultController'] = $internalConfig['defaultController'];
return $result;
/* End of file internal.php */
/* Location: ./application/config/internal.php */