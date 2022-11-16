<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Session
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V1\\';

////////////////////////////////////////////////////////////////////////
// POST rest/v1/session
// DELETE rest/v1/session
$rest['v1/session'] = [
    'POST' => [
        'commandClass' => $v1Namespace . 'SessionKeyCreate',
        'params' => [
            'username' => true,
            'password' => true
        ],
        'bodyParams' => []
    ],
    'DELETE' => [
        'commandClass' => $v1Namespace . 'SessionKeyRelease',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ]
];

return $rest;
