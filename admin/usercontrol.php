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
		$addsummary .= "Creating default htaccess file<BR>";
		$fname="$homedir/.htaccess";
		echo "<FONT COLOR='WHITE'>";
		$handle=fopen($fname, 'a') or die ("<TABLE WIDTH='250' BORDER='1' ALIGN='CENTER'><TR><TD ALIGN='CENTER'><B>Error.</B><BR>Couldn't create htaccess file. Have you set your config.php properly? Check the '\$homedir' setting in particular!<P><a href='$scriptname'>Back to admin</a></TD></TR></TABLE>");
		echo "</FONT>";
		fputs($handle, $htaccess);
		fclose($handle);
		$addsummary .= "Security Levels are now set up!";
		$addsummary .= "<BR><BR><a href='$scriptname'>Finished</a>";
		//CREATE DEFAULT USER AND PASS
		$addsummary = "Creating default users<BR>";
		if ($htpasswddir) {$htpasswd="\"$htpasswddir/htpasswd\"";} else {$htpasswd="htpasswd";}
		$command="$htpasswd -bc \"$homedir/.htpasswd\" $defaultuser $defaultpass";
		$addsummary .= "<FONT SIZE='1'>$command</FONT><BR><BR>";
		exec($command);
		if (file_exists("$homedir/.htpasswd"))
			{
			$addsummary .= "Updating users table<BR>";
			$uquery="INSERT INTO users VALUES ('$defaultuser', '$defaultpass', '5')";
			$uresult=mysql_query($uquery);
			}
		else
			{
			unlink($fname);
			$addsummary .= "Error occurred creating htpasswd file. Sorry.";
			}
		$addsummary .= "<BR><a href='$scriptname'>Finished</a>";
		}
	}

elseif ($action == "deleteall")
	{
	$addsummary = "<B>DELETING SECURITY...</B><BR>";
	$fname1="$homedir/.htaccess";
	unlink($fname1);
	$addsummary .= "Access file removed<BR>";
	$fname1="$homedir/.htpasswd";
	unlink($fname1);
	$addsummary .= "Password file removed<BR>";
	$dq="DELETE FROM users";
	$dr=mysql_query($dq);
	$addsummary .= "User records removed.";
	$addsummary .= "<BR><BR><a href='$scriptname'>Finished</a>";
	}
	
elseif ($action == "adduser")
	{
	$addsummary = "<B>ADDING USER...</B><BR>";
	if ($user && $pass)
		{
		$addsummary .= "Adding user $user with password $pass<BR>";
		if ($htpasswddir) {$htpasswd="\"$htpasswddir/htpasswd\"";} else {$htpasswd="htpasswd";}
		$command="$htpasswd -b .htpasswd $user $pass";
		exec($command);
		$uquery = "INSERT INTO users VALUES ('$user', '$pass', '$level')";
		$uresult = mysql_query($uquery);
		
		}
	else
		{
		$addsummary .= "Could not add user. Username and/or password were not supplied<BR>";
		}
	$addsummary .= "<BR><BR><a href='$scriptname'>Finished</a>";
	}

elseif ($action == "deluser")
	{
	$addsummary = "DELETING USER...<BR>";
	if ($user)
		{
		$fname="$homedir/.htpasswd";
		$htpasswds = file($fname);
		foreach ($htpasswds as $htp)
			{
			list ($fuser, $fpass) = split(":", $htp);
			if ($fuser == $user)
				{
				$addsummary .= "User found!<BR>";
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
		$addsummary .= "User password deleted<BR>";
		//DELETE USER FROM TABLE
		$dquery="DELETE FROM users WHERE user='$user'";
		$dresult=mysql_query($dquery);
		$addsummary .= "User records deleted.";
		}
	else
		{
		$addsummary .= "Could not delete user. Username not supplied!<BR>";
		}
	$addsummary .= "<BR><BR><a href='$scriptname'>Finished</a>";
	}

elseif ($action == "moduser")
	{
	$addsummary = "<B>MODIFYING USER...</B><BR>";
	if ($user && $pass)
		{
		$addsummary .= "Modifying user $user with password $pass<BR>";
		$command="\"$homedir/htpasswd.exe\" -b .htpasswd $user $pass";
		exec($command);
		$uquery = "UPDATE users SET password='$pass', security='$level' WHERE user='$user'";
		$uresult = mysql_query($uquery);
		$addsummary .= "User added!";
		}
	else
		{
		$addsummary .= "Could not modify user. Username and/or password were not supplied!";
		}
	$addsummary .= "<BR><BR><a href='$scriptname'>Finished</a>";
	}
?>