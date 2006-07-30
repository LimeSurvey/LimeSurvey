<?php
/*
	#############################################################
	# >>> PHPSurveyor       									#
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
//Ensure script is not run directly, avoid path disclosure
if (empty($_GET['sid'])) {die ("Cannot run this script directly");}

$surveyid = $_GET['sid'];

$boxstyle = "style='border-color: #111111; border-width: 1; border-style: solid'";
require_once(dirname(__FILE__).'/../config.php');


if (!isset($tpldir)) {$tpldir=$publicdir."/templates";}
if (!isset($templatedir) || !$templatedir) {$thistpl=$tpldir."/default";} else {$thistpl=$tpldir."/$templatedir";}
if (!is_dir($thistpl)) {$thistpl=$tpldir."/default";}


sendcacheheaders();

DoHeader();
echo "<meta http-equiv='content-script-type' content='text/javascript' />\n"
   . "<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\">\n";
echo "<title>"._("Printable Version of Survey")."</title></head>\n<body>\n";

// PRESENT SURVEY DATAENTRY SCREEN

$desquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
$desresult = db_execute_assoc($desquery);
while ($desrow = $desresult->FetchRow())
	{
	$surveyname = $desrow['short_title'];
	$surveydesc = $desrow['description'];
	$surveyactive = $desrow['active'];
	$surveytable = "{$dbprefix}survey_{$desrow['sid']}";
	$surveyuseexpiry = $desrow['useexpiry'];
	$surveyexpirydate = $desrow['expires'];
	$surveyfaxto = $desrow['faxto'];
	}
if (!isset($surveyfaxto) || !$surveyfaxto and isset($surveyfaxnumber)) 
	{
    $surveyfaxto=$surveyfaxnumber; //Use system fax number if none is set in survey.
	}

echo "<table width='100%' cellspacing='0'>\n";
echo "\t<tr>\n";
echo "\t\t<td colspan='3' align='center'>\n";
echo "\t\t\t<table border='1' style='border-collapse: collapse; border-color: #111111; width: 100%'>\n";
echo "\t\t\t\t<tr><td align='center'>\n";
echo "\t\t\t\t\t<font size='5' face='verdana'><strong>$surveyname</strong></font>\n";
echo "\t\t\t\t\t<br />$setfont$surveydesc</font>\n";
echo "\t\t\t\t</td></tr>\n";
echo "\t\t\t</table>\n";
echo "\t\t</td>\n";
echo "\t</tr>\n";
// SURVEY NAME AND DESCRIPTION TO GO HERE

$fieldmap=createFieldMap($surveyid);

$degquery = "SELECT * FROM {$dbprefix}groups WHERE sid=$surveyid ORDER BY {$dbprefix}groups.sortorder";
$degresult = db_execute_assoc($degquery);
// GROUP NAME
while ($degrow = $degresult->FetchRow())
	{
	$deqquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid={$degrow['gid']} ORDER BY title";
	$deqresult = db_execute_assoc($deqquery);
	$deqrows = array(); //Create an empty array in case FetchRow does not return any rows
	while ($deqrow = $deqresult->FetchRow()) {$deqrows[] = $deqrow;} // Get table output into array
	
	// Perform a case insensitive natural sort on group name then question title of a multidimensional array
	usort($deqrows, 'CompareGroupThenTitle');
	
	echo "\t<tr>\n";
	echo "\t\t<td colspan='3' align='center' bgcolor='#EEEEEE' style='border-width: 1; border-style: double; border-color: #111111'>\n";
	echo "\t\t\t<font size='3' face='verdana'><strong>{$degrow['group_name']}</strong></font>\n";
	if ($degrow['description'])
		{
		echo "\t\t\t<br /><font size='2' face='verdana'>{$degrow['description']}</font>\n";
		}
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	$gid = $degrow['gid'];
	//Alternate bgcolor for different groups
	if (!isset($bgc) || $bgc == "#EEEEEE") {$bgc = "#DDDDDD";}
	else {$bgc = "#EEEEEE";}
	
	foreach ($deqrows as $deqrow)
		{
		//GET ANY CONDITIONS THAT APPLY TO THIS QUESTION
		$explanation = ""; //reset conditions explanation
		$x=0;
		$distinctquery="SELECT DISTINCT cqid, {$dbprefix}questions.title FROM {$dbprefix}conditions, {$dbprefix}questions WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid AND {$dbprefix}conditions.qid={$deqrow['qid']} ORDER BY cqid";
		$distinctresult=db_execute_assoc($distinctquery);
		while ($distinctrow=$distinctresult->FetchRow())
			{
			if ($x > 0) {$explanation .= " <i>"._("and")."</i> ";}
			$explanation .= _("if you answered")." ";
			$conquery="SELECT cid, cqid, {$dbprefix}questions.title,\n"
					 ."{$dbprefix}questions.question, value, {$dbprefix}questions.type,\n"
					 ."{$dbprefix}questions.lid, cfieldname\n"
					 ."FROM {$dbprefix}conditions, {$dbprefix}questions\n"
					 ."WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid\n"
					 ."AND {$dbprefix}conditions.cqid={$distinctrow['cqid']}\n"
					 ."AND {$dbprefix}conditions.qid={$deqrow['qid']}";
			$conresult=db_execute_assoc($conquery) or die("$conquery<br />".htmlspecialchars($connect->ErrorMsg()));
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
							case "Y": $conditions[]=_("Yes"); break;
							case "N": $conditions[]=_("No"); break;
							}
						break;
					case "G":
						switch($conrow['value'])
							{
							case "M": $conditions[]=_("Male"); break;
							case "F": $conditions[]=_("Female"); break;
							} // switch
						break;
					case "A":
					case "B":
						$conditions[]=$conrow['value'];
						break;
					case "C":
						switch($conrow['value'])
							{
							case "Y": $conditions[]=_("Yes"); break;
							case "U": $conditions[]=_("Uncertain"); break;
							case "N": $conditions[]=_("No"); break;
							} // switch
						break;
					case "E":
						switch($conrow['value'])
							{
							case "I": $conditions[]=_("Increase"); break;
							case "D": $conditions[]=_("Decrease"); break;
							case "S": $conditions[]=_("Same"); break;
							}
					case "F":
					case "H":
					case "W":
					case "L":
					 default: 
						$value=substr($conrow['cfieldname'], strpos($conrow['cfieldname'], "X".$conrow['cqid'])+strlen("X".$conrow['cqid']), strlen($conrow['cfieldname']));
						$fquery = "SELECT * FROM {$dbprefix}labels\n"
								. "WHERE lid='{$conrow['lid']}'\n"
								. "AND code='{$conrow['value']}'";
						$fresult=db_execute_assoc($fquery) or die("$fquery<br />".htmlspecialchars($connect->ErrorMsg()));
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
						$ansquery="SELECT answer FROM {$dbprefix}answers WHERE qid='{$conrow['cqid']}' AND code='{$thiscquestion[0]['aid']}'";
						$ansresult=db_execute_assoc($ansquery);
						while ($ansrow=$ansresult->FetchRow())
							{
							$answer_section=" (".$ansrow['answer'].")";
							}
						break;
					default:
						$ansquery="SELECT answer FROM {$dbprefix}answers WHERE qid='{$conrow['cqid']}' AND code='{$conrow['value']}'";
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
				$explanation .=  "'".implode("' "._PS_CON_OR." '", $conditions)."'";	
				}
			else
				{
				$explanation .= "'".$conditions[0]."'";
				}
			unset($conditions);
			$explanation .= " "._("to question")." '".$distinctrow['title']." $answer_section'";
			$x++;
			}
		
		if ($explanation) 
			{
			$explanation = "["._("Only answer this question")." ".$explanation."]";
			echo "<tr bgcolor='$bgc'><td colspan='3'>$setfont$explanation</font></td></tr>\n";
			}
		
		//END OF GETTING CONDITIONS
		
		$qid = $deqrow['qid'];
		$fieldname = "$surveyid"."X"."$gid"."X"."$qid";
		echo "\t<tr bgcolor='$bgc'>\n";
		echo "\t\t<td valign='top' align='left' colspan='3'>\n";
		if ($deqrow['mandatory'] == "Y")
			{
		    echo _("*");
			}
		echo "\t\t\t<strong>$setfont{$deqrow['title']}: {$deqrow['question']}</font></strong>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		//DIFFERENT TYPES OF DATA FIELD HERE
		echo "\t<tr bgcolor='$bgc'>\n";
		echo "\t\t<td width='15%' valign='top'>\n";
		if ($deqrow['help'])
			{
			$hh = $deqrow['help'];
			echo "\t\t\t<table width='100%' border='1'><tr><td align='center'><font size='1'>$hh</font></td></tr></table>\n";

			}
		echo "\t\t</td>\n";
		echo "\t\t<td style='padding-left: 20px'>\n";
		switch($deqrow['type'])
			{
			case "5":  //5 POINT CHOICE
				echo "\t\t\t$setfont<u>"._("Please choose <strong>only one</strong> of the following:")."</u><br /></font>\n";
				for ($i=1; $i<=5; $i++) 
					{
					echo "\t\t\t<input type='checkbox' name='$fieldname' value='$i' readonly='readonly' />$i \n";
					}
				break;
			case "D":  //DATE
				echo "\t\t\t$setfont<u>"._("Please enter a date:")."</u><br />\n";
				echo "\t\t\t<input type='text' $boxstyle name='$fieldname' size='30' value='&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;' readonly='readonly' /></font>\n";
				break;
			case "G":  //GENDER
				echo "\t\t\t$setfont<u>"._("Please choose <strong>only one</strong> of the following:")."</u><br />\n";
				echo "\t\t\t<input type='checkbox' name='$fieldname' value='F' readonly='readonly' />"._("Female")."<br />\n";
				echo "\t\t\t<input type='checkbox' name='$fieldname' value='M' readonly='readonly' />"._("Male")."<br /></font>\n";
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
				echo "\t\t\t$setfont<u>"._("Please choose <strong>only one</strong> of the following:")."</u></font><br />\n";
				$deaquery = "SELECT * FROM {$dbprefix}labels WHERE lid={$deqrow['lid']} ORDER BY sortorder, title";
				$dearesult = db_execute_assoc($deaquery) or die("ERROR: $deaquery<br />\n".htmlspecialchars($connect->ErrorMsg()));
				$deacount=$dearesult->RecordCount();
				if ($deqrow['other'] == "Y") {$deacount++;}
				if ($dcols > 0 && $deacount >= $dcols)
					{
				    $width=sprintf("%0d", 100/$dcols);
					$maxrows=ceil(100*($meacount/$dcols)/100); //Always rounds up to nearest whole number
					$divider="</td>\n <td valign='top' width='$width%' nowrap>";
					$upto=0;
					echo "<table class='question'><tr>\n <td valign='top' width='$width%' nowrap>$setfont";
					while ($dearow = $dearesult->FetchRow())
						{
						if ($upto == $maxrows) 
							{
						    echo $divider;
							$upto=0;
							}
						echo "\t\t\t<input type='checkbox' name='$fieldname' value='{$dearow['code']}' readonly='readonly' />{$dearow['title']}<br />\n";
						$upto++;
						}
					if ($deqrow['other'] == "Y")
						{
					    echo "\t\t\t<input type='checkbox' readonly='readonly' />"._("Other")." <input type='text' size='30' readonly='readonly' /><br />\n";
						}
					echo "</font></td></tr></table>\n";
				    //Let's break the presentation into columns.
					}
				else
					{
					echo $setfont;	
					while ($dearow = $dearesult->FetchRow())
						{
						echo "\t\t\t<input type='checkbox' name='$fieldname' value='{$dearow['code']}' readonly='readonly' />{$dearow['title']}<br />\n";
						}
					if ($deqrow['other'] == "Y")
						{
					    echo "\t\t\t<input type='checkbox' readonly='readonly' />"._("Other")." <input type='text' size='30' readonly='readonly' /><br />\n";
						}
					echo "\t\t\t</font>";	
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
				echo "\t\t\t$setfont<u>"._("Please choose <strong>only one</strong> of the following:")."</u><br /></font>\n";
				$deaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$dearesult = db_execute_assoc($deaquery);
				$deacount=$dearesult->RecordCount();
				if ($deqrow['other'] == "Y") {$deacount++;}
				if ($dcols > 0 && $deacount >= $dcols)
					{
				    $width=sprintf("%0d", 100/$dcols);
					$maxrows=ceil(100*($meacount/$dcols)/100); //Always rounds up to nearest whole number
					$divider=" </td>\n <td valign='top' width='$width%' nowrap>";
					$upto=0;
					echo "<table class='question'><tr>\n <td valign='top' width='$width%' nowrap>";
					while ($dearow = $dearesult->FetchRow())
						{
						if ($upto == $maxrows) 
							{
						    echo $divider;
							$upto=0;
							}
						echo "\t\t\t<input type='checkbox' name='$fieldname' value='{$dearow['code']}' readonly='readonly' />{$dearow['answer']}<br />\n";
						$upto++;
						}
					if ($deqrow['other'] == "Y")
						{
					    echo "\t\t\t<input type='checkbox' readonly='readonly' />"._("Other")." <input type='text' size='30' readonly='readonly' /><br />\n";
						}
					echo "</td></tr></table>\n";
				    //Let's break the presentation into columns.
					}
				else
					{
					while ($dearow = $dearesult->FetchRow())
						{
						echo "\t\t\t<input type='checkbox' name='$fieldname' value='{$dearow['code']}' readonly='readonly' />{$dearow['answer']}<br />\n";
						}
					if ($deqrow['other'] == "Y")
						{
					    echo "\t\t\t<input type='checkbox' readonly='readonly' />"._("Other")." <input type='text' size='30' readonly='readonly' /><br />\n";
						}
					}
				break;
			case "O":  //LIST WITH COMMENT
				echo "\t\t\t$setfont<u>"._("Please choose <strong>only one</strong> of the following:")."</u><br />\n";
				$deaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$dearesult = db_execute_assoc($deaquery);
				while ($dearow = $dearesult->FetchRow())
					{
					echo "\t\t\t<input type='checkbox' name='$fieldname' value='{$dearow['code']}' readonly='readonly' />{$dearow['answer']}<br />\n";
					}
				echo "\t\t\t<u>"._("Make a comment on your choice here:")."</u><br /></font>\n";
				echo "\t\t\t<textarea $boxstyle cols='50' rows='8' name='$fieldname"."comment"."' readonly='readonly'></textarea>\n";
				break;
			case "R":  //RANKING Type Question
				$reaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$rearesult = db_execute_assoc($reaquery) or die ("Couldn't get ranked answers<br />".htmlspecialchars($connect->ErrorMsg()));
				$reacount = $rearesult->RecordCount();
				echo "\t\t\t$setfont<u>"._("Please number each box in order of preference from 1 to")." $reacount</u><br /></font>\n";
				while ($rearow = $rearesult->FetchRow())
					{
					echo "\t\t\t<table cellspacing='1' cellpadding='0'><tr><td width='20' height='20' bgcolor='white' style='border: solid 1 #111111'>&nbsp;</td>\n";
					echo "\t\t\t<td valign='middle'>$setfont{$rearow['answer']}</font></td></tr></table>\n";
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
				echo "\t\t\t$setfont<u>"._("Please choose <strong>all</strong> that apply:")."</u><br /></font>\n";
				$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				$meacount = $mearesult->RecordCount();
				if ($deqrow['other'] == "Y") {$meacount++;}
				if ($dcols > 0 && $meacount >= $dcols)
					{
				    $width=sprintf("%0d", 100/$dcols);
					$maxrows=ceil(100*($meacount/$dcols)/100); //Always rounds up to nearest whole number
					$divider=" </td>\n <td valign='top' width='$width%' nowrap>";
					$upto=0;
					echo "<table class='question'><tr>\n <td valign='top' width='$width%' nowrap>";
					while ($mearow = $mearesult->FetchRow())
						{
						if ($upto == $maxrows) 
							{
						    echo $divider;
							$upto=0;
							}
						echo "\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y' readonly='readonly' />{$mearow['answer']}<br />\n";
						$upto++;
						}
					if ($deqrow['other'] == "Y")
						{
						echo "\t\t\t"._("Other").": <input type='text' $boxstyle size='60' name='$fieldname" . "other' readonly='readonly' />\n";
						}
					echo "</td></tr></table>\n";
					}
				else
					{
					while ($mearow = $mearesult->FetchRow())
						{
					echo "\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y' readonly='readonly' />{$mearow['answer']}<br />\n";
					}
					if ($deqrow['other'] == "Y")
						{
						echo "\t\t\t"._("Other").": <input type='text' $boxstyle size='60' name='$fieldname" . "other' readonly='readonly' />\n";
						}
					}
				break;
			case "J":  //FILE CSV MORE
				echo "\t\t\t$setfont<u>"._("Please choose <strong>all</strong> that apply:")."</u><br />\n";
				$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$mearesult = mysql_query($meaquery);
				while ($mearow = mysql_fetch_array($mearesult))
					{
					echo "\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y' />{$mearow['answer']}<br />\n";
					}
				break;
			case "I":  //FILE CSV ONE
				echo "\t\t\t$setfont<u>"._("Please choose <strong>only one</strong> of the following:").":</u><br />\n";
				$deaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$dearesult = mysql_query($deaquery);
				while ($dearow = mysql_fetch_array($dearesult))
					{
					echo "\t\t\t<input type='checkbox' name='$fieldname' value='{$dearow['code']}' />{$dearow['answer']}<br />\n";
					}
				break;

			case "P":  //MULTIPLE OPTIONS WITH COMMENTS
				$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				echo "\t\t\t$setfont<u>"._("Please choose all that apply and provide a comment:")."</u><br /></font>\n";
				echo "\t\t\t<table border='0'>\n";
				while ($mearow = $mearesult->FetchRow())
					{
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td>$setfont<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y'";
					if ($mearow['default_value'] == "Y") {echo " checked";}
					echo " readonly='readonly' />{$mearow['answer']} </font></td>\n";
					//This is the commments field:
					echo "\t\t\t\t\t<td>$setfont<input type='text' $boxstyle name='$fieldname{$mearow['code']}comment' size='60' readonly='readonly' /></font></td>\n";
					echo "\t\t\t\t</tr>\n";
					}
				echo "\t\t\t</table>\n";
				break;
			case "Q":  //MULTIPLE SHORT TEXT
				echo "\t\t\t$setfont<u>"._("Please write your answer(s) here:")."</u><br /></font>\n";
				$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				echo "\t\t\t<table border='0'>\n";
				while ($mearow = $mearesult->FetchRow())
					{
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td>$setfont{$mearow['answer']}: <input type='text' size='60' name='$fieldname{$mearow['code']}' value=''";
					if ($mearow['default_value'] == "Y") {echo " checked";}
					echo " readonly='readonly' /></font></td>\n";
					echo "\t\t\t\t</tr>\n";
					}
				echo "\t\t\t</table>\n";
				break;
			case "S":  //SHORT TEXT
				echo "\t\t\t$setfont<u>"._("Please write your answer here:")."</u><br /></font>\n";
				echo "\t\t\t<input type='text' name='$fieldname' size='60' $boxstyle readonly='readonly' />\n";
				break;
			case "T":  //LONG TEXT
				echo "\t\t\t$setfont<u>"._("Please write your answer here:")."</u><br /></font>\n";
				echo "\t\t\t<textarea $boxstyle cols='50' rows='8' name='$fieldname' readonly='readonly'></textarea>\n";
				break;
			case "U":  //HUGE TEXT
				echo "\t\t\t$setfont<u>"._("Please write your answer here:")."</u><br /></font>\n";
				echo "\t\t\t<textarea $boxstyle cols='70' rows='50' name='$fieldname' readonly='readonly'></textarea>\n";
 				break;
			case "N":  //NUMERICAL
				echo "\t\t\t$setfont<u>"._("Please write your answer here:")."</u><br />\n";
				echo "\t\t\t<input type='text' size='40' $boxstyle readonly='readonly' /></font>\n";
				break;
			case "Y":  //YES/NO
				echo "\t\t\t$setfont<u>"._("Please choose <strong>only one</strong> of the following:")."</u><br />\n";
				echo "\t\t\t<input type='checkbox' name='$fieldname' value='Y' readonly='readonly' />"._("Yes")."<br />\n";
				echo "\t\t\t<input type='checkbox' name='$fieldname' value='N' readonly='readonly' />"._("No")."<br /></font>\n";
				break;
			case "A":  //ARRAY (5 POINT CHOICE)
				$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				echo "\t\t\t$setfont<u>"._("Please choose the appropriate response for each item:")."</u><br /></font>\n";
				echo "\t\t\t<table>\n";
				while ($mearow = $mearesult->FetchRow())
					{
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td align='left'>$setfont{$mearow['answer']}</font></td>\n";
					echo "\t\t\t\t\t<td>$setfont";
					for ($i=1; $i<=5; $i++)
						{
						echo "\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='$i' readonly='readonly' />$i&nbsp;\n";
						}
					echo "\t\t\t\t\t</font></td>\n";
					echo "\t\t\t\t</tr>\n";
					}
				echo "\t\t\t</table>\n";
				break;
			case "B":  //ARRAY (10 POINT CHOICE)
				$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				echo "\t\t\t$setfont<u>"._("Please choose the appropriate response for each item:")."</u><br /></font>";
				echo "\t\t\t<table border='0'>\n";
				while ($mearow = $mearesult->FetchRow())
					{
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td align='left'>$setfont{$mearow['answer']}</font></td>\n";
					echo "\t\t\t\t\t<td>$setfont\n";
					for ($i=1; $i<=10; $i++)
						{
						echo "\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='$i' readonly='readonly' />$i&nbsp;\n";
						}
					echo "\t\t\t\t\t</font></td>\n";
					echo "\t\t\t\t</tr>\n";
					}
				echo "\t\t\t</table>\n";
				break;
			case "C":  //ARRAY (YES/UNCERTAIN/NO)
				$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				echo "\t\t\t$setfont<u>"._("Please choose the appropriate response for each item:")."</u><br /></font>\n";
				echo "\t\t\t<table>\n";
				while ($mearow = $mearesult->FetchRow())
					{
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td align='left'>$setfont{$mearow['answer']}</font></td>\n";
					echo "\t\t\t\t\t<td>$setfont\n";
					echo "\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y' readonly='readonly' />"._("Yes")."&nbsp;\n";
					echo "\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='U' readonly='readonly' />"._("Uncertain")."&nbsp;\n";
					echo "\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='N' readonly='readonly' />"._("No")."&nbsp;\n";
					echo "\t\t\t\t\t</font></td>\n";
					echo "\t\t\t\t</tr>\n";
					}
				echo "\t\t\t</table>\n";
				break;
			case "E":  //ARRAY (Increase/Same/Decrease)
				$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				echo "\t\t\t$setfont<u>"._("Please choose the appropriate response for each item:")."</u><br /></font>\n";
				echo "\t\t\t<table>\n";
				while ($mearow = $mearesult->FetchRow())
					{
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td align='left'>$setfont{$mearow['answer']}</font></td>\n";
					echo "\t\t\t\t\t<td>$setfont\n";
					echo "\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='I' readonly='readonly' />"._("Increase")."&nbsp;\n";
					echo "\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='S' readonly='readonly' />"._("Same")."&nbsp;\n";
					echo "\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='D' readonly='readonly' />"._("Decrease")."&nbsp;\n";
					echo "\t\t\t\t\t</font></td>\n";
					echo "\t\t\t\t</tr>\n";
					}
				echo "\t\t\t</table>\n";
				break;
			case "F": //ARRAY (Flexible Labels)
				//$headstyle="style='border-left-style: solid; border-left-width: 1px; border-left-color: #AAAAAA'";
				$headstyle="style='padding-left: 20px; padding-right: 7px'";
				$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				echo "\t\t\t$setfont<u>"._("Please choose the appropriate response for each item:")."</u><br /></font>\n";
				echo "\t\t\t<table align='left' cellspacing='0'><tr><td></td>\n";
				$fquery = "SELECT * FROM {$dbprefix}labels WHERE lid='{$deqrow['lid']}' ORDER BY sortorder, code";
				$fresult = db_execute_assoc($fquery);
				$fcount = $fresult->RecordCount();
				$fwidth = "120";
				$i=0;
				while ($frow = $fresult->FetchRow())
					{
					echo "\t\t\t\t\t\t<td align='center' valign='bottom' $headstyle><font size='1'>{$frow['title']}</font></td>\n";
					$i++;
					}
				echo "\t\t\t\t\t\t</tr>\n";
				while ($mearow = $mearesult->FetchRow())
					{
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td align='left'>$setfont{$mearow['answer']}</font></td>\n";
					//echo "\t\t\t\t\t<td>";
					for ($i=1; $i<=$fcount; $i++)
						{
						
						echo "\t\t\t\t\t<td align='center'";
						if ($i > 1) {echo " $headstyle";}
						echo ">$setfont\n";
						echo "\t\t\t\t\t\t<input type='checkbox' readonly='readonly' /></font>\n";
						echo "\t\t\t\t\t</td>\n";
						}
					//echo "\t\t\t\t\t</tr></table></td>\n";
					echo "\t\t\t\t</tr>\n";
					}
				echo "\t\t\t</table>\n";
				break;
			case "H": //ARRAY (Flexible Labels) by Column
				//$headstyle="style='border-left-style: solid; border-left-width: 1px; border-left-color: #AAAAAA'";
				$headstyle="style='padding-left: 20px; padding-right: 7px'";
				$fquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
				$fresult = db_execute_assoc($fquery);
				echo "\t\t\t$setfont<u>"._("Please choose the appropriate response for each item:")."</u><br /></font>\n";
				echo "\t\t\t<table align='left' cellspacing='0'><tr><td></td>\n";
				$meaquery = "SELECT * FROM {$dbprefix}labels WHERE lid='{$deqrow['lid']}' ORDER BY sortorder, code";
				$mearesult = db_execute_assoc($meaquery);
				$fcount = $fresult->RecordCount();
				$fwidth = "120";
				$i=0;
				while ($frow = $fresult->FetchRow())
					{
					echo "\t\t\t\t\t<td align='center'>$setfont{$frow['answer']}</font></td>\n";
					$i++;
					}
				echo "\t\t\t\t\t\t</tr>\n";
				while ($mearow = $mearesult->FetchRow())
					{
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t\t<td align='left' valign='bottom' $headstyle><font size='1'>{$mearow['title']}</font></td>\n";
					//echo "\t\t\t\t\t<td>";
					for ($i=1; $i<=$fcount; $i++)
						{
						
						echo "\t\t\t\t\t<td align='center'";
						if ($i > 1) {echo " $headstyle";}
						echo ">$setfont\n";
						echo "\t\t\t\t\t\t<input type='checkbox' readonly='readonly' /></font>\n";
						echo "\t\t\t\t\t</td>\n";
						}
					//echo "\t\t\t\t\t</tr></table></td>\n";
					echo "\t\t\t\t</tr>\n";
					}
				echo "\t\t\t</table>\n";
				break;
			}
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr><td height='3' colspan='3'><hr noshade size='1'></td></tr>\n";
		}
	}
echo "\t<tr>\n";
echo "\t\t<td colspan='3' align='center'>\n";
echo "\t\t\t<table width='100%' border='1' style='border-collapse: collapse'>\n";
echo "\t\t\t\t<tr>\n";
echo "\t\t\t\t\t<td align='center'>\n";
echo "\t\t\t\t\t\t$setfont<strong>"._("Submit Your Survey.")."</strong></font><br />\n";
echo "\t\t\t\t\t\t"._("Thank you for completing this survey.")." "._("Please fax your completed survey to:")." $surveyfaxto";
if ($surveyuseexpiry=="Y")
	{
	echo " by $surveyexpirydate";
	}
echo ".\n";
echo "\t\t\t\t\t</td>\n";
echo "\t\t\t\t</tr>\n";
echo "\t\t\t</table>\n";
echo "\t\t</td>\n";
echo "\t</tr>\n";
echo "</table>\n";
echo "</body>\n</html>";

?>
