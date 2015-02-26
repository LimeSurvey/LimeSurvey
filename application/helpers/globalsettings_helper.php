<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

function getGlobalSetting($settingname)
{
    $dbvalue = Yii::app()->getConfig($settingname);

    if ($dbvalue === false)
    {
    	$dbvalue = SettingGlobal::model()->findByPk($settingname);

        if ($dbvalue === null)
        {
            Yii::app()->setConfig($settingname, null);
			$dbvalue = '';
        }
        else
        {
            $dbvalue = $dbvalue->getAttribute('stg_value');
        }

		if (Yii::app()->getConfig($settingname) !== false)
		{
            // If the setting was not found in the setting table but exists as a variable (from config.php)
            // get it and save it to the table
            setGlobalSetting($settingname, Yii::app()->getConfig($settingname));
            $dbvalue = Yii::app()->getConfig($settingname);
        }
    }

    return $dbvalue;
}

function setGlobalSetting($settingname, $settingvalue)
{
    if (Yii::app()->getConfig("demoMode")==true && ($settingname=='sitename' || $settingname=='defaultlang' || $settingname=='defaulthtmleditormode' || $settingname=='filterxsshtml'))
    {
        return; //don't save
    }

    SettingGlobal::set($settingname, $settingvalue);
    App()->setConfig($settingname, $settingvalue);
}

?>
