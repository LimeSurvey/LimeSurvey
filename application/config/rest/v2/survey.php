<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// REST V2 Survey Config
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v2Namespace = '\LimeSurvey\Api\Command\V2\\';

$rest['v2/survey'] = [
    'GET' => [
        'description' => '',
        'commandClass' => $v2Namespace . 'SurveyList',
        'auth' => 'session',
        'params' => [
            'pageSize' => true,
            'page' => true
        ],
        'bodyParams' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'schema' => null
            ]
        ]
    ]
];

$rest['v2/survey-detail/$surveyId'] = [
    'GET' => [
        'description' => '',
        'commandClass' => $v2Namespace . 'SurveyDetail',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'schema' => null
            ]
        ]
    ]
];

$rest['v2/survey/$surveyId'] = [
    'PATCH' => [
        'description' => '',
        'commandClass' => $v2Namespace . 'SurveyPatch',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'schema' => null
            ]
        ]
    ]
];

return $rest;
