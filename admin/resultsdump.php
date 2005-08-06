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
if (empty($_GET['sid'])) {die ("Cannot run this script directly");}

$surveyid = $_GET['sid'];

require_once("config.php");

if (!$surveyid)
	{
	echo "ERROR: Cannot backup without a survey SID!";
	exit;
	}

if (ini_get('safe_mode'))
	{
	echo "ERROR: Your server has safe_mode set to ON, and subsequently PHPSurveyor cannot dump your survey results.<br /><br />\n";
	echo "You should either set your safe_mode to OFF (which, naturally, has security implications) or consider using";
	echo " an alternative script, like phpMyAdmin to dump the results from survey_$surveyid.";
	exit;
	}

$filename="survey_{$surveyid}_dump.sql";
$mysqldump="$mysqlbin/mysqldump";
if (substr($OS, 0, 3) == "WIN") {$mysqldump .= ".exe";}
//Check that $mysqlbin/mysqldump actually exists
if (!file_exists($mysqldump)) 
	{
	echo "$setfont<center><strong><font color='red'>ERROR:</font></strong><br />\n";
	echo "Cannot find mysqldump file. ($mysqldump)<br /><br />\n";
	echo "If this script is running on a Windows Server, you should uncomment the \$mysqlbin line in config.php<br /><br />\n";
	echo "<a href='browse.php?sid=$surveyid'>Back to browse</a></center>";
	exit;
	}

$command="$mysqlbin/mysqldump -u $databaseuser --password=$databasepass $databasename {$dbprefix}survey_$surveyid > $filename";

$backup = popen("$command","r");
pclose($backup);

header("Content-Disposition: attachment; filename=$filename");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
Header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");                          // HTTP/1.0

$handle=fopen("$filename", "r");
while (!feof($handle))
	{
	$buffer=fgets($handle, 1024);
	echo $buffer;
	}
fclose($handle);

unlink("$filename");
?>