<?php

use LimeSurvey\Api\Command\V1\UserPermission;
use LimeSurvey\Api\Rest\V1\SchemaFactory\{SchemaFactoryError, SchemaFactoryUserPermission};

$errorSchema = (new SchemaFactoryError())->make();

$rest = [];

$rest['v1/user-permissions'] = [
    'GET' => [
        'tag' => 'user',
        'description' => 'User permissions',
        'commandClass' => UserPermission::class,
        'auth' => true,
        'params' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactoryUserPermission())->make()
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
