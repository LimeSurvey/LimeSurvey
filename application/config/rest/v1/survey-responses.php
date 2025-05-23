<?php

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactorySurveyDetail,
};

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

$errorSchema = (new SchemaFactoryError())->make();

$rest = [];

$rest['v1/survey-responses/$id'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Survey responses',
        'commandClass' => SurveyResponses::class,
        'auth' => true,
        'params' => [
            'filters' => ['type' => 'array'],
            'sort' => ['type' => 'array'],
            'pageSize' => ['type' => 'array'],
            'page' => ['type' => 'array']
        ],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (Schema::create())
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized',
                'schema' => $errorSchema
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Not Found',
                'schema' => $errorSchema
            ]
        ]
    ]
];


$rest['v1/survey-responses/$id'] = [
    'DELETE' => [
        'description' => 'Delete survey responses',
        'commandClass' => \LimeSurvey\Libraries\Api\Command\V1\SurveyResponsesDelete::class,
        'auth' => true,
        'params' => [
            'rid' => ['type' => 'array'],
        ],
        'bodyParams' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
            ],
            'unauthorized' => [
                'code' => 403,
                'description' => 'Forbidden',
                'schema' => $errorSchema
            ]
        ]
    ]
];


return $rest;
