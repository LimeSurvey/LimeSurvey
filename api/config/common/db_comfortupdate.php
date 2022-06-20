<?php

use App\MultiDb\ConnectionComfortInterface;
use App\MultiDb\ConnectionComfort as MySqlConnection;

return [
    ConnectionComfortInterface::class => [
        'class' => MySqlConnection::class,
        '__construct()' => [
            'dsn' => $params['yiisoft/db-mysql']['comfortupdate']['dsn'],
        ],
        'setUsername()' => [$params['yiisoft/db-mysql']['comfortupdate']['username']],
        'setPassword()' => [$params['yiisoft/db-mysql']['comfortupdate']['password']],
        'setCharset()' => [$params['yiisoft/db-mysql']['comfortupdate']['charset']],
    ],
];