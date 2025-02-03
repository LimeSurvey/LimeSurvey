<?php

use LimeSurvey\Api\Command\V1\I18n;
use LimeSurvey\Libraries\Api\Command\V1\I18nMissing;
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactoryI18nMissingTranslations,
    SchemaFactoryI18nMissingTranslationsResponse,
    SchemaFactoryI18nTranslations};

$errorSchema = (new SchemaFactoryError())->make();
$i18nTranslationsSchema = (new SchemaFactoryI18nTranslations())->make();
$i18nMissingSchemaResponse = (new SchemaFactoryI18nMissingTranslationsResponse())->make();
$i18nMissingSchema = (new SchemaFactoryI18nMissingTranslations())->make();

$rest = [];

$rest['v1/i18n/{lang}'] = [
    'GET' => [
        'tag' => 'i18n',
        'summary' => 'Get translations',
        'description' => 'Get translations for a specific language',
        'operationId' => 'getTranslations',
        'commandClass' => I18n::class,
        'auth' => true,
        'parameters' => [
            [
                'name' => 'lang',
                'in' => 'path',
                'required' => true,
                'schema' => [
                    'type' => 'string'
                ],
                'description' => 'Language code'
            ]
        ],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => $i18nTranslationsSchema
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

$rest['v1/i18n-missing'] = [
    'POST' => [
        'tag' => 'i18n',
        'summary' => 'Save missing translations',
        'description' => 'Save missing translations for a specific language',
        'commandClass' => I18nMissing::class,
        'auth' => true,
        'schema' => $i18nMissingSchema,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => $i18nMissingSchemaResponse
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized',
                'schema' => $errorSchema
            ],
            'error' => [
                'code' => 404,
                'description' => 'Not Found',
                'schema' => $errorSchema
            ]
        ]
    ],
];

return $rest;
