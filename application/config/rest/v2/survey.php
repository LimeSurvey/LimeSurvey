<?php

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

use \LimeSurvey\Api\Command\V2\SurveyList;
use \LimeSurvey\Api\Command\V2\SurveyDetail;
use \LimeSurvey\Api\Command\V2\SurveyPatch;

use LimeSurvey\Api\Rest\V2\SchemaFactory\SchemaFactoryError;
use LimeSurvey\Api\Rest\V2\SchemaFactory\SchemaFactorySurveyList;
use LimeSurvey\Api\Rest\V2\SchemaFactory\SchemaFactorySurveyDetail;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// REST V2 Survey Config
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];

$rest['v2/survey'] = [
    'GET' => [
        'description' => 'Survey list',
        'commandClass' => SurveyList::class,
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
                'schema' => (new SchemaFactorySurveyList)->create()
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized',
                'schema' => (new SchemaFactoryError)->create()
            ]
        ]
    ]
];

$rest['v2/survey-detail/$surveyId'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Survey detail',
        'commandClass' => SurveyDetail::class,
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
                'schema' => (new SchemaFactorySurveyDetail)->create()
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized',
                'schema' => (new SchemaFactoryError)->create()
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Not Found',
                'schema' => (new SchemaFactoryError)->create()
            ]
        ]
    ],
    'PATCH' => [
        'tag' => 'survey',
        'description' => 'Survey update via RFC 6902 based patch',
        'commandClass' => SurveyPatch::class,
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
                'description' => 'Unauthorized',
                'schema' => (new SchemaFactoryError)->create()
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Not Found',
                'schema' => (new SchemaFactoryError)->create()
            ]
        ]
    ]
];

return $rest;
