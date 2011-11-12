<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

return array(
	'basePath' => dirname(dirname(__FILE__)),
	'name' => 'LimeSurvey',

	'import' => array(
		'application.core.*',
		'application.models.*',
		'application.controllers.*',
		'application.controllers.admin.*',
	),

	'defaultController' => 'survey',

	'components' => array(
		'db' => array(
			'connectionString' => 'mysql:host=localhost;dbname=limesurvey_ci',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
			'tablePrefix' => 'lime_',
		),
		/**
		 Uncomment after implementing the error controller
		'errorHandler' => array(
			'errorAction' => 'error',
		),
		 */
		'urlManager' => array(
			'urlFormat' => 'path',
			'rules' => require('routes.php'), //!!! Completely move it here
		),
		'session' => array (
		    'class' => 'system.web.CDbHttpSession',
		    'connectionID' => 'db',
		    'sessionTableName' => 'YiiSession',
		),
	),
);
?>


/* End of file config.php */
/* Location: ./application/config/config.php */