<?php

use LimeSurvey\Api\Command\V1\{SurveyGroupList, SurveyGroups};

use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactorySurveyGroupList
};

$errorSchema = (new SchemaFactoryError)->make();

$rest = [];

$rest['v1/survey-group-list'] = [
    'GET' => [
        'tag' => 'survey-group',
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

$rest['v1/survey-groups'] = [
    'GET' => [
        'tag' => 'survey-group',
        'description' => 'Survey groups',
        'commandClass' => SurveyGroups::class,
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
