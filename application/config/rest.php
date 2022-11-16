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

////////////////////////////////////////////////////////////////////////
// GET rest/v1/survey/$id
// POST rest/v1/survey/$id
$v1Namespace = '\LimeSurvey\Api\Command\V1\\';
$rest['v1/survey'] = [
    'GET' => [
        [
            'byId' => 'surveyID',
            'commandClass' => $v1Namespace . 'SurveyPropertiesGet',
            'auth' => 'session',
            'params' => [
                'surveySettings' => true
            ],
            'bodyParams' => []
        ]
    ],
    'POST' => [
        [
            'byId' => 'surveyID',
            'commandClass' => $v1Namespace . 'SurveyAdd',
            'auth' => 'session',
            'params' => [],
            'bodyParams' => [
                'surveyTitle' => true,
                'surveyLanguage' => true,
                'format' => true
            ]
        ]
    ]
];
$rest['v1/session'] = [
    'POST' => [
        [
            'commandClass' => $v1Namespace . 'SessionKeyCreate',
            'params' => [
                'username' => true,
                'password' => true
            ],
            'bodyParams' => []
        ]
    ]
];





//DO NOT CHANGE BELOW HERE --------------------

return array('rest' => $rest);
