<?php
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
* $Id$
*/

//SESSIONCONTROL.PHP FILE MANAGES ADMIN SESSIONS. 
//Ensure script is not run directly, avoid path disclosure

if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}

// Read the session name from the settings table
$usquery = "SELECT stg_value FROM ".db_table_name("settings_global")." where stg_name='SessionName'";
$usresult = db_execute_assoc($usquery,'',true);
if ($usresult)
{
	$usrow = $usresult->FetchRow();
	@session_name($usrow['stg_value']);
}
 else {session_name("LimeSurveyAdmin");}
 
 
if (session_id() == "") @session_start();
//LANGUAGE ISSUES
// if changelang is called from the login page, then there is no userId 
//  ==> thus we just change the login form lang: no user profile update
// if changelang is called from another form (after login) then update user lang
// when a loginlang is specified at login time, the user profile is updated in usercontrol.php 
if (returnglobal('action') == "changelang" && (!isset($login) || !$login ))	
	{
	$_SESSION['adminlang']=returnglobal('lang');
	// if user is logged in update language in database
	if(isset($_SESSION['loginID']))
		{
		$uquery = "UPDATE {$dbprefix}users SET lang='{$_SESSION['adminlang']}' WHERE uid={$_SESSION['loginID']}";	//		added by Dennis
		$uresult = $connect->Execute($uquery);
		}
	}
elseif (!isset($_SESSION['adminlang']) || $_SESSION['adminlang']=='' )
	{
	$_SESSION['adminlang']=$defaultlang;
	}
// OLD LANGUAGE SETTING
//SetInterfaceLanguage($_SESSION['adminlang']);

// if changehtmleditormode is called then update user htmleditormode
if (returnglobal('action') == "changehtmleditormode" )	
	{
	$_SESSION['htmleditormode']=returnglobal('htmleditormode');
	if(isset($_SESSION['loginID']))
		{
		$uquery = "UPDATE {$dbprefix}users SET htmleditormode='{$_SESSION['htmleditormode']}' WHERE uid={$_SESSION['loginID']}";	//		added by Dennis
		$uresult = $connect->Execute($uquery) or die("Can't update htmleditor setting");
		}
	}
elseif (!isset($_SESSION['htmleditormode']) || $_SESSION['htmleditormode']=='' )
	{
	$_SESSION['htmleditormode']=$defaulthtmleditormode;
	}

// Construct the language class, and set the language.
if (isset($_REQUEST['rootdir'])) {die('You cannot start this script directly');}
require_once($rootdir.'/classes/core/language.php');
$clang = new limesurvey_lang($_SESSION['adminlang']);

// get user rights
if(isset($_SESSION['loginID'])) {GetSessionUserRights($_SESSION['loginID']);}
	
if ($_SERVER['REQUEST_METHOD'] == 'POST' && returnglobal('action') != 'login')
{
	if (returnglobal('checksessionbypost') != $_SESSION['checksessionpost'])
	{
		error_log("LimeSurvey ERROR while checking POST session- Probable CSRF attack Received=".returnglobal('checksessionbypost')." / Expected= ".$_SESSION['checksessionpost']." .");
		$action='';
		$subaction='';
		if (isset($_POST['action'])) {unset($_POST['action']);}
		if (isset($_POST['subaction'])) {unset($_POST['subaction']);}
		//include("access_denied.php");
	}
}

function GetSessionUserRights($loginID)
{
	global $dbprefix,$connect; 
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
    $squery = "SELECT create_survey, configurator, create_user, delete_user, superadmin, manage_template, manage_label FROM {$dbprefix}users WHERE uid=$loginID";	//		added by Dennis
	$sresult = $connect->Execute($squery);
	if(@$fields = $sresult->FetchRow())
		{
		$_SESSION['USER_RIGHT_CREATE_SURVEY'] = $fields['create_survey'];
		$_SESSION['USER_RIGHT_CONFIGURATOR'] = $fields['configurator'];
		$_SESSION['USER_RIGHT_CREATE_USER'] = $fields['create_user'];
		$_SESSION['USER_RIGHT_DELETE_USER'] = $fields['delete_user'];
		$_SESSION['USER_RIGHT_SUPERADMIN'] = $fields['superadmin'];
		$_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] = $fields['manage_template'];
		$_SESSION['USER_RIGHT_MANAGE_LABEL'] = $fields['manage_label'];
		}

	// SuperAdmins
	// * original superadmin with uid=1 unless manually changed and defined
	//   in config.php
	// * or any user having USER_RIGHT_SUPERADMIN right

	// Let's check if I am the Initial SuperAdmin
	$adminquery = "SELECT uid FROM {$dbprefix}users WHERE parent_id=0";
	$adminresult = db_select_limit_assoc($adminquery, 1);
	$row=$adminresult->FetchRow();
	if($row['uid'] == $_SESSION['loginID'])
	{
		$initialSuperadmin=true;
	}
	else
	{
		$initialSuperadmin=false;
	}

	if ( $initialSuperadmin === true)
	{
		$_SESSION['USER_RIGHT_SUPERADMIN'] = 1;
		$_SESSION['USER_RIGHT_INITIALSUPERADMIN'] = 1;
	}
	else
	{
		$_SESSION['USER_RIGHT_INITIALSUPERADMIN'] = 0;
	}
}


	
?>
