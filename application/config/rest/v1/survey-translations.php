<?php

use LimeSurvey\Api\Command\V1\SurveyTranslations;
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactorySurveyDetail,
    SchemaFactorySurveyPatch,
    SchemaFactorySurveyTemplate
};

$errorSchema = (new SchemaFactoryError())->make();
$surveyPatchSchema = (new SchemaFactorySurveyPatch())->make();
$surveyTemplateSchema = (new SchemaFactorySurveyTemplate())->make();

$rest = [];

$rest['v1/survey-translations/$id'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Survey translations',
        'commandClass' => SurveyTranslations::class,
        'auth' => true,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveyDetail())->make()
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
];

return $rest;
