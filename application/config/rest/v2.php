<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// REST V2 Config
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [
    'v2' => [
        'title' => 'LimeSurvey v2 REST API',
        'description' => 'LimeSurvey v2 REST API',
        'entity' => [
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
    include __DIR__ . '/v2/survey.php',
    include __DIR__ . '/v2/session.php'
);
