<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

return array(
    'components' => array(
        'db' => array(
            'connectionString' => 'mysql:host=127.0.0.1;port=3306;dbname=limesurvey;',
            'emulatePrepare' => true,
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'tablePrefix' => 'lime_',
        ),

        // Uncomment the following lines if you need table-based sessions.
        // Note: Table-based sessions are currently not supported on MSSQL server.
        // 'session' => array (
        // 'class' => 'application.core.web.DbHttpSession',
        // 'connectionID' => 'db',
        // 'sessionTableName' => '{{sessions}}',
        // ),

        'urlManager' => array(
            'urlFormat' => 'get',
            'rules' => array(
                // You can add your own rules here
            ),
            'showScriptName' => true,
        ),

    ),
    // Use the following config variable to set modified optional settings copied from config-defaults.php
    'config'=>array(
        // debug: Set this to 1 if you are looking for errors. If you still get no errors after enabling this
        // then please check your error-logs - either in your hosting provider admin panel or in some /logs directory
        // on your webspace.
        // LimeSurvey developers: Set this to 2 to additionally display STRICT PHP error messages and put MySQL in STRICT mode and get full access to standard themes
        'debug'=>2,
        'debugsql'=>1, // Set this to 1 to enanble sql logging, only active when debug = 2
        // 'force_xmlsettings_for_survey_rendering' => true, // Uncomment if you want to force the use of the XML file rather than DB (for easy theme development)
        // 'use_asset_manager'=>true, // Uncomment if you want to use debug mode and asset manager at the same time
        // Update default LimeSurvey config here
        'mysqlEngine'=>'InnoDB',
    )
);
