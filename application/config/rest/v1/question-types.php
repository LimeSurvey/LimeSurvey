<?php

use LimeSurvey\Api\Command\V1\QuestionTypeList;
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactoryQuestionTypes
};

$errorSchema = (new SchemaFactoryError())->make();

$rest = [];

$rest['v1/questionTypes'] = [
    'GET' => [
        'tag' => 'question-types',
        'description' => 'Question types of the editor, with the categories of the question type selector',
        'commandClass' => QuestionTypeList::class,
        'auth' => true,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactoryQuestionTypes())->make()
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
