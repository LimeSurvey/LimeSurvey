<?php

use LimeSurvey\Libraries\Api\Rest\V1\SchemaFactory\SchemaFactorySurveyStatistics;
use LimeSurvey\Api\Rest\V1\SchemaFactory\{SchemaFactoryError,
    SchemaFactorySurveyPatch,
    SchemaFactorySurveyStatisticsOverview,
    SchemaFactorySurveyTemplate
};
use LimeSurvey\Libraries\Api\Command\V1\Statistics;
use LimeSurvey\Libraries\Api\Command\V1\StatisticsOverview;

$errorSchema = (new SchemaFactoryError())->make();
$surveyPatchSchema = (new SchemaFactorySurveyPatch())->make();
$surveyTemplateSchema = (new SchemaFactorySurveyTemplate())->make();

$rest = [];

$rest['v1/statistics-overview/$id'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Statistics Overview',
        'commandClass' => StatisticsOverview::class,
        'auth' => true,
        'params' => [
        ],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveyStatisticsOverview())->make()
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized',
                'schema' => $errorSchema
            ]
        ]
    ]
];

$rest['v1/statistics/$id'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Statistics',
        'commandClass' => Statistics::class,
        'auth' => true,
        'params' => [
            'minId' => ['type' => 'integer'],
            'maxId' => ['type' => 'integer'],
            'completed' => ['type' => 'bool'],
        ],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveyStatistics())->make()
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
