<?php

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponsesPatch;
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactorySurveyResponses,
    SchemaFactorySurveyResponsesPatch
};

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

$errorSchema = (new SchemaFactoryError())->make();
$responsesSchema = (new SchemaFactorySurveyResponses())->make();
$responsesPatchSchema = (new SchemaFactorySurveyResponsesPatch())->make();

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
                'schema' => $responsesSchema,
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
    ],
    'PATCH' => [
        'tag' => 'survey',
        'description' => 'Survey responses patch',
        'commandClass' => SurveyResponsesPatch::class,
        'auth' => true,
        'example' => __DIR__ . '/example/survey-responses-patch.json',
        'schema' => (Schema::create()),
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'schema' => $responsesPatchSchema,
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


return $rest;
