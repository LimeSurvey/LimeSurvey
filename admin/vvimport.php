<?php
/*
#############################################################
# >>> PHPSurveyor  										    #
#############################################################
# > Author:  Jason Cleeland									#
# > E-mail:  jason@cleeland.org								#
# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
# >          CARLTON SOUTH 3053, AUSTRALIA                  #
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


if (!isset($dbprefix)) {die ("Cannot run this script directly");}
if (!isset($noid)) {$noid=returnglobal('noid');}
if (!isset($insertstyle)) {$insertstyle=returnglobal('insert');}

include_once("login_check.php");

if ($subaction != "upload")
{
	//Make sure that the survey is active
	$tablelist = $connect->MetaTables();
	if (in_array("{$dbprefix}survey_$surveyid", $tablelist))
	{

		$vvoutput= "<br />
		<form enctype='multipart/form-data' method='post' action='admin.php?sid=$surveyid'>
		<table class='outlinetable' align='center'>		
		<tr><th colspan=2>".$clang->gT("Import a VV survey file")."</th></tr>
		<tr><td>".$clang->gT("File:")."</td><td><input type='file' size=50 name='the_file'></td></tr>
		<tr><td>".$clang->gT("Survey ID:")."</td><td><input type='text' size=10 name='sid' value='$surveyid' readonly></td></tr>
		<tr><td>".$clang->gT("Exclude record IDs?")."</td><td><input type='checkbox' name='noid' value='noid' checked=checked onchange='form.insertmethod.disabled=this.checked;' ></td></tr>
        <!-- this next item should only appear if noid is not checked -->
		<tr><td>".$clang->gT("When an imported record matches an existing record ID:")."</td><td><select id='insertmethod' name='insert' disabled='disabled'>
        <option value='error' selected='selected'>".$clang->gT("Report an error (and skip the new record).")."</option>
        <option value='renumber'>".$clang->gT("Renumber the new record.")."</option>
        <option value='ignore'>".$clang->gT("Ignore the new record.")."</option>
        <option value='replace'>".$clang->gT("Replace the existing record.")."</option>
        </select></td></tr>
		<tr><td colspan='2' align='center' ><input type='submit' value='".$clang->gT("Import")."'>
		<input type='hidden' name='action' value='vvimport' />
		<input type='hidden' name='subaction' value='upload' />
		</td></tr>
        <tr><td colspan='2' align='center'>[<a href='$scriptname?action=browse&amp;sid=$surveyid'>".$clang->gT("Return to Survey Administration")."</a>]</td></tr>
		</table>
		</form><br />";
	}
	else
	{
		$vvoutput .= "<br /><table class='outlinetable' align='center'>
		<tr><th colspan=2>Import a VV survey file</th></tr>
		<tr><td colspan='2' align='center'>
		<strong>".$clang->gT("Cannot import the VVExport file.")."</strong><br /><br />
		".("This survey is not active. You must activate the survey before attempting to import a VVexport file.")."<br /><br />
		[<a href='$scriptname?sid=4'>".$clang->gT("Return to Survey Administration")."</a>]
		</td></tr>
		</table>";		
	}


}
else
{
	$vvoutput = "<br /><table class='outlinetable' align='center'>
		<tr><th>Upload</th></tr>
		<tr><td align='center'>";
	$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];

	if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
	{
		$vvoutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
		$vvoutput .= $clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your admin folder.")."<br /><br />\n";
		$vvoutput .= "<input type='submit' value='".$clang->gT("Back to Response Import")."' onclick=\"window.open('$scriptname?action=vvimport&sid=$surveyid', '_top')\">\n";
		$vvoutput .= "</font></td></tr></table><br />&nbsp;\n";
		return;
	}
	// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY

	$vvoutput .= "<strong><font color='green'>".$clang->gT("Success")."</font></strong><br />\n";
	$vvoutput .= $clang->gT("File upload succeeded.")."<br /><br />\n";
	$vvoutput .= $clang->gT("Reading file..")."<br />\n";
	$handle = fopen($the_full_file_path, "r");
	while (!feof($handle))
	{
		$buffer = fgets($handle, 20480); //To allow for very long lines (up to 10k)
		$bigarray[] = $buffer;
	}
	fclose($handle);

	$surveytable = "{$dbprefix}survey_$surveyid";

	unlink($the_full_file_path); //delete the uploaded file
	unset($bigarray[0]); //delete the first line

	$fieldnames=explode("\t", trim($bigarray[1]));
	$fieldcount=count($fieldnames)-1;
	while (trim($fieldnames[$fieldcount]) == "") // get rid of blank entries
	{
		unset($fieldnames[$fieldcount]);
		$fieldcount--;
	}

	$realfieldnames = array_values($connect->MetaColumnNames($surveytable, true));
	if ($noid == "noid") {unset($realfieldnames[0]);}
	unset($bigarray[1]); //delete the second line

	//	$vvoutput .= "<tr><td valign='top'><strong>Import Fields:<pre>"; print_r($fieldnames); $vvoutput .= "</pre></td>";
	//	$vvoutput .= "<td valign='top'><strong>Actual Fields:<pre>"; print_r($realfieldnames); $vvoutput .= '</pre></td></tr>';

	//See if any fields in the import file don't exist in the active survey
	$missing = array_diff($fieldnames, $realfieldnames);
	if (is_array($missing) && count($missing) > 0)
	{
		foreach ($missing as $key=>$val)
		{
			$donotimport[]=$key;
			unset($fieldnames[$key]);
		}
	}
	$importcount=0;
	$recordcount=0;
	foreach($bigarray as $row)
	{
		if (trim($row) != "")
		{
			$recordcount++;
			$fieldvalues=explode("\t", str_replace("\n", "", $row), $fieldcount+1);
			// Excel likes to quote fields sometimes. =(
			$fieldvalues=preg_replace('/^"(.*)"$/s','\1',$fieldvalues);
			// careful about the order of these arrays:
			// lbrace has to be substituted *last*
			$fieldvalues=str_replace(array("{newline}",
			"{cr}",
			"{tab}",
			"{quote}",
			"{lbrace}"),
			array("\n",
			"\r",
			"\t",
			"\"",
			"{"),
			$fieldvalues);
			if (isset($donotimport)) //remove any fields which no longer exist
			{
				foreach ($donotimport as $not)
				{
					unset($fieldvalues[$not]);
				}
			}
			// sometimes columns with nothing in them get omitted by excel
			while (count($fieldnames) > count($fieldvalues))
			{
				$fieldvalues[]="";
			}
			// sometimes columns with nothing in them get added by excel
			while (count($fieldnames) < count($fieldvalues) &&
			trim($fieldvalues[count($fieldvalues)-1])=="")
			{
				unset($fieldvalues[count($fieldvalues)-1]);
			}
			// make this safe for mysql (*after* we undo first excel's
			// and then our escaping).
			$fieldvalues=array_map('db_quote',$fieldvalues);
			// okay, now we should be good to go.
			if ($insertstyle=="ignore" && !$noid)
			$insert = "INSERT IGNORE";
			else if ($insertstyle=="replace" && !$noid)
			$insert = "REPLACE";
			else $insert = "INSERT";
			$insert .= " INTO $surveytable\n";
			$insert .= "(".implode(", ", $fieldnames).")\n";
			$insert .= "VALUES\n";
			$insert .= "('".implode("', '", $fieldvalues)."')\n";
			if (!$result = $connect->Execute($insert))
			{
				$idkey = array_search('id',$fieldnames);
				if ($insertstyle=="renumber" && $idkey!==FALSE)
				{
					// try again, without the 'id' field.
					unset($fieldnames[$idkey]);
					unset($fieldvalues[$idkey]);
					$insert = "INSERT INTO $surveytable\n";
					$insert .= "(".implode(", ", $fieldnames).")\n";
					$insert .= "VALUES\n";
					$insert .= "('".implode("', '", $fieldvalues)."')\n";
					$result = $connect->Execute($insert);
				}
			}
			if (!$result)
			{
				$vvoutput .= "<table align='center' class='outlintable'>
				      <tr><td>".$clang->gT("Import Failed on Record")." $recordcount ".$clang->gT("because")." [".$connect->ErrorMsg()."]
					  </td></tr></table>\n";
			}
			else
			{
				$importcount++;
			}

		}
	}

	if ($noid == "noid" || $insertstyle == "renumber")
	{
		$vvoutput .= "<br /><i><strong><font color='red'>".$clang->gT("Important Note:")."<br />".$clang->gT("Do NOT refresh this page, as this will import the file again and produce duplicates")."</font></strong></i><br /><br />";
	}
	$vvoutput .= $clang->gT("Total records imported:")." ".$importcount."<br /><br />";
	$vvoutput .= "[<a href='admin.php?action=browse&amp;sid=$surveyid'>".$clang->gT("Browse Responses")."</a>]";
	$vvoutput .= "</td></tr></table><br />&nbsp;";
}
?>
