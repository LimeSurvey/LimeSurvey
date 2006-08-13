<?php
/*
#############################################################
# >>> PHPSurveyor  										    #
#############################################################
# > Author:  Jason Cleeland									#
# > E-mail:  jason@cleeland.org								#
# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
# >          CARLTON SOUTH 3053, AUSTRALIA
# > Date: 	 20 February 2003								#
#															#
# This set of scripts allows you to develop, publish and	#
# perform data-entry on surveys.							#
#############################################################
#															#
#	Copyright (C) 2003  Jason Cleeland						#
#															#
# This program is free software; you can redistribute 		#
# it and/or modify it under the terms of the GNU General 	#
# Public License as published by the Free Software 			#
# Foundation; either version 2 of the License, or (at your 	#
# option) any later version.								#
#															#
# This program is distributed in the hope that it will be 	#
# useful, but WITHOUT ANY WARRANTY; without even the 		#
# implied warranty of MERCHANTABILITY or FITNESS FOR A 		#
# PARTICULAR PURPOSE.  See the GNU General Public License 	#
# for more details.											#
#															#
# You should have received a copy of the GNU General 		#
# Public License along with this program; if not, write to 	#
# the Free Software Foundation, Inc., 59 Temple Place - 	#
# Suite 330, Boston, MA  02111-1307, USA.					#
#############################################################
*/

include("classes/htaccess.class.php");

if (empty($homedir)) {die("Cannot run this script directly");}
if ($accesscontrol <> 1) {exit;}

//REDIRECT EVERYTHING HERE IF THERE IS NO .htaccess FILE.
if (!file_exists("$homedir/.htaccess") && $action == "setup")
{

	$addsummary = "<br />"._("Creating default htaccess file")."<br />\n";
	$ht = new htaccess("$homedir/.htaccess","$homedir/.htpasswd");
	$ht->setAuthType("Basic");
	$ht->setAuthName("PHPSurveyor Admin Interface");
	
	$addsummary .= _("Security Levels are now set up!")."<br />\n<br />\n";
	$addsummary .= "<a href='$scriptname?action=editusers'>"._("Continue")."</a>\n";

	$addsummary = "<br />"._("Creating default users")."<br />\n";

	$ht->addUser($defaultuser,$defaultpass);

	if (file_exists("$homedir/.htpasswd"))
	{
		$addsummary .= _("Updating users table")."<br />\n";
		$uquery="INSERT INTO ".db_table_name('users')." VALUES ('$defaultuser', '$defaultpass', '5')";
		$uresult=$connect->Execute($uquery);
		$ht->addLogin();
	}
	else
	{
		$addsummary .= _("Error occurred creating htpasswd file")."<br /><br />\n<font size='1'>"._("If you are using a windows server it is recommended that you copy the apache htpasswd.exe file into your admin folder for this function to work properly. This file is usually found in /apache group/apache/bin/")."<br /></font>\n";
	}
	$addsummary .= "<br />\n<a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
}
elseif ($action == "deleteall")
{
	$addsummary = "<br /><strong>"._("Removing security settings")."..</strong><br />\n";
	$fname1="$homedir/.htaccess";
	unlink($fname1);
	$fname1="$homedir/.htpasswd";
	unlink($fname1);
	$dq="DELETE FROM ".db_table_name('users');
	$dr=$connect->Execute($dq);
	$addsummary .= _("Access file, password file and user database deleted");
	$addsummary .= "<br /><br /><a href='$scriptname'>"._("Main Admin Screen")."</a><br />&nbsp;\n";
}

elseif ($action == "adduser")
{
	$addsummary = "<br /><strong>"._("Adding User")."</strong><br />\n";
	$user=preg_replace("/\W/","",$user);
	$pass=preg_replace("/\W/","",$pass);
	if ($user && $pass)
	{
		$ht = new htaccess("$homedir/.htaccess","$homedir/.htpasswd");
		$ht->addUser($user,$pass);
		$uquery = "INSERT INTO ".db_table_name('users')." VALUES ('$user', '$pass', '{$_POST['level']}')";
		$uresult = $connect->Execute($uquery);
		$addsummary .= "<br />"._("Username").": $user<br />"._("Password").": $pass<br />";
	}
	else
	{
		$addsummary .= _("Could not add user. Username and/or password were not supplied")."<br />\n";
	}
	$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
}

elseif ($action == "deluser")
{
	$addsummary = "<br /><strong>"._("Deleting User")."</strong><br />\n";
	if ($user)
	{
		$ht = new htaccess("$homedir/.htaccess","$homedir/.htpasswd");
		$ht->delUser($user);
		//DELETE USER FROM TABLE
		$dquery="DELETE FROM ".db_table_name('users')." WHERE user='$user'";
		$dresult=$connect->Execute($dquery);
	}
	else
	{
		$addsummary .= "<br />"._("Could not delete user. Username was not supplied.")."<br />\n";
	}
	$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
}

elseif ($action == "moduser")
{
	$addsummary = "<br /><strong>"._("Modifying User")."</strong><br />\n";
	$user=preg_replace("/\W/","",$user);
	$pass=preg_replace("/\W/","",$pass);
	if ($user && $pass)
	{
		//$addsummary .= "Modifying user $user with password $pass<br />\n";
		if ($htpasswddir) {$htpasswd = "\"$htpasswddir/htpasswd\"";} else {$htpasswd = "htpasswd";}
		$command="$htpasswd -b .htpasswd $user $pass 2>&1";
		exec($command, $CommandResult, $CommandStatus);
		if ($CommandStatus) //0=success, for other possibilities see http://httpd.apache.org/docs/programs/htpasswd.html
		{
			$addsummary .= "<pre>";
			$addsummary .= "\$CommandStatus = $CommandStatus\n";
			$addsummary .= "\$CommandResult = \n";
			foreach ($CommandResult as $Line) {$addsummary .= "$Line\n";}
			$addsummary .= "</pre>\n";
		}
		$uquery = "UPDATE ".db_table_name('users')." SET password='$pass', security='{$_POST['level']}' WHERE user='$user'";
		$uresult = $connect->Execute($uquery);

		$addsummary .= "<br />"._("Username").": $user<br />"._("Password").": $pass<br />\n";
	}
	else
	{
		$addsummary .= _("Could not modify user. Username and/or password were not supplied");
	}
	$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
}
?>
