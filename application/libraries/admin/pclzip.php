<?php
/*
 * LimeSurvey
 * Copyright (C) 2007-2014 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

    // Set the correct temp path for PclZip
if (!defined('PCLZIP_TEMPORARY_DIR')) {
    define('PCLZIP_TEMPORARY_DIR', Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR);
    }

# include PclZip class
require_once(APPPATH.'/third_party/pclzip/pclzip.lib.php');
