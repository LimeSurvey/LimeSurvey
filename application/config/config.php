<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

return array(
    'components' => array(
        'db' => array(
            'connectionString' => 'mysql:host=localhost;port=3306;dbname=limesurvey;',
            'emulatePrepare' => true,
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'tablePrefix' => 'lime_',
        ),
    ),
    'config'=>array(
        'debug'=>0,
        'debugsql'=>0,
        'mysqlEngine'=>'InnoDB', // Update default LimeSurvey config here
    )
);
