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
//Ensure script is not run directly, avoid path disclosure
if (empty($_GET['dbname'])) {die ("Cannot run this script directly");}

$dbname = $_GET['dbname'];

require_once("config.php");

sendcacheheaders();

echo $htmlheader;
echo "<br />\n";
echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"._CREATEDB."</strong></td></tr>\n";
echo "\t<tr height='22' bgcolor='#CCCCCC'><td align='center'>$setfont\n";

if (!$dbname)
	{
	echo "<br /><strong>$setfont<font color='red'>"._ERROR."</font></strong><br />\n";
	echo _CD_NODBNAME;
	
	echo "<br /><br />\n";
	echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick='location.href=\"$scriptname\"'>";
	exit;
	}
$connect=mysql_connect("$databaselocation:$databaseport", "$databaseuser", "$databasepass");
if (!mysql_selectdb ($dbname, $connect)) //Database named in config.php does not exist
	{
	$createDb=mysql_query("CREATE DATABASE `$dbname`"); //Better than using mysql_create_db which is deprecated and does not work for MySQL 4 Client API
	if ($createDb) //Database has been succesfully created
		{
		echo "<br />$setfont<strong><font color='green'>\n";
		echo _CD_DBCREATED."</font></strong><br /><br />\n";
		echo _CD_POPULATE_MESSAGE."<br /><br />\n";
		echo "<input $btstyle type='submit' value='"._CD_POPULATE."' onClick='location.href=\"checkfields.php\"'>";
		}
	else
		{
		echo "<strong>$setfont<font color='red'>"._ERROR."</strong></font><br />\n";
		echo _CD_NOCREATE." ($dbname)<br /><font size='1'>\n";
		echo mysql_error();
		echo "</font><br /><br />\n";
		echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick='location.href=\"$scriptname\"'>";
		}
	}
echo "</td></tr></table>\n";

?>