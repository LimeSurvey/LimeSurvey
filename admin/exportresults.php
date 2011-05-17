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

if (!isset($imageurl)) {$imageurl="./images";}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($exportstyle)) {$exportstyle=returnglobal('exportstyle');}
if (!isset($answers)) {$answers=returnglobal('answers');}
if (!isset($type)) {$type=returnglobal('type');}
if (!isset($convertyto1)) {$convertyto1=returnglobal('convertyto1');}
if (!isset($convertnto2)) {$convertnto2=returnglobal('convertnto2');}
if (!isset($convertspacetous)) {$convertspacetous=returnglobal('convertspacetous');}

if (!bHasSurveyPermission($surveyid, 'responses','export'))
{
    exit;
}

include_once(dirname(__FILE__)."/classes/phpexcel/PHPExcel.php");
include_once(dirname(__FILE__)."/classes/tcpdf/extensiontcpdf.php");
include_once(dirname(__FILE__)."/exportresults_objects.php");

$surveybaselang=GetBaseLanguageFromSurveyID($surveyid);
$exportoutput="";

// Get info about the survey
$thissurvey=getSurveyInfo($surveyid);

if (!$exportstyle)
{

    //FIND OUT HOW MANY FIELDS WILL BE NEEDED - FOR 255 COLUMN LIMIT
    $excesscols=createFieldMap($surveyid);
    $excesscols=array_keys($excesscols);



    $afieldcount = count($excesscols);
    $exportoutput .= browsemenubar($clang->gT("Export results"));
    $exportoutput .= "<div class='header ui-widget-header'>".$clang->gT("Export results").'</div>'
    ."<div class='wrap2columns'>\n"
    ."<form id='resultexport' action='$scriptname?action=exportresults' method='post'><div class='left'>\n";

    if (isset($_POST['sql'])) {$exportoutput .= " - ".$clang->gT("Filtered from statistics script");}
    if (returnglobal('id')<>'') {$exportoutput .= " - ".$clang->gT("Single response");}

    if (incompleteAnsFilterstate() == "filter")
    {
        $selecthide="selected='selected'";
        $selectshow="";
        $selectinc="";
    }
    elseif (incompleteAnsFilterstate() == "inc")
    {
        $selecthide="";
        $selectshow="";
        $selectinc="selected='selected'";
    }
    else
    {
        $selecthide="";
        $selectshow="selected='selected'";
        $selectinc="";
    }

    //get max number of datasets

    $max_datasets_query = "SELECT COUNT(id) FROM {$dbprefix}survey_$surveyid";
    $max_datasets = $connect->GetOne($max_datasets_query);


    $exportoutput .='<fieldset><legend>'.$clang->gT("General").'</legend>'
    // form fields to limit export from X to Y
    ."<ul><li><label>".$clang->gT("Range:")."</label> ".$clang->gT("From")." <input type='text' name='export_from' size='8' value='1' />";
    $exportoutput .= " ".$clang->gT("to")." <input type='text' name='export_to' size='8' value='$max_datasets' /></li>"

    ."<li><br /><label for='filterinc'>".$clang->gT("Completion state")."</label> <select id='filterinc' name='filterinc'>\n"
    ."<option value='filter' $selecthide>".$clang->gT("Completed responses only")."</option>\n"
    ."<option value='show' $selectshow>".$clang->gT("All responses")."</option>\n"
    ."<option value='incomplete' $selectinc>".$clang->gT("Incomplete responses only")."</option>\n"
    ."\t</select>\n"
    ."</li></ul></fieldset>"

    .'<fieldset><legend>'
    .$clang->gT("Questions")."</legend>\n"
    ."<ul>\n"
    ."<li><input type='radio' class='radiobtn' name='exportstyle' value='abrev' id='headabbrev' />"
    ."<label for='headabbrev'>".$clang->gT("Abbreviated headings")."</label></li>\n"
    ."<li><input type='radio' class='radiobtn' checked name='exportstyle' value='full' id='headfull'  />"
    ."<label for='headfull'>".$clang->gT("Full headings")."</label></li>\n"
    ."<li><input type='radio' class='radiobtn' checked name='exportstyle' value='headcodes' id='headcodes' />"
    ."<label for='headcodes'>".$clang->gT("Question codes")."</label></li>\n"
    ."<li><br /><input type='checkbox' value='Y' name='convertspacetous' id='convertspacetous' />"
    ."<label for='convertspacetous'>"
    .$clang->gT("Convert spaces in question text to underscores")."</label></li>\n"
    ."</ul>\n"
    ."</fieldset>\n"
    
    ."<fieldset>\n"
    ."<legend>".$clang->gT("Answers")."</legend>\n"
    ."<ul>\n"
    ."<li><input type='radio' class='radiobtn' name='answers' value='short' id='ansabbrev' />"
    ."<label for='ansabbrev'>".$clang->gT("Answer Codes")."</label></li>";

    $exportoutput .= "<li><input type='checkbox' value='Y' name='convertyto1' id='convertyto1' style='margin-left: 25px' />"
    ."<label for='convertyto1'>".$clang->gT("Convert Y to")."</label> <input type='text' name='convertyto' size='3' value='1' maxlength='1' style='width:10px'  />";
    $exportoutput .= "</li>\n"
    ."<li><input type='checkbox' value='Y' name='convertnto2' id='convertnto2' style='margin-left: 25px' />"
    ."<label for='convertnto2'>".$clang->gT("Convert N to")."</label> <input type='text' name='convertnto' size='3' value='2' maxlength='1' style='width:10px' />";
    $exportoutput .= "</li><li>\n"
    ."<input type='radio' class='radiobtn' checked name='answers' value='long' id='ansfull' />"
    ."<label for='ansfull'>"
    .$clang->gT("Full Answers")."</label></li>\n"
    ."</ul></fieldset>"
    ."<fieldset><legend>".$clang->gT("Format")."</legend>\n"
    ."<ul>\n"
    ."<li>\n"
    ."\t<input type='radio' class='radiobtn' name='type' value='doc' id='worddoc' onclick='dument.getElementById(\"ansfull\").checked=true;document.getElementById(\"ansabbrev\").disabled=true;' />"
    ."<label for='worddoc'>"
    .$clang->gT("Microsoft Word (Latin charset)")."</label></li>\n"
    ."\t<li><input type='radio' class='radiobtn' name='type' value='xls' checked id='exceldoc'";
    if (!function_exists('iconv'))
    {
        $exportoutput.=' disabled="disabled" ';
    }
    $exportoutput.="onclick='document.getElementById(\"ansabbrev\").disabled=false;' />"
    ."<label for='exceldoc'>"
    .$clang->gT("Microsoft Excel (All charsets)");
    if (!function_exists('iconv'))
    {
        $exportoutput.='<font class="warningtitle">'.$clang->gT("(Iconv Library not installed)").'</font>';
    }
    $exportoutput.="</label></li>\n"
    ."\t<li><input type='radio' class='radiobtn' name='type' value='csv' id='csvdoc'";
    if (!function_exists('iconv'))
    {
        $exportoutput.=' checked="checked" ';
    }
    $exportoutput.=" onclick='document.getElementById(\"ansabbrev\").disabled=false;' />"
    ."<label for='csvdoc'>"
    .$clang->gT("CSV File (All charsets)")."</label></li>\n";
    if(isset($usepdfexport) && $usepdfexport == 1)
    {
        $exportoutput .= "\t<li><input type='radio' class='radiobtn' name='type' value='pdf' id='pdfdoc' onclick='document.getElementById(\"ansabbrev\").disabled=false;' />"
        ."<label for='pdfdoc'>"
        .$clang->gT("PDF")."<br />"
        ."</label></li>\n";
    }
    $exportoutput.="</ul></fieldset>\n"
    ."</div>\n"
    ."<div class='right'>\n"
    ."<fieldset>\n"
    ."<legend>".$clang->gT("Column control")."</legend>\n";

    $exportoutput.="\t<input type='hidden' name='sid' value='$surveyid' />\n";
    if (isset($_POST['sql']))
    {
        $exportoutput .= "\t<input type='hidden' name='sql' value=\""
        .stripcslashes($_POST['sql'])
        ."\" />\n";
    }
    if (returnglobal('id')<>'')
    {
        $exportoutput .= "\t<input type='hidden' name='answerid' value=\""
        .stripcslashes(returnglobal('id'))
        ."\" />\n";
    }

    $exportoutput .= $clang->gT("Choose Columns").":\n";

    if ($afieldcount > 255)
    {
        $exportoutput .= "\t<img src='$imageurl/help.gif' alt='".$clang->gT("Help")."' onclick='javascript:alert(\""
        .$clang->gT("Your survey contains more than 255 columns of responses. Spreadsheet applications such as Excel are limited to loading no more than 255. Select the columns you wish to export in the list below.","js")
        ."\")' />";
    }
    else
    {
        $exportoutput .= "\t<img src='$imageurl/help.gif' alt='".$clang->gT("Help")."' onclick='javascript:alert(\""
        .$clang->gT("Choose the columns you wish to export.","js")
        ."\")' />";
    }
    $exportoutput .= "<br /><select name='colselect[]' multiple size='20'>\n";
    $i=1;
    foreach($excesscols as $ec)
    {
        $exportoutput .= "<option value='$ec'";
        if (isset($_POST['summary']))
        {
            if (in_array($ec, $_POST['summary']))
            {
                $exportoutput .= "selected";
            }
        }
        elseif ($i<256)
        {
            $exportoutput .= " selected";
        }
        $exportoutput .= ">$i: $ec</option>\n";
        $i++;
    }
    $exportoutput .= "\t</select>\n";
    $exportoutput .= "<br />&nbsp;</fieldset>\n";
        //OPTIONAL EXTRAS (FROM TOKENS TABLE)
    // Find out if survey results are anonymous
    if ($thissurvey['anonymized'] == "N" && tableExists("tokens_$surveyid"))
        {
            $exportoutput .= "<fieldset><legend>".$clang->gT("Token control")."</legend>\n"
            .$clang->gT("Choose token fields").":"
            ."<img src='$imageurl/help.gif' alt='".$clang->gT("Help")."' onclick='javascript:alert(\""
            .$clang->gT("Your survey can export associated token data with each response. Select any additional fields you would like to export.","js")
            ."\")' /><br />"
            ."<select name='attribute_select[]' multiple size='20'>\n"
            ."<option value='first_name' id='first_name' />".$clang->gT("First name")."</option>\n"
            ."<option value='last_name' id='last_name' />".$clang->gT("Last name")."</option>\n"
            ."<option value='email_address' id='email_address' />".$clang->gT("Email address")."</option>\n"
            ."<option value='token' id='token' />".$clang->gT("Token")."</option>\n";

            $attrfieldnames=GetTokenFieldsAndNames($surveyid,true);
            foreach ($attrfieldnames as $attr_name=>$attr_desc)
            {
                $exportoutput .= "<option value='$attr_name' id='$attr_name' />".$attr_desc."</option>\n";
            }
            $exportoutput .= "</select></fieldset>\n";
        }
    $exportoutput .= "</div>\n"
    ."\t<div style='clear:both;'><p><input type='submit' value='".$clang->gT("Export data")."' /></div></form></div>\n";
    return;
}

<<<<<<< .working
=======




// ======================================================================
// Actual export routines start here !
// ======================================================================

$tokenTableExists=tableExists('tokens_'.$surveyid);
$aTokenFieldNames=array();

if ($tokenTableExists)
{
    $aTokenFieldNames=GetTokenFieldsAndNames($surveyid);
    $attributeFieldAndNames=GetTokenFieldsAndNames($surveyid,true);
    $attributeFields=array_keys($attributeFieldAndNames);
}

switch ( $_POST["type"] ) {
    case "doc":
        header("Content-Disposition: attachment; filename=results-survey".$surveyid.".doc");
        header("Content-type: application/vnd.ms-word");
        $separator="\t";
        break;
    case "xls":
        header("Content-Disposition: attachment; filename=results-survey".$surveyid.".xls");
        header("Content-type: application/vnd.ms-excel");
        $workbook = new PHPExcel();
        // Creating the first worksheet

        $query="SELECT * FROM {$dbprefix}surveys_languagesettings WHERE surveyls_survey_id=".$surveyid;
        $result=db_execute_assoc($query) or safe_die("Couldn't get privacy data<br />$query<br />".$connect->ErrorMsg());
        $row = $result->FetchRow();

        $sheet = $workbook->getActiveSheet();
        $row['surveyls_title']=str_replace(array('*', ':', '/', '\\', '?', '[', ']'),array(' '),$row['surveyls_title']); // Remove invalid characters
        $sheet->setTitle(substr($row['surveyls_title'],0,31));
        $separator="~|";
        break;
    case "csv":
        header("Content-Disposition: attachment; filename=results-survey".$surveyid.".csv");
        header("Content-type: text/comma-separated-values; charset=UTF-8");
        $separator=",";
        break;
    case "pdf":
        $pdf = new PDF($pdforientation,'mm','A4');
        $pdf->SetFont($pdfdefaultfont,'',$pdffontsize);
        $pdf->AddPage();
        $pdf->intopdf("PDF Export ".date("Y.m.d-H:i",time()));
        $query="SELECT * FROM {$dbprefix}surveys_languagesettings WHERE surveyls_survey_id=".$surveyid;
        $result=db_execute_assoc($query) or safe_die("Couldn't get privacy data<br />$query<br />".$connect->ErrorMsg());
        while ($row = $result->FetchRow())
        {
            $pdf->intopdf($clang->gT("General information in language: ").getLanguageNameFromCode($row['surveyls_language']),'B');
            $pdf->ln();
            $pdf->titleintopdf($row['surveyls_title'],$row['surveyls_description']);
            $surveyname=$row['surveyls_title'];
        }
        $pdf->AddPage();
        $separator="\t";
        break;
    default:
        header("Content-Disposition: attachment; filename=results-survey".$surveyid.".csv");
        header("Content-type: text/comma-separated-values; charset=UTF-8");
        $separator=",";
        break;
}
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: public");

>>>>>>> .merge-right.r10071
// Export Language is set by default to surveybaselang
// * the explang language code is used in SQL queries
// * the alang object is used to translate headers and hardcoded answers
// In the future it might be possible to 'post' the 'export language' from
// the exportresults form
$explang = $surveybaselang;
$elang=new limesurvey_lang($explang);

//Get together our FormattingOptions and then call into the exportSurvey 
//function.
$options = new FormattingOptions();
$options->selectedColumns = $_POST['colselect'];
$options->responseMinRecord = sanitize_int($_POST['export_from']) - 1;
$options->responseMaxRecord = sanitize_int($_POST['export_to']) - 1;
$options->answerFormat = $answers;
$options->convertN = $convertnto2;
if ($options->convertN)
{
    $options->nValue = $convertnto;
        } 
$options->convertY = $convertyto1;
if ($options->convertY)
        {
    $options->yValue = $convertyto;
        }
$options->format = $type;
$options->headerSpacesToUnderscores = $convertspacetous;
$options->headingFormat = $exportstyle;
$options->responseCompletionState = incompleteAnsFilterstate();

//If we have no data for the filter state then default to show all.
if (empty($options->responseCompletionState)) 
{
    $options->responseCompletionState = 'show';
    if (in_array('first_name',$_POST['attribute_select']))
    {
        $dquery .= ", {$dbprefix}tokens_$surveyid.firstname";
    }
    if (in_array('last_name',$_POST['attribute_select']))
    {
        $dquery .= ", {$dbprefix}tokens_$surveyid.lastname";
    }
    if (in_array('email_address',$_POST['attribute_select']))
    {
        $dquery .= ", {$dbprefix}tokens_$surveyid.email";
    }
    if (in_array('token',$_POST['attribute_select']))
    {
        $dquery .= ", {$dbprefix}tokens_$surveyid.token";
    }

    foreach ($attributeFields as $attr_name)
    {
        if (in_array($attr_name,$_POST['attribute_select']))
        {
            $dquery .= ", {$dbprefix}tokens_$surveyid.$attr_name";
        }
    }
}
if ($options->responseCompletionState == 'inc')
{
    $options->responseCompletionState = 'incomplete';
}

$resultsService = new ExportSurveyResultsService();
$resultsService->exportSurvey($surveyid, $explang, $options);

exit;
