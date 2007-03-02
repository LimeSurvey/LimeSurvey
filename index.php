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

// Performance optimized	: Nov 28, 2006
// Performance Improvement	: 41% (Call to templatereplace())
// Optimized By				: swales

require_once(dirname(__FILE__).'/config.php');
ini_set('session.gc_maxlifetime', 3000);
ini_set("session.bug_compat_warn", 0); //Turn this off until first "Next" warning is worked out

if (!isset($surveyid)) {	$surveyid=returnglobal('sid');}
//This next line is for security reasons. It ensures that the $surveyid value is never anything but a number.
if (_PHPVERSION >= '4.2.0') {settype($surveyid, "int");} else {settype($surveyid, "integer");}
session_start();

// Set the language of the survey, either from POST, GET parameter of session var
if (isset($_POST['lang']) && $_POST['lang']!='')  // this one comes from the language question
{
    $_POST['lang'] = preg_replace("/[^a-zA-Z0-9_]/", "", $_POST['lang']);
	if ($_POST['lang']) $clang = SetSurveyLanguage( $surveyid, $_POST['lang']);
	UpdateSessionGroupList();  // to refresh the language strings in the group list session variable
	UpdateFieldArray();        // to refresh question titles and question text 
} 
else 
if (isset($_GET['lang']))
{
	$_GET['lang'] = preg_replace("/[^a-zA-Z0-9_]/", "", $_GET['lang']);
	if ($_GET['lang']) $clang = SetSurveyLanguage( $surveyid, $_GET['lang']);
} 

if (isset($_SESSION['s_lang']))
{
	$clang = SetSurveyLanguage( $surveyid, $_SESSION['s_lang']);
} else {
	$baselang = GetBaseLanguageFromSurveyID($surveyid);
	$clang = SetSurveyLanguage( $surveyid, $baselang);
}


if ( $embedded_inc != '' )
require_once( $embedded_inc );


//DEFAULT SETTINGS FOR TEMPLATES
if (!$publicdir) {$publicdir=".";}
$tpldir="$publicdir/templates";

//CHECK FOR REQUIRED INFORMATION (sid)
if (!$surveyid)
{

	//A nice exit
	sendcacheheaders();
	doHeader();
//	$output=file("$tpldir/default/startpage.pstpl");
//	foreach($output as $op)
//	{
//		echo templatereplace($op);
//	}
	echo templatereplace(file_get_contents("$tpldir/default/startpage.pstpl"));
	echo "\t\t<center><br />\n"
	."\t\t\t<font color='RED'><strong>".$clang->gT("ERROR")."</strong></font><br />\n"
	."\t\t\t".$clang->gT("You have not provided a survey identification number")."<br />\n"
	."\t\t\t".$clang->gT("Please contact")." $siteadminname ( $siteadminemail ) ".$clang->gT("for further assistance")."\n"
	."\t\t</center><br />\n";
//	$output=file("$tpldir/default/endpage.pstpl");
//	foreach($output as $op)
//	{
//		echo templatereplace($op);
//	}
	echo templatereplace(file_get_contents("$tpldir/default/endpage.pstpl"));
	doFooter();
	exit;
}

//Check to see if a refering URL has been captured.
getreferringurl();

// Get token
if (!isset($token)) {$token=trim(returnglobal('token'));}

// If token was submitted from token form
if (isset($_GET['tokenSEC']) && $_GET['tokenSEC'] == 1)
{
	if (!isset($_GET['loadsecurity']) || $_GET['loadsecurity'] != $_SESSION['secanswer'])
	{
		$secerror = $clang->gT("The answer to security question is incorrect")."<br />\n";
		$_GET['token'] = "";
	}
}

if (isset($_GET['newtest']) && $_GET['newtest'] = "Y") unset($_GET['token']);

//GET BASIC INFORMATION ABOUT THIS SURVEY
$thissurvey=getSurveyInfo($surveyid, $_SESSION['s_lang']);

if (is_array($thissurvey)) {$surveyexists=1;} else {$surveyexists=0;}


//SEE IF SURVEY USES TOKENS
$i = 0; $tokensexist = 0;
$tablelist = $connect->MetaTables() or die ("Error getting tokens<br />".htmlspecialchars($connect->ErrorMsg()));
foreach ($tablelist as $tbl)
{
	if (db_quote_id($tbl) == db_table_name('tokens_'.$surveyid)) $tokensexist = 1;
}



//SET THE TEMPLATE DIRECTORY
if (!$thissurvey['templatedir']) {$thistpl=$tpldir."/default";} else {$thistpl=$tpldir."/{$thissurvey['templatedir']}";}
if (!is_dir($thistpl)) {$thistpl=$tpldir."/default";}



//MAKE SURE SURVEY HASN'T EXPIRED
if ($thissurvey['expiry'] < date("Y-m-d") && $thissurvey['useexpiry'] == "Y")
{
	sendcacheheaders();
	doHeader();
//	$output=file("$tpldir/default/startpage.pstpl");
//	foreach ($output as $op)
//	{
//		echo templatereplace($op);
//	}
	echo templatereplace(file_get_contents("$tpldir/default/startpage.pstpl"));
	echo "\t\t<center><br />\n"
	."\t\t\t".$clang->gT("This survey is no longer available.")."<br /><br />\n"
	."\t\t\t".$clang->gT("Please contact")." <i>{$thissurvey['adminname']}</i> (<i>{$thissurvey['adminemail']}</i>) "
	.$clang->gT("for further assistance")."<br /><br />\n";
//	$output=file("$tpldir/default/endpage.pstpl");
//	foreach ($output as $op)
//	{
//		echo templatereplace($op);
//	}
	echo templatereplace(file_get_contents("$tpldir/default/endpage.pstpl"));
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
//	$output=file("$tpldir/default/startpage.pstpl");
//	foreach($output as $op)
//	{
//		echo templatereplace($op);
//	}
	echo templatereplace(file_get_contents("$tpldir/default/startpage.pstpl"));
	echo "\t\t<center><br />\n"
	."\t\t\t<font color='RED'><strong>".$clang->gT("Error")."</strong></font><br />\n"
	."\t\t\t".$clang->gT("You have already completed this survey.")."<br /><br />\n"
	."\t\t\t".$clang->gT("Please contact")." <i>{$thissurvey['adminname']}</i> (<i>{$thissurvey['adminemail']}</i>) "
	.$clang->gT("for further assistance")."<br /><br />\n";
//	$output=file("$tpldir/default/endpage.pstpl");
//	foreach($output as $op)
//	{
//		echo templatereplace($op);
//	}
	echo templatereplace(file_get_contents("$tpldir/default/endpage.pstpl"));
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

//LOAD SAVED SURVEY
if (isset($_POST['loadall']) && $_POST['loadall'] == "reload")
{
	$errormsg="";
	// if (loadname is not set) or if ((loadname is set) and (loadname is NULL))
	if (!isset($_POST['loadname']) || (isset($_POST['loadname']) && ($_POST['loadname'] == null)))
	{
		$errormsg .= $clang->gT("You did not provide a name")."<br />\n";
	}
	// if (loadpass is not set) or if ((loadpass is set) and (loadpass is NULL))
	if (!isset($_POST['loadpass']) || (isset($_POST['loadpass']) && ($_POST['loadpass'] == null)))
	{
		$errormsg .= $clang->gT("You did not provide a password")."<br />\n";
	}

	// if security question asnwer is incorrect
	if (!isset($_POST['loadsecurity']) || $_POST['loadsecurity'] != $_SESSION['secanswer'])
	{
		$errormsg .= $clang->gT("The answer to security question is incorrect")."<br />\n";
	}
	
	// Load session before loading the values from the saved data
	if (isset($_GET['loadall']))
	{
		buildsurveysession();
	}

	// --> START NEW FEATURE - SAVE
	$_SESSION['holdname']=$_POST['loadname']; //Session variable used to load answers every page.
	$_SESSION['holdpass']=$_POST['loadpass']; //Session variable used to load answers every page.

	if ($errormsg == "") loadanswers();
	// <-- END NEW FEATURE - SAVE
	$_POST['move'] = "movenext";

	if ($errormsg)
	{
		$_POST['loadall'] = $clang->gT("Load Unfinished Survey");
	}
}
//Allow loading of saved survey
if (isset($_POST['loadall']) && $_POST['loadall'] == $clang->gT("Load Unfinished Survey"))
{
	require_once("load.php");
}


//Check if TOKEN is used for EVERY PAGE
//This function fixes a bug where users able to submit two surveys/votes
//by checking that the token has not been used at each page displayed.
if ($tokensexist == 1 && returnglobal('token'))
{
	//check if token actually does exist

	$tkquery = "SELECT COUNT(*) FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='".trim(returnglobal('token'))."' AND (completed = 'N' or completed='')";
	$tkresult = db_execute_num($tkquery);
	list($tkexist) = $tkresult->FetchRow();
	if (!$tkexist)
	{
		sendcacheheaders();
		doHeader();
		//TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT
//		foreach(file("$thistpl/startpage.pstpl") as $op)
//		{
//			echo templatereplace($op);
//		}
		echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
//		foreach(file("$thistpl/survey.pstpl") as $op)
//		{
//			echo "\t".templatereplace($op);
//		}
		echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));
		echo "\t<center><br />\n"
		."\t".$clang->gT("This is a controlled survey. You need a valid token to participate.")."<br /><br />\n"
		."\t".$clang->gT("The token you have provided is either not valid, or has already been used.")."\n"
		."\t".$clang->gT("For further information contact")." {$thissurvey['adminname']} "
		."(<a href='mailto:{$thissurvey['adminemail']}'>"
		."{$thissurvey['adminemail']}</a>)<br /><br />\n"
		."\t<a href='javascript: self.close()'>".$clang->gT("Close this Window")."</a><br />&nbsp;\n";
//		foreach(file("$thistpl/endpage.pstpl") as $op)
//		{
//			echo templatereplace($op);
//		}
		echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
		exit;
	}
}
//CLEAR SESSION IF REQUESTED
if (isset($_GET['move']) && $_GET['move'] == "clearall")
{
	$s_lang = $_SESSION['s_lang'];
	session_unset();
	session_destroy();
	sendcacheheaders();
	if (isset($_GET['redirect'])) {
		session_write_close();
		header("Location: {$_GET['redirect']}");
	}
	doHeader();
//	foreach(file("$thistpl/startpage.pstpl") as $op)
//	{
//		echo templatereplace($op);
//	}
	echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
	echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
	."\t<script type='text/javascript'>\n"
	."\t<!--\n"
	."\t\tfunction checkconditions(value, name, type)\n"
	."\t\t\t{\n"
	."\t\t\t}\n"
	."\t//-->\n"
	."\t</script>\n\n";

	//Present the clear all page using clearall.pstpl template
//	foreach(file("$thistpl/clearall.pstpl") as $op)
//	{
//		echo templatereplace($op);
//	}
	echo templatereplace(file_get_contents("$thistpl/clearall.pstpl"));

//	foreach(file("$thistpl/endpage.pstpl") as $op)
//	{
//		echo templatereplace($op);
//	}
	echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
	doFooter();
	exit;
}

if (isset($_GET['newtest']) && $_GET['newtest'] == "Y")
{
	session_unset();
	//DELETE COOKIE (allow to use multiple times)
	setcookie("$cookiename", "INCOMPLETE", time()-120);
	//echo "Reset Cookie!";
}



// SAVE POSTED ANSWERS TO DATABASE IF MOVE (NEXT,PREV,LAST, or SUBMIT) or RETURNING FROM SAVE FORM
if (isset($_POST['move']) || isset($_POST['saveprompt']))
{
	require_once("save.php");

	// RELOAD THE ANSWERS INCASE SOMEONE ELSE CHANGED THEM
	if ($thissurvey['active'] == "Y" && $thissurvey['allowsave'] == "Y") {
		loadanswers();
	}
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

if (isset($_POST['saveall']))
{
	print "<SCRIPT LANGUAGE='JavaScript'> alert('".$clang->gT("Survey Saved","js")."'); </SCRIPT>";
}

function loadanswers()
{
	global $dbprefix,$surveyid,$errormsg;
	global $thissurvey, $clang;

	if (isset($_POST['loadall']) && $_POST['loadall'] == "reload")
	{
		$query = "SELECT * FROM ".db_table_name('saved_control')." INNER JOIN {$thissurvey['tablename']}
			ON ".db_table_name('saved_control').".srid = {$thissurvey['tablename']}.id
			WHERE ".db_table_name('saved_control').".sid=$surveyid\n";
		if (isset($_POST['scid'])) //Would only come from email
		{
			$query .= "AND ".db_table_name('saved_control').".scid=".auto_escape($_POST['scid'])."\n";
		}
		$query .="AND ".db_table_name('saved_control').".identifier='".auto_escape($_SESSION['holdname'])."'
				  AND ".db_table_name('saved_control').".access_code='".md5(auto_unescape($_SESSION['holdpass']))."'\n";
	}
	elseif (isset($_SESSION['srid']))
	{
		$query = "SELECT * FROM {$thissurvey['tablename']}
			WHERE {$thissurvey['tablename']}.id=".$_SESSION['srid']."\n";
	}
	else
	{
		return;
	}
	$result = db_execute_assoc($query) or die ("Error loading results<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
	if ($result->RecordCount() < 1)
	{
		$errormsg .= $clang->gT("There is no matching saved survey")."<br />\n";
	}
	else
	{
		//A match has been found. Let's load the values!
		//If this is from an email, build surveysession first
		$row=$result->FetchRow();
		foreach ($row as $column => $value)
		{
			if ($column == "token")
			{
				$_POST['token']=$value;
				$token=$value;
			}
			if ($column == "saved_thisstep")
			{
				$_SESSION['step']=$value;
			}
			if ($column == "scid")
			{
				$_SESSION['scid']=$value;
			}
			if ($column == "srid")
			{
				$_SESSION['srid']=$value;
			}
			if ($column == "datestamp")
			{
				$_SESSION['datestamp']=$value;
			}
			else
			{
				//Only make session variables for those in insertarray[]
				if (in_array($column, $_SESSION['insertarray']))
				{
					$_SESSION[$column]=$value;
				}
			}
		} // foreach
	}
	return true;
}


function getTokenData($surveyid, $token)
{
	global $dbprefix, $connect;
	$query = "SELECT * FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='$token'";
	$result = db_execute_assoc($query) or die("Couldn't get token info in getTokenData()<br />".$query."<br />".htmlspecialchars($connect->ErrorMsg()));
	while($row=$result->FetchRow())
	{
		$thistoken=array("firstname"=>$row['firstname'],
		"lastname"=>$row['lastname'],
		"email"=>$row['email'],
		"language" =>$row['language'],
		"attribute_1"=>$row['attribute_1'],
		"attribute_2"=>$row['attribute_2']);
	} // while
	return $thistoken;
}

function makegraph($thisstep, $total)
{
	global $thissurvey;
	global $thistpl, $publicurl, $clang;
	$chart=$thistpl."/chart.jpg";
	if (!is_file($chart)) {$shchart="chart.jpg";}
	else {$shchart = "$publicurl/templates/{$thissurvey['templatedir']}/chart.jpg";}
	$graph = "<table class='graph' width='100' align='center' cellpadding='2'><tr><td>\n"
	. "<table width='180' align='center' cellpadding='0' cellspacing='0' border='0' class='innergraph'>\n"
	. "<tr><td align='right' width='40'>0%&nbsp;</td>\n";
	$size=intval(($thisstep-1)/$total*100);
	$graph .= "<td width='100' align='left'>\n"
	. "<table cellspacing='0' cellpadding='0' border='0' width='100%'>\n"
	. "<tr><td class='progressbar'>\n"
	. "<img src='$shchart' height='12' width='$size' align='left' alt='$size% ".$clang->gT("complete")."' />\n"
	. "</td></tr>\n"
	. "</table>\n"
	. "</td>\n"
	. "<td align='left' width='40'>&nbsp;100%</td></tr>\n"
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
	global $dbprefix, $connect;
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
			$query = "SELECT * FROM ".db_table_name('conditions')."\n"
			."WHERE qid=$cc[0] ORDER BY cqid";
			$result = db_execute_assoc($query) or die("Couldn't check conditions<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
			while($row=$result->FetchRow())
			{
				//Iterate through each condition for this question and check if it is met.
				$query2= "SELECT type, gid FROM ".db_table_name('questions')."\n"
				." WHERE qid={$row['cqid']} AND language='".$_SESSION['s_lang']."'";
				$result2=db_execute_assoc($query2) or die ("Coudn't get type from questions<br />$ccquery<br />".htmlspecialchars($connect->ErrorMsg()));
				while($row2=$result2->FetchRow())
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
	global $dbprefix, $connect;
	foreach ($_SESSION['fieldarray'] as $sfa)
	{
		if ($sfa[1] == $value && $sfa[7] == "Y" && isset($_SESSION[$value]) && $_SESSION[$value])
		{
			$currentcfield="";
			$query = "SELECT ".db_table_name('conditions').".*, ".db_table_name('questions').".type "
			. "FROM ".db_table_name('conditions').", ".db_table_name('questions')." "
			. "WHERE ".db_table_name('conditions').".cqid=".db_table_name('questions').".qid "
			. "AND ".db_table_name('conditions').".qid=$sfa[0] "
			. "ORDER BY ".db_table_name('conditions').".qid";
			$result=db_execute_assoc($query) or die($query."<br />".htmlspecialchars($connect->ErrorMsg()));
			while($rows = $result->FetchRow())
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
	global $clang;
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
						if (isset($_POST['move']) && $_POST['move'] == "moveprev") {$_SESSION['step'] = $_POST['thisstep'];}
						if (isset($_POST['move']) && $_POST['move'] == "movenext") {$_SESSION['step'] = $_POST['thisstep'];}
						if (isset($_POST['move']) && $_POST['move'] == "movelast") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == "movenext";}
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
			$dtcm = "tbdisp$cm";
			if (isset($_SESSION[$cm]) && ($_SESSION[$cm] == "0" || $_SESSION[$cm]))
			{
			}
			elseif ((!isset($_POST[$multiname]) || !$_POST[$multiname]) && (!isset($_POST[$dtcm]) || $_POST[$dtcm] == "on"))
			{
				//One of the mandatory questions hasn't been asnwered
				if (isset($_POST['move']) && $_POST['move'] == "moveprev") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == "movenext") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == "movelast") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == "movenext";}
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
				if (isset($_POST['move']) && $_POST['move'] == "moveprev") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == "movenext") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == "movelast") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == "movenext";}
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
						if (isset($_POST['move']) && $_POST['move'] == "moveprev") {$_SESSION['step'] = $_POST['thisstep'];}
						if (isset($_POST['move']) && $_POST['move'] == "movenext") {$_SESSION['step'] = $_POST['thisstep'];}
						if (isset($_POST['move']) && $_POST['move'] == "movelast") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == "movenext";}
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
			$dtccm = "tbdisp$ccm";
			if (isset($_SESSION[$ccm]) && ($_SESSION[$ccm] == "0" || $_SESSION[$ccm]) && isset($_POST[$dccm]) && $_POST[$dccm] == "on") //There is an answer
			{
				//The question has an answer, and the answer was displaying
			}
			elseif ((isset($_POST[$dccm]) && $_POST[$dccm] == "on") && (!isset($_POST[$multiname]) || !$_POST[$multiname]) && (!isset($_POST[$dtccm]) || $_POST[$dtccm] == "on")) // Question and Answers is on, there is no answer, but it's a multiple
			{
				if (isset($_POST['move']) && $_POST['move'] == "moveprev") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == "movenext") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == "movelast") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == "movenext";}
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
				if (isset($_POST['move']) && $_POST['move'] == "moveprev") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == "movenext") {$_SESSION['step'] = $_POST['thisstep'];}
				if (isset($_POST['move']) && $_POST['move'] == "movelast") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == "movenext";}
				$notanswered[]=substr($multiname, 5, strlen($multiname));
			}
		}
	}
	if (!isset($notanswered)) {return false;}//$notanswered=null;}
	return $notanswered;
}

function checkpregs($backok=null)
{
	global $connect;
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
					."FROM ".db_table_name('questions')."\n"
					."WHERE qid=".$fieldinfo['qid']." "
					. "AND language='".$_SESSION['s_lang']."'";
					$pregresult=db_execute_assoc($pregquery) or die("ERROR: $pregquery<br />".htmlspecialchars($connect->ErrorMsg()));
					while($pregrow=$pregresult->FetchRow())
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
			if (isset($_POST['move']) && $_POST['move'] == "moveprev") {$_SESSION['step'] = $_POST['thisstep'];}
			if (isset($_POST['move']) && $_POST['move'] == "movenext") {$_SESSION['step'] = $_POST['thisstep'];}
			if (isset($_POST['move']) && $_POST['move'] == "movelast") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == "movenext";}
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

function submittokens()
{
	global $thissurvey;
	global $dbprefix, $surveyid, $connect;
	global $sitename, $thistpl;

	// Put date into sent and completed
	
	$today = date("Y-m-d");
	$utquery = "UPDATE {$dbprefix}tokens_$surveyid\n"
	. "SET completed='$today'\n"

	. "WHERE token='{$_POST['token']}'";
	$utresult = $connect->Execute($utquery) or die ("Couldn't update tokens table!<br />\n$utquery<br />\n".htmlspecialchars($connect->ErrorMsg()));

	// TLR change to put date into sent and completed
	$cnfquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." WHERE token='{$_POST['token']}' AND completed!='N' AND completed!=''";
  
	$cnfresult = db_execute_assoc($cnfquery);
	while ($cnfrow = $cnfresult->FetchRow())
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
			global $currentadminlang, $homedir, $homeurl;
			$langdir="$homeurl/lang/$currentadminlang";
			if (!is_dir($langdir2))
			{
				$langdir="$homeurl/lang/english"; //default to english if there is no matching language dir
			}
			$message = $clang->gT("Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}");
		}

		$message=Replacefields($message, $fieldsarray);

		//Only send confirmation email if there is a valid email address
		if (validate_email($cnfrow['email'])) {MailTextMessage($message, $subject, $to, $from, $sitename);}
	}
}

function sendsubmitnotification($sendnotification)
{
	global $thissurvey;
	global $dbprefix, $clang;
	global $sitename, $homeurl, $surveyid, $publicurl;

	$subject = "$sitename Survey Submitted";

	$message = $clang->gT("Survey Submitted")." - {$thissurvey['name']}\r\n"
	. $clang->gT("A new response was entered for your survey")."\r\n\r\n";
	if ($thissurvey['allowsave'] == "Y" && isset($_SESSION['scid']))
	{
		$message .= $clang->gT("Click the following link to reload the survey:")."\r\n";
		$message .= "  $publicurl/index.php?sid=$surveyid&loadall=reload&scid=".$_SESSION['scid']."&loadname=".urlencode($_SESSION['holdname'])."&loadpass=".urlencode($_SESSION['holdpass'])."\r\n\r\n";
	}

	$message .= $clang->gT("Click the following link to see the individual response:")."\r\n"
	. "  $homeurl/admin.php?action=browse&sid=$surveyid&subaction=id&id=".$_SESSION['srid']."\r\n\r\n"
	// Add link to edit individual responses from notification email
	. $clang->gT("Click the following link to edit the individual response:")."\r\n"

	. "  $homeurl/admin.php?action=dataentry&sid=$surveyid&subaction=edit&surveytable=survey_$surveyid&id=".$_SESSION['srid']."\r\n\r\n"
	. $clang->gT("View statistics by clicking here:")."\r\n"
	. "  $homeurl/admin.php?action=statistics&sid=$surveyid\r\n\r\n";
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
	$from = $thissurvey['adminname'].' <'.$thissurvey['adminemail'].'>';

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
	global $thistpl, $subquery, $surveyid, $connect;
	sendcacheheaders();
	doHeader();
	echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
	$completed = "<br /><strong><font size='2' color='red'>"
	. $clang->gT("Did Not Save")."</strong></font><br /><br />\n\n"
	. $clang->gT("An unexpected error has occurred and your responses cannot be saved.")."<br /><br />\n";
	if ($thissurvey['adminemail'])
	{
		$completed .= $clang->gT("Your responses have not been lost and have been emailed to the survey administrator and will be entered into our database at a later point.")."<br /><br />\n";
		$email=$clang->gT("An error occurred saving a response to survey id")." ".$thissurvey['name']." - $surveyid\n\n";
		$email .= $clang->gT("DATA TO BE ENTERED").":\n";
		foreach ($_SESSION['insertarray'] as $value)
		{
			$email .= "$value: {$_SESSION[$value]}\n";
		}
		$email .= "\n".$clang->gT("SQL CODE THAT FAILED").":\n"
		. "$subquery\n\n"
		. $clang->gT("ERROR MESSAGE").":\n"
		. $connect->ErrorMsg()."\n\n";
		MailTextMessage($email, _DNSAVEEMAIL5, $thissurvey['adminemail'], $thissurvey['adminemail'], "PHPSurveyor");
		echo "<!-- EMAIL CONTENTS:\n$email -->\n";
		//An email has been sent, so we can kill off this session.
		session_unset();
		session_destroy();
	}
	else
	{
		$completed .= "<a href='javascript:location.reload()'>".$clang->gT("Try to submit again")."</a><br /><br />\n";
		$completed .= $subquery;
	}
	return $completed;
}

function buildsurveysession()
{
	// Performance optimized	: Nov 22, 2006
	// Performance Improvement	: 17%
	// Optimized By				: swales

	global $thissurvey, $secerror;
	global $tokensexist, $thistpl;
	global $surveyid, $dbprefix, $connect;
	global $register_errormsg, $clang;

	//This function builds all the required session variables when a survey is first started.
	//It is called from each various format script (ie: group.php, question.php, survey.php)
	//if the survey has just begun. This funcion also returns the variable $totalquestions.

	//BEFORE BUILDING A NEW SESSION FOR THIS SURVEY, LET'S CHECK TO MAKE SURE THE SURVEY SHOULD PROCEED!
	if ($tokensexist == 1 && !returnglobal('token'))
	{
		sendcacheheaders();
		doHeader();
		//NO TOKEN PRESENTED. EXPLAIN PROBLEM AND PRESENT FORM

		echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
		echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));
		if (isset($thissurvey) && $thissurvey['allowregister'] == "Y")
		{
			echo templatereplace(file_get_contents("$thistpl/register.pstpl"));
		}
		else
		{
?>
	<center><br />
	<?php if (isset($secerror)) echo "<font color='#FF0000'>".$secerror."</font><br />"; ?>
	<?php echo $clang->gT("This is a controlled survey. You need a valid token to participate.") ?><br /><br />
	<?php echo $clang->gT("If you have been issued with a token, please enter it in the box below and click continue.") ?><br />&nbsp;
	<form method='get' action='<?php echo $_SERVER['PHP_SELF'] ?>'>
	<table align='center'>
		<tr>
			<td align='right' valign='middle'>
			<input type='hidden' name='sid' value='<?php echo $surveyid ?>' id='sid' />
			<input type='hidden' name='tokenSEC' value='1' id='sid' />
			<?php echo $clang->gT("Token") ?>:</td><td align='left' valign='middle'><input class='text' type='text' name='token'>
			</td>
		</tr>
		<tr>
			<td align='center' valign='middle'>
			<?php echo $clang->gT("Security Question"); ?>:</td><td align='left' valign='middle'><table><tr><td valign='center'><img src='images/verification.php'></td><td valign='center'><input type='text' size='5' maxlength='3' name='loadsecurity' value=''></td></tr></table>
			</td>
		</tr>
		<tr><td colspan="2" align="center"><input class='submit' type='submit' value='<?php echo $clang->gT("Continue") ?>' /></td></tr>
	</table>
	</form>
	<br />&nbsp;</center>
<?php
		}

		echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
		exit;
	}
	//Tokens are required, and a token has been provided.
	elseif ($tokensexist == 1 && returnglobal('token'))
	{
		//check if token actually does exist
		$tkquery = "SELECT COUNT(*) FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='".trim(returnglobal('token'))."' AND (completed = 'N' or completed='')";
		$tkresult = db_execute_num($tkquery);
		list($tkexist) = $tkresult->FetchRow();
		if (!$tkexist)
		{
			sendcacheheaders();
			doHeader();
			//TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

			echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

			echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));
			echo "\t<center><br />\n"
			."\t".$clang->gT("This is a controlled survey. You need a valid token to participate.")."<br /><br />\n"
			."\t".$clang->gT("The token you have provided is either not valid, or has already been used.")."\n"
			."\t".$clang->gT("For further information contact")." {$thissurvey['adminname']} "
			."(<a href='mailto:{$thissurvey['adminemail']}'>"
			."{$thissurvey['adminemail']}</a>)<br /><br />\n"
			."\t<a href='javascript: self.close()'>".$clang->gT("Close this Window")."</a><br />&nbsp;\n";

			echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
			exit;
		}
	}

	//RESET ALL THE SESSION VARIABLES AND START AGAIN
	unset($_SESSION['grouplist']);
	unset($_SESSION['fieldarray']);
	unset($_SESSION['insertarray']);
	unset($_SESSION['thistoken']);


	//RL: multilingual support

	if (isset($_GET['token'])){
	//get language from token (if one exists)
		$tkquery2 = "SELECT * FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='".trim(returnglobal('token'))."' AND (completed = 'N' or completed='')";
		//echo $tkquery2;
		$result = db_execute_assoc($tkquery2) or die ("Couldn't get tokens<br />$tkquery<br />".htmlspecialchars($connect->ErrorMsg()));
		while ($rw = $result->FetchRow())
		{
			$tklanguage=$rw['language'];
		}
	}
	if (returnglobal('lang')) { $language_to_set=returnglobal('lang');
		} elseif (isset($tklanguage)) { $language_to_set=$tklanguage;}
		else {$language_to_set = $thissurvey['language'];}

	if (!isset($_SESSION['s_lang'])) {
		SetSurveyLanguage($surveyid, $language_to_set);
	}


UpdateSessionGroupList();



// Optimized Query
	// Change query to use sub-select to see if conditions exist.
	$query = "SELECT ".db_table_name('questions').".*, ".db_table_name('groups').".*,\n"
	." (SELECT count(1) FROM ".db_table_name('conditions')."\n"
	." WHERE ".db_table_name('questions').".qid = ".db_table_name('conditions').".qid) AS hasconditions\n"
    ." FROM ".db_table_name('groups')." INNER JOIN ".db_table_name('questions')." ON ".db_table_name('groups').".gid = ".db_table_name('questions').".gid\n"
    ." WHERE ".db_table_name('questions').".sid=".$surveyid."\n"
    ." AND ".db_table_name('groups').".language='".$_SESSION['s_lang']."'\n"
    ." AND ".db_table_name('questions').".language='".$_SESSION['s_lang']."'\n"
    ." ORDER BY ".db_table_name('groups').".group_order,".db_table_name('questions').".question_order";

 //var_dump($_SESSION);
//	echo $query."<br>";
	$result = db_execute_assoc($query);

	$arows = $result->GetRows();

	$totalquestions = $result->RecordCount();

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
		echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
		echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));
		echo "\t<center><br />\n"
		."\t".$clang->gT("This survey does not yet have any questions and cannot be tested or completed.")."<br /><br />\n"
		."\t".$clang->gT("For further information contact")." {$thissurvey['adminname']}"
		." (<a href='mailto:{$thissurvey['adminemail']}'>"
		."{$thissurvey['adminemail']}</a>)<br /><br />\n"
		."\t<a href='javascript: self.close()'>".$clang->gT("Close this Window")."</a><br />&nbsp;\n";

		echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
		exit;
	}

	//Perform a case insensitive natural sort on group name then question title of a multidimensional array
//	usort($arows, 'CompareGroupThenTitle');

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

// Optimized Query
			$abquery = "SELECT ".db_table_name('answers').".code, ".db_table_name('questions').".other\n"
			. " FROM ".db_table_name('answers')."\n"
			. " INNER JOIN ".db_table_name('questions')."\n"
			. " ON ".db_table_name('answers').".qid=".db_table_name('questions').".qid\n"
			. " WHERE ".db_table_name('questions').".sid=$surveyid\n"
			. " AND ".db_table_name('questions').".qid={$arow['qid']}\n"
			. " AND ".db_table_name('questions').".language='".$_SESSION['s_lang']."' \n"
			. " AND ".db_table_name('answers').".language='".$_SESSION['s_lang']."' \n"
			. " ORDER BY ".db_table_name('answers').".sortorder, ".db_table_name('answers').".answer";

			$abresult = db_execute_assoc($abquery);
			while ($abrow = $abresult->FetchRow())
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


// Optimized Query
			$abquery = "SELECT ".db_table_name('answers').".code, ".db_table_name('questions').".other\n"
			. " FROM ".db_table_name('answers')."\n"
			. " INNER JOIN ".db_table_name('questions')."\n"
			. " ON ".db_table_name('answers').".qid=".db_table_name('questions').".qid\n"
			. " WHERE ".db_table_name('questions').".sid=$surveyid\n"
			. " AND ".db_table_name('questions').".qid={$arow['qid']}\n"
			. " AND ".db_table_name('questions').".language='".$_SESSION['s_lang']."' \n"
			. " AND ".db_table_name('answers').".language='".$_SESSION['s_lang']."' \n"
			. " ORDER BY ".db_table_name('answers').".sortorder, ".db_table_name('answers').".answer";

			$abresult = $connect->Execute($abquery) or die("ERROR:<br />".$abquery."<br />".htmlspecialchars($connect->ErrorMsg()));
			$abcount = $abresult->RecordCount();
			for ($i=1; $i<=$abcount; $i++)
			{
				$_SESSION['insertarray'][] = "$fieldname".$i;
			}
		}


		elseif ($arow['type'] == "Q" || $arow['type'] == "J" )
		{

// Optimized Query
			$abquery = "SELECT ".db_table_name('answers').".code\n"
			. " FROM ".db_table_name('answers')."\n"
			. " WHERE ".db_table_name('answers').".qid={$arow['qid']}\n"
			. " ORDER BY ".db_table_name('answers').".sortorder, ".db_table_name('answers').".answer";

			$abresult = db_execute_assoc($abquery);
			while ($abrow = $abresult->FetchRow())
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

			$abquery = "SELECT a.code, a.default_value\n"
			. " FROM ".db_table_name('answers')." as a \n"
			. " WHERE a.qid={$arow['qid']}\n"
			. " ORDER BY a.sortorder, a.answer";

			$abresult = db_execute_assoc($abquery);
			while($abrow = $abresult->FetchRow())
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



//		Separate query for each row no necessary because query above includes a sub-select now.
//		Increases performance by at least 17% and reduces number of queries executed
//		//Check to see if there are any conditions set for this question


		if ($arow['hasconditions']>0)
		{
			$conditions = "Y";
		}
		else
		{
			$conditions = "N";
		}


		//3(b) See if any of the insertarray values have been passed in the query URL

		if (isset($_SESSION['insertarray']))
		{foreach($_SESSION['insertarray'] as $field)
		{
			if (isset($_GET[$field]))
			{
				$_SESSION[$field]=$_GET[$field];
			}
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
	// Check if the current survey language is set - if not set it
	// this way it can be changed later (for example by a special question type)

	return $totalquestions;
}

function surveymover()
{
	//This function creates the form elements in the survey navigation bar
	//with "<<PREV" or ">>NEXT" in them. The "submit" value determines how the script moves from
	//one survey page to another. It is a hidden element, updated by clicking
	//on the  relevant button - allowing "NEXT" to be the default setting when
	//a user presses enter.
	//
	//Attribute accesskey added for keyboard navigation.
	global $thissurvey, $clang;
	global $surveyid, $presentinggroupdescription;
	$surveymover = "";
	if (isset($_SESSION['step']) && $_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && !$presentinggroupdescription && $thissurvey['format'] != "A")
	{
		$surveymover = "<INPUT TYPE=\"hidden\" name=\"move\" value=\"movelast\" id=\"movelast\" />";
	}
	else
	{
		$surveymover = "<INPUT TYPE=\"hidden\" name=\"move\" value=\"movenext\" id=\"movenext\" />";
	}
	
	
	if (isset($_SESSION['step']) && $_SESSION['step'] > 0 && $thissurvey['format'] != "A" && $thissurvey['allowprev'] != "N")
	{
		$surveymover .= "<input class='submit' accesskey='p' type='button' onclick=\"javascript:document.phpsurveyor.move.value = 'moveprev'; document.phpsurveyor.submit();\" value=' << "
		. $clang->gT("prev")." ' name='move2' />\n";
	}
	if (isset($_SESSION['step']) && $_SESSION['step'] && (!$_SESSION['totalsteps'] || ($_SESSION['step'] < $_SESSION['totalsteps'])))
	{
		$surveymover .=  "\t\t\t\t\t<input class='submit' type='submit' accesskey='n' onclick=\"javascript:document.phpsurveyor.move.value = 'movenext';\" value=' "
		. $clang->gT("next")." >> ' name='move2' />\n";
	}
	if (!isset($_SESSION['step']) || !$_SESSION['step'])
	{
		$surveymover .=  "\t\t\t\t\t<input class='submit' type='submit' accesskey='n' onclick=\"javascript:document.phpsurveyor.move.value = 'movenext';\" value=' "
		. $clang->gT("next")." >> ' name='move2' />\n";
	}
	if (isset($_SESSION['step']) && $_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && $presentinggroupdescription == "yes")
	{
		$surveymover .=  "\t\t\t\t\t<input class='submit' type='submit' onclick=\"javascript:document.phpsurveyor.move.value = 'movenext';\" value=' "
		. $clang->gT("next")." >> ' name='move2' />\n";
	}
	if ($_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && !$presentinggroupdescription && $thissurvey['format'] != "A")
	{
		$surveymover .= "\t\t\t\t\t<input class='submit' type='submit' accesskey='l' onclick=\"javascript:document.phpsurveyor.move.value = 'movelast';\" value=' "
		. $clang->gT("last")." ' name='move2' />\n";
	}
	if ($_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && !$presentinggroupdescription && $thissurvey['format'] == "A")
	{
		$surveymover .= "\t\t\t\t\t<input class='submit' type='submit' onclick=\"javascript:document.phpsurveyor.move.value = 'movesubmit';\" value=' "
		. $clang->gT("submit")." ' name='move2' />\n";
	}
	$surveymover .= "<input type='hidden' name='PHPSESSID' value='".session_id()."' id='PHPSESSID' />\n";
	return $surveymover;
}

function doAssessment($surveyid)
{
	global $dbprefix, $thistpl, $connect;
	$query = "SELECT * FROM ".db_table_name('assessments')."
			  WHERE sid=$surveyid
			  ORDER BY scope";
	if ($result = db_execute_assoc($query))
	{
		if ($result->RecordCount() > 0)
		{
			while ($row=$result->FetchRow())
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

function UpdateSessionGroupList()
//1. SESSION VARIABLE: grouplist
//A list of groups in this survey, ordered by group name.
{
   global $surveyid;
    unset ($_SESSION['grouplist']);
	$query = "SELECT * FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='".$_SESSION['s_lang']."' ORDER BY ".db_table_name('groups').".group_order";
	$result = db_execute_assoc($query) or die ("Couldn't get group list<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
	while ($row = $result->FetchRow())
	{
		$_SESSION['grouplist'][]=array($row['gid'], $row['group_name'], $row['description']);
	}
}

function UpdateFieldArray()
//The FieldArray contains all necessary information regarding the questions
//This function is needed to update it in case the survey is switched to another language
// Todo: Test this!
{
    global $surveyid;

    if (isset($_SESSION['fieldarray'])) 
    {
    	reset($_SESSION['fieldarray']);
    	while ( list($key) = each($_SESSION['fieldarray']) )
    	{
      		$questionarray =& $myArray[$key];
   	  		$questionarray[2]=$row['title'];
  	  		$questionarray[3]=$row['question'];
      		unset($questionarray);
    	}
    }

// This seems to only work in PHP 5 because of the referenced (&) array in the foreach construct
/*    foreach($_SESSION['fieldarray'] as &$questionarray) 
    {
       	$query = "SELECT * FROM ".db_table_name('questions')." WHERE qid=".$questionarray[0]." AND language='".$_SESSION['s_lang']."'";
    	$result = db_execute_assoc($query) or die ("Couldn't get question <br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
    	while ($row = $result->FetchRow())
    	{
    		$questionarray[2]=$row['title'];
    		$questionarray[3]=$row['question'];
    	}
    }
    */
}


?>
