<?php

use LimeSurvey\Api\Command\V1\{
    SiteSettings
};

use LimeSurvey\Api\Rest\V1\SchemaFactory\SchemaFactorySiteSettings;

$rest = [];

$rest['v1/site-settings'] = [
    'GET' => [
        'description' => 'Site Settings',
        'commandClass' => SiteSettings::class,
        'params' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactorySiteSettings)->make()
            ]
        ]
    ]
];

return $rest;
