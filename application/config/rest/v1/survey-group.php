<?php

use LimeSurvey\Api\Command\V1\{
    SurveyGroupList,
};

use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactorySurveyGroupList
};

$errorSchema = (new SchemaFactoryError)->make();

$rest = [];

$rest['v1/survey-group'] = [
    'GET' => [
        'description' => 'Survey group list',
        'commandClass' => SurveyGroupList::class,
        'auth' => true,
        'params' => [
            'pageSize' => ['type' => 'int'],
            'page' => ['type' => 'int']
        ],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveyGroupList)->make()
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
