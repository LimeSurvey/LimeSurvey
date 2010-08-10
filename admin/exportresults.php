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


if (!isset($imagefiles)) {$imagefiles="./images";}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($exportstyle)) {$exportstyle=returnglobal('exportstyle');}
if (!isset($answers)) {$answers=returnglobal('answers');}
if (!isset($type)) {$type=returnglobal('type');}
if (!isset($convertyto1)) {$convertyto1=returnglobal('convertyto1');}
if (!isset($convertnto2)) {$convertnto2=returnglobal('convertnto2');}
if (!isset($convertspacetous)) {$convertspacetous=returnglobal('convertspacetous');}

$sumquery5 = "SELECT b.* FROM {$dbprefix}surveys AS a INNER JOIN {$dbprefix}surveys_rights AS b ON a.sid = b.sid WHERE a.sid=$surveyid AND b.uid = ".$_SESSION['loginID']; //Getting rights for this survey and user
$sumresult5 = db_execute_assoc($sumquery5);
$sumrows5 = $sumresult5->FetchRow();

if ($sumrows5['export'] != "1" && $_SESSION['USER_RIGHT_SUPERADMIN'] != 1)
{
    exit;
}

include_once("login_check.php");
include_once(dirname(__FILE__)."/classes/pear/Spreadsheet/Excel/Writer.php");
include_once(dirname(__FILE__)."/classes/tcpdf/extensiontcpdf.php");

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
    $exportoutput .= browsemenubar($clang->gT("Export Results"));
    $exportoutput .= "<div class='header'>".$clang->gT("Export results").'</div>'
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
        $exportoutput .= "\t<img src='$imagefiles/help.gif' alt='".$clang->gT("Help")."' onclick='javascript:alert(\""
        .$clang->gT("Your survey contains more than 255 columns of responses. Spreadsheet applications such as Excel are limited to loading no more than 255. Select the columns you wish to export in the list below.","js")
        ."\")' />";
    }
    else
    {
        $exportoutput .= "\t<img src='$imagefiles/help.gif' alt='".$clang->gT("Help")."' onclick='javascript:alert(\""
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
    if ($thissurvey['private'] == "N" && tableExists("tokens_$surveyid"))
        {
            $exportoutput .= "<fieldset><legend>".$clang->gT("Token Control")."</legend>\n"
            .$clang->gT("Choose Token Fields").":"
            ."<img src='$imagefiles/help.gif' alt='".$clang->gT("Help")."' align='right' onclick='javascript:alert(\""
            .$clang->gT("Your survey can export associated token data with each response. Select any additional fields you would like to export.","js")
            ."\")' /><ul><li>\n"
            ."<input type='checkbox' class='checkboxbtn' name='first_name' id='first_name' />"
            ."<label for='first_name'>".$clang->gT("First Name")."</label></li>\n"
            ."<li><input type='checkbox' class='checkboxbtn' name='last_name' id='last_name' />"
            ."<label for='last_name'>".$clang->gT("Last Name")."</label></li>\n"
            ."<li><input type='checkbox' class='checkboxbtn' name='email_address' id='email_address' />"
            ."<label for='email_address'>".$clang->gT("Email")."</label></li>\n"
            ."<li><input type='checkbox' class='checkboxbtn' name='token' id='token' />"
            ."<label for='token'>".$clang->gT("Token")."</label></li>\n";

            $attrfieldnames=GetTokenFieldsAndNames($surveyid,true);
            foreach ($attrfieldnames as $attr_name=>$attr_desc)
            {
                $exportoutput .= "<li><input type='checkbox' class='checkboxbtn' name='$attr_name' id='$attr_name'>"
                ."<label for='$attr_name'>".$attr_desc."</label></li>\n";
            }
            $exportoutput .= "</ul></fieldset>\n";
        }
    $exportoutput .= "</div>\n"
    ."\t<div style='clear:both;'><p><input type='submit' value='".$clang->gT("Export data")."' /></div></form></div>\n";
    return;
}





// ======================================================================
// Actual export routines start here !
// ======================================================================

$tokenTableExists=tableExists('tokens_'.$surveyid);

if ($tokenTableExists)
{
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

        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion(8);
        // Inform the module that our data will arrive as UTF-8.
        // Set the temporary directory to avoid PHP error messages due to open_basedir restrictions and calls to tempnam("", ...)
        if (!empty($tempdir)) {
            $workbook->setTempDir($tempdir);
        }
        $workbook->send('results-survey'.$surveyid.'.xls');
        // Creating the first worksheet

        $query="SELECT * FROM {$dbprefix}surveys_languagesettings WHERE surveyls_survey_id=".$surveyid;
        $result=db_execute_assoc($query) or safe_die("Couldn't get privacy data<br />$query<br />".$connect->ErrorMsg());
        $row = $result->FetchRow();

        $sheet =& $workbook->addWorksheet(utf8_decode($row['surveyls_title']));
        $sheet->setInputEncoding('utf-8');
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

// Export Language is set by default to surveybaselang
// * the explang language code is used in SQL queries
// * the alang object is used to translate headers and hardcoded answers
// In the future it might be possible to 'post' the 'export language' from
// the exportresults form
$explang = $surveybaselang;
$elang=new limesurvey_lang($explang);

//STEP 1: First line is column headings

$fieldmap=createFieldMap($surveyid);

//Get the fieldnames from the survey table for column headings
$surveytable = "{$dbprefix}survey_$surveyid";
if (isset($_POST['colselect']))
{
    $selectfields="";
    foreach($_POST['colselect'] as $cs)
    {
        if ($tokenTableExists && $cs == 'token')
        {
            // We shouldnt include the token field when we are joining with the token field    
        } 
        elseif ($cs != 'completed')
        {
            $selectfields.= db_quote_id($cs).", ";
        }
        else
        {
            $selectfields.= "CASE WHEN $surveytable.submitdate IS NULL THEN 'N' ELSE 'Y' END AS completed, ";
        }
    }
    $selectfields = mb_substr($selectfields, 0, strlen($selectfields)-2);
}
else
{
    $selectfields="$surveytable.*, CASE WHEN $surveytable.submitdate IS NULL THEN 'N' ELSE 'Y' END AS completed";
}

$dquery = "SELECT $selectfields";
if (isset($_POST['first_name']) && $_POST['first_name']=="on")
{
    $dquery .= ", {$dbprefix}tokens_$surveyid.firstname";
}
if (isset($_POST['last_name']) && $_POST['last_name']=="on")
{
    $dquery .= ", {$dbprefix}tokens_$surveyid.lastname";
}
if (isset($_POST['email_address']) && $_POST['email_address']=="on")
{
    $dquery .= ", {$dbprefix}tokens_$surveyid.email";
}

if ($tokenTableExists && $thissurvey['private']=='N')
{
    if (isset($_POST['token']) && $_POST['token']=="on")
    {
        $dquery .= ", {$dbprefix}tokens_$surveyid.token";
    }

    foreach ($attributeFields as $attr_name)
    {
        if (isset($_POST[$attr_name]) && $_POST[$attr_name]=="on")
        {
            $dquery .= ", {$dbprefix}tokens_$surveyid.$attr_name";
        }
    }
}
$dquery .= " FROM $surveytable";

if ($tokenTableExists && $thissurvey['private']=='N')
{
    $dquery .= " LEFT OUTER JOIN {$dbprefix}tokens_$surveyid"
    . " ON $surveytable.token = {$dbprefix}tokens_$surveyid.token";
}
if (incompleteAnsFilterstate() == "filter")
{
    $dquery .= "  WHERE $surveytable.submitdate is not null ";
} elseif (incompleteAnsFilterstate() == "inc")
{
    $dquery .= "  WHERE $surveytable.submitdate is null ";
}

$dquery .=" ORDER BY id ";

$dresult = db_select_limit_assoc($dquery, 1) or safe_die($clang->gT("Error")." getting results<br />$dquery<br />".$connect->ErrorMsg());
$fieldcount = $dresult->FieldCount();
$firstline="";
$faid="";
for ($i=0; $i<$fieldcount; $i++)
{
    //Iterate through column names and output headings
    $field=$dresult->FetchField($i);
    $fieldinfo=$field->name;

    if ($fieldinfo == "lastname")
    {
        if ($type == "csv") {$firstline .= "\"".$elang->gT("Last Name")."\"$separator";}
        else {$firstline .= $elang->gT("Last Name")."$separator";}
    }
    elseif ($fieldinfo == "firstname")
    {
        if ($type == "csv") {$firstline .= "\"".$elang->gT("First Name")."\"$separator";}
        else {$firstline .= $elang->gT("First Name")."$separator";}
    }
    elseif ($fieldinfo == "email")
    {
        if ($type == "csv") {$firstline .= "\"".$elang->gT("Email Address")."\"$separator";}
        else {$firstline .= $elang->gT("Email Address")."$separator";}
    }
    elseif ($fieldinfo == "token")
    {
        if ($type == "csv") {$firstline .= "\"".$elang->gT("Token")."\"$separator";}
        else {$firstline .= $elang->gT("Token")."$separator";}
    }
    elseif (substr($fieldinfo,0,10)=="attribute_")
    {
        if ($type == "csv") {$firstline .= CSVEscape($fieldinfo)."$separator";}
        else {$firstline .= $attributeFieldAndNames[$fieldinfo]."$separator";}
    }
    elseif ($fieldinfo == "id")
    {
        if ($type == "csv") {$firstline .= "\"id\"$separator";}
        else {$firstline .= "id$separator";}
    }
    elseif ($fieldinfo == "datestamp")
    {
        if ($type == "csv") {$firstline .= "\"".$elang->gT("Date Last Action")."\"$separator";}
        else {$firstline .= $elang->gT("Date Last Action")."$separator";}
    }
    elseif ($fieldinfo == "startdate")
    {
        if ($type == "csv") {$firstline .= "\"".$elang->gT("Date Started")."\"$separator";}
        else {$firstline .= $elang->gT("Date Started")."$separator";}
    }
    elseif ($fieldinfo == "submitdate")
    {
        if ($type == "csv") {$firstline .= "\"".$elang->gT("Completed")."\"$separator";}
        else {$firstline .= $elang->gT("Completed")."$separator";}
    }
    elseif ($fieldinfo == "ipaddr")
    {
        if ($type == "csv") {$firstline .= "\"".$elang->gT("IP-Address")."\"$separator";}
        else {$firstline .= $elang->gT("IP-Address")."$separator";}
    }
    elseif ($fieldinfo == "refurl")
    {
        if ($type == "csv") {$firstline .= "\"".$elang->gT("Referring URL")."\"$separator";}
        else {$firstline .= $elang->gT("Referring URL")."$separator";}
    }
    elseif ($fieldinfo == "lastpage")
    {
        if ($type == "csv") {$firstline .= "\"".$elang->gT("Last page seen")."\"$separator";}
        else {$firstline .= $elang->gT("Last page seen")."$separator";}
    }
    elseif ($fieldinfo == "startlanguage")
    {
        if ($type == "csv") {$firstline .= "\"".$elang->gT("Start language")."\"$separator";}
        else {$firstline .= $elang->gT("Start language")."$separator";}
    }
    else
    {
        //Data field heading!
        $fielddata=$fieldmap[$fieldinfo];

        $fqid=$fielddata['qid'];
        $ftype=$fielddata['type'];
        $fsid=$fielddata['sid'];
        $fgid=$fielddata['gid'];
        $faid=$fielddata['aid'];
        if ($exportstyle == "abrev")
        {
            $qq = "SELECT question FROM {$dbprefix}questions WHERE qid=$fqid and language='$explang'";
            $qr = db_execute_assoc($qq);
            while ($qrow=$qr->FetchRow())
            {$qname=$qrow['question'];}
            $qname=strip_tags_full($qname);
            $qname=mb_substr($qname, 0, 15)."..";
            $firstline = str_replace("\n", "", $firstline);
            $firstline = str_replace("\r", "", $firstline);
            if ($type == "csv") {$firstline .= "\"$qname";}
            else {$firstline .= "$qname";}
            if (isset($faid)) {$firstline .= " [{$faid}]"; $faid="";}
            if ($ftype == ":" || $ftype == ";")
            {
                 
            }
            if ($type == "csv") {$firstline .= "\"";}
            $firstline .= "$separator";
        }
        else    //headcode or full answer
        {
            $qq = "SELECT question, type, other, title FROM {$dbprefix}questions WHERE qid=$fqid AND language='$explang' ORDER BY gid, title"; //get the question
            $qr = db_execute_assoc($qq) or safe_die ("ERROR:<br />".$qq."<br />".$connect->ErrorMsg());
            while ($qrow=$qr->FetchRow())
            {
                if ($exportstyle == "headcodes"){$fquest=$qrow['title'];}
                else {$fquest=$qrow['question'];}
            }
            switch ($ftype)
            {
                case "R": //RANKING TYPE
                    $fquest .= " [".$elang->gT("Ranking")." $faid]";
                    break;
                case "L":
                case "!":
                    if ($faid == "other") {
                        $fquest .= " [".$elang->gT("Other")."]";
                    }
                    break;
                case "O": //DROPDOWN LIST WITH COMMENT
                    if ($faid == "comment")
                    {
                        $fquest .= " - Comment";
                    }
                    break;
                case "M": //Multiple option
                    if ($faid == "other")
                    {
                        $fquest .= " [".$elang->gT("Other")."]";
                    }
                    else
                    {
                        if ($answers == "short") {
                            $fquest .= " [$faid]"; //Show only the code
                        }
                        else
                        {
                            $lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code = '$faid' AND language = '$explang'";
                            $lr = db_execute_assoc($lq);
                            while ($lrow = $lr->FetchRow())
                            {
                                $fquest .= " [".strip_tags_full($lrow['answer'])."]";
                            }
                        }
                    }
                    break;
                case "P": //Multiple option with comment
                    if (mb_substr($faid, -7, 7) == "comment")
                    {
                        $faid=mb_substr($faid, 0, -7);
                        $comment=true;
                    }
                    if ($faid == "other")
                    {
                        $fquest .= " [".$elang->gT("Other")."]";
                    }
                    else
                    {
                        if ($answers == "short") {
                            $fquest .= " [$faid]"; //Show only the code
                        }
                        else
                        {
                            $lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code = '$faid' AND language = '$explang'";
                            $lr = db_execute_assoc($lq);
                            while ($lrow = $lr->FetchRow())
                            {
                                $fquest .= " [".strip_tags_full($lrow['answer'])."]";
                            }
                        }
                    }
                    if (isset($comment) && $comment == true) {$fquest .= " - comment"; $comment=false;}
                    break;
                case "A":
                case "B":
                case "C":
                case "E":
                case "F":
                case "H":
                case "K":
                case "Q":
                case "^":
                    if ($answers == "short") {
                        $fquest .= " [$faid]";
                    }
                    else
                    {
                        $lq = "SELECT question FROM {$dbprefix}questions WHERE parent_qid=$fqid AND title= '$faid' AND language = '$explang'";
                        $lr = db_execute_assoc($lq);
                        while ($lrow=$lr->FetchRow())
                        {
                            $fquest .= " [".strip_tags_full($lrow['question'])."]";
                        }
                    }
                    break;
                case ":":
                case ";":
                    list($faid, $fcode) = explode("_", $faid);
                    if ($answers == "short") {
                        $fquest .= " [$faid] [$fcode]";
                    } else {
                        
                        $fquery = "SELECT sq.*, q.other"
                            ." FROM ".db_table_name('questions')." sq, ".db_table_name('questions')." q"
                            ." WHERE sq.sid=$surveyid AND sq.parent_qid=q.qid "
                            . "AND q.language='".GetBaseLanguageFromSurveyID($surveyid)."'"
                            ." AND sq.language='".GetBaseLanguageFromSurveyID($surveyid)."'"
                            ." AND q.qid={$fqid}
                               AND sq.scale_id=0
                               ORDER BY sq.question_order";
            
                            $y_axis_db = db_execute_assoc($fquery);
            
                            // Get the X-Axis   
                            $aquery = "SELECT sq.*
                                FROM ".db_table_name('questions')." q, ".db_table_name('questions')." sq 
                                WHERE q.sid=$surveyid 
                                AND sq.parent_qid=q.qid
                                AND q.language='".GetBaseLanguageFromSurveyID($surveyid)."'
                                AND sq.language='".GetBaseLanguageFromSurveyID($surveyid)."'
                                AND q.qid={$fqid}
                                AND sq.scale_id=1
                                ORDER BY sq.question_order";
              
                            $x_axis_db=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());

                           while ($arows = $y_axis_db->FetchRow())
                        {
                                while ($xrows = $x_axis_db->FetchRow())
                            {
                                $fquest .= " [".strip_tags_full($arows['question'])."] [".strip_tags_full($xrows['question'])."]";
                            }
                        }
                    }
                    break;
                case "1": // multi scale Headline
                    $iAnswerScale = substr($fieldinfo,-1)+1;
                    $lq = "select sq.question from {$dbprefix}questions as sq where sq.title='$faid' and parent_qid=$fqid AND sq.language='$surveybaselang' and sq.scale_id=0";
                    $lr = db_execute_assoc($lq);
                    $j=0;
                    while ($lrow=$lr->FetchRow())
                    {
                        $fquest .= " [".FlattenText($lrow['question'],true)."][Scale {$iAnswerScale}]";
                        $j++;
                    }
                    break;

            }
            $fquest=FlattenText($fquest,true);
            if ($type == "csv")
            {
                $firstline .="\"$fquest\"$separator";
            }
            else
            {
                $firstline .= $fquest.$separator;
            }
        }
        if($convertspacetous == "Y")
        {
            $firstline=str_replace(" ", "_", $firstline);
        }
    }
}
if ($type == "csv") { $firstline = mb_substr(trim($firstline),0,strlen($firstline)-1);}
else
{
    $firstline = trim($firstline);
}

$firstline .= "\n";

if ($type == "doc" || $type == "pdf")
{
    $flarray=explode($separator, $firstline);

}
else
if ($type == "xls")
{
    //var_dump ($firstline);
    $flarray=explode($separator, $firstline);
    $fli=0;
    foreach ($flarray as $fl)
    {
        $sheet->write(0,$fli,$fl);
        $fli++;
    }
    //print_r($fieldmap);
}
else
{
    $exportoutput .= $firstline; //Sending the header row
}


//calculate interval because the second argument at SQL "limit"
//is the number of records not the ending point
$from_record = sanitize_int($_POST['export_from']) - 1;
$limit_interval = sanitize_int($_POST['export_to']) - sanitize_int($_POST['export_from']) + 1;
$attributefieldAndNames=array();

//Now dump the data
if ($tokenTableExists && $thissurvey['private']=='N')
{
    $dquery = "SELECT $selectfields";
    if (isset($_POST['first_name']) && $_POST['first_name']=="on")
    {
        $dquery .= ", {$dbprefix}tokens_$surveyid.firstname";
    }
    if (isset($_POST['last_name']) && $_POST['last_name']=="on")
    {
        $dquery .= ", {$dbprefix}tokens_$surveyid.lastname";
    }
    if (isset($_POST['email_address']) && $_POST['email_address']=="on")
    {
        $dquery .= ", {$dbprefix}tokens_$surveyid.email";
    }
    if (isset($_POST['token']) && $_POST['token']=="on")
    {
        $dquery .= ", {$dbprefix}tokens_$surveyid.token";
    }
    foreach ($attributeFields as $attributefield)
    {
        if (isset($_POST[$attributefield]) && $_POST[$attributefield]=="on")
        {
            $dquery .= ", {$dbprefix}tokens_$surveyid.$attributefield";
        }
    }
    $dquery	.= " FROM $surveytable "
    . "LEFT OUTER JOIN {$dbprefix}tokens_$surveyid "
    . "ON $surveytable.token={$dbprefix}tokens_$surveyid.token ";
    if (incompleteAnsFilterstate() == "filter")
    {
        $dquery .= "  WHERE $surveytable.submitdate is not null ";
    } elseif (incompleteAnsFilterstate() == "inc")
    {
        $dquery .= "  WHERE $surveytable.submitdate is null ";
    }
}
else // this applies for exporting everything
{
    $dquery = "SELECT $selectfields FROM $surveytable ";

    if (incompleteAnsFilterstate() == "filter")
    {
        $dquery .= "  WHERE $surveytable.submitdate is not null ";
    } elseif (incompleteAnsFilterstate() == "inc")
    {
        $dquery .= "  WHERE $surveytable.submitdate is null ";
    }
}

if (isset($_POST['sql'])) //this applies if export has been called from the statistics package
{
    if ($_POST['sql'] != "NULL")
    {
        if (incompleteAnsFilterstate() == "filter" || incompleteAnsFilterstate() == "inc") {$dquery .= " AND ".stripcslashes($_POST['sql'])." ";}
        else {$dquery .= "WHERE ".stripcslashes($_POST['sql'])." ";}
    }
}
if (isset($_POST['answerid']) && $_POST['answerid'] != "NULL") //this applies if export has been called from single answer view
{
    if (incompleteAnsFilterstate() == "filter" || incompleteAnsFilterstate() == "inc") {$dquery .= " AND $surveytable.id=".stripcslashes($_POST['answerid'])." ";}
    else {$dquery .= "WHERE $surveytable.id=".stripcslashes($_POST['answerid'])." ";}
}


$dquery .= "ORDER BY $surveytable.id";
if ($answers == "short") //Nice and easy. Just dump the data straight
{
    //$dresult = db_execute_assoc($dquery);
    $dresult = db_select_limit_assoc($dquery, $limit_interval, $from_record);
    $rowcounter=0;
    while ($drow = $dresult->FetchRow())
    {
        $drow=array_map('strip_tags_full',$drow);
        if($convertyto1 == "Y")
        //Converts "Y" to "1" in export
        {
            $convertyto=returnglobal('convertyto');
            foreach($drow as $key=>$dr) {
                $fielddata=$fieldmap[$key];
                if(isset($fielddata['type']) &&
                ($fielddata['type'] == "M" ||
                $fielddata['type'] == "P" ||
                $fielddata['type'] == "Y")
                )
                {
                    if($dr == "Y") {$dr = $convertyto;}
                }
                $line[$key]=$dr;
            }
            $drow=$line;
        }
        if($convertnto2 == "Y")
        //Converts "N" to "2" in export
        {
            $convertnto=returnglobal('convertnto');
            foreach($drow as $key=>$dr) {
                $fielddata=$fieldmap[$key];
                if(isset($fielddata['type']) &&
                ($fielddata['type'] == "M" ||
                $fielddata['type'] == "P" ||
                $fielddata['type'] == "Y")
                )
                {
                    if($dr == "N") {$dr = $convertnto;}
                }
                $line[$key]=$dr;
            }
            $drow=$line;
        }
        $rowcounter++;
        if ($type == "csv")
        {
            $exportoutput .= "\"".implode("\"$separator\"", str_replace("\"", "\"\"", str_replace("\r\n", " ", $drow))) . "\"\n"; //create dump from each row
        }
        elseif ($type == "xls")
        {
            $colcounter=0;
            foreach ($drow as $rowfield)
            {
                $rowfield=str_replace("?","-",$rowfield);
                // Let's enclose in \" if begins by =
                if (substr($rowfield,0,1) ==  "=")
                {
                    $rowfield = "\"".$rowfield."\"";
                }
                $sheet->write($rowcounter,$colcounter,$rowfield);
                $colcounter++;
            }
        }
        else if($type == "pdf")
        {
            $pdf->titleintopdf($clang->gT("New Record"));
            foreach ($drow as $rowfield)
            {
                $rowfield=str_replace("?","-",$rowfield);
                $pdfstring .=$rowfield." | ";
            }
            $pdf->intopdf($pdfstring);
        }
        else
        {
            $exportoutput .= implode($separator, str_replace("\r\n", " ", $drow)) . "\n"; //create dump from each row
        }
    }
}
elseif ($answers == "long")        //chose complete answers
{

    $labelscache=array();
    //$dresult = db_execute_num($dquery) or safe_die("ERROR: $dquery -".$connect->ErrorMsg());
    $dresult = db_select_limit_num($dquery, $limit_interval, $from_record);
    $fieldcount = $dresult->FieldCount();
    $rowcounter=0;

    while ($drow = $dresult->FetchRow())
    {
        $rowcounter++;
        if ($type == "pdf")
        {
            //$pdf->Write (5,$exportoutput);
            if($rowcounter != 1)
            {
                $pdf->AddPage();
            }
            $pdf->Cell(0,10,$elang->gT('NEW RECORD')." ".$rowcounter,1,1);
        }

        if ($type == "doc")
        {
            $exportoutput .= "\n\n\n".$elang->gT('NEW RECORD')."\n";
        }
        for ($i=0; $i<$fieldcount; $i++) //For each field, work out the QID
        {
            $fqid=0;            // By default fqid is set to zero
            $field=$dresult->FetchField($i);
            $fieldinfo=$field->name;
            if ($fieldinfo != "startlanguage" && $fieldinfo != "id" && $fieldinfo != "datestamp" && $fieldinfo != "startdate" && $fieldinfo != "ipaddr"  && $fieldinfo != "refurl" && $fieldinfo != "token" && $fieldinfo != "firstname" && $fieldinfo != "lastname" && $fieldinfo != "email" && (substr($fieldinfo,0,10)!="attribute_") && $fieldinfo != "completed")
            {
                $fielddata=$fieldmap[$fieldinfo];
                $fqid=$fielddata['qid'];
                $ftype=$fielddata['type'];
                $fsid=$fielddata['sid'];
                $fgid=$fielddata['gid'];
                $faid=$fielddata['aid'];

                if ($type == "doc" || $type == "pdf")
                {
                    $ftitle=$flarray[$i];
                }
            }
            else
            {
                $fsid=""; $fgid="";
                if ($type == "doc" || $type == "pdf")
                {
                    switch($fieldinfo)
                    {
                        case "datestamp":
                            $ftitle=$elang->gT("Date Last Action").":";
                            break;
                        case "startdate":
                            $ftitle=$elang->gT("Date Started").":";
                            break;
                        case "ipaddr":
                            $ftitle=$elang->gT("IP Address").":";
                            break;
                        case "completed":
                            $ftitle=$elang->gT("Completed").":";
                            break;
                        case "refurl":
                            $ftitle=$elang->gT("Referring URL").":";
                            break;
                        case "firstname":
                            $ftitle=$elang->gT("First Name").":";
                            break;
                        case "lastname":
                            $ftitle=$elang->gT("Last Name").":";
                            break;
                        case "email":
                            $ftitle=$elang->gT("Email").":";
                            break;
                        case "id":
                            $ftitle=$elang->gT("ID").":";
                            break;
                        case "token":
                            $ftitle=$elang->gT("Token").":";
                            break;
                        case "tid":
                            $ftitle=$elang->gT("Token ID").":";
                            break;
                        case "startlanguage":
                            $ftitle=$elang->gT("Language").":";
                            break;
                        default:
                            if (substr($fieldinfo,0,10)=='attribute_')
                            {
                                $ftitle=$attributeFieldAndNames[$fieldinfo];
                            }
                            else
                            {
                                $fielddata=$fieldmap[$fieldinfo];
                                if (isset($fielddata['title']) && !isset($ftitle)) {$ftitle=$fielddata['title'].":";}
                            }
                    } // switch
                }
            }
            if ($fqid == 0)
            {
                $ftype = "-";  //   This is set if it not a normal answer field, but something like tokenID, First Name etc
            }
            if ($type == "csv") {$exportoutput .= "\"";}
            if ($type == "doc") {$exportoutput .= "\n$ftitle\n\t";}
            if ($type == "pdf"){ $pdf->intopdf($ftitle);}
            switch ($ftype)
            {
                case "-": //SPECIAL Placeholder TYPE
                    $exportoutput .= $drow[$i];
                    if($type == "pdf"){$pdf->intopdf($drow[$i]);}
                    break;
                case "R": //RANKING TYPE
                    $lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND language='$explang' AND code = ?";
                    $lr = db_execute_assoc($lq, array($drow[$i]));
                    while ($lrow = $lr->FetchRow())
                    {
                        $exportoutput .= strip_tags_full($lrow['answer']);
                        if($type == "pdf"){$pdf->intopdf(strip_tags_full($lrow['answer']));}
                    }
                    break;
                case "1":
                    if (mb_substr($fieldinfo,-1) == 0)
                    {
                        //$lq = "select a.*, l.*, l.code as lcode, l.title as ltitle from {$dbprefix}answers as a, {$dbprefix}labels as l where qid=$fqid AND l.lid =$flid AND a.language='$explang' AND l.code = ? group by l.lid";
                        $lq = "select answer as ltitle from {$dbprefix}answers where qid=$fqid AND language='$explang' and scale_id=0 AND code = ?";
                    }
                    else
                    {
                        //$lq = "select a.*, l.*, l.code as lcode, l.title as ltitle from {$dbprefix}answers as a, {$dbprefix}labels as l where qid=$fqid AND l.lid =$flid1 AND a.language='$explang' AND l.code = ? group by l.lid";
                        $lq = "select answer as ltitle from {$dbprefix}answers where qid=$fqid AND language='$explang' and scale_id=1 AND code = ?";
                    }
                    $lr = db_execute_assoc($lq, array($drow[$i])) or safe_die($lq."<br />ERROR:<br />".$connect->ErrorMsg());
                    while ($lrow = $lr->FetchRow())
                    {
                        $exportoutput .= strip_tags_full($lrow['ltitle']);
                        if($type == "pdf"){$pdf->intopdf(strip_tags_full($lrow['ltitle']));}
                    }
                    break;
                case "L": //DROPDOWN LIST
                case "!":
                    if (mb_substr($fieldinfo, -5, 5) == "other")
                    {
                        $exportoutput .= strip_tags_full($drow[$i]);
                        if($type == "pdf"){$pdf->intopdf($drow[$i]);}
                    }
                    else
                    {
                        if ($drow[$i] == "-oth-")
                        {
                            $exportoutput .= $elang->gT("Other");
                            if($type == "pdf"){$pdf->intopdf($elang->gT("Other"));}
                        }
                        else
                        {
                            $lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND language='$explang' AND code = ?";
                            $lr = db_execute_assoc($lq, array($drow[$i])) or safe_die($lq."<br />ERROR:<br />".$connect->ErrorMsg());
                            while ($lrow = $lr->FetchRow())
                            {
                                //if ($lrow['code'] == $drow[$i]) {$exportoutput .= $lrow['answer'];}
                                if ($type == "csv")
                                {
                                    $exportoutput .= str_replace("\"", "\"\"", strip_tags_full($lrow['answer']));
                                    if($type == "pdf"){$pdf->intopdf(str_replace("\"", "\"\"", strip_tags_full($lrow['answer'])));}
                                }
                                else
                                {
                                    $exportoutput .= strip_tags_full($lrow['answer']);
                                    if($type == "pdf"){$pdf->intopdf(strip_tags_full($lrow['answer']));}
                                }

                            }
                        }
                    }
                    break;
                case "O": //DROPDOWN LIST WITH COMMENT
                    $lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND language='$explang' ORDER BY answer";
                    $lr = db_execute_assoc($lq) or safe_die ("Could do it<br />$lq<br />".$connect->ErrorMsg());
                    $found = "";
                    while ($lrow = $lr->FetchRow())
                    {
                        if ($lrow['code'] == $drow[$i])
                        {
                            $exportoutput .= strip_tags_full($lrow['answer']);
                            $found = "Y";
                            if($type == "pdf"){$pdf->intopdf(strip_tags_full($lrow['answer']));}
                        }
                    }
                    //This following section exports the comment field
                    if ($found != "Y")
                    {
                        if ($type == "csv")
                        {$exportoutput .= str_replace("\r\n", "\n", str_replace("\"", "\"\"", strip_tags_full($drow[$i])));}
                        else if ($type == "pdf")
                        {$pdf->intopdf(str_replace("\r\n", " ", strip_tags_full($drow[$i])));}
                        else
                        {$exportoutput .= str_replace("\r\n", " ", $drow[$i]);}
                    }
                    break;
                case "Y": //YES\NO
                    switch($drow[$i])
                    {
                        case "Y":
                            $exportoutput .= $elang->gT("Yes");
                            if($type == "pdf"){$pdf->intopdf($elang->gT("Yes"));}
                            break;
                        case "N":
                            $exportoutput .= $elang->gT("No");
                            if($type == "pdf"){$pdf->intopdf($elang->gT("No"));}
                            break;
                        default:
                            $exportoutput .= $elang->gT("N/A");
                            if($type == "pdf"){$pdf->intopdf($elang->gT("N/A"));}
                            break;
                    }
                    break;
                case "G": //GENDER
                    switch($drow[$i])
                    {
                        case "M":
                            $exportoutput .= $elang->gT("Male");
                            if($type == "pdf"){$pdf->intopdf($elang->gT("Male"));}
                            break;
                        case "F":
                            $exportoutput .= $elang->gT("Female");
                            if($type == "pdf"){$pdf->intopdf($elang->gT("Female"));}
                            break;
                        default:
                            $exportoutput .= $elang->gT("N/A");
                            if($type == "pdf"){$pdf->intopdf($elang->gT("N/A"));}
                            break;
                    }
                    break;
                case "M": //multioption
                case "P":
                    if (mb_substr($fieldinfo, -5, 5) == "other")
                    {
                        $exportoutput .= strip_tags_full($drow[$i]);
                        if($type == "pdf"){$pdf->intopdf($drow[$i]);}
                    }
                    elseif (mb_substr($fieldinfo, -7, 7) == "comment")
                    {
                        $exportoutput .= strip_tags_full($drow[$i]);
                        if($type == "pdf"){$pdf->intopdf($drow[$i]);}
                    }
                    else
                    {
                        switch($drow[$i])
                        {
                            case "Y":
                                $exportoutput .= $elang->gT("Yes");
                                if($type == "pdf"){$pdf->intopdf($elang->gT("Yes"));}
                                break;
                            case "N":
                                $exportoutput .= $elang->gT("No");
                                if($type == "pdf"){$pdf->intopdf($elang->gT("No"));}
                                break;
                            case "":
                                $exportoutput .= $elang->gT("No");
                                if($type == "pdf"){$pdf->intopdf($elang->gT("No"));}
                                break;
                            default:
                                $exportoutput .= $drow[$i];
                                if($type == "pdf"){$pdf->intopdf($drow[$i]);}
                                break;
                        }
                    }
                    break;
                case "C":
                    switch($drow[$i])
                    {
                        case "Y":
                            $exportoutput .= $elang->gT("Yes");
                            if($type == "pdf"){$pdf->intopdf($elang->gT("Yes")); }
                            break;
                        case "N":
                            $exportoutput .= $elang->gT("No");
                            if($type == "pdf"){$pdf->intopdf($elang->gT("No")); }
                            break;
                        case "U":
                            $exportoutput .= $elang->gT("Uncertain");
                            if($type == "pdf"){$pdf->intopdf($elang->gT("Uncertain"));}
                            break;
                    }
                case "E":
                    switch($drow[$i])
                    {
                        case "I":
                            $exportoutput .= $elang->gT("Increase");
                            if($type == "pdf"){$pdf->intopdf($elang->gT("Increase"));}
                            break;
                        case "S":
                            $exportoutput .= $elang->gT("Same");
                            if($type == "pdf"){$pdf->intopdf($elang->gT("Same"));}
                            break;
                        case "D":
                            $exportoutput .= $elang->gT("Decrease");
                            if($type == "pdf"){$pdf->intopdf($elang->gT("Decrease"));}
                            break;
                    }
                    break;
                case "F":
                case "H":
                    if (!isset($labelscache[$fqid.'|'.$explang.'|'.$drow[$i]]))
                    {
                        $fquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND language='$explang' AND scale_id=0 AND code='{$drow[$i]}'";
                        $fresult = db_execute_assoc($fquery) or safe_die("ERROR:".$fquery."\n".$qq."\n".$connect->ErrorMsg());
                        if ($fresult)
                        {
                            $frow=$fresult->FetchRow();
                            if($type == "pdf"){$pdf->intopdf(strip_tags_full($frow['answer']));}
                            $exportoutput .= strip_tags_full($frow['answer']);
                            $labelscache[$fqid.'|'.$explang.'|'.$drow[$i]]=strip_tags_full($frow['answer']);
                        }
                    }
                    else
                    {
                        $exportoutput .=$labelscache[$fqid.'|'.$explang.'|'.$drow[$i]];
                        if($type == "pdf"){$pdf->intopdf($labelscache[$fqid.'|'.$explang.'|'.$drow[$i]]);}
                    }
                    break;
                case "1": //dual scale
                    if (mb_substr($fieldinfo,-1) == '0')
                    {
                        $strlabel = "1";
                        $lq = "select title from {$dbprefix}labels as l where l.lid = $flid AND l.language='$surveybaselang'";
                    }
                    else
                    {
                        $strlabel = "2";
                        $lq = "select title from {$dbprefix}labels as l where l.lid = $flid1 AND l.language='$surveybaselang'";
                    }
                    $lr = db_execute_assoc($lq);
                    while ($lrow=$lr->FetchRow())
                    {
                        $fquest .= " [".strip_tags_full($lrow['title'])."][".strip_tags_full($strlabel).". label]";
                    }

                    break;

                default: $tempresult=$dresult->FetchField($i);
                if ($tempresult->name == "token")
                {
                    $tokenquery = "SELECT firstname, lastname FROM {$dbprefix}tokens_$surveyid WHERE token='$drow[$i]'";
                    if ($tokenresult = db_execute_assoc($tokenquery)) //or safe_die ("Couldn't get token info<br />$tokenquery<br />".$connect->ErrorMsg());
                    while ($tokenrow=$tokenresult->FetchRow())
                    {
                        $exportoutput .= "{$tokenrow['lastname']}, {$tokenrow['firstname']}";
                        if($type == "pdf"){$pdf->intopdf($tokenrow['lastname']." , ".$tokenrow['firstname']);}
                    }
                    else
                    {
                        $exportoutput .= $elang->gT("Tokens problem - token table missing");
                        if($type == "pdf"){$pdf->intopdf($elang->gT("Tokens problem - token table missing"));}
                    }
                }
                else
                {
                    if ($type == "csv")
                    {$exportoutput .= str_replace("\r\n", "\n", str_replace("\"", "\"\"", strip_tags_full($drow[$i])));}
                    else if ($type == "pdf")
                    {$pdf->intopdf(trim(strip_tags($drow[$i])));}
                    else if ($type == "doc")
                    {$pdf->intopdf(trim(strip_tags($drow[$i])));}
                    else
                    {$exportoutput .= str_replace("\r\n", " ", $drow[$i]);}
                }
            }
            if ($type == "csv") {$exportoutput .= "\"";}
            $exportoutput .= "$separator";
            $ftype = "";
        }
        $exportoutput=mb_substr($exportoutput,0,-(strlen($separator)));
        IF ($type=='xls')
        {
            $rowarray=explode($separator, $exportoutput);
            $fli=0;
            foreach ($rowarray as $row)
            {
                // Let's enclose in \" if begins by =
                if (substr($row,0,1) ==  "=")
                {
                    $row = "\"".$row."\"";
                }
                $sheet->write($rowcounter,$fli,$row);
                $fli++;
            }
            $exportoutput='';
        }
        else {$exportoutput .= "\n";}
    }
}
if ($type=='xls')
{
    $workbook->close();
}
else if($type=='pdf')
{
    $pdf->Output($clang->gT($surveyname)." ".$surveyid.".pdf","DD");
}
else
{
    echo $exportoutput;
}
exit;


function strip_tags_full($string) {
    $string=html_entity_decode($string, ENT_QUOTES, "UTF-8");
    mb_regex_encoding('utf-8');
    $pattern = array('\r', '\n', '-oth-');
    for ($i=0; $i<sizeof($pattern); $i++) {
        $string = mb_ereg_replace($pattern[$i], '', $string);
    }
    return strip_tags($string);
}

?>
