<?php

use LimeSurvey\Api\Command\V1\{
    SurveysGroupList,
};

use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactorySurveysGroupList
};

$errorSchema = (new SchemaFactoryError)->make();

$rest = [];

$rest['v1/surveys-group'] = [
    'GET' => [
        'description' => 'Surveys group list',
        'commandClass' => SurveysGroupList::class,
        'auth' => 'session',
        'params' => [
            'pageSize' => ['type' => 'int'],
            'page' => ['type' => 'int']
        ],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveysGroupList)->make()
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized',
                'schema' => $errorSchema
            ]
        ]
    ]
];

return $rest;
