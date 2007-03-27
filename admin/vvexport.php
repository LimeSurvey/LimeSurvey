<?php
/*
#############################################################
# >>> PHPSurveyor                                           #
#############################################################
# > Author:  Jason Cleeland                                 #
# > E-mail:  jason@cleeland.org                             #
# > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
# >          CARLTON SOUTH 3053, AUSTRALIA                  #
# > Date:    20 February 2003                               #
#                                                           #
# This set of scripts allows you to develop, publish and    #
# perform data-entry on surveys.                            #
#############################################################
#                                                           #
#   Copyright (C) 2003  Jason Cleeland                      #
#                                                           #
# This program is free software; you can redistribute       #
# it and/or modify it under the terms of the GNU General    #
# Public License as published by the Free Software          #
# Foundation.                                               #
#                                                           #
#                                                           #
# This program is distributed in the hope that it will be   #
# useful, but WITHOUT ANY WARRANTY; without even the        #
# implied warranty of MERCHANTABILITY or FITNESS FOR A      #
# PARTICULAR PURPOSE.  See the GNU General Public License   #
# for more details.                                         #
#                                                           #
# You should have received a copy of the GNU General        #
# Public License along with this program; if not, write to  #
# the Free Software Foundation, Inc., 59 Temple Place -     #
# Suite 330, Boston, MA  02111-1307, USA.                   #
#############################################################
*/
//Exports all responses to a survey in special "Verified Voting" format.
require_once(dirname(__FILE__).'/../config.php');

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($action)) {$action = returnglobal('action');}

include_once("login_check.php");

if (!$action == "export")
{
	echo $htmlheader;
	echo "<br /><form method='post' action='vvexport.php?sid=$surveyid'>
    	<table align='center' class='outlinetable'>
        <tr><th colspan='2'>".$clang->gT("Export a VV survey file")."</th></tr>
        <tr>
         <td align='right'>".$clang->gT("Export Survey").":</td>
         <td><input type='text' size=4 value='$surveyid' name='sid' readonly></td>
        </tr>
        <tr>
         <td align='right'>
          Mode:
         </td>
         <td>
          <select name='method' >
           <option value='deactivate'>".$clang->gT("Export, then de-activate survey")."</option>
           <option value='none' selected>".$clang->gT("Export but leave survey active")."</option>
          </select>
         </td>
        </tr>
        <tr>
         <td>&nbsp;
         </td>
         <td>
          <input type='submit' value='".$clang->gT("Export Responses")."' onclick='return confirm(\"".$clang->gT("If you have chosen to export and de-activate, this will rename your current responses table and it will not be easy to restore it. Are you sure?")."\")'>&nbsp;
          <input type='hidden' name='action' value='export'>
         </td>
        </tr>
        <tr><td colspan='2' align='center'>[<a href='$scriptname?sid=$surveyid'>".$clang->gT("Return to Survey Administration")."</a>]</td></tr>
        </table>
        </form>";        
}
elseif (isset($surveyid) && $surveyid)
{
	//Export is happening
	header("Content-Disposition: attachment; filename=vvexport_$surveyid.xls");
	header("Content-type: application/vnd.ms-excel");
	Header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	$s="\t";

	$fieldmap=createFieldMap($surveyid, "full");
	$surveytable = "{$dbprefix}survey_$surveyid";

	GetBaseLanguageFromSurveyID($surveyid);

	$fieldnames = array_values($connect->MetaColumnNames($surveytable, true));

	//Create the human friendly first line
	$firstline="";
	$secondline="";
	foreach ($fieldnames as $field)
	{
		$fielddata=arraySearchByKey($field, $fieldmap, "fieldname", 1);
		//echo "<pre>";print_r($fielddata);echo "</pre>";
		if (count($fielddata) < 1) {$firstline.=$field;}
		else
		//{$firstline.=str_replace("\n", " ", str_replace("\t", "   ", strip_tags($fielddata['question'])));}
		{$firstline.=preg_replace('/\s+/',' ',strip_tags($fielddata['question']));}
		$firstline .= $s;
		$secondline .= $field.$s;
	}
	echo $firstline."\n";
	echo $secondline."\n";
	$query = "SELECT * FROM $surveytable";
	$result = db_execute_assoc($query) or die("Error:<br />$query<br />".$connect->ErrorMsg());

	while ($row=$result->FetchRow())
	{
		foreach ($fieldnames as $field)
		{
			$value=trim($row[$field]);
			// sunscreen for the value. necessary for the beach.
			// careful about the order of these arrays:
			// lbrace has to be substituted *first*
			$value=str_replace(array("{",
			"\n",
			"\r",
			"\t"),
			array("{lbrace}",
			"{newline}",
			"{cr}",
			"{tab}"),
			$value);
			// one last tweak: excel likes to quote values when it
			// exports as tab-delimited (esp if value contains a comma,
			// oddly enough).  So we're going to encode a leading quote,
			// if it occurs, so that we can tell the difference between
			// strings that "really are" quoted, and those that excel quotes
			// for us.
			$value=preg_replace('/^"/','{quote}',$value);
			// yay!  that nasty sun won't hurt us now!
			$sun[]=$value;
		}
		$beach=implode($s, $sun);
		echo $beach;
		unset($sun);
		echo "\n";
	}

	//echo "<pre>$firstline</pre>";
	//echo "<pre>$secondline</pre>";
	//echo "<pre>"; print_r($fieldnames); echo "</pre>";
	//echo "<pre>"; print_r($fieldmap); echo "</pre>";

	//Now lets finalised according to the "method"
	if (!isset($method)) {$method=returnglobal('method');}
	switch($method)
	{
		case "deactivate": //Deactivate the survey
		$date = date('YmdHi'); //'Hi' adds 24hours+minutes to name to allow multiple deactiviations in a day
		$tablelist = $connect->MetaTables();
		if (in_array("{$dbprefix}tokens_{$_GET['sid']}", $tablelist))
		{
			$toldtable="tokens_{$_GET['sid']}";
			$tnewtable="old_tokens_{$_GET['sid']}_{$date}";
			$tdeactivatequery = "RENAME TABLE ".db_table_name($toldtable)." TO ".db_table_name($tnewtable);
			$tdeactivateresult = $connect->Execute($tdeactivatequery) or die ("\n\n".$clang->gT("Error")."Couldn't deactivate tokens table because:<br />".$connect->ErrorMsg()."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
		}
		$oldtable="survey_{$_GET['sid']}";
		$newtable="old_{$_GET['sid']}_{$date}";

		//Update the auto_increment value from the table before renaming
		$query = "SELECT id FROM ".db_table_name($oldtable)." ORDER BY id desc";
		$result = db_select_limit_assoc($query, 1) or die("Couldn't get latest id from table<br />$query<br />".$connect->ErrorMsg());
		while ($row=$result->FetchRow())
		{
			$new_autonumber_start=$row['id']+1;
		}
		$query = "UPDATE ".db_table_name('surveys')." SET autonumber_start=$new_autonumber_start WHERE sid=$surveyid";
		$result = $connect->Execute($query); //Note this won't kill the script if it fails

		//Rename survey responses table
		$deactivatequery = "RENAME TABLE ".db_table_name($oldtable)." TO ".db_table_name($newtable);
		$deactivateresult = $connect->Execute($deactivatequery) or die ("\n\n".$clang->gT("Error")."Couldn't deactivate because:<br />".$connect->ErrorMsg()."<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>Admin</a>");
		break;
		case "delete": //Delete the rows
		break;
		default:

	} // switch
}

?>
