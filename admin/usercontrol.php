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
if ($accesscontrol <> 1) {exit;}

if (!file_exists("$homedir/.htaccess"))
	{
	//REDIRECT EVERYTHING HERE IF THERE IS NO .htaccess FILE.
	if ($action == "setup")
		{
		//DON'T DO ANYTHING UNLESS IT HAS BEEN ASKED FOR
		//CREATE HTACCESS FILE
		$addsummary .= "<br />"._UC_CREATE."<br />\n";
		$fname="$homedir/.htaccess";
		echo "<font color='white'>";
		$handle=fopen($fname, 'a') or die ("<table width='250' border='1' align='center'>\n<tr>\n<td align='center'>\n<b>"._ERROR."</b><br />\n"._UC_NOCREATE."\n<p><a href='$scriptname'>"._GO_ADMIN."</a></p>\n</td>\n</tr>\n</table>\n");
		echo "</font>";
		fputs($handle, $htaccess);
		fclose($handle);
		$addsummary .= _UC_SEC_DONE."<br />\n<br />\n";
		$addsummary .= "<a href='$scriptname?action=editusers'>"._CONTINUE."</a>\n";
		
		//CREATE DEFAULT USER AND PASS
		$addsummary = "<br />"._UC_CREATE_DEFAULT."<br />\n";
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
			$addsummary .= _UC_UPDATE_TABLE."<br />\n";
			$uquery="INSERT INTO {$dbprefix}users VALUES ('$defaultuser', '$defaultpass', '5')";
			$uresult=mysql_query($uquery);
			}
		else
			{
			unlink($fname);
			$addsummary .= _UC_HTPASSWD_ERROR."<br /><br />\n<font size='1'>"._UC_HTPASSWD_EXPLAIN."<br /></font>\n";
			}
		$addsummary .= "<br />\n<a href='$scriptname?action=editusers'>"._CONTINUE."</a><br />&nbsp;\n";
		}
	}

elseif ($action == "deleteall")
	{
	$addsummary = "<br /><b>"._UC_SEC_REMOVE."..</b><br />\n";
	$fname1="$homedir/.htaccess";
	unlink($fname1);
	$fname1="$homedir/.htpasswd";
	unlink($fname1);
	$dq="DELETE FROM {$dbprefix}users";
	$dr=mysql_query($dq);
	$addsummary .= _UC_ALL_REMOVED;
	$addsummary .= "<br /><br /><a href='$scriptname'>"._GO_ADMIN."</a><br />&nbsp;\n";
	}
	
elseif ($action == "adduser")
	{
	$addsummary = "<br /><b>"._UC_ADD_USER."</b><br />\n";
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
		$uquery = "INSERT INTO {$dbprefix}users VALUES ('$user', '$pass', '{$_POST['level']}')";
		$uresult = mysql_query($uquery);
		$addsummary .= "<br />"._USERNAME.": $user<br />"._PASSWORD.": $pass<br />";
		}
	else
		{
		$addsummary .= _UC_ADD_MISSING."<br />\n";
		}
	$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._CONTINUE."</a><br />&nbsp;\n";
	}

elseif ($action == "deluser")
	{
	$addsummary = "<br /><b>"._UC_DEL_USER."</b><br />\n";
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
		$dquery="DELETE FROM {$dbprefix}users WHERE user='$user'";
		$dresult=mysql_query($dquery);
		}
	else
		{
		$addsummary .= "<br />"._UC_DEL_MISSING."<br />\n";
		}
	$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._CONTINUE."</a><br />&nbsp;\n";
	}

elseif ($action == "moduser")
	{
	$addsummary = "<br /><b>"._UC_MOD_USER."</b><br />\n";
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
		$uquery = "UPDATE {$dbprefix}users SET password='$pass', security='$level' WHERE user='$user'";
		$uresult = mysql_query($uquery);
		
		$addsummary .= "<br />"._USERNAME.": $user<br />"._PASSWORD.": $pass<br />\n";
		}
	else
		{
		$addsummary .= _UC_MOD_MISSING;
		}
	$addsummary .= "<br /><br /><a href='$scriptname?action=editusers'>"._CONTINUE."</a><br />&nbsp;\n";
	}
?>