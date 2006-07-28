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

if (empty($homedir)) {die("Cannot run this script directly");}
if ($accesscontrol <> 1) {exit;}

if (!file_exists("$homedir/.htaccess"))
	{
	//REDIRECT EVERYTHING HERE IF THERE IS NO .htaccess FILE.
	if ($action == "setup")
		{
		//DON'T DO ANYTHING UNLESS IT HAS BEEN ASKED FOR
		//CREATE HTACCESS FILE
		$addsummary = "<br />"._("Creating default htaccess file")."<br />\n";
		$fname="$homedir/.htaccess";
		echo "<font color='white'>";
		$handle=fopen($fname, 'a') or die ("<table width='250' border='1' align='center'>\n<tr>\n<td align='center'>\n<strong>"._("Error")."</strong><br />\n"._("Couldn't create htaccess file. Check your config.php for \$homedir setting, and that you have write permission in the correct directory.")."\n<p><a href='$scriptname'>"._("Main Admin Screen")."</a></p>\n</td>\n</tr>\n</table>\n");
		echo "</font>";
		fputs($handle, $htaccess);
		fclose($handle);
		$addsummary .= _("Security Levels are now set up!")."<br />\n<br />\n";
		$addsummary .= "<a href='$scriptname?action=editusers'>"._("Continue")."</a>\n";
		
		//CREATE DEFAULT USER AND PASS
		$addsummary = "<br />"._("Creating default users")."<br />\n";
		if (isset($htpasswddir) && $htpasswddir) {$htpasswd = "\"$htpasswddir/htpasswd\"";} else {$htpasswd = "htpasswd";}
		
		# Form command line. Redirect STDERR to STDOUT using 2>&1
		$command = "$htpasswd -bc .htpasswd $defaultuser $defaultpass 2>&1";
		$addsummary .= "<font size='1'>".htmlspecialchars($command)."</font><br />\n<br />\n";
		
		exec($command, $CommandResult, $CommandStatus);
		if ($CommandStatus) //0=success, for other possibilities see http://httpd.apache.org/docs/programs/htpasswd.html
			{
			$addsummary .= "<pre>";
			$addsummary .= "\$CommandStatus = $CommandStatus\n";
			$addsummary .= "\$CommandResult = \n";
			foreach ($CommandResult as $Line) {$addsummary .= "$Line\n";}
			$addsummary .= "</pre>\n";
			}
		
		if (file_exists("$homedir/.htpasswd"))
			{
			$addsummary .= _("Updating users table")."<br />\n";
			$uquery="INSERT INTO ".db_table_name('users')." VALUES ('$defaultuser', '$defaultpass', '5')";
			$uresult=$connect->Execute($uquery);
			}
		else
			{
			unlink($fname);
			$addsummary .= _("Error occurred creating htpasswd file")."<br /><br />\n<font size='1'>"._("If you are using a windows server it is recommended that you copy the apache htpasswd.exe file into your admin folder for this function to work properly. This file is usually found in /apache group/apache/bin/")."<br /></font>\n";
			}
		$addsummary .= "<br />\n<a href='$scriptname?action=editusers'>"._("Continue")."</a><br />&nbsp;\n";
		}
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
		if (isset($htpasswddir) && $htpasswddir) {$htpasswd="\"$htpasswddir/htpasswd\"";} else {$htpasswd="htpasswd";}
		$command="$htpasswd -b .htpasswd $user $pass 2>&1";
		exec($command, $CommandResult, $CommandStatus);
		if ($CommandStatus) //0=success, for other possibilities see http://httpd.apache.org/docs/programs/htpasswd.html
			{
			$addsummary .= "<pre>"
						 . "\$CommandStatus = $CommandStatus\n"
						 . "\$CommandResult = \n";
			foreach ($CommandResult as $Line) {$addsummary .= "$Line\n";}
			$addsummary .= "</pre>\n";
			}
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
		$fname="$homedir/.htpasswd";
		$htpasswds = file($fname);
		foreach ($htpasswds as $htp)
			{
			list ($fuser, $fpass) = split(":", $htp);
			if ($fuser == $user)
				{
				//$addsummary .= "User found!<br />\n";
				}
			else
				{
				$newhtpasswd[]=$htp;
				}
			}
		//WRITE FILE
		$nfname="$homedir/.htpasswd";
		$fp = fopen($nfname,"w");
		foreach ($newhtpasswd as $nhtp)
			{
			fputs($fp, $nhtp);
			}
		fclose($fp);
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
