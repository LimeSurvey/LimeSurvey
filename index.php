<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA					#
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


session_start();
ini_set("session.bug_compat_warn", 0); //Turn this off until first "Next" warning is worked out
require_once("./admin/config.php");

if (!isset($sid)) {$sid=returnglobal('sid');}
//This next line is for security reasons. It ensures that the $sid value is never anything but a number.
if (phpversion() >= '4.2.0') {settype($sid, "int");} else {settype($sid, "integer");} 

//DEFAULT SETTINGS FOR TEMPLATES
if (!$publicdir) {$publicdir=".";}
$tpldir="$publicdir/templates";

//CHECK FOR REQUIRED INFORMATION (sid)
if (!$sid)
	{
	//A nice exit
	sendcacheheaders();
	echo "<html>\n";
	$output=file("$tpldir/default/startpage.pstpl");
	foreach($output as $op)
		{
		echo templatereplace($op);
		}
	echo "\t\t<center><br />\n"
		."\t\t\t<font color='RED'><b>ERROR</b></font><br />\n"
		."\t\t\tYou have not provided a survey identification number.<br /><br />\n"
		."\t\t\tPlease contact <i>$siteadminname</i> at <i>$siteadminemail</i> for further assistance.<br /><br />\n";
	$output=file("$tpldir/default/endpage.pstpl");
	foreach($output as $op)
		{
		echo templatereplace($op);
		}
	echo "</html>\n";
	exit;
	}

//GET BASIC INFORMATION ABOUT THIS SURVEY
if (!isset($token)) {$token=returnglobal('token');}
$query="SELECT * FROM {$dbprefix}surveys WHERE sid=$sid";
$result=mysql_query($query) or die ("Couldn't access surveys<br />$query<br />".mysql_error());
$surveyexists=mysql_num_rows($result);
while ($row=mysql_fetch_array($result))
	{
	$surveyname = $row['short_title'];
	$surveydescription = $row['description'];
	$surveywelcome = $row['welcome'];
	$templatedir = $row['template'];
	$surveyadminname = $row['admin'];
	$surveyadminemail = $row['adminemail'];
	$surveyactive = $row['active'];
	$surveyexpiry = $row['expires'];
	$surveyprivate = $row['private'];
	$surveytable = "{$dbprefix}survey_{$row['sid']}";
	$surveyurl = $row['url'];
	$surveyurldescrip = $row['urldescrip'];
	$surveyformat = $row['format'];
	$surveylanguage = $row['language'];
	$surveydatestamp = $row['datestamp'];
	$surveyusecookie = $row['usecookie'];
	$sendnotification = $row['notification'];
	}

if (!$surveyadminemail) {$surveyadminemail=$siteadminemail;}
if (!$surveyadminname) {$surveyadminname=$siteadminname;}
//SEE IF SURVEY USES TOKENS
$i = 0; $tokensexist = 0;
$tresult = @mysql_list_tables($databasename) or die ("Error getting tokens<br />".mysql_error());
while($tbl = @mysql_tablename($tresult, $i++))
	{
	if ($tbl == "{$dbprefix}tokens_$sid") {$tokensexist = 1;}
	}

//SET THE TEMPLATE DIRECTORY
if (!$templatedir) {$thistpl=$tpldir."/default";} else {$thistpl=$tpldir."/$templatedir";}
if (!is_dir($thistpl)) {$thistpl=$tpldir."/default";}

//REQUIRE THE LANGUAGE FILE
$langdir="$publicdir/lang";
$langfilename="$langdir/$surveylanguage.lang.php";
//Use the default language file if the $surveylanguage file doesn't exist
if (!is_file($langfilename)) {$langfilename="$langdir/$defaultlang.lang.php";}
require($langfilename);

//MAKE SURE SURVEY HASN'T EXPIRED
if ($surveyexpiry < date("Y-m-d") && $surveyexpiry != "0000-00-00")
	{
	sendcacheheaders();
	echo "<html>\n";
	$output=file("$tpldir/default/startpage.pstpl");
	foreach ($output as $op)
		{
		echo templatereplace($op);
		}
	echo "\t\t<center><br />\n"
		."\t\t\t"._SURVEYEXPIRED."<br /><br />\n"
		."\t\t\t"._CONTACT1." <i>$surveyadminname</i> (<i>$surveyadminemail</i>) "
		._CONTACT2."<br /><br />\n";
	$output=file("$tpldir/default/endpage.pstpl");
	foreach ($output as $op)
		{
		echo templatereplace($op);
		}
	echo "</html>\n";
	exit;
	}
	
//CHECK FOR PREVIOUSLY COMPLETED COOKIE
//If cookies are being used, and this survey has been completed, a cookie called "PHPSID[sid]STATUS" will exist (ie: SID6STATUS) and will have a value of "COMPLETE"
$cookiename="PHPSID".returnglobal('sid')."STATUS";
if (isset($_COOKIE[$cookiename]) && $_COOKIE[$cookiename] == "COMPLETE" && $surveyusecookie == "Y" && $tokensexist != 1 && (!isset($_GET['newtest']) || $_GET['newtest'] != "Y"))
	{
	sendcacheheaders();
	echo "<html>\n";
	$output=file("$tpldir/default/startpage.pstpl");
	foreach($output as $op)
		{
		echo templatereplace($op);
		}
	echo "\t\t<center><br />\n"
		."\t\t\t<font color='RED'><b>"._ERROR_PS."</b></font><br />\n"
		."\t\t\t"._SURVEYCOMPLETE."<br /><br />\n"
		."\t\t\t"._CONTACT1." <i>$surveyadminname</i> (<i>$surveyadminemail</i>) "
		._CONTACT2."<br /><br />\n";
	$output=file("$tpldir/default/endpage.pstpl");
	foreach($output as $op)
		{
		echo templatereplace($op);
		}
	echo "</html>\n";
	exit;
	}

//CHECK IF SURVEY ID DETAILS HAVE CHANGED
if (isset($_SESSION['oldsid'])) {$oldsid=$_SESSION['oldsid'];}

if (!isset($oldsid)) {$_SESSION['oldsid'] = $sid;}

if (isset($oldsid) && $oldsid && $oldsid != $sid)
	{
	session_unset();
	$_SESSION['oldsid']=$sid;
	}

//CLEAR SESSION IF REQUESTED
if (isset($_GET['move']) && $_GET['move'] == "clearall")
	{
	sendcacheheaders();
	echo "<html>\n";
	foreach(file("$thistpl/startpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
		."\t<script type='text/javascript'>\n"
		."\t<!--\n"
		."\t\tfunction checkconditions(value, name, type)\n"
		."\t\t\t{\n"
		."\t\t\t}\n"
		."\t//-->\n"
		."\t</script>\n\n";

	echo "\t<br />\n"
		."\t<table align='center' cellpadding='30'><tr><td align='center' bgcolor='white'>\n"
		."\t\t<font face='arial' size='2'>\n"
		."\t\t<b><font color='red'>"._ANSCLEAR."</b></font><br /><br />"
		."<a href='{$_SERVER['PHP_SELF']}?sid=$sid";
	if (returnglobal('token')) {
	    echo "&token=".returnglobal('token');
	}
	echo "'>"._RESTART."</a><br />\n"
		."\t\t<a href='javascript: window.close()'>"._CLOSEWIN_PS."</a>\n"
		."\t\t</font>\n"
		."\t</td></tr>\n"
		."\t</table>\n\t<br />\n";
	foreach(file("$thistpl/endpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	echo "</html>\n";
	session_unset();
	session_destroy();
	exit;	
	}

if (isset($_GET['newtest']) && $_GET['newtest'] == "Y")
	{
	foreach ($_SESSION as $SES)
		{
		session_unset();
		}
	//DELETE COOKIE (allow to use multiple times)
	setcookie("$cookiename", "INCOMPLETE", time()-120);
	//echo "Reset Cookie!";

	}

sendcacheheaders();
//CALL APPROPRIATE SCRIPT
switch ($surveyformat)
	{
	case "A": //All in one
		require_once("survey.php");
		break;
	case "S": //One at a time
		require_once("question.php");
		break;
	case "G": //Group at a time
		require_once("group.php");
		break;		
	default:
		require_once("question.php");
	}

function templatereplace($line)
	{
	global $surveyname, $surveydescription;
	global $surveywelcome, $percentcomplete;
	global $groupname, $groupdescription, $question;
	global $questioncode, $answer, $navigator;
	global $help, $totalquestions, $surveyformat;
	global $completed, $surveyurl, $surveyurldescrip;
	global $notanswered, $privacy, $sid;
	global $publicurl, $templatedir, $token;
	
	if ($templatedir) {$templateurl="$publicurl/templates/$templatedir/";}
	else {$templateurl="$publicurl/templates/default/";}

	$clearall = "\t\t\t\t\t<div class='clearall'>"
			  . "<a href='{$_SERVER['PHP_SELF']}?sid=$sid&move=clearall";
	if (returnglobal('token')) {
	    $clearall .= "&token=".returnglobal('token');
	}
	$clearall .="' onClick='return confirm(\""
			  . _CONFIRMCLEAR."\")'>["
			  . _EXITCLEAR."]</a></div>\n";
	
	
	if (ereg("^<body", $line))
		{
		if (isset($_SESSION['step']) && !ereg("^checkconditions()", $line) && ($_SESSION['step'] || $_SESSION['step'] > 0) && (isset($_POST['move']) && ($_POST['move'] != " "._LAST." " || ($_POST['move'] == " "._LAST." " && $notanswered)) && ($_POST['move'] != " "._SUBMIT." " || ($_POST['move']== " "._SUBMIT." " && $notanswered))))
			{
			$line=str_replace("<body", "<body onload=\"checkconditions()\"", $line);
			}
		//elseif ($surveyformat == "A")
		//	{
		//	$line=str_replace("<body", "<body onload=\"checkconditions()\"", $line);
		//	}
		}
	
	$line=str_replace("{SURVEYNAME}", $surveyname, $line);
	$line=str_replace("{SURVEYDESCRIPTION}", $surveydescription, $line);
	$line=str_replace("{WELCOME}", $surveywelcome, $line);
	$line=str_replace("{PERCENTCOMPLETE}", $percentcomplete, $line);
	$line=str_replace("{GROUPNAME}", $groupname, $line);
	$line=str_replace("{GROUPDESCRIPTION}", $groupdescription, $line);
	$line=str_replace("{QUESTION}", $question, $line);
	$line=str_replace("{QUESTION_CODE}", $questioncode, $line);
	$line=str_replace("{ANSWER}", $answer, $line);
	$line=str_replace("{NUMBEROFQUESTIONS}", $totalquestions, $line);
	$line=str_replace("{TOKEN}", $token, $line);
	$line=str_replace("{SID}", $sid, $line);
	if ($help) {
		$line=str_replace("{QUESTIONHELP}", "<img src='".$publicurl."/help.gif' alt='Help' align='left'>".$help, $line);
		$line=str_replace("{QUESTIONHELPPLAINTEXT}", strip_tags(addslashes($help)), $line);
	}
	else
		{$line=str_replace("{QUESTIONHELP}", $help, $line);}
	$line=str_replace("{NAVIGATOR}", $navigator, $line);
	$submitbutton="<input class='submit' type='submit' value=' "._SUBMIT." ' name='move'>";
	$line=str_replace("{SUBMITBUTTON}", $submitbutton, $line);
	$line=str_replace("{COMPLETED}", $completed, $line);
	if (!$surveyurldescrip) {$linkreplace="<a href='$surveyurl'>$surveyurl</a>";}
	else {$linkreplace="<a href='$surveyurl'>$surveyurldescrip</a>";}
	$line=str_replace("{URL}", $linkreplace, $line);
	$line=str_replace("{PRIVACY}", $privacy, $line);
	$line=str_replace("{CLEARALL}", $clearall, $line);
	$line=str_replace("{TEMPLATEURL}", $templateurl, $line);
	return $line;
	}

function makegraph($thisstep, $total)
	{
	global $thistpl, $templatedir, $publicurl;
	$chart=$thistpl."/chart.jpg";
	if (!is_file($chart)) {$shchart="chart.jpg";}
	else {$shchart = "$publicurl/templates/$templatedir/chart.jpg";}
	$graph = "<table class='graph' width='100' align='center' cellpadding='2'><tr><td>\n"
		   . "<table width='180' align='center' cellpadding='0' cellspacing='0' border='0' class='innergraph'>\n"
		   . "<tr><td align='right' width='40'>0%</td>\n";
	$size=intval(($thisstep-1)/$total*100);
	$graph .= "<td width='100' align='left'><img src='$shchart' height='12' width='$size' align='left' alt='$size% "._COMPLETE."'></td>\n"
		    . "<td align='left' width='40'>100%</td></tr>\n"
		    . "</table>\n"
		    . "</td></tr>\n</table>\n";
	return $graph;
	}

function checkgroupfordisplay($gid)
	{
	//This function checks all the questions in a group to see if they have
	//conditions, and if the do - to see if the conditions are met.
	//If none of the questions in the group are set to display, then
	//the function will return false, to indicate that the whole group
	//should not display at all.
	global $dbprefix;
	$countQuestionsInThisGroup=0;
	$countConditionalQuestionsInThisGroup=0;
	foreach ($_SESSION['fieldarray'] as $ia) //Run through all the questions
		{
		if ($ia[5] == $gid) //If the question is in the group we are checking:
			{
			$countQuestionsInThisGroup++;
			if ($ia[7] == "Y") //This question is conditional
				{
			    $countConditionalQuestionsInThisGroup++;
				$checkConditions[]=$ia; //Create an array containing all the conditional questions
				}
			}
		}
	if ($countQuestionsInThisGroup != $countConditionalQuestionsInThisGroup)
		{
		unset($checkConditions); // We can safely delete this variable because it won't be needed.
		return true;
		}
	else
		{
		$totalands=0;
		foreach ($checkConditions as $cc)
			{
			$query = "SELECT * FROM {$dbprefix}conditions WHERE qid=$cc[0] ORDER BY cqid";
			$result = mysql_query($query) or die("Couldn't check conditions<br />$query<br />".mysql_error());
			while($row=mysql_fetch_array($result))
				{
				$query2="SELECT type FROM {$dbprefix}questions WHERE qid={$row['cqid']}";
				$result2=mysql_query($query2) or die ("Coudn't get type from questions<br />$ccquery<br />".mysql_error());
				while($row2=mysql_fetch_array($result2))
					{
					$thistype=$row2['type'];
					}
				if ($thistype == "M" || $thistype == "P")
					{
					$fieldname=$row['cfieldname'].$row['value'];
					if (isset($_SESSION[$fieldname])) 
						{
						$cfieldname=$_SESSION[$fieldname];
						$cvalue="Y";
						}
					}
				else
					{
					if (isset($_SESSION[$row['cfieldname']]))
						{
						$cfieldname=$_SESSION[$row['cfieldname']];
						$cvalue=$row['value'];
					    }
					}
				if ($cfieldname == $cvalue)
					{
					$distinctcqids[$row['cqid']]=1;
					}
				else
					{
					if (!isset($distinctcqids[$row['cqid']])) 
						{
						$distinctcqids[$row['cqid']]=0;
						}
					}
				} // while
			foreach($distinctcqids as $key=>$val)
				{
				$totalands=$totalands+$val;
				}
			}
		if ($totalands == count($distinctcqids)) 
			{
		    return true;
			}
		else
			{
			return false;
			}
		}
	}

function checkconfield($value)
	{
	global $dbprefix;
	foreach ($_SESSION['fieldarray'] as $sfa)
		{
		if ($sfa[1] == $value && $sfa[7] == "Y" && isset($_SESSION[$value]) && $_SESSION[$value])
			{
			$currentcfield="";
			$query = "SELECT {$dbprefix}conditions.*, {$dbprefix}questions.type "
				   . "FROM {$dbprefix}conditions, {$dbprefix}questions "
				   . "WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid "
				   . "AND {$dbprefix}conditions.qid=$sfa[0] "
				   . "ORDER BY {$dbprefix}conditions.qid";
			$result=mysql_query($query) or die($query."<br />".mysql_error());
			while($rows = mysql_fetch_array($result))
				{
				if($rows['type'] == "M" || $rows['type'] == "A" || $rows['type'] == "B" || $rows['type'] == "C" || $rows['type'] == "E" || $rows['type'] == "F" || $rows['type'] == "P")
					{
					$matchfield=$rows['cfieldname'].$rows['value'];
					$matchvalue="Y";
					}
				else
					{
					$matchfield=$rows['cfieldname'];
					$matchvalue=$rows['value'];
					}
				$cqval[]=array("cfieldname"=>$rows['cfieldname'],
							 "value"=>$rows['value'],
							 "type"=>$rows['type'],
							 "matchfield"=>$matchfield,
							 "matchvalue"=>$matchvalue);
				if ($rows['cfieldname'] != $currentcfield)
					{
					$container[]=$rows['cfieldname'];
					}
				$currentcfield=$rows['cfieldname'];
				}
			//At least one match must be found for each "$container"
			$total=0;
			foreach($container as $con)
				{
				$addon=0;
				foreach($cqval as $cqv)
					{//Go through each condition
					if($cqv['cfieldname'] == $con)
						{
						if(isset($_SESSION[$cqv['matchfield']]) && $_SESSION[$cqv['matchfield']] == $cqv['matchvalue'])
							{//plug succesful matches into appropriate container
							$addon=1;
							}
						}
					}
				if($addon==1){$total++;}
				}
			if($total<count($container))
				{
				$_SESSION[$value]="";
				}
			unset($cqval);
			unset($container);
			}
		}
	}

function checkmandatorys($backok=null)
	{
	if ((isset($_POST['mandatory']) && $_POST['mandatory']) && (!isset($backok) || $backok != "Y"))
		{
		$chkmands=explode("|", $_POST['mandatory']); //These are the mandatory questions to check
		$mfns=explode("|", $_POST['mandatoryfn']); //These are the fieldnames of the mandatory questions
		$mi=0;
		foreach ($chkmands as $cm)
			{
			if (!isset($multiname) || $multiname != "MULTI$mfns[$mi]")  //no multiple type mandatory set, or does not match this question (set later on for first time)
				{
				if ((isset($multiname) && $multiname) && (isset($_POST[$multiname]) && $_POST[$multiname])) //This isn't the first time (multiname exists, and is a posted variable)
					{
					if ($$multiname == $$multiname2) //The number of questions not answered is equal to the number of questions
						{
						//The number of questions not answered is equal to the number of questions
						//This section gets used if it is a multiple choice type question
						if (isset($_POST['move']) && $_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep'];}
						if (isset($_POST['move']) && $_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep'];}
						if (isset($_POST['move']) && $_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == " "._NEXT." >> ";}
					    $notanswered[]=substr($multiname, 5, strlen($multiname));
						$$multiname=0;
						$$multiname2=0;
						}
					}
				$multiname="MULTI$mfns[$mi]";
				$multiname2=$multiname."2"; //POSSIBLE CORRUPTION OF PROCESS - CHECK LATER
				$$multiname=0;
				$$multiname2=0;
				}
			else {$multiname="MULTI$mfns[$mi]";}
			if (isset($_SESSION[$cm]) && ($_SESSION[$cm] == "0" || $_SESSION[$cm]))
				{
				}
			elseif (!isset($_POST[$multiname]) || !$_POST[$multiname])
				{
				//One of the mandatory questions hasn't been asnwered
				if (isset($_POST['move']) && $_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == " "._NEXT." >> ";}
				$notanswered[]=$mfns[$mi];
				}
			else
				{
				//One of the mandatory questions hasn't been answered
				$$multiname++;
				}
			$$multiname2++;
			$mi++;
			}
		if ($multiname && isset($_POST[$multiname]) && $_POST[$multiname]) // Catch the last multiple options question in the lot
			{
			if ($$multiname == $$multiname2) //so far all multiple choice options are unanswered
				{
				//The number of questions not answered is equal to the number of questions
				if (isset($_POST['move']) && $_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == " "._NEXT." >> ";}
			    $notanswered[]=substr($multiname, 5, strlen($multiname));
				$$multiname="";
				$$multiname2="";
				}
			}
		}
	if (!isset($notanswered)) {return false;}//$notanswered=null;}
	return $notanswered;
	}

function checkconditionalmandatorys($backok=null)
	{
	if ((isset($_POST['conmandatory']) && $_POST['conmandatory']) && (!isset($backok) || $backok != "Y")) //Mandatory conditional questions that should only be checked if the conditions for displaying that question are met
		{
		$chkcmands=explode("|", $_POST['conmandatory']);
		$cmfns=explode("|", $_POST['conmandatoryfn']);
		$mi=0;
		foreach ($chkcmands as $ccm)
			{
			if (!isset($multiname) || $multiname != "MULTI$cmfns[$mi]") //the last multipleanswerchecked is different to this one
				{
				if (isset($multiname) && $multiname && isset($_POST[$multiname]) && $_POST[$multiname])
					{
					if ($$multiname == $$multiname2) //For this lot all multiple choice options are unanswered
						{
						//The number of questions not answered is equal to the number of questions
						if (isset($_POST['move']) && $_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep'];}
						if (isset($_POST['move']) && $_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep'];}
						if (isset($_POST['move']) && $_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == " "._NEXT." >> ";}
					    $notanswered[]=substr($multiname, 5, strlen($multiname));
						$$multiname=0;
						$$multiname2=0;
						}
				    }
				$multiname="MULTI$cmfns[$mi]"; 
				$multiname2=$multiname."2"; //POSSIBLE CORRUPTION OF PROCESS - CHECK LATER
				$$multiname=0; 
				$$multiname2=0;
				}
			else{$multiname="MULTI$cmfns[$mi]";}
			$dccm="display$cmfns[$mi]";
			if (isset($_SESSION[$ccm]) && ($_SESSION[$ccm] == "0" || $_SESSION[$ccm]) && isset($_POST[$dccm]) && $_POST[$dccm] == "on") //There is an answer
				{
				}
			elseif ((isset($_POST[$dccm]) && $_POST[$dccm] == "on") && (!isset($_POST[$multiname]) || !$_POST[$multiname])) //Question is on, there is no answer, but it's a multiple
				{
				if (isset($_POST['move']) && $_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == " "._NEXT." >> ";}
				$notanswered[]=$cmfns[$mi];
				}
			elseif (isset($_POST[$dccm]) && $_POST[$dccm] == "on")
				{
				//One of the conditional mandatory questions was on, but hasn't been answered
				$$multiname++; 
				}
			$$multiname2++;
			$mi++;
			}
		if (isset($multiname) && $multiname && isset($_POST[$multiname]) && $_POST[$multiname])
			{
			if ($$multiname == $$multiname2) //so far all multiple choice options are unanswered
				{
				//The number of questions not answered is equal to the number of questions
				if (isset($_POST['move']) && $_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == " "._NEXT." >> ";}
			    $notanswered[]=substr($multiname, 5, strlen($multiname));
				}
			}
		}	
	if (!isset($notanswered)) {return false;}//$notanswered=null;}
	return $notanswered;
	}

function addtoarray_single($array1, $array2)
	{
	//Takes two single element arrays and adds second to end of first if value exists
	if (is_array($array2)) 
		{
		foreach ($array2 as $ar)
			{
			if ($ar && $ar !== null)
				{
			    $array1[]=$ar;
				}
			}
		}
	return $array1;
	}

function remove_nulls_from_array($array)
	{
	foreach ($array as $ar)
		{
		if ($ar !== null)
			{
		    $return[]=$ar;
			}
		}
	if (isset($return)) 
		{
		return $return;
		}
	else
		{
		return false;
		}
	}
?>