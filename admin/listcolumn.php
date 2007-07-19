<?php
/*
#############################################################
# >>> LimeSurvey  										#
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
require_once(dirname(__FILE__).'/../config.php');
if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}


include_once("login_check.php");

sendcacheheaders();

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($column)) {$column=returnglobal('column');}
if (!isset($order)) {$order=returnglobal('order');}
if (!isset($sql)) {$sql=returnglobal('sql');}

if (!$surveyid)
{
	//NOSID
	exit;
}
if (!$column)
{
	//NOCOLUMN
	exit;
}

$query = "SELECT id, $column FROM {$dbprefix}survey_$surveyid WHERE $column != ''";

if ($sql && $sql != "NULL")
{
	$query .= " AND ".auto_unescape(urldecode($sql));
}

if ($order == "alpha")
{
	$query .= " ORDER BY $column";
}

$result=db_execute_assoc($query) or die("Error with query: ".$query."<br />".$connect->ErrorMsg());
echo "<html><body topmargin='0' leftmargin='0' bgcolor='black'>\n";
echo "<table width='98%' align='center' border='1' bordercolor='#111111' cellspacing='0' bgcolor='white'>\n";
echo "<tr><td bgcolor='black' valign='top'><input type='image' src='$imagefiles/downarrow.png' align='left' onclick=\"window.open('listcolumn.php?sid=$surveyid&column=$column&order=id', '_top')\"></td>\n";
echo "<td bgcolor='black' valign='top'><input type='image' align='right' src='$imagefiles/close.gif' onclick='window.close()' />";
echo "<input type='image' src='$imagefiles/downarrow.png' align='left' onclick=\"window.open('listcolumn.php?sid=$surveyid&column=$column&order=alpha', '_top')\" />";
echo "</td></tr>\n";
while ($row=$result->FetchRow())
{
	echo  "<tr><td valign='top' align='center' >"
	. "<a href='$scriptname?action=browse&sid=$surveyid&subaction=id&id=".$row['id']."' target='home'>"
	. $row['id']."</a></td>"
	. "<td valign='top'>".$row[$column]."</td></tr>\n";
}
echo "</table>\n";
echo "</body></html>";
?>
