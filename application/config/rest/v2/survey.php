<?php

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// REST V2 Survey Config
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$schema = [
    'SurveyListSuccess' => require(__DIR__ . '/schema/SurveyListSuccess.php')
];

$rest = [];
$v2Namespace = '\LimeSurvey\Api\Command\V2\\';

$rest['v2/survey'] = [
    'GET' => [
        'description' => 'Survey list',
        'commandClass' => $v2Namespace . 'SurveyList',
        'auth' => 'session',
        'params' => [
            'pageSize' => ['type' => 'int'],
            'page' => ['type' => 'int']
        ],
        'examples' => [],
        'content' => null,
        'schema' => null,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => $schema['SurveyListSuccess']
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized'
            ]
        ]
    ]
];

$rest['v2/survey-detail/$surveyId'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Survey detail',
        'commandClass' => $v2Namespace . 'SurveyDetail',
        'auth' => 'session',
        'params' => [],
        'examples' => null,
        'content' => null,
        'schema' => null,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => null,
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
    'PATCH' => [
        'tag' => 'survey',
        'description' => 'Survey update via RFC 6902 based patch',
        'commandClass' => $v2Namespace . 'SurveyPatch',
        'auth' => 'session',
        'params' => [],
        'examples' => null,
        'content' => null,
        'schema' => (
            Schema::object()
            ->properties(
                Schema::array('patch')
            )
        ),
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'examples' => null,
                'content' => null,
                'schema' => null
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
