<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
$route= array();

$route['<_sid:\d+>/lang-<_lang:\w+[-\w]+>/tk-<_token:\w+>/*'] = "survey/index/sid/<_sid>/lang/<_lang>/token/<_token>"; //This one must be first
$route['<_sid:\d+>/lang-<_lang:\w+[-\w]+>/*'] = "survey/index/sid/<_sid>/lang/<_lang>";
$route['<_sid:\d+>/tk-<_token:\w+>/*'] = "survey/index/sid/<_sid>/token/<_token>";
$route['<_sid:\d+>/*'] = "survey/index/sid/<_sid>";


//question
$route['admin/labels/'] = "admin/labels/sa/view";
$route['admin/labels/sa/<_action:\w+>/<_lid:\d+>'] = "admin/labels/sa/<_action>/lid/<_lid>";

//Admin Routes
$route['admin/<action:\w+>/<sa:\w+>/*'] = 'admin/<action>/sa/<sa>';

//optout
$route['optout/<_sid:\d+>/(:any)/(:any)'] = "optout/index/<_sid>/$2/$3";

return $route;
