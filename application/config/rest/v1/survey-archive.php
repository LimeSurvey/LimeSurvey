<?php

use LimeSurvey\Api\Command\V1\{
    SurveyArchive,
    SurveyArchiveDetails
};
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactorySurveyArchive
};
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

$rest = [];

$rest['v1/survey-archives/$id'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Survey archives',
        'commandClass' => SurveyArchive::class,
        'auth' => true,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveyArchive())->make()
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Not Found',
                'schema' => $errorSchema
            ]
        ]
    ]
];

$rest['v1/action/survey-archives/id/$id/basetable/$basetable'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Survey archives',
        'commandClass' => SurveyArchive::class,
        'auth' => true,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveyArchive())->make()
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Not Found',
                'schema' => $errorSchema
            ]
        ]
    ]
];

$rest['v1/survey-archive-details/$id'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Get token and/or response archive data',
        'commandClass' => SurveyArchiveDetails::class,
        'auth' => true,
        'params' => [
            'timestamp' => ['type' => 'int'],
            'archiveType' => ['type' => 'string'],
            'filters' => ['type' => 'json'],
            'sort' => ['type' => 'json'],
            'page' => ['type' => 'int'],
            'pageSize' => ['type' => 'int'],
        ],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Archive data returned successfully',
                'schema' => (Schema::create()),
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Archive not found',
                'schema' => $errorSchema,
            ]
        ]
    ]
];

return $rest;
