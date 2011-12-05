<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	'connectionString' Hostname, database, port and database type for
|	 the connection. Driver example: mysql. Currently supported:
|				 mysql, pgsql, mssql, sqlite, oci
|	'username' The username used to connect to the database
|	'password' The password used to connect to the database
|	'tablePrefix' You can add an optional prefix, which will be added
|				 to the table name when using the Active Record class
|
*/
return array(
	'basePath' => dirname(dirname(__FILE__)),
	'name' => 'LimeSurvey',
	'defaultController' => 'survey',

	'import' => array(
		'application.core.*',
		'application.models.*',
		'application.controllers.*',
	),

	'components' => array(
		/*
		'db' => array(
			'connectionString' => 'mysql:host=localhost;port=3306;dbname=limesurvey',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => 'root',
			'charset' => 'utf8',
			'tablePrefix' => 'lime_',
		),

		'session' => array (
			'class' => 'system.web.CDbHttpSession',
			'connectionID' => 'db',
			'sessionTableName' => '{{sessions}}',
		),
		*/
		'urlManager' => array(
			'urlFormat' => 'path',
			'rules' => require('routes.php'),
		),

	),
);
/* End of file config.php */
/* Location: ./application/config/config.php */