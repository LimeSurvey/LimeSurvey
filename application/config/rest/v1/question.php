<?php

use \LimeSurvey\Api\Command\V1\QuestionPropertiesGet;
use \LimeSurvey\Api\Command\V1\QuestionPropertiesSet;
use \LimeSurvey\Api\Command\V1\QuestionDelete;
use \LimeSurvey\Api\Command\V1\QuestionImport;
use \LimeSurvey\Api\Command\V1\QuestionList;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Question
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];

$rest['v1/question/$id'] = [
    'GET' => [
        'commandClass' => QuestionPropertiesGet::class,
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
        'commandClass' => QuestionPropertiesSet::class,
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
        'commandClass' => QuestionDelete::class,
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
        'commandClass' => QuestionImport::class,
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
        'commandClass' => QuestionList::class,
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
