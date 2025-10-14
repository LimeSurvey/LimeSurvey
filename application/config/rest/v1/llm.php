<?php

use LimeSurvey\Api\Command\V1\LLM;
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactorySurveyArchive
};

$errorSchema = (new SchemaFactoryError)->make();

$rest = [];

$rest['v1/ai/completion'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Survey archives',
        'commandClass' => LLM::class,
        'auth' => false,
        'params' => [
            'command' => ['type' => 'str'],
            'operation' => ['type' => 'str']
        ],
//        'bodyParams' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveyArchive())->make()
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
