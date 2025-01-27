<?php

$rest = [
    'v1' => [
        'title' => 'LimeSurvey V1',
        'description' => 'LimeSurvey V1 REST API',
        'tags' => [
            'survey' => [
                'name' => 'Survey',
                'description' => 'Survey',
            ],
            'auth' => [
                'name' => 'Auth',
                'description' => 'Auth',
            ],
            'session' => [
                'name' => 'Session',
                'description' => 'Session (deprecated - use auth)',
            ],
            'survey-group' => [
                'name' => 'Survey Group',
                'description' => 'Survey Group',
            ],
            'site-settings' => [
                'name' => 'Site Settings',
                'description' => 'Site Settings',
            ],
            'user' => [
                'name' => 'User',
                'description' => 'User',
            ],
            'upload' => [
                'name' => 'File Upload',
                'description' => 'File Upload',
            ],
        ]
    ]
];

return array_merge(
    $rest,
    include_once __DIR__ . '/v1/survey.php',
    include_once __DIR__ . '/v1/auth.php',
    include_once __DIR__ . '/v1/survey-group.php',
    include_once __DIR__ . '/v1/user.php',
    include_once __DIR__ . '/v1/site-settings.php',
    include_once __DIR__ . '/v1/i18n.php',
    include_once __DIR__ . '/v1/file-upload.php',
);
