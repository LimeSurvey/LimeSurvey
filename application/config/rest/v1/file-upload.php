<?php

use LimeSurvey\Api\Command\V1\{
    FileUpload
};

use LimeSurvey\Api\Rest\V1\SchemaFactory\SchemaFactorySiteSettings;

$rest = [];

$rest['v1/file-upload'] = [
    'POST' => [
        'description' => 'File Upload',
        'commandClass' => FileUpload::class,
        'params' => [
            'survey_id' => [
                'type' => 'int',
            ]
        ],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => null // ToDO
            ]
        ]
    ]
];

return $rest;
