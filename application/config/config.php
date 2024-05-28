<?php

return array(
    'components' => array(
        'db' => array(
            'connectionString' => 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'),
            'emulatePrepare' => true,
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'tablePrefix' => 'lime_',
            'mysqlEngine' => 'InnoDB',
        ),
        'urlManager' => array(
            'urlFormat' => 'path',
            'rules' => array(
                '' => 'survey/index',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
            ),
            'showScriptName' => true,
        ),
        'request' => array(
            'enableCsrfValidation' => true,
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
                array(
                    'class' => 'CWebLogRoute',
                ),
            ),
        ),
    ),
    'params' => array(
        'debug' => 2,
        'debugsql' => 0,
    ),
);
