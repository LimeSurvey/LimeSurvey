<?php
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id$
 */

// Security Checked: POST, GET, SESSION, REQUEST, returnglobal, DB

/*
 * The SPSS DATA LIST / BEGIN DATA parser is rather simple minded, the number after the type
 * specifier identifies the field width (maximum number of characters to scan)
 * It will stop short of that number of characters, honouring quote delimited
 * space separated strings, however if the width is too small the remaining data in the current
 * line becomes part of the next column.  Since we want to restrict this script to ONE scan of
 * the data (scan & output at same time), the information needed to construct the
 * DATA LIST is held in the $fields array, while the actual data is written to a
 * to a temporary location, updating length (size) values in the $fields array as
 * the tmp file is generated (uses @fwrite's return value rather than strlen).
 * Final output renders $fields to a DATA LIST, and then stitches in the tmp file data.
 *
 * Optimization opportunities remain in the VALUE LABELS section, which runs a query / column
 */

include_once('login_check.php');
//for scale 1=nominal, 2=ordinal, 3=scale
$typeMap = array(
'5'=>Array('name'=>'5 Point Choice','size'=>1,'SPSStype'=>'F','Scale'=>3),
'B'=>Array('name'=>'Array (10 Point Choice)','size'=>1,'SPSStype'=>'F','Scale'=>3),
'A'=>Array('name'=>'Array (5 Point Choice)','size'=>1,'SPSStype'=>'F','Scale'=>3),
'F'=>Array('name'=>'Array (Flexible Labels)','size'=>1,'SPSStype'=>'F'),
'1'=>Array('name'=>'Array (Flexible Labels) Dual Scale','size'=>1,'SPSStype'=>'F'),
'H'=>Array('name'=>'Array (Flexible Labels) by Column','size'=>1,'SPSStype'=>'F'),
'E'=>Array('name'=>'Array (Increase, Same, Decrease)','size'=>1,'SPSStype'=>'F','Scale'=>2),
'C'=>Array('name'=>'Array (Yes/No/Uncertain)','size'=>1,'SPSStype'=>'F'),
'X'=>Array('name'=>'Boilerplate Question','size'=>1,'SPSStype'=>'A','hide'=>1),
'D'=>Array('name'=>'Date','size'=>10,'SPSStype'=>'SDATE'),
'G'=>Array('name'=>'Gender','size'=>1,'SPSStype'=>'F'),
'U'=>Array('name'=>'Huge Free Text','size'=>1,'SPSStype'=>'A'),
'I'=>Array('name'=>'Language Switch','size'=>1,'SPSStype'=>'A'),
'!'=>Array('name'=>'List (Dropdown)','size'=>1,'SPSStype'=>'F'),
'W'=>Array('name'=>'List (Flexible Labels) (Dropdown)','size'=>1,'SPSStype'=>'F'),
'Z'=>Array('name'=>'List (Flexible Labels) (Radio)','size'=>1,'SPSStype'=>'F'),
'L'=>Array('name'=>'List (Radio)','size'=>1,'SPSStype'=>'F'),
'O'=>Array('name'=>'List With Comment','size'=>1,'SPSStype'=>'F'),
'T'=>Array('name'=>'Long free text','size'=>1,'SPSStype'=>'A'),
'K'=>Array('name'=>'Multiple Numerical Input','size'=>1,'SPSStype'=>'F'),
'M'=>Array('name'=>'Multiple Options','size'=>1,'SPSStype'=>'F'),
'P'=>Array('name'=>'Multiple Options With Comments','size'=>1,'SPSStype'=>'F'),
'Q'=>Array('name'=>'Multiple Short Text','size'=>1,'SPSStype'=>'F'),
'N'=>Array('name'=>'Numerical Input','size'=>3,'SPSStype'=>'F','Scale'=>3),
'R'=>Array('name'=>'Ranking','size'=>1,'SPSStype'=>'F'),
'S'=>Array('name'=>'Short free text','size'=>1,'SPSStype'=>'F'),
'Y'=>Array('name'=>'Yes/No','size'=>1,'SPSStype'=>'F'),
':'=>Array('name'=>'Multi flexi numbers','size'=>1,'SPSStype'=>'F','Scale'=>3),
';'=>Array('name'=>'Multi flexi text','size'=>1,'SPSStype'=>'A'),
);

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
$filterstate = incompleteAnsFilterstate();
$spssver = returnglobal('spssver');
if (is_null($spssver)) {
	if (!isset($_SESSION['spssversion'])) {
		$_SESSION['spssversion'] = 2;	//Set default to 2, version 16 or up
	}
	$spssver = $_SESSION['spssversion'];
} else {
	$_SESSION['spssversion'] = $spssver;
}	

$length_varlabel = '255'; // Set the max text length of Variable Labels
$length_vallabel = '120'; // Set the max text length of Value Labels

switch ($spssver) {
	case 1:	//<16
		$length_data	 = '255'; // Set the max text length of the Value
		break;
	case 2:	//>=16
		$length_data	 = '16384'; // Set the max text length of the Value
		break;
	default:
		$length_data	 = '16384'; // Set the max text length of the Value

}		

$headerComment = '*$Rev$' . " $filterstate $spssver.\n";

if  (!isset($subaction))
{
	$exportspssoutput = browsemenubar($clang->gT('Export results'));
	$exportspssoutput .= "<div class='header'>".$clang->gT("Export result data to SPSS")."</div>\n";
	
	$selecthide="";
	$selectshow="";
	$selectinc="";
	switch ($filterstate) {
		case "inc":
		    $selectinc="selected='selected'";
		    break;
		case "filter":
			$selecthide="selected='selected'";
			break;
		default: 
			$selectshow="selected='selected'";
	}
	
	$exportspssoutput .= "<form action='$scriptname' id='exportspss' method='get'><ul>\n"
	."<li><label for='filterinc'>".$clang->gT("Data selection:")."</label><select id='filterinc' name='filterinc' onchange='this.form.submit();'>\n"
	."\t<option value='filter' $selecthide>".$clang->gT("Completed records only")."</option>\n"
	."\t<option value='show' $selectshow>".$clang->gT("All records")."</option>\n"
	."\t<option value='incomplete' $selectinc>".$clang->gT("Incomplete records only")."</option>\n"
	."</select><li>\n";
		
	$exportspssoutput .= "<li><label for='spssver'>".$clang->gT("SPSS version:")."</label><select id='spssver' name='spssver' onchange='this.form.submit();'>\n";
	if ($spssver == 1) $selected = "selected='selected'"; else $selected = "";
	$exportspssoutput .= "\t<option value='1' $selected>".$clang->gT("Prior to 16")."</option>\n";
	if ($spssver == 2) $selected = "selected='selected'"; else $selected = "";
	$exportspssoutput .= "\t<option value='2' $selected>".$clang->gT("16 or up")."</option>\n";
	$exportspssoutput .= "</select>\n";
	$exportspssoutput .= "<input type='hidden' name='sid' value='$surveyid' />\n"
	."<input type='hidden' name='action' value='exportspss' /></li></ul>\n"
    ."</form>\n";
	
	$exportspssoutput .= "<p style='width:100%;'><ul style='width:300px;margin:0 auto;'><li><a href='$scriptname?action=exportspss&amp;sid=$surveyid&amp;subaction=dlstructure'>".$clang->gT("Export SPSS syntax file")."</a></li><li>"
	."<a href='$scriptname?action=exportspss&amp;sid=$surveyid&amp;subaction=dldata'>".$clang->gT("Export SPSS data file")."</a></li></ul></p><br />\n"
	."<p><div class='messagebox'><div class='header'>".$clang->gT("Instructions for the impatient")."</div>"
	."<br/><ol style='margin:0 auto; font-size:8pt;'>"
	."<li>".$clang->gT("Download the data and the syntax file.")."</li>"
	."<li>".$clang->gT("Open the syntax file in SPSS in Unicode mode").".</li>"
	."<li>".$clang->gT("Edit the 4th line and complete the filename with a full path to the downloaded data file.")."</li>"
	."<li>".$clang->gT("Choose 'Run/All' from the menu to run the import.")."</li>"
	."</ol><p>"
	.$clang->gT("Your data should be imported now.").'</div>';
	
	
} else {
	// Get Base Language:

	$language = GetBaseLanguageFromSurveyID($surveyid);
	$clang = new limesurvey_lang($language);
	require_once ("export_data_functions.php");
}



if  ($subaction=='dldata') {
	header("Content-Type: application/download; charset=utf-8");
	header("Content-Disposition: attachment; filename=survey_".$surveyid."_SPSS_data_file.dat");
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: no-cache');

	sendcacheheaders();

	$na = "";
	spss_export_data($na);

	exit;
}


if  ($subaction=='dlstructure') {
	header("Content-Type: application/download; charset=utf-8");
	header("Content-Disposition: attachment; filename=survey_".$surveyid."_SPSS_syntax_file.sps");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: no-cache');

	sendcacheheaders();

	// Build array that has to be returned
	$fields = spss_fieldmap();

	//Now get the query string with all fields to export
	$query = spss_getquery();

	$result=db_execute_num($query) or safe_die("Couldn't get results<br />$query<br />".$connect->ErrorMsg()); //Checked
	$num_fields = $result->FieldCount();

	//Now we check if we need to adjust the size of the field or the type of the field
	while ($row = $result->FetchRow()) {
		$fieldno = 0;
		while ($fieldno < $num_fields)
		{
			//Performance improvement, don't recheck fields that have valuelabels
			if (!isset($fields[$fieldno]['answers'])) {
				$strTmp=mb_substr(strip_tags_full($row[$fieldno]), 0, $length_data);
				$len = mb_strlen($strTmp);
				if($len > $fields[$fieldno]['size']) $fields[$fieldno]['size'] = $len;

				if (trim($strTmp) != ''){
					if ($fields[$fieldno]['SPSStype']=='F' && (my_is_numeric($strTmp)===false || $fields[$fieldno]['size']>16))
					{
						$fields[$fieldno]['SPSStype']='A';
					}
				}
			}
			$fieldno++;
		}
	}

	/**
	 * End of DATA print out
	 *
	 * Now $fields contains accurate length data, and the DATA LIST can be rendered -- then the contents of the temp file can
	 * be sent to the client.
	 */
	echo $headerComment;
	if ($spssver == 2) echo "SET UNICODE=ON.\n";
	echo "GET DATA\n"
	."/TYPE=TXT\n"
	."/FILE='survey_".$surveyid."_SPSS_data_file.dat'\n"
	."/DELCASE=LINE\n"
	."/DELIMITERS=\",\"\n"
	."/QUALIFIER=\"'\"\n"
	."/ARRANGEMENT=DELIMITED\n"
	."/FIRSTCASE=1\n"
	."/IMPORTCASE=ALL\n"
	."/VARIABLES=";
	foreach ($fields as $field){
		if($field['SPSStype'] == 'DATETIME23.2') $field['size']='';
		if($field['SPSStype']=='F' && ($field['LStype'] == 'N' || $field['LStype']=='K')) {
			$field['size'].='.'.($field['size']-1);
		}
		if (!$field['hide']) echo "\n {$field['id']} {$field['SPSStype']}{$field['size']}";
	}
	echo ".\nCACHE.\n"
	."EXECUTE.\n";

	//Create the variable labels:
	echo "*Define Variable Properties.\n";
	foreach ($fields as $field) {
		if (!$field['hide']) echo "VARIABLE LABELS " . $field['id'] . " \"" . addslashes(mb_substr(strip_tags_full($field['VariableLabel']),0,$length_varlabel)) . "\".\n";
	}

	// Create our Value Labels!
	echo "*Define Value labels.\n";
	foreach ($fields as $field) {
		if (isset($field['answers'])) {
			$answers = $field['answers'];
			//print out the value labels!
			echo "VALUE LABELS  {$field['id']}\n";
			$i=0;
			foreach ($answers as $answer) {
				$i++;
				if ($field['SPSStype']=="F" && my_is_numeric($answer['code'])) {
					$str = "{$answer['code']}";
				} else {
					$str = "\"{$answer['code']}\"";
				}
				if ($i < count($answers)) {
					echo " $str \"{$answer['value']}\"\n";
				} else {
					echo " $str \"{$answer['value']}\".\n";
				}
			}
		}
	}

	foreach ($fields as $field){
		if($field['scale']!=='') {
			switch ($field['scale']) {
				case 2:
					echo "VARIABLE LEVEL {$field['id']}(ORDINAL).\n";
					break;
				case 3:
					echo "VARIABLE LEVEL {$field['id']}(SCALE).\n";
			}
		}
	}

	//Rename the Variables (in case somethings goes wrong, we still have the OLD values
	foreach ($fields as $field){
		if (isset($field['sql_name']) && $field['hide']===0) {
			$ftitle = $field['title'];
			if (!preg_match ("/^([a-z]|[A-Z])+.*$/", $ftitle)) {
				$ftitle = "q_" . $ftitle;
			}
			$ftitle = str_replace(array(" ","-",":",";","!","/","\\"), array("_","_hyph_","_dd_","_dc_","_excl_","_fs_","_bs_"), $ftitle);
			if ($ftitle != $field['title']) echo "* Variable name was incorrect and was changed from {$field['title']} to $ftitle .\n";
			echo "RENAME VARIABLE ( " . $field['id'] . " = " . $ftitle . " ).\n";
		}
	}
	exit;
}
?>