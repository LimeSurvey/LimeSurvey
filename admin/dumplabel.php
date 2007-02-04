<?php
/*
#############################################################
# >>> PHPSurveyor  										#
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
# Public License Version 2 as published by the Free         #
# Software Foundation.										#
#															#
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


// DUMP THE RELATED DATA FOR A SINGLE QUESTION INTO A SQL FILE FOR IMPORTING LATER ON OR
// ON ANOTHER SURVEY SETUP DUMP ALL DATA WITH RELATED QID FROM THE FOLLOWING TABLES
// 1. questions
// 2. answers

//Ensure script is not run directly, avoid path disclosure
if (empty($_GET['lid'])) {die ("Cannot run this script directly");}

$lid=$_GET['lid'];

require_once(dirname(__FILE__).'/../config.php');

//echo $htmlheader;
if (!$lid)
{
	echo $htmlheader;
	echo "<br />\n";
	echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Export Label Set")."</strong></td></tr>\n";
	echo "\t<tr bgcolor='#CCCCCC'><td align='center'>$setfont\n";
	echo "$setfont<br /><strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n".$clang->gT("No LID has been provided. Cannot dump label set.")."<br />\n";
	echo "<br /><input type='submit' value='".$clang->gT("Main Admin Screen")."' onClick=\"window.open('$scriptname', '_top')\">\n";
	echo "\t</td></tr>\n";
	echo "</table>\n";
	echo "</body></html>\n";
	exit;
}

$dumphead = "# PHPSurveyor Label Set Dump\n"
. "# DBVersion $dbversionnumber\n"
." # This is a dumped label set from the PHPSurveyor Script\n"
. "# http://www.phpsurveyor.org/\n"
. "# Do not change this header!\n";

//1: Questions Table
$qquery = "SELECT * FROM {$dbprefix}labelsets WHERE lid=$lid";
$qdump = BuildCSVFromQuery($qquery);

//2: Answers table
$aquery = "SELECT lid, code, title, sortorder, language FROM {$dbprefix}labels WHERE lid=$lid";
$adump = BuildCSVFromQuery($aquery);

$fn = "phpsurveyor_labelset_$lid.csv";

//header("Content-Type: application/msword"); //EXPORT INTO MSWORD
header("Content-Disposition: attachment; filename=$fn");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
Header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");                          // HTTP/1.0

echo $dumphead, $qdump, $adump;

?>
