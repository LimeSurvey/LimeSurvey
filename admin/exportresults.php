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

if (!$exportstyle)
{
    // Get info about the survey
    $thissurvey=getSurveyInfo($surveyid);

    // First add the standard fields 
    $excesscols[]="id";
    $excesscols[]='completed';
    if ($thissurvey["datestamp"]=='Y') {$excesscols[]='datestamp';}
    if ($thissurvey["datestamp"]=='Y') {$excesscols[]='startdate';}
    if ($thissurvey["ipaddr"]=='Y') {$excesscols[]='ipaddr';}
    if ($thissurvey["refurl"]=='Y') {$excesscols[]='refurl';}    
    
	//FIND OUT HOW MANY FIELDS WILL BE NEEDED - FOR 255 COLUMN LIMIT
	$query=" SELECT other, q.type, q.gid, q.qid, q.lid, q.lid1 FROM {$dbprefix}questions as q, {$dbprefix}groups as g "
	." where q.gid=g.gid and g.sid=$surveyid and g.language='$surveybaselang' and q.language='$surveybaselang'"
	." order by group_order, question_order";

	$result=db_execute_assoc($query) or safe_die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
	while ($rows = $result->FetchRow())
	{
		if (($rows['type']=='A') || ($rows['type']=='B')||($rows['type']=='C')||($rows['type']=='M')||($rows['type']=='P')||($rows['type']=='Q')||($rows['type'] == "K") ||($rows['type']=='E')||($rows['type']=='F')||($rows['type']=='H'))
		{
			$detailquery="select code from {$dbprefix}answers where qid=".$rows['qid']." and language='$surveybaselang' order by sortorder,code";
			$detailresult=db_execute_assoc($detailquery) or safe_die("Couldn't find detailfields<br />$detailquery<br />".$connect->ErrorMsg());
			while ($detailrows = $detailresult->FetchRow())
			{
				$excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid'].$detailrows['code'];
				if ($rows['type']=='P')
				{
					$excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid'].$detailrows['code']."comment";
				}
			}
		}
		elseif ($rows['type']==":" || $rows['type'] == ";")
		{
			$detailquery="select code from {$dbprefix}answers where qid=".$rows['qid']." and language='$surveybaselang' order by sortorder,code";
			$detailresult=db_execute_assoc($detailquery) or die("Couldn't find detailfields<br />$detailquery<br />".htmlspecialchars($connect->ErrorMsg()));
			while ($detailrows = $detailresult->FetchRow())
			{
			    $detailquery2="SELECT * 
				               FROM {$dbprefix}labels 
							   WHERE lid=".$rows['lid']."
							   AND language='$surveybaselang'
							   ORDER BY sortorder, title";
				$detailresult2=db_execute_assoc($detailquery2) or die("Couldn't find labels<br />$detailquery2<br />".htmlspecialchars($connect->ErrorMsg()));
				while ($dr2 = $detailresult2->FetchRow())
				{
				    $excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid'].$detailrows['code']."_".$dr2['code'];
			    }
			}
		}
		elseif ($rows['type']=='R')
		{
			$detailquery="select code from {$dbprefix}answers where qid=".$rows['qid']." and language='$surveybaselang' order by sortorder,code";
			$detailresult=db_execute_assoc($detailquery) or safe_die("Couldn't find detailfields<br />$detailquery<br />".$connect->ErrorMsg());
			$i=1;
			while ($detailrows = $detailresult->FetchRow())
			{
				$excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid'].$i;
				$i++;
			}
		}
		elseif ($rows['type']=='1')
		{
			// $detailquery="select code from {$dbprefix}answers where qid=".$rows['qid']." and language='$surveybaselang' order by sortorder,code";
			$detailquery="select a.code, l.lid from {$dbprefix}answers as a, {$dbprefix}labels as l where qid=".$rows['qid']." AND (l.lid =".$rows['lid'].") and a.language='$surveybaselang' group by a.code order by a.code ";
			$detailresult=db_execute_assoc($detailquery) or safe_die("Couldn't find detailfields<br />$detailquery<br />".$connect->ErrorMsg());
			$i=0;
			while ($detailrows = $detailresult->FetchRow())
			{
				// $excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid'].$rows['code'].$detailrows['code']."#".$i;
                $excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid'].$detailrows['code']."#0";
				$i++;
			}
            // second scale
            $detailquery="select a.code, l.lid from {$dbprefix}answers as a, {$dbprefix}labels as l where qid=".$rows['qid']." AND (l.lid =".$rows['lid1'].") and a.language='$surveybaselang' group by a.code order by a.code ";
            $detailresult=db_execute_assoc($detailquery) or safe_die("Couldn't find detailfields<br />$detailquery<br />".$connect->ErrorMsg());
            $i=0;
            while ($detailrows = $detailresult->FetchRow())
            {
                // $excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid'].$rows['code'].$detailrows['code']."#".$i;
                $excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid'].$detailrows['code']."#1";
                $i++;
            }
            
		}
		else
		{
			$excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid'];
		}
		if ($rows['other']=="Y" && ($rows['type']=='M' || $rows['type']=='!'|| $rows['type']=='L' || $rows['type']=='P' || $rows['type'] == "Z" || $rows['type'] == "W"))
		{
			$excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid']."other";
		}
		if ($rows['other']=="Y" && ($rows['type']=='P' ))
		{
			$excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid']."othercomment";
		}
		if ($rows['type']=='O' )
		{
			$excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid']."comment";
		}

	}



	$afieldcount = count($excesscols);
    $exportoutput .= browsemenubar($clang->gT("Export Results"));
	$exportoutput .= "<br />\n"
	."<form action='$scriptname?action=exportresults' method='post'>\n"
	."<table align='center'><tr>"
	."<td valign='top'>\n"
	."<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	."\t<tr><td colspan='2' height='4'>"
	."<strong>"
	.$clang->gT("Export responses");
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

	$exportoutput .= "</strong> ($afieldcount ".$clang->gT("Columns").")</td></tr>\n"
	."\t<tr><td height='8'><font size='1'><strong>"
	.$clang->gT("Questions")."</strong></font></td></tr>\n"
	."\t<tr>\n"
	."\t\t<td>\n"
	."\t\t\t<input type='radio' class='radiobtn' name='exportstyle' value='abrev' id='headabbrev'>"
	."<font size='1'><label for='headabbrev'>"
	.$clang->gT("Abbreviated headings")."</label><br />\n"
	."\t\t\t<input type='radio' class='radiobtn' checked name='exportstyle' value='full' id='headfull'>"
	."<label for='headfull'>"
	.$clang->gT("Full headings")."</label><br />\n"
	."\t\t\t<input type='radio' class='radiobtn' checked name='exportstyle' value='headcodes' id='headcodes'>"
	."<label for='headcodes'>"
	.$clang->gT("Question codes")."</label><br />\n"
	
	."\t\t\t<input type='checkbox' value='Y' name='convertspacetous' id='convertspacetous'>"
	."<font size='1'><label for='convertspacetous'>"
	.$clang->gT("Convert spaces in question text to underscores")."</label><br />"
	
	."\t\t\t&nbsp ".$clang->gT("Include")." <select name='filterinc'>\n"
	."\t\t\t\t<option value='filter' $selecthide>".$clang->gT("Completed Records Only")."</option>\n"
	."\t\t\t\t<option value='show' $selectshow>".$clang->gT("All Records")."</option>\n"
	."\t\t\t\t<option value='incomplete' $selectinc>".$clang->gT("Incomplete Records Only")."</option>\n"
	."\t\t\t</select>\n"
	."\t\t</font></font></td>\n"
	."\t</tr>\n"
	."\t<tr><td height='8'><font size='1'><strong>"
	.$clang->gT("Answers")."</strong></font></font></td></tr>\n"
	."\t<tr>\n"
	."\t\t<td>\n"
	."\t\t\t<input type='radio' class='radiobtn' name='answers' value='short' id='ansabbrev'>"
	."<font size='1'><label for='ansabbrev'>"
	.$clang->gT("Answer Codes")."</label>";
	
	$exportoutput .= "<br />\n"
	."\t\t\t<input type='checkbox' value='Y' name='convertyto1' id='convertyto1' style='margin-left: 25px'>"
	."<font size='1'><label for='convertyto1'>"
	.$clang->gT("Convert Y to 1")."</label>";

     $exportoutput .= "<br />\n"
	."\t\t\t<input type='radio' class='radiobtn' checked name='answers' value='long' id='ansfull'>"
	."<label for='ansfull'>"
	.$clang->gT("Full Answers")."</label>\n"
	."\t\t</font></td>\n"
	."\t</tr>\n"
	."\t<tr><td><font size='1'><strong>"
	.$clang->gT("Format")."</strong></font></td></tr>\n"
	."\t<tr>\n"
	."\t\t<td>\n"
	."\t\t\t<input type='radio' class='radiobtn' name='type' value='doc' id='worddoc' onclick='dument.getElementById(\"ansfull\").checked=true;document.getElementById(\"ansabbrev\").disabled=true;'>"
	."<font size='1'><label for='worddoc'>"
	.$clang->gT("Microsoft Word (Latin charset)")."</label><br />\n"
	."\t\t\t<input type='radio' class='radiobtn' name='type' value='xls' checked id='exceldoc'";
    if (!function_exists('iconv'))  
    {
      $exportoutput.=' disabled="disabled" ';
    }    
    $exportoutput.="onclick='document.getElementById(\"ansabbrev\").disabled=false;'>"
	."<label for='exceldoc'>"
	.$clang->gT("Microsoft Excel (All charsets)");
     if (!function_exists('iconv'))
    {
      $exportoutput.='<br /><font class="warningtitle">'.$clang->gT("(Iconv Library not installed)").'</font>';
    }
    $exportoutput.="</label><br />\n"
	."\t\t\t<input type='radio' class='radiobtn' name='type' value='csv' id='csvdoc'";
    if (!function_exists('iconv'))  
    {
      $exportoutput.=' checked="checked" ';
    }    
    $exportoutput.=" onclick='document.getElementById(\"ansabbrev\").disabled=false;'>"
	."<label for='csvdoc'>"
	.$clang->gT("CSV File (All charsets)")."</label><br />\n";
    if(isset($usepdfexport) && $usepdfexport == 1)
    {
	    $exportoutput .= "\t\t\t<input type='radio' class='radiobtn' name='type' value='pdf' id='pdfdoc' onclick='document.getElementById(\"ansabbrev\").disabled=false;'>"
	    ."<label for='pdfdoc'>"
	    .$clang->gT("PDF")."<br />"
        ."</label>\n";
    }
    
    
    //get max number of datasets
    $max_datasets = 0;
    
    $max_datasets_query = "SELECT COUNT(id) FROM {$dbprefix}survey_$surveyid";    
    $max_datasets_result = db_execute_num($max_datasets_query);
    
    while($max = $max_datasets_result -> FetchRow())
    {
    	$max_datasets = $max[0];
    }
    
    // form fields to limit export from X to Y
    $exportoutput .= "<br /> ".$clang->gT("from")." <input type='text' name='export_from' size='8' value='1'>";
    $exportoutput .= " ".$clang->gT("to")." <input type='text' name='export_to' size='8' value='$max_datasets'>";
    
    
	$exportoutput .="\t\t</font></font></td>\n"
	."\t</tr>\n"
	."\t<tr><td height='2' bgcolor='silver'></td></tr>\n"
	."\t<tr>\n"
	."\t\t<td align='center'>\n"
	."\t\t\t<input type='submit' value='"
	.$clang->gT("Export Data")."'>\n"
	."\t\t\t<input type='hidden' name='sid' value='$surveyid'>\n"
	."\t\t</font></td>\n"
	."\t</tr>\n"
	."\t<tr>\n"
	."\t\t<td align=\"center\" bgcolor='silver'>\n";
	if (isset($_POST['sql']))
	{
		$exportoutput .= "\t<input type='hidden' name='sql' value=\""
		.stripcslashes($_POST['sql'])
		."\">\n";
	}
	if (returnglobal('id')<>'')
	{
		$exportoutput .= "\t<input type='hidden' name='answerid' value=\""
		.stripcslashes(returnglobal('id'))
		."\">\n";
	}
	$exportoutput .= "</td>\n"
	."\t</tr>\n"
	."</table>\n"
	."</td>";
	$query="SELECT private FROM {$dbprefix}surveys WHERE sid=$surveyid"; //Find out if tokens are used
	$result=db_execute_assoc($query) or safe_die("Couldn't get privacy data<br />$query<br />".$connect->ErrorMsg());
	while ($rows = $result->FetchRow()) {$surveyprivate=$rows['private'];}
	if ($surveyprivate == "N")
	{
		$query=db_select_tables_like("{$dbprefix}tokens_$surveyid"); //SEE IF THERE IS AN ASSOCIATED TOKENS TABLE
		$result=$connect->Execute($query) or safe_die("Couldn't get table list<br />$query<br />".$connect->ErrorMsg());
		$tablecount=$result->RecordCount();
	}
	$exportoutput .= "<td valign='top'>\n"
	."<table align='center' width='150' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>"
	."\t<tr>\n"
	."\t\t<td height='8'><strong>"
	.$clang->gT("Column Control")."</strong>\n"
	."\t\t</td>\n"
	."\t</tr>\n"
	."\t<tr>\n"
	."\t\t<td height='8'><strong><font size='1'>\n"
	."\t\t\t".$clang->gT("Choose Columns").":\n"
	."\t\t</font></strong>";
	if ($afieldcount > 255)
	{
		$exportoutput .= "\t\t\t<img src='$imagefiles/help.gif' alt='".$clang->gT("Help")."' align='right' onclick='javascript:alert(\""
		.$clang->gT("Your survey contains more than 255 columns of responses. Spreadsheet applications such as Excel are limited to loading no more than 255. Select the columns you wish to export in the list below.","js")
		."\")'>";
	}
	else
	{
		$exportoutput .= "\t\t\t<img src='$imagefiles/help.gif' alt='".$clang->gT("Help")."' align='right' onclick='javascript:alert(\""
		.$clang->gT("Choose the columns you wish to export.","js")
		."\")'>";
	}
	$exportoutput .= "\t\t</font></td>\n"
	."\t</tr>\n"
	."\t<tr>\n"
	."\t\t<td align='center'><font size='1'>\n"
	."\t\t\t<select name='colselect[]' multiple size='15'>\n";
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
	$exportoutput .= "\t\t\t</select><br />\n"
	."\t\t<img src='$imagefiles/blank.gif' height='7' alt=''></font></font></td>\n"
	."\t</tr>\n"
	."</table>\n"
	."</td>\n";
	if (isset($tablecount) && $tablecount > 0) //Do second column
	{
		//OPTIONAL EXTRAS (FROM TOKENS TABLE)
		if ($tablecount > 0)
		{
			$exportoutput .= "<td valign='top'>\n"
			."<table align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>"
			."\t<tr>\n"
			."\t\t<td height='8'><font face='verdana' size='1'><strong>"
			.$clang->gT("Token Control")."</strong>\n"
			."\t\t</font></td>\n"
			."\t</tr>\n"
			."\t<tr>\n"
			."\t\t<td height='8'><strong><font size='1'>\n"
			.$clang->gT("Choose Token Fields").":"
			."\t\t</font></font></strong></td>\n"
			."\t</tr>\n"
			."\t<tr>\n"
			."\t\t<td><font size='1'>"
			."<img src='$imagefiles/help.gif' alt='".$clang->gT("Help")."' align='right' onclick='javascript:alert(\""
			.$clang->gT("Your survey can export associated token data with each response. Select any additional fields you would like to export.","js")
			."\")' /><br /><br />\n"
			."<input type='checkbox' class='checkboxbtn' name='first_name' id='first_name'>"
			."<label for='first_name'>".$clang->gT("First Name")."</label><br />\n"
			."<input type='checkbox' class='checkboxbtn' name='last_name' id='last_name'>"
			."<label for='last_name'>".$clang->gT("Last Name")."</label><br />\n"
			."<input type='checkbox' class='checkboxbtn' name='email_address' id='email_address'>"
			."<label for='email_address'>".$clang->gT("Email")."</label><br />\n"
			."<input type='checkbox' class='checkboxbtn' name='token' id='token'>"
			."<label for='token'>".$clang->gT("Token")."</label><br />\n";
			$query = "SELECT * FROM {$dbprefix}tokens_$surveyid"; //SEE IF TOKENS TABLE HAS ATTRIBUTE FIELDS
			$result = db_select_limit_assoc($query, 1) or safe_die ($query."<br />".$connect->ErrorMsg());
			$rowcount = $result->FieldCount();
			if ($rowcount > 7)
			{
				$exportoutput .= "<input type='checkbox' class='checkboxbtn' name='attribute_1' id='attribute_1'>"
				."<label for='attribute_1'>".$clang->gT("Attribute 1")."</label><br />\n"
				."<input type='checkbox' class='checkboxbtn' name='attribute_2' id='attribute_2'>"
				."<label for='attribute_2'>".$clang->gT("Attribute 2")."</label><br />\n";
			}
			$exportoutput .= "\t\t</font></font></td>\n"
			."\t</tr>\n"
			."</table>"
			."</td>";
		}
	}
	$exportoutput .= "</tr>\n"
	."</table><br />\n"
	."\t</form>\n";
	return;
}

//HERE WE EXPORT THE ACTUAL RESULTS

//sendcacheheaders();             // sending "cache headers" before this permit us to send something else than a "text/html" content-type
switch ( $_POST["type"] ) {     // this is a step to register_globals = false ;c)
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
      $sheet =& $workbook->addWorksheet('Survey Results');
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
Header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

// Export Language is set by default to surveybaselang
// * the explang language code is used in SQL queries
// * the alang object is used to translate headers and hardcoded answers
// In the future it might be possible to 'post' the 'export language' from
// the exportresults form
$explang = $surveybaselang;
$elang=new limesurvey_lang($explang);

//STEP 1: First line is column headings

$fieldmap=createFieldMap($surveyid);

// We make the fieldmap alot more accesible by using the SGQA identifier as key 
// so we do not need ArraySearchByKey later
foreach ($fieldmap as $fieldentry)
{
    $outmap[]=$fieldentry['fieldname'];
    $outmap[$fieldentry['fieldname']]['type']= $fieldentry['type'];
    $outmap[$fieldentry['fieldname']]['sid']= $fieldentry['sid'];
    $outmap[$fieldentry['fieldname']]['gid']= $fieldentry['gid'];
    $outmap[$fieldentry['fieldname']]['qid']= $fieldentry['qid'];
    $outmap[$fieldentry['fieldname']]['aid']= $fieldentry['aid'];
    if (isset($fieldentry['lid1'])) {$outmap[$fieldentry['fieldname']]['lid1']= $fieldentry['lid1'];}
    if ($fieldentry['qid']!='')
    {
        $qq = "SELECT lid, other FROM {$dbprefix}questions WHERE qid={$fieldentry['qid']} and language='$surveybaselang'";
        $qr = db_execute_assoc($qq) or safe_die("Error selecting type and lid from questions table.<br />".$qq."<br />".$connect->ErrorMsg());
        while ($qrow = $qr->FetchRow())
        {
            $outmap[$fieldentry['fieldname']]['lid']=$qrow['lid'];
            $outmap[$fieldentry['fieldname']]['other']=$qrow['other'];        
        }
    }
 
} 
//Get the fieldnames from the survey table for column headings
$surveytable = "{$dbprefix}survey_$surveyid";
if (isset($_POST['colselect']))
{
	$selectfields="";
	foreach($_POST['colselect'] as $cs)
	{
		if ($cs != 'completed')
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
if (isset($_POST['token']) && $_POST['token']=="on")
{
	$dquery .= ", {$dbprefix}tokens_$surveyid.token";
}
if (isset($_POST['attribute_1']) && $_POST['attribute_1']=="on")
{
	$dquery .= ", {$dbprefix}tokens_$surveyid.attribute_1";
}
if (isset($_POST['attribute_2']) && $_POST['attribute_2']=="on")
{
	$dquery .= ", {$dbprefix}tokens_$surveyid.attribute_2";
}
$dquery .= " FROM $surveytable";
if ((isset($_POST['first_name']) && $_POST['first_name']=="on")  || (isset($_POST['token']) && $_POST['token']=="on") || (isset($_POST['last_name']) && $_POST['last_name']=="on") || (isset($_POST['attribute_1']) && $_POST['attribute_1']=="on") || (isset($_POST['attribute_2']) && $_POST['attribute_2']=="on") || (isset($_POST['email_address']) && $_POST['email_address']=="on"))
{
	$dquery .= ""
	. " LEFT OUTER JOIN {$dbprefix}tokens_$surveyid"
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
	elseif ($fieldinfo == "attribute_1")
	{
		if ($type == "csv") {$firstline .= "\"attr1\"$separator";}
		else {$firstline .= $elang->gT("Attribute 1")."$separator";}
	}
	elseif ($fieldinfo == "attribute_2")
	{
		if ($type == "csv") {$firstline .= "\"attr2\"$separator";}
		else {$firstline .= $elang->gT("Attribute 2")."$separator";}
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
	elseif ($fieldinfo == "completed")
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
	else
	{
		//Data fields!
        //$fielddata=arraySearchByKey($fieldinfo, $fieldmap, "fieldname", 1);
        $fielddata=$outmap[$fieldinfo];
        
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
				case "W":
				case "Z":
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
				case "M": //multioption
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
				case "P": //multioption with comment
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
					$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code= '$faid' AND language = '$explang'";
					$lr = db_execute_assoc($lq);
					while ($lrow=$lr->FetchRow())
					{
						$fquest .= " [".strip_tags_full($lrow['answer'])."]";
					}
				}
				break;
				case ":":
				case ";":
				    list($faid, $fcode) = explode("_", $faid);
				    if ($answers == "short") {
					  $fquest .= " [$faid] [$fcode]";
					} else {
					    $lq1="SELECT lid FROM {$dbprefix}questions WHERE qid=$fqid";
					    $lr1=db_execute_assoc($lq1);
					    while($lrow1=$lr1->FetchRow()) {
						  $flid = $lrow1['lid'];
						}
    					$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code= '$faid' AND language = '$explang'";
    					$lr = db_execute_assoc($lq);
    					while ($lrow=$lr->FetchRow())
    					{
    					    $lq2 = "SELECT * FROM {$dbprefix}labels WHERE lid=$flid AND code='$fcode' AND language = '$explang'";
    					    $lr2 = db_execute_assoc($lq2);
    					    while ($lrow2=$lr2->FetchRow())
    					    {
    						    $fquest .= " [".$lrow['answer']."] [".$lrow2['title']."]";
    					    }
						}
					}
				break;
				case "1": // multi scale Headline				
                $flid=$fielddata['lid']; 
		        $flid1=$fielddata['lid1'];
                if (mb_substr($fieldinfo,-1) == '0')
                { //TIBO
                    $strlabel = "1";
        		    $lq = "select a.*, l.*, t.label_name as labeltitle from {$dbprefix}answers as a, {$dbprefix}labels as l, {$dbprefix}labelsets as t where a.code='$faid' and qid=$fqid AND l.lid = $flid AND a.language='$surveybaselang'  AND t.lid=$flid group by l.lid";
                }
                else 
                {
                    $strlabel = "2";
                   $lq = "select a.*, l.*, t.label_name as labeltitle from {$dbprefix}answers as a, {$dbprefix}labels as l, {$dbprefix}labelsets as t where a.code='$faid' and qid=$fqid AND l.lid = $flid1 AND a.language='$surveybaselang'  AND t.lid=$flid1 group by l.lid";
                }
				$lr = db_execute_assoc($lq);
				$j=0;	
				while ($lrow=$lr->FetchRow())
				{
					$strlabel = $strlabel."-".$lrow['labeltitle'];
                    $fquest .= " [".strip_tags_full($lrow['answer'])."][".strip_tags_full($strlabel)."]";
					$j++;
				}
			
				break;
				
			}
            $fquest=strip_tags_full($fquest);               
            
			$fquest = str_replace("\n", " ", $fquest);
			$fquest = str_replace("\r", "", $fquest);
			if ($type == "csv")
			{
				$firstline .="\"$fquest\"$separator";
			}
			else
			{
				$firstline .= "$fquest $separator";
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


//Now dump the data
if ((isset($_POST['first_name']) && $_POST['first_name']=="on") || (isset($_POST['token']) && $_POST['token']=="on") || (isset($_POST['last_name']) && $_POST['last_name']=="on") || (isset($_POST['attribute_1']) && $_POST['attribute_1']=="on") || (isset($_POST['attribute_2']) && $_POST['attribute_2'] == "on") || (isset($_POST['email_address']) && $_POST['email_address'] == "on"))
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
	if (isset($_POST['attribute_1']) && $_POST['attribute_1']=="on")
	{
		$dquery .= ", {$dbprefix}tokens_$surveyid.attribute_1";
	}
	if (isset($_POST['attribute_2']) && $_POST['attribute_2']=="on")
	{
		$dquery .= ", {$dbprefix}tokens_$surveyid.attribute_2";
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
		  foreach($drow as $key=>$dr) {
            $fielddata=arraySearchByKey($key, $fieldmap, "fieldname", 1);
            if(isset($fielddata['type']) && ($fielddata['type'] == "M" || $fielddata['type'] == "P"))
            {
		      if($dr == "Y") {$dr = "1";}
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
elseif ($answers == "long")        //vollst�ndige Antworten gew�hlt
{
//	echo $dquery;
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
            if ($fieldinfo != "startlanguage" && $fieldinfo != "id" && $fieldinfo != "datestamp" && $fieldinfo != "startdate" && $fieldinfo != "ipaddr"  && $fieldinfo != "refurl" && $fieldinfo != "token" && $fieldinfo != "firstname" && $fieldinfo != "lastname" && $fieldinfo != "email" && $fieldinfo != "attribute_1" && $fieldinfo != "attribute_2" && $fieldinfo != "completed")
			{
//				$fielddata=arraySearchByKey($fieldinfo, $fieldmap, "fieldname", 1);
                $fielddata=$outmap[$fieldinfo];
				$fqid=$fielddata['qid'];
				$ftype=$fielddata['type'];
				$fsid=$fielddata['sid'];
				$fgid=$fielddata['gid'];
				$faid=$fielddata['aid'];		
                $flid=$fielddata['lid'];
                if (isset($fielddata['lid1'])) {$flid1=$fielddata['lid1'];}
                
                $fother=$fielddata['other'];
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
						case "attribute_1":
						$ftitle=$elang->gT("Attribute 1").":";
						break;
						case "attribute_2":
						$ftitle=$elang->gT("Attribute 2").":";
						break;
						case "startlanguage":
						$ftitle=$elang->gT("Language").":";
						break;
						default:
                        $fielddata=$outmap[$fieldinfo];  
//						$fielddata=arraySearchByKey($fieldinfo, $fieldmap, "fieldname", 1);
						if (isset($fielddata['title']) && !isset($ftitle)) {$ftitle=$fielddata['title'].":";} 
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
				case "-": //JASONS SPECIAL TYPE
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
					$lq = "select a.*, l.*, l.code as lcode, l.title as ltitle from {$dbprefix}answers as a, {$dbprefix}labels as l where qid=$fqid AND l.lid =$flid AND a.language='$explang' AND l.code = ? group by l.lid";
                    }
                    else
                    {
                     $lq = "select a.*, l.*, l.code as lcode, l.title as ltitle from {$dbprefix}answers as a, {$dbprefix}labels as l where qid=$fqid AND l.lid =$flid1 AND a.language='$explang' AND l.code = ? group by l.lid";
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
				case "W":
				case "Z":
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
						$fquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$flid AND language='$explang' AND code='$drow[$i]'";
						$fresult = db_execute_assoc($fquery) or safe_die("ERROR:".$fquery."<br />".$qq."<br />".$connect->ErrorMsg());
						while ($frow = $fresult->FetchRow())
						{
							$exportoutput .= strip_tags_full($frow['title']);
                            if($type == "pdf"){$pdf->intopdf($frow['title']);}
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
                if (!isset($labelscache[$flid.'|'.$explang.'|'.$drow[$i]]))
                {
				    $fquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$flid AND language='$explang' AND code='$drow[$i]'";
				    $fresult = db_execute_assoc($fquery) or safe_die("ERROR:".$fquery."\n".$qq."\n".$connect->ErrorMsg());
				    if ($fresult) 
				    {
                        $frow=$fresult->FetchRow();
                        if($type == "pdf"){$pdf->intopdf(strip_tags_full($frow['title']));}
	                    $exportoutput .= strip_tags_full($frow['title']);
                        $labelscache[$flid.'|'.$explang.'|'.$drow[$i]]=strip_tags_full($frow['title']);
				    }
                }
                else 
                    {
                        $exportoutput .=$labelscache[$flid.'|'.$explang.'|'.$drow[$i]];
                        if($type == "pdf"){$pdf->intopdf($labelscache[$flid.'|'.$explang.'|'.$drow[$i]]);}
                    }     
				break;
                case "1": //dual scale
                $flid=$fielddata['lid']; 
                $flid1=$fielddata['lid1'];
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
                    {$pdf->intopdf(str_replace("\r\n", " ", strip_tags_full($drow[$i])));}
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
              $sheet->write($rowcounter,$fli,$row);
              $fli++;
        	}
        	$exportoutput='';
        }
         else {$exportoutput .= "\n";}
    }}
if ($type=='xls') 
{ 
  $workbook->close();
}
else if($type=='pdf')
{
  $pdf->Output($clang->gT($surveyname)." ".$surveyid.".pdf","D");
}
else 
{
  echo $exportoutput;
}
exit;


function strip_tags_full($string) {
    $string=html_entity_decode_php4($string, ENT_QUOTES, "UTF-8");
    mb_regex_encoding('utf-8');
    $pattern = array('\r', '\n', '-oth-');
    for ($i=0; $i<sizeof($pattern); $i++) {
        $string = mb_ereg_replace($pattern[$i], '', $string);
    }
    return strip_tags($string);
}

?>
