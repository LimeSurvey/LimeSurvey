<?php

use LimeSurvey\Libraries\Api\Rest\V1\SchemaFactory\SchemaFactoryVersionInfo;
use LimeSurvey\Api\Command\V1\{
    VersionInfo
};


$rest = [];

$rest['v1/version-info'] = [
    'GET' => [
        'description' => 'Version Info',
        'commandClass' => VersionInfo::class,
        'params' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
                'content' => null,
                'schema' => (new SchemaFactoryVersionInfo())->make()
            ]
        ]
    ]
];

return $rest;
