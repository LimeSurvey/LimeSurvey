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
//require_once('classes/core/settingsstorage.php');
$CI =& get_instance();
$CI->load->library("admin/settingsstorage","settingsstorage");

//Ensure script is not run directly, avoid path disclosure
//if (!isset($homedir) || isset($_REQUEST['$homedir'])) {die("Cannot run this script directly");}
injectglobalsettings();


function injectglobalsettings()
{
    //global $connect;
	$CI =& get_instance();
	
    //$registry = SettingsStorage::getInstance();
	$registry = $CI->settingsstorage->getInstance();
	
	$CI->load->model("settings_global_model");
	
    //$usquery = "SELECT * FROM ".db_table_name("settings_global");
    //$dbvaluearray=$connect->GetAll($usquery);
	
	$query = $CI->settings_global_model->getAllRecords();
	
    //if ($dbvaluearray!==false)
    if($query->num_rows() > 0)
    {
        //foreach  ($dbvaluearray as $setting)
        foreach ($query->result_array() as $setting)
        {
            //global $$setting['stg_name'];
            if (isset($CI->config->config[$setting['stg_name']]))
            {
                //$$setting['stg_name']=$setting['stg_value'];
                $CI->config->set_item($setting['stg_name'], $setting['stg_value']);
            }
            
            $registry->set($setting['stg_name'],$setting['stg_value']);
        }
    }
}

function getGlobalSetting($settingname)
{
    //global $connect, $$settingname;
	$CI =& get_instance();
    //$registry = SettingsStorage::getInstance();
	
	$registry = $CI->settingsstorage->getInstance();
	$CI->load->model("settings_global_model");

    if (!$registry->isRegistered($settingname)) {
        //$usquery = "SELECT stg_value FfROM ".db_table_name("settings_global")." where stg_name='$settingname'";
		$query = $CI->settings_global_model->getSomeRecords(array("stg_value"),array("stg_name" => $settingname));
        //$dbvalue=$connect->GetOne($usquery);
        $dbvalue = $query->row_array();
		$dbvalue = $dbvalue['stg_value'];
		//var_dump($dbvalue);
        if (is_null($dbvalue))
        {
            $registry->set($settingname,$dbvalue);
        } elseif (isset($CI->config->config[$settingname])) {
            // If the setting was not found in the setting table but exists as a variable (from config.php)
            // get it and save it to the table
            setGlobalSetting($settingname,$CI->config->item($settingname));
            $dbvalue=$CI->config->item($settingname);
        }
    } else {
        $dbvalue=$registry->get($settingname);
    }

    return $dbvalue;
}

function setGlobalSetting($settingname, $settingvalue)
{
    $CI =& get_instance();
    if ($CI->config->item("demoModeOnly")==true && ($settingname=='sitename' || $settingname=='defaultlang' || $settingname=='defaulthtmleditormode' || $settingname=='filterxsshtml'))
    {
        return; //don't save
    }

	$CI->load->model("settings_global_model");
	$CI->settings_global_model->updateSetting($settingname, $settingvalue);
	
    $registry = $CI->settingsstorage->getInstance();
    $registry->set($settingname,$settingvalue);
    if (isset($CI->config->config[$settingname])) $CI->config->set_item($settingname, $settingvalue);
}

?>
