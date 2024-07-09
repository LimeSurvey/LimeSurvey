<?php

use LimeSurvey\Api\Command\V1\{
    AuthKeyCreate,
    AuthKeyRelease
};
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactoryAuthToken
};

$errorSchema = (new SchemaFactoryError)->make();

$rest = [];

$rest['v1/session'] = [
    'POST' => [
        'description' => 'Generate new authentication token',
        'commandClass' => AuthKeyCreate::class,
        'params' => [
            'username' => ['src' => 'form'],
            'password' => ['src' => 'form']
        ],
        'bodyParams' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success - returns string access token for use in header '
                    . '"Authorization: Bearer $token"',
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
        'description' => 'Destroy currently used authentication token',
        'commandClass' => AuthKeyRelease::class,
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
