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
	while($i<$afieldcount)
		{
		$meta=mysql_fetch_field($result, $i);
		$excesscols[]=$meta->name;
		$i++;
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
		._CLOSEWIN."' onClick=\"window.close()\">\n"
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
	if ($tablecount > 0) //Do second column
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

//if (isset($_POST['colselect']))
//	{
//	foreach($_POST['colselect'] as $colname)
//		{
//		echo $colname."<br />";
//		}
//	}

if ($type == "doc") 
	{
	header("Content-Disposition: attachment; filename=survey.doc");
	header("Content-type: application/vnd.ms-word");
	$s="\t";
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
//$s = "\t";
$lq = "SELECT DISTINCT qid FROM {$dbprefix}questions WHERE sid=$sid"; //GET LIST OF LEGIT QIDs FOR TESTING LATER
$lr = mysql_query($lq);
$legitqs[] = "DUMMY ENTRY";
while ($lw = mysql_fetch_array($lr))
	{
	$legitqs[] = $lw['qid']; //this creates an array of question id's'
	}

//Get the fieldnames from the survey table for column headings
$surveytable = "{$dbprefix}survey_$sid";
if (isset($_POST['colselect']))
	{
	$selectfields="";
    foreach($_POST['colselect'] as $cs)
		{
		$selectfields.= "$surveytable.$cs, ";
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
for ($i=0; $i<$fieldcount; $i++)
	{
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
	else //A normal question field. Break the fieldname up into constituent parts to find $sid, $gid, and $qid
		{
		list($fsid, $fgid, $fqid) = split("X", $fieldinfo);
		if ($style == "abrev") //Print out abbreviated question title
			{
			if (!$fqid) {$fqid = 0;}
			$oldfqid=$fqid;
			while (!in_array($fqid, $legitqs)) //checks that the qid exists in our list. If not, have to do some tricky stuff to find where qid ends and answer code begins:
				{
				$fqid = substr($fqid, 0, strlen($fqid)-1); //keeps cutting off the end until it finds the real qid
				}
			if (strlen($fqid) < strlen($oldfqid)) 
				{
				$faid = substr($oldfqid, strlen($fqid), strlen($oldfqid)-strlen($fqid));
				$oldfqid="";
				}
			//Now we know what the qid is, we'll get the question text and then print it out (in abbreviate format of course)
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
		else //Print out extended question title
			{
			if (!$fqid) {$fqid = 0;}
			$oldfqid=$fqid;
			while (!in_array($fqid, $legitqs)) //checks that the qid exists in our list
				{
				$fqid = substr($fqid, 0, strlen($fqid)-1); //keeps cutting off the end until it finds the real qid
				}
			if (strlen($fqid) < strlen($oldfqid)) 
				{
				$faid = substr($oldfqid, strlen($fqid), strlen($oldfqid)-strlen($fqid));
				$oldfqid="";
				}
			$qq = "SELECT question, type FROM {$dbprefix}questions WHERE qid=$fqid"; //get the question
			$qr = mysql_query($qq);
			while ($qrow = mysql_fetch_array($qr, MYSQL_ASSOC))
				{
				$ftype = $qrow['type']; //get the question type
				$fquest = $qrow['question'];
				}
			switch ($ftype)
				{
				case "R": //RANKING TYPE
					$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code = '$faid'";
					$lr = mysql_query($lq);
					while ($lrow = mysql_fetch_array($lr, MYSQL_ASSOC))
						{
						$fquest .= " [".$lrow['answer']."]";
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
				case "Q":
					$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code= '$faid'";
					$lr = mysql_query($lq);
					while ($lrow=mysql_fetch_array($lr, MYSQL_ASSOC))
						{
						$fquest .= " [".$lrow['answer']."]";
						}
					break;
				default:
					//echo "---------- $ftype ----------";
					//if (mysql_field_name($dresult, $i) == "token")
					//	{
					//	$tokenquery = "SELECT firstname, lastname FROM {$dbprefix}tokens_$sid WHERE token='$drow[$i]'";
					//	if ($tokenresult = mysql_query($tokenquery)) //or die ("Couldn't get token info<br />$tokenquery<br />".mysql_error());
					//	while ($tokenrow=mysql_fetch_array($tokenresult))
					//		{
					//		echo "{$tokenrow['lastname']}, {$tokenrow['firstname']}";
					//		}
					//	else
					//		{
					//		echo "Not found";
					//		}
					//	}
					//else
					//	{
					//	echo str_replace("\r\n", " ", $drow[$i]);
					//	}
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
echo $firstline; //Sending the header row

//Now dump the data
if (isset($_POST['sql'])) //this applies if export has been called from the statistics package
	{
	if ($_POST['sql'] == "NULL") {$dquery = "SELECT $selectfields FROM $surveytable ORDER BY id";}
	else {$dquery = "SELECT $selectfields FROM $surveytable WHERE ".stripcslashes($_POST['sql'])." ORDER BY id";}
	}
elseif ((isset($_POST['first_name']) && $_POST['first_name']=="on") || (isset($_POST['last_name']) && $_POST['last_name']=="on") || (isset($_POST['attribute_1']) && $_POST['attribute_1']=="on") || (isset($_POST['attribute_2']) && $_POST['attribute_2'] == "on") || (isset($_POST['email_address']) && $_POST['email_address'] == "on"))
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
			. "ON $surveytable.token={$dbprefix}tokens_$sid.token "
			. "ORDER BY $surveytable.id";
	}
else // this applies for exporting everything
	{
	$dquery = "SELECT $selectfields FROM $surveytable ORDER BY id";
	}

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
	$dresult = mysql_query($dquery);
	$fieldcount = mysql_num_fields($dresult);
	while ($drow = mysql_fetch_array($dresult))
		{
		if (!ini_get('safe_mode'))
			{
			set_time_limit(3); //Give each record 3 seconds	
			}
		for ($i=0; $i<$fieldcount; $i++)
			{
			if (mysql_field_name($dresult, $i) != "id" && mysql_field_name($dresult, $i) != "datestamp" && mysql_field_name($dresult, $i) != "token")
				{
				list($fsid, $fgid, $fqid) = split("X", mysql_field_name($dresult, $i));
				}
			else
				{
				$fsid=""; $fgid=""; $fqid="";
				}
			if (!$fqid) {$fqid = 0;}
			if ($fqid == 0) 
				{
			    $ftype = "-";
				}
			else
				{
				$oldfqid=$fqid;
				while (!in_array($fqid, $legitqs)) //checks that the qid exists in our list
					{
					$fqid = substr($fqid, 0, strlen($fqid)-1);
					}
				$qq = "SELECT type, lid FROM {$dbprefix}questions WHERE qid=$fqid";
				$qr = mysql_query($qq) or die("Error selecting type and lid from questions table.<br />".$qq."<br />".mysql_error());
				while ($qrow = mysql_fetch_array($qr, MYSQL_ASSOC))
					{$ftype = $qrow['type']; $lid=$qrow['lid'];}
				}
			if ($type == "csv") {echo "\"";}
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
					$lq = "SELECT * FROM {$dbprefix}answers WHERE qid=$fqid AND code ='".mysql_escape_string($drow[$i])."'";
					$lr = mysql_query($lq) or die($lq."<br />ERROR:<br />".mysql_error());
					while ($lrow = mysql_fetch_array($lr, MYSQL_ASSOC))
						{
						//if ($lrow['code'] == $drow[$i]) {echo $lrow['answer'];} 
						echo $lrow['answer'];
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
					if (substr($oldfqid, -5, 5) == "other")
						{
						echo "$drow[$i]";
						}
					elseif (substr($oldfqid, -7, 7) == "comment")
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