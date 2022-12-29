<?php

use \LimeSurvey\Api\Command\V1\SiteSettingsGet;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Site Settings
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];

$rest['v1/siteSettings/$id'] = [
    'GET' => [
        'commandClass' => SiteSettingsGet::class,
        'auth' => 'session',
        'params' => [],
        'content' => null,
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success'
            ],
            'unauthorized' => [
                'code' => 401,
                'description' => 'Unauthorized'
            ],
            'not-found' => [
                'code' => 404,
                'description' => 'Not Found'
            ]
        ]
    ]
];

return $rest;
