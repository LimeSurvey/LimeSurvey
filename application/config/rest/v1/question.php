<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Question
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V1\\';

////////////////////////////////////////////////////////////////////////
// POST rest/v1/question
// GET rest/v1/question
$rest['v1/question'] = [
    'POST' => [
        'commandClass' => $v1Namespace . 'QuestionImport',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [
            'surveyID' => true,
            'groupID' => true,
            'importData' => true,
            'importDataType' => true,
            'newQuestionTitle' => true,
            'newQuestion' => true,
            'newQuestionHelp' => true
        ]
    ],
    'GET' => [
        'commandClass' => $v1Namespace . 'QuestionGroupList',
        'auth' => 'session',
        'params' => [
            'surveyID' => true,
            'groupID' => true,
            'language' => true,
        ],
        'bodyParams' => []
    ]
];

// GET rest/v1/question/$questionID
// PUT rest/v1/question/$questionID
// DELETE rest/v1/question/$questionID
$rest['v1/question/$questionID'] = [
    'GET' => [
        [
            'commandClass' => $v1Namespace . 'QuestionPropertiesGet',
            'auth' => 'session',
            'params' => [
                'questionSettings' => true,
                'language' => true
            ],
            'bodyParams' => []
        ]
    ],
    'PUT' => [
        [
            'commandClass' => $v1Namespace . 'QuestionPropertiesSet',
            'auth' => 'session',
            'params' => [],
            'bodyParams' => [
                'language' => true,
                'questionData' => true
            ]
        ]
    ],
    'DELETE' => [
        [
            'commandClass' => $v1Namespace . 'QuestionDelete',
            'auth' => 'session',
            'params' => [],
            'bodyParams' => []
        ]
    ]
];


return $rest;
