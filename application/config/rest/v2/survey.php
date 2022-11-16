<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Survey
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v2Namespace = '\LimeSurvey\Api\Command\V2\\';

$rest['v2/survey'] = [
    'GET' => [
        'commandClass' => $v2Namespace . 'SurveyList',
        'auth' => 'session',
        'params' => [
            'page' => true,
            'limit' => true
        ],
        'bodyParams' => []
    ]
];

$rest['v2/detail/$surveyId'] = [
    'GET' => [
        'commandClass' => $v2Namespace . 'SurveyDetail',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ],
    'POST' => [
        'commandClass' => $v2Namespace . 'SurveyPatch',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ]
];

return $rest;
