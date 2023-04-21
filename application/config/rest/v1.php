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
            'session' => [
                'name' => 'Session',
                'description' => 'Session',
            ]
        ]
    ]
];

return array_merge(
    $rest,
    include __DIR__ . '/v1/survey.php',
    include __DIR__ . '/v1/session.php'
);
