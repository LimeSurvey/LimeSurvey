<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id: update.php 10323 2011-06-22 15:33:54Z dionet $
 *
 */

/**
 * This functions checks if the databaseversion in the settings table is the same one as required
 * If not then the necessary upgrade procedures are run
 */
function CheckForDBUpgrades($subaction = null)
{
    $connect = Yii::app()->db;
	$clang = Yii::app()->getController()->lang;
	$dbversionnumber = Yii::app()->getConfig('dbversionnumber');
    $currentDBVersion=GetGlobalSetting('DBVersion');
	$dbprefix = Yii::app()->tablePrefix;
	$usertemplaterootdir = Yii::app()->getConfig('usertemplaterootdir');
	$standardtemplaterootdir = Yii::app()->getConfig('standardtemplaterootdir');
    if (intval($dbversionnumber)>intval($currentDBVersion))
    {
        if(isset($subaction) && $subaction=="continue")
        {
        	require_once(APPPATH.'third_party/adodb/adodb.inc.php');
			connectadodb();
            echo "<div style='width:90%; padding:1% 10%;background-color:#eee;'>";
            $upgradedbtype=$CI->db->dbdriver;
            if ($upgradedbtype=='mysqli') $upgradedbtype='mysql';
            $CI->load->helper('update/upgrade-'.$upgradedbtype);
            $CI->load->helper('update/upgrade-all');
            $tables = $connect->getSchema()->getTableNames();
            db_upgrade_all(intval($currentDBVersion));
            db_upgrade(intval($currentDBVersion));
            $CI->db->update('settings_global',array('stg_value' => intval($dbversionnumber)),array('stg_name' => 'DBVersion'));
            echo "<br />".sprintf($clang->gT("Database has been successfully upgraded to version %s"),$dbversionnumber);
			echo "<br /><a href='".site_url("admin")."'>".$clang->gT("Back to main menu")."</a></div>";
        }
        else {
            ShowDBUpgradeNotice();
        }
    }
}

function ShowDBUpgradeNotice() {
    $error=false;
	    $CI =& get_instance();
	$clang = $CI->limesurvey_lang;
	$sitename = Yii::app()->getConfig('sitename');
	echo '<div class="messagebox">';
    echo "<div class='header'>".$clang->gT('Database upgrade').'</div><p>';
    echo $clang->gT('Please verify the following information before continuing with the database upgrade:').'<ul>';
    echo "<li><b>" .$clang->gT('Database type') . ":</b> " . $CI->db->dbdriver . "</li>";
    echo "<li><b>" .$clang->gT('Database name') . ":</b> " . $CI->db->database . "</li>";
    echo "<li><b>" .$clang->gT('Table prefix') . ":</b> " . Yii::app()->tablePrefix . "</li>";
    echo "<li><b>" .$clang->gT('Site name') . ":</b> " . Yii::app()->getConfig("sitename") . "</li>";
    echo "<li><b>" .$clang->gT('Root URL') . ":</b> " . site_url() . "</li>";
    echo '</ul>';
    echo "<br />";
    echo "<a href='".site_url("admin/update/db/continue")."'>" . $clang->gT('Click here to continue') . "</a>";
    echo "<br />";
	echo '</div>';
}

function connectadodb() {
	global $connect;
	$CI =& get_instance();
	$databasetype = $CI->db->dbdriver;
    if ($databasetype=='postgre') $databasetype='postgres';
	$databaselocation = (empty ($CI->db->hostname)) ? "localhost" : $CI->db->hostname;
	$databaseport = (empty ($CI->db->port)) ? "default" : $CI->db->port;
	$databaseuser = $CI->db->username;
	$databasepass = $CI->db->password;
	$databasename = $CI->db->database;
	$connect=ADONewConnection($databasetype);
	$database_exists = FALSE;
	switch ($databasetype)
	{
	    case "postgres":
	    case "mysqli":
	    case "mysql": if ($databaseport!="default") {$dbhost="$databaselocation:$databaseport";}
	    else {$dbhost=$databaselocation;}
	    break;
	    case "mssql_n":
		case "mssqlnative":
	    case "mssql": if ($databaseport!="default") {$dbhost="$databaselocation,$databaseport";}
	    else {$dbhost=$databaselocation;}
	    break;
	    case "odbc_mssql": $dbhost="Driver={SQL Server};Server=$databaselocation;Database=".$databasename;
	    break;

	    default: safe_die("Unknown database type");
	}
	// Now try connecting to the database
    if (@$connect->Connect($dbhost, $databaseuser, $databasepass, $databasename))
    {
        $database_exists = TRUE;
    }
    else {
        // If that doesnt work try connection without database-name
        $connect->database = '';
        if (!@$connect->Connect($dbhost, $databaseuser, $databasepass))
        {
            safe_die("Can't connect to LimeSurvey database. Reason: ".$connect->ErrorMsg());
        }
    }
}
