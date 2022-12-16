<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// REST V1 Config
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [
    'v1' => [
        'title' => 'LimeSurvey V1',
        'description' => 'LimeSurvey V1 REST API',
        'tags' => [
            'survey' => [
                'name' => 'Survey',
                'description' => 'Survey',
            ],
            'questionGroup' => [
                'name' => 'Question Group',
                'description' => 'Question Group',
            ],
            'question' => [
                'name' => 'Question',
                'description' => 'Question',
            ],
            'session' => [
                'name' => 'Session',
                'description' => 'Session',
            ],
            'site-settings' => [
                'name' => 'Site Settings',
                'description' => 'Site Settings',
            ]

        ]
    ]
];

return array_merge(
    $rest,
    include __DIR__ . '/v1/survey.php',
    include __DIR__ . '/v1/question-group.php',
    include __DIR__ . '/v1/question.php',
    include __DIR__ . '/v1/session.php',
    include __DIR__ . '/v1/site-settings.php'
);
