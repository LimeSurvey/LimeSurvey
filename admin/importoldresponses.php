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
//import responses from an old_ survey table into an active survey
require_once(dirname(__FILE__).'/../config.php');
include_once("login_check.php");

if (!isset($oldtable)) {$oldtable=returnglobal('oldtable');}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}


if (!$subaction == "import")
{
	// show UI for choosing old table

	$query = db_select_tables_like("{$dbprefix}old\_survey_{$surveyid}\_%");
	$result = db_execute_num($query) or die("Error:<br />$query<br />".$connect->ErrorMsg());
	$optionElements = '';
	while ($row = $result->FetchRow())
	{
		$optionElements .= "\t\t\t<option>{$row[0]}</option>\n";
	}

	$importoldresponsesoutput = "";
    $importoldresponsesoutput .= "<table width='99%' align='center' style='margin: 5px; border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
    ."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Quick Statistics")."</strong></font></td></tr>\n";
    //Get the menubar
    $importoldresponsesoutput .= browsemenubar();
    $importoldresponsesoutput .= "</table>\n";
	$importoldresponsesoutput .= "<br /><table align='center' class='outlinetable'>
		<tr>
			<th colspan='2'>".$clang->gT("Import responses from an old (deactivated) survey table into an active survey")."</th>
		</tr>
		<form method='post'>
		<tr>
		 <td align='right'>".$clang->gT("Target Survey ID")."</td>
		 <td> $surveyid<input type='hidden' value='$surveyid' name='sid'></td>
		</tr>
		<tr>
		 <td align='right'>
		  Source table:
		 </td>
		 <td>
		  <select name='oldtable' >
{$optionElements}
		  </select>
		 </td>
		</tr>
		<tr>
		 <td colspan='2' align='center'>
		  <input type='submit' value='".$clang->gT("Import Responses")."' onClick='return confirm(\"Are you sure?\")'>&nbsp;
 	 	  <input type='hidden' name='subaction' value='import'>
		 </td>
		</tr>
		</form>
		</table><br />&nbsp;";
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

	header("Location: $scriptname?action=browse&sid=$surveyid");
}

?>
