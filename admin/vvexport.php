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
//Exports all responses to a survey in special "Verified Voting" format.
require_once("config.php");

if (!isset($sid)) {$sid=returnglobal('sid');}

header("Content-Disposition: attachment; filename=vvexport_$sid.xls");
header("Content-type: application/vnd.ms-excel");
$s="\t";

$fieldmap=createFieldMap($sid, "full");
$surveytable = "{$dbprefix}survey_$sid";

loadPublicLangFile($sid);

$fldlist = mysql_list_fields($databasename, $surveytable);
$columns = mysql_num_fields($fldlist);
for ($i = 0; $i < $columns; $i++)
	{
	$fieldnames[] = mysql_field_name($fldlist, $i);
	}
	
	
//Create the human friendly first line
$firstline="";
$secondline="";
foreach ($fieldnames as $field)
	{
	$fielddata=arraySearchByKey($field, $fieldmap, "fieldname", 1);
	//echo "<pre>";print_r($fielddata);echo "</pre>";
	if (count($fielddata) < 1) {$firstline.=$field;}
	else 
		{$firstline.=str_replace("\n", " ", str_replace("\t", "   ", $fielddata['question']));}
	$firstline .= $s;
	$secondline .= $field.$s;
	}
echo $firstline."\n";
echo $secondline."\n";
$query = "SELECT * FROM $surveytable";
$result = mysql_query($query) or die("Error:<br />$query<br />".mysql_error());

while ($row=mysql_fetch_array($result))
	{
	foreach ($fieldnames as $field)
		{
		echo $row[$field].$s;
		}
	echo "\n";
	}

//echo "<pre>$firstline</pre>";
//echo "<pre>$secondline</pre>";
//echo "<pre>"; print_r($fieldnames); echo "</pre>";
//echo "<pre>"; print_r($fieldmap); echo "</pre>";
?>