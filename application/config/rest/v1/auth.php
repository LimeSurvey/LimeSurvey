<?php

use LimeSurvey\Api\Command\V1\{
    AuthSessionCreate,
    AuthTokenSimpleCreate,
    AuthTokenSimpleRefresh,
    AuthTokenSimpleRelease
};
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactoryAuthToken
};

$errorSchema = (new SchemaFactoryError)->make();

$rest = [];

$rest['v1/auth'] = [
    'POST' => [
        'description' => 'Generate new authentication token',
        'commandClass' => AuthTokenSimpleCreate::class,
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
    'PUT' => [
        'description' => 'Refresh authentication token',
        'commandClass' => AuthTokenSimpleRefresh::class,
        'auth' => true,
        'params' => [],
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
        'commandClass' => AuthTokenSimpleRelease::class,
        'auth' => true,
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

// Add session endpoints to auth endpoints for backward compatibility
// - can remove this once the survey template functionality is calling
// - /auth instead of /session
$rest['v1/session'] = $rest['v1/auth'];
$rest['v1/session']['POST']['commandClass'] = AuthSessionCreate::class;

return $rest;
