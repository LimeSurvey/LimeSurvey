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
$action = $_GET['action']; if (!$action) {$action = $_POST['action'];}
$sid = $_GET['sid']; if (!$sid) {$sid = $_POST['sid'];}
$id = $_GET['id']; if (!$id) {$id = $_POST['id'];}
$surveytable = $_GET['surveytable']; if (!$surveytable) {$surveytable = $_POST['surveytable'];}

include("config.php");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
//Send ("Expires: " & Format$(Date - 30, "ddd, d mmm yyyy") & " " & Format$(Time, "hh:mm:ss") & " GMT ") 
$surveyoptions = browsemenubar();
echo $htmlheader;
echo "<table height='1'><tr><td></td></tr></table>\n";
//echo "<table width='100%' border='0' bgcolor='#555555'><tr><td align='center'><font color='white'><b>Data Entry</b></td></tr></table>\n";

if (!mysql_selectdb ($databasename, $connect))
	{
	//echo "</table>\n";
	echo "<table height='1'><tr><td></td></tr></table>\n";
	echo "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._DATAENTRY."</b></td></tr>\n";
	echo "\t<tr height='22' bgcolor='#CCCCCC'><td align='center'>$setfont\n";
	echo "<b><font color='red'>"._ERROR."</font></b><br />\n";
	echo _ST_NODB1."<br />\n";
	echo _ST_NODB2."<br /><br />\n";
	echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\"><br />\n";
	echo "</td></tr></table>\n";
	echo "</body>\n</html>";
	exit;
	}
if (!$sid && !$action)
	{
	//echo "</table>\n";
	echo "<table height='1'><tr><td></td></tr></table>\n";
	echo "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._DATAENTRY."</b></td></tr>\n";
	echo "\t<tr height='22' bgcolor='#CCCCCC'><td align='center'>$setfont\n";
	echo "<b><font color='red'>"._ERROR."</font></b><br />\n";
	echo _DE_NOSID."<br /><br />\n";
	echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\"><br />\n";
	echo "</td></tr></table>\n";
	echo "</body>\n</html>";
	exit;
	}

if ($action == "edit" || $action == "")
	{
	$query = "SELECT language FROM {$dbprefix}surveys WHERE sid=$sid";
	$result = mysql_query($query);
	while ($row=mysql_fetch_row($result)) {$surveylanguage = $row['language'];}
	if (!$templatedir) {$thistpl=$tpldir."/default";} else {$thistpl=$tpldir."/$templatedir";}
	if (!is_dir($thistpl)) {$thistpl=$tpldir."/default";}
	$langdir="$publicdir/lang";
	$langfilename="$langdir/$surveylanguage.lang.php";
	if (!is_file($langfilename)) {$langfilename="$langdir/$defaultlang.lang.php";}
	require($langfilename);	
	}	
	
if ($action == "insert")
	{
	echo "<table height='1'><tr><td></td></tr></table>\n";
	echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._DATAENTRY."</b></td></tr>\n";
	echo "\t<tr height='22' bgcolor='#CCCCCC'><td align='center'>$setfont\n";
	echo "\t\t\t<b>Inserting data</b><br />\n";
	echo "SID: $sid, ($surveytable)<br /><br />\n";
	$iquery = "SELECT * FROM {$dbprefix}questions, {$dbprefix}groups WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}questions.sid=$sid ORDER BY group_name, title";
	$iresult = mysql_query($iquery);
	
	while ($irow = mysql_fetch_array($iresult))
		{
		if ($irow['type'] != "M" && $irow['type'] != "A" && $irow['type'] != "B" && $irow['type'] != "C" && $irow['type'] != "E" && $irow['type'] != "F" && $irow['type'] != "P" && $irow['type'] != "O" && $irow['type'] != "R" && $irow['type'] != "Q")
			{
			$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
			$col_name .= "$fieldname, \n";
			if (get_magic_quotes_gpc())
				{$insertqr .= "'" . $_POST[$fieldname] . "', \n";}
			else
				{
				if (phpversion() >= "4.3.0")
					{
					$insertqr .= "'" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
					}
				else
					{
					$insertqr .= "'" . mysql_escape_string($_POST[$fieldname]) . "', \n";
					}
				}
			}
		elseif ($irow['type'] == "O")
			{
			$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
			$fieldname2 = $fieldname . "comment";
			$col_name .= "$fieldname, \n$fieldname2, \n";
			if (get_magic_quotes_gpc())
				{$insertqr .= "'" . $_POST[$fieldname] . "', \n'" . $_POST[$fieldname2] . "', \n";}
			else
				{
				if (phpversion() >= "4.3.0")
					{
					$insertqr .= "'" . mysql_real_escape_string($_POST[$fieldname]) . "', \n'" . mysql_real_escape_string($_POST[$fieldname2]) . "', \n";
					}
				else
					{
					$insertqr .= "'" . mysql_escape_string($_POST[$fieldname]) . "', \n'" . mysql_escape_string($_POST[$fieldname2]) . "', \n";
					}
				}
			}
		elseif ($irow['type'] == "R")
			{
			$i2query = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.qid={$irow['qid']} AND {$dbprefix}questions.sid=$sid ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			$i2result = mysql_query($i2query);
			$i2count = mysql_num_rows($i2result);
			for ($i=1; $i<=$i2count; $i++)
				{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}$i";
				$col_name .= "$fieldname, \n";		
				if (get_magic_quotes_gpc())
					{$insertqr .= "'" . $_POST["d$fieldname"] . "', \n";}
				else
					{
					if (phpversion() >= "4.3.0")
						{
						$insertqr .= "'" . mysql_real_escape_string($_POST["d$fieldname"]) . "', \n";
						}
					else
						{
						$insertqr .= "'" . mysql_escape_string($_POST["d$fieldname"]) . "', \n";
						}
					}
				}
			}
		else
			{
			$i2query = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.qid={$irow['qid']} AND {$dbprefix}questions.sid=$sid ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			$i2result = mysql_query($i2query);
			while ($i2row = mysql_fetch_array($i2result))
				{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}";
				$col_name .= "$fieldname, \n";
				if (get_magic_quotes_gpc())
					{$insertqr .= "'" . $_POST[$fieldname] . "', \n";}
				else
					{
					if (phpversion() >= "4.3.0")
						{
						$insertqr .= "'" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
						}
					else
						{
						$insertqr .= "'" . mysql_escape_string($_POST[$fieldname]) . "', \n";
						}
					}
				$otherexists = "";
				if ($i2row['other'] == "Y") {$otherexists = "Y";}
				if ($irow['type'] == "P")
					{
					$fieldname2 = $fieldname."comment";
					$col_name .= "$fieldname2, \n";
					if (get_magic_quotes_gpc())
						{$insertqr .= "'" . $_POST[$fieldname2] . "', \n";}
					else
						{
						if (phpversion() >= "4.3.0")
							{
							$insertqr .= "'" . mysql_real_escape_string($_POST[$fieldname2]) . "', \n";
							}
						else
							{
							$insertqr .= "'" . mysql_escape_string($_POST[$fieldname2]) . "', \n";
							}
						}
					}
				}
			if ($otherexists == "Y") 
				{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}other";
				$col_name .= "$fieldname, \n";
				if (get_magic_quotes_gpc())
					{$insertqr .= "'" . $_POST[$fieldname] . "', \n";}
				else
					{
					if (phpversion() >= "4.3.0")
						{
						$insertqr .= "'" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
						}
					else
						{
						$insertqr .= "'" . mysql_escape_string($_POST[$fieldname]) . "', \n";
						}
					}
				}
			}
		}
	
	$col_name = substr($col_name, 0, -3); //Strip off the last comma-space
	$insertqr = substr($insertqr, 0, -3); //Strip off the last comma-space
	
	if ($_POST['token']) //handle tokens if survey needs them
		{
		$col_name .= ", token\n";
		$insertqr .= ", '{$_POST['token']}'";
		}
	if ($_POST['datestamp']) //handle datestamp if needed
		{
		$col_name .= ", datestamp\n";
		$insertqr .= ", '{$_POST['datestamp']}'";
		}
	
	
	$SQL = "INSERT INTO $surveytable \n($col_name) \nVALUES \n($insertqr)";
	//echo $SQL; //Debugging line
	$iinsert = mysql_query($SQL) or die ("Could not insert your data:<br />\n" . mysql_error() . "\n<pre style='text-align: left'>$SQL</pre>\n</body>\n</html>");
	
	echo "\t\t\t<font color='green'><b>"._SUCCESS."</b></font><br />\n";
	
	$fquery = "SELECT id FROM $surveytable ORDER BY id DESC LIMIT 1";
	$fresult = mysql_query($fquery);
	while ($frow = mysql_fetch_array($fresult))
		{
		echo "\t\t\t"._DE_RECORD." {$frow['id']}<br />\n";
		$thisid=$frow['id'];
		}
	
	echo "\t\t\t</font><br />[<a href='dataentry.php?sid=$sid'>"._DE_ADDANOTHER."</a>]<br />\n";
	echo "\t\t\t[<a href='browse.php?sid=$sid&action=id&id=$thisid'>"._DE_VIEWTHISONE."</a>]<br />\n";
	echo "\t\t\t[<a href='browse.php?sid=$sid&action=all&limit=50'>"._DE_BROWSE."</a>]<br />\n";
	echo "\t</td></tr>\n";
	echo "</table>\n";
	//echo "<pre style='text-align: left'>$SQL</pre><br />\n"; //Debugging info
	echo "</body>\n</html>";
	
	}

elseif ($action == "edit")
	{

	echo "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._BROWSERESPONSES."</b></td></tr>\n";
	echo "$surveyheader";
	echo "$surveyoptions";
	echo "</table>\n";
	echo "<table height='1'><tr><td></td></tr></table>\n";

	//FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
	$fnquery = "SELECT * FROM {$dbprefix}questions, {$dbprefix}groups, {$dbprefix}surveys WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}questions.sid={$dbprefix}surveys.sid AND {$dbprefix}questions.sid='$sid'";
	$fnresult = mysql_query($fnquery);
	$fncount = mysql_num_rows($fnresult);
	//echo "$fnquery<br /><br />\n";
	
	$arows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	while ($fnrow = mysql_fetch_assoc($fnresult)) {$fnrows[] = $fnrow; $private=$fnrow['private']; $datestamp=$fnrow['datestamp'];} // Get table output into array
	
	// Perform a case insensitive natural sort on group name then question title of a multidimensional array
	usort($fnrows, 'CompareGroupThenTitle');
	
	// $fnames = (Field Name in Survey Table, Short Title of Question, Question Type, Field Name, Question Code, Predetermined Answers if exist) 
	$fnames[] = array("id", "id", "id", "id", "id", "id", "id");

	if ($private == "N") //show token info if survey not private
		{
		$fnames[] = array ("token", "Token ID", "Token", "token", "TID", "");
		}
	if ($datestamp == "Y")
		{
		$fnames[] = array ("datestamp", "Date Stamp", "Datestamp", "datestamp", "datestamp", "");
		}

	foreach ($fnrows as $fnrow)
		{
		$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}";
		$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}";
		$fquestion = $fnrow['question'];
		if ($fnrow['type'] == "M" || $fnrow['type'] == "A" || $fnrow['type'] == "B" || $fnrow['type'] == "C" || $fnrow['type'] == "E" || $fnrow['type'] == "F" || $fnrow['type'] == "P" || $fnrow['type'] == "Q")
			{
			$fnrquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$fnrow['qid']} ORDER BY sortorder, answer";
			$fnrresult = mysql_query($fnrquery);
			while ($fnrrow = mysql_fetch_array($fnrresult))
				{
				$fnames[] = array("$field{$fnrrow['code']}", "$ftitle ({$fnrrow['code']})", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
				if ($fnrow['type'] == "P")
					{
					$fnames[] = array("$field{$fnrrow['code']}"."comment", "$ftitle"."comment", "{$fnrow['question']}(comment)", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
					}
				}
			if ($fnrow['other'] == "Y")
				{
				$fnames[] = array("$field"."other", "$ftitle"."other", "{$fnrow['question']}(other)", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
				if ($fnrow['type'] == "P")
					{
					$fnames[] = array("$field"."othercomment", "$ftitle"."othercomment", "{$fnrow['question']}(other comment)", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
					}
				}
			}
		elseif ($fnrow['type'] == "R")
			{
			$fnrquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$fnrow['qid']} ORDER BY sortorder, answer";
			$fnrresult = mysql_query($fnrquery);
			$fnrcount = mysql_num_rows($fnrresult);
			for ($j=1; $j<=$fnrcount; $j++)
				{
				$fnames[] = array("$field$j", "$ftitle ($j)", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "$j", "{$fnrow['qid']}", "{$fnrow['lid']}");
				}
			}
		elseif ($fnrow['type'] == "O")
			{
			$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
			$field2 = $field."comment";
			$ftitle2 = $ftitle."[Comment]";
			$longtitle = "{$fnrow['question']}<br />(Comment)";
			$fnames[] = array("$field2", "$ftitle", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
			}
		else
			{
			$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
			}
		//$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}", "{$fnrow['type']}");
		//echo "$field | $ftitle | $fquestion<br />\n";
		}
	//echo "<pre>"; print_r($fnames); echo "</pre>"; //Debugging info
	$nfncount = count($fnames)-1;

	foreach ($fnames as $fnm)
		{
		echo "<!-- DEBUG FNAMES: $fnm[0], $fnm[1], $fnm[2], $fnm[3], $fnm[4], $fnm[5], $fnm[6], $fnm[7], $fnm[8] -->\n";
		}
	
	//SHOW INDIVIDUAL RECORD
	$idquery = "SELECT * FROM $surveytable WHERE id=$id";
	$idresult = mysql_query($idquery) or die ("Couldn't get individual record<br />$idquery<br />".mysql_error());
	echo "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._DATAENTRY."</b></td></tr>\n";
	echo "<form method='post' action='dataentry.php' name='editsurvey' id='editsurvey'>\n";
	echo "\t<tr><td style='border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #555555' colspan='2' bgcolor='#999999' align='center'>$setfont<b>"._DE_EDITING." (ID $id)</td></tr>\n";
	echo "\t<tr><td colspan='2' bgcolor='#CCCCCC' height='1'></td></tr>\n";

	while ($idrow = mysql_fetch_assoc($idresult))
		{
		for ($i=0; $i<$nfncount+1; $i++)
			{
			$answer = $idrow[$fnames[$i][0]];
			echo "\t<tr>\n";
			echo "\t\t<td bgcolor='#EEEEEE' valign='top' align='right' width='20%'>$setfont";
			echo "<b>\n";
			if ($fnames[$i][3] != "A" && $fnames[$i][3] != "B" && $fnames[$i][3]!="C" && $fnames[$i][3] != "E" && $fnames[$i][3]!="P" && $fnames[$i][3] != "M") 
				{
				echo "\t\t\t{$fnames[$i][2]}\n";
				}
			else
				{
				echo "\t\t\t{$fnames[$i][2]}\n";
				}
			echo "\t\t</td>\n";
			echo "\t\t<td valign='top'>\n";
			//echo "\t\t\t-={$fnames[$i][3]}=-"; //Debugging info
			switch ($fnames[$i][3])
				{
				case "id":
					echo "\t\t\t{$idrow[$fnames[$i][0]]} <font color='red' size='1'>"._DE_NOMODIFY."</font>\n";
					break;
				case "5": //5 POINT CHOICE radio-buttons
					for ($x=1; $x<=5; $x++)
						{
						echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='$x'";
						if ($idrow[$fnames[$i][0]] == $x) {echo " checked";}
						echo " />$x \n";
						}
					break;
				case "D": //DATE
					echo "\t\t\t<input type='text' size='10' name='{$fnames[$i][0]}' value='{$idrow[$fnames[$i][0]]}' />\n";
					break;
				case "G": //GENDER drop-down list
					echo "\t\t\t<select name='{$fnames[$i][0]}'>\n";
					echo "\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
					echo ">"._PLEASECHOOSE."..</option>\n";
					echo "\t\t\t\t<option value='F'";
					if ($idrow[$fnames[$i][0]] == "F") {echo " selected";}
					echo ">"._FEMALE."</option>\n";
					echo "\t\t\t\t<option value='M'";
					if ($idrow[$fnames[$i][0]] == "M") {echo " selected";}
					echo ">"._MALE."</option>\n";
					echo "\t\t\t<select>\n";
					break;
				case "L": //LIST drop-down/radio-button list
					$lquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$fnames[$i][7]} ORDER BY sortorder, answer";
					$lresult = mysql_query($lquery);
					echo "\t\t\t<select name='{$fnames[$i][0]}'>\n";
					echo "\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
					echo ">"._PLEASECHOOSE."..</option>\n";
					
					while ($llrow = mysql_fetch_array($lresult))
						{
						echo "\t\t\t\t<option value='{$llrow['code']}'";
						if ($idrow[$fnames[$i][0]] == $llrow['code']) {echo " selected";}
						echo ">{$llrow['answer']}</option>\n";
						}
					echo "\t\t\t</select>\n";
					break;
				case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
					$lquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$fnames[$i][7]} ORDER BY sortorder, answer";
					$lresult = mysql_query($lquery);
					echo "\t\t\t<select name='{$fnames[$i][0]}'>\n";
					echo "\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
					echo ">"._PLEASECHOOSE."..</option>\n";
					
					while ($llrow = mysql_fetch_array($lresult))
						{
						echo "\t\t\t\t<option value='{$llrow['code']}'";
						if ($idrow[$fnames[$i][0]] == $llrow['code']) {echo " selected";}
						echo ">{$llrow['answer']}</option>\n";
						}
					echo "\t\t\t</select>\n";
					$i++;
					echo "\t\t\t<br />\n";
					echo "\t\t\t<textarea cols='45' rows='5' name='{$fnames[$i][0]}'>";
					echo htmlspecialchars($idrow[$fnames[$i][0]]) . "</textarea>\n";
					break;

				case "R": //RANKING TYPE QUESTION
					$l=$i;
					$thisqid=$fnames[$l][7];
					$myfname=substr($fnames[$i][0], 0, -1);
					while ($fnames[$i][3] == "R")
						{
						//Let's get all the existing values into an array
						if ($idrow[$fnames[$i][0]])
							{
							$currentvalues[] = $idrow[$fnames[$i][0]];						
							}
						$i++;
						}
					$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$thisqid ORDER BY sortorder, answer";
					$ansresult = mysql_query($ansquery);
					$anscount = mysql_num_rows($ansresult);
					echo "\t\t\t<script type='text/javascript'>\n";
					echo "\t\t\t<!--\n";
					echo "\t\t\t\tfunction rankthis_$thisqid(\$code, \$value)\n";
					echo "\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\$index=document.editsurvey.CHOICES_$thisqid.selectedIndex;\n";
					echo "\t\t\t\t\tdocument.editsurvey.CHOICES_$thisqid.selectedIndex=-1;\n";
					echo "\t\t\t\t\tfor (i=1; i<=$anscount; i++)\n";
					echo "\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\t\$b=i;\n";
					echo "\t\t\t\t\t\t\$b += '';\n";	
					echo "\t\t\t\t\t\t\$inputname=\"RANK_$thisqid\"+\$b;\n";
					echo "\t\t\t\t\t\t\$hiddenname=\"d$myfname\"+\$b;\n";
					echo "\t\t\t\t\t\t\$cutname=\"cut_$thisqid\"+i;\n";
					echo "\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='none';\n";
					echo "\t\t\t\t\t\tif (!document.getElementById(\$inputname).value)\n";
					echo "\t\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\t\tdocument.getElementById(\$inputname).value=\$value;\n";
					echo "\t\t\t\t\t\t\tdocument.getElementById(\$hiddenname).value=\$code;\n";
					echo "\t\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='';\n";
					echo "\t\t\t\t\t\t\tfor (var b=document.getElementById('CHOICES_$thisqid').options.length-1; b>=0; b--)\n";
					echo "\t\t\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\t\t\tif (document.getElementById('CHOICES_$thisqid').options[b].value == \$code)\n";
					echo "\t\t\t\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').options[b] = null;\n";
					echo "\t\t\t\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\t\t\ti=$anscount;\n";
					echo "\t\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\tif (document.getElementById('CHOICES_$thisqid').options.length == 0)\n";
					echo "\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').disabled=true;\n";
					echo "\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\t}\n";
					echo "\t\t\t\tfunction deletethis_$thisqid(\$text, \$value, \$name, \$thisname)\n";
					echo "\t\t\t\t\t{\n";
					echo "\t\t\t\t\tvar qid='$thisqid';\n";
					echo "\t\t\t\t\tvar lngth=qid.length+4;\n";
					echo "\t\t\t\t\tvar cutindex=\$thisname.substring(lngth, \$thisname.length);\n";
					echo "\t\t\t\t\tcutindex=parseFloat(cutindex);\n";
					echo "\t\t\t\t\tdocument.getElementById(\$name).value='';\n";
					echo "\t\t\t\t\tdocument.getElementById(\$thisname).style.display='none';\n";
					echo "\t\t\t\t\tif (cutindex > 1)\n";
					echo "\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\t\$cut1name=\"cut_$thisqid\"+(cutindex-1);\n";
					echo "\t\t\t\t\t\t\$cut2name=\"d$myfname\"+(cutindex);\n";
					echo "\t\t\t\t\t\tdocument.getElementById(\$cut1name).style.display='';\n";
					echo "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n";
					echo "\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\telse\n";
					echo "\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\t\$cut2name=\"d$myfname\"+(cutindex);\n";
					echo "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n";
					echo "\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\tvar i=document.getElementById('CHOICES_$thisqid').options.length;\n";
					echo "\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').options[i] = new Option(\$text, \$value);\n";
					echo "\t\t\t\t\tif (document.getElementById('CHOICES_$thisqid').options.length > 0)\n";
					echo "\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').disabled=false;\n";
					echo "\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\t}\n";
					echo "\t\t\t//-->\n";
					echo "\t\t\t</script>\n";	
					while ($ansrow = mysql_fetch_array($ansresult)) //Now we're getting the codes and answers
						{
						$answers[] = array($ansrow['code'], $ansrow['answer']);
						}
					//now find out how many existing values there are
					$existing = count($currentvalues);
					
					for ($j=1; $j<=$anscount; $j++) //go through each ranking and check for matching answer
						{
						$k=$j-1;
						if ($currentvalues[$k]) 
							{
							foreach ($answers as $ans)
								{
								if ($ans[0] == $currentvalues[$k])
									{
									$thiscode=$ans[0];
									$thistext=$ans[1];
									}
								}
							}
						$ranklist .= "\t\t\t\t\t\t&nbsp;<font color='#000080'>$j:&nbsp;<input style='width:150; color: #222222; font-size: 10; background-color: silver' name='RANK$j' id='RANK$j'";
						if ($currentvalues[$k])
							{
							$ranklist .= " value='";
							$ranklist .= $thistext;
							$ranklist .= "'";
							}
						$ranklist .= " onFocus=\"this.blur()\">\n";
						$ranklist .= "\t\t\t\t\t\t<input type='hidden' id='d$myfname$j' name='d$myfname$j' value='";
						$chosen[]=""; //create array
						if ($currentvalues[$k]) 
							{
							$ranklist .= $thiscode;
							$chosen[]=array($thiscode, $thistext);
							}
						$ranklist .= "'>\n";
						$ranklist .= "\t\t\t\t\t\t<img src='./images/cut.gif' title='"._REMOVEITEM."' ";
						if ($j != $existing)
							{
							$ranklist .= "style='display:none'";
							}
						$ranklist .= " id='cut_$thisqid$j' name='cut$j' onClick=\"deletethis(document.editsurvey.RANK_$thisqid$j.value, document.editsurvey.d$myfname$j.value, document.editsurvey.RANK_$thisqid$j.id, this.id)\"><br />\n\n";
						}
					
					$choicelist .= "\t\t\t\t\t\t<select size='$anscount' name='CHOICES' id='CHOICES_$thisqid' onClick=\"rankthis_$thisqid(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" style='background-color: #EEEFFF; font-family: verdana; font-size: 12; color: #000080; width: 150'>\n";
					foreach ($answers as $ans)
						{
						if (!in_array($ans, $chosen))
							{
							$choicelist .= "\t\t\t\t\t\t\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
							}
						}
					$choicelist .= "\t\t\t\t\t\t</select>\n";
	
					echo "\t\t\t<table align='left' border='0' cellspacing='5'>\n";
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t</tr>\n";
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td align='left' valign='top' width='200' style='border: solid 1 #111111' bgcolor='silver'>\n";
					echo "\t\t\t\t\t\t$setfont<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"._YOURCHOICES.":</b><br />\n";
					echo "&nbsp;&nbsp;&nbsp;&nbsp;".$choicelist;
					echo "\t\t\t\t\t</td>\n";
					echo "\t\t\t\t\t<td align='left' bgcolor='silver' width='200' style='border: solid 1 #111111'>$setfont\n";
					echo "\t\t\t\t\t\t$setfont<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"._YOURRANKING.":</b><br />\n";
					echo $ranklist;
					echo "\t\t\t\t\t</td>\n";
					echo "\t\t\t\t</tr>\n";
					echo "\t\t\t</table>\n";
					echo "\t\t\t<input type='hidden' name='multi' value='$anscount' />\n";
					echo "\t\t\t<input type='hidden' name='lastfield' value='$multifields' />\n";
					$choicelist="";
					$ranklist="";
					unset($answers);
					break;

				case "M": //MULTIPLE OPTIONS checkbox
					while ($fnames[$i][3] == "M")
						{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i]));
						//echo substr($fnames[$i][0], strlen($fnames[$i][0])-5, 5)."<br />\n";
						if (substr($fnames[$i][0], -5) == "other")
							{
							echo "\t\t\t$setfont<input type='text' name='{$fnames[$i][0]}' value='";
							echo htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n";
							}
						else
							{
							echo "\t\t\t$setfont<input type='checkbox' name='{$fnames[$i][0]}' value='Y'";
							if ($idrow[$fnames[$i][0]] == "Y") {echo " checked";}
							echo " />{$fnames[$i][6]}<br />\n";
							}
						$i++;
						}
					$i--;
					break;
				case "P": //MULTIPLE OPTIONS WITH COMMENTS checkbox + text
					echo "<table>\n";
					while ($fnames[$i][3] == "P")
						{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i]));
						if (substr($fnames[$i][0], -7) == "comment")
							{
							echo "\t\t<td>$setfont<input type='text' name='{$fnames[$i][0]}' size='50' value='";
							echo htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' /></td>\n";
							echo "\t</tr>\n";
							}
						elseif (substr($fnames[$i][0], -5) == "other")
							{
							echo "\t<tr>\n";
							echo "\t\t<td>\n";
							echo "\t\t\t<input type='text' name='{$fnames[$i][0]}' style='width: ";
							echo strlen($idrow[$fnames[$i][0]])."em' value='";
							echo htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n";
							echo "\t\t</td>\n";
							echo "\t\t<td>\n";
							$i++;
							echo "\t\t\t<input type='text' name='{$fnames[$i][0]}' size='50' value='";
							echo htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n";
							echo "\t\t</td>\n";
							echo "\t</tr>\n";
							}
						else
							{
							echo "\t<tr>\n";
							echo "\t\t<td>$setfont<input type='checkbox' name=\"{$fnames[$i][0]}\" value='Y'";
							if ($idrow[$fnames[$i][0]] == "Y") {echo " checked";}
							echo " />{$fnames[$i][6]}</td>\n";
							}
						$i++;
						}
					echo "</table>\n";
					$i--;
					break;
				case "N": //NUMERICAL TEXT
					echo keycontroljs();
					echo "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='{$idrow[$fnames[$i][0]]}' ";
					echo "onKeyPress=\"return goodchars(event,'0123456789.,')\" />\n";					
					break;
				case "S": //SHORT FREE TEXT
					echo "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='";
					echo htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n";
					break;
				case "T": //LONG FREE TEXT
					echo "\t\t\t<textarea rows='5' cols='45' name='{$fnames[$i][0]}'>";
					echo htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "</textarea>\n";
					break;
				case "Y": //YES/NO radio-buttons
					echo "\t\t\t<select name='{$fnames[$i][0]}'>\n";
					echo "\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
					echo ">"._PLEASECHOOSE."..</option>\n";
					echo "\t\t\t\t<option value='Y'";
					if ($idrow[$fnames[$i][0]] == "Y") {echo " selected";}
					echo ">"._YES."</option>\n";
					echo "\t\t\t\t<option value='N'";
					if ($idrow[$fnames[$i][0]] == "N") {echo " selected";}
					echo ">"._NO."</option>\n";
					echo "\t\t\t</select>\n";
					break;
				case "A": //ARRAY (5 POINT CHOICE) radio-buttons
					echo "<table>\n";
					$thisqid=$fnames[$i][7];
					while ($fnames[$i][7] == $thisqid)
						{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i]));
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$fnames[$i][6]}</td>\n";
						echo "\t\t<td>$setfont\n";
						for ($j=1; $j<=5; $j++)
							{
							echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='$j'";
							if ($idrow[$fnames[$i][0]] == $j) {echo " checked";}
							echo " />$j&nbsp;\n";
							}
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						$i++;
						}
					echo "</table>\n";
					$i--;
					break;
				case "B": //ARRAY (10 POINT CHOICE) radio-buttons
					echo "<table>\n";
					$thisqid=$fnames[$i][7];
					while ($fnames[$i][7] == $thisqid)
						{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i]));
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$fnames[$i][6]}</td>\n";
						echo "\t\t<td>$setfont\n";
						for ($j=1; $j<=10; $j++)
							{
							echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='$j'";
							if ($idrow[$fnames[$i][0]] == $j) {echo " checked";}
							echo " />$j&nbsp;\n";
							}
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						$i++;
						}
					$i--;
					echo "</table>\n";
					break;
				case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
					echo "<table>\n";
					$thisqid=$fnames[$i][7];
					while ($fnames[$i][7] == $thisqid)
						{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i]));
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$fnames[$i][6]}</td>\n";
						echo "\t\t<td>$setfont\n";
						echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='Y'";
						if ($idrow[$fnames[$i][0]] == "Y") {echo " checked";}
						echo " />"._YES."&nbsp;\n";
						echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='U'";
						if ($idrow[$fnames[$i][0]] == "U") {echo " checked";}
						echo " />"._UNCERTAIN."&nbsp\n";
						echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='N'";
						if ($idrow[$fnames[$i][0]] == "N") {echo " checked";}
						echo " />"._NO."&nbsp;\n";
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						$i++;
						}
					$i--;
					echo "</table>\n";
					break;
				case "E": //ARRAY (Increase/Same/Decrease) radio-buttons
					echo "<table>\n";
					$thisqid=$fnames[$i][7];
					while ($fnames[$i][7] == $thisqid)
						{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i]));
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$fnames[$i][6]}</td>\n";
						echo "\t\t<td>$setfont\n";
						echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='I'";
						if ($idrow[$fnames[$i][0]] == "Y") {echo " checked";}
						echo " />Increase&nbsp;\n";
						echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='S'";
						if ($idrow[$fnames[$i][0]] == "U") {echo " checked";}
						echo " />Same&nbsp\n";
						echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='D'";
						if ($idrow[$fnames[$i][0]] == "N") {echo " checked";}
						echo " />Decrease&nbsp;\n";
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						$i++;
						}
					$i--;
					echo "</table>\n";
					break;
				case "F": //ARRAY (Flexible Labels)
					echo "<table>\n";
					$thisqid=$fnames[$i][7];
					while ($fnames[$i][7] == $thisqid)
						{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i]));
						echo "\t<tr>\n";
						echo "\t\t<td align='right' valign='top'>$setfont{$fnames[$i][6]}</td>\n";
						$fquery = "SELECT * FROM labels WHERE lid='{$fnames[$i][8]}'";
						$fresult = mysql_query($fquery);
						echo "\t\t<td>$setfont\n";
						while ($frow=mysql_fetch_array($fresult))
							{
							echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='{$frow['code']}'";
							if ($idrow[$fnames[$i][0]] == $frow['code']) {echo " checked";}
							echo " />".$frow['title']."&nbsp;\n";
							}
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						$i++;
						}
					$i--;
					echo "</table>\n";
					break;
				default: //This really only applies to tokens for non-private surveys
					echo "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='";
					echo $idrow[$fnames[$i][0]] . "'>\n";
					break;
				}
			//echo "\t\t\t$setfont{$idrow[$fnames[$i][0]]}\n"; //Debugging info
			//echo $fnames[$i][0], $fnames[$i][1], $fnames[$i][2], "\n"; //Debugging info
			echo "\t\t</td>\n";
			echo "\t</tr>\n";
			echo "\t<tr><td colspan='2' bgcolor='#CCCCCC' height='1'></td></tr>\n";
			}
		}
	echo "</table>\n";
	echo "<table height='1'><tr><td></td></tr></table>\n";
	echo "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr>\n";
	echo "\t\t<td bgcolor='#CCCCCC' align='center'>\n";
	echo "\t\t\t<input type='submit' $btstyle value='"._DE_UPDATE."'>\n";
	echo "\t\t\t<input type='hidden' name='id' value='$id'>\n";
	echo "\t\t\t<input type='hidden' name='sid' value='$sid'>\n";
	echo "\t\t\t<input type='hidden' name='action' value='update'>\n";
	echo "\t\t\t<input type='hidden' name='surveytable' value='{$dbprefix}survey_$sid'>\n";
	echo "\t\t</td>\n";
	echo "\t\t</form>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	}
	

elseif ($action == "update")
	{
	echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._DATAENTRY."</b></td></tr>\n";
	//echo "$surveyoptions";
	echo "\t<tr><td align='center'>\n";
	//echo "<br /><b>Updating data for Survey $sid, tablename $surveytable - Record No $id</b><br /><br />\n";
	$iquery = "SELECT * FROM {$dbprefix}questions, {$dbprefix}groups WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}questions.sid=$sid ORDER BY group_name, title";
	$iresult = mysql_query($iquery);
	
	$updateqr = "UPDATE $surveytable SET \n";
	
	while ($irow = mysql_fetch_array($iresult))
		{
		if ($irow['type'] != "Q" && $irow['type'] != "M" && $irow['type'] != "P" && $irow['type'] != "A" && $irow['type'] != "B" && $irow['type'] != "C" && $irow['type'] != "E" && $irow['type'] != "F" && $irow['type'] != "O" && $irow['type'] != "R")
			{
			$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
			if (get_magic_quotes_gpc())
				//{$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";}
				{$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";}
			else
				{
				if (phpversion() >= "4.3.0")
					{
					$updateqr .= "$fieldname = '" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
					}
				else
					{
					$updateqr .= "$fieldname = '" . mysql_escape_string($_POST[$fieldname]) . "', \n";
					}
				}
			}
		elseif ($irow['type'] == "O")
			{
			$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
			$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";
			$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}comment";
			if (get_magic_quotes_gpc())
				{$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";}
			else
				{
				if (phpversion() >= "4.3.0")
					{
					$updateqr .= "$fieldname = '" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
					}
				else
					{
					$updateqr .= "$fieldname = '" . mysql_escape_string($_POST[$fieldname]) . "', \n";
					}
				}
			}
		elseif ($irow['type'] == "R")
			{
			$i2query = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.qid={$irow['qid']} AND {$dbprefix}questions.sid=$sid ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			$i2result = mysql_query($i2query);
			$i2count = mysql_num_rows($i2result);
			for ($x=1; $x<=$i2count; $x++)
				{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}$x";
				if (get_magic_quotes_gpc())
					{$updateqr .= "$fieldname = '" . $_POST["d$fieldname"] . "', \n";}
				else
					{
					if (phpversion() >= "4.3.0")
						{
						$updateqr .= "$fieldname = '" . mysql_real_escape_string($_POST["d$fieldname"]) . "', \n";
						}
					else
						{
						$updateqr .= "$fieldname = '" . mysql_escape_string($_POST["d$fieldname"]) . "', \n";
						}
					}
				}
			}
		else
			{
			$i2query = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.qid={$irow['qid']} AND {$dbprefix}questions.sid=$sid ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			//echo $i2query;
			$i2result = mysql_query($i2query);
			$otherexists = "";
			while ($i2row = mysql_fetch_array($i2result))
				{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}";
				$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";
				if ($i2row['other'] == "Y") {$otherexists = "Y";}
				if ($irow['type'] == "P")
					{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}comment";
					if (get_magic_quotes_gpc())
						{$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";}
					else
						{
						if (phpversion() >= "4.3.0")
							{
							$updateqr .= "$fieldname = '" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
							}
						else
							{
							$updateqr .= "$fieldname = '" . mysql_escape_string($_POST[$fieldname]) . "', \n";
							}
						}
					}
				}
			if ($otherexists == "Y") 
				{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}other";
				if (get_magic_quotes_gpc())
					{$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";}
				else
					{
					if (phpversion() >= "4.3.0")
						{
						$updateqr .= "$fieldname = '" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
						}
					else
						{
						$updateqr .= "$fieldname = '" . mysql_escape_string($_POST[$fieldname]) . "', \n";
						}
					}
				}
			}	
		}
	$updateqr = substr($updateqr, 0, -3);
	if ($_POST['datestamp']) {$updateqr .= ", datestamp='{$_POST['datestamp']}'";}
	if ($_POST['token']) {$updateqr .= ", token='{$_POST['token']}'";}
	$updateqr .= " WHERE id=$id";
	$updateres = mysql_query($updateqr) or die("Update failed:<br />\n" . mysql_error() . "\n<pre style='text-align: left'>$updateqr</pre>");
	echo "<font color='green'><b>"._SUCCESS."</b></font><br />\n";
	echo _DE_UPDATED."<br /><br />\n";
	echo "<a href='browse.php?sid=$sid&action=id&id=$id'>"._DE_VIEWTHISONE."</a>\n<br />\n";
	echo "<a href='browse.php?sid=$sid&action=all'>"._DE_BROWSE."</a><br />\n";
	//echo "<pre style='text-align: left'>$updateqr</pre>"; //Debugging info
	echo "</td></tr></table>\n";
	echo "</body>\n</html>\n";
	}

elseif ($action == "delete")
	{
	echo "<table height='1'><tr><td></td></tr></table>\n";
	echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._DATAENTRY."</b></td></tr>\n";
	echo "\t<tr height='22' bgcolor='#CCCCCC'><td align='center'>$setfont\n";
	echo "\t\t\t<b>$surveyname</b><br />\n";
	echo "\t\t\t$setfont$surveydesc\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	$delquery = "DELETE FROM $surveytable WHERE id=$id";
	echo "\t<tr>\n";
	$delresult = mysql_query($delquery) or die ("Couldn't delete record $id<br />\n".mysql_error());
	echo "\t\t<td align='center'><br />$setfont<b>"._DE_DELRECORD." (ID: $id)</b><br /><br />\n";
	echo "\t\t\t<a href='browse.php?sid=$sid&action=all'>"._DE_BROWSE."</a>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	echo "</body>\n</html>\n";
	}
	
else
	{
	// PRESENT SURVEY DATAENTRY SCREEN
	echo "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._BROWSERESPONSES."</b></td></tr>\n";
	echo $surveyoptions;
	$desquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$sid";
	$desresult = mysql_query($desquery);
	while ($desrow = mysql_fetch_array($desresult))
		{
		$surveyname = $desrow['short_title'];
		$surveydesc = $desrow['description'];
		$surveyactive = $desrow['active'];
		$surveyprivate = $desrow['private'];
		$surveytable = "{$dbprefix}survey_{$desrow['sid']}";
		$surveydatestamp = $desrow['datestamp'];
		}
	//if ($surveyactive == "Y") {echo "$surveyoptions\n";}
	echo "<table height='1'><tr><td></td></tr></table>\n";
	echo "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='3' height='4'><font size='1' face='verdana' color='white'><b>"._DATAENTRY."</b></td></tr>\n";
	echo "\t<tr bgcolor='#777777'>\n";
	echo "\t\t<td colspan='3' align='center'><font color='white'>\n";
	echo "\t\t\t<b>$surveyname</b>\n";
	echo "\t\t\t<br>$setfont$surveydesc\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<form action='dataentry.php' name='addsurvey' method='post' id='addsurvey'>\n";
	
	if ($surveyprivate == "N") //Give entry field for token id
		{
		echo "\t<tr>\n";
		echo "\t\t<td valign='top' width='1%'></td>\n";
		echo "\t\t<td valign='top' align='right' width='30%'>$setfont<b>"._TOKEN.":</b></font></td>\n";
		echo "\t\t<td valign='top' style='padding-left: 20px'>$setfont\n";
		echo "\t\t\t<input type='text' name='token'>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		}
	if ($surveydatestamp == "Y") //Give datestampentry field
		{
		echo "\t<tr>\n";
		echo "\t\t<td valign='top' width='1%'></td>\n";
		echo "\t\t<td valign='top' align='right' width='30%'>$setfont<b>"._DATESTAMP.":</b></font></td>\n";
		echo "\t\t<td valign='top' style='padding-left: 20px'>$setfont\n";
		echo "\t\t\t<input type='text' name='datestamp' value='$localtimedate'>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		}

	// SURVEY NAME AND DESCRIPTION TO GO HERE
	$degquery = "SELECT * FROM {$dbprefix}groups WHERE sid=$sid ORDER BY group_name";
	$degresult = mysql_query($degquery);
	// GROUP NAME
	while ($degrow = mysql_fetch_array($degresult))
		{
		$deqquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$sid AND gid={$degrow['gid']}";
		$deqresult = mysql_query($deqquery);
		echo "\t<tr>\n";
		echo "\t\t<td colspan='3' align='center' bgcolor='#AAAAAA'>$setfont<b>{$degrow['group_name']}</td>\n";
		echo "\t</tr>\n";
		$gid = $degrow['gid'];
		
		//Alternate bgcolor for different groups
		if ($bgc == "#EEEEEE") {$bgc = "#DDDDDD";}
		else {$bgc = "#EEEEEE";}
		if (!$bgc) {$bgc = "#EEEEEE";}
		
		$deqrows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
		while ($deqrow = mysql_fetch_array($deqresult)) {$deqrows[] = $deqrow;} //Get table output into array
		
		// Perform a case insensitive natural sort on group name then question title of a multidimensional array
		usort($deqrows, 'CompareGroupThenTitle');
		
		foreach ($deqrows as $deqrow)
			{
			//GET ANY CONDITIONS THAT APPLY TO THIS QUESTION
			$explanation = ""; //reset conditions explanation
			$x=0;
			$distinctquery="SELECT DISTINCT cqid, {$dbprefix}questions.title FROM {$dbprefix}conditions, {$dbprefix}questions WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid AND {$dbprefix}conditions.qid={$deqrow['qid']} ORDER BY cqid";
			$distinctresult=mysql_query($distinctquery);
			while ($distinctrow=mysql_fetch_array($distinctresult))
				{
				if ($x > 0) {$explanation .= " <i>"._DE_AND."</i><br />";}
				//$explanation .= "if you answered ";
				$conquery="SELECT cid, cqid, {$dbprefix}questions.title, {$dbprefix}questions.question, value, {$dbprefix}questions.type FROM {$dbprefix}conditions, {$dbprefix}questions WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid AND {$dbprefix}conditions.cqid={$distinctrow['cqid']} AND {$dbprefix}conditions.qid={$deqrow['qid']}";
				$conresult=mysql_query($conquery);
				while ($conrow=mysql_fetch_array($conresult))
					{
					if ($conrow['type'] == "Y")
						{
						switch ($conrow['value'])
							{
							case "Y":
								$conditions[]=_YES;
								break;
							case "N":
								$conditions[]=_NO;
								break;
							}
						}
					if ($conrow['type'] == "G")
						{
						switch ($conrow['value'])
							{
							case "M":
								$conditions[]=_MALE;
								break;
							case "F":
								$conditions[]=_FEMALE;
								break;
							}
						}
					$ansquery="SELECT answer FROM {$dbprefix}answers WHERE qid='{$conrow['cqid']}' AND code='{$conrow['value']}'";
					$ansresult=mysql_query($ansquery);
					while ($ansrow=mysql_fetch_array($ansresult))
						{
						$conditions[]=$ansrow['answer'];
						}
					}
				if (count($conditions) > 1)
					{
					$conanswers = "'".implode("' "._DE_OR." '", $conditions)."'";
					$explanation .= " -" . str_replace("{ANSWER}", $conanswers, _DE_CONDITIONHELP2);
					}
				else
					{
					$explanation .= " -" . str_replace("{ANSWER}", "'{$conditions[0]}'", _DE_CONDITIONHELP2);
					}
				unset($conditions);
				$explanation = str_replace("{QUESTION}", "'{$distinctrow['title']}'", $explanation);
				$x++;
				}

			if ($explanation) 
				{
				$explanation = "<font color='maroon' size='1'>["._DE_CONDITIONHELP1."]<br />$explanation\n";
				echo "<tr bgcolor='$bgc'><td colspan='3'>$setfont$explanation</font></td></tr>\n";
				}

			//END OF GETTING CONDITIONS

			$qid = $deqrow['qid'];
			$fieldname = "$sid"."X"."$gid"."X"."$qid";
			echo "\t<tr bgcolor='$bgc'>\n";
			echo "\t\t<td valign='top' width='1%'>$setfont<font size='1'>{$deqrow['title']}</font></font></td>\n";
			echo "\t\t<td valign='top' align='right' width='30%'>$setfont";
			if ($deqrow['mandatory']=="Y") //question is mandatory
				{
				echo "<font color='red'>*</font>";
				}
			echo "</font><b>{$deqrow['question']}</b></td>\n";
			echo "\t\t<td valign='top' style='padding-left: 20px'>$setfont\n";
			//DIFFERENT TYPES OF DATA FIELD HERE
			if ($deqrow['help'])
				{
				$hh = addcslashes($deqrow['help'], "\0..\37'\""); //Escape ASCII decimal 0-32 plus single and double quotes to make JavaScript happy.
				$hh = htmlspecialchars($hh, ENT_QUOTES); //Change & " ' < > to HTML entities to make HTML happy.
				echo "\t\t\t<img src='./images/help.gif' alt='"._DE_QUESTIONHELP."' align='right' onClick=\"javascript:alert('Question {$deqrow['title']} Help: $hh')\" />\n";
				}
			switch($deqrow['type'])
				{
				case "5": //5 POINT CHOICE radio-buttons
					echo "\t\t\t<select name='$fieldname'>\n";
					echo "\t\t\t\t<option value=''>"._NOANSWER."</option>\n";
					for ($x=1; $x<=5; $x++)
						{
						echo "\t\t\t\t<option value='$x'>$x</option>\n";
						}
					echo "\t\t\t</select>\n";
					break;
				case "D": //DATE
					echo "\t\t\t<input type='text' name='$fieldname' size='10' />\n";
					break;
				case "G": //GENDER drop-down list
					echo "\t\t\t<select name='$fieldname'>\n";
					echo "\t\t\t\t<option selected value=''>"._PLEASECHOOSE."..</option>\n";
					echo "\t\t\t\t<option value='F'>"._FEMALE."</option>\n";
					echo "\t\t\t\t<option value='M'>"._MALE."</option>\n";
					echo "\t\t\t</select>\n";
					break;
				case "Q": //MULTIPLE SHORT TEXT
					$deaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$dearesult = mysql_query($deaquery);
					echo "\t\t\t<table>\n";
					while ($dearow = mysql_fetch_array($dearesult))
						{
						echo "\t\t\t\t<tr><td align='right'>$setfont";
						echo $dearow['answer'];
						echo "</td>\n";
						echo "\t\t\t\t\t<td><input type='text' name='$fieldname{$dearow['code']}'></td>\n";
						echo "\t\t\t\t</tr>\n";
						}
					echo "\t\t\t</table>\n";
					break;
				case "L": //LIST drop-down/radio-button list
					$deaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$dearesult = mysql_query($deaquery);
					echo "\t\t\t<select name='$fieldname'>\n";
					while ($dearow = mysql_fetch_array($dearesult))
						{
						echo "\t\t\t\t<option value='{$dearow['code']}'";
						if ($dearow['default'] == "Y") {echo " selected"; $defexists = "Y";}
						echo ">{$dearow['answer']}</option>\n";
						}
					if (!$defexists) {echo "\t\t\t\t<option selected value=''>"._PLEASECHOOSE."..</option>\n";}
					echo "\t\t\t</select>\n";
					break;
				case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
					$deaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$dearesult = mysql_query($deaquery);
					echo "\t\t\t<select name='$fieldname'>\n";
					while ($dearow = mysql_fetch_array($dearesult))
						{
						echo "\t\t\t\t<option value='{$dearow['code']}'";
						if ($dearow['default'] == "Y") {echo " selected"; $defexists = "Y";}
						echo ">{$dearow['answer']}</option>\n";
						}
					if (!$defexists) {echo "\t\t\t\t<option selected value=''>"._PLEASECHOOSE."..</option>\n";}
					echo "\t\t\t</select>\n";
					echo "\t\t\t<br />"._COMMENT.":<br />\n";
					echo "\t\t\t<textarea cols='40' rows='5' name='$fieldname";
					echo "comment'>$idrow[$i]</textarea>\n";
					break;
				case "R": //RANKING TYPE QUESTION
					$thisqid=$deqrow['qid'];
					$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$thisqid ORDER BY sortorder, answer";
					$ansresult = mysql_query($ansquery);
					$anscount = mysql_num_rows($ansresult);
					echo "\t\t\t<script type='text/javascript'>\n";
					echo "\t\t\t<!--\n";
					echo "\t\t\t\tfunction rankthis_$thisqid(\$code, \$value)\n";
					echo "\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\$index=document.addsurvey.CHOICES_$thisqid.selectedIndex;\n";
					echo "\t\t\t\t\tdocument.addsurvey.CHOICES_$thisqid.selectedIndex=-1;\n";
					echo "\t\t\t\t\tfor (i=1; i<=$anscount; i++)\n";
					echo "\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\t\$b=i;\n";
					echo "\t\t\t\t\t\t\$b += '';\n";
					echo "\t\t\t\t\t\t\$inputname=\"RANK_$thisqid\"+\$b;\n";
					echo "\t\t\t\t\t\t\$hiddenname=\"d$fieldname\"+\$b;\n";
					echo "\t\t\t\t\t\t\$cutname=\"cut_$thisqid\"+i;\n";
					echo "\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='none';\n";
					echo "\t\t\t\t\t\tif (!document.getElementById(\$inputname).value)\n";
					echo "\t\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\t\tdocument.getElementById(\$inputname).value=\$value;\n";
					echo "\t\t\t\t\t\t\tdocument.getElementById(\$hiddenname).value=\$code;\n";
					echo "\t\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='';\n";
					echo "\t\t\t\t\t\t\tfor (var b=document.getElementById('CHOICES_$thisqid').options.length-1; b>=0; b--)\n";
					echo "\t\t\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\t\t\tif (document.getElementById('CHOICES_$thisqid').options[b].value == \$code)\n";
					echo "\t\t\t\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').options[b] = null;\n";
					echo "\t\t\t\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\t\t\ti=$anscount;\n";
					echo "\t\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\tif (document.getElementById('CHOICES_$thisqid').options.length == 0)\n";
					echo "\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').disabled=true;\n";
					echo "\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\t}\n";
					echo "\t\t\t\tfunction deletethis_$thisqid(\$text, \$value, \$name, \$thisname)\n";
					echo "\t\t\t\t\t{\n";
					echo "\t\t\t\t\tvar qid='$thisqid';\n";
					echo "\t\t\t\t\tvar lngth=qid.length+4;\n";
					echo "\t\t\t\t\tvar cutindex=\$thisname.substring(lngth, \$thisname.length);\n";
					echo "\t\t\t\t\tcutindex=parseFloat(cutindex);\n";
					echo "\t\t\t\t\tdocument.getElementById(\$name).value='';\n";
					echo "\t\t\t\t\tdocument.getElementById(\$thisname).style.display='none';\n";
					echo "\t\t\t\t\tif (cutindex > 1)\n";
					echo "\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\t\$cut1name=\"cut_$thisqid\"+(cutindex-1);\n";
					echo "\t\t\t\t\t\t\$cut2name=\"d$fieldname\"+(cutindex);\n";
					echo "\t\t\t\t\t\tdocument.getElementById(\$cut1name).style.display='';\n";
					echo "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n";
					echo "\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\telse\n";
					echo "\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\t\$cut2name=\"d$fieldname\"+(cutindex);\n";
					echo "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n";
					echo "\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\tvar i=document.getElementById('CHOICES_$thisqid').options.length;\n";
					echo "\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').options[i] = new Option(\$text, \$value);\n";
					echo "\t\t\t\t\tif (document.getElementById('CHOICES_$thisqid').options.length > 0)\n";
					echo "\t\t\t\t\t\t{\n";
					echo "\t\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').disabled=false;\n";
					echo "\t\t\t\t\t\t}\n";
					echo "\t\t\t\t\t}\n";
					echo "\t\t\t//-->\n";
					echo "\t\t\t</script>\n";	
					while ($ansrow = mysql_fetch_array($ansresult))
						{
						$answers[] = array($ansrow['code'], $ansrow['answer']);
						}
	
					for ($i=1; $i<=$anscount; $i++)
						{
						$myfname=$fname.$i;
						if ($_SESSION[$myfname])
							{
							$existing++;
							}
						}
					for ($i=1; $i<=$anscount; $i++)
						{
						$myfname = $fname.$i;
						if ($_SESSION[$myfname])
							{
							foreach ($answers as $ans)
								{
								if ($ans[0] == $_SESSION[$myfname])
									{
									$thiscode=$ans[0];
									$thistext=$ans[1];
									}
								}
							}
						$ranklist .= "\t\t\t\t\t\t&nbsp;<font color='#000080'>$i:&nbsp;<input type='text' style='width:150; color: #222222; font-size: 10; background-color: silver' name='RANK$i' id='RANK_$thisqid$i'";
						if ($_SESSION[$myfname])
							{
							$ranklist .= " value='";
							$ranklist .= $thistext;
							$ranklist .= "'";
							}
						$ranklist .= " onFocus=\"this.blur()\">\n";
						$ranklist .= "\t\t\t\t\t\t<input type='hidden' id='d$fieldname$i' name='d$fieldname$i' value='";
						$chosen[]=""; //create array
						if ($_SESSION[$myfname]) 
							{
							$ranklist .= $thiscode;
							$chosen[]=array($thiscode, $thistext);
							}
						$ranklist .= "'>\n";
						$ranklist .= "\t\t\t\t\t\t<img src='./images/cut.gif' title='"._REMOVEITEM."' ";
						if ($i != $existing)
							{
							$ranklist .= "style='display:none'";
							}
						$mfn=$fieldname.$i;
						$ranklist .= " id='cut_$thisqid$i' name='cut$i' onClick=\"deletethis_$thisqid(document.addsurvey.RANK_$thisqid$i.value, document.addsurvey.d$fieldname$i.value, document.addsurvey.RANK_$thisqid$i.id, this.id)\"><br />\n\n";
						}
					
					$choicelist .= "\t\t\t\t\t\t<select size='$anscount' name='CHOICES' id='CHOICES_$thisqid' onClick=\"rankthis_$thisqid(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" style='background-color: #EEEFFF; font-family: verdana; font-size: 12; color: #000080; width: 150'>\n";
					foreach ($answers as $ans)
						{
						if (!in_array($ans, $chosen))
							{
							$choicelist .= "\t\t\t\t\t\t\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
							}
						}
					$choicelist .= "\t\t\t\t\t\t</select>\n";
	
					echo "\t\t\t<table align='left' border='0' cellspacing='5'>\n";
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t</tr>\n";
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td align='left' valign='top' width='200' style='border: solid 1 #111111' bgcolor='silver'>\n";
					echo "\t\t\t\t\t\t$setfont<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"._YOURCHOICES.":</b><br />\n";
					echo "&nbsp;&nbsp;&nbsp;&nbsp;".$choicelist;
					echo "\t\t\t\t\t</td>\n";
					echo "\t\t\t\t\t<td align='left' bgcolor='silver' width='200' style='border: solid 1 #111111'>$setfont\n";
					echo "\t\t\t\t\t\t$setfont<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"._YOURRANKING.":</b><br />\n";
					echo $ranklist;
					echo "\t\t\t\t\t</td>\n";
					echo "\t\t\t\t</tr>\n";
					echo "\t\t\t</table>\n";
					echo "\t\t\t<input type='hidden' name='multi' value='$anscount' />\n";
					echo "\t\t\t<input type='hidden' name='lastfield' value='$multifields' />\n";
					$choicelist="";
					$ranklist="";
					unset($answers);
					break;
				case "M": //MULTIPLE OPTIONS checkbox (Quite tricky really!)
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult = mysql_query($meaquery);
					while ($mearow = mysql_fetch_array($mearesult))
						{
						echo "\t\t\t$setfont<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y'";
						if ($mearow['default'] == "Y") {echo " checked";}
						echo " />{$mearow['answer']}<br />\n";
						}
					if ($deqrow['other'] == "Y")
						{
						echo "\t\t\tOther: <input type='text' name='$fieldname";
						echo "other' />\n";
						}
					break;
				case "P": //MULTIPLE OPTIONS WITH COMMENTS checkbox + text
					echo "<table border='0'>\n";
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult = mysql_query($meaquery);
					while ($mearow = mysql_fetch_array($mearesult))
						{
						echo "\t<tr>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t$setfont<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y'";
						if ($mearow['default'] == "Y") {echo " checked";}
						echo " />{$mearow['answer']}\n";
						echo "\t\t</td>\n";
						//This is the commments field:
						echo "\t\t<td>\n";
						echo "\t\t\t<input type='text' name='$fieldname{$mearow['code']}comment' size='50' />\n";
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						}
					if ($deqrow['other'] == "Y")
						{
						echo "\t<tr>\n";
						echo "\t\t<td style='padding-left: 22px'>$setfont"._OTHER.":</td>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t<input type='text' name='$fieldname"."other' size='50'/>\n";
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						}
					echo "</table>\n";
					break;
				case "N": //NUMERICAL TEXT
					echo keycontroljs();
					echo "\t\t\t<input type='text' name='$fieldname' onKeyPress=\"return goodchars(event,'0123456789.,')\" />";
					break;
				case "S": //SHORT FREE TEXT
					echo "\t\t\t<input type='text' name='$fieldname' />\n";				
					break;
				case "T": //LONG FREE TEXT
					echo "\t\t\t<textarea cols='40' rows='5' name='$fieldname'></textarea>\n";
					break;
				case "Y": //YES/NO radio-buttons
					echo "\t\t\t<select name='$fieldname'>\n";
					echo "\t\t\t\t<option selected value=''>"._PLEASECHOOSE."..</option>\n";
					echo "\t\t\t\t<option value='Y'>"._YES."</option>\n";
					echo "\t\t\t\t<option value='N'>"._NO."</option>\n";
					echo "\t\t\t</select>\n";
					break;
				case "A": //ARRAY (5 POINT CHOICE) radio-buttons
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult = mysql_query($meaquery);
					echo "<table>\n";
					while ($mearow = mysql_fetch_array($mearesult))
						{
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$mearow['answer']}</td>\n";
						echo "\t\t<td>$setfont\n";
						echo "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						echo "\t\t\t\t<option value=''>"._PLEASECHOOSE."..</option>\n";
						for ($i=1; $i<=5; $i++)
							{
							echo "\t\t\t\t<option value='$i'>$i</option>\n";
							}
						echo "\t\t\t</select>\n";
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						}
					echo "</table>\n";
					break;
				case "B": //ARRAY (10 POINT CHOICE) radio-buttons
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult = mysql_query($meaquery);
					echo "<table>\n";
					while ($mearow = mysql_fetch_array($mearesult))
						{
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$mearow['answer']}</td>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						echo "\t\t\t\t<option value=''>"._PLEASECHOOSE."..</option>\n";
						for ($i=1; $i<=10; $i++)
							{
							echo "\t\t\t\t<option value='$i'>$i</option>\n";
							}
						echo "</select>\n";
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						}
					echo "</table>\n";
					break;
				case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult=mysql_query($meaquery);
					echo "<table>\n";
					while ($mearow = mysql_fetch_array($mearesult))
						{
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$mearow['answer']}</td>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						echo "\t\t\t\t<option value=''>"._PLEASECHOOSE."..</option>\n";
						echo "\t\t\t\t<option value='Y'>"._YES."</option>\n";
						echo "\t\t\t\t<option value='U'>"._UNCERTAIN."</option>\n";
						echo "\t\t\t\t<option value='N'>"._NO."</option>\n";
						echo "\t\t\t</select>\n";
						echo "\t\t</td>\n";
						echo "</tr>\n";
						}
					echo "</table>\n";
					break;
				case "E": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult=mysql_query($meaquery) or die ("Couldn't get answers, Type \"E\"<br />$meaquery<br />".mysql_error());
					echo "<table>\n";
					while ($mearow = mysql_fetch_array($mearesult))
						{
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$mearow['answer']}</td>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						echo "\t\t\t\t<option value=''>"._PLEASECHOOSE."..</option>\n";
						echo "\t\t\t\t<option value='I'>"._INCREASE."</option>\n";
						echo "\t\t\t\t<option value='S'>"._SAME."</option>\n";
						echo "\t\t\t\t<option value='D'>"._DECREASE."</option>\n";
						echo "\t\t\t</select>\n";
						echo "\t\t</td>\n";
						echo "</tr>\n";
						}
					echo "</table>\n";
					break;
				case "F": //ARRAY (Flexible Labels)
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult=mysql_query($meaquery) or die ("Couldn't get answers, Type \"E\"<br />$meaquery<br />".mysql_error());
					echo "<table>\n";
					while ($mearow = mysql_fetch_array($mearesult))
						{
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$mearow['answer']}</td>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						echo "\t\t\t\t<option value=''>"._PLEASECHOOSE."..</option>\n";
						$fquery = "SELECT * FROM {$dbprefix}labels WHERE lid={$deqrow['lid']} ORDER BY sortorder, code";
						$fresult = mysql_query($fquery);
						while ($frow = mysql_fetch_array($fresult))
							{
							echo "\t\t\t\t<option value='{$frow['code']}'>".$frow['title']."</option>\n";
							}
						echo "\t\t\t</select>\n";
						echo "\t\t</td>\n";
						echo "</tr>\n";
						}
					echo "</table>\n";
					break;
				}
			//echo " [$sid"."X"."$gid"."X"."$qid]";
			echo "\t\t</td>\n";
			echo "\t</tr>\n";
			echo "\t<tr><td colspan='3' height='2' bgcolor='silver'></td></tr>\n";		
			}		
		}
	
	if ($surveyactive == "Y")
		{
		echo "\t<tr>\n";
		echo "\t\t<td colspan='3' align='center' bgcolor='#CCCCCC'>$setfont\n";
		echo "\t\t\t<input type='submit' value='"._SUBMIT."' $btstyle/>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		}
	elseif ($surveyactive == "N")
		{
		echo "\t<tr>\n";
		echo "\t\t<td colspan='3' align='center' bgcolor='#CCCCCC'>$setfont\n";
		echo "\t\t\t<font color='red'><b>"._DE_NOTACTIVE."\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";	
		}
	else
		{
		echo "</form>\n";
		echo "\t<tr>\n";
		echo "\t\t<td colspan='3' align='center' bgcolor='#CCCCCC'>$setfont\n";
		echo "\t\t\t<font color='red'><b>"._ERROR."</b></font><br />\n";
		echo "\t\t\t"._DE_NOEXIST."<br /><br />\n";
		echo "\t\t\t<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\">\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "</table>";
		echo htmlfooter("instructions.html#Editing and Deleting Responses", "Using PHPSurvey to Edit Responses");
		echo "</body></html>";
		exit;
		}
	echo "\t<input type='hidden' name='action' value='insert' />\n";
	echo "\t<input type='hidden' name='surveytable' value='$surveytable' />\n";
	echo "\t<input type='hidden' name='sid' value='$sid' />\n";
	echo "\t</form>\n";
	echo "</table>\n";
	//echo "</body>\n</html>";
	}
echo "&nbsp;";
echo htmlfooter("instructions.html#Editing and Deleting Responses", "Using PHPSurvey to Edit Responses");
echo "</body>\n</html>";
?>