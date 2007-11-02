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
if (!isset($style)) {$style=returnglobal('style');}
if (!isset($answers)) {$answers=returnglobal('answers');}
if (!isset($type)) {$type=returnglobal('type');}

$sumquery5 = "SELECT b.* FROM {$dbprefix}surveys AS a INNER JOIN {$dbprefix}surveys_rights AS b ON a.sid = b.sid WHERE a.sid=$surveyid AND b.uid = ".$_SESSION['loginID']; //Getting rights for this survey and user
$sumresult5 = db_execute_assoc($sumquery5);
$sumrows5 = $sumresult5->FetchRow();

if ($sumrows5['export'] != "1")
{
	exit;
}

include_once("login_check.php");
include_once(dirname(__FILE__)."/classes/pear/Spreadsheet/Excel/Writer.php");

$surveybaselang=GetBaseLanguageFromSurveyID($surveyid);
$exportoutput="";

if (!$style)
{
    $excesscols[]="id";
	$thissurvey=getSurveyInfo($surveyid);
	//FIND OUT HOW MANY FIELDS WILL BE NEEDED - FOR 255 COLUMN LIMIT
	$query=" SELECT other, q.type, q.gid, q.qid FROM {$dbprefix}questions as q, {$dbprefix}groups as g "
	." where q.gid=g.gid and g.sid=$surveyid and g.language='$surveybaselang' and q.language='$surveybaselang'"
	." order by group_order, question_order";
	$result=db_execute_assoc($query) or die("Couldn't count fields<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
	while ($rows = $result->FetchRow())
	{
		if (($rows['type']=='A') || ($rows['type']=='B')||($rows['type']=='C')||($rows['type']=='M')||($rows['type']=='P')||($rows['type']=='Q')||($rows['type']=='E')||($rows['type']=='F')||($rows['type']=='H'))
		{
			$detailquery="select code from {$dbprefix}answers where qid=".$rows['qid']." and language='$surveybaselang' order by sortorder,code";
			$detailresult=db_execute_assoc($detailquery) or die("Couldn't find detailfields<br />$detailquery<br />".htmlspecialchars($connect->ErrorMsg()));
			while ($detailrows = $detailresult->FetchRow())
			{
				$excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid'].$detailrows['code'];
				if ($rows['type']=='P')
				{
					$excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid'].$detailrows['code']."comment";
				}
			}
		}
		elseif ($rows['type']=='R')
		{
			$detailquery="select code from {$dbprefix}answers where qid=".$rows['qid']." and language='$surveybaselang' order by sortorder,code";
			$detailresult=db_execute_assoc($detailquery) or die("Couldn't find detailfields<br />$detailquery<br />".htmlspecialchars($connect->ErrorMsg()));
			$i=1;
			while ($detailrows = $detailresult->FetchRow())
			{
				$excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid'].$i;
				$i++;
			}
		}
		else
		{
			$excesscols[]=$surveyid.'X'.$rows['gid']."X".$rows['qid'];
		}
		if ($rows['other']=="Y" && (($rows['type']=='M') || ($rows['type']=='!')|| ($rows['type']=='L')|| ($rows['type']=='P')))
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

	if ($thissurvey["datestamp"]=='Y') {$excesscols[]='datestamp';}
	if ($thissurvey["ipaddr"]=='Y') {$excesscols[]='ipaddr';}
    if ($thissurvey["refurl"]=='Y') {$excesscols[]='refurl';}

	$afieldcount = count($excesscols);
    $exportoutput .= "<table width='99%' align='center' class='menubar' cellpadding='1' cellspacing='0'>\n"
    ."\t<tr><td colspan='2' height='4'><strong>".$clang->gT("Export Results")."</font></td></tr>\n";
    $exportoutput .= browsemenubar();
    $exportoutput .= "</table>\n";	
	$exportoutput .= "<br />\n"
	."<form action='$scriptname?action=exportresults' method='post'>\n"
	."<table align='center'><tr>"
	."<td valign='top'>\n"
	."<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	."\t<tr><td colspan='2' height='4'>"
	."<strong>"
	.$clang->gT("Export Responses");
	if (isset($_POST['sql'])) {$exportoutput .= " - ".$clang->gT("Filtered from Statistics Script");}
	if (returnglobal('id')<>'') {$exportoutput .= " - ".$clang->gT("Single Response");}

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

	$exportoutput .= "</strong> ($afieldcount ".$clang->gT("Columns").")</td></tr>\n"
	."\t<tr><td height='8'><font size='1'><strong>"
	.$clang->gT("Questions")."</strong></font></td></tr>\n"
	."\t<tr>\n"
	."\t\t<td>\n"
	."\t\t\t<input type='radio' class='radiobtn' name='style' value='abrev' id='headabbrev'>"
	."<font size='1'><label for='headabbrev'>"
	.$clang->gT("Abbreviated headings")."</label><br />\n"
	."\t\t\t<input type='radio' class='radiobtn' checked name='style' value='full' id='headfull'>"
	."<label for='headfull'>"
	.$clang->gT("Full headings")."</label><br />\n"
	."\t\t\t<input type='radio' class='radiobtn' checked name='style' value='headcodes' id='headcodes'>"
	."<label for='headcodes'>"
	.$clang->gT("Question Codes")."</label><br />\n"
	."\t\t\t&nbsp ".$clang->gT("Filter incomplete answers")." <select name='filterinc'>\n"
	."\t\t\t\t<option value='filter' $selecthide>".$clang->gT("Enable")."</option>\n"
	."\t\t\t\t<option value='show' $selectshow>".$clang->gT("Disable")."</option>\n"
	."\t\t\t</select>\n"
	."\t\t</font></font></td>\n"
	."\t</tr>\n"
	."\t<tr><td height='8'><font size='1'><strong>"
	.$clang->gT("Answers")."</strong></font></font></td></tr>\n"
	."\t<tr>\n"
	."\t\t<td>\n"
	."\t\t\t<input type='radio' class='radiobtn' name='answers' value='short' id='ansabbrev'>"
	."<font size='1'><label for='ansabbrev'>"
	.$clang->gT("Answer Codes")."</label><br />\n"
	."\t\t\t<input type='radio' class='radiobtn' checked name='answers' value='long' id='ansfull'>"
	."<label for='ansfull'>"
	.$clang->gT("Full Answers")."</label>\n"
	."\t\t</font></td>\n"
	."\t</tr>\n"
	."\t<tr><td><font size='1'><strong>"
	.$clang->gT("Format")."</strong></font></td></tr>\n"
	."\t<tr>\n"
	."\t\t<td>\n"
	."\t\t\t<input type='radio' class='radiobtn' name='type' value='doc' id='worddoc' onclick='document.getElementById(\"ansfull\").checked=true;document.getElementById(\"ansabbrev\").disabled=true;'>"
	."<font size='1'><label for='worddoc'>"
	.$clang->gT("Microsoft Word (Latin charset)")."</label><br />\n"
	."\t\t\t<input type='radio' class='radiobtn' name='type' value='xls' checked id='exceldoc' onclick='document.getElementById(\"ansabbrev\").disabled=false;'>"
	."<label for='exceldoc'>"
	.$clang->gT("Microsoft Excel (Latin charset)")."</label><br />\n"
	."\t\t\t<input type='radio' class='radiobtn' name='type' value='csv' id='csvdoc' onclick='document.getElementById(\"ansabbrev\").disabled=false;'>"
	."<label for='csvdoc'>"
	.$clang->gT("CSV File (UTF-8 charset - use this for non-latin languages)")."</label>\n"
	."\t\t</font></font></td>\n"
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
	$result=db_execute_assoc($query) or die("Couldn't get privacy data<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
	while ($rows = $result->FetchRow()) {$surveyprivate=$rows['private'];}
	if ($surveyprivate == "N")
	{
		$query=db_select_tables_like("{$dbprefix}tokens_$surveyid"); //SEE IF THERE IS AN ASSOCIATED TOKENS TABLE
		$result=$connect->Execute($query) or die("Couldn't get table list<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
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
			$result = db_select_limit_assoc($query, 1) or die ($query."<br />".htmlspecialchars($connect->ErrorMsg()));
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

sendcacheheaders();             // sending "cache headers" before this permit us to send something else than a "text/html" content-type
switch ( $_POST["type"] ) {     // this is a step to register_globals = false ;c)
	case "doc":
	header("Content-Disposition: attachment; filename=survey.doc");
	header("Content-type: application/vnd.ms-word");
	$separator="\t";
	break;
	case "xls":

      $workbook = new Spreadsheet_Excel_Writer();
      // Set the temporary directory to avoid PHP error messages due to open_basedir restrictions and calls to tempnam("", ...)
      if (!empty($tempdir)) {
        $workbook->setTempDir($tempdir);
      }
      $workbook->send('results.xls');
      // Creating the first worksheet
      $sheet =& $workbook->addWorksheet('Survey Results');
      $separator="|";
	break;
	case "csv":
	header("Content-Disposition: attachment; filename=survey.csv");
	header("Content-type: text/comma-separated-values; charset=UTF-8");
	$separator=",";
	break;
	default:
	header("Content-Disposition: attachment; filename=survey.csv");
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

//Get the fieldnames from the survey table for column headings
$surveytable = "{$dbprefix}survey_$surveyid";
if (isset($_POST['colselect']))
{
	$selectfields="";
	foreach($_POST['colselect'] as $cs)
	{
		$selectfields.= "$surveytable.".db_quote_id($cs).", ";
	}
	$selectfields = mb_substr($selectfields, 0, strlen($selectfields)-2);
}
else
{
	$selectfields="$surveytable.*";
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
if (incompleteAnsFilterstate() === true)
{
	$dquery .= "  WHERE $surveytable.submitdate is not null ";
}
$dquery .=" ORDER BY id";
$dresult = db_select_limit_assoc($dquery, 1) or die($clang->gT("Error")." getting results<br />$dquery<br />".htmlspecialchars($connect->ErrorMsg()));
$fieldcount = $dresult->FieldCount();
$firstline="";
$faid="";
for ($i=0; $i<$fieldcount; $i++)
{
	//Iterate through column names and output headings
	$field=$dresult->FetchField($i);
	$fieldinfo=$field->name;
//	if ($fieldinfo == "token")
//	{
//		if ($answers == "short")
//		{
//			if ($type == "csv")
//			{
//				$firstline.="\"".$elang->gT("Token")."\"$separator";
//			}
//			else
//			{
//				$firstline .= $elang->gT("Token")."$separator";
//			}
//		}
//		if ($answers == "long")
//		{
//			if ($style == "abrev")
//			{
//				if ($type == "csv") {$firstline .= "\"".$elang->gT("Token")."\"$separator";}
//				else {$firstline .= $elang->gT("Token")."$separator";}
//			}
//			else
//			{
//				if ($type == "csv") {$firstline .= "\"".$elang->gT("Token")."\"$separator";}
//				else {$firstline .= $elang->gT("Token")."$separator";}
//			}
//		}
//	}
//	elseif ($fieldinfo == "lastname")
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
		if ($type == "csv") {$firstline .= "\"".$elang->gT("Time Submitted")."\"$separator";}
		else {$firstline .= $elang->gT("Time Submitted")."$separator";}
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
		$fielddata=arraySearchByKey($fieldinfo, $fieldmap, "fieldname", 1);
		$fqid=$fielddata['qid'];
		$ftype=$fielddata['type'];
		$fsid=$fielddata['sid'];
		$fgid=$fielddata['gid'];
		$faid=$fielddata['aid'];
		if ($style == "abrev")
		{
			$qq = "SELECT question FROM {$dbprefix}questions WHERE qid=$fqid and language='$explang'";
			$qr = db_execute_assoc($qq);
			while ($qrow=$qr->FetchRow())
			{$qname=$qrow['question'];}
			$qname=mb_substr($qname, 0, 15)."..";
			$qname=strip_tags($qname);
			$firstline = str_replace("\n", "", $firstline);
			$firstline = str_replace("\r", "", $firstline);
			if ($type == "csv") {$firstline .= "\"$qname";}
			else {$firstline .= "$qname";}
			if (isset($faid)) {$firstline .= " [{$faid}]"; $faid="";}
			if ($type == "csv") {$firstline .= "\"";}
			$firstline .= "$separator";
		}
		else
		{
			$qq = "SELECT question, type, other, title FROM {$dbprefix}questions WHERE qid=$fqid AND language='$explang' ORDER BY gid, title"; //get the question
			$qr = db_execute_assoc($qq) or die ("ERROR:<br />".$qq."<br />".htmlspecialchars($connect->ErrorMsg()));
			while ($qrow=$qr->FetchRow())
			{
				if ($style == "headcodes"){$fquest=$qrow['title'];}
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
					$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code = '$faid' AND language = '$explang'";
					$lr = db_execute_assoc($lq);
					while ($lrow = $lr->FetchRow())
					{
						$fquest .= " [".$lrow['answer']."]";
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
					$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code = '$faid' AND language = '$explang'";
					$lr = db_execute_assoc($lq);
					while ($lrow = $lr->FetchRow())
					{
						$fquest .= " [".$lrow['answer']."]";
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
						$fquest .= " [".$lrow['answer']."]";
					}
				}
				break;
			}
			$fquest = strip_tags($fquest);
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
	}
}

if ($type == "csv") { $firstline = mb_substr(trim($firstline),0,strlen($firstline)-1);}
else
{
	$firstline = trim($firstline);
}

$firstline .= "\n";

if ($type == "doc")
{
	$flarray=explode($separator, $firstline);
	$fli=0;
	//die(print_r($fieldmap)."\n".print_r($firstline));
	$y=1;
	for ($x=0; $x<count($fieldmap); $x++)
	{
//		if ($fieldmap[$x]['fieldname'] != "datestamp" && $fieldmap[$x]['fieldname'] != "ipaddr" && $fieldmap[$x]['fieldname'] != "refurl")
//		{
			$fieldmap[$x]['title']=$flarray[$x];
//			$y++;
//		}
	}
}
else
if ($type == "xls")
{
	//var_dump ($firstline);
    $flarray=explode($separator, $firstline);
	$fli=0;
//    $format = $xls->addFormat();
//    $format->setBold();
//    $format->setColor("green");	
	foreach ($flarray as $fl)
	{
      $sheet->write(0,$fli,mb_convert_encoding($fl, "ISO-8859-1", "UTF-8"));      
      $fli++;
	}
	//print_r($fieldmap);
}
else
{
	$exportoutput .= $firstline; //Sending the header row
}


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

	if (incompleteAnsFilterstate() === true)
	{
    $dquery .= "  WHERE $surveytable.submitdate is not null ";
	}
}
else // this applies for exporting everything
{
	$dquery = "SELECT $selectfields FROM $surveytable ";

	if (incompleteAnsFilterstate() === true)
	{
    $dquery .= "  WHERE $surveytable.submitdate is not null ";
	}
}

if (isset($_POST['sql'])) //this applies if export has been called from the statistics package
{
	if ($_POST['sql'] != "NULL")
	{
		if (incompleteAnsFilterstate() === true) {$dquery .= " AND ".stripcslashes($_POST['sql'])." ";}
		else {$dquery .= "WHERE ".stripcslashes($_POST['sql'])." ";}
	}
}
if (isset($_POST['answerid']) && $_POST['answerid'] != "NULL") //this applies if export has been called from single answer view
{
	if (incompleteAnsFilterstate() === true) {$dquery .= " AND $surveytable.id=".stripcslashes($_POST['answerid'])." ";}
	else {$dquery .= "WHERE $surveytable.id=".stripcslashes($_POST['answerid'])." ";}
}


$dquery .= "ORDER BY $surveytable.id";
if ($answers == "short") //Nice and easy. Just dump the data straight
{
	$dresult = db_execute_assoc($dquery);
	$rowcounter=0;
	while ($drow = $dresult->FetchRow())
	{
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
              $sheet->write($rowcounter,$colcounter,mb_convert_encoding($rowfield, "ISO-8859-1", "UTF-8"));
              $colcounter++;
        	}
        }		
        else
		{
			$exportoutput .= implode($separator, str_replace("\r\n", " ", $drow)) . "\n"; //create dump from each row
		}
	}
}
elseif ($answers == "long")
{
//	echo $dquery;
	$dresult = db_execute_num($dquery) or die("ERROR: $dquery -".htmlspecialchars($connect->ErrorMsg()));
	$fieldcount = $dresult->FieldCount();
	$rowcounter=0;

	while ($drow = $dresult->FetchRow())
	{
		$rowcounter++;
		if ($type == "doc")
		{
			$exportoutput .= "\n\n\n".$elang->gT('NEW RECORD')."\n";
		}
		if (!ini_get('safe_mode'))
		{
			set_time_limit(3); //Give each record 3 seconds
		}
		for ($i=0; $i<$fieldcount; $i++) //For each field, work out the QID
		{
			$fqid=0;            // By default fqid is set to zero 
            $field=$dresult->FetchField($i);
			$fieldinfo=$field->name;
            if ($fieldinfo != "startlanguge" && $fieldinfo != "id" && $fieldinfo != "datestamp" && $fieldinfo != "ipaddr"  && $fieldinfo != "refurl" && $fieldinfo != "token" && $fieldinfo != "firstname" && $fieldinfo != "lastname" && $fieldinfo != "email" && $fieldinfo != "attribute_1" && $fieldinfo != "attribute_2")
			{
				//die(print_r($fieldmap));
				$fielddata=arraySearchByKey($fieldinfo, $fieldmap, "fieldname", 1);
				$fqid=$fielddata['qid'];
				$ftype=$fielddata['type'];
				$fsid=$fielddata['sid'];
				$fgid=$fielddata['gid'];
				$faid=$fielddata['aid'];
				if ($type == "doc")
				{
					$ftitle=$fielddata['title'];
				}
				$qq = "SELECT lid, other FROM {$dbprefix}questions WHERE qid=$fqid and language='$surveybaselang'";
				$qr = db_execute_assoc($qq) or die("Error selecting type and lid from questions table.<br />".$qq."<br />".htmlspecialchars($connect->ErrorMsg()));
				while ($qrow = $qr->FetchRow())
				{$lid=$qrow['lid']; $fother=$qrow['other'];} // dgk bug fix. $ftype should not be modified here!
			}
			else
			{
				$fsid=""; $fgid=""; 
				if ($type == "doc")
				{
					switch($fieldinfo)
					{
						case "datestamp":
						$ftitle=$elang->gT("Time Submitted").":";
						break;
						case "ipaddr":
						$ftitle=$elang->gT("IP Address").":";
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
						$fielddata=arraySearchByKey($fieldinfo, $fieldmap, "fieldname", 1);
						if (isset($fielddata['title'])) {$ftitle=$fielddata['title'].":";} else {$ftitle='';}
					} // switch
				}
			}
			if ($fqid == 0)
			{
				$ftype = "-";  //   This is set if it not a normal answer field, but something like tokenID, First Name etc
			}
			if ($type == "csv") {$exportoutput .= "\"";}
			if ($type == "doc") {$exportoutput .= "\n$ftitle\n\t";}
			switch ($ftype)
			{
				case "-": //JASONS SPECIAL TYPE
				$exportoutput .= $drow[$i];
				break;
				case "R": //RANKING TYPE
				$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND language='$explang' AND code = ?";
				$lr = db_execute_assoc($lq, array($drow[$i]));
				while ($lrow = $lr->FetchRow())
				{
					$exportoutput .= $lrow['answer'];
				}
				break;
				case "L": //DROPDOWN LIST
				case "!":
				if (mb_substr($fieldinfo, -5, 5) == "other")
				{
					$exportoutput .= $drow[$i];
				}
				else
				{
					if ($drow[$i] == "-oth-")
					{
						$exportoutput .= $elang->gT("Other");
					}
					else
					{
						$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND language='$explang' AND code = ?";
						$lr = db_execute_assoc($lq, array($drow[$i])) or die($lq."<br />ERROR:<br />".htmlspecialchars($connect->ErrorMsg()));
						while ($lrow = $lr->FetchRow())
						{
							//if ($lrow['code'] == $drow[$i]) {$exportoutput .= $lrow['answer'];}
							$exportoutput .= $lrow['answer'];
						}
					}
				}
				break;
				case "W":
				case "Z":
				if (mb_substr($fieldinfo, -5, 5) == "other")
				{
					$exportoutput .= $drow[$i];
				}
				else
				{
					if ($drow[$i] == "-oth-")
					{
						$exportoutput .= $elang->gT("Other");
					}
					else
					{
						$fquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND language='$explang' AND code='$drow[$i]'";
						$fresult = db_execute_assoc($fquery) or die("ERROR:".$fquery."\n".$qq."\n".htmlspecialchars($connect->ErrorMsg()));
						while ($frow = $fresult->FetchRow())
						{
							$exportoutput .= $frow['title'];
						}
					}
				}
				break;
				case "O": //DROPDOWN LIST WITH COMMENT
				$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND language='$explang' ORDER BY answer";
				$lr = db_execute_assoc($lq) or die ("Could do it<br />$lq<br />".htmlspecialchars($connect->ErrorMsg()));
				$found = "";
				while ($lrow = $lr->FetchRow())
				{
					if ($lrow['code'] == $drow[$i]) {$exportoutput .= $lrow['answer']; $found = "Y";}
				}
				if ($found != "Y") {if ($type == "csv") {$exportoutput .= str_replace("\"", "\"\"", $drow[$i]);} else {$exportoutput .= str_replace("\r\n", " ", $drow[$i]);}}
				break;
				case "Y": //YES\NO
				switch($drow[$i])
				{
					case "Y": $exportoutput .= $elang->gT("Yes"); break;
					case "N": $exportoutput .= $elang->gT("No"); break;
					default: $exportoutput .= $elang->gT("N/A"); break;
				}
				break;
				case "G": //GENDER
				switch($drow[$i])
				{
					case "M": $exportoutput .= $elang->gT("Male"); break;
					case "F": $exportoutput .= $elang->gT("Female"); break;
					default: $exportoutput .= $elang->gT("N/A"); break;
				}
				break;
				case "M": //multioption
				case "P":
				if (mb_substr($fieldinfo, -5, 5) == "other")
				{
					$exportoutput .= "$drow[$i]";
				}
				elseif (mb_substr($fieldinfo, -7, 7) == "comment")
				{
					$exportoutput .= "$drow[$i]";
				}
				else
				{
					switch($drow[$i])
					{
						case "Y": $exportoutput .= $elang->gT("Yes"); break;
						case "N": $exportoutput .= $elang->gT("No"); break;
						case "": $exportoutput .= $elang->gT("No"); break;
						default: $exportoutput .= $drow[$i]; break;
					}
				}
				break;
				case "C":
				switch($drow[$i])
				{
					case "Y":
					$exportoutput .= $elang->gT("Yes");
					break;
					case "N":
					$exportoutput .= $elang->gT("No");
					break;
					case "U":
					$exportoutput .= $elang->gT("Uncertain");
					break;
				}
				case "E":
				switch($drow[$i])
				{
					case "I":
					$exportoutput .= $elang->gT("Increase");
					break;
					case "S":
					$exportoutput .= $elang->gT("Same");
					break;
					case "D":
					$exportoutput .= $elang->gT("Decrease");
					break;
				}
				break;
				case "F":
				case "H":
				$fquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND language='$explang' AND code='$drow[$i]'";
				$fresult = db_execute_assoc($fquery) or die("ERROR:".$fquery."\n".$qq."\n".htmlspecialchars($connect->ErrorMsg()));
				while ($frow = $fresult->FetchRow())
				{
					$exportoutput .= $frow['title'];
				}
				break;
				default: $tempresult=$dresult->FetchField($i);
				if ($tempresult->name == "token")
				{
					$tokenquery = "SELECT firstname, lastname FROM {$dbprefix}tokens_$surveyid WHERE token='$drow[$i]'";
					if ($tokenresult = db_execute_assoc($tokenquery)) //or die ("Couldn't get token info<br />$tokenquery<br />".$connect->ErrorMsg());
					while ($tokenrow=$tokenresult->FetchRow())
					{
						$exportoutput .= "{$tokenrow['lastname']}, {$tokenrow['firstname']}";
					}
					else
					{
						$exportoutput .= "Tokens problem - token table missing";
					}
				}
				else
				{
					if ($type == "csv")
					{$exportoutput .= str_replace("\r\n", "\n", str_replace("\"", "\"\"", $drow[$i]));}
					else
					{$exportoutput .= str_replace("\r\n", " ", $drow[$i]);}
				}
			}
			if ($type == "csv") {$exportoutput .= "\"";}
			$exportoutput .= "$separator";
			$ftype = "";
		}
		
        IF ($type=='xls')
        {
            $rowarray=explode($separator, $exportoutput);
        	$fli=0;
        	foreach ($rowarray as $row)
        	{
              $sheet->write($rowcounter,$fli,mb_convert_encoding($row, "ISO-8859-1", "UTF-8"));
              $fli++;
        	}
        	$exportoutput='';
        }
         else {$exportoutput .= "\n";}
    }
}
if ($type=='xls') { $workbook->close();}
    else {echo $exportoutput;}
exit;
?>
