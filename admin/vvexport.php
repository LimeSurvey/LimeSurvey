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
if (!isset($action)) {$action = returnglobal('action');}

if (!$action == "export")
	{
    echo $htmlheader;
	echo "<br /><table align='center' class='outlinetable'>
		<tr><th colspan='2'>VV Export</th></tr>
		<form method='post'>
		<tr>
		 <td align='right'>"._EXPORTSURVEY.":</td>
		 <td><input type='text' $slstyle size=4 value='$sid' name='sid' readonly></td>
		</tr>
		<tr>
		 <td align='right'>
		  Mode:
		 </td>
		 <td>
		  <select name='method' $slstyle>
		   <option value='deactivate' selected>Export then de-activate Survey</option>
		   <option value='none'>Export but leave results and survey active</option>
		  </select>
		 </td>
		</tr>
		<tr>
		 <td>&nbsp;
		 </td>
		 <td>
		  <input type='submit' value='"._EXPORTRESULTS."' $btstyle>&nbsp;
		 </td>
		</tr>
		 <input type='hidden' name='action' value='export'>
		</form>
		<tr><td colspan='2' align='center'>[<a href='$scriptname?sid=4'>"._B_ADMIN_BT."</a>]</td></tr>
		</table>";
	}
elseif (isset($sid) && $sid)
	{
	//Export is happening, first lets do the exporting
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
	
	//Now lets finalised according to the "method"
	if (!isset($method)) {$method=returnglobal('method');}
	switch($method)
		{
		case "deactivate": //Deactivate the survey 
			$date = date('YmdHi'); //'Hi' adds 24hours+minutes to name to allow multiple deactiviations in a day
			$result = mysql_list_tables($databasename);
			while ($row = mysql_fetch_row($result))
				{
				$tablelist[]=$row[0];
			    }
			if (in_array("{$dbprefix}tokens_{$_GET['sid']}", $tablelist))
				{
				$toldtable="{$dbprefix}tokens_{$_GET['sid']}";
				$tnewtable="{$dbprefix}old_tokens_{$_GET['sid']}_{$date}";
				$tdeactivatequery = "RENAME TABLE $toldtable TO $tnewtable";
				$tdeactivateresult = mysql_query($tdeactivatequery) or die ("\n\n"._ERROR."Couldn't deactivate tokens table because:<br />".mysql_error()."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>"._GO_ADMIN."</a>");
				}
			$oldtable="{$dbprefix}survey_{$_GET['sid']}";
			$newtable="{$dbprefix}old_{$_GET['sid']}_{$date}";
			$deactivatequery = "RENAME TABLE $oldtable TO $newtable";
			$deactivateresult = mysql_query($deactivatequery) or die ("\n\n"._ERROR."Couldn't deactivate because:<BR>".mysql_error()."<BR><BR><a href='$scriptname?sid={$_GET['sid']}'>Admin</a>");
			break;
		case "delete": //Delete the rows 
			break;
		default:
			
		} // switch
	}

?>