<?php

use \LimeSurvey\Api\Command\V1\SessionKeyCreate;
use \LimeSurvey\Api\Command\V1\SessionKeyRelease;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Session
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];

$rest['v1/session'] = [
    'POST' => [
        'commandClass' => SessionKeyCreate::class,
        'params' => [
            'username' => true,
            'password' => true
        ],
        'content' => null,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success'
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized'
            ]
        ]
    ],
    'DELETE' => [
        'commandClass' => SessionKeyRelease::class,
        'auth' => 'session',
        'params' => [],
        'content' => null,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success'
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized'
            ]
        ]
    ]
];

return $rest;
