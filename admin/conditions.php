<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA
 	# > Date: 	 19 April 2003								#
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

include("config.php");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0

echo $htmlheader;
echo "<table width='100%' border='0' bgcolor='#555555'>\n";
echo "\t<tr><td align='center'><font color='white'><b>Condition Designer</b></td></tr>\n";
echo "</table>\n";


if (!$_GET['sid'] && !$_POST['sid'])
	{
	echo "<br /><center>$setfont<b>No survey identification. You must not run this script directly.</b></font></center>\n";
	echo "</body></html>\n";
	exit;
	}
if (!$_GET['qid'] && !$_POST['qid'])
	{
	echo "<br /><center>$setfont<b>No question identification. You must not run this script directly.</b></font></center>\n";
	echo "</body></html>\n";
	exit;
	}

//ADD NEW ENTRY IF THIS IS AN ADD
if ($_POST['action'] == "insertcondition")
	{
	$query = "INSERT INTO conditions (qid, cqid, cfieldname, value) VALUES "
		   . "('{$_POST['qid']}', '{$_POST['cqid']}', '{$_POST['cquestions']}', '{$_POST['canswers']}')";
	$result = mysql_query($query) or die ("Couldn't insert new condition<br />$query<br />".mysql_error());
	}
//DELETE ENTRY IF THIS IS DELETE
if ($_POST['action'] == "delete")
	{
	$query = "DELETE FROM conditions WHERE cid={$_POST['cid']}";
	$result = mysql_query($query) or die ("Couldn't delete condition<br />$query<br />".mysql_error());
	}

unset($cquestions);
unset($canswers);


// *******************************************************************
// ** ADD FORM
// *******************************************************************
//1: Get information for this question
$qid=$_GET['qid']; if (!$qid) {$qid=$_POST['qid'];}
$sid=$_GET['sid']; if (!$sid) {$sid=$_POST['sid'];}

$query = "SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND qid=$qid";
$result = mysql_query($query) or die ("Couldn't get information for question $qid<br />$query<br />".mysql_error());
while ($rows=mysql_fetch_array($result))
	{
	$questiongroupname=$rows['group_name'];
	$questiontitle=$rows['title'];
	$questiontext=$rows['question'];
	$questiontype=$rows['type'];
	}

//2: Get all other questions that occur before this question that are pre-determined answer types
$query = "SELECT questions.qid, questions.sid, questions.gid, questions.question, questions.type FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid=$sid AND (groups.group_name < '$questiongroupname' OR (groups.group_name = '$questiongroupname' AND questions.title < '$questiontitle')) AND questions.type NOT IN ('S', 'D', 'T') ORDER BY CONCAT(groups.group_name, questions.title)";
echo "<!-- DEBUG: \n";
echo $query;
echo "\n-->\n";
$result=mysql_query($query) or die ("Couldn't get other questions.<br />$query<br />".mysql_error());
$questionscount=mysql_num_rows($result);
//conditionstable: 0=qid, 1=cqid, 2=cfieldname, 3=description, 4=method, 5=value
//$cquestions: 0=description, 1=cqid, 2=type, 3=fieldname
//$canswers: 0=fieldname, 1=code, 2=answer

if ($questionscount > 0)
	{
	$X="X";
	while ($rows=mysql_fetch_array($result))
		{
		if (strlen($rows['question']) > 30) {$shortquestion=substr($rows['question'], 0, 30).".. ";}
		else {$shortquestion=$rows['question'];}
		if ($rows['type'] == "A" || $rows['type'] == "B" || $rows['type'] == "C")
			{
			$aquery="SELECT * FROM answers WHERE qid={$rows['qid']}";
			$aresult=mysql_query($aquery) or die ("Couldn't get answers to Array questions<br />$aquery<br />".mysql_error());
			while ($arows = mysql_fetch_array($aresult))
				{
				if (strlen($arows['answer']) > 15) {$shortanswer=substr($arows['answer'], 0, 15).".. ";}
				else {$shortanswer = $arows['answer'];}
				$cquestions[]=array("$shortquestion [$shortanswer]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']);
				switch ($rows['type'])
					{
					case "A":
						for ($i=1; $i<=5; $i++)
							{
							$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $i, $i);
							}
						break;
					case "B":
						for ($i=1; $i<=10; $i++)
							{
							$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $i, $i);
							}
						break;
					case "C":
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "Y", "Yes");
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "U", "Uncertain");
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "N", "No");
						break;
					}
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "", "No Answer");
				}
			}
		elseif ($rows['type'] == "R")
			{
			$aquery="SELECT * FROM answers WHERE qid={$rows['qid']}";
			$aresult=mysql_query($aquery) or die ("Couldn't get answers to Ranking question<br />$aquery<br />".mysql_error());
			$acount=mysql_num_rows($aresult);
			while ($arow=mysql_fetch_array($aresult))
				{
				$quicky[]=array($arow['code'], $arow['answer']);
				}
			for ($i=1; $i<=$acount; $i++)
				{
				$cquestions[]=array("$shortquestion [RANK $i]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i);
				foreach ($quicky as $qck)
					{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i, $qck[0], $qck[1]);
					}
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i, "", "No Answer");
				}
			}
		else
			{
			$cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid']);
			switch ($rows['type'])
				{
				case "Y":
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "Y", "Yes");
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "N", "No");
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "", "No Answer");
					break;
				case "G":
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "F", "Female");
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "M", "Male");
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "", "No Answer");
					break;
				case "5":
					for ($i=1; $i<=5; $i++)
						{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], $i, $i);
						}
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "", "No Answer");
					break;
				default:
					$aquery="SELECT * FROM answers WHERE qid={$rows['qid']}";
					$aresult=mysql_query($aquery) or die ("Couldn't get answers to Ranking question<br />$aquery<br />".mysql_error());
					while ($arows=mysql_fetch_array($aresult))
						{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], $arows['code'], $arows['answer']);
						}
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "", "No Answer");
					break;
				}
			}
		}
	}

//JAVASCRIPT TO SHOW MATCHING ANSWERS TO SELECTED QUESTION
echo "<script type='text/javascript'>\n";
echo "<!--\n";
echo "\tvar Fieldnames = new Array();\n";
echo "\tvar Codes = new Array();\n";
echo "\tvar Answers = new Array();\n";
echo "\tvar QFieldnames = new Array();\n";
echo "\tvar Qcqids = new Array();\n";
$jn=0;
foreach($canswers as $can)
	{
	echo "\t\tFieldnames[$jn]='$can[0]';\n";
	echo "\t\tCodes[$jn]='$can[1]';\n";
	echo "\t\tAnswers[$jn]='$can[2]';\n";
	$jn++;
	}
$jn=0;
foreach ($cquestions as $cqn)
	{
	echo "\t\tQFieldnames[$jn]='$cqn[3]';\n";
	echo "\t\tQcqids[$jn]='$cqn[1]';\n";
	$jn++;
	}
echo "\n";
echo "\tfunction clearAnswers()\n";
echo "\t\t{\n";
echo "\t\t\tfor (var i=document.getElementById('canswers').options.length-1; i>=0; i--)\n";
echo "\t\t\t\t{\n";
//echo "alert(i);\n";
echo "\t\t\t\t\tdocument.getElementById('canswers').options[i] = null;\n";
echo "\t\t\t\t}\n";
echo "\t\t}\n";

echo "\tfunction getAnswers(fname)\n";
echo "\t\t{\n";
//echo "\t\talert(getElementById('canswers').options.length)\n";
//echo "\t\t\t{\n";
echo "\t\t\tfor (var i=document.getElementById('canswers').options.length-1; i>=0; i--)\n";
echo "\t\t\t\t{\n";
//echo "alert(i);\n";
echo "\t\t\t\t\tdocument.getElementById('canswers').options[i] = null;\n";
echo "\t\t\t\t}\n";
//echo "\t\t\t}\n";
//echo "\t\t\talert(fname);\n";
echo "\t\t\tvar Keys = new Array();\n";
echo "\t\t\tfor (var i=0;i<Fieldnames.length;i++)\n";
echo "\t\t\t\t{\n";
echo "\t\t\t\tif (Fieldnames[i] == fname)\n";
echo "\t\t\t\t\t{\n";
echo "\t\t\t\t\tKeys[Keys.length]=i;\n";
echo "\t\t\t\t\t}\n";
echo "\t\t\t\t}\n";
echo "\t\t\tfor (var i=0;i<QFieldnames.length;i++)\n";
echo "\t\t\t\t{\n";
echo "\t\t\t\tif (QFieldnames[i] == fname)\n";
echo "\t\t\t\t\t{\n";
echo "\t\t\t\t\tdocument.getElementById('cqid').value=Qcqids[i];\n";
echo "\t\t\t\t\t}\n";
echo "\t\t\t\t}\n";
//echo "\t\t\talert(Keys.length);\n";
echo "\t\t\tfor (var i=0;i<Keys.length;i++)\n";
echo "\t\t\t\t{\n";
//echo "\t\t\t\talert(Answers[Keys[i]]);\n";
echo "\t\t\t\tdocument.getElementById('canswers').options[document.getElementById('canswers').options.length] = new Option(Answers[Keys[i]], Codes[Keys[i]]);\n";
echo "\t\t\t\t}\n";
echo "\t\t}\n";
echo "//-->\n";
echo "</script>\n";	

//SHOW FORM TO CREATE IT!
echo "<table width='98%' align='center' cellspacing='0' cellpadding='0'>\n";
echo "\t<tr bgcolor='#CDCDCD'>\n";
echo "\t\t<td colspan='3' align='center'>\n";
echo "\t\t\t$setfont<b>Only show question $questiontitle<img src='speaker.jpg' alt='$questiontext' onClick=\"alert('$questiontext')\"> if:</b></font>\n";
echo "\t\t</td>\n";
echo "\t</tr>\n";

//3: Get other conditions currently set for this question
$query = "SELECT conditions.cid, conditions.cqid, conditions.cfieldname, conditions.value, questions.type FROM conditions, questions WHERE conditions.cqid=questions.qid AND conditions.qid=$qid ORDER BY conditions.cfieldname";
$result = mysql_query($query) or die ("Couldn't get other conditions for question $qid<br />$query<br />".mysql_error());
$conditionscount=mysql_num_rows($result);
echo $conditionscount;
if ($conditionscount > 0)
	{
	while ($rows=mysql_fetch_array($result))
		{
		if ($currentfield && $currentfield != $rows['cfieldname'])
			{
			echo "\t\t\t\t<tr bgcolor='#FFFFFF'>\n";
			echo "\t\t\t\t\t<td colspan='3' align='center'>\n";
			echo "$setfont<font size='1'>AND</font></font>";
			}
		elseif ($currentfield)
			{
			echo "\t\t\t\t<tr bgcolor='#EFEFEF'>\n";
			echo "\t\t\t\t\t<td colspan='3' align='center'>\n";
			echo "$setfont<font size='1'>OR</font></font>";
			}
		echo "\t\t\t\t\t</td>\n";
		echo "\t\t\t\t</tr>\n";
		echo "\t<tr bgcolor='#EFEFEF'>\n";
		echo "\t<form name='del{$rows['cid']}' id='del{$rows['cid']}' method='post' action='{$_SERVER['PHP_SELF']}'>\n";
		echo "\t\t<td align='right' valign='middle'><font size='1' face='verdana'>\n";
		//BUILD FIELDNAME?
		foreach ($cquestions as $cqn)
			{
			if ($cqn[3] == $rows['cfieldname'])
				{
				echo "\t\t\t$cqn[0] (qid{$rows['cqid']})\n";
				}
			else
				{
				//echo "\t\t\t<font color='red'>ERROR: Delete this condition. It is out of order.</font>\n";
				}
			}
		echo "\t\t</font></td>\n";
		echo "\t\t<td align='center' valign='middle'><font size='1'>equals</font></td>";
		echo "\t\t<td>\n";
		echo "\t\t\t<table border='0' cellpadding='0' cellspacing='0' width='99%'>\n";
		echo "\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t<td align='left' valign='middle'>\n";
		echo "\t\t\t\t\t\t<font size='1' face='verdana'>\n";
		foreach ($canswers as $can)
			{
			//echo $rows['cfieldname'] . "- $can[0]<br />";
			//echo $can[1];
			if ($can[0] == $rows['cfieldname'] && $can[1] == $rows['value'])
				{
				echo "\t\t\t\t\t\t$can[2] ($can[1])\n";
				}
			}
		echo "\t\t\t\t\t</td>\n";
		echo "\t\t\t\t\t<td align='right' valign='middle'>\n";
		echo "\t\t\t\t\t\t<input type='submit' value='Del' style='font-face: verdana; font-size: 8; height:13' align='right'>\n";
		echo "\t\t\t\t\t</td>\n";
		echo "\t\t\t\t</tr>\n";
		echo "\t\t\t</table>\n";
		echo "\t\t</td>\n";
		echo "\t<input type='hidden' name='action' value='delete'>\n";
		echo "\t<input type='hidden' name='cid' value='{$rows['cid']}'>\n";
		echo "\t<input type='hidden' name='sid' value='$sid'>\n";
		echo "\t<input type='hidden' name='qid' value='$qid'>\n";
		echo "\t</form>\n";
		echo "\t</tr>\n";
		$currentfield=$rows['cfieldname'];
		}
	echo "\t<tr>\n";
	echo "\t\t<td colspan='3' height='3'>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	}

echo "\t<tr bgcolor='#CDCDCD'>\n";
echo "\t\t<td colspan='3' align='center'>\n";
echo "\t\t\t$setfont<b>New Condition:</b></font>\n";
echo "\t\t</td>\n";
echo "\t</tr>\n";
echo "\t<tr bgcolor='#EFEFEF'>\n";
echo "\t\t<th width='40%'>\n";
echo "\t\t\t$setfont<b>Question</b></font>\n";
echo "\t\t</th>\n";
echo "\t\t<th width='20%'>\n";
echo "\t\t</th>\n";
echo "\t\t<th width='40%'>\n";
echo "\t\t\t$setfont<b>Answer</b></font>\n";
echo "\t\t</th>\n";
echo "\t</tr>\n";
echo "<form action='{$_SERVER['PHP_SELF']}' name='addconditions' id='addconditions' method='post'>\n";
echo "\t<tr>\n";
echo "\t\t<td valign='top'>\n";
echo "\t\t\t<select onClick=\"getAnswers(this.options[this.selectedIndex].value)\" name='cquestions' id='cquestions' style='font-face:verdana; font-size:10; width:220' size='5'>\n";
foreach ($cquestions as $cqn)
	{
	echo "\t\t\t\t<option value='$cqn[3]'>$cqn[0]</option>\n";
	}
echo "\t\t\t</select>\n";
echo "\t\t</td>\n";
echo "\t\t<td align='center'>\n";
//echo "\t\t\t<select name='method' id='method' style='font-face:verdana; font-size:10'>\n";
//echo "\t\t\t\t<option value='='>Equals</option>\n";
//echo "\t\t\t\t<option value='!'>Does not equal</option>\n";
//echo "\t\t\t</select>\n";
echo "\t\t\tEquals\n";
echo "\t\t</td>\n";
echo "\t\t<td valign='top'>\n";
echo "\t\t\t<select name='canswers' id='canswers' style='font-face:verdana; font-size:10; width:220' size='5'>\n";

echo "\t\t\t</select>\n";
echo "\t</tr>\n";
echo "\t<tr>\n";
echo "\t\t<td colspan='3' align='center'>\n";
echo "\t\t\t<input type='reset' value='Clear' onClick=\"clearAnswers()\" $btstyle />\n";
echo "\t\t\t<input type='submit' value='Add Condition' $btstyle />\n";
echo "\t\t</td>\n";
echo "\t</tr>\n";
echo "<input type='hidden' name='sid' value='$sid' />\n";
echo "<input type='hidden' name='qid' value='$qid' />\n";
echo "<input type='hidden' name='action' value='insertcondition' />\n";
echo "<input type='hidden' name='cqid' id='cqid' value='' />\n";
echo "</form>\n";
echo "\t<tr bgcolor='#CDCDCD'>\n";
echo "\t\t<td height='5' colspan='3'>\n";
echo "\t\t</td>\n";
echo "\t</tr>\n";
echo "\t<tr>\n";
echo "\t\t<td colspan='3' align='center'>\n";
echo "\t\t\t<input type='submit' value='Close Conditions Window' onClick=\"window.close()\" $btstyle>\n";
echo "\t\t</td>\n";
echo "\t</tr>\n";
echo "</table>\n";

echo htmlfooter("instructions.html#conditions", "Using PHPSurveyor's Conditions");

?>