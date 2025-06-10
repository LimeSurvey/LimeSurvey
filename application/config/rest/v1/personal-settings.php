<?php

use LimeSurvey\Api\Command\V1\{
    PersonalSettings,
    PersonalSettingsPatch
};

use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryError,
    SchemaFactoryPersonalSettings,
    SchemaFactoryPersonalSettingsPatch};

$errorSchema = (new SchemaFactoryError())->make();
$personalSettingsPatchSchema = (new SchemaFactoryPersonalSettingsPatch)->make();

$rest = [];

$rest['v1/personal-settings/$id'] = [
    'GET' => [
        'description' => 'Personal Settings',
        'commandClass' => PersonalSettings::class,
        'auth' => true,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactoryPersonalSettings)->make()
            ]
        ]
    ],
    'PATCH' => [
        'description' => 'Update personal settings',
        'commandClass' => PersonalSettingsPatch::class,
        'auth' => true,
        'example' => __DIR__. '/example/personal-settings-patch.json',
        'schema' => $personalSettingsPatchSchema,
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

return $rest;