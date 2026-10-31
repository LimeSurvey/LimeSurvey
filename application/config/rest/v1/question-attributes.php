<?php

use LimeSurvey\Api\Command\V1\{
    QuestionAttributesDetail,
    SurveyList,
    SurveyDetail,
    SurveyPatch,
    SurveyTemplate,
    SurveyArchive,
    SurveyLogic,
    SurveyQuestionsFieldname
};
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactoryQuestionAttribute,
    SchemaFactorySurveyList,
    SchemaFactorySurveyDetail,
    SchemaFactorySurveyPatch,
    SchemaFactorySurveyTemplate,
    SchemaFactorySurveyArchive,
    SchemaFactorySurveyLogic,
    SchemaFactorySurveyQuestionsFieldname
};

$errorSchema = (new SchemaFactoryError())->make();
$surveyPatchSchema = (new SchemaFactorySurveyPatch())->make();
$surveyTemplateSchema = (new SchemaFactorySurveyTemplate())->make();

$rest = [];

$rest['v1/questionThemeAttribute'] = [
    'GET' => [
        'description' => 'Question attributes detail',
        'commandClass' => QuestionAttributesDetail::class,
        'auth' => true,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactoryQuestionAttribute())->make()
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
