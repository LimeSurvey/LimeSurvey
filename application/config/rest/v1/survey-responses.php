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
        'auth' => false,
        'params' => [
            'filters' => ['type' => 'int'],
            'sort' => ['type' => 'int'],
            'pageSize' => ['type' => 'int'],
            'page' => ['type' => 'int']
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


return $rest;
