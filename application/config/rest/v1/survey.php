<?php

use LimeSurvey\Api\Command\V1\{
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

$rest['v1/survey-detail/$id'] =
$rest['v1/survey-detail/$id/ts/$ts'] = [
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
        'auth' => true,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveyTemplate())->make()
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
        'auth' => true,
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

$rest['v1/survey-logic/$id'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Survey logic overview',
        'commandClass' => SurveyLogic::class,
        'auth' => true,
        'params' => [
            'gid' => ['type' => 'integer'],
            'qid' => ['type' => 'integer'],
            'lang' => ['type' => 'string'],
            'assessments' => ['type' => 'string'],
        ],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveyLogic())->make()
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
        ]
    ]
];

$rest['v1/survey-questions-fieldname/$id'] = [
    'GET' => [
        'tag' => 'survey',
        'description' => 'Survey questions fieldname',
        'commandClass' => SurveyQuestionsFieldname::class,
        'auth' => true,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySurveyQuestionsFieldname())->make()
            ]
        ]
    ]
];

return $rest;
