<?php  if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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


//Compatibility with classic modrewrite
$route['<_sid:\d+>/lang-<_lang:\w+[-\w]+>/tk-<_token:\w+>/*'] = "survey/index/sid/<_sid>/lang/<_lang>/token/<_token>"; //This one must be first
$route['<_sid:\d+>/lang-<_lang:\w+[-\w]+>/*'] = "survey/index/sid/<_sid>/lang/<_lang>";
$route['<_sid:\d+>/tk-<_token:\w+>/*'] = "survey/index/sid/<_sid>/token/<_token>";
$route['<_sid:\d+>/*'] = "survey/index/sid/<_sid>";
$route['<sid:\d+>'] = array('survey/index', 'matchValue'=>true);

//Admin Routes
$route['admin/index'] = "admin"; // this can not be changed to "dashboard/view" as long as the AdminController exists, will break "path" urlFormat
$route['admin/<action:\w+>/sa/<sa:\w+>/*'] = 'admin/<action>/sa/<sa>';
$route['admin/<action:\w+>/<sa:\w+>/*'] = 'admin/<action>/sa/<sa>';

//question
$route['admin/labels/<_action:\w+>'] = "admin/labels/index/<_action>";
$route['admin/labels/<_action:\w+>/<_lid:\d+>'] = "admin/labels/index/<_action>/<_lid>";

//ExpressionScript Engine tests
$route['admin/expressions'] = "admin/expressions/index";

// REST
// - Resource index
// - Resource index with id
// - Resource index with id and basetable (for survey responses)
$route['rest/<_api_version:\w+>/<_entity>/<_id>'] = array(
    'rest',
    'verb' => 'GET, PUT, PATCH, POST, DELETE',
    'matchValue' => false
);
$route['rest/<_api_version:\w+>/<_entity>'] = array(
    'rest',
    'verb' => 'GET, PUT, PATCH, POST, DELETE',
    'matchValue' => false
);

$route['rest/<_api_version:\w+>/<_entity>/id/<_id>/basetable/<_basetable>'] = array(
    'rest',
    'verb' => 'GET, PUT, PATCH, POST, DELETE',
    'matchValue' => false
);

//optout - optin
$route['optout/<_sid:\d+>/(:any)/(:any)'] = "optout/index/<_sid>/$2/$3";
$route['optout/tokens/<surveyid:\d+>'] = array('optout/tokens', 'matchValue'=>true);
$route['optout/participants/<surveyid:\d+>'] = array('optout/participants', 'matchValue'=>true);
$route['optin/tokens/<surveyid:\d+>'] = array('optin/tokens', 'matchValue'=>true);
$route['optin/participants/<surveyid:\d+>'] = array('optin/participants', 'matchValue'=>true);
$route['statistics_user/<surveyid:\d+>'] = array('StatisticsUser/action', 'matchValue'=>true);
$route['statistics_user/action'] = 'StatisticsUser/action';

//$route['<_controller:\w+>/<_action:\w+>'] = '<_controller>/<_action>';

return $route;
