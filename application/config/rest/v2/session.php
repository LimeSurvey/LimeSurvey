<?php

use LimeSurvey\Api\Rest\V2\SchemaFactory\SchemaFactoryError;
use LimeSurvey\Api\Rest\V2\SchemaFactory\SchemaFactoryAuthToken;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Session
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V2\\';

$rest['v2/session'] = [
    'POST' => [
        'description' => 'Generate new authentication token',
        'commandClass' => $v1Namespace . 'SessionKeyCreate',
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
                'schema' => (new SchemaFactoryError)->create()
            ]
        ]
    ],
    'DELETE' => [
        'description' => 'Destroy currently used authentication token',
        'commandClass' => $v1Namespace . 'SessionKeyRelease',
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
                'schema' => (new SchemaFactoryError)->create()
            ]
        ]
    ]
];

return $rest;
