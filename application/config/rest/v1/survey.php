<?php

use LimeSurvey\Api\Command\V1\{
    SurveyList,
    SurveyDetail,
    SurveyPatch,
    SurveyTemplate,
    SurveyArchive
};
use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactorySurveyList,
    SchemaFactorySurveyDetail,
    SchemaFactorySurveyPatch,
    SchemaFactorySurveyTemplate,
    SchemaFactorySurveyArchive
};

$errorSchema = (new SchemaFactoryError())->make();
$surveyPatchSchema = (new SchemaFactorySurveyPatch())->make();
$surveyTemplateSchema = (new SchemaFactorySurveyTemplate())->make();

$rest = [];

$rest['v1/survey'] = [
    'GET' => [
        'description' => 'Survey list',
        'commandClass' => SurveyList::class,
        'auth' => true,
        'params' => [
            'pageSize' => ['type' => 'int'],
            'page' => ['type' => 'int']
        ],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveyList())->make()
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized',
                'schema' => $errorSchema
            ]
        ]
    ]
];

$rest['v1/survey-detail/$id'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Survey detail',
        'commandClass' => SurveyDetail::class,
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
    'PATCH' => [
        'tag' => 'survey',
        'description' => 'Survey update via RFC 6902 based patch',
        'commandClass' => SurveyPatch::class,
        'auth' => true,
        'example' => __DIR__ . '/example/survey-patch-all.json',
        'schema' => $surveyPatchSchema,
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
    ]
];

$rest['v1/survey-template/$id'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Survey template',
        'commandClass' => SurveyTemplate::class,
        'auth' => Yii::app()->getConfig('authTemplates', false),
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => $surveyTemplateSchema
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Not Found',
                'schema' => $errorSchema
            ]
        ]
    ],
    'POST' => [
        'tag' => 'survey',
        'description' => 'Survey template',
        'commandClass' => SurveyTemplate::class,
        'auth' => Yii::app()->getConfig('authTemplates', false),
        'example' => __DIR__ . '/example/survey-post-template.json',
        'schema' => $surveyTemplateSchema,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => $surveyTemplateSchema
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Not Found',
                'schema' => $errorSchema
            ]
        ]
    ],
];

$rest['v1/survey-archives/$id'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Survey archives',
        'commandClass' => SurveyArchive::class,
        'auth' => true,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveyArchive())->make()
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Not Found',
                'schema' => $errorSchema
            ]
        ]
    ]
];

$rest['v1/survey-archives/$id/$basetable'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Survey archives',
        'commandClass' => SurveyArchive::class,
        'auth' => true,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveyArchive())->make()
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Not Found',
                'schema' => $errorSchema
            ]
        ]
    ]
];

return $rest;
