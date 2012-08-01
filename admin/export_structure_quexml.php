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
 * $Id: export_structure_quexml.php 11697 2011-12-20 04:17:59Z azammitdcarf $
 */


//Ensure script is not run directly, avoid path disclosure
include_once("login_check.php");



if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

if (isset($surveyprintlang) && !empty($surveyprintlang))
	$quexmllang = $surveyprintlang;
	else
	$quexmllang=GetBaseLanguageFromSurveyID($surveyid);

$qlang = new limesurvey_lang($quexmllang);

if (!$surveyid)
{
	echo $htmlheader
		."<br />\n"
		."<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._EXPORTSURVEY."</strong></td></tr>\n"
		."\t<tr><td align='center'>\n"
		."$setfont<br /><strong><font color='red'>"
		._ERROR."</font></strong><br />\n"
		._ES_NOSID."<br />\n"
		."<br /><input type='submit' $btstyle value='"
		._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\">\n"
		."\t</td></tr>\n"
		."</table>\n"
		."</body></html>\n";
	exit;
}





function cleanup($string)
{
	return html_entity_decode(trim(strip_tags(str_ireplace("<br />","\n",$string),'<p><b><u><i><em>')),ENT_QUOTES,'UTF-8');
}


function create_free($f,$len,$lab="")
{
	global $dom;
	$free = $dom->createElement("free");

	$format = $dom->createElement("format",cleanup($f));

	$length = $dom->createElement("length",cleanup($len));

	$label = $dom->createElement("label",cleanup($lab));

	$free->appendChild($format);
	$free->appendChild($length);
	$free->appendChild($label);


	return $free;
}


function fixed_array($array)
{
	global $dom;
	$fixed = $dom->createElement("fixed");

	foreach ($array as $key => $v)
	{
		$category = $dom->createElement("category");

		$label = $dom->createElement("label",cleanup($key));

		$value= $dom->createElement("value",cleanup($v));

		$category->appendChild($label);
		$category->appendChild($value);

		$fixed->appendChild($category);
	}


	return $fixed;
}

/**
 * Calculate if this item should have a skipTo element attached to it
 * 
 * @param mixed $qid   
 * @param mixed $value 
 * 
 * @return bool|string Text of item to skip to otherwise false if nothing to skip to
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-28
 * @TODO Correctly handle conditions in a database agnostic way
*/
function skipto($qid,$value,$cfieldname = "")
{
	return false;
}



function create_fixed($qid,$rotate=false,$labels=true,$scale=0,$other=false,$varname="")
{
	global $dom;
	global $connect ;
	global $dbprefix ; 
	global $quexmllang;
	global $qlang;

	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	if ($labels)
		$Query = "SELECT * FROM {$dbprefix}labels WHERE lid = $labels  AND language='$quexmllang' ORDER BY sortorder ASC";
	else
		$Query = "SELECT code,answer as title,sortorder FROM {$dbprefix}answers WHERE qid = $qid AND scale_id = $scale  AND language='$quexmllang' ORDER BY sortorder ASC";

	$QueryResult = db_execute_assoc($Query);

	$fixed = $dom->createElement("fixed");

	$nextcode = "";

	while ($Row = $QueryResult->FetchRow())
	{
		$category = $dom->createElement("category");

		$label = $dom->createElement("label",cleanup($Row['title']));

		$value= $dom->createElement("value",cleanup($Row['code']));

		$category->appendChild($label);
		$category->appendChild($value);

		$st = skipto($qid,$Row['code']);
		if ($st !== false)
		{
			$skipto = $dom->createElement("skipTo",$st);
			$category->appendChild($skipto);	
		}


		$fixed->appendChild($category);
		$nextcode = $Row['code'];
	}

	if ($other)
	{
		$category = $dom->createElement("category");

		$label = $dom->createElement("label",get_length($qid,"other_replace_text",$qlang->gT("Other")));

		$value= $dom->createElement("value",'-oth');

		$category->appendChild($label);
		$category->appendChild($value);	    

		$contingentQuestion = $dom->createElement("contingentQuestion");
		$length = $dom->createElement("length",24);
		$text = $dom->createElement("text",get_length($qid,"other_replace_text",$qlang->gT("Other")));

		$contingentQuestion->appendChild($text);
		$contingentQuestion->appendChild($length);
		$contingentQuestion->setAttribute("varName",$varname . 'other');

		$category->appendChild($contingentQuestion);

		$fixed->appendChild($category);
	}

	if ($rotate) $fixed->setAttribute("rotate","true");

	return $fixed;
}

function get_length($qid,$attribute,$default)
{
	global $dom;
	global $dbprefix;
	global $connect ;
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	$Query = "SELECT value FROM {$dbprefix}question_attributes WHERE qid = $qid AND attribute = '$attribute'";
	//$QueryResult = mysql_query($Query) or die ("ERROR: $QueryResult<br />".mysql_error());
	$QueryResult = db_execute_assoc($Query);

	$Row = $QueryResult->FetchRow();
	if ($Row && !empty($Row['value']))
		return $Row['value'];
	else
		return $default;

}


function create_multi(&$question,$qid,$varname,$scale_id = false,$free = false,$other = false)
{
	global $dom;
	global $dbprefix;
	global $connect ;
	global $quexmllang ;
	global $surveyid;
	global $qlang;

	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	$Query = "SELECT * FROM {$dbprefix}questions WHERE parent_qid = $qid  AND language='$quexmllang' ";
	if ($scale_id != false) $Query .= " AND scale_id = $scale_id ";
	$Query .= " ORDER BY question_order ASC";
	//$QueryResult = mysql_query($Query) or die ("ERROR: $QueryResult<br />".mysql_error());
	$QueryResult = db_execute_assoc($Query);

	$nextcode = "";

	while ($Row = $QueryResult->FetchRow())
	{
		$response = $dom->createElement("response");
		if ($free == false)
		{
			$fixed = $dom->createElement("fixed");
			$category = $dom->createElement("category");

			$label = $dom->createElement("label",cleanup($Row['question']));

			$value= $dom->createElement("value",1);
			$nextcode = $Row['title'];

			$category->appendChild($label);
			$category->appendChild($value);

			$st = skipto($qid,'Y'," AND c.cfieldname LIKE '+$surveyid" . "X" . $Row['gid'] . "X" . $qid . $Row['title'] . "' ");
			if ($st !== false)
			{
				$skipto = $dom->createElement("skipTo",$st);
				$category->appendChild($skipto);	
			}


			$fixed->appendChild($category);

			$response->appendChild($fixed);
		}
		else
			$response->appendChild(create_free($free['f'],$free['len'],$Row['question']));

		$response->setAttribute("varName",$varname . cleanup($Row['title']));

		$question->appendChild($response);
	}

	if ($other && $free==false)
	{
		$response = $dom->createElement("response");
		$fixed = $dom->createElement("fixed");
		$category = $dom->createElement("category");

		$label = $dom->createElement("label",get_length($qid,"other_replace_text",$qlang->gT("Other")));

		$value= $dom->createElement("value",1);

		//Get next code
		if (is_numeric($nextcode))
			$nextcode++;
		else if (is_string($nextcode))
			$nextcode = chr(ord($nextcode) + 1);

		$category->appendChild($label);
		$category->appendChild($value);	    

		$contingentQuestion = $dom->createElement("contingentQuestion");
		$length = $dom->createElement("length",24);
		$text = $dom->createElement("text",get_length($qid,"other_replace_text",$qlang->gT("Other")));

		$contingentQuestion->appendChild($text);
		$contingentQuestion->appendChild($length);
		$contingentQuestion->setAttribute("varName",$varname . 'other');

		$category->appendChild($contingentQuestion);

		$fixed->appendChild($category);
		$response->appendChild($fixed);
		$response->setAttribute("varName",$varname . cleanup($nextcode));

		$question->appendChild($response);
	}




	return;

}

function create_subQuestions(&$question,$qid,$varname,$use_answers = false)
{
	global $dom;
	global $dbprefix;
	global $connect ;
	global $quexmllang ;
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	if ($use_answers)
		$Query = "SELECT answer as question, code as title FROM {$dbprefix}answers WHERE qid = $qid  AND language='$quexmllang' ORDER BY sortorder ASC";
	else
		$Query = "SELECT * FROM {$dbprefix}questions WHERE parent_qid = $qid and scale_id = 0  AND language='$quexmllang' ORDER BY question_order ASC";
	$QueryResult = db_execute_assoc($Query);
	while ($Row = $QueryResult->FetchRow())
	{
		$subQuestion = $dom->createElement("subQuestion");
		$text = $dom->createElement("text",cleanup($Row['question']));
		$subQuestion->appendChild($text);
		$subQuestion->setAttribute("varName",$varname . cleanup($Row['title']));
		$question->appendChild($subQuestion);
	}

	return;
}

global $dbprefix;
global $connect ;
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$dom = new DOMDocument('1.0','UTF-8'); 


//Title and survey id
$questionnaire = $dom->createElement("questionnaire");

$Query = "SELECT * FROM {$dbprefix}surveys,{$dbprefix}surveys_languagesettings WHERE sid=$surveyid and surveyls_survey_id=sid and surveyls_language='".$quexmllang."'";
$QueryResult = db_execute_assoc($Query);
$Row = $QueryResult->FetchRow();
$questionnaire->setAttribute("id", $Row['sid']);
$title = $dom->createElement("title",cleanup($Row['surveyls_title']));
$questionnaire->appendChild($title);

//investigator and datacollector
$investigator = $dom->createElement("investigator");
$name = $dom->createElement("name");
$name = $dom->createElement("firstName");
$name = $dom->createElement("lastName");
$dataCollector = $dom->createElement("dataCollector");

$questionnaire->appendChild($investigator);
$questionnaire->appendChild($dataCollector);

//questionnaireInfo == welcome
if (!empty($Row['surveyls_welcometext']))
{
	$questionnaireInfo = $dom->createElement("questionnaireInfo");
	$position = $dom->createElement("position","before");
	$text = $dom->createElement("text",cleanup($Row['surveyls_welcometext']));
	$administration = $dom->createElement("administration","self");
	$questionnaireInfo->appendChild($position);
	$questionnaireInfo->appendChild($text);
	$questionnaireInfo->appendChild($administration);
	$questionnaire->appendChild($questionnaireInfo);
}

if (!empty($Row['surveyls_endtext']))
{
	$questionnaireInfo = $dom->createElement("questionnaireInfo");
	$position = $dom->createElement("position","after");
	$text = $dom->createElement("text",cleanup($Row['surveyls_endtext']));
	$administration = $dom->createElement("administration","self");
	$questionnaireInfo->appendChild($position);
	$questionnaireInfo->appendChild($text);
	$questionnaireInfo->appendChild($administration);
	$questionnaire->appendChild($questionnaireInfo);
}

//section == group


$Query = "SELECT * FROM {$dbprefix}groups WHERE sid=$surveyid AND language='$quexmllang' order by group_order ASC";
$QueryResult = db_execute_assoc($Query);

//for each section
while ($Row = $QueryResult->FetchRow())
{
	$gid = $Row['gid'];

	$section = $dom->createElement("section");

	if (!empty($Row['group_name']))
	{
		$sectionInfo = $dom->createElement("sectionInfo");
		$position = $dom->createElement("position","title");
		$text = $dom->createElement("text",cleanup($Row['group_name']));
		$administration = $dom->createElement("administration","self");
		$sectionInfo->appendChild($position);
		$sectionInfo->appendChild($text);
		$sectionInfo->appendChild($administration);
		$section->appendChild($sectionInfo);
	}


	if (!empty($Row['description']))
	{
		$sectionInfo = $dom->createElement("sectionInfo");	
		$position = $dom->createElement("position","before");
		$text = $dom->createElement("text",cleanup($Row['description']));
		$administration = $dom->createElement("administration","self");
		$sectionInfo->appendChild($position);
		$sectionInfo->appendChild($text);
		$sectionInfo->appendChild($administration);
		$section->appendChild($sectionInfo);
	}

	$section->setAttribute("id", $gid);

	//boilerplate questions convert to sectionInfo elements
	$Query = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid = $gid AND type LIKE 'X'  AND language='$quexmllang' ORDER BY question_order ASC";
	$QR = db_execute_assoc($Query);
	while ($RowQ = $QR->FetchRow())
	{
		$sectionInfo = $dom->createElement("sectionInfo");
		$position = $dom->createElement("position","before");
		$text = $dom->createElement("text",cleanup($RowQ['question']));
		$administration = $dom->createElement("administration","self");
		$sectionInfo->appendChild($position);
		$sectionInfo->appendChild($text);
		$sectionInfo->appendChild($administration);

		$section->appendChild($sectionInfo);
	}



	//foreach question
	$Query = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid = $gid AND parent_qid=0 AND language='$quexmllang' AND type NOT LIKE 'X' ORDER BY question_order ASC";
	$QR = db_execute_assoc($Query);
	while ($RowQ = $QR->FetchRow())
	{
		$question = $dom->createElement("question");
		$type = $RowQ['type'];
		$qid = $RowQ['qid'];

		$other = false;
		if ($RowQ['other'] == 'Y') $other = true;

		//create a new text element for each new line
		$questiontext = explode('<br />',$RowQ['question']);
		foreach ($questiontext as $qt)
		{
			$txt = cleanup($qt);
			if (!empty($txt))
			{
				$text = $dom->createElement("text",$txt);
				$question->appendChild($text);
			}
		}


		//directive
		if (!empty($RowQ['help']))
		{
			$directive = $dom->createElement("directive");
			$position = $dom->createElement("position","during");
			$text = $dom->createElement("text",cleanup($RowQ['help']));
			$administration = $dom->createElement("administration","self");

			$directive->appendChild($position);
			$directive->appendChild($text);
			$directive->appendChild($administration);

			$question->appendChild($directive);
		}

		$response = $dom->createElement("response");
		$sgq = $surveyid . "X" . $gid . "X" . $qid;
		$response->setAttribute("varName",$sgq);

		switch ($type)
		{
			case "X": //BOILERPLATE QUESTION - none should appear

				break;
			case "5": //5 POINT CHOICE radio-buttons
				$response->appendChild(fixed_array(array("1" => 1,"2" => 2,"3" => 3,"4" => 4,"5" => 5)));
			$question->appendChild($response);
			break;
			case "D": //DATE
				$response->appendChild(create_free("date","8",""));
			$question->appendChild($response);
			break;
			case "L": //LIST drop-down/radio-button list
				$response->appendChild(create_fixed($qid,false,false,0,$other,$sgq));
			$question->appendChild($response);
			break;
			case "!": //List - dropdown
				$response->appendChild(create_fixed($qid,false,false,0,$other,$sgq));
			$question->appendChild($response);
			break;
			case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
				$response->appendChild(create_fixed($qid,false,false,0,$other,$sgq));
			$question->appendChild($response);
			//no comment - this should be a separate question
			break;
			case "R": //RANKING STYLE
				create_subQuestions($question,$qid,$sgq,true);
			$Query = "SELECT COUNT(*) as sc FROM {$dbprefix}answers WHERE qid = $qid AND language='$quexmllang' ";
			$QRE = db_execute_assoc($Query);
			//$QRE = mysql_query($Query) or die ("ERROR: $QRE<br />".mysql_error());
			//$QROW = mysql_fetch_assoc($QRE);
			$QROW = $QRE->FetchRow();
			$response->appendChild(create_free("integer",strlen($QROW['sc']),""));
			$question->appendChild($response);
			break;
			case "M": //Multiple choice checkbox
				create_multi($question,$qid,$sgq,false,false,$other);
			break;
			case "P": //Multiple choice with comments checkbox + text
				//Not yet implemented
				create_multi($question,$qid,$sgq,false,false,$other);
			//no comments added
			break;
			case "Q": //MULTIPLE SHORT TEXT
				create_subQuestions($question,$qid,$sgq);
			$response->appendChild(create_free("text",get_length($qid,"maximum_chars","10"),""));
			$question->appendChild($response);
			break;
			case "K": //MULTIPLE NUMERICAL
				create_subQuestions($question,$qid,$sgq);
			$response->appendChild(create_free("integer",get_length($qid,"maximum_chars","10"),""));
			$question->appendChild($response);
			break;
			case "N": //NUMERICAL QUESTION TYPE
				$response->appendChild(create_free("integer",get_length($qid,"maximum_chars","10"),get_length($qid,"prefix","")));
			$question->appendChild($response);
			break;
			case "S": //SHORT FREE TEXT
				$response->appendChild(create_free("text",get_length($qid,"maximum_chars","240"),get_length($qid,"prefix","")));
			$question->appendChild($response);
			break;
			case "T": //LONG FREE TEXT
				$response->appendChild(create_free("longtext",get_length($qid,"display_rows","40"),get_length($qid,"prefix","")));
			$question->appendChild($response);
			break;
			case "U": //HUGE FREE TEXT
				$response->appendChild(create_free("longtext",get_length($qid,"display_rows","80"),get_length($qid,"prefix","")));
			$question->appendChild($response);
			break;
			case "Y": //YES/NO radio-buttons
				$response->appendChild(fixed_array(array($qlang->gT("Yes") => 'Y',$qlang->gT("No") => 'N')));
			$question->appendChild($response);
			break;
			case "G": //GENDER drop-down list
				$response->appendChild(fixed_array(array($qlang->gT("Female") => 'F',$qlang->gT("Male") => 'M')));
			$question->appendChild($response);
			break;
			case "A": //ARRAY (5 POINT CHOICE) radio-buttons
				create_subQuestions($question,$qid,$sgq);
			$response->appendChild(fixed_array(array("1" => 1,"2" => 2,"3" => 3,"4" => 4,"5" => 5)));
			$question->appendChild($response);
			break;
			case "B": //ARRAY (10 POINT CHOICE) radio-buttons
				create_subQuestions($question,$qid,$sgq);
			$response->appendChild(fixed_array(array("1" => 1,"2" => 2,"3" => 3,"4" => 4,"5" => 5,"6" => 6,"7" => 7,"8" => 8,"9" => 9,"10" => 10)));
			$question->appendChild($response);
			break;
			case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
				create_subQuestions($question,$qid,$sgq);
			$response->appendChild(fixed_array(array($qlang->gT("Yes") => 'Y',$qlang->gT("Uncertain") => 'U',$qlang->gT("No") => 'N')));
			$question->appendChild($response);
			break;
			case "E": //ARRAY (Increase/Same/Decrease) radio-buttons
				create_subQuestions($question,$qid,$sgq);
			$response->appendChild(fixed_array(array($qlang->gT("Increase") => 'I',$qlang->gT("Same") => 'S',$qlang->gT("Decrease") => 'D')));
			$question->appendChild($response);
			break;
			case "F": //ARRAY (Flexible) - Row Format
				//select subQuestions from answers table where QID
				create_subQuestions($question,$qid,$sgq);
			$response->appendChild(create_fixed($qid,false,false,0,$other,$sgq));
			$question->appendChild($response);
			//select fixed responses from
			break;
			case "H": //ARRAY (Flexible) - Column Format
				create_subQuestions($question,$RowQ['qid'],$sgq);
			$response->appendChild(create_fixed($qid,true,false,0,$other,$sgq));
			$question->appendChild($response);
			break;
			case "1": //Dualscale multi-flexi array
				//select subQuestions from answers table where QID
				create_subQuestions($question,$qid,$sgq);
			$response = $dom->createElement("response");
			$response->appendChild(create_fixed($qid,false,false,0,$other,$sgq)); 
			$response2 = $dom->createElement("response");  
			$response2->setAttribute("varName",cleanup($sgq) . "_2");
			$response2->appendChild(create_fixed($qid,false,false,1,$other,$sgq));   
			$question->appendChild($response);
			$question->appendChild($response2);  
			break;
			case ":": //multi-flexi array numbers
				create_subQuestions($question,$qid,$sgq);
			//get multiflexible_checkbox - if set then each box is a checkbox (single fixed response)
			$mcb  = get_length($qid,'multiflexible_checkbox',-1);
			if ($mcb != -1)
				create_multi($question,$qid,$sgq,1);
			else
			{
				//get multiflexible_max - if set then make boxes of max this width
				$mcm = strlen(get_length($qid,'multiflexible_max',1));
				create_multi($question,$qid,$sgq,1,array('f' => 'integer', 'len' => $mcm, 'lab' => ''));
			}
			break;
			case ";": //multi-flexi array text
				create_subQuestions($question,$qid,$sgq);
			//foreach question where scale_id = 1 this is a textbox
			create_multi($question,$qid,$sgq,1,array('f' => 'text', 'len' => 10, 'lab' => ''));
			break;
			case "^": //SLIDER CONTROL - not supported
				$response->appendChild(fixed_array(array("NOT SUPPORTED:$type" => 1)));
			$question->appendChild($response);
			break;
		} //End Switch




		$section->appendChild($question);
	}


	$questionnaire->appendChild($section);
}


$dom->appendChild($questionnaire);

$dom->formatOutput = true;

$quexml =  $dom->saveXML();

if (!(isset($noheader) && $noheader == true))
{
	$fn = "survey_{$surveyid}_{$quexmllang}.xml";
	header("Content-Type: text/xml");
	header("Content-Disposition: attachment; filename=$fn");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Pragma: public");                          // HTTP/1.0

	echo $quexml;	
	exit();
}
?>
