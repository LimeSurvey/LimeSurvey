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
* $Id: $
*/
if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}
if (!isset($action)) {$action=returnglobal('action');}
//
// phpCAS simple client
//
if(!isset($_SESSION['CASauthenticated']) || (isset($_SESSION['CASauthenticated']) && $_SESSION['CASauthenticated']==FALSE) || isset($_REQUEST['action']))
{
	// import phpCAS lib
	include_once('classes/phpCAS/CAS.php');
	
	phpCAS::setDebug();
	
	// initialize phpCAS
	phpCAS::client(CAS_VERSION_2_0, $casAuthServer, $casAuthPort, $casAuthUri, false);
	
	// no SSL validation for the CAS server
	phpCAS::setNoCasServerValidation();
	
	//if($action=='logout')

	if (isset($_REQUEST['action']) && $_REQUEST['action']=='logout')
	{
		session_destroy();
		session_write_close();
	  	phpCAS::logout();
	  //phpCAS::forceAuthentication();
	}
	//if ($action=='login')
	if (isset($_REQUEST['action']) && $_REQUEST['action']=='login')
	{
	  phpCAS::forceAuthentication();
	}

	
	// check CAS authentication
	$auth = phpCAS::checkAuthentication();
	$_SESSION['CASauthenticated'] = $auth;
	
	if($auth)
	{
		/**
		 * User is authenticated from CAS, with this, he gets full Superadmin rights, when successful authenticates with CAS.
		 * This is very basic though, you should try to use what your CAS server provides you with (ROLES, rights, extra attibutes, etc.)
		 * 
		 */
		$_SESSION['user'] = phpCAS::getUser();
		$_SESSION['loginID'] = 1;

		/**
		 * @var unknown_type
		 */
		$_SESSION['USER_RIGHT_CREATE_SURVEY'] = 1;
        $_SESSION['USER_RIGHT_CONFIGURATOR'] = 1;
        $_SESSION['USER_RIGHT_CREATE_USER'] = 1;
        $_SESSION['USER_RIGHT_DELETE_USER'] = 1;
        $_SESSION['USER_RIGHT_SUPERADMIN'] = 1;
        $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] = 1;
        $_SESSION['USER_RIGHT_MANAGE_LABEL'] = 1;
        
        // Passwort notify for not changed Passwort have to be false when authing over CAS (or Warnings appear, because it is not set)
        $_SESSION['pw_notify'] = false;
	}
	else
	{
		phpCAS::forceAuthentication();
	}
	
	
	
//	echo $auth;
//	echo phpCAS::getUser();
//	echo phpCAS::getVersion();
}
?>