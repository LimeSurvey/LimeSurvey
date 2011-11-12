<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey (tm)
 * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id$
 */

//Ensure script is not run directly, avoid path disclosure
//if (!isset($homedir) || isset($_REQUEST['$homedir'])) {die("Cannot run this script directly");}
injectglobalsettings();


function injectglobalsettings()
{
    //$usquery = "SELECT * FROM ".db_table_name("settings_global");
    //$dbvaluearray=$connect->GetAll($usquery);

	$settings = Settings_global::model()->findAll();

    //if ($dbvaluearray!==false)
    if (count($settings) > 0)
    {
        //foreach  ($dbvaluearray as $setting)
        foreach ($settings as $setting)
        {
            if (Yii::app()->getConfig($setting->getAttribute('stg_name')) !== false)
            {
                //$$setting['stg_name']=$setting['stg_value'];
                Yii::app()->setConfig($setting->getAttribute('stg_name'), $setting->getAttribute('stg_value'));
            }

            Yii::app()->setRegistry($setting->getAttribute('stg_name'), $setting->getAttribute('stg_value'));
        }
    }
}

function getGlobalSetting($settingname)
{
    if (Yii::app()->getRegistry($settingname) === false) {
        //$usquery = "SELECT stg_value FfROM ".db_table_name("settings_global")." where stg_name='$settingname'";

    	$dbvalue = Settings_global::model()->findByPk($settingname);
		//$dbvalue = $dbvalue['stg_value'];
		//var_dump($dbvalue);

        if (empty($dbvalue))
        {
            Yii::app()->setRegistry($settingname, null);
			$dbvalue="";
        }
    	else
    		$dbvalue = $dbvalue->getAttribute('stg_value');

		if (Yii::app()->getConfig($settingname) !== false)
		{
            // If the setting was not found in the setting table but exists as a variable (from config.php)
            // get it and save it to the table
            setGlobalSetting($settingname, Yii::app()->getConfig($settingname));
            $dbvalue = Yii::app()->getConfig($settingname);
        }
    }
	else
        $dbvalue = Yii::app()->getRegistry($settingname);

    return $dbvalue;
}

function setGlobalSetting($settingname, $settingvalue)
{
    if (Yii::app()->getConfig("demoMode")==true && ($settingname=='sitename' || $settingname=='defaultlang' || $settingname=='defaulthtmleditormode' || $settingname=='filterxsshtml'))
    {
        return; //don't save
    }

	if ($record = Settings_global::model()->findByPk($settingname))
	{
		$record->stg_value = $settingvalue;
		$record->save();
	}
	else
	{
		$record = new Settings_global;
		$record->stg_name = $settingname;
		$record->stg_value = $settingvalue;
		$record->save();
	}

    Yii::app()->setRegistry($settingname,$settingvalue);

    Yii::app()->setConfig($settingname, $settingvalue);
}

?>
