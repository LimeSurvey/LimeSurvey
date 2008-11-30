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

//Ensure script is not run directly, avoid path disclosure
include_once("login_check.php");
include_once(dirname(__FILE__)."/classes/tcpdf/extensiontcpdf.php");    

$printablesurveyoutput="<?xml version=\"1.0\"?><!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n"
."<html>\n<head>\n"
. "<!--[if lt IE 7]>\n"
. "<script defer type=\"text/javascript\" src=\"scripts/pngfix.js\"></script>\n"
. "<![endif]-->\n"
. "<title>$sitename</title>\n"
. "<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />\n"
. "<script type=\"text/javascript\" src=\"scripts/tabpane/js/tabpane.js\"></script>\n"
. "<script type=\"text/javascript\" src=\"scripts/tooltips.js\"></script>\n"
. "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"../scripts/calendar/calendar-blue.css\" title=\"win2k-cold-1\" />\n"
. "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/$admintheme/printablestyle.css\" />\n"
. "<script type=\"text/javascript\" src=\"../scripts/calendar/calendar.js\"></script>\n"
. "<script type=\"text/javascript\" src=\"../scripts/calendar/lang/calendar-".$_SESSION['adminlang'].".js\"></script>\n"
. "<script type=\"text/javascript\" src=\"../scripts/calendar/calendar-setup.js\"></script>\n"
. "<script type=\"text/javascript\" src=\"scripts/validation.js\"></script>\n"
. "</head>\n<body>\n";

$surveyid = $_GET['sid'];

// PRESENT SURVEY DATAENTRY SCREEN
if(isset($_POST['printableexport']))
{
    $pdf = new PDF ($pdforientation,'mm','A4');
    $pdf->SetFont($pdfdefaultfont,'',$pdffontsize);
    $pdf->AddPage();
}
// Set the language of the survey, either from GET parameter of session var
if (isset($_GET['lang']))
{
	$_GET['lang'] = preg_replace("/[^a-zA-Z0-9-]/", "", $_GET['lang']);
	if ($_GET['lang']) $surveyprintlang = $_GET['lang'];
} else
{
	$surveyprintlang=GetbaseLanguageFromSurveyid($surveyid);
}

// Setting the selected language for printout
$clang = new limesurvey_lang($surveyprintlang);

$desquery = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid) WHERE sid=$surveyid and surveyls_language=".$connect->qstr($surveyprintlang); //Getting data for this survey

$desresult = db_execute_assoc($desquery);
while ($desrow = $desresult->FetchRow())
{
	$surveyname = $desrow['surveyls_title'];
	$surveydesc = $desrow['surveyls_description'];
	$surveyactive = $desrow['active'];
	$surveytable = db_table_name("survey_".$desrow['sid']);
	$surveyuseexpiry = $desrow['useexpiry'];
	$surveyexpirydate = $desrow['expires'];
	$surveyfaxto = $desrow['faxto'];
}
if (!isset($surveyfaxto) || !$surveyfaxto and isset($surveyfaxnumber))
{
	$surveyfaxto=$surveyfaxnumber; //Use system fax number if none is set in survey.
}


$printablesurveyoutput .="<table width='100%' cellspacing='0'>\n";
if(isset($usepdfexport) && $usepdfexport == 1 && !in_array($surveyprintlang,$notsupportlanguages))
{
    $printablesurveyoutput .="<tr><td colspan='3' align='center'>"
                            ."<form action='$scriptname?action=showprintablesurvey&amp;sid=".$surveyid."&amp;lang=".$surveyprintlang."' method='post'>\n";
    $printablesurveyoutput .="<input type='submit' value='".$clang->gT("PDF Export")."'/>";
    $printablesurveyoutput .="<input type='hidden' name='checksessionbypost' value='".$_SESSION['checksessionpost']."'/>";
    $printablesurveyoutput .="<input type='hidden' name='printableexport' value='true'/>";
    $printablesurveyoutput .="</form>";
}
if(isset($_POST['printableexport'])){$pdf->titleintopdf($surveyname,$surveydesc);}
$printablesurveyoutput .="\t<tr>\n";
$printablesurveyoutput .="\t\t<td colspan='3' align='center'>\n";
$printablesurveyoutput .="\t\t\t<table border='1' style='border-collapse: collapse; border-color: #111111; width: 100%'>\n";
$printablesurveyoutput .="\t\t\t\t<tr><td align='center'>\n";
$printablesurveyoutput .="\t\t\t\t\t<font size='5' face='verdana'><strong>$surveyname</strong></font>\n";
$printablesurveyoutput .="\t\t\t\t\t<br />$surveydesc\n";
$printablesurveyoutput .="\t\t\t\t</td></tr>\n";
$printablesurveyoutput .="\t\t\t</table>\n";
$printablesurveyoutput .="\t\t</td>\n";
$printablesurveyoutput .="\t</tr>\n";
// SURVEY NAME AND DESCRIPTION TO GO HERE

$fieldmap=createFieldMap($surveyid);

$degquery = "SELECT * FROM ".db_table_name("groups")." WHERE sid='{$surveyid}' AND language='{$surveyprintlang}' ORDER BY ".db_table_name("groups").".group_order";
$degresult = db_execute_assoc($degquery);
// GROUP NAME
while ($degrow = $degresult->FetchRow())
{
	$deqquery = "SELECT * FROM ".db_table_name("questions")." WHERE sid=$surveyid AND gid={$degrow['gid']} AND language='{$surveyprintlang}' AND TYPE<>'I' ORDER BY question_order";
	$deqresult = db_execute_assoc($deqquery);
	$deqrows = array(); //Create an empty array in case FetchRow does not return any rows
	while ($deqrow = $deqresult->FetchRow()) {$deqrows[] = $deqrow;} // Get table output into array

	// Perform a case insensitive natural sort on group name then question title of a multidimensional array
	usort($deqrows, 'CompareGroupThenTitle');

	$printablesurveyoutput .="\t<tr>\n";
	$printablesurveyoutput .="\t\t<td colspan='3' align='center' bgcolor='#EEEEEE' style='border-width: 1; border-style: double; border-color: #111111'>\n";
	$printablesurveyoutput .="\t\t\t<font size='3' face='verdana'><strong>{$degrow['group_name']}</strong></font>\n";
	if ($degrow['description'])
	{
		$printablesurveyoutput .="\t\t\t<br /><font size='2' face='verdana'>{$degrow['description']}</font>\n";
	}
    if(isset($_POST['printableexport'])){$pdf->titleintopdf($degrow['group_name'],$degrow['description']);}
	$printablesurveyoutput .="\t\t</td>\n";
	$printablesurveyoutput .="\t</tr>\n";
	$gid = $degrow['gid'];
	//Alternate bgcolor for different groups
	if (!isset($bgc) || $bgc == "#EEEEEE") {$bgc = "#DDDDDD";}
	else {$bgc = "#EEEEEE";}

	foreach ($deqrows as $deqrow)
	{
		//GET ANY CONDITIONS THAT APPLY TO THIS QUESTION
		$explanation = ""; //reset conditions explanation
		$s=0;
		$scenarioquery="SELECT DISTINCT ".db_table_name("conditions").".scenario FROM ".db_table_name("conditions")." WHERE ".db_table_name("conditions").".qid={$deqrow['qid']}";
		$scenarioresult=db_execute_assoc($scenarioquery);
		while ($scenariorow=$scenarioresult->FetchRow())
        		{
        		if ($s == 0 && $scenarioresult->RecordCount() > 1) { $explanation .= " <br>-------- <i>Scenario {$scenariorow['scenario']}</i> --------<br />";}
        		if ($s > 0) { $explanation .= " <br>-------- <i>".$clang->gT("or")." Scenario {$scenariorow['scenario']}</i> --------<br />";}

			$x=0;
			$distinctquery="SELECT DISTINCT cqid, method, ".db_table_name("questions").".title FROM ".db_table_name("conditions").", ".db_table_name("questions")." WHERE ".db_table_name("conditions").".cqid=".db_table_name("questions").".qid AND ".db_table_name("conditions").".qid={$deqrow['qid']} AND ".db_table_name("conditions").".scenario={$scenariorow['scenario']} AND language='{$surveyprintlang}' ORDER BY cqid";
			$distinctresult=db_execute_assoc($distinctquery);
			while ($distinctrow=$distinctresult->FetchRow())
			{
				if ($x > 0) {$explanation .= " <i>".$clang->gT("and")."</i> ";}
				if (trim($distinctrow['method'])=='') {$distinctrow['method']='==';}
				if ($distinctrow['method']=='==') {$explanation .= $clang->gT("if you answered")." ";}
				elseif ($distinctrow['method']='!=')
				{$explanation .= $clang->gT("if you have NOT answered")." ";}
				else
				{$explanation .= $clang->gT("if you answered")." ";}


				$conquery="SELECT cid, cqid, ".db_table_name("questions").".title,\n"
				."".db_table_name("questions").".question, value, ".db_table_name("questions").".type,\n"
				."".db_table_name("questions").".lid, cfieldname\n"
				."FROM ".db_table_name("conditions").", ".db_table_name("questions")."\n"
				."WHERE ".db_table_name("conditions").".cqid=".db_table_name("questions").".qid\n"
				."AND ".db_table_name("conditions").".cqid={$distinctrow['cqid']}\n"
				."AND ".db_table_name("conditions").".qid={$deqrow['qid']} AND language='{$surveyprintlang}'";
				$conresult=db_execute_assoc($conquery) or safe_die("$conquery<br />".htmlspecialchars($connect->ErrorMsg()));
				$conditions=array();
				while ($conrow=$conresult->FetchRow())
				{

					$postans="";
					$value=$conrow['value'];
					switch($conrow['type'])
					{
						case "Y":
						switch ($conrow['value'])
						{
							case "Y": $conditions[]=$clang->gT("Yes"); break;
							case "N": $conditions[]=$clang->gT("No"); break;
						}
						break;
						case "G":
						switch($conrow['value'])
						{
							case "M": $conditions[]=$clang->gT("Male"); break;
							case "F": $conditions[]=$clang->gT("Female"); break;
						} // switch
						break;
						case "A":
						case "B":
						$conditions[]=$conrow['value'];
						break;
						case "C":
						switch($conrow['value'])
						{
							case "Y": $conditions[]=$clang->gT("Yes"); break;
							case "U": $conditions[]=$clang->gT("Uncertain"); break;
							case "N": $conditions[]=$clang->gT("No"); break;
						} // switch
						break;
						case "E":
						switch($conrow['value'])
						{
							case "I": $conditions[]=$clang->gT("Increase"); break;
							case "D": $conditions[]=$clang->gT("Decrease"); break;
							case "S": $conditions[]=$clang->gT("Same"); break;
						}
						case "F":
						case "1":
						case "H":
						case "W":
						case "L":
						default:
						$value=substr($conrow['cfieldname'], strpos($conrow['cfieldname'], "X".$conrow['cqid'])+strlen("X".$conrow['cqid']), strlen($conrow['cfieldname']));
						$fquery = "SELECT * FROM ".db_table_name("labels")."\n"
						. "WHERE lid='{$conrow['lid']}'\n"
						. "AND code='{$conrow['value']}' AND language='{$surveyprintlang}'";
						$fresult=db_execute_assoc($fquery) or safe_die("$fquery<br />".htmlspecialchars($connect->ErrorMsg()));
						while($frow=$fresult->FetchRow())
						{
							$postans=$frow['title'];
							$conditions[]=$frow['title'];
						} // while
						break;
					} // switch
					$answer_section="";
					switch($conrow['type'])
					{
						case "A":
						case "B":
						case "C":
						case "E":
						$thiscquestion=arraySearchByKey($conrow['cfieldname'], $fieldmap, "fieldname");
						$ansquery="SELECT answer FROM ".db_table_name("answers")." WHERE qid='{$conrow['cqid']}' AND code='{$thiscquestion[0]['aid']}' AND language='{$surveyprintlang}'";
						$ansresult=db_execute_assoc($ansquery);
						while ($ansrow=$ansresult->FetchRow())
						{
							$answer_section=" (".$ansrow['answer'].")";
						}
						break;
						default:
						$ansquery="SELECT answer FROM ".db_table_name("answers")." WHERE qid='{$conrow['cqid']}' AND code='{$conrow['value']}' AND language='{$surveyprintlang}'";
						$ansresult=db_execute_assoc($ansquery);
						while ($ansrow=$ansresult->FetchRow())
						{
							$conditions[]=$ansrow['answer'];
						}
						$conditions = array_unique($conditions);
						break;
					}
				}
				if (count($conditions) > 1)
				{
					$explanation .=  "'".implode("' ".$clang->gT("or")." '", $conditions)."'";
				}
				elseif (count($conditions) == 1)
				{
					$explanation .= "'".$conditions[0]."'";
				}
				unset($conditions);
				$explanation .= " ".$clang->gT("to question")." '".$distinctrow['title']." $answer_section'";
				$x++;
			}
			$s++;
		}

		if ($explanation)
		{
			$explanation = "[".sprintf($clang->gT("Only answer this question %s"), $explanation)."]";
			$printablesurveyoutput .="<tr bgcolor='$bgc'><td colspan='3'>$explanation</td></tr>\n";
		}
		//END OF GETTING CONDITIONS

		$qid = $deqrow['qid'];
		$fieldname = "$surveyid"."X"."$gid"."X"."$qid";
		$printablesurveyoutput .="\t<tr bgcolor='$bgc'>\n";
		$printablesurveyoutput .="\t\t<td valign='top' align='left' colspan='3'>\n";
        $pdfoutput ='';
		if ($deqrow['mandatory'] == "Y")
		{
			$printablesurveyoutput .=$clang->gT("*");
            $pdfoutput .= $clang->gT("*");
		}
		$printablesurveyoutput .="\t\t\t<strong>{$deqrow['title']}: {$deqrow['question']}</strong>\n";
		$printablesurveyoutput .="\t\t</td>\n";
		$printablesurveyoutput .="\t</tr>\n";
		//DIFFERENT TYPES OF DATA FIELD HERE
        if(isset($_POST['printableexport'])){$pdf->intopdf($deqrow['title']." ".$deqrow['question']);}
		$printablesurveyoutput .="\t<tr bgcolor='$bgc'>\n";
		$printablesurveyoutput .="\t\t<td width='15%' valign='top'>\n";
		if ($deqrow['help'])
		{
			$hh = $deqrow['help'];
			$printablesurveyoutput .="\t\t\t<table width='100%' border='1'><tr><td align='center'><font size='1'>$hh</font></td></tr></table>\n";
            if(isset($_POST['printableexport'])){$pdf->helptextintopdf($hh);}
		}
		$printablesurveyoutput .="\t\t</td>\n";
		$printablesurveyoutput .="\t\t<td style='padding-left: 20px'>\n";
		switch($deqrow['type'])
		{
			case "5":  //5 POINT CHOICE
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose *only one* of the following:")."</u><br />\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *only one* of the following:"),"U");}
            $pdfoutput ='';
			for ($i=1; $i<=5; $i++)
			{
                $pdfoutput .=" o ".$i." ";
				$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname' value='$i' readonly='readonly' />$i \n";
			}
            if(isset($_POST['printableexport'])){$pdf->intopdf($pdfoutput);}
			break;
			case "D":  //DATE
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please enter a date:")."</u><br />\n";
			$printablesurveyoutput .="\t\t\t<input type='text' class='boxstyle' name='$fieldname' size='30' value='&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;' readonly='readonly' />\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please enter a date:")." ___________");}
			break;
			case "G":  //GENDER
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose *only one* of the following:")."</u><br />\n";
			$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname' value='F' readonly='readonly' />".$clang->gT("Female")."<br />\n";
			$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname' value='M' readonly='readonly' />".$clang->gT("Male")."<br />\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *only one* of the following:"));}
            if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$clang->gT("Female")." | o ".$clang->gT("Male"));}
			break;
			case "W": //Flexible List
			case "Z":
			$qidattributes=getQuestionAttributes($deqrow['qid']);
			if ($displaycols=arraySearchByKey("display_columns", $qidattributes, "attribute", 1))
			{
				$dcols=$displaycols['value'];
			}
			else
			{
				$dcols=0;
			}
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose *only one* of the following:")."</u><br />\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *only one* of the following:"),"U");}
			$deaquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$deqrow['lid']} AND language='{$surveyprintlang}' ORDER BY sortorder";
			$dearesult = db_execute_assoc($deaquery) or safe_die("ERROR: $deaquery<br />\n".$connect->ErrorMsg());
			$deacount=$dearesult->RecordCount();
			if ($deqrow['other'] == "Y") {$deacount++;}
			if ($dcols > 0 && $deacount >= $dcols)
			{
				$width=sprintf("%0d", 100/$dcols);
				$maxrows=ceil(100*($deacount/$dcols)/100); //Always rounds up to nearest whole number
				$divider="</td>\n <td valign='top' width='$width%' nowrap='nowrap'>";
				$upto=0;
				$printablesurveyoutput .="<table class='question'><tr>\n <td valign='top' width='$width%' nowrap='nowrap'>";
				while ($dearow = $dearesult->FetchRow())
				{
					if ($upto == $maxrows)
					{
						$printablesurveyoutput .=$divider;
						$upto=0;
					}
					$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname' value='{$dearow['code']}' readonly='readonly' />{$dearow['title']}<br />\n";
                    if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$dearow['title']);}
					$upto++;
				}
				if ($deqrow['other'] == "Y")
				{
					$printablesurveyoutput .="\t\t\t<input type='checkbox' readonly='readonly' />".$clang->gT("Other")." <input type='text' size='30' readonly='readonly' /><br />\n";
                    if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Other").": ________");}
				}
				$printablesurveyoutput .="</td></tr></table>\n";
				//Let's break the presentation into columns.
			}
			else
			{
				while ($dearow = $dearesult->FetchRow())
				{
					$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname' value='{$dearow['code']}' readonly='readonly' />{$dearow['title']}<br />\n";
                    if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$dearow['title']);}
				}
				if ($deqrow['other'] == "Y")
				{
					$printablesurveyoutput .="\t\t\t<input type='checkbox' readonly='readonly' />".$clang->gT("Other")." <input type='text' size='30' readonly='readonly' /><br />\n";
                    if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Other").": ________");}
				}
			}
			break;
			case "L":  //LIST
			case "!":
			$qidattributes=getQuestionAttributes($deqrow['qid']);
			if ($displaycols=arraySearchByKey("display_columns", $qidattributes, "attribute", 1))
			{
				$dcols=$displaycols['value'];
			}
			else
			{
				$dcols=0;
			}

			if ($optCategorySeparator = arraySearchByKey('category_separator', $qidattributes, 'attribute', 1))
			{
				$optCategorySeparator = $optCategorySeparator['value'];
			}
			else
			{
				unset($optCategorySeparator);
			}

			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose *only one* of the following:")."</u><br />\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *only one* of the following:"));}
			$deaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
			$dearesult = db_execute_assoc($deaquery);
			$deacount=$dearesult->RecordCount();
			if ($deqrow['other'] == "Y") {$deacount++;}
			if ($dcols > 0 && $deacount >= $dcols)
			{
				$width=sprintf("%0d", 100/$dcols);
				$maxrows=ceil(100*($deacount/$dcols)/100); //Always rounds up to nearest whole number
				$divider=" </td>\n <td valign='top' width='$width%' nowrap='nowrap'>";
				$upto=0;
				$printablesurveyoutput .="<table class='question'><tr>\n <td valign='top' width='$width%' nowrap='nowrap'>";
				while ($dearow = $dearesult->FetchRow())
				{
					if (isset($optCategorySeparator))
					{
						list ($category, $answer) = explode($optCategorySeparator,$dearow['answer']);
						if ($category != '')
						{
							$dearow['answer'] = "($category) $answer";
						}
						else
						{
							$dearow['answer'] = $answer;
						}
					}

					if ($upto == $maxrows)
					{
						$printablesurveyoutput .=$divider;
						$upto=0;
					}
					$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname' value='{$dearow['code']}' readonly='readonly' />{$dearow['answer']}<br />\n";
                    if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$dearow['answer']);}
					$upto++;
				}
				if ($deqrow['other'] == "Y")
				{
					$printablesurveyoutput .="\t\t\t<input type='checkbox' readonly='readonly' />".$clang->gT("Other")." <input type='text' size='30' readonly='readonly' /><br />\n";
                    if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$clang->gT("Other").": ________");}
				}
				$printablesurveyoutput .="</td></tr></table>\n";
				//Let's break the presentation into columns.
			}
			else
			{
				while ($dearow = $dearesult->FetchRow())
				{
					if (isset($optCategorySeparator))
					{
						list ($category, $answer) = explode($optCategorySeparator,$dearow['answer']);
						if ($category != '')
						{
							$dearow['answer'] = "($category) $answer";
						}
						else
						{
							$dearow['answer'] = $answer;
						}
					}

					$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname' value='{$dearow['code']}' readonly='readonly' />{$dearow['answer']}<br />\n";
                    if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$dearow['answer']);}
				}
				if ($deqrow['other'] == "Y")
				{
					$printablesurveyoutput .="\t\t\t<input type='checkbox' readonly='readonly' />".$clang->gT("Other")." <input type='text' size='30' readonly='readonly' /><br />\n";
                    if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$clang->gT("Other").": ________");}
				}
			}
			break;
			case "O":  //LIST WITH COMMENT
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose *only one* of the following:")."</u><br />\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *only one* of the following:"),"U");}
			$deaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$surveyprintlang}' ORDER BY sortorder, answer ";
			$dearesult = db_execute_assoc($deaquery);
			while ($dearow = $dearesult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname' value='{$dearow['code']}' readonly='readonly' />{$dearow['answer']}<br />\n";
                if(isset($_POST['printableexport'])){$pdf->intopdf($dearow['answer']);}
			}
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Make a comment on your choice here:")."</u><br />\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf("Make a comment on your choice here:");}
			$printablesurveyoutput .="\t\t\t<textarea class='boxstyle' cols='50' rows='8' name='$fieldname"."comment"."' readonly='readonly'></textarea>\n";
            for($i=0;$i<9;$i++)
            {
                if(isset($_POST['printableexport'])){$pdf->intopdf("____________________");}
            }
			break;
			case "R":  //RANKING Type Question
			$reaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
			$rearesult = db_execute_assoc($reaquery) or safe_die ("Couldn't get ranked answers<br />".$connect->ErrorMsg());
			$reacount = $rearesult->RecordCount();
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please number each box in order of preference from 1 to")." $reacount</u><br />\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please number each box in order of preference from 1 to ").$reacount,"U");}
			while ($rearow = $rearesult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t<table cellspacing='1' cellpadding='0'><tr><td width='20' height='20' bgcolor='white' style='border: solid 1 #111111'>&nbsp;</td>\n";
				$printablesurveyoutput .="\t\t\t<td valign='middle'>{$rearow['answer']}</td></tr></table>\n";
                if(isset($_POST['printableexport'])){$pdf->intopdf("__ ".$rearow['answer']);}
			}
			break;
			case "M":  //MULTIPLE OPTIONS (Quite tricky really!)
			$qidattributes=getQuestionAttributes($deqrow['qid']);
			if ($displaycols=arraySearchByKey("display_columns", $qidattributes, "attribute", 1))
			{
				$dcols=$displaycols['value'];
			}
			else
			{
				$dcols=0;
			}
			if (!$maxansw=arraySearchByKey("max_answers", $qidattributes, "attribute", 1))
			{
				$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose *all* that apply:")."</u><br />\n";
                if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *all* that apply:"),"U");}
			}
			else
			{
				$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose *at most* ").$maxansw['value']."</strong> ".$clang->gT("answers:")."</u><br />\n";
                if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *at most* ").$maxansw['value'].$clang->gT("answers:"),"U");}
			}
			$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
			$mearesult = db_execute_assoc($meaquery);
			$meacount = $mearesult->RecordCount();
			if ($deqrow['other'] == "Y") {$meacount++;}
			if ($dcols > 0 && $meacount >= $dcols)
			{
				$width=sprintf("%0d", 100/$dcols);
				$maxrows=ceil(100*($meacount/$dcols)/100); //Always rounds up to nearest whole number
				$divider=" </td>\n <td valign='top' width='$width%' nowrap='nowrap'>";
				$upto=0;
				$printablesurveyoutput .="<table class='question'><tr>\n <td valign='top' width='$width%' nowrap='nowrap'>";
				while ($mearow = $mearesult->FetchRow())
				{
					if ($upto == $maxrows)
					{
						$printablesurveyoutput .=$divider;
						$upto=0;
					}
					$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y' readonly='readonly' />{$mearow['answer']}<br />\n";
                    if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$mearow['answer']);}
					$upto++;
				}
				if ($deqrow['other'] == "Y")
				{
					$printablesurveyoutput .="\t\t\t".$clang->gT("Other").": <input type='text' class='boxstyle' size='60' name='$fieldname" . "other' readonly='readonly' />\n";
                    if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$clang->gT("Other").": ________");}

				}
				$printablesurveyoutput .="</td></tr></table>\n";
			}
			else
			{
				while ($mearow = $mearesult->FetchRow())
				{
					$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y' readonly='readonly' />{$mearow['answer']}<br />\n";
                     if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$mearow['answer']);}
				}
				if ($deqrow['other'] == "Y")
				{
					$printablesurveyoutput .="\t\t\t".$clang->gT("Other").": <input type='text' class='boxstyle' size='60' name='$fieldname" . "other' readonly='readonly' />\n";
                    if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Other").": ________");}
				}
			}
			break;
			/*case "I":  //Language Switch  in a printable survey does not make sense
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose *only one* of the following:")."</u><br />\n";
        	$answerlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        	$answerlangs [] = GetBaseLanguageFromSurveyID($surveyid);

        	foreach ($answerlangs as $ansrow)
        	{
				$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname' value='{$ansrow}' />".getLanguageNameFromCode($ansrow, true)."<br />\n";
			}
			break;
            */

			case "P":  //MULTIPLE OPTIONS WITH COMMENTS
			$qidattributes=getQuestionAttributes($deqrow['qid']);
			if (!$maxansw=arraySearchByKey("max_answers", $qidattributes, "attribute", 1))
			{
				$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose all that apply and provide a comment:")."</u><br />\n";
                if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose all that apply and provide a comment:"),"U");}
			}
			else
			{
				$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose *at most* ").$maxansw['value']."</strong> ".$clang->gT("answers and provide a comment:")."</u><br />\n";
                if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *at most* ").$maxansw['value'].$clang->gT("answers and provide a comment:"),"U");}
			}
			$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
			$mearesult = db_execute_assoc($meaquery);
//			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose all that apply and provide a comment:")."</u><br />\n";
			$printablesurveyoutput .="\t\t\t<table border='0'>\n";
            $pdfoutput=array();
            $j=0;
			while ($mearow = $mearesult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t<tr>\n";
				$printablesurveyoutput .="\t\t\t\t\t<td><input type='checkbox' name='$fieldname{$mearow['code']}' value='Y'";
				if ($mearow['default_value'] == "Y") {$printablesurveyoutput .=" checked";}
				$printablesurveyoutput .=" readonly='readonly' />{$mearow['answer']}</td>\n";
				//This is the commments field:
				$printablesurveyoutput .="\t\t\t\t\t<td><input type='text' class='boxstyle' name='$fieldname{$mearow['code']}comment' size='60' readonly='readonly' /></td>\n";
				$printablesurveyoutput .="\t\t\t\t</tr>\n";
                $pdfoutput[$j]=array(" o ".$mearow['code']," __________");
                $j++;
			}
			$printablesurveyoutput .="\t\t\t</table>\n";
            if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
			break;
			case "Q":  //MULTIPLE SHORT TEXT


			$width=60;
			case "K":  //MULTIPLE NUMERICAL
			$width=(!isset($width)) ? 30 : $width;
			if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write your answer(s) here:"),"U");}
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please write your answer(s) here:")."</u><br />\n";
			$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
			$mearesult = db_execute_assoc($meaquery);
			$printablesurveyoutput .="\t\t\t<table border='0'>\n";
			while ($mearow = $mearesult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t<tr>\n";
				$printablesurveyoutput .="\t\t\t\t\t<td align='right'>{$mearow['answer']}:</td><td><input type='text' size='$width' name='$fieldname{$mearow['code']}' value=''";
				if ($mearow['default_value'] == "Y") {$printablesurveyoutput .=" checked";}
				$printablesurveyoutput .=" readonly='readonly' /></td>\n";
				$printablesurveyoutput .="\t\t\t\t</tr>\n";
                if(isset($_POST['printableexport'])){$pdf->intopdf($mearow['answer'].": ____________________");}
			}
			$printablesurveyoutput .="\t\t\t</table>\n";
			break;
			case "S":  //SHORT TEXT
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please write your answer here:")."</u><br />\n";
			$printablesurveyoutput .="\t\t\t<input type='text' name='$fieldname' size='60' class='boxstyle' readonly='readonly' />\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write your answer here:"),"U");}
            if(isset($_POST['printableexport'])){$pdf->intopdf("____________________");}
			break;
			case "T":  //LONG TEXT
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please write your answer here:")."</u><br />\n";
			$printablesurveyoutput .="\t\t\t<textarea class='boxstyle' cols='50' rows='8' name='$fieldname' readonly='readonly'></textarea>\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write your answer here:"),"U");}
            for($i=0;$i<9;$i++)
            {
                if(isset($_POST['printableexport'])){$pdf->intopdf("____________________");}
            }
			break;
			case "U":  //HUGE TEXT
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please write your answer here:")."</u><br />\n";
			$printablesurveyoutput .="\t\t\t<textarea class='boxstyle' cols='70' rows='50' name='$fieldname' readonly='readonly'></textarea>\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write your answer here:"),"U");}
            for($i=0;$i<20;$i++)
            {
                if(isset($_POST['printableexport'])){$pdf->intopdf("____________________");}
            }
			break;
			case "N":  //NUMERICAL
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please write your answer here:")."</u><br />\n";
			$printablesurveyoutput .="\t\t\t<input type='text' size='40' class='boxstyle' readonly='readonly' />\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write your answer here:"),"U");}
            if(isset($_POST['printableexport'])){$pdf->intopdf("____________________");}

			break;
			case "Y":  //YES/NO
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose *only one* of the following:")."</u><br />\n";
			$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname' value='Y' readonly='readonly' />".$clang->gT("Yes")."<br />\n";
			$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname' value='N' readonly='readonly' />".$clang->gT("No")."<br />\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *only one* of the following:"),"U");}
            if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$clang->gT("Yes"));}
            if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$clang->gT("No"));}
			break;
			case "A":  //ARRAY (5 POINT CHOICE)
			$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$surveyprintlang}'  ORDER BY sortorder, answer";
			$mearesult = db_execute_assoc($meaquery);
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose the appropriate response for each item:")."</u><br />\n";
			$printablesurveyoutput .="\t\t\t<table>\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
            $pdfoutput = array();
            $j=0;
			while ($mearow = $mearesult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t<tr>\n";
                $answertext=$mearow['answer'];
                if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
				$printablesurveyoutput .="\t\t\t\t\t<td align='left'>$answertext</td>\n";
				$printablesurveyoutput .="\t\t\t\t\t<td>";
                $pdfoutput[$j][0]=$answertext;
				for ($i=1; $i<=5; $i++)
				{
					$printablesurveyoutput .="\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='$i' readonly='readonly' />$i&nbsp;\n";
                    $pdfoutput[$j][$i]=" o ".$i;
				}
				$printablesurveyoutput .="\t\t\t\t\t</td>\n";
                $answertext=$mearow['answer'];
                if (strpos($answertext,'|'))
                {
                    $answertext=substr($answertext,strpos($answertext,'|')+1);
                       $printablesurveyoutput .= "\t\t\t\t<td class='answertextright'>$answertext</td>\n";
                }
				$printablesurveyoutput .="\t\t\t\t</tr>\n";
                $j++;
			}
			$printablesurveyoutput .="\t\t\t</table>\n";
            if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
			break;
			case "B":  //ARRAY (10 POINT CHOICE)
			$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
			$mearesult = db_execute_assoc($meaquery);
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose the appropriate response for each item:")."</u><br />";
			$printablesurveyoutput .="\t\t\t<table border='0'>\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
            $pdfoutput=array();
            $j=0;
			while ($mearow = $mearesult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t<tr>\n";
				$printablesurveyoutput .="\t\t\t\t\t<td align='left'>{$mearow['answer']}</td>\n";
				$printablesurveyoutput .="\t\t\t\t\t<td>\n";
                $pdfoutput[$j][0]=$mearow['answer'];
				for ($i=1; $i<=10; $i++)
				{
					$printablesurveyoutput .="\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='$i' readonly='readonly' />$i&nbsp;\n";
                    $pdfoutput[$j][$i]=" o ".$i;
				}
				$printablesurveyoutput .="\t\t\t\t\t</td>\n";
				$printablesurveyoutput .="\t\t\t\t</tr>\n";
                $j++;
			}
			$printablesurveyoutput .="\t\t\t</table>\n";
            if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
			break;
			case "C":  //ARRAY (YES/UNCERTAIN/NO)
			$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
			$mearesult = db_execute_assoc($meaquery);
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose the appropriate response for each item:")."</u><br />\n";
			$printablesurveyoutput .="\t\t\t<table>\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
            $pdfoutput = array();
            $j=0;
			while ($mearow = $mearesult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t<tr>\n";
				$printablesurveyoutput .="\t\t\t\t\t<td align='left'>{$mearow['answer']}</td>\n";
				$printablesurveyoutput .="\t\t\t\t\t<td>\n";
				$printablesurveyoutput .="\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y' readonly='readonly' />".$clang->gT("Yes")."&nbsp;\n";
				$printablesurveyoutput .="\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='U' readonly='readonly' />".$clang->gT("Uncertain")."&nbsp;\n";
				$printablesurveyoutput .="\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='N' readonly='readonly' />".$clang->gT("No")."&nbsp;\n";
				$printablesurveyoutput .="\t\t\t\t\t</td>\n";
				$printablesurveyoutput .="\t\t\t\t</tr>\n";

                $pdfoutput[$j]=array($mearow['answer']," o ".$clang->gT("Yes")," o ".$clang->gT("Uncertain")," o ".$clang->gT("No"));
                $j++;
			}
			$printablesurveyoutput .="\t\t\t</table>\n";
            if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
			break;
			case "E":  //ARRAY (Increase/Same/Decrease)
			$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$surveyprintlang}'  ORDER BY sortorder, answer";
			$mearesult = db_execute_assoc($meaquery);
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose the appropriate response for each item:")."</u><br />\n";
			$printablesurveyoutput .="\t\t\t<table>\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
            $pdfoutput = array();
            $j=0;
			while ($mearow = $mearesult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t<tr>\n";
				$printablesurveyoutput .="\t\t\t\t\t<td align='left'>{$mearow['answer']}</td>\n";
				$printablesurveyoutput .="\t\t\t\t\t<td>\n";
				$printablesurveyoutput .="\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='I' readonly='readonly' />".$clang->gT("Increase")."&nbsp;\n";
				$printablesurveyoutput .="\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='S' readonly='readonly' />".$clang->gT("Same")."&nbsp;\n";
				$printablesurveyoutput .="\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='D' readonly='readonly' />".$clang->gT("Decrease")."&nbsp;\n";
				$printablesurveyoutput .="\t\t\t\t\t</td>\n";
				$printablesurveyoutput .="\t\t\t\t</tr>\n";
                $pdfoutput[$j]=array($mearow['answer'].":"," o ".$clang->gT("Increase")," o ".$clang->gT("Same")," o ".$clang->gT("Decrease"));
                $j++;
			}
			$printablesurveyoutput .="\t\t\t</table>\n";
            if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
			break;
			case ":": //ARRAY (Multi Flexible) (Numbers)
			$headstyle="style='padding-left: 20px; padding-right: 7px'";
        	$qidattributes=getQuestionAttributes($deqrow['qid']);
        	if ($maxvalue=arraySearchByKey("multiflexible_max", $qidattributes, "attribute", 1)) {
        		$maxvalue=$maxvalue['value'];
        	} else {
        		$maxvalue=10;
        	}
        	if ($minvalue=arraySearchByKey("multiflexible_min", $qidattributes, "attribute", 1)) {
        		$minvalue=$minvalue['value'];
        	} else {
        		$minvalue=1;
        	}
        	if ($stepvalue=arraySearchByKey("multiflexible_step", $qidattributes, "attribute", 1)) {
        		$stepvalue=$stepvalue['value'];
        	} else {
        		$stepvalue=1;
        	}
        	if (arraySearchByKey("multiflexible_checkbox", $qidattributes, "attribute", 1)) {
        		$checkboxlayout=true;
        	} else {
        		$checkboxlayout=false;
        	}
			$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
			$mearesult = db_execute_assoc($meaquery);
			if ($checkboxlayout === false)
			{
				if ($stepvalue > 1)
				{
					$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please write a multiple of $stepvalue between $minvalue and $maxvalue for each item:")."</u><br />\n";
				    if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write a multiple of $stepvalue between $minvalue and $maxvalue for each item:"),"U");}
				} else {
					$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please write a number between $minvalue and $maxvalue for each item:")."</u><br />\n";
				    if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write a number between $minvalue and $maxvalue for each item:"),"U");}
				}
			}
			else
			{
					$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Check any that apply").":</u><br />\n";
				    if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Check any that apply"),"U");}
			}

			$printablesurveyoutput .="\t\t\t<table align='left' cellspacing='0'><tr><td></td>\n";
			$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$deqrow['lid']}'  AND language='{$surveyprintlang}' ORDER BY sortorder, code";
			$fresult = db_execute_assoc($fquery);
			$fcount = $fresult->RecordCount();
			$fwidth = "120";
			$i=0;
			$pdfoutput = array();
			$pdfoutput[0][0]=' ';
			while ($frow = $fresult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t\t\t<td align='center' valign='bottom' $headstyle><font size='1'>{$frow['title']}</font></td>\n";
				$i++;
   				$pdfoutput[0][$i]=$frow['title'];
			}
			$printablesurveyoutput .="\t\t\t\t\t\t</tr>\n";
			$a=1; //Counter for pdfoutput
			while ($mearow = $mearesult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t<tr>\n";
                $answertext=$mearow['answer'];
                if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
				$printablesurveyoutput .="\t\t\t\t\t<td align='left'>$answertext</td>\n";
				//$printablesurveyoutput .="\t\t\t\t\t<td>";
				$pdfoutput[$a][0]=$answertext;
				for ($i=1; $i<=$fcount; $i++)
				{

					$printablesurveyoutput .="\t\t\t\t\t<td align='center'";
					if ($i > 1) {$printablesurveyoutput .=" $headstyle";}
					$printablesurveyoutput .=">\n";
					if ($checkboxlayout === false)
					{
						$printablesurveyoutput .="\t\t\t\t\t\t<input type='text' style='height: 14pt; width: 14pt' readonly='readonly' />\n";
					    $pdfoutput[$a][$i]="__";
					}
					else
					{
						$printablesurveyoutput .="\t\t\t\t\t\t<input type='checkbox' readonly='readonly' />\n";
						$pdfoutput[$a][$i]="o";
					}
					$printablesurveyoutput .="\t\t\t\t\t</td>\n";
				}
                $answertext=$mearow['answer'];
                if (strpos($answertext,'|'))
                {
                    $answertext=substr($answertext,strpos($answertext,'|')+1);
                       $printablesurveyoutput .= "\t\t\t\t<td class='answertextright'>$answertext</td>\n";
                       //$pdfoutput[$a][$i]=$answertext;
                }
				$printablesurveyoutput .="\t\t\t\t</tr>\n";
				$a++;
			}
			$printablesurveyoutput .="\t\t\t</table>\n";
			if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
			break;
			///////////////////////////////////////////////////////////////////////////////////////
			case ";": //ARRAY (Multi Flexible) (text)
			$headstyle="style='padding-left: 20px; padding-right: 7px'";
        	$qidattributes=getQuestionAttributes($deqrow['qid']);
			$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
			$mearesult = db_execute_assoc($meaquery);

			$printablesurveyoutput .="\t\t\t<table align='left' cellspacing='0'><tr><td></td>\n";
			$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$deqrow['lid']}'  AND language='{$surveyprintlang}' ORDER BY sortorder, code";
			$fresult = db_execute_assoc($fquery);
			$fcount = $fresult->RecordCount();
			$fwidth = "120";
			$i=0;
			$pdfoutput=array();
			$pdfoutput[0][0]='';
			while ($frow = $fresult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t\t\t<td align='center' valign='bottom' $headstyle><font size='1'>{$frow['title']}</font></td>\n";
				$i++;
				$pdfoutput[0][$i]=$frow['title'];
			}
			$printablesurveyoutput .="\t\t\t\t\t\t</tr>\n";
			$a=1;
			while ($mearow = $mearesult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t<tr>\n";
                $answertext=$mearow['answer'];
                if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
				$printablesurveyoutput .="\t\t\t\t\t<td align='left'>$answertext</td>\n";
				$pdfoutput[$a][0]=$answertext;
				//$printablesurveyoutput .="\t\t\t\t\t<td>";
				for ($i=1; $i<=$fcount; $i++)
				{
					$printablesurveyoutput .="\t\t\t\t\t<td align='center'";
					if ($i > 1) {$printablesurveyoutput .=" $headstyle";}
					$printablesurveyoutput .=">\n";
					$printablesurveyoutput .="\t\t\t\t\t\t<input type='text' style='height: 14pt; width: 150pt' readonly='readonly' />\n";
					$printablesurveyoutput .="\t\t\t\t\t</td>\n";
					$pdfoutput[$a][$i]="_____________";
				}
                $answertext=$mearow['answer'];
                if (strpos($answertext,'|'))
                {
                    $answertext=substr($answertext,strpos($answertext,'|')+1);
                       $printablesurveyoutput .= "\t\t\t\t<td class='answertextright'>$answertext</td>\n";
                }
				$printablesurveyoutput .="\t\t\t\t</tr>\n";
				$a++;
			}
			$printablesurveyoutput .="\t\t\t</table>\n";
			if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
			break;
			case "F": //ARRAY (Flexible Labels)
			//$headstyle="style='border-left-style: solid; border-left-width: 1px; border-left-color: #AAAAAA'";
			$headstyle="style='padding-left: 20px; padding-right: 7px'";
			$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
			$mearesult = db_execute_assoc($meaquery);
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose the appropriate response for each item:")."</u><br />\n";
			$printablesurveyoutput .="\t\t\t<table align='left' cellspacing='0'><tr><td></td>\n";
			$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$deqrow['lid']}'  AND language='{$surveyprintlang}' ORDER BY sortorder, code";
			$fresult = db_execute_assoc($fquery);
			$fcount = $fresult->RecordCount();
			$fwidth = "120";
			$i=1;
            $pdfoutput = array();
            $pdfoutput[0][0]='';
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
			while ($frow = $fresult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t\t\t<td align='center' valign='bottom' $headstyle><font size='1'>{$frow['title']}</font></td>\n";
                $pdfoutput[0][$i] = $frow['title'];
                $i++;
			}
			$printablesurveyoutput .="\t\t\t\t\t\t</tr>\n";
            $counter = 1;
			while ($mearow = $mearesult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t<tr>\n";
                $answertext=$mearow['answer'];
                if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
				$printablesurveyoutput .="\t\t\t\t\t<td align='left'>$answertext</td>\n";
				//$printablesurveyoutput .="\t\t\t\t\t<td>";
                $pdfoutput[$counter][0]=$answertext;
				for ($i=1; $i<=$fcount; $i++)
				{

					$printablesurveyoutput .="\t\t\t\t\t<td align='center'";
					if ($i > 1) {$printablesurveyoutput .=" $headstyle";}
					$printablesurveyoutput .=">\n";
					$printablesurveyoutput .="\t\t\t\t\t\t<input type='checkbox' readonly='readonly' />\n";
					$printablesurveyoutput .="\t\t\t\t\t</td>\n";
                    $pdfoutput[$counter][$i] = "o";

                }
                $counter++;

                $answertext=$mearow['answer'];
                if (strpos($answertext,'|'))
                {
                    $answertext=substr($answertext,strpos($answertext,'|')+1);
                       $printablesurveyoutput .= "\t\t\t\t<td class='answertextright'>$answertext</td>\n";

                }
				$printablesurveyoutput .="\t\t\t\t</tr>\n";
			}
			$printablesurveyoutput .="\t\t\t</table>\n";
            if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
			break;
			case "1": //ARRAY (Flexible Labels) multi scale
            $qidattributes=getQuestionAttributes($deqrow['qid']);
            if ($dsheaderA=arraySearchByKey("dualscale_headerA", $qidattributes, "attribute", 1))
            {
                $leftheader= $dsheaderA['value'];
            }
            else
            {
                $leftheader ='';
            }
            if ($dsheaderB=arraySearchByKey("dualscale_headerB", $qidattributes, "attribute", 1))
            {
                $rightheader= $dsheaderB['value'];
            }
            else
            {
                $rightheader ='';
            }

			//$headstyle="style='border-left-style: solid; border-left-width: 1px; border-left-color: #AAAAAA'";
			$headstyle="style='padding-left: 20px; padding-right: 7px'";
			$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
			$mearesult = db_execute_assoc($meaquery);
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose the appropriate response for each item:")."</u><br />\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
			$printablesurveyoutput .="\t\t\t<table align='left' cellspacing='0'>";

			$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$deqrow['lid']}'  AND language='{$surveyprintlang}' ORDER BY sortorder, code";
			$fresult = db_execute_assoc($fquery);
			$fcount = $fresult->RecordCount();
			$fwidth = "120";
			$l1=0;
            $printablesurveyoutput2 = '<td></td>';
            $myheader2 = '';
            $pdfoutput = array();
            $pdfoutput[0][0]='';
			while ($frow = $fresult->FetchRow())
			{
				$printablesurveyoutput2 .="\t\t\t\t\t\t<td align='center' valign='bottom' $headstyle><font size='1'>{$frow['title']}</font></td>\n";
                $myheader2 .= "<td></td>";
                $pdfoutput[0][$l1+1]=$frow['title'];
				$l1++;
			}
			// second scale
			$printablesurveyoutput2 .="\t\t\t\t\t\t<td align='center' valign='bottom' $headstyle><font size='1'></font></td>\n";
			$fquery1 = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$deqrow['lid1']}'  AND language='{$surveyprintlang}' ORDER BY sortorder, code";
			$fresult1 = db_execute_assoc($fquery1);
			$fcount1 = $fresult1->RecordCount();
			$fwidth = "120";
			$l2=0;
			while ($frow1 = $fresult1->FetchRow())
			{
				$printablesurveyoutput2 .="\t\t\t\t\t\t<td align='center' valign='bottom' $headstyle><font size='1'>{$frow1['title']}</font></td>\n";
                $pdfoutput[1][$l2]=$frow['title'];
				$l2++;
			}
            // build header if needed
            if ($leftheader != '' || $rightheader !='')
            {
                $myheader = "\t\t\t\t\t<td></td>";
                $myheader .= "\t\t\t\t\t<td align='center' colspan='".$l1."' rowspan='1' width='20px'><span class='dsheader'>$leftheader</span></td>\n";

                if ($rightheader !='')
                {
                    // $myheader .= "\t\t\t\t\t" .$myheader2;
                    $myheader .= "\t\t\t\t\t<td></td>";
                    $myheader .= "\t\t\t\t\t<td align='center' colspan='".$l2."' rowspan='1' width='20px'><span class='dsheader'>$rightheader</span></td>\n";
                }

                $myheader .= "\t\t\t\t</tr>\n";
            }
            else
            {
                $myheader = '';
            }
            $printablesurveyoutput .= $myheader;
            $printablesurveyoutput .= $printablesurveyoutput2;
			$printablesurveyoutput .="\t\t\t\t\t\t</tr>\n";
			while ($mearow = $mearesult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t<tr>\n";
                $answertext=$mearow['answer'];
                if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
				$printablesurveyoutput .="\t\t\t\t\t<td align='left'>$answertext</td>\n";
				for ($i=1; $i<=$fcount; $i++)
				{
                	$printablesurveyoutput .="\t\t\t\t\t<td align='center'";
					if ($i > 1) {$printablesurveyoutput .=" $headstyle";}
					$printablesurveyoutput .=">\n";
					$printablesurveyoutput .="\t\t\t\t\t\t<input type='checkbox' readonly='readonly' />\n";
					$printablesurveyoutput .="\t\t\t\t\t</td>\n";
				}
				$printablesurveyoutput .="\t\t\t\t\t\t<td align='center' valign='bottom' $headstyle><font size='1'></font></td>\n";
				for ($i=1; $i<=$fcount1; $i++)
				{
					$printablesurveyoutput .="\t\t\t\t\t<td align='center'";
					if ($i > 1) {$printablesurveyoutput .=" $headstyle";}
					$printablesurveyoutput .=">\n";
					$printablesurveyoutput .="\t\t\t\t\t\t<input type='checkbox' readonly='readonly' />\n";
					$printablesurveyoutput .="\t\t\t\t\t</td>\n";
				}

                $answertext=$mearow['answer'];
                if (strpos($answertext,'|'))
                {
                    $answertext=substr($answertext,strpos($answertext,'|')+1);
                       $printablesurveyoutput .= "\t\t\t\t<td class='answertextright'>$answertext</td>\n";
                }
				$printablesurveyoutput .="\t\t\t\t</tr>\n";
			}
			$printablesurveyoutput .="\t\t\t</table>\n";
            if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
			break;
			case "H": //ARRAY (Flexible Labels) by Column
			//$headstyle="style='border-left-style: solid; border-left-width: 1px; border-left-color: #AAAAAA'";
			$headstyle="style='padding-left: 20px; padding-right: 7px'";
			$fquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
			$fresult = db_execute_assoc($fquery);
			$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose the appropriate response for each item:")."</u><br />\n";
            if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
			$printablesurveyoutput .="\t\t\t<table align='left' cellspacing='0'><tr><td></td>\n";
			$meaquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$deqrow['lid']}'  AND language='{$surveyprintlang}' ORDER BY sortorder, code";
			$mearesult = db_execute_assoc($meaquery);
			$fcount = $fresult->RecordCount();
			$fwidth = "120";
			$i=0;
            $pdfoutput = array();
            $pdfoutput[0][0]='';
			while ($frow = $fresult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t\t<td align='center'>{$frow['answer']}</td>\n";
				$i++;
                $pdfoutput[0][$i]=$frow['answer'];
			}
			$printablesurveyoutput .="\t\t\t\t\t\t</tr>\n";
            $a=1;
			while ($mearow = $mearesult->FetchRow())
			{
				$printablesurveyoutput .="\t\t\t\t<tr>\n";
				$printablesurveyoutput .="\t\t\t\t\t\t<td align='left' valign='bottom' $headstyle><font size='1'>{$mearow['title']}</font></td>\n";
				//$printablesurveyoutput .="\t\t\t\t\t<td>";
                $pdfoutput[$a][0]=$mearow['title'];
				for ($i=1; $i<=$fcount; $i++)
				{

					$printablesurveyoutput .="\t\t\t\t\t<td align='center'";
					if ($i > 1) {$printablesurveyoutput .=" $headstyle";}
					$printablesurveyoutput .=">\n";
					$printablesurveyoutput .="\t\t\t\t\t\t<input type='checkbox' readonly='readonly' />\n";
					$printablesurveyoutput .="\t\t\t\t\t</td>\n";
                    $pdfoutput[$a][$i]="o";
				}
				//$printablesurveyoutput .="\t\t\t\t\t</tr></table></td>\n";
				$printablesurveyoutput .="\t\t\t\t</tr>\n";
                $a++;
			}
			$printablesurveyoutput .="\t\t\t</table>\n";
            if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
			break;
		}
		$printablesurveyoutput .="\t\t</td>\n";
		$printablesurveyoutput .="\t</tr>\n";
		$printablesurveyoutput .="\t<tr><td height='3' colspan='3'><hr noshade='noshade' size='1' /></td></tr>\n";
        if(isset($_POST['printableexport'])){$pdf->ln(5);}
	}
}
$printablesurveyoutput .="\t<tr>\n";
$printablesurveyoutput .="\t\t<td colspan='3' align='center'>\n";
$printablesurveyoutput .="\t\t\t<table width='100%' border='1' style='border-collapse: collapse'>\n";
$printablesurveyoutput .="\t\t\t\t<tr>\n";
$printablesurveyoutput .="\t\t\t\t\t<td align='center'>\n";
$printablesurveyoutput .="\t\t\t\t\t\t<strong>".$clang->gT("Submit Your Survey.")."</strong><br />\n";
$printablesurveyoutput .="\t\t\t\t\t\t".$clang->gT("Thank you for completing this survey.");
if(isset($_POST['printableexport'])){$pdf->titleintopdf($clang->gT("Submit Your Survey."),$clang->gT("Thank you for completing this survey."));}
if(!empty($surveyfaxto)) //If no fax number exists, don't display faxing information!
{
    $printablesurveyoutput .= $clang->gT("Please fax your completed survey to:")." $surveyfaxto";
    if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please fax your completed survey to:")."$surveyfaxto",'B');}
}

if ($surveyuseexpiry=="Y")
{
    $printablesurveyoutput .= sprintf($clang->gT("Please submit by %s"), $surveyexpirydate);
    if(isset($_POST['printableexport'])){$pdf->intopdf(sprintf($clang->gT("Please submit by %s"), $surveyexpirydate));}
}
$printablesurveyoutput .=".\n";
$printablesurveyoutput .="\t\t\t\t\t</td>\n";
$printablesurveyoutput .="\t\t\t\t</tr>\n";
$printablesurveyoutput .="\t\t\t</table>\n";
$printablesurveyoutput .="\t\t</td>\n";
$printablesurveyoutput .="\t</tr>\n";
$printablesurveyoutput .="</table>\n";
$printablesurveyoutput .="</body>\n</html>";
if(isset($_POST['printableexport']))
{
    $pdf->write_out($clang->gT($surveyname)." ".$surveyid.".pdf");
}
echo $printablesurveyoutput ;
exit;
?>
