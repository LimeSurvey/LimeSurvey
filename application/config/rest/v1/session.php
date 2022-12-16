<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Session
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V1\\';

$rest['v1/session'] = [
    'POST' => [
        'commandClass' => $v1Namespace . 'SessionKeyCreate',
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
        'commandClass' => $v1Namespace . 'SessionKeyRelease',
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
