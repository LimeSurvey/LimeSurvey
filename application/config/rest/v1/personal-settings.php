<?php

use LimeSurvey\Api\Command\V1\{
    PersonalSettings
};

use LimeSurvey\Api\Rest\V1\SchemaFactory\SchemaFactoryPersonalSettings;

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
    ]
];

return $rest;