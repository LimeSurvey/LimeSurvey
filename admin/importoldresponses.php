<?php
/*
	#############################################################
	# >>> PHPSurveyor  									    	#
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
//import responses from an old_ survey table into an active survey
require_once(dirname(__FILE__).'/../config.php');

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($action)) {$action = returnglobal('action');}
if (!isset($oldtable)) {$oldtable = returnglobal('oldtable');}

if (!$action == "import")
	{
	// show UI for choosing old table

	$query = "SHOW TABLES LIKE '{$dbprefix}old\_{$surveyid}\_%'";
	$result = db_execute_num($query) or die("Error:<br />$query<br />".$connect->ErrorMsg());
	$optionElements = '';
	while ($row = $result->FetchRow())
		{
		$optionElements .= "\t\t\t<option>{$row[0]}</option>\n";
		}
	
    echo $htmlheader;
	echo "<br /><table align='center' class='outlinetable'>
		<tr>
			<th colspan='2'>"._IORD_TITLE."</th>
		</tr>
		<form method='post'>
		<tr>
		 <td align='right'>"._IORD_TARGETID."</td>
		 <td> $surveyid<input type='hidden' $slstyle value='$surveyid' name='sid'></td>
		</tr>
		<tr>
		 <td align='right'>
		  Source table:
		 </td>
		 <td>
		  <select name='oldtable' $slstyle>
{$optionElements}
		  </select>
		 </td>
		</tr>
		<tr>
		 <td>&nbsp;
		 </td>
		 <td>
		  <input type='submit' value='"._IORD_BTIMPORT."' $btstyle onClick='return confirm(\"Are you sure?\")'>&nbsp;
		 </td>
		</tr>
		 <input type='hidden' name='action' value='import'>
		</form>
		<tr><td colspan='2' align='center'>[<a href='browse.php?sid=$surveyid'>"._B_ADMIN_BT."</a>]</td></tr>
		</table>
</body></html>";
	}
elseif (isset($surveyid) && $surveyid && isset($oldtable))
	{
	
	// options (UI not implemented)
	
	$dontimportfields = array(
		'id' //,'otherfield'
	);
	$presetfields = array( // quote all strings so we can allow NULL
		//'4X13X951'=>"'Y'"
		//'id' => "NULL" 
	);
	$importidrange = false; //array('first'=>3,'last'=>10);

	$activetable = "{$dbprefix}survey_$surveyid";

	// fields we can import
	$importablefields = array();	
	$query = "SHOW COLUMNS FROM {$activetable}";
	$result = db_execute_assoc($query) or die("Error:<br />$query<br />".$connect->ErrorMsg());
	while ($row = $result->FetchRow())
		{
		if (!in_array($row['Field'],$dontimportfields))
			{
			$importablefields[] = $row['Field'];
			}
		}

	// fields we can supply
	$availablefields = array();
	$query = "SHOW COLUMNS FROM {$oldtable}";
	$result = db_execute_assoc($query) or die("Error:<br />$query<br />".$connect->ErrorMsg());
	while ($row = $result->FetchRow())
		{
		$availablefields[] = $row['Field'];
		}
	foreach ($presetfields as $field => $value)
		{
		if (!in_array($field,$availablefields))
			{
			$availablefields[] = $field;
			}
		}

	$fieldstoimport = array_intersect($importablefields,$availablefields);

	// data sources for each field (field of oldtable or preset value)
	$sourcefields = array();
	foreach ($fieldstoimport as $field)
		{
		$sourcefields[] = array_key_exists($field,$presetfields)?
			$presetfields[$field] 
			: ($oldtable.'.'.$field);
		}

	$query = "INSERT INTO {$activetable} (\n\t".join("\t, ",$fieldstoimport)."\n) "
			."SELECT\n\t".join("\t,",$sourcefields)."\n"
			."FROM {$oldtable}";
	if (is_array($importidrange))
		{
		$query .= " WHERE {$oldtable}.id >= {$importidrange['first']} "
				 ." AND {$oldtable}.id <= {$importidrange['last']}";
		}
	
	$result = $connect->Execute($query) or die("Error:<br />$query<br />".$connect->ErrorMsg());

	header("Location: {$homeurl}/browse.php?sid={$surveyid}");
	}

?>
