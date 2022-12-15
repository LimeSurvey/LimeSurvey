<?php

$rest = [
    'v1' => [
        'title' => 'LimeSurvey v1 REST API',
        'description' => 'LimeSurvey v1 REST API',
        'entity' => [
            'survey' => [
                'name' => 'Survey',
                'description' => 'Survey',
            ],
            'session' => [
                'name' => 'Question Group',
                'description' => 'Question Group',
            ],
            'session' => [
                'name' => 'Question',
                'description' => 'Question',
            ],
            'session' => [
                'name' => 'Session',
                'description' => 'Session',
            ],
            'session' => [
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
