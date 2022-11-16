<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Survey
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V1\\';

////////////////////////////////////////////////////////////////////////
// GET rest/v1/survey/$surveyID
// POST rest/v1/survey/$surveyID
$rest['v1/survey/$surveyID'] = [
    'GET' => [
        'commandClass' => $v1Namespace . 'SurveyPropertiesGet',
        'auth' => 'session',
        'params' => [
            'surveySettings' => true
        ],
        'bodyParams' => []
    ],
    'POST' => [
        'byId' => 'surveyID',
        'commandClass' => $v1Namespace . 'SurveyAdd',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [
            'surveyTitle' => true,
            'surveyLanguage' => true,
            'format' => true
        ]
    ],
];
// DELETE rest/v1/survey
$rest['v1/survey'] = [
    'DELETE' => [
        'commandClass' => $v1Namespace . 'SurveyDelete',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ]
];

return $rest;
