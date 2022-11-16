<?php
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */


$rest = [];

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Survey
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$v1Namespace = '\LimeSurvey\Api\Command\V1\\';

////////////////////////////////////////////////////////////////////////
// GET rest/v1/survey/$surveyID
// POST rest/v1/survey/$surveyID

$rest['v1/survey/$surveyID'] = [
    'GET' => [
        'commandClass' => $v1Namespace . 'SurveyPropertiesGet',
        'auth' => 'session',
        'params' => [
            'surveySettings' => true
        ],
        'bodyParams' => []
    ],
    'POST' => [
        'byId' => 'surveyID',
        'commandClass' => $v1Namespace . 'SurveyAdd',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [
            'surveyTitle' => true,
            'surveyLanguage' => true,
            'format' => true
        ]
    ],
];
// DELETE rest/v1/survey
$rest['v1/survey'] = [
    'DELETE' => [
        'commandClass' => $v1Namespace . 'SurveyDelete',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ]
];


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

////////////////////////////////////////////////////////////////////////
// POST rest/v1/question
// GET rest/v1/question
$rest['v1/question'] = [
    'POST' => [
        'commandClass' => $v1Namespace . 'QuestionImport',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [
            'surveyID' => true,
            'groupID' => true,
            'importData' => true,
            'importDataType' => true,
            'newQuestionTitle' => true,
            'newQuestion' => true,
            'newQuestionHelp' => true
        ]
    ],
    'GET' => [
        'commandClass' => $v1Namespace . 'QuestionGroupList',
        'auth' => 'session',
        'params' => [
            'surveyID' => true,
            'groupID' => true,
            'language' => true,
        ],
        'bodyParams' => []
    ]
];

$rest['v1/question/$questionID'] = [
    'GET' => [
        [
            'commandClass' => $v1Namespace . 'QuestionPropertiesGet',
            'auth' => 'session',
            'params' => [
                'questionSettings' => true,
                'language' => true
            ],
            'bodyParams' => []
        ]
    ],
    'PUT' => [
        [
            'commandClass' => $v1Namespace . 'QuestionPropertiesSet',
            'auth' => 'session',
            'params' => [],
            'bodyParams' => [
                'language' => true,
                'questionData' => true
            ]
        ]
    ],
    'DELETE' => [
        [
            'commandClass' => $v1Namespace . 'QuestionDelete',
            'auth' => 'session',
            'params' => [],
            'bodyParams' => []
        ]
    ]
];

////////////////////////////////////////////////////////////////////////
// POST rest/v1/session
// DELETE rest/v1/session
$rest['v1/session'] = [
    'POST' => [
        'commandClass' => $v1Namespace . 'SessionKeyCreate',
        'params' => [
            'username' => true,
            'password' => true
        ],
        'bodyParams' => []
    ],
    'DELETE' => [
        'commandClass' => $v1Namespace . 'SessionKeyRelease',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ]
];


////////////////////////////////////////////////////////////////////////
// POST rest/v1/session
// DELETE rest/v1/session
$rest['v1/siteSettings/$settingName'] = [
    'GET' => [
        'commandClass' => $v1Namespace . 'SiteSettingsGet',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ]
];

//DO NOT CHANGE BELOW HERE --------------------

return array('rest' => $rest);
