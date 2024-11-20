<?php

use LimeSurvey\Api\Command\V1\FileUpload;
use LimeSurvey\Api\Rest\V1\SchemaFactory\SchemaFactoryError;

$errorSchema = (new SchemaFactoryError())->make();
$rest = [];

$rest['v1/file-upload/$id'] = [
    'POST' => [
        'tag' => 'upload',
        'description' => 'File upload via Axios post request',
        'commandClass' => FileUpload::class,
        'params' => [
            'pageSize' => ['type' => 'int'],
            'page' => ['type' => 'int']
        ],
        'auth' => true,
        'example' => __DIR__ . '/example/survey-post-template.json',
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'examples' => null,
                'content' => null,
                'schema' => null
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
];;

return $rest;
