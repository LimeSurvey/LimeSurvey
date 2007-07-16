<?php
/*
    #############################################################
    # >>> PHPSurveyor                                           #
    #############################################################
    # > Author:  Jason Cleeland                                 #
    # > E-mail:  jason@cleeland.org                             #
    # > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
    # >          CARLTON SOUTH 3053, AUSTRALIA                  #
    # > Date:    20 February 2003                               #
    #                                                           #
    # This set of scripts allows you to develop, publish and    #
    # perform data-entry on surveys.                            #
    #############################################################
    #                                                           #
    #   Copyright (C) 2003  Jason Cleeland                      #
    #                                                           #
    # This program is free software; you can redistribute       #
    # it and/or modify it under the terms of the GNU General    #
    # Public License as published by the Free Software          #
    # Foundation; either version 2 of the License, or (at your  #
    # option) any later version.                                #
    #                                                           #
    # This program is distributed in the hope that it will be   #
    # useful, but WITHOUT ANY WARRANTY; without even the        #
    # implied warranty of MERCHANTABILITY or FITNESS FOR A      #
    # PARTICULAR PURPOSE.  See the GNU General Public License   #
    # for more details.                                         #
    #                                                           #
    # You should have received a copy of the GNU General        #
    # Public License along with this program; if not, write to  #
    # the Free Software Foundation, Inc., 59 Temple Place -     #
    # Suite 330, Boston, MA  02111-1307, USA.                   #
    #############################################################   
*/

if ($argc != 4 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

This is a command line LimeSurvey Survey importer.

  Usage:
  php <?php echo $argv[0]; ?> <File to import> <user> <password>

  <File to import> has to be a LimeSurvey survey dump.
  With the --help, -help, -h, or -? options, you can get this help.

<?php
	exit;
} else {
    $the_full_file_path = $argv[1];
    $username = $argv[2];
    $userpass = $argv[3];
}

if (!file_exists($the_full_file_path)) {
    echo "\nThe file $the_full_file_path does not exist\n";
    exit;
}

$_SERVER['SERVER_NAME'] = "";				// just to avoid notices
$_SERVER['SERVER_SOFTWARE'] = "";		// just to avoid notices
require_once(dirname(__FILE__).'/../config.php');  // config.php itself includes common.php


require_once($homedir."/classes/core/sha256.php"); 
$adminoutput ="";										// just to avoid notices
include("database.php");
$query = "SELECT uid, password, lang FROM ".db_table_name('users')." WHERE users_name=".$connect->qstr($username);
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$result = $connect->SelectLimit($query, 1) or die ($query."\n".$connect->ErrorMsg());
if ($result->RecordCount() < 1)
{
	// wrong or unknown username and/or email
	echo "\n".$clang->gT("User name not found!")."\n";
	exit;
}
else
{
	$fields = $result->FetchRow();
	if (SHA256::hash($userpass) == $fields['password'])
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
		echo "\n".$clang->gT("User name / password dont match!")."\n";
		exit;
	}
}

echo "\n";

$importsurvey = "";

$importingfrom = "cmdline";	// "http" for the web version and "cmdline" for the command line version
include("importsurvey.php");

?>
