<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA
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
	# Public License as published by the Free Software 			#
	# Foundation; either version 2 of the License, or (at your 	#
	# option) any later version.								#
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

require_once("config.php");
if (!isset($imagefiles)) {$imagefiles="./images";}
if (!isset($sid)) {$sid=returnglobal('sid');}
if (!isset($style)) {$style=returnglobal('style');}
if (!isset($answers)) {$answers=returnglobal('answers');}
if (!isset($type)) {$type=returnglobal('type');}

if (!$style)
	{
	sendcacheheaders();
	//FIND OUT HOW MANY FIELDS WILL BE NEEDED - FOR 255 COLUMN LIMIT
	$query="SELECT * FROM {$dbprefix}survey_$sid LIMIT 1";
	$result=mysql_query($query) or die("Couldn't count fields<br />$query<br />".mysql_error());
	$afieldcount=mysql_num_fields($result);
	$i=0;
	$fieldmap=createFieldMap($sid);
	foreach ($fieldmap as $fm) 
		{
		$query="SELECT group_name, title\n"
			  ."FROM {$dbprefix}questions, {$dbprefix}groups\n"
			  ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid\n"
			  ."AND {$dbprefix}questions.qid='".$fm['qid']."'";
		$result=mysql_query($query) or die("EXPORT: Fieldmap-$query<br />".mysql_error());
		while ($row=mysql_fetch_array($result))
			{
			$groupname=$row['group_name'];
			$title=$row['title'];
			}
		if (!isset($groupname)) { $groupname="";}
		if (!isset($title)) { $title="";}
		$eachone[]=array("fieldname"=>$fm['fieldname'],
						 "group_name"=>$groupname,
						 "title"=>$title);
		}
		foreach($eachone as $ea)
			{
			$excesscols[]=$ea['fieldname'];
			}
	echo $htmlheader
		."<br />\n"
		."<table align='center'><tr>"
		."<form action='export.php' method='post'>\n"
		."<td valign='top'>\n"
		."<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'>"
		."<font size='1' face='verdana' color='white'><b>"
		._EXPORTRESULTS;
	if (isset($_POST['sql'])) {echo " ("._EX_FROMSTATS.")";}
	echo "</b> ($afieldcount Cols)</font></td></tr>\n"
		."\t<tr><td height='8' bgcolor='silver'>$setfont<font size='1'><b>"
		._EX_HEADINGS."</b></font></font></td></tr>\n"
		."\t<tr>\n"
		."\t\t<td>\n"
		."\t\t\t$setfont<input type='radio' name='style' value='abrev' id='headabbrev'>"
		."<font size='1'><label for='headabbrev'>"
		._EX_HEAD_ABBREV."</label><br />\n"
		."\t\t\t<input type='radio' checked name='style' value='full' id='headfull'>"
		."<label for='headfull'>"
		._EX_HEAD_FULL."</label>\n"
		."\t\t</font></font></td>\n"
		."\t</tr>\n"
		."\t<tr><td height='8' bgcolor='silver'>$setfont<font size='1'><b>"
		._EX_ANSWERS."</b></font></font></td></tr>\n"
		."\t<tr>\n"
		."\t\t<td>\n"
		."\t\t\t$setfont<input type='radio' name='answers' value='short' id='ansabbrev'>"
		."<font size='1'><label for='ansabbrev'>"
		._EX_ANS_ABBREV."</label><br />\n"
		."\t\t\t<input type='radio' checked name='answers' value='long' id='ansfull'>"
		."<label for='ansfull'>"
		._EX_ANS_FULL."</label>\n"
		."\t\t</font></font></td>\n"
		."\t</tr>\n"
		."\t<tr><td height='8' bgcolor='silver'>$setfont<font size='1'><b>"
		._EX_FORMAT."</b></font></font></td></tr>\n"
		."\t<tr>\n"
		."\t\t<td>\n"
		."\t\t\t$setfont<input type='radio' name='type' value='doc' id='worddoc'>"
		."<font size='1'><label for='worddoc'>"
		._EX_FORM_WORD."</label><br />\n"
		."\t\t\t<input type='radio' name='type' value='xls' checked id='exceldoc'>"
		."<label for='exceldoc'>"
		._EX_FORM_EXCEL."</label><br />\n"
		."\t\t\t<input type='radio' name='type' value='csv' id='csvdoc'>"
		."<label for='csvdoc'>"
		._EX_FORM_CSV."</label>\n"
		."\t\t</font></font></td>\n"
		."\t</tr>\n"
		."\t<tr><td height='2' bgcolor='silver'></td></tr>\n"
		."\t<tr>\n"
		."\t\t<td align='center' bgcolor='silver'>\n"
		."\t\t\t$setfont<input $btstyle type='submit' value='"
		._EX_EXPORTDATA."'>\n"
		."\t\t</font></td>\n"
		."\t</tr>\n"
		."\t<input type='hidden' name='sid' value='$sid'>\n";
	if (isset($_POST['sql'])) 
		{
		echo "\t<input type='hidden' name='sql' value=\""
			.stripcslashes($_POST['sql'])
			."\">\n";
		}
	echo "\t<tr>\n"
		."\t\t<td align=\"center\" bgcolor='silver'>\n"
		."\t\t\t<input $btstyle type='submit' value='"
		._CLOSEWIN."' onClick=\"self.close()\">\n"
		."\t\t</td>\n"
		."\t</tr>\n"
		."</table>\n"
		."</td>";
	$query="SELECT private FROM {$dbprefix}surveys WHERE sid=$sid"; //Find out if tokens are used
	$result=mysql_query($query) or die("Couldn't get privacy data<br />$query<br />".mysql_error());
	while ($rows = mysql_fetch_array($result)) {$surveyprivate=$rows['private'];}
	if ($surveyprivate == "N")
		{
		$query="SHOW TABLES LIKE '{$dbprefix}tokens_$sid'"; //SEE IF THERE IS AN ASSOCIATED TOKENS TABLE
		$result=mysql_query($query) or die("Couldn't get table list<br />$query<br />".mysql_error());
		$tablecount=mysql_num_rows($result);
		}
	echo "<td valign='top'>\n"
		."<table align='center' width='150' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>"
		."\t<tr>\n"
		."\t\t<td height='8' bgcolor='#555555'><font face='verdana' color='white' size='1'><b>"
		._EX_COLCONTROLS."</b>\n"
		."\t\t</font></td>\n"
		."\t</tr>\n"
		."\t<tr>\n"
		."\t\t<td bgcolor='silver' height='8'><b>$setfont<font size='1'>\n"
		."\t\t\t"._EX_COLSELECT.":\n"
		."\t\t</font></font></td>\n"
		."\t</tr>\n"
		."\t<tr>\n"
		."\t\t<td>$setfont\n";
	if ($afieldcount > 255)
		{
		echo "\t\t\t<img src='$imagefiles/showhelp.gif' alt='"._HELP."' align='right' onclick='javascript:alert(\""
			._EX_COLNOTOK
			."\")'>";
		}
	else
		{
		echo "\t\t\t<img src='$imagefiles/showhelp.gif' alt='"._HELP."' align='right' onclick='javascript:alert(\""
			._EX_COLOK
			."\")'>";
		}
	echo "\t\t</font></font></td>\n"
		."\t</tr>\n"
		."\t<tr>\n"
		."\t\t<td align='center'>$setfont<font size='1'>\n"
		."\t\t\t<select name='colselect[]' $slstyle2 multiple size='15'>\n";
	$i=1;
	foreach($excesscols as $ec)
		{
		echo "<option value='$ec'";
		if ($i<256) 
			{
			echo " selected";
		    }
		echo ">$i: $ec</option>\n";
		$i++;
		}
	echo "\t\t\t</select><br />\n"
		."\t\t<img src='$imagefiles/blank.gif' height='7' alt='-'></td>\n"
		."\t</tr>\n"
		."</table>\n"
		."</td>\n";
	if (isset($tablecount) && $tablecount > 0) //Do second column
		{
		//OPTIONAL EXTRAS (FROM TOKENS TABLE)
		if ($tablecount > 0) 
			{
 		echo "<td valign='top'>\n"
 			."<table align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>"
 			."\t<tr>\n"
 			."\t\t<td height='8' bgcolor='#555555'><font face='verdana' color='white' size='1'><b>"
 			._EX_TOKENCONTROLS."</b>\n"
 			."\t\t</font></td>\n"
 			."\t</tr>\n"
 			."\t<tr>\n"
 			."\t\t<td bgcolor='silver' height='8'><b>$setfont<font size='1'>\n"
 			._EX_TOKSELECT.":"
 			."\t\t</font></font></b></td>\n"
 			."\t</tr>\n"
 			."\t<tr>\n"
 			."\t\t<td>$setfont<font size='1'>"
 			."<img src='$imagefiles/showhelp.gif' alt='"._HELP."' align='right' onclick='javascript:alert(\""
 			._EX_TOKENMESSAGE
 			."\")'><br /><br />\n"
 			."<input type='checkbox' name='first_name' id='first_name'>"
 			."<label for='first_name'>"._TL_FIRST."</label><br />\n"
 			."<input type='checkbox' name='last_name' id='last_name'>"
 			."<label for='last_name'>"._TL_LAST."</label><br />\n"
 			."<input type='checkbox' name='email_address' id='email_address'>"
 			."<label for='email_address'>"._TL_EMAIL."</label><br />\n";
 		$query = "SELECT * FROM {$dbprefix}tokens_$sid LIMIT 1"; //SEE IF TOKENS TABLE HAS ATTRIBUTE FIELDS
 		$result = mysql_query($query) or die ($query."<br />".mysql_error());
 		$rowcount = mysql_num_fields($result);
 		if ($rowcount > 7)
 			{
 			echo "<input type='checkbox' name='attribute_1' id='attribute_1'>"
 				."<label for='attribute_1'>"._TL_ATTR1."</label><br />\n"
 				."<input type='checkbox' name='attribute_2' id='attribute_2'>"
 				."<label for='attribute_2'>"._TL_ATTR2."</label><br />\n";
 			}
 		echo "\t\t</font></font></td>\n"
 			."\t</tr>\n"
 			."</table>"
 			."</td>";
			}
		}
	echo "</tr>\n"
		."\t</form>\n"
		."</table><br />\n"
		.htmlfooter("instructions.html", "General PHPSurveyor Instructions");
	exit;
	}

//HERE WE EXPORT THE ACTUAL RESULTS

if ($type == "doc") 
	{
	header("Content-Disposition: attachment; filename=survey.doc");
	header("Content-type: application/vnd.ms-word");
	$s="\n\n";
	}
elseif ($type == "xls") 
	{
	header("Content-Disposition: attachment; filename=survey.xls");
	header("Content-type: application/vnd.ms-excel");
	$s="\t";
	}
elseif ($type == "csv") 
	{
	header("Content-Disposition: attachment; filename=survey.csv");
	header("Content-Type: application/download");
	$s=",";
	}
else 
	{
	header("Content-Disposition: attachment; filename=survey.doc");
	}
sendcacheheaders();

//Select public language file
$query = "SELECT language FROM {$dbprefix}surveys WHERE sid=$sid";
$result = mysql_query($query);
while ($row=mysql_fetch_array($result)) {$surveylanguage = $row['language'];}
$langdir="$publicdir/lang";
$langfilename="$langdir/$surveylanguage.lang.php";
if (!is_file($langfilename)) {$langfilename="$langdir/$defaultlang.lang.php";}
require($langfilename);	

//STEP 1: First line is column headings

$fieldmap=createFieldMap($sid);

//Get the fieldnames from the survey table for column headings
$surveytable = "{$dbprefix}survey_$sid";
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
	$dquery .= ", {$dbprefix}tokens_$sid.firstname";
	}
if (isset($_POST['last_name']) && $_POST['last_name']=="on")
	{
	$dquery .= ", {$dbprefix}tokens_$sid.lastname";
	}
if (isset($_POST['email_address']) && $_POST['email_address']=="on")
	{
	$dquery .= ", {$dbprefix}tokens_$sid.email";
	}
if (isset($_POST['attribute_1']) && $_POST['attribute_1']=="on") 
	{
	$dquery .= ", {$dbprefix}tokens_$sid.attribute_1";
	}
if (isset($_POST['attribute_2']) && $_POST['attribute_2']=="on")
	{
	$dquery .= ", {$dbprefix}tokens_$sid.attribute_2";
	}
$dquery .= " FROM $surveytable";
if ((isset($_POST['first_name']) && $_POST['first_name']=="on") || (isset($_POST['last_name']) && $_POST['last_name']=="on") || (isset($_POST['attribute_1']) && $_POST['attribute_1']=="on") || (isset($_POST['attribute_2']) && $_POST['attribute_2']=="on") || (isset($_POST['email_address']) && $_POST['email_address']=="on")) 
	{
	$dquery .= ""
			 . " LEFT OUTER JOIN {$dbprefix}tokens_$sid"
			 . " ON $surveytable.token = {$dbprefix}tokens_$sid.token";
	}
$dquery .=" ORDER BY id LIMIT 1";
$dresult = mysql_query($dquery) or die(_ERROR." getting results<br />$dquery<br />".mysql_error());
$fieldcount = mysql_num_fields($dresult);
$firstline="";
$faid="";
$debug="";
for ($i=0; $i<$fieldcount; $i++)
	{
	//Iterate through column names and output headings
	$fieldinfo=mysql_field_name($dresult, $i);
	if ($fieldinfo == "token")
		{
		if ($answers == "short") 
			{
			if ($type == "csv") 
				{
				$firstline.="\""._TL_TOKEN."\"$s";
				}
			else 
				{
				$firstline .= _TL_TOKEN."$s";
				}
			}
		if ($answers == "long") 
			{
			if ($style == "abrev")
				{
				if ($type == "csv") {$firstline .= "\""._TL_TOKEN."\"$s";}
				else {$firstline .= _TL_TOKEN."$s";}
				}
			else 
				{
				if ($type == "csv") {$firstline .= "\""._TL_TOKEN."\"$s";}
				else {$firstline .= _TL_TOKEN."$s";}
				}
			}
		}
	elseif ($fieldinfo == "lastname")
		{
		if ($type == "csv") {$firstline .= "\""._TL_LAST."\"$s";}
		else {$firstline .= _TL_LAST."$s";}
		}
	elseif ($fieldinfo == "firstname")
		{
		if ($type == "csv") {$firstline .= "\""._TL_FIRST."\"$s";}
		else {$firstline .= _TL_FIRST."$s";}
		}
	elseif ($fieldinfo == "email")
		{
		if ($type == "csv") {$firstline .= "\""._EMAIL."\"$s";}
		else {$firstline .= _EMAIL."$s";}
		}
	elseif ($fieldinfo == "attribute_1")
		{
		if ($type == "csv") {$firstline .= "\"attr1\"$s";}
		else {$firstline .= _TL_ATTR1."$s";}
		}
	elseif ($fieldinfo == "attribute_2")
		{
		if ($type == "csv") {$firstline .= "\"attr2\"$s";}
		else {$firstline .= _TL_ATTR2."$s";}
		}
	elseif ($fieldinfo == "id")
		{
		if ($type == "csv") {$firstline .= "\"id\"$s";}
		else {$firstline .= "id$s";}
		}
	elseif ($fieldinfo == "datestamp")
		{
		if ($type == "csv") {$firstline .= "\"Time Submitted\"$s";}
		else {$firstline .= "Time Submitted$s";}
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
			$qr = mysql_query($qq);
			while ($qrow=mysql_fetch_array($qr, MYSQL_ASSOC))
				{$qname=$qrow['question'];}
			$qname=substr($qname, 0, 15)."..";
			$qname=strip_tags($qname);
			$firstline = str_replace("\n", "", $firstline);
			$firstline = str_replace("\r", "", $firstline);
			if ($type == "csv") {$firstline .= "\"$qname";}
			else {$firstline .= "$qname";}
			if (isset($faid)) {$firstline .= " [{$faid}]"; $faid="";}
			if ($type == "csv") {$firstline .= "\"";}
			$firstline .= "$s";
			}
		else
			{
			$qq = "SELECT question, type, other FROM {$dbprefix}questions WHERE qid=$fqid"; //get the question
			$qr = mysql_query($qq) or die ("ERROR:<br />".$qq."<br />".mysql_error());
			while ($qrow=mysql_fetch_array($qr, MYSQL_ASSOC))
				{$fquest=$qrow['question'];}
			switch ($ftype)
				{
				case "R": //RANKING TYPE
					$fquest .= " ["._RANK." $faid]";
					break;
				case "L":
				case "!":
					if ($faid == "other") {
						$fquest .= " ["._OTHER."]";
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
						$fquest .= " ["._OTHER."]";
						}
					else
						{
						$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code = '$faid'";
						$lr = mysql_query($lq);
						while ($lrow = mysql_fetch_array($lr, MYSQL_ASSOC))
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
						$fquest .= " ["._OTHER."]";
						}
					else
						{
						$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code = '$faid'";
						$lr = mysql_query($lq);
						while ($lrow = mysql_fetch_array($lr, MYSQL_ASSOC))
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
					$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code= '$faid'";
					$lr = mysql_query($lq);
					$debug .= " | QUERY FOR ANSWER CODE [$lq]";
					while ($lrow=mysql_fetch_array($lr, MYSQL_ASSOC))
						{
						$fquest .= " [".$lrow['answer']."]";
						}
					break;
				}
			$fquest = strip_tags($fquest);
			$fquest = str_replace("\n", " ", $fquest);
			$fquest = str_replace("\r", "", $fquest);
			if ($type == "csv")
				{
				$firstline .="\"$fquest\"$s";
				}
			else
				{
				$firstline .= "$fquest $s";
				}
			}		
		}	
	}
$firstline = trim($firstline);
$firstline .= "\n";

if ($type == "doc") 
	{
	$flarray=explode($s, $firstline);
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
	echo $firstline; //Sending the header row
	}


//Now dump the data
if ((isset($_POST['first_name']) && $_POST['first_name']=="on") || (isset($_POST['last_name']) && $_POST['last_name']=="on") || (isset($_POST['attribute_1']) && $_POST['attribute_1']=="on") || (isset($_POST['attribute_2']) && $_POST['attribute_2'] == "on") || (isset($_POST['email_address']) && $_POST['email_address'] == "on"))
	{
	$dquery = "SELECT $selectfields";
	if (isset($_POST['first_name']) && $_POST['first_name']=="on")
		{
		$dquery .= ", {$dbprefix}tokens_$sid.firstname";
		}
	if (isset($_POST['last_name']) && $_POST['last_name']=="on")
		{
		$dquery .= ", {$dbprefix}tokens_$sid.lastname";
		}
	if (isset($_POST['email_address']) && $_POST['email_address']=="on")
		{
		$dquery .= ", {$dbprefix}tokens_$sid.email";
		}
	if (isset($_POST['attribute_1']) && $_POST['attribute_1']=="on")
		{
		$dquery .= ", {$dbprefix}tokens_$sid.attribute_1";
		}
	if (isset($_POST['attribute_2']) && $_POST['attribute_2']=="on")
		{
		$dquery .= ", {$dbprefix}tokens_$sid.attribute_2";
		}
	$dquery	.= " FROM $surveytable "
			. "LEFT OUTER JOIN {$dbprefix}tokens_$sid "
			. "ON $surveytable.token={$dbprefix}tokens_$sid.token ";
	}
else // this applies for exporting everything
	{
	$dquery = "SELECT $selectfields FROM $surveytable ";
	}

if (isset($_POST['sql'])) //this applies if export has been called from the statistics package
	{
	if ($_POST['sql'] != "NULL") {$dquery .= "WHERE ".stripcslashes($_POST['sql'])." ";}
	}

$dquery .= "ORDER BY $surveytable.id";
if ($answers == "short") //Nice and easy. Just dump the data straight
	{
	$dresult = mysql_query($dquery);
	while ($drow = mysql_fetch_array($dresult, MYSQL_ASSOC))
		{
		if ($type == "csv")
			{
			echo "\"".implode("\"$s\"", str_replace("\"", "\"\"", str_replace("\r\n", " ", $drow))) . "\"\n"; //create dump from each row
			}
		else
			{
			echo implode($s, str_replace("\r\n", " ", $drow)) . "\n"; //create dump from each row
			}
		}
	}
elseif ($answers == "long")
	{
	$debug="";
	$dresult = mysql_query($dquery) or die("ERROR: $dquery -".mysql_error());
	$fieldcount = mysql_num_fields($dresult);
	while ($drow = mysql_fetch_array($dresult))
		{
		if ($type == "doc")
			{
		    echo "\n\n\nNEW RECORD\n";
			}
		if (!ini_get('safe_mode'))
			{
			set_time_limit(3); //Give each record 3 seconds	
			}
		for ($i=0; $i<$fieldcount; $i++) //For each field, work out the QID
			{
			$debug .= "\n";
			$fieldinfo=mysql_field_name($dresult, $i);
			if (mysql_field_name($dresult, $i) != "id" && mysql_field_name($dresult, $i) != "datestamp" && mysql_field_name($dresult, $i) != "token" && mysql_field_name($dresult, $i) != "firstname" && mysql_field_name($dresult, $i) != "lastname" && mysql_field_name($dresult, $i) != "email" && mysql_field_name($dresult, $i) != "attribute_1" && mysql_field_name($dresult, $i) != "attribute_2")
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
				$qr = mysql_query($qq) or die("Error selecting type and lid from questions table.<br />".$qq."<br />".mysql_error());
				while ($qrow = mysql_fetch_array($qr, MYSQL_ASSOC))
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
							$ftitle=_TL_FIRST.":";
							break;
						case "lastname":
							$ftitle=_TL_LAST.":";
							break;
						case "email":
							$ftitle=_TL_EMAIL.":";
							break;
						case "attribute_1":
							$ftitle=_TL_ATTR1.":";
							break;
						case "attribute_2":
							$ftitle=_TL_ATTR2.":";
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
			if ($type == "csv") {echo "\"";}
			if ($type == "doc") {echo "\n$ftitle\n\t";}
			switch ($ftype)
				{
				case "-": //JASONS SPECIAL TYPE
					echo $drow[$i];
					break;
				case "R": //RANKING TYPE
					$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code = '".mysql_escape_string($drow[$i])."'";
					$lr = mysql_query($lq);
					while ($lrow = mysql_fetch_array($lr, MYSQL_ASSOC))
						{
						echo $lrow['answer'];
						}
					break;
				case "L": //DROPDOWN LIST
				case "!":
					if (substr($fieldinfo, -5, 5) == "other") 
						{
						echo $drow[$i];
						}
					else
						{
						if ($drow[$i] == "-oth-") 
							{
						    echo _OTHER;
							}
						else
							{
							$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code ='".mysql_escape_string($drow[$i])."'";
							$lr = mysql_query($lq) or die($lq."<br />ERROR:<br />".mysql_error());
							while ($lrow = mysql_fetch_array($lr, MYSQL_ASSOC))
								{
								//if ($lrow['code'] == $drow[$i]) {echo $lrow['answer'];} 
								echo $lrow['answer'];
								}
							}
						}
					break;
				case "O": //DROPDOWN LIST WITH COMMENT
					$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid ORDER BY answer";
					$lr = mysql_query($lq) or die ("Could do it<br />$lq<br />".mysql_error());
					while ($lrow = mysql_fetch_array($lr, MYSQL_ASSOC))
						{
						if ($lrow['code'] == $drow[$i]) {echo $lrow['answer']; $found = "Y";}
						}
					if ($found != "Y") {if ($type == "csv") {echo str_replace("\"", "\"\"", $drow[$i]);} else {echo str_replace("\r\n", " ", $drow[$i]);}}
					$found = "";
					break;
				case "Y": //YES\NO
					switch($drow[$i])
						{
						case "Y": echo _YES; break;
						case "N": echo _NO; break;
						default: echo _NOTAPPLICABLE; break;
						}
					break;
				case "G": //GENDER
					switch($drow[$i])
						{
						case "M": echo _MALE; break;
						case "F": echo _FEMALE; break;
						default: echo _NOTAPPLICABLE; break;
						}
					break;
				case "M": //multioption
				case "P":
					if (substr($fieldinfo, -5, 5) == "other")
						{
						echo "$drow[$i]";
						}
					elseif (substr($fieldinfo, -7, 7) == "comment")
						{
						echo "$drow[$i]";
						}
					else
						{
						switch($drow[$i])
							{
							case "Y": echo _YES; break;
							case "N": echo _NO; break;
							case "": echo _NO; break;
							default: echo $drow[$i]; break;
							}
						}
					break;
				case "C":
					switch($drow[$i])
						{
						case "Y":
							echo _YES;
							break;
						case "N":
							echo _NO;
							break;
						case "U":
							echo _UNCERTAIN;
							break;
						}
				case "E":
					switch($drow[$i])
						{
						case "I":
							echo _INCREASE;
							break;
						case "S":
							echo _SAME;
							break;
						case "D":
							echo _DECREASE;
							break;
						}
					break;
				case "F":
				case "H":
					$fquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND code='$drow[$i]'";
					$fresult = mysql_query($fquery) or die("ERROR:".$fquery."\n".$qq."\n".mysql_error());
					while ($frow = mysql_fetch_array($fresult))
						{
						echo $frow['title'];
						}
					break;
				default:
					if (mysql_field_name($dresult, $i) == "token")
						{
						$tokenquery = "SELECT firstname, lastname FROM {$dbprefix}tokens_$sid WHERE token='$drow[$i]'";
						if ($tokenresult = mysql_query($tokenquery)) //or die ("Couldn't get token info<br />$tokenquery<br />".mysql_error());
						while ($tokenrow=mysql_fetch_array($tokenresult))
							{
							echo "{$tokenrow['lastname']}, {$tokenrow['firstname']}";
							}
						else
							{
							echo "Tokens problem - token table missing";
							}
						}
					else
						{
						if ($type == "csv")
						{echo str_replace("\r\n", "\n", str_replace("\"", "\"\"", $drow[$i]));}
						else
						{echo str_replace("\r\n", " ", $drow[$i]);}
						}
				}
			if ($type == "csv") {echo "\"";}
			echo "$s";
			$ftype = "";
			}
		echo "\n";
		}
	}
?>