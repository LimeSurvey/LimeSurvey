<?php

use \LimeSurvey\Api\Command\V1\{
    SessionKeyCreate,
    SessionKeyRelease
};
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactoryAuthToken
};

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Session
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$errorSchema = (new SchemaFactoryError)->create();

$rest = [];

$rest['v1/session'] = [
    'POST' => [
        'description' => 'Generate new authentication token',
        'commandClass' => SessionKeyCreate::class,
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
                'schema' => (new SchemaFactoryAuthToken)->create()
            ],
            'unauthorized' => [
                'code' => 403,
                'description' => 'Unauthorized',
                'schema' => $errorSchema
            ]
        ]
    ],
    'DELETE' => [
        'description' => 'Destroy currently used authentication token',
        'commandClass' => SessionKeyRelease::class,
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
                'description' => 'Unauthorized',
                'schema' => $errorSchema
            ]
        ]
    ]
];

return $rest;
