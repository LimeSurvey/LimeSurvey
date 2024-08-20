<?php

use LimeSurvey\Api\Command\V1\{
    UserList
};

use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactoryUserList
};

$errorSchema = (new SchemaFactoryError)->make();

$rest = [];

$rest['v1/user'] = [
    'GET' => [
        'description' => 'User list',
        'commandClass' => UserList::class,
        'auth' => true,
        'params' => [
            'pageSize' => ['type' => 'int'],
            'page' => ['type' => 'int']
        ],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactoryUserList)->make()
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized',
                'schema' => $errorSchema
            ],
            'forbidden' => [
                'code' => 403,
                'description' => 'Forbidden',
                'schema' => $errorSchema
            ]
        ]
    ]
];

return $rest;
