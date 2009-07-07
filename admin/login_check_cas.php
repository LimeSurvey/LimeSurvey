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
	//echo "bla";
	// import phpCAS lib
	include_once('classes/phpCAS/CAS.php');
	include_once("classes/phpCAS/cas_config.php");
	if(isset($_GET['user']))
	{
		$token = $_GET['token'];
		$user = $_GET['user'];

		$action = getGet('action');
		$siddy = getGet('sid');
			
		$get = '?';
		if($action!=FALSE)
		$get .= "action=".$action."&";
		if($siddy!=FALSE)
		$get .= "sid=".$siddy."&";
			
		if($user == verifyToken($token) && verifyToken($token) != null)
		{
			$auth = TRUE;
			//setUserRightsCas($user);
			$_SESSION['CASauthenticated'] = $auth;
			header("Location: admin.php$get");
		}
		else
		{
			$auth = FALSE;
			$_SESSION['CASauthenticated'] = $auth;
			header("Location: http://$casAuthServer$casAuthUri&category=auth.login");
		}
	}elseif(!isset($_SESSION['CASauthenticated']))
	{
		header("Location: http://$casAuthServer$casAuthUri&category=auth.login");
	}

	if (isset($_REQUEST['action']) && $_REQUEST['action']=='logout')
	{
		//session_unset();
		session_destroy();
		session_write_close();
		//phpCAS::logout();
		//phpCAS::forceAuthentication();
		header("Location: http://$casAuthServer$casAuthUri&category=auth.logout");
	}

	//if ($action=='login')
	if (isset($_REQUEST['action']) && $_REQUEST['action']=='login')
	{
		//phpCAS::forceAuthentication();
		header("Location: http://$casAuthServer$casAuthUri&category=auth.login");
	}
	if($_SESSION['CASauthenticated']===FALSE)
	{
		header("Location: http://$casAuthServer$casAuthUri&category=auth.login");
	}

}
if(isset($_GET['token']))
{

	$action = getGet('action');
	$siddy = getGet('sid');
		
	$get = '?';
	if($action!=FALSE)
	$get .= "action=".$action."&";
	if($siddy!=FALSE)
	$get .= "sid=".$siddy."&";


		header("Location: admin.php$get");

}

function getGet($var)
{
	switch ($var){
		case "all":
			foreach($_GET as $get)
			{
				return;
			}
			break;
		default:
			if(isset($_GET["$var"]))
			{
				return $_GET["$var"];
			}
			else return FALSE;
			break;

	}
}
function verifyToken($token) {
	global $singleSignOnService, $singleSignOnSharedSecret;

	// check the configuration options in LocalSettings.php
	//QISSingleSignOn::checkConfiguration();

	//echo ('QISSingleSignOn: token:'.htmlspecialchars($token));

	// prepare token
	$tokens = explode('/', $token, 4);
	if ((count($tokens) != 4) or (strpos($tokens[3], '/') === false)) {
		echo ('QISSingleSignOn: Token incomplete:'.htmlspecialchars($token));
		return null;
	}

	// find the _last_ '/' to split username and hash as the username may include '/'-chars.
	$temp_pos = strrpos($tokens[3], '/');
	$tokens[4] = substr($tokens[3], $temp_pos + 1);
	$tokens[3] = substr($tokens[3], 0, $temp_pos);

	// check version
	if ($tokens[0] != '1.0') {
		echo ('QISSingleSignOn: Unknown version:'.htmlspecialchars($tokens));
		return null;
	}

	// check time
	$currentTime = microtime();
	$currentTime = substr($currentTime, strpos($currentTime, ' '));
	if (intval($tokens[1]) > intval($currentTime) + 60) {
		echo ('QISSingleSignOn: Token was created in the future (Check your clocks):'.htmlspecialchars($token));
		return null;
	}
	if (intval($tokens[1]) + 60 < intval($currentTime)) {
		echo ('QISSingleSignOn: Token expired:'.htmlspecialchars($token));
		return null;
	}

	// check service name
	if ($tokens[2] != $singleSignOnService) {
		echo ('QISSingleSignOn: Wrong service:'.htmlspecialchars($token));
		return null;
	}

	// check username name (using Title::newFormText as in User::newFromName)
	$userinfo = explode('/', urldecode($tokens[3]));

	// Andere Methode wie bei tokens: find the _last_ '/' to split username and hash as the username may include '/'-chars.
	//               $temp_pos = strrpos($tokens[3], '/');
	//               $userinfo[1] = substr($tokens[3], $temp_pos + 1);
	//               $userinfo[0] = substr($tokens[3], 0, $temp_pos);

	//				echo ('QISSingleSignOn: userinfo-0:'.$userinfo[0]."\n");
	//				echo ('QISSingleSignOn: userinfo-1:'.$userinfo[1]."\n");

	//$t = Title::newFromText($userinfo[0]);

	$user = $userinfo[0];
	if ($user == null) {
		echo ('QISSingleSignOn: Invalid character in user name: '.htmlspecialchars($userinfo[0]));
		return null;
	}

	// check hash
	$toHash = $tokens[0].'/'.$tokens[1].'/'.$tokens[2].'/'.$tokens[3].'/'.$singleSignOnSharedSecret;
	$hash =  md5($toHash);
	if ($hash != $tokens[4]) {
		echo ('QISSingleSignOn: Hash verification failed:'.htmlspecialchars($token).' Should be: ' . $hash);
		return null;
	}

	// copy _ridlist to session for WikiRights (if present)
	if (count($userinfo) > -1) {
		//session_start();
		setUserRightsCas($user, $user);
		//$_SESSION['_ridlist'] = $userinfo[1];
	}

	// welcome, you passed all tests.
	return $user;
}

function setUserRightsCas($user, $role="")
{
	include("../config-defaults.php");
	include("../config.php");
	
	$_SESSION['user'] = $user;
	$_SESSION['loginID'] = 1;
	$_SESSION['dateformat'] = 1;

	$_SESSION['adminlang'] = $defaultlang;
	$_SESSION['htmleditormode'] = 'default';

	$_SESSION['checksessionpost'] = randomkey(10);
	$_SESSION['pw_notify']=false;

	switch ($role){
		case "admin":
			//echo "hallo";
			$_SESSION['USER_RIGHT_CREATE_SURVEY'] = 1;
			$_SESSION['USER_RIGHT_CONFIGURATOR'] = 1;
			$_SESSION['USER_RIGHT_CREATE_USER'] = 1;
			$_SESSION['USER_RIGHT_DELETE_USER'] = 1;
			$_SESSION['USER_RIGHT_SUPERADMIN'] = 1;
			$_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] = 1;
			$_SESSION['USER_RIGHT_MANAGE_LABEL'] = 1;
			break;
		default:
			//echo "default";
			$_SESSION['USER_RIGHT_CREATE_SURVEY'] = 1;
			$_SESSION['USER_RIGHT_CONFIGURATOR'] = 1;
			$_SESSION['USER_RIGHT_CREATE_USER'] = 0;
			$_SESSION['USER_RIGHT_DELETE_USER'] = 0;
			$_SESSION['USER_RIGHT_SUPERADMIN'] = 0;
			$_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] = 1;
			$_SESSION['USER_RIGHT_MANAGE_LABEL'] = 1;

			break;
	}

}

?>