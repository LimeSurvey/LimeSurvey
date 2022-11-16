<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Question Group
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V1\\';

////////////////////////////////////////////////////////////////////////
// POST rest/v1/questionGroup
// GET rest/v1/questionGroup
$rest['v1/questionGroup'] = [
    'POST' => [
        'commandClass' => $v1Namespace . 'QuestionGroupAdd',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [
            'surveyID' => true,
            'groupTitle' => true,
            'groupDescription' => true
        ]
    ],
    'GET' => [
        'commandClass' => $v1Namespace . 'QuestionGroupList',
        'auth' => 'session',
        'params' => [
            'groupSettings' => true,
            'language' => true
        ],
        'bodyParams' => []
    ]
];

// GET rest/v1/questionGroup/{id}
// PUT rest/v1/questionGroup/{id}
// DELETE rest/v1/questionGroup/{id}
$rest['v1/questionGroup/$groupID'] = [
    'GET' => [
        'commandClass' => $v1Namespace . 'QuestionGroupPropertiesGet',
        'auth' => 'session',
        'params' => [
            'language' => true
        ],
        'bodyParams' => []
    ],
    'PUT' => [
        'commandClass' => $v1Namespace . 'QuestionGroupPropertiesSet',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [
            'group_name' => true,
            'description' => true,
            'language' => true,
            'questiongroupl10ns' => true,
            'group_order' => true,
            'randomization_group' => true,
            'grelevance' => true
        ]
    ],
    'DELETE' => [
        'commandClass' => $v1Namespace . 'QuestionGroupDelete',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ]
];

return $rest;
