<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Site Settings
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V1\\';


$rest['v1/siteSettings/$settingName'] = [
    'GET' => [
        'commandClass' => $v1Namespace . 'SiteSettingsGet',
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
