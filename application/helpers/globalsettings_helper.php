<?php if (!defined('BASEPATH')) {
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
injectglobalsettings();


function injectglobalsettings()
{
    $settings = SettingGlobal::model()->findAll();

    //if ($dbvaluearray!==false)
    if (count($settings) > 0) {
        foreach ($settings as $setting) {
            Yii::app()->setConfig($setting->getAttribute('stg_name'), $setting->getAttribute('stg_value'));
        }
    }
}
/**
 * Returns a global setting
 * @deprecated : use App()->getConfig($settingname)
 * since all config are set at start of App : no need to read and test again 
 *
 * @param string $settingname
 * @return string
 */
function getGlobalSetting($settingname)
{
    $dbvalue = Yii::app()->getConfig($settingname);

    if ($dbvalue === false) {
        $dbvalue = SettingGlobal::model()->findByPk($settingname);

        if ($dbvalue === null) {
            Yii::app()->setConfig($settingname, null);
            $dbvalue = '';
        } else {
            $dbvalue = $dbvalue->getAttribute('stg_value');
        }

        if (Yii::app()->getConfig($settingname) !== false) {
            // If the setting was not found in the setting table but exists as a variable (from config.php)
            // get it and save it to the table
            SettingGlobal::setSetting($settingname, Yii::app()->getConfig($settingname));
            $dbvalue = Yii::app()->getConfig($settingname);
        }
    }

    return $dbvalue;
}
