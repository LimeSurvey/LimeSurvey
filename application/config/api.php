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


$api = [];

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Survey
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
// GET rest/v1/survey/$id
$api[] = [
    'version' => 'v1',
    'entity' => 'survey',
    'method' => 'GET',
    'byId' => 'surveyID',
    'commandClass' => '\LimeSurvey\Api\Command\V1\SurveyPropertiesGet',
    'auth' => 'session',
    'queryParams' => [
        'surveySettings' => ['required' => false, 'default' => null]
    ],
    'bodyParams' => []
];

////////////////////////////////////////////////////////////////////////
// POST rest/v1/survey/$id
$api[] = [
    'version' => 'v1',
    'entity' => 'survey',
    'method' => 'POST',
    'byId' => 'surveyID',
    'commandClass' => '\LimeSurvey\Api\Command\V1\SurveyAdd',
    'auth' => 'session',
    'queryParams' => [],
    'bodyParams' => [
        'surveyTitle' => true,
        'surveyLanguage' => true,
        'format' => true
    ]
];



//DO NOT CHANGE BELOW HERE --------------------

return array('api' => $api);
