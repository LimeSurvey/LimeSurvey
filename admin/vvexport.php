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
*/

//Exports all responses to a survey in special "Verified Voting" format.


if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}
include_once("login_check.php");

$sumquery5 = "SELECT b.* FROM {$dbprefix}surveys AS a INNER JOIN {$dbprefix}surveys_rights AS b ON a.sid = b.sid WHERE a.sid=$surveyid AND b.uid = ".$_SESSION['loginID']; //Getting rights for this survey and user
$sumresult5 = db_execute_assoc($sumquery5);
$sumrows5 = $sumresult5->FetchRow();

if ($sumrows5['export'] != "1")
{
	return;
}
if (!$subaction == "export")
{
	if (incompleteAnsFilterstate() === true)
	{
		$selecthide="selected='selected'";
		$selectshow="";
	}
	else
	{
		$selecthide="";
		$selectshow="selected='selected'";
	}

	$vvoutput = "<br /><form method='post' action='admin.php?action=vvexport&sid=$surveyid'>"
    	."<table align='center' class='outlinetable'>"
        ."<tr><th colspan='2'>".$clang->gT("Export a VV survey file")."</th></tr>"
        ."<tr>"
        ."<td align='right'>".$clang->gT("Export Survey").":</td>"
        ."<td><input type='text' size='10' value='$surveyid' name='sid' readonly='readonly' /></td>"
        ."</tr>"
	."<tr>"
	."<td align='right'>".$clang->gT("Filter incomplete answers")." </td>"
	."<td><select name='filterinc'>\n"
	."\t<option value='filter' $selecthide>".$clang->gT("Enable")."</option>\n"
	."\t<option value='show' $selectshow>".$clang->gT("Disable")."</option>\n"
	."</select></td>\n"
        ."<tr>"
        ."<td colspan='2' align='center'>"
        ."<input type='submit' value='".$clang->gT("Export Responses")."' />&nbsp;"
        ."<input type='hidden' name='subaction' value='export' />"
        ."</td>"
        ."</tr>"
        ."<tr><td colspan='2' align='center'>[<a href='$scriptname?action=browse&amp;sid=$surveyid'>".$clang->gT("Return to Survey Administration")."</a>]</td></tr>"
        ."</table>";
}
elseif (isset($surveyid) && $surveyid)
{
	//Export is happening
	header("Content-Disposition: attachment; filename=vvexport_$surveyid.csv");
	header("Content-type: text/comma-separated-values; charset=UTF-8");
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
		//$vvoutput .= "<pre>";print_r($fielddata);$vvoutput .= "</pre>";
		if (count($fielddata) < 1) {$firstline.=$field;}
		else
		//{$firstline.=str_replace("\n", " ", str_replace("\t", "   ", strip_tags($fielddata['question'])));}
		{$firstline.=preg_replace('/\s+/',' ',strip_tags($fielddata['question']));}
		$firstline .= $s;
		$secondline .= $field.$s;
	}
	$vvoutput = $firstline."\n";
	$vvoutput .= $secondline."\n";
	$query = "SELECT * FROM $surveytable";
	if (incompleteAnsFilterstate() === true)
	{
		$query .= " WHERE submitdate is not null ";
	}
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
			// yay!  that nasty soab won't hurt us now!
			$sun[]=$value;
		}
		$beach=implode($s, $sun);
		$vvoutput .= $beach;
		unset($sun);
		$vvoutput .= "\n";
	}
	echo $vvoutput;
	exit;

	//$vvoutput .= "<pre>$firstline</pre>";
	//$vvoutput .= "<pre>$secondline</pre>";
	//$vvoutput .= "<pre>"; print_r($fieldnames); $vvoutput .= "</pre>";
	//$vvoutput .= "<pre>"; print_r($fieldmap); $vvoutput .= "</pre>";

}

?>
