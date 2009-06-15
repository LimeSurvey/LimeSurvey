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


if ($argc < 2 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

This is a command line LimeSurvey Survey importer.

  Usage:
  php <?php echo $argv[0]; ?> <File to import> [<user> <password>]

  <File to import> has to be a LimeSurvey survey dump.
  <user> and <password> are only required if the control access is active
  With the --help, -help, -h, or -? options, you can get this help.

<?php
	exit;
} else {
    $the_full_file_path = $argv[1];
    
    $username = ($argc>2)? $argv[2] : "";
    $userpass = ($argc>3)? $argv[3] : "";
}

if (!file_exists($the_full_file_path)) {
    echo "\nThe file $the_full_file_path does not exist\n";
    exit;
}

$_SERVER['SERVER_NAME'] = "";				// just to avoid notices
$_SERVER['SERVER_SOFTWARE'] = "";		// just to avoid notices
require_once(dirname(__FILE__).'/../config-defaults.php'); 
require_once(dirname(__FILE__).'/../common.php');

if (isset($_REQUEST['homedir'])) {die('');}
require_once($homedir."/classes/core/sha256.php"); 
$adminoutput ="";										// just to avoid notices
include("database.php");
$query = "SELECT uid, password, lang FROM ".db_table_name('users')." WHERE users_name=".$connect->qstr($username);
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$result = $connect->SelectLimit($query, 1) or die ($query."\n".$connect->ErrorMsg());
if ($result->RecordCount() < 1)
   {
	// wrong or unknown username and/or email
	echo "\n".$clang->gT("User name invalid!")."\n";
	exit;
	}
else
	{
	$fields = $result->FetchRow();
	if (SHA256::hashing($userpass) == $fields['password'])
		{
			$_SESSION['loginID'] = intval($fields['uid']);
			$clang = new limesurvey_lang($fields['lang']);
	
			GetSessionUserRights($_SESSION['loginID']);
			if (!$_SESSION['USER_RIGHT_CREATE_SURVEY'])
			{
				// no permission to create survey!
				echo "\n".$clang->gT("You are not allowed to import a survey!")."\n";
				exit;
			}
		}
		else
		{
			// password don't match username
			echo "\n".$clang->gT("User name and password do not match!")."\n";
			exit;
		}
	}

echo "\n";

$importsurvey = "";

$importingfrom = "cmdline";	// "http" for the web version and "cmdline" for the command line version
include("importsurvey.php");

?>
