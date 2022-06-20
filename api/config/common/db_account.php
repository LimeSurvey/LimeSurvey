<?php

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Mysql\Connection as MySqlConnection;

return [
    ConnectionInterface::class => [
        'class' => MySqlConnection::class,
        '__construct()' => [
            'dsn' => $params['yiisoft/db-mysql']['account']['dsn'],
        ],
        'setUsername()' => [$params['yiisoft/db-mysql']['account']['username']],
        'setPassword()' => [$params['yiisoft/db-mysql']['account']['password']],
        'setCharset()' => [$params['yiisoft/db-mysql']['account']['charset']],
    ],
];