<?php

if (!defined('BASEPATH')) {
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
//Ensure script is not run directly, avoid path disclosure
//if (!isset($homedir) || isset($_REQUEST['$homedir'])) {die("Cannot run this script directly");}
/**
 * Returns a global setting
 * @deprecated : use App()->getConfig('settingname')
 * since all config are set at start of App : no need to read and test again
 *
 * @param string $settingname
 * @return string
 */
function getGlobalSetting($settingname)
{
    return App()->getConfig($settingname);
}
