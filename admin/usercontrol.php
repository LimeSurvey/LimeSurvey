<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
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

if (!$homedir) {exit;}

if (!file_exists("$homedir/.htaccess"))
	{
	//REDIRECT EVERYTHING HERE IF THERE IS NO .htaccess FILE.
	if ($action == "setup")
		{
		//DON'T DO ANYTHING UNLESS IT HAS BEEN ASKED FOR
		//CREATE HTACCESS FILE
		$addsummary .= "Creating default htaccess file<br />\n";
		$fname="$homedir/.htaccess";
		echo "<font color='white'>";
		$handle=fopen($fname, 'a') or die ("<table width='250' border='1' align='center'>\n<tr>\n<td align='center'>\n<b>Error.</b><br />\nCouldn't create htaccess file. Have you set your config.php properly? Check the '\$homedir' setting in particular!\n<p><a href='$scriptname'>Back to admin</a></p>\n</td>\n</tr>\n</table>\n");
		echo "</font>";
		fputs($handle, $htaccess);
		fclose($handle);
		$addsummary .= "Security Levels are now set up!<br />\n<br />\n";
		$addsummary .= "<a href='$scriptname?action=editusers'>Finished</a>\n";
		
		//CREATE DEFAULT USER AND PASS
		$addsummary = "Creating default users<br />\n";
		if ($htpasswddir) {$htpasswd = "\"$htpasswddir/htpasswd\"";} else {$htpasswd = "htpasswd";}
		
		# Form command line. Redirect STDERR to STDOUT using 2>&1
		$command = "$htpasswd -bc .htpasswd $defaultuser $defaultpass 2>&1";
		$addsummary .= "<font size='1'>$command</font><br />\n<br />\n";
		
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
			$addsummary .= "Updating users table<br />\n";
			$uquery="INSERT INTO users VALUES ('$defaultuser', '$defaultpass', '5')";
			$uresult=mysql_query($uquery);
			}
		else
			{
			unlink($fname);
			$addsummary .= "Error occurred creating htpasswd file. Sorry.<br />\n";
			}
		$addsummary .= "<br />\n<a href='$scriptname?action=editusers'>Finished</a>\n";
		}
	}

elseif ($action == "deleteall")
	{
	$addsummary = "<b>DELETING SECURITY...</b><br />\n";
	$fname1="$homedir/.htaccess";
	unlink($fname1);
	$addsummary .= "Access file removed<br />\n";
	$fname1="$homedir/.htpasswd";
	unlink($fname1);
	$addsummary .= "Password file removed<br />\n";
	$dq="DELETE FROM users";
	$dr=mysql_query($dq);
	$addsummary .= "User records removed.";
	$addsummary .= "<br /><br /><a href='$scriptname'>Finished</a>\n";
	}
	
elseif ($action == "adduser")
	{
	$addsummary = "<b>ADDING USER...</b><br />\n";
	if ($user && $pass)
		{
		$addsummary .= "Adding user $user with password $pass<br />\n";
		if ($htpasswddir) {$htpasswd="\"$htpasswddir/htpasswd\"";} else {$htpasswd="htpasswd";}
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
		$uquery = "INSERT INTO users VALUES ('$user', '$pass', '$level')";
		$uresult = mysql_query($uquery);
		
		}
	else
		{
		$addsummary .= "Could not add user. Username and/or password were not supplied<br />\n";
		}
	$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>Finished</a>\n";
	}

elseif ($action == "deluser")
	{
	$addsummary = "DELETING USER...<br />\n";
	if ($user)
		{
		$fname="$homedir/.htpasswd";
		$htpasswds = file($fname);
		foreach ($htpasswds as $htp)
			{
			list ($fuser, $fpass) = split(":", $htp);
			if ($fuser == $user)
				{
				$addsummary .= "User found!<br />\n";
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
		$addsummary .= "User password deleted<br />\n";
		//DELETE USER FROM TABLE
		$dquery="DELETE FROM users WHERE user='$user'";
		$dresult=mysql_query($dquery);
		$addsummary .= "User records deleted.";
		}
	else
		{
		$addsummary .= "Could not delete user. Username not supplied!<br />\n";
		}
	$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>Finished</a>\n";
	}

elseif ($action == "moduser")
	{
	$addsummary = "<b>MODIFYING USER...</b><br />\n";
	if ($user && $pass)
		{
		$addsummary .= "Modifying user $user with password $pass<br />\n";
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
		$uquery = "UPDATE users SET password='$pass', security='$level' WHERE user='$user'";
		$uresult = mysql_query($uquery);
		$addsummary .= "User added!";
		}
	else
		{
		$addsummary .= "Could not modify user. Username and/or password were not supplied!";
		}
	$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>Finished</a>\n";
	}
?>