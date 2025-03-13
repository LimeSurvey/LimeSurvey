<?php


use LimeSurvey\Api\Command\V1\{
    SessionKeyCreate,
    SessionKeyRelease
};
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
};

$errorSchema = (new SchemaFactoryError)->make();

$rest = [];

$rest['v1/ai/completion'] = [
    'GET' => [
        'description' => 'Chat completions',
        'commandClass' => \LimeSurvey\Api\Command\V1\LLM::class,
        'auth' => false,
        'params' => [
            'command' => ['type' => 'str'],
            'operation' => ['type' => 'str']
        ],
        'bodyParams' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => null
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
