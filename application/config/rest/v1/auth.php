<?php

use LimeSurvey\Api\Command\V1\{
    AuthTokenCreate,
    AuthTokenRelease
};
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactoryAuthToken
};

$errorSchema = (new SchemaFactoryError)->make();

$rest = [];

$rest['v1/auth'] = [
    'POST' => [
        'description' => 'Authenticate',
        'commandClass' => AuthTokenCreate::class,
        'auth' => 'session',
        'params' => [
            'username' => ['src' => 'form'],
            'password' => ['src' => 'form']
        ],
        'bodyParams' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'schema' => (new SchemaFactoryAuthToken)->make()
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized',
                'schema' => $errorSchema
            ]
        ]
    ],
    'DELETE' => [
        'description' => 'Clear authentication',
        'commandClass' => AuthTokenRelease::class,
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
            ],
            'unauthorized' => [
                'code' => 403,
                'description' => 'Forbidden',
                'schema' => $errorSchema
            ]
        ]
    ]
];

return $rest;
