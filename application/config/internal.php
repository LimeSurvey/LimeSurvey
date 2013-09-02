<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This file contains configuration parameters for the Yii framework.
 * Do not change these unless you know what you are doing.
 * 
 */
$internalConfig = array(
	'basePath' => dirname(dirname(__FILE__)),
	'runtimePath' => dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'runtime',
	'name' => 'LimeSurvey',
	'defaultController' => 'survey',
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
                '^.*_json$',
                'remotecontrol'
            ),
            'enableCsrfValidation'=>true,    // CSRF protection
            'enableCookieValidation'=>false   // Enable to activate cookie protection
        ),

        'user' => array(
            'class' => 'LSWebUser',
        ),
	)
);

if (!file_exists(dirname(__FILE__) .  '/config.php')) {
    $userConfig = require(dirname(__FILE__) . '/config-sample-mysql.php');
} else {
    $userConfig = require(dirname(__FILE__) . '/config.php');
}

return CMap::mergeArray($internalConfig, $userConfig);
/* End of file internal.php */
/* Location: ./application/config/internal.php */