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

require_once(dirname(__FILE__).'/../config.php');
if (!isset($imagefiles)) {$imagefiles="./images";}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($style)) {$style=returnglobal('style');}
if (!isset($answers)) {$answers=returnglobal('answers');}
if (!isset($type)) {$type=returnglobal('type');}


//Ensure script is not run directly, avoid path disclosure
if (empty($surveyid)) {die ("Cannot run this script directly");}
include_once("login_check.php");
$exportoutput="";

if (!$style)
{
    $excesscols[]="id";
	$thissurvey=getSurveyInfo($surveyid);
	//FIND OUT HOW MANY FIELDS WILL BE NEEDED - FOR 255 COLUMN LIMIT
	$query=" SELECT other, {$dbprefix}questions.type, {$dbprefix}questions.gid, {$dbprefix}questions.qid FROM {$dbprefix}questions, {$dbprefix}groups "
	." where {$dbprefix}questions.gid={$dbprefix}groups.gid and {$dbprefix}groups.sid=$surveyid"
	." order by {$dbprefix}groups.group_order, {$dbprefix}questions.title";
	$result=db_execute_assoc($query) or die("Couldn't count fields<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
	while ($rows = $result->FetchRow())
	{
		if (($rows['type']=='A') || ($rows['type']=='B')||($rows['type']=='C')||($rows['type']=='M')||($rows['type']=='P')||($rows['type']=='Q')||($rows['type']=='E')||($rows['type']=='F')||($rows['type']=='H'))
		{
			$detailquery="select code from {$dbprefix}answers where qid=".$rows['qid']." order by sortorder,code";
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
			$detailquery="select code from {$dbprefix}answers where qid=".$rows['qid']." order by sortorder,code";
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

	$afieldcount = count($excesscols);
    $exportoutput .= "<table width='99%' align='center' style='margin: 5px; border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
    ."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Export Results")."</strong></font></td></tr>\n";
    $exportoutput .= browsemenubar();
    $exportoutput .= "</table>\n";	
	$exportoutput .= "<br />\n"
	."<form action='$scriptname?action=exportresults' method='post'>\n"
	."<table align='center'><tr>"
	."<td valign='top'>\n"
	."<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	."\t<tr bgcolor='#555555'><td colspan='2' height='4'>"
	."<font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Export Responses");
	if (isset($_POST['sql'])) {$exportoutput .= " - ".$clang->gT("Filtered from Statistics Script");}
	if (returnglobal('id')<>'') {$exportoutput .= " - ".$clang->gT("Single Response");}
	$exportoutput .= "</strong> ($afieldcount ".$clang->gT("Columns").")</font></td></tr>\n"
	."\t<tr><td height='8' bgcolor='silver'><font size='1'><strong>"
	.$clang->gT("Questions")."</strong></font></font></td></tr>\n"
	."\t<tr>\n"
	."\t\t<td>\n"
	."\t\t\t$setfont<input type='radio' name='style' value='abrev' id='headabbrev'>"
	."<font size='1'><label for='headabbrev'>"
	.$clang->gT("Abbreviated headings")."</label><br />\n"
	."\t\t\t<input type='radio' checked name='style' value='full' id='headfull'>"
	."<label for='headfull'>"
	.$clang->gT("Full headings")."</label><br />\n"
	."\t\t\t<input type='radio' checked name='style' value='headcodes' id='headcodes'>"
	."<label for='headcodes'>"
	.$clang->gT("Question Codes")."</label>\n"
	."\t\t</font></font></td>\n"
	."\t</tr>\n"
	."\t<tr><td height='8' bgcolor='silver'><font size='1'><strong>"
	.$clang->gT("Answers")."</strong></font></font></td></tr>\n"
	."\t<tr>\n"
	."\t\t<td>\n"
	."\t\t\t$setfont<input type='radio' name='answers' value='short' id='ansabbrev'>"
	."<font size='1'><label for='ansabbrev'>"
	.$clang->gT("Answer Codes")."</label><br />\n"
	."\t\t\t<input type='radio' checked name='answers' value='long' id='ansfull'>"
	."<label for='ansfull'>"
	.$clang->gT("Full Answers")."</label>\n"
	."\t\t</font></font></td>\n"
	."\t</tr>\n"
	."\t<tr><td height='8' bgcolor='silver'><font size='1'><strong>"
	.$clang->gT("Format")."</strong></font></font></td></tr>\n"
	."\t<tr>\n"
	."\t\t<td>\n"
	."\t\t\t$setfont<input type='radio' name='type' value='doc' id='worddoc'>"
	."<font size='1'><label for='worddoc'>"
	.$clang->gT("Microsoft Word")."</label><br />\n"
	."\t\t\t<input type='radio' name='type' value='xls' checked id='exceldoc'>"
	."<label for='exceldoc'>"
	.$clang->gT("Microsoft Excel")."</label><br />\n"
	."\t\t\t<input type='radio' name='type' value='csv' id='csvdoc'>"
	."<label for='csvdoc'>"
	.$clang->gT("CSV Comma Delimited")."</label>\n"
	."\t\t</font></font></td>\n"
	."\t</tr>\n"
	."\t<tr><td height='2' bgcolor='silver'></td></tr>\n"
	."\t<tr>\n"
	."\t\t<td align='center' bgcolor='silver'>\n"
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
	."\t\t<td height='8' bgcolor='#555555'><font face='verdana' color='white' size='1'><strong>"
	.$clang->gT("Column Control")."</strong>\n"
	."\t\t</font></td>\n"
	."\t</tr>\n"
	."\t<tr>\n"
	."\t\t<td bgcolor='silver' height='8'><strong><font size='1'>\n"
	."\t\t\t".$clang->gT("Choose Columns").":\n"
	."\t\t</font></strong>";
	if ($afieldcount > 255)
	{
		$exportoutput .= "\t\t\t<img src='$imagefiles/help.gif' alt='".$clang->gT("Help")."' align='right' onclick='javascript:alert(\""
		.$clang->gT("Your survey contains more than 255 columns of responses. Spreadsheet applications such as Excel are limited to loading no more than 255. Select the columns you wish to export in the list below.")
		."\")'>";
	}
	else
	{
		$exportoutput .= "\t\t\t<img src='$imagefiles/help.gif' alt='".$clang->gT("Help")."' align='right' onclick='javascript:alert(\""
		.$clang->gT("Choose the columns you wish to export.")
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
			."\t\t<td height='8' bgcolor='#555555'><font face='verdana' color='white' size='1'><strong>"
			.$clang->gT("Token Control")."</strong>\n"
			."\t\t</font></td>\n"
			."\t</tr>\n"
			."\t<tr>\n"
			."\t\t<td bgcolor='silver' height='8'><strong><font size='1'>\n"
			.$clang->gT("Choose Token Fields").":"
			."\t\t</font></font></strong></td>\n"
			."\t</tr>\n"
			."\t<tr>\n"
			."\t\t<td><font size='1'>"
			."<img src='$imagefiles/help.gif' alt='".$clang->gT("Help")."' align='right' onclick='javascript:alert(\""
			.$clang->gT("Your survey can export associated token data with each response. Select any additional fields you would like to export.")
			."\")' /><br /><br />\n"
			."<input type='checkbox' name='first_name' id='first_name'>"
			."<label for='first_name'>".$clang->gT("First Name")."</label><br />\n"
			."<input type='checkbox' name='last_name' id='last_name'>"
			."<label for='last_name'>".$clang->gT("Last Name")."</label><br />\n"
			."<input type='checkbox' name='email_address' id='email_address'>"
			."<label for='email_address'>".$clang->gT("Email")."</label><br />\n"
			."<input type='checkbox' name='token' id='token'>"
			."<label for='token'>".$clang->gT("Token")."</label><br />\n";
			$query = "SELECT * FROM {$dbprefix}tokens_$surveyid LIMIT 1"; //SEE IF TOKENS TABLE HAS ATTRIBUTE FIELDS
			$result = $connect->Execute($query) or die ($query."<br />".htmlspecialchars($connect->ErrorMsg()));
			$rowcount = $result->FieldCount();
			if ($rowcount > 7)
			{
				$exportoutput .= "<input type='checkbox' name='attribute_1' id='attribute_1'>"
				."<label for='attribute_1'>".$clang->gT("Attribute 1")."</label><br />\n"
				."<input type='checkbox' name='attribute_2' id='attribute_2'>"
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
	header("Content-Disposition: attachment; filename=survey.xls");
	header("Content-type: application/vnd.ms-excel");
	$separator="\t";
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

//STEP 1: First line is column headings

$fieldmap=createFieldMap($surveyid);

//Get the fieldnames from the survey table for column headings
$surveytable = "{$dbprefix}survey_$surveyid";
if (isset($_POST['colselect']))
{
	$selectfields="";
	foreach($_POST['colselect'] as $cs)
	{
		$selectfields.= "$surveytable.`$cs`, ";
	}
	$selectfields = substr($selectfields, 0, strlen($selectfields)-2);
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
$dquery .=" ORDER BY id LIMIT 1";
$dresult = $connect->Execute($dquery) or die($clang->gT("Error")." getting results<br />$dquery<br />".htmlspecialchars($connect->ErrorMsg()));
$fieldcount = $dresult->FieldCount();
$firstline="";
$faid="";
$debug="";
for ($i=0; $i<$fieldcount; $i++)
{
	//Iterate through column names and output headings
	$field=$dresult->FetchField($i);
	$fieldinfo=$field->name;
	if ($fieldinfo == "token")
	{
		if ($answers == "short")
		{
			if ($type == "csv")
			{
				$firstline.="\"".$clang->gT("Token")."\"$separator";
			}
			else
			{
				$firstline .= $clang->gT("Token")."$separator";
			}
		}
		if ($answers == "long")
		{
			if ($style == "abrev")
			{
				if ($type == "csv") {$firstline .= "\"".$clang->gT("Token")."\"$separator";}
				else {$firstline .= $clang->gT("Token")."$separator";}
			}
			else
			{
				if ($type == "csv") {$firstline .= "\"".$clang->gT("Token")."\"$separator";}
				else {$firstline .= $clang->gT("Token")."$separator";}
			}
		}
	}
	elseif ($fieldinfo == "lastname")
	{
		if ($type == "csv") {$firstline .= "\"".$clang->gT("Last Name")."\"$separator";}
		else {$firstline .= $clang->gT("Last Name")."$separator";}
	}
	elseif ($fieldinfo == "firstname")
	{
		if ($type == "csv") {$firstline .= "\"".$clang->gT("First Name")."\"$separator";}
		else {$firstline .= $clang->gT("First Name")."$separator";}
	}
	elseif ($fieldinfo == "email")
	{
		if ($type == "csv") {$firstline .= "\"".$clang->gT("Email Address")."\"$separator";}
		else {$firstline .= $clang->gT("Email Address")."$separator";}
	}
	elseif ($fieldinfo == "token")
	{
		if ($type == "csv") {$firstline .= "\"".$clang->gT("Token")."\"$separator";}
		else {$firstline .= $clang->gT("Token")."$separator";}
	}
	elseif ($fieldinfo == "attribute_1")
	{
		if ($type == "csv") {$firstline .= "\"attr1\"$separator";}
		else {$firstline .= $clang->gT("Attribute 1")."$separator";}
	}
	elseif ($fieldinfo == "attribute_2")
	{
		if ($type == "csv") {$firstline .= "\"attr2\"$separator";}
		else {$firstline .= $clang->gT("Attribute 2")."$separator";}
	}
	elseif ($fieldinfo == "id")
	{
		if ($type == "csv") {$firstline .= "\"id\"$separator";}
		else {$firstline .= "id$separator";}
	}
	elseif ($fieldinfo == "datestamp")
	{
		if ($type == "csv") {$firstline .= "\"Time Submitted\"$separator";}
		else {$firstline .= "Time Submitted$separator";}
	}
	elseif ($fieldinfo == "ipaddr")
	{
		if ($type == "csv") {$firstline .= "\"".$clang->gT("IP-Address")."\"$separator";}
		else {$firstline .= $clang->gT("IP-Address")."$separator";}
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
			$qq = "SELECT question FROM {$dbprefix}questions WHERE qid=$fqid";
			$qr = db_execute_assoc($qq);
			while ($qrow=$qr->FetchRow())
			{$qname=$qrow['question'];}
			$qname=substr($qname, 0, 15)."..";
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
			$qq = "SELECT question, type, other, title FROM {$dbprefix}questions WHERE qid=$fqid order by gid, title"; //get the question
			$qr = db_execute_assoc($qq) or die ("ERROR:<br />".$qq."<br />".htmlspecialchars($connect->ErrorMsg()));
			while ($qrow=$qr->FetchRow())
			{
				if ($style == "headcodes"){$fquest=$qrow['title'];}
				else {$fquest=$qrow['question'];}
			}
			switch ($ftype)
			{
				case "R": //RANKING TYPE
				$fquest .= " [".$clang->gT("Ranking")." $faid]";
				break;
				case "L":
				case "!":
				case "W":
				case "Z":
				if ($faid == "other") {
					$fquest .= " [".$clang->gT("Other")."]";
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
					$fquest .= " [".$clang->gT("Other")."]";
				}
				else
				{
					$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code = '$faid'";
					$lr = db_execute_assoc($lq);
					while ($lrow = $lr->FetchRow())
					{
						$fquest .= " [".$lrow['answer']."]";
					}
				}
				break;
				case "P": //multioption with comment
				if (substr($faid, -7, 7) == "comment")
				{
					$faid=substr($faid, 0, -7);
					$comment=true;
				}
				if ($faid == "other")
				{
					$fquest .= " [".$clang->gT("Other")."]";
				}
				else
				{
					$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code = '$faid'";
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
					$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code= '$faid'";
					$lr = db_execute_assoc($lq);
					$debug .= " | QUERY FOR ANSWER CODE [$lq]";
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

if ($type == "csv") { $firstline = substr(trim($firstline),0,strlen($firstline)-1);}
else
{
	$firstline = trim($firstline);
}

$firstline .= "\n";

if ($type == "doc")
{
	$flarray=explode($separator, $firstline);
	$fli=0;
	foreach ($flarray as $fl)
	{
		$fieldmap[$fli]['title']=$fl;
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
	if (isset($_POST['token']) && $_POST['token']=="on")
	{
		$dquery .= ", {$dbprefix}tokens_$surveyid.token";
	}
	if (isset($_POST['email_address']) && $_POST['email_address']=="on")
	{
		$dquery .= ", {$dbprefix}tokens_$surveyid.email";
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
}
else // this applies for exporting everything
{
	$dquery = "SELECT $selectfields FROM $surveytable ";
}

if (isset($_POST['sql'])) //this applies if export has been called from the statistics package
{
	if ($_POST['sql'] != "NULL") {$dquery .= "WHERE ".stripcslashes($_POST['sql'])." ";}
}
if (isset($_POST['answerid'])) //this applies if export has been called from single answer view
{
	if ($_POST['answerid'] != "NULL") {$dquery .= "WHERE $surveytable.id=".stripcslashes($_POST['answerid'])." ";}
}


$dquery .= "ORDER BY $surveytable.id";
if ($answers == "short") //Nice and easy. Just dump the data straight
{
	$dresult = db_execute_assoc($dquery);
	while ($drow = $dresult->FetchRow())
	{
		if ($type == "csv")
		{
			$exportoutput .= "\"".implode("\"$separator\"", str_replace("\"", "\"\"", str_replace("\r\n", " ", $drow))) . "\"\n"; //create dump from each row
		}
		else
		{
			$exportoutput .= implode($separator, str_replace("\r\n", " ", $drow)) . "\n"; //create dump from each row
		}
	}
}
elseif ($answers == "long")
{
	$debug="";
	$dresult = db_execute_num($dquery) or die("ERROR: $dquery -".htmlspecialchars($connect->ErrorMsg()));
	$fieldcount = $dresult->FieldCount();
	while ($drow = $dresult->FetchRow())
	{
		if ($type == "doc")
		{
			$exportoutput .= "\n\n\nNEW RECORD\n";
		}
		if (!ini_get('safe_mode'))
		{
			set_time_limit(3); //Give each record 3 seconds
		}
		for ($i=0; $i<$fieldcount; $i++) //For each field, work out the QID
		{
			$debug .= "\n";
			$field=$dresult->FetchField($i);
			$fieldinfo=$field->name;
			if ($fieldinfo != "id" && $fieldinfo != "datestamp" && $fieldinfo != "ipaddr"&& $fieldinfo != "token" && $fieldinfo != "firstname" && $fieldinfo != "lastname" && $fieldinfo != "email" && $fieldinfo != "attribute_1" && $fieldinfo != "attribute_2")
			{
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
				$qq = "SELECT lid, other FROM {$dbprefix}questions WHERE qid=$fqid";
				$qr = db_execute_assoc($qq) or die("Error selecting type and lid from questions table.<br />".$qq."<br />".htmlspecialchars($connect->ErrorMsg()));
				while ($qrow = $qr->FetchRow())
				{$lid=$qrow['lid']; $fother=$qrow['other'];} // dgk bug fix. $ftype should not be modified here!
			}
			else
			{
				$fsid=""; $fgid=""; $fqid="";
				if ($type == "doc")
				{
					switch($fieldinfo)
					{
						case "firstname":
						$ftitle=$clang->gT("First Name").":";
						break;
						case "lastname":
						$ftitle=$clang->gT("Last Name").":";
						break;
						case "email":
						$ftitle=$clang->gT("Email").":";
						break;
						case "token":
						$ftitle=$clang->gT("Token").":";
						break;
						case "attribute_1":
						$ftitle=$clang->gT("Attribute 1").":";
						break;
						case "attribute_2":
						$ftitle=$clang->gT("Attribute 2").":";
						break;
						default:
						$fielddata=arraySearchByKey($fieldinfo, $fieldmap, "fieldname", 1);
						$ftitle=$fielddata['title'].":";
					} // switch
				}
			}
			if (!$fqid) {$fqid = "0";}
			if ($fqid == 0)
			{
				$ftype = "-";
			}
			if ($type == "csv") {$exportoutput .= "\"";}
			if ($type == "doc") {$exportoutput .= "\n$ftitle\n\t";}
			switch ($ftype)
			{
				case "-": //JASONS SPECIAL TYPE
				$exportoutput .= $drow[$i];
				break;
				case "R": //RANKING TYPE
				$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code = ?";
				$lr = db_execute_assoc($lq, array($drow[$i]));
				while ($lrow = $lr->FetchRow())
				{
					$exportoutput .= $lrow['answer'];
				}
				break;
				case "L": //DROPDOWN LIST
				case "!":
				if (substr($fieldinfo, -5, 5) == "other")
				{
					$exportoutput .= $drow[$i];
				}
				else
				{
					if ($drow[$i] == "-oth-")
					{
						$exportoutput .= $clang->gT("Other");
					}
					else
					{
						$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code = ?";
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
				if (substr($fieldinfo, -5, 5) == "other")
				{
					$exportoutput .= $drow[$i];
				}
				else
				{
					if ($drow[$i] == "-oth-")
					{
						$exportoutput .= $clang->gT("Other");
					}
					else
					{
						$fquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND code='$drow[$i]'";
						$fresult = db_execute_assoc($fquery) or die("ERROR:".$fquery."\n".$qq."\n".htmlspecialchars($connect->ErrorMsg()));
						while ($frow = $fresult->FetchRow())
						{
							$exportoutput .= $frow['title'];
						}
					}
				}
				break;
				case "O": //DROPDOWN LIST WITH COMMENT
				$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid ORDER BY answer";
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
					case "Y": $exportoutput .= $clang->gT("Yes"); break;
					case "N": $exportoutput .= $clang->gT("No"); break;
					default: $exportoutput .= $clang->gT("N/A"); break;
				}
				break;
				case "G": //GENDER
				switch($drow[$i])
				{
					case "M": $exportoutput .= $clang->gT("Male"); break;
					case "F": $exportoutput .= $clang->gT("Female"); break;
					default: $exportoutput .= $clang->gT("N/A"); break;
				}
				break;
				case "M": //multioption
				case "P":
				if (substr($fieldinfo, -5, 5) == "other")
				{
					$exportoutput .= "$drow[$i]";
				}
				elseif (substr($fieldinfo, -7, 7) == "comment")
				{
					$exportoutput .= "$drow[$i]";
				}
				else
				{
					switch($drow[$i])
					{
						case "Y": $exportoutput .= $clang->gT("Yes"); break;
						case "N": $exportoutput .= $clang->gT("No"); break;
						case "": $exportoutput .= $clang->gT("No"); break;
						default: $exportoutput .= $drow[$i]; break;
					}
				}
				break;
				case "C":
				switch($drow[$i])
				{
					case "Y":
					$exportoutput .= $clang->gT("Yes");
					break;
					case "N":
					$exportoutput .= $clang->gT("No");
					break;
					case "U":
					$exportoutput .= $clang->gT("Uncertain");
					break;
				}
				case "E":
				switch($drow[$i])
				{
					case "I":
					$exportoutput .= $clang->gT("Increase");
					break;
					case "S":
					$exportoutput .= $clang->gT("Same");
					break;
					case "D":
					$exportoutput .= $clang->gT("Decrease");
					break;
				}
				break;
				case "F":
				case "H":
				$fquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND code='$drow[$i]'";
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
		$exportoutput .= "\n";
	}
}
echo $exportoutput;
exit;
?>
