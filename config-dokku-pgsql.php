<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
if (!function_exists('pg_connection_string_from_database_url')) {
    function pg_connection_string_from_database_url() {
        extract(parse_url($_ENV["DATABASE_URL"]));
        $dbname = substr($path, 1);
        return "pgsql:host=$host;port=$port;user=$user;password=$pass;dbname=$dbname;"; # <- you may want to add sslmode=require there too
    }
}
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
|    'connectionString' Hostname, database, port and database type for
|     the connection. Driver example: mysql. Currently supported:
|                 mysql, pgsql, mssql, sqlite, oci
|    'username' The username used to connect to the database
|    'password' The password used to connect to the database
|    'tablePrefix' You can add an optional prefix, which will be added
|                 to the table name when using the Active Record class
|
*/
return array(
    'name' => 'LimeSurvey',
    'components' => array(
        'db' => array(
            'connectionString' => pg_connection_string_from_database_url(),
            'emulatePrepare' => true,
            'username' => parse_url($_ENV["DATABASE_URL"], PHP_URL_USER),
            'password' => parse_url($_ENV["DATABASE_URL"], PHP_URL_PASS),
            'charset' => 'utf8',
            'tablePrefix' => 'lime_',
        ),

        // Uncomment the following line if you need table-based sessions
        // 'session' => array (
            // 'class' => 'application.core.web.DbHttpSession',
            // 'connectionID' => 'db',
            // 'sessionTableName' => '{{sessions}}',
        // ),

        'urlManager' => array(
            'urlFormat' => 'get',
            'rules' => array(
            // You can put your own rules here
            ),
            'showScriptName' => true,
        ),

    ),
    // Use the following config variable to set modified optional settings copied from config-defaults.php
    'config'=>array(
    // debug: Set this to 1 if you are looking for errors. If you still get no errors after enabling this
    // then please check your error-logs - either in your hosting provider admin panel or in some /logs directory
    // on your webspace.
    // LimeSurvey developers: Set this to 2 to additionally display STRICT PHP error messages and get full access to standard themes
        'debug'=>0,
        'debugsql'=>0, // Set this to 1 to enanble sql logging, only active when debug = 2
        // 'use_asset_manager'=>true, // Uncomment if you want to use debug mode and asset manager at the same time
    )
);
/* End of file config.php */
/* Location: ./application/config/config.php */
