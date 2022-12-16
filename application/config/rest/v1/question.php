<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Question
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V1\\';

$rest['v1/question/$questionID'] = [
    'GET' => [
        'commandClass' => $v1Namespace . 'QuestionPropertiesGet',
        'auth' => 'session',
        'params' => [
            'questionSettings' => true,
            'language' => true
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
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Not Found'
            ]
        ]
    ],
    'PUT' => [
        'commandClass' => $v1Namespace . 'QuestionPropertiesSet',
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
    ],
    'DELETE' => [
        'commandClass' => $v1Namespace . 'QuestionDelete',
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

$rest['v1/question'] = [
    'POST' => [
        'commandClass' => $v1Namespace . 'QuestionImport',
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
    ],
    'GET' => [
        'commandClass' => $v1Namespace . 'QuestionGroupList',
        'auth' => 'session',
        'params' => [
            'surveyID' => true,
            'groupID' => true,
            'language' => true,
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
    ]
];

return $rest;
