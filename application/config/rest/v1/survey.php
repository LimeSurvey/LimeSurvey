<?php

use \LimeSurvey\Api\Command\V1\{
    SurveyAdd,
    SurveyDelete
};

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Survey
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];

$rest['v1/survey'] = [
    'POST' => [
        'commandClass' => SurveyAdd::class,
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

$rest['v1/survey/$id'] = [
    'DELETE' => [
        'commandClass' => SurveyDelete::class,
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
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Not Found'
            ]
        ]
    ]
];

return $rest;
