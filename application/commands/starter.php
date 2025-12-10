#!/usr/bin/php
<?php
    /*
    * LimeSurvey (tm)
    * Copyright (C) 2011-2026 The LimeSurvey Project Team
    * All rights reserved.
    * License: GNU/GPL License v2 or later, see LICENSE.php
    * LimeSurvey is free software. This version may have been modified pursuant
    * to the GNU General Public License, and as distributed it includes or
    * is derivative of works licensed under the GNU General Public License or
    * other free or open source software licenses.
    * See COPYRIGHT.php for copyright notices and details.
    *
    */
if (!isset($argv[0])) {
    die();
}
    define('BASEPATH', '.');
    $sCurrentDir = dirname(__FILE__);
    $config = require(dirname($sCurrentDir) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
    unset($config['defaultController']);
    unset($config['config']);
    require(dirname(dirname($sCurrentDir)) . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'yiic.php');

?>
