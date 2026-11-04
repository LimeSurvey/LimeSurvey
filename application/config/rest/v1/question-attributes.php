<?php

use LimeSurvey\Api\Command\V1\QuestionAttributesDetail;
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactoryQuestionThemeAttributes
};

$errorSchema = (new SchemaFactoryError())->make();

$rest = [];

$rest['v1/questionThemeAttribute'] = [
    'GET' => [
        'tag' => 'question-attributes',
        'description' => 'Editor attribute definitions of all question themes',
        'commandClass' => QuestionAttributesDetail::class,
        'auth' => true,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactoryQuestionThemeAttributes())->make()
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
