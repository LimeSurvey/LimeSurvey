<?php

use \LimeSurvey\Api\Command\V1\QuestionGroupPropertiesGet;
use \LimeSurvey\Api\Command\V1\QuestionGroupPropertiesSet;
use \LimeSurvey\Api\Command\V1\QuestionGroupDelete;
use \LimeSurvey\Api\Command\V1\QuestionGroupAdd;
use \LimeSurvey\Api\Command\V1\QuestionGroupList;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Question Group
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];

$rest['v1/questionGroup/$groupID'] = [
    'GET' => [
        'commandClass' => QuestionGroupPropertiesGet::class,
        'auth' => 'session',
        'params' => [
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
        'commandClass' => QuestionGroupPropertiesSet::class,
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
        'commandClass' => QuestionGroupDelete::class,
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

$rest['v1/questionGroup'] = [
    'POST' => [
        'commandClass' => QuestionGroupAdd::class,
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
    'GET' => [
        'commandClass' => QuestionGroupList::class,
        'auth' => 'session',
        'params' => [
            'surveyID' => true,
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
    ]
];



return $rest;
