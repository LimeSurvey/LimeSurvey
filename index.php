<?php
/*
	#############################################################
	# >>> PHPSurveyor  											#
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

if ( $embedded_inc != '' )
	require_once( $embedded_inc );

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
//This next line is for security reasons. It ensures that the $surveyid value is never anything but a number.
if (_PHPVERSION >= '4.2.0') {settype($surveyid, "int");} else {settype($surveyid, "integer");} 

//DEFAULT SETTINGS FOR TEMPLATES
if (!$publicdir) {$publicdir=".";}
$tpldir="$publicdir/templates";

//CHECK FOR REQUIRED INFORMATION (sid)
if (!$surveyid)
	{
	$langfilename="$publicdir/lang/$defaultlang.lang.php";
	require($langfilename);
	//A nice exit
	sendcacheheaders();
	doHeader();
	$output=file("$tpldir/default/startpage.pstpl");
	foreach($output as $op)
		{
		echo templatereplace($op);
		}
	echo "\t\t<center><br />\n"
		."\t\t\t<font color='RED'><strong>ERROR</strong></font><br />\n"
		."\t\t\t"._NOSID."<br />\n"
		."\t\t\t"._CONTACT1." $siteadminname ( $siteadminemail ) "._CONTACT2."\n"
		."\t\t</center><br />\n";
	$output=file("$tpldir/default/endpage.pstpl");
	foreach($output as $op)
		{
		echo templatereplace($op);
		}
	doFooter();
	exit;
	}

if (!isset($token)) {$token=trim(returnglobal('token'));}
//GET BASIC INFORMATION ABOUT THIS SURVEY
$thissurvey=getSurveyInfo($surveyid);
if (is_array($thissurvey)) {$surveyexists=1;} else {$surveyexists=0;}

//SEE IF SURVEY USES TOKENS
$i = 0; $tokensexist = 0;
$tresult = @mysql_list_tables($databasename) or die ("Error getting tokens<br />".mysql_error());
while($tbl = @mysql_tablename($tresult, $i++))
	{
	if ($tbl == "{$dbprefix}tokens_$surveyid") {$tokensexist = 1;}
	}

//SET THE TEMPLATE DIRECTORY
if (!$thissurvey['templatedir']) {$thistpl=$tpldir."/default";} else {$thistpl=$tpldir."/{$thissurvey['templatedir']}";}
if (!is_dir($thistpl)) {$thistpl=$tpldir."/default";}

//REQUIRE THE LANGUAGE FILE
$langdir="$publicdir/lang";
$langfilename="$langdir/{$thissurvey['language']}.lang.php";
//Use the default language file if the $thissurvey['language'] file doesn't exist
if (!is_file($langfilename)) {$langfilename="$langdir/$defaultlang.lang.php";}
require_once($langfilename);
$saveerror=error_reporting(3);
require_once("$langdir/english.lang.php"); //Load missing values from english file
error_reporting ($saveerror);

//MAKE SURE SURVEY HASN'T EXPIRED
if ($thissurvey['expiry'] < date("Y-m-d") && $thissurvey['expiry'] != "0000-00-00")
	{
	sendcacheheaders();
	doHeader();
	$output=file("$tpldir/default/startpage.pstpl");
	foreach ($output as $op)
		{
		echo templatereplace($op);
		}
	echo "\t\t<center><br />\n"
		."\t\t\t"._SURVEYEXPIRED."<br /><br />\n"
		."\t\t\t"._CONTACT1." <i>{$thissurvey['adminname']}</i> (<i>{$thissurvey['adminemail']}</i>) "
		._CONTACT2."<br /><br />\n";
	$output=file("$tpldir/default/endpage.pstpl");
	foreach ($output as $op)
		{
		echo templatereplace($op);
		}
	doFooter();
	exit;
	}
	
//CHECK FOR PREVIOUSLY COMPLETED COOKIE
//If cookies are being used, and this survey has been completed, a cookie called "PHPSID[sid]STATUS" will exist (ie: SID6STATUS) and will have a value of "COMPLETE"
$cookiename="PHPSID".returnglobal('sid')."STATUS";
if (isset($_COOKIE[$cookiename]) && $_COOKIE[$cookiename] == "COMPLETE" && $thissurvey['usecookie'] == "Y" && $tokensexist != 1 && (!isset($_GET['newtest']) || $_GET['newtest'] != "Y"))
	{
	sendcacheheaders();
	doHeader();
	$output=file("$tpldir/default/startpage.pstpl");
	foreach($output as $op)
		{
		echo templatereplace($op);
		}
	echo "\t\t<center><br />\n"
		."\t\t\t<font color='RED'><strong>"._ERROR_PS."</strong></font><br />\n"
		."\t\t\t"._SURVEYCOMPLETE."<br /><br />\n"
		."\t\t\t"._CONTACT1." <i>{$thissurvey['adminname']}</i> (<i>{$thissurvey['adminemail']}</i>) "
		._CONTACT2."<br /><br />\n";
	$output=file("$tpldir/default/endpage.pstpl");
	foreach($output as $op)
		{
		echo templatereplace($op);
		}
	doFooter();
	exit;
	}

//CHECK IF SURVEY ID DETAILS HAVE CHANGED
if (isset($_SESSION['oldsid'])) {$oldsid=$_SESSION['oldsid'];}

if (!isset($oldsid)) {$_SESSION['oldsid'] = $surveyid;}

if (isset($oldsid) && $oldsid && $oldsid != $surveyid)
	{
	session_unset();
	$_SESSION['oldsid']=$surveyid;
	}

//Save and clear session if requested
if (isset($_POST['saveall']) && $_POST['saveall'] == _SAVE_AND_RETURN) 
	{
	require_once("save.php");
	}

if (isset($_GET['loadall']) && $_GET['loadall'] == "reload")
	{
    if (returnglobal('loadname') && returnglobal('loadpass'))
		{
        $_POST['loadall']="reload";
		$_POST['loadname']=returnglobal('loadname');
		$_POST['loadpass']=returnglobal('loadpass');
		$_POST['scid']=returnglobal('scid');
		}
	}
//Load saved survey
if (isset($_POST['loadall']) && $_POST['loadall'] == "reload")
	{
	$errormsg="";
	// if (loadname is not set) or if ((loadname is set) and (loadname is NULL))
	if (!isset($_POST['loadname']) || (isset($_POST['loadname']) && ($_POST['loadname'] == null)))		
		{
	    $errormsg .= _LOADNONAME."<br />\n";
		}
	// if (loadpass is not set) or if ((loadpass is set) and (loadpass is NULL))
	if (!isset($_POST['loadpass']) || (isset($_POST['loadpass']) && ($_POST['loadpass'] == null)))
		{
		$errormsg .= _LOADNOPASS."<br />\n";
		}
	$query = "SELECT * FROM {$dbprefix}saved, {$dbprefix}saved_control
			  WHERE {$dbprefix}saved.scid={$dbprefix}saved_control.scid 
			  AND {$dbprefix}saved_control.sid=$surveyid\n";
	if (isset($_POST['scid'])) {
	    $query .= "AND {$dbprefix}saved.scid=".auto_escape($_POST['scid'])."\n";
	}		  
	$query .="AND {$dbprefix}saved_control.identifier='".auto_escape($_POST['loadname'])."'
			  AND {$dbprefix}saved_control.access_code='".md5(auto_unescape($_POST['loadpass']))."'\n";
	$result = mysql_query($query) or die ("Error loading results<br />$query<br />".mysql_error());
	if (mysql_num_rows($result) > 0)
		{
		//A match has been found. Let's load the values if there are saved responses!
		//If this is from an email, build surveysession first
		while($row=mysql_fetch_array($result))
			{
			if ($row['fieldname'] == "token")
				{
			    $_POST['token']=$row['value'];
				$token=$row['value'];
				}
			else
				{
				$_SESSION[$row['fieldname']]=$row['value'];
				$_POST['thisstep']=$row['saved_thisstep']-1;
				$_SESSION['scid']=$row['scid'];
				}
			} // while
		}
		else
    		{
    		$errormsg .= _LOADNOMATCH."<br />\n";
    		}			
		$_SESSION['savename']=$_POST['loadname']; //This session variable hangs around
		                                           //for later use.
		$_POST['move'] = " "._NEXT." >> "; 
		if (isset($_GET['loadall']))
			{
			buildsurveysession();
			}
		
	
	if ($errormsg)
		{
	    $_POST['loadall'] = _LOAD_SAVED;
		}
	}
//Allow loading of saved survey
if (isset($_POST['loadall']) && $_POST['loadall'] == _LOAD_SAVED)
	{
    require_once("load.php");
	}


//Check if TOKEN is used for EVERY PAGE
//This function fixes a bug where users able to submit two surveys/votes
//by checking that the token has not been used at each page displayed.
if ($tokensexist == 1 && returnglobal('token'))
	{
	//check if token actually does exist
	$tkquery = "SELECT * FROM {$dbprefix}tokens_$surveyid WHERE token='".trim(returnglobal('token'))."' AND completed != 'Y'";
	$tkresult = mysql_query($tkquery);
	$tkexist = mysql_num_rows($tkresult);
	if (!$tkexist)
		{
		sendcacheheaders();
		doHeader();
		//TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT
		foreach(file("$thistpl/startpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		foreach(file("$thistpl/survey.pstpl") as $op)
			{
			echo "\t".templatereplace($op);
			}
		echo "\t<center><br />\n"
			."\t"._NOTOKEN1."<br /><br />\n"
			."\t"._NOTOKEN3."\n"
			."\t"._FURTHERINFO." {$thissurvey['adminname']} "
			."(<a href='mailto:{$thissurvey['adminemail']}'>"
			."{$thissurvey['adminemail']}</a>)<br /><br />\n"
			."\t<a href='javascript: self.close()'>"._CLOSEWIN_PS."</a><br />&nbsp;\n";
		foreach(file("$thistpl/endpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		exit;
		}
	}
//CLEAR SESSION IF REQUESTED
if (isset($_GET['move']) && $_GET['move'] == "clearall")
	{
	session_unset();
	session_destroy();
	sendcacheheaders();
	if (isset($_GET['redirect'])) {
	    session_write_close();
	    header("Location: {$_GET['redirect']}");
	}
	doHeader();
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

	//Present the clear all page using clearall.pstpl template
	foreach(file("$thistpl/clearall.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	
	foreach(file("$thistpl/endpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	doFooter();
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
switch ($thissurvey['format'])
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

function getTokenData($surveyid, $token)
	{
	global $dbprefix;
	$query = "SELECT * FROM {$dbprefix}tokens_$surveyid WHERE token='$token'";
	$result = mysql_query($query) or die("Couldn't get token info in getTokenData()<br />".$query."<br />".mysql_error());
	while($row=mysql_fetch_array($result))
		{
		$thistoken=array("firstname"=>$row['firstname'],
						 "lastname"=>$row['lastname'],
						 "email"=>$row['email'],
						 "attribute_1"=>$row['attribute_1'],
						 "attribute_2"=>$row['attribute_2']);
		} // while
	return $thistoken;
	}

function makegraph($thisstep, $total)
	{
	global $thissurvey;
	global $thistpl, $publicurl;
	$chart=$thistpl."/chart.jpg";
	if (!is_file($chart)) {$shchart="chart.jpg";}
	else {$shchart = "$publicurl/templates/{$thissurvey['templatedir']}/chart.jpg";}
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
	if ($countQuestionsInThisGroup != $countConditionalQuestionsInThisGroup || !isset($checkConditions))
		{
		//One of the questions in this group is NOT conditional, therefore
		//the group MUST be displayed
		return true;
		}
	else
		{
		//All of the questions in this group are conditional. Now we must
		//check every question, to see if the condition for each has been met.
		//If 1 or more have their conditions met, then the group should
		//be displayed.
		foreach ($checkConditions as $cc)
			{
			$totalands=0;
			$query = "SELECT * FROM {$dbprefix}conditions\n"
					."WHERE qid=$cc[0] ORDER BY cqid";
			$result = mysql_query($query) or die("Couldn't check conditions<br />$query<br />".mysql_error());
			while($row=mysql_fetch_array($result))
				{
				//Iterate through each condition for this question and check if it is met.
				$query2= "SELECT type, gid FROM {$dbprefix}questions\n"
						." WHERE qid={$row['cqid']}";
				$result2=mysql_query($query2) or die ("Coudn't get type from questions<br />$ccquery<br />".mysql_error());
				while($row2=mysql_fetch_array($result2))
					{
					$cq_gid=$row2['gid'];
					//Find out the "type" of the question this condition uses
					$thistype=$row2['type'];
					}
				if ($gid == $cq_gid) 
					{
				    //Don't do anything - this cq is in the current group
					}
				elseif ($thistype == "M" || $thistype == "P")
					{
					// For multiple choice type questions, the "answer" value will be "Y"
					// if selected, the fieldname will have the answer code appended.
					$fieldname=$row['cfieldname'].$row['value'];
					if (isset($_SESSION[$fieldname])) 
						{
						$cfieldname=$_SESSION[$fieldname];
						$cvalue="Y";
						}
					}
				else
					{
					//For all other questions, the "answer" value will be the answer
					//code.
					if (isset($_SESSION[$row['cfieldname']]))
						{
						$cfieldname=$_SESSION[$row['cfieldname']];
						$cvalue=$row['value'];
						}
					}
				if ($cfieldname == $cvalue)
					{
					//This condition is met
					//Bugfix provided by Zoran Avtarovski
					if (!isset($distinctcqids[$row['cqid']])  || $distinctcqids[$row['cqid']] == 0)
						{
						$distinctcqids[$row['cqid']]=1;
						}
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
				//Because multiple cqids are treated as "AND", we only check
				//one condition per conditional qid (cqid). As long as one
				//match is found for each distinct cqid, then the condition is met.
				$totalands=$totalands+$val;
				}
			if ($totalands >= count($distinctcqids))
				{
				//The number of matches to conditions exceeds the number of distinct 
				//conditional questions, therefore a condition has been met.
				//As soon as any condition for a question is met, we MUST show the group.
				return true;
				}
			unset($distinctcqids);
			}
			//Since we made it this far, there mustn't have been any conditions met.
			//Therefore the group should not be displayed.
			return false;
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
				if($rows['type'] == "M" || $rows['type'] == "P")
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
				//The question has an answer, and the answer was displaying
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

function checkpregs($backok=null)
	{
	if (!isset($backok) || $backok != "Y") 
		{
		global $dbprefix;
		$fieldmap=createFieldMap(returnglobal('sid'));
		if (isset($_POST['fieldnames']))
			{
			$fields=explode("|", $_POST['fieldnames']);
			foreach ($fields as $field)
				{
				//Get question information
				if (isset($_POST[$field]) && ($_POST[$field] == "0" || $_POST[$field])) //Only do this if there is an answer
					{
					$fieldinfo=arraySearchByKey($field, $fieldmap, "fieldname", 1);
					$pregquery="SELECT preg\n"
							  ."FROM {$dbprefix}questions\n"
							  ."WHERE qid=".$fieldinfo['qid'];
					$pregresult=mysql_query($pregquery) or die("ERROR: $pregquery<br />".mysql_error());
					while($pregrow=mysql_fetch_array($pregresult))
						{
						$preg=$pregrow['preg'];
						} // while
					if (isset($preg) && $preg)
						{
					    if (!preg_match($preg, $_POST[$field]))
							{
							$notvalidated[]=$field;
							}
						}
				    }
				}
			}
		if (isset($notvalidated) && is_array($notvalidated))
			{
			if (isset($_POST['move']) && $_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep'];}
			if (isset($_POST['move']) && $_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep'];}
			if (isset($_POST['move']) && $_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == " "._NEXT." >> ";}
			return $notvalidated;
			}
	    }
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

//FUNCTIONS USED WHEN SUBMITTING RESULTS:
function createinsertquery()
	{
	global $thissurvey;
	global $deletenonvalues, $thistpl;
	global $surveyid;
	$fieldmap=createFieldMap($surveyid); //Creates a list of the legitimate questions for this survey
	
	if (isset($_SESSION['insertarray']) && is_array($_SESSION['insertarray']))
		{
		$inserts=array_unique($_SESSION['insertarray']);
	    foreach ($inserts as $value)
			{
			//Work out if the field actually exists in this survey
			$fieldexists = arraySearchByKey($value, $fieldmap, "fieldname");
			//Iterate through possible responses
			if (isset($_SESSION[$value]) && !empty($fieldexists))
				{
				//If deletenonvalues is ON, delete any values that shouldn't exist
				if($deletenonvalues==1) {checkconfield($value);}
				//Only create column name and data entry if there is actually data!
				$colnames[]=$value;
			    $values[]=mysql_escape_string($_SESSION[$value]);
				}
			}
		if (!isset($colnames) || !is_array($colnames)) //If something went horribly wrong - ie: none of the insertarray fields exist for this survey, crash out
			{
			echo submitfailed();
			
			exit;		
		    }
		$query = "INSERT INTO {$thissurvey['tablename']}\n"
				."(`".implode("`, `", $colnames)."`)\n"
				."VALUES ('".implode("', '", $values)."')";
		return $query;
		}
	else
		{
		sendcacheheaders();
		doHeader();
		foreach(file("$thistpl/startpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		echo "<br /><center><font face='verdana' size='2'><font color='red'><strong>"._ERROR_PS."</strong></font><br /><br />\n";
		echo _BADSUBMIT1."<br /><br />\n";
		echo "<font size='1'>"._BADSUBMIT2."<br />\n";
		echo "</font></center><br /><br />";
		exit;		
		}
	}

function submittokens()
	{
	global $thissurvey;
	global $dbprefix, $surveyid;
	global $sitename, $thistpl;
	
	$utquery = "UPDATE {$dbprefix}tokens_$surveyid\n"
			 . "SET completed='Y'\n"
			 . "WHERE token='{$_POST['token']}'";
	$utresult = mysql_query($utquery) or die ("Couldn't update tokens table!<br />\n$utquery<br />\n".mysql_error());
	$cnfquery = "SELECT * FROM {$dbprefix}tokens_$surveyid WHERE token='{$_POST['token']}' AND completed='Y'";
	$cnfresult = mysql_query($cnfquery);
	while ($cnfrow = mysql_fetch_array($cnfresult))
		{
		$from = "{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";
		$to = $cnfrow['email'];
        $subject=$thissurvey['email_confirm_subj'];
        
        $fieldsarray["{ADMINNAME}"]=$thissurvey['adminname'];
        $fieldsarray["{ADMINEMAIL}"]=$thissurvey['adminemail'];
        $fieldsarray["{SURVEYNAME}"]=$thissurvey['name'];
        $fieldsarray["{SURVEYDESCRIPTION}"]=$thissurvey['description'];
        $fieldsarray["{FIRSTNAME}"]=$cnfrow['firstname'];
        $fieldsarray["{LASTNAME}"]=$cnfrow['lastname'];
        $fieldsarray["{ATTRIBUTE_1}"]=$cnfrow['attribute_1'];
        $fieldsarray["{ATTRIBUTE_2}"]=$cnfrow['attribute_2'];

        $subject=Replacefields($subject, $fieldsarray);

		if ($thissurvey['email_confirm']) 
			{
			$message=$thissurvey['email_confirm'];
			}
		else
			{
			//Get the default email_confirm from the default admin lang file
			global $defaultlang, $homedir, $homeurl;
			$langdir="$homeurl/lang/$defaultlang";
			$langdir2="$homedir/lang/$defaultlang";
			if (!is_dir($langdir2)) 
				{
				$langdir="$homeurl/lang/english"; //default to english if there is no matching language dir
				$langdir2="$homedir/lang/english";
				}
			require("$langdir2/messages.php");
			echo "<!-- Sending Default Email -->\n";
			$message = _TC_EMAILCONFIRM;
			}

        $message=Replacefields($message, $fieldsarray);
        
		//Only send confirmation email if there is a valid email address
		if (validate_email($cnfrow['email'])) {MailTextMessage($message, $subject, $to, $from, $sitename);} 
		
		//DEBUG INFO: CAN BE REMOVED
		echo "<!-- DEBUG: MAIL INFORMATION\n"
			."FROM: $from\n"
			."TO: $to\n"
			."SUBJECT: $subject\n"
			."MESSAGE: $message\n"
			."-->\n";
		//END DEBUG
		}	
	}
	
function sendsubmitnotification($sendnotification)
	{
	global $thissurvey;
	global $savedid, $dbprefix;
	global $sitename, $homeurl, $surveyid;

	$subject = "$sitename Survey Submitted";
	$message = _CONFIRMATION_MESSAGE1." {$thissurvey['name']}\r\n"
			 . _CONFIRMATION_MESSAGE2."\r\n\r\n"
			 . _CONFIRMATION_MESSAGE3."\r\n"
			 . "  $homeurl/browse.php?sid=$surveyid&action=id&id=$savedid\r\n\r\n"
             // Add link to edit individual responses from notification email  
			 . _CONFIRMATION_MESSAGE5."\r\n"
             . " $homeurl/dataentry.php?sid=$surveyid&action=edit&surveytable=survey_$surveyid&id=$savedid\r\n\r\n"  
			 . _CONFIRMATION_MESSAGE4."\r\n"
			 . "  $homeurl/statistics.php?sid=$surveyid\r\n\r\n";
	if ($sendnotification > 1)
		{ //Send results as well. Currently just bare-bones - will be extended in later release
		$message .= "----------------------------\r\n";
		foreach ($_SESSION['insertarray'] as $value)
			{
			$questiontitle=returnquestiontitlefromfieldcode($value);
			$message .= "$questiontitle:   ";
			if (isset($_SESSION[$value]))
				{
			    $message .= getextendedanswer($value, $_SESSION[$value]);
				}
			$message .= "\r\n";
			}
		$message .= "----------------------------\r\n\r\n";
		}
	$message.= "PHPSurveyor";
	$from = $thissurvey['adminemail'];
	
	if ($recips=explode(";", $thissurvey['adminemail'])) 
		{
	    foreach ($recips as $rc) 
			{
			MailTextMessage($message, $subject, trim($rc), $from, $sitename);
			}
		}
	else
		{
		MailTextMessage($message, $subject, $thissurvey['adminemail'], $from, $sitename);			
		}
	}

function submitfailed()
	{
	global $thissurvey;
	global $thistpl, $subquery, $surveyid;
	sendcacheheaders();
	doHeader();
	foreach(file("$thistpl/startpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	$completed = "<br /><strong><font size='2' color='red'>"
			   . _DIDNOTSAVE."</strong></font><br /><br />\n\n"
			   . _DIDNOTSAVE2."<br /><br />\n";
	if ($thissurvey['adminemail'])
		{	
		$completed .= _DIDNOTSAVE3."<br /><br />\n";
		$email=_DNSAVEEMAIL1." ".$thissurvey['name']." - $surveyid\n\n";
		$email .= _DNSAVEEMAIL2.":\n";
		foreach ($_SESSION['insertarray'] as $value)
			{
			$email .= "$value: {$_SESSION[$value]}\n";
			}
		$email .= "\n"._DNSAVEEMAIL3.":\n"
				. "$subquery\n\n"
				. _DNSAVEEMAIL4.":\n"
				. mysql_error()."\n\n";
		MailTextMessage($email, _DNSAVEEMAIL5, $thissurvey['adminemail'], $thissurvey['adminemail'], "PHPSurveyor");
		echo "<!-- EMAIL CONTENTS:\n$email -->\n";
		//An email has been sent, so we can kill off this session.
		session_unset();
		session_destroy();
		}
	else
		{
		$completed .= "<a href='javascript:location.reload()'>"._SUBMITAGAIN."</a><br /><br />\n";
		$completed .= $subquery;
		}
	return $completed;
	}

function buildsurveysession()
	{
	global $thissurvey;
	global $tokensexist, $thistpl;
	global $surveyid, $dbprefix;
	global $register_errormsg;
	
	//This function builds all the required session variables when a survey is first started.
	//It is called from each various format script (ie: group.php, question.php, survey.php)
	//if the survey has just begun. This funcion also returns the variable $totalquestions.
	
	//BEFORE BUILDING A NEW SESSION FOR THIS SURVEY, LET'S CHECK TO MAKE SURE THE SURVEY SHOULD PROCEED!
	if ($tokensexist == 1 && !returnglobal('token'))
		{
		sendcacheheaders();
		doHeader();
		//NO TOKEN PRESENTED. EXPLAIN PROBLEM AND PRESENT FORM
		foreach(file("$thistpl/startpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		foreach(file("$thistpl/survey.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		if (isset($thissurvey) && $thissurvey['allowregister'] == "Y")
			{
			foreach(file("$thistpl/register.pstpl") as $op)
				{
				echo templatereplace($op);
				}
			}
		else
			{
?>
	<center><br />
	<?php echo _NOTOKEN1 ?><br /><br />
	<?php echo _NOTOKEN2 ?><br />&nbsp;
	<table align='center'>
	<form method='get' action='<?php echo $_SERVER['PHP_SELF'] ?>'>
	<input type='hidden' name='sid' value='<?php echo $surveyid ?>' id='sid'>
		<tr>
			<td align='center' valign='middle'>
			<?php echo _TOKEN_PS ?>: <input class='text' type='text' name='token'>
			<input class='submit' type='submit' value='<?php echo _CONTINUE_PS ?>'>
			</td>
		</tr>
	</form>
	</table>
	<br />&nbsp;</center>
<?php
			}
		foreach(file("$thistpl/endpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		exit;
		}
	//Tokens are required, and a token has been provided.
	elseif ($tokensexist == 1 && returnglobal('token'))
		{
		//check if token actually does exist
		$tkquery = "SELECT * FROM {$dbprefix}tokens_$surveyid WHERE token='".trim(returnglobal('token'))."' AND completed != 'Y'";
		$tkresult = mysql_query($tkquery);
		$tkexist = mysql_num_rows($tkresult);
		if (!$tkexist)
			{
			sendcacheheaders();
			doHeader();
			//TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT
			foreach(file("$thistpl/startpage.pstpl") as $op)
				{
				echo templatereplace($op);
				}
			foreach(file("$thistpl/survey.pstpl") as $op)
				{
				echo "\t".templatereplace($op);
				}
			echo "\t<center><br />\n"
				."\t"._NOTOKEN1."<br /><br />\n"
				."\t"._NOTOKEN3."\n"
				."\t"._FURTHERINFO." {$thissurvey['adminname']} "
				."(<a href='mailto:{$thissurvey['adminemail']}'>"
				."{$thissurvey['adminemail']}</a>)<br /><br />\n"
				."\t<a href='javascript: self.close()'>"._CLOSEWIN_PS."</a><br />&nbsp;\n";
			foreach(file("$thistpl/endpage.pstpl") as $op)
				{
				echo templatereplace($op);
				}
			exit;
			}
		}

	//RESET ALL THE SESSION VARIABLES AND START AGAIN
	unset($_SESSION['grouplist']);
	unset($_SESSION['fieldarray']);
	unset($_SESSION['insertarray']);
	unset($_SESSION['thistoken']);
	//1. SESSION VARIABLE: grouplist
	//A list of groups in this survey, ordered by group name.

	$query = "SELECT * FROM {$dbprefix}groups WHERE sid=$surveyid ORDER BY group_name";
	$result = mysql_query($query) or die ("Couldn't get group list<br />$query<br />".mysql_error());
	while ($row = mysql_fetch_array($result))
		{
		$_SESSION['grouplist'][]=array($row['gid'], $row['group_name'], $row['description']);
		}

	$query = "SELECT * FROM {$dbprefix}questions, {$dbprefix}groups\n"
			."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid\n"
			."AND {$dbprefix}questions.sid=$surveyid\n"
			."ORDER BY group_name";
	$result = mysql_query($query);
	$totalquestions = mysql_num_rows($result);

	//2. SESSION VARIABLE: totalsteps
	//The number of "pages" that will be presented in this survey
	//The number of pages to be presented will differ depending on the survey format
	switch($thissurvey['format'])
		{
		case "A":
			$_SESSION['totalsteps']=1;
			break;
		case "G":
			$_SESSION['totalsteps']=count($_SESSION['grouplist']);
			break;
		case "S":
			$_SESSION['totalsteps']=$totalquestions;
		}
	
	if ($totalquestions == "0")	//break out and crash if there are no questions!
		{
		sendcacheheaders();
		doHeader();
		foreach(file("$thistpl/startpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		foreach(file("$thistpl/survey.pstpl") as $op)
			{
			echo "\t".templatereplace($op);
			}
		echo "\t<center><br />\n"
			."\t"._NOQUESTIONS."<br /><br />\n"
			."\t"._FURTHERINFO." {$thissurvey['adminname']}"
			." (<a href='mailto:{$thissurvey['adminemail']}'>"
			."{$thissurvey['adminemail']}</a>)<br /><br />\n"
			."\t<a href='javascript: self.close()'>"._CLOSEWIN_PS."</a><br />&nbsp;\n";
		foreach(file("$thistpl/endpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		exit;
		}

	$arows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	while ($row = mysql_fetch_assoc($result)) 
		{
		$arows[] = $row;
		} // Get table output into array
	
	//Perform a case insensitive natural sort on group name then question title of a multidimensional array
	usort($arows, 'CompareGroupThenTitle'); 
	
	//3. SESSION VARIABLE - insertarray
	//An array containing information about used to insert the data into the db at the submit stage
	//4. SESSION VARIABLE - fieldarray
	//See rem at end..
	if ($thissurvey['private'] == "N")
		{
		$_SESSION['token'] = returnglobal('token');
		$_SESSION['insertarray'][]= "token";
		}
	
	if ($tokensexist == 1 && $thissurvey['private'] == "N") 
		{
		//Gather survey data for "non anonymous" surveys, for use in presenting questions
	    $_SESSION['thistoken']=getTokenData($surveyid, returnglobal('token'));
		}
	
	foreach ($arows as $arow)
		{
		//WE ARE CREATING A SESSION VARIABLE FOR EVERY FIELD IN THE SURVEY
		$fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";
		if ($arow['type'] == "M" || $arow['type'] == "A" || $arow['type'] == "B" || 
			$arow['type'] == "C" || $arow['type'] == "E" || $arow['type'] == "F" || 
			$arow['type'] == "H" || $arow['type'] == "P" || $arow['type'] == "^") 
			{
			$abquery = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other\n"
					 . "FROM {$dbprefix}answers, {$dbprefix}questions\n"
					 . "WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid\n"
					 . "AND sid=$surveyid AND {$dbprefix}questions.qid={$arow['qid']}\n"
					 . "ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			$abresult = mysql_query($abquery);
			while ($abrow = mysql_fetch_array($abresult))
				{
				$_SESSION['insertarray'][] = $fieldname.$abrow['code'];
				$alsoother = "";
				if ($abrow['other'] == "Y") {$alsoother = "Y";}
				if ($arow['type'] == "P")
					{
					$_SESSION['insertarray'][] = $fieldname.$abrow['code']."comment";	
					}
				}
			if ($alsoother) //Add an extra field for storing "Other" answers
				{
				$_SESSION['insertarray'][] = $fieldname."other";
				if ($arow['type'] == "P")
					{
					$_SESSION['insertarray'][] = $fieldname."othercomment";	
					}
				}
			} 
		elseif ($arow['type'] == "R") 
			{
			$abquery = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other\n"
					 . "FROM {$dbprefix}answers, {$dbprefix}questions\n"
					 . "WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid\n"
					 . "AND sid=$surveyid\n"
					 . "AND {$dbprefix}questions.qid={$arow['qid']}\n"
					 . "ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			$abresult = mysql_query($abquery) or die("ERROR:<br />".$abquery."<br />".mysql_error());
			$abcount = mysql_num_rows($abresult);
			for ($i=1; $i<=$abcount; $i++)
				{
				$_SESSION['insertarray'][] = "$fieldname".$i;
				}			
			}
		elseif ($arow['type'] == "Q")
			{
			$abquery = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other\n"
					 . "FROM {$dbprefix}answers, {$dbprefix}questions\n"
					 . "WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid\n"
					 . "AND sid=$surveyid\n"
					 . "AND {$dbprefix}questions.qid={$arow['qid']}\n"
					 . "ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			$abresult = mysql_query($abquery);
			while ($abrow = mysql_fetch_array($abresult))
				{
				$_SESSION['insertarray'][] = $fieldname.$abrow['code'];
				}
			}
		elseif ($arow['type'] == "O")	
			{
			$_SESSION['insertarray'][] = $fieldname;
			$fn2 = $fieldname."comment";
			$_SESSION['insertarray'][] = $fn2;
			}
		elseif ($arow['type'] == "L" || $arow['type'] == "!")
			{
			$_SESSION['insertarray'][] = $fieldname;
			if ($arow['other'] == "Y") { $_SESSION['insertarray'][] = $fieldname."other";}
			//go through answers, and if there is a default, register it now so that conditions work properly the first time
			$abquery = "SELECT {$dbprefix}answers.*\n"
					 . "FROM {$dbprefix}answers, {$dbprefix}questions\n"
					 . "WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid\n"
					 . "AND sid=$surveyid\n"
					 . "AND {$dbprefix}questions.qid={$arow['qid']}\n"
					 . "ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			$abresult = mysql_query($abquery);
			while($abrow = mysql_fetch_array($abresult))
				{
				if ($abrow['default_value'] == "Y") 
					{
				    $_SESSION[$fieldname] = $abrow['code'];
					}
				}
			}
		else
			{
			$_SESSION['insertarray'][] = $fieldname;
			}

		//Check to see if there are any conditions set for this question
		if (conditionscount($arow['qid']) > 0)
			{
			$conditions = "Y";
			}
		else
			{
			$conditions = "N";
			}

		//3(b) See if any of the insertarray values have been passed in the query URL
		foreach($_SESSION['insertarray'] as $field)
			{
			if (isset($_GET[$field]))
				{
				$_SESSION[$field]=$_GET[$field];
			    }
			}

		//4. SESSION VARIABLE: fieldarray
		//NOW WE'RE CREATING AN ARRAY CONTAINING EACH FIELD AND RELEVANT INFO
		//ARRAY CONTENTS - 	[0]=questions.qid, 
		//					[1]=fieldname, 
		//					[2]=questions.title, 
		//					[3]=questions.question
		//                 	[4]=questions.type, 
		//					[5]=questions.gid, 
		//					[6]=questions.mandatory, 
		//					[7]=conditionsexist
		$_SESSION['fieldarray'][] = array($arow['qid'], 
										  $fieldname, 
										  $arow['title'], 
										  $arow['question'], 
										  $arow['type'], 
										  $arow['gid'], 
										  $arow['mandatory'], 
										  $conditions);
		}
	return $totalquestions;
	}

function surveymover()
	{
	//This function creates the form elements in the survey navigation bar
	//with "<<PREV" or ">>NEXT" in them. MOdified by Mikkel Skovgaard Sørensen
	//so that the "submit" value which determines how the script moves from
	//one survey page to another is now a hidden element, updated by clicking
	//on the  relevant button - allowing "NEXT" to be the default setting when
	//a user presses enter.
	//
	//Attribute accesskey added for keyboard navigation.
	global $thissurvey;
	global $surveyid, $presentinggroupdescription;
	$surveymover = "";
	if (isset($_SESSION['step']) && $_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && !$presentinggroupdescription && $thissurvey['format'] != "A")
		{
		$surveymover = "<INPUT TYPE=\"hidden\" name=\"move\" value=\" ". _LAST." \" id=\"movelast\">";
		}
	else
		{
		$surveymover = "<INPUT TYPE=\"hidden\" name=\"move\" value=\" ". _NEXT." >> \" id=\"movenext\">";
		}
	if (isset($_SESSION['step']) && $_SESSION['step'] > 0 && $thissurvey['format'] != "A" && $thissurvey['allowprev'] != "N")
		{
		$surveymover .= "<input class='submit' accesskey='p' type='button' onclick=\"javascript:document.phpsurveyor.move.value = this.value; document.phpsurveyor.submit();\" value=' << "
					 . _PREV." ' name='move2' />\n";
		}
	if (isset($_SESSION['step']) && $_SESSION['step'] && (!$_SESSION['totalsteps'] || ($_SESSION['step'] < $_SESSION['totalsteps'])))
		{
		$surveymover .=  "\t\t\t\t\t<input class='submit' type='submit' accesskey='n' onclick=\"javascript:document.phpsurveyor.move.value = this.value;\" value=' "
					  . _NEXT." >> ' name='move2' />\n";
		}
	if (!isset($_SESSION['step']) || !$_SESSION['step'])
		{
		$surveymover .=  "\t\t\t\t\t<input class='submit' type='submit' accesskey='n' onclick=\"javascript:document.phpsurveyor.move.value = this.value;\" value=' "
					  . _NEXT." >> ' name='move2' />\n";
		}
	if (isset($_SESSION['step']) && $_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && $presentinggroupdescription == "yes")
		{
		$surveymover .=  "\t\t\t\t\t<input class='submit' type='submit' onclick=\"javascript:document.phpsurveyor.move.value = this.value;\" value=' "
					  . _NEXT." >> ' name='move2' />\n";
		}
	if ($_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && !$presentinggroupdescription && $thissurvey['format'] != "A")
		{
		$surveymover .= "\t\t\t\t\t<input class='submit' type='submit' accesskey='l' onclick=\"javascript:document.phpsurveyor.move.value = this.value;\" value=' "
					  . _LAST." ' name='move2' />\n";
		}
	if ($_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && !$presentinggroupdescription && $thissurvey['format'] == "A")
		{
		$surveymover .= "\t\t\t\t\t<input class='submit' type='submit' onclick=\"javascript:document.phpsurveyor.move.value = this.value;\" value=' "
					  . _SUBMIT." ' name='move2' />\n";
		}
	$surveymover .= "<input type='hidden' name='PHPSESSID' value='".session_id()."' id='PHPSESSID'>\n";
	return $surveymover;
	}

function doAssessment($surveyid) 
	{
	global $dbprefix, $thistpl;
	$query = "SELECT * FROM {$dbprefix}assessments
			  WHERE sid=$surveyid
			  ORDER BY scope";
	if ($result = mysql_query($query)) 
		{
	    if (mysql_num_rows($result) > 0) 
			{
			while ($row=mysql_fetch_array($result))
				{
				if ($row['scope'] == "G") 
					{
				    $assessment['group'][$row['gid']][]=array("name"=>$row['name'],
															"min"=>$row['minimum'],
															"max"=>$row['maximum'],
															"message"=>$row['message'],
															"link"=>$row['link']);
					}
				else
					{
				    $assessment['total'][]=array( "name"=>$row['name'],
												"min"=>$row['minimum'],
												"max"=>$row['maximum'],
												"message"=>$row['message'],
												"link"=>$row['link']);
					}
				}
			$fieldmap=createFieldMap($surveyid, "full");
			$i=0;
			$total=0;
			foreach($fieldmap as $field) 
				{
				if (($field['fieldname'] != "datestamp") and 
                                    ($field['fieldname'] != "ipaddr"))
					{
					$fieldmap[$i]['answer']=$_SESSION[$field['fieldname']];
					$groups[]=$field['gid'];
					$total=$total+$_SESSION[$field['fieldname']];
					$i++;
					}
				}
			
			$groups=array_unique($groups);
			
			foreach($groups as $group) 
				{
				$grouptotal=0;
				foreach ($fieldmap as $field) 
					{
						if ($field['gid'] == $group && isset($field['answer'])) 
							{
						    //$grouptotal=$grouptotal+$field['answer'];
							$grouptotal=$grouptotal+$_SESSION[$field['fieldname']];
							}
					}
				$subtotal[$group]=$grouptotal;
				}
	    	}
			$assessments = "";
			if (isset($subtotal) && is_array($subtotal)) 
				{
				foreach($subtotal as $key=>$val) 
					{
					if (isset($assessment['group'][$key])) 
						{
						foreach($assessment['group'][$key] as $assessed) 
							{
							if ($val >= $assessed['min'] && $val <= $assessed['max']) 
								{
							    $assessments .= "\t\t\t<!-- GROUP ASSESSMENT: Score: $total -->
	`							<table align='center'>
								 <tr>
								  <th>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $val), stripslashes($assessed['name']))."
								  </th>
								 </tr>
								 <tr>
								  <td align='center'>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $val), stripslashes($assessed['message']))."
								 </td>
								</tr>
							  	<tr>
								 <td align='center'><a href='".$assessed['link']."'>".$assessed['link']."</a>
								 </td>
								</tr>
							   </table><br />\n";
								}
							}
						}
					}
				}
			
			if (isset($assessment['total'])) 
				{
				foreach($assessment['total'] as $assessed) 
					{
					if ($total >= $assessed['min'] && $total <= $assessed['max']) 
						{
					    $assessments .= "\t\t\t<!-- TOTAL ASSESSMENT: Score: $total -->
						<table align='center'><tr><th>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $val), stripslashes($assessed['name']))."
						 </th></tr>
						 <tr>
						  <td align='center'>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $val), stripslashes($assessed['message']))."
						  </td>
						 </tr>
						 <tr>
						  <td align='center'><a href='".$assessed['link']."'>".$assessed['link']."</a>
						  </td>
						 </tr>
						</table>\n";
						}
					}
				}			
			
			return $assessments;
		}
	}
?>