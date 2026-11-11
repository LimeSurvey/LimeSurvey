<?php

use LimeSurvey\Api\Command\V1\{
    LabelSetDetail,
    LabelSetList,
    LabelSetSave
};
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactoryLabelSet
};

$errorSchema = (new SchemaFactoryError())->make();
$labelSetSchemaFactory = new SchemaFactoryLabelSet();

$rest = [];

$errorResponses = [
    'unauthorized' => [
        'code' => 401,
        'description' => 'Unauthorized',
        'schema' => $errorSchema
    ],
    'forbidden' => [
        'code' => 403,
        'description' => 'Forbidden',
        'schema' => $errorSchema
    ],
    'not-found' => [
        'code' => 404,
        'description' => 'Not Found',
        'schema' => $errorSchema
    ]
];

// The route with $id must come first: a route without $id also matches requests that have one.
$rest['v1/label-sets/$id'] = [
    'GET' => [
        'tag' => 'label-sets',
        'description' => 'Label set with its labels',
        'commandClass' => LabelSetDetail::class,
        'auth' => true,
        'responses' => array_merge([
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => $labelSetSchemaFactory->make()
            ]
        ], $errorResponses)
    ],
    'PUT' => [
        'tag' => 'label-sets',
        'description' => 'Overwrite the languages and labels of a label set',
        'commandClass' => LabelSetSave::class,
        'auth' => true,
        'schema' => $labelSetSchemaFactory->makeSaveRequest(),
        'responses' => array_merge([
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => $labelSetSchemaFactory->make()
            ],
            'bad-request' => [
                'code' => 400,
                'description' => 'Bad request',
                'schema' => $errorSchema
            ]
        ], $errorResponses)
    ]
];

$rest['v1/label-sets'] = [
    'GET' => [
        'tag' => 'label-sets',
        'description' => 'Label set list',
        'commandClass' => LabelSetList::class,
        'auth' => true,
        'params' => [
            'languages' => ['type' => 'string'],
            'updatable' => ['type' => 'string'],
        ],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => $labelSetSchemaFactory->makeList()
            ],
            'unauthorized' => $errorResponses['unauthorized']
        ]
    ],
    'POST' => [
        'tag' => 'label-sets',
        'description' => 'Create a label set',
        'commandClass' => LabelSetSave::class,
        'auth' => true,
        'schema' => $labelSetSchemaFactory->makeSaveRequest(),
        'responses' => array_merge([
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => $labelSetSchemaFactory->make()
            ],
            'bad-request' => [
                'code' => 400,
                'description' => 'Bad request',
                'schema' => $errorSchema
            ]
        ], $errorResponses)
    ]
];

return $rest;
