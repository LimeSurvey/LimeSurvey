<?php

/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

$config_folder = dirname(__FILE__) . '/../application/config/';
$config_file = $config_folder . 'config.php';
if (!file_exists($config_file)) {
    $config_file = $config_folder . 'config-sample-mysql.php';
}
define('BASEPATH', dirname(__FILE__) . '/..'); // To prevent direct access not allowed
$config = require($config_file);

$urlStyle = $config['components']['urlManager']['urlFormat'];

// Simple redirect to still have the old /admin URL
if ($urlStyle == 'path') {
    header('Location: ../index.php/admin');
} else {
    // For IIS use get style
    header('Location: ../index.php?r=admin');
}
