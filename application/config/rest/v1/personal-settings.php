<?php

use LimeSurvey\Api\Command\V1\{
    PersonalSettings,
    PersonalSettingsPatch
};

use LimeSurvey\Api\Rest\V1\SchemaFactory\{
    SchemaFactoryPersonalSettings,
    SchemaFactoryPersonalSettingsPatch
};

$rest = [];

$rest['v1/personal-settings'] = [
    'GET' => [
        'description' => 'Personal Settings',
        'commandClass' => PersonalSettings::class,
        'params' => [],
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
        'params' => [],
        'example' => __DIR__. '/example/personal-settings-patch.json',
        'content' => [
            'application/json' => [
                'schema' => (new SchemaFactoryPersonalSettingsPatch)->make()
            ]
        ],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null
            ]
        ]
    ]
];

return $rest;
