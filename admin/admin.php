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

require_once(dirname(__FILE__).'/../config.php');  // config.php itself includes common.php

if (!isset($adminlang)) {$adminlang=returnglobal('adminlang');}              //??
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}  //SurveyID
if (!isset($ugid)) {$ugid=returnglobal('ugid');}         //Usergroup-ID
if (!isset($gid)) {$gid=returnglobal('gid');}            //GroupID
if (!isset($qid)) {$qid=returnglobal('qid');}            //QuestionID
if (!isset($lid)) {$lid=returnglobal('lid');}            //LabelID
if (!isset($code)) {$code=returnglobal('code');}         // ??
if (!isset($action)) {$action=returnglobal('action');}   //Desired action
if (!isset($subaction)) {$subaction=returnglobal('subaction');}//Desired sibaction
if (!isset($ok)) {$ok=returnglobal('ok');}               // ??
if (!isset($fp)) {$fp=returnglobal('filev');}                 //??
if (!isset($elem)) {$elem=returnglobal('elem');}              //??

$adminoutput='';

include_once("login_check.php");
  

if ($action == "activate")
	{
	$surquery = "SELECT activate_survey FROM {$dbprefix}surveys_rights WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
	$surresult = $connect->Execute($surquery) or die($connect->ErrorMsg());		
	$surrows = $surresult->FetchRow();

	if($surrows['activate_survey'])
		{
		include("activate.php");
		}
	else
		{
		include("access_denied.php");		
		}	
	}
	
if ($action == "deactivate")
{
	$surquery = "SELECT activate_survey FROM {$dbprefix}surveys_rights WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
	$surresult = $connect->Execute($surquery) or die($connect->ErrorMsg());		
	$surrows = $surresult->FetchRow();

	if($surrows['activate_survey'])
		{
		include("deactivate.php");
		}
	else
		{
		include("access_denied.php");		
		}
}

if ($action == "importsurvey")
	{
	if($_SESSION['USER_RIGHT_CREATE_SURVEY'])
		{
		include("importsurvey.php");
		}
	else
		{
		include("access_denied.php");		
		}
	}



if ($action == "importgroup")
	{
	/*$surquery = "SELECT define_questions FROM {$dbprefix}surveys_rights WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
	$surresult = $connect->Execute($surquery) or die($connect->ErrorMsg());		
	$surrows = $surresult->FetchRow();

	if($surrows['define_questions'])
		{*/
		include("importgroup.php");
		/*}
	else
		{
		include("access_denied.php");		
		}*/
	
	}
if ($action == "importquestion")
	{
	/*$surquery = "SELECT define_questions FROM {$dbprefix}surveys_rights WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
	$surresult = $connect->Execute($surquery) or die($connect->ErrorMsg());		
	$surrows = $surresult->FetchRow();

	if($surrows['define_questions'])
		{*/
		include("importquestion.php");
		/*}
	else
		{
		include("access_denied.php");		
		}*/
	}



//CHECK THAT SURVEYS MARKED AS ACTIVE ACTUALLY HAVE MATCHING TABLES
//    checkactivations();

if(isset($_SESSION['loginID']) && $action!='login')
{
  //VARIOUS DATABASE OPTIONS/ACTIONS PERFORMED HERE
  if ($action == "delsurvey"         || $action == "delgroup"       || $action == "delgroupall" ||
      $action == "delquestion"       || $action =="delquestionall"  || $action == "insertnewsurvey" ||
      $action == "copynewquestion"   || $action == "insertnewgroup" || $action == "insertCSV" ||
      $action == "insertnewquestion" || $action == "updatesurvey"   || $action == "updatesurvey2" || $action=="updategroup" ||
      $action == "updatequestion"    || $action == "modanswer"      || $action == "renumberquestions" ||
      $action == "delattribute"      || $action == "addattribute"   || $action == "editattribute")
  {
  	include("database.php");
  }

  if ($action=="exportresults")  { include("exportresults.php"); }
  else  
  if ($action=="statistics")  {	include("statistics.php"); }
  else  
  if ($action=="dataentry")  {	include("dataentry.php"); }
  else  
  if ($action=="browse")  {	include("browse.php"); }
  else  
  if ($action=="tokens")  {	include("tokens.php"); }
  else  
  if ($action=="showprintablesurvey")  {	include("printablesurvey.php"); }
  else  
  if ($action=="checkintegrity")  {	include("integritycheck.php"); }
  else
  if ($action=="labels" || $action=="newlabelset" || $action=="insertlabelset" ||
      $action=="deletelabelset" || $action=="editlabelset" || $action=="modlabelsetanswers") { include("labels.php");}
  else    
  if ($action=="templates" || $action=="templatecopy" || $action=="templatesavechanges" || $action=="templaterename"
      || $action=="templateupload" || $action=="templatefiledelete" || $action=="templatezip")  {	include("templates.php"); }
  else    
  if ($action=="assessments" || $action=="assessmentdelete" || $action=="assessmentedit" || $action=="foo"
      || $action=="assessmentadd" || $action=="assessmentupdate" || $action=="foo")  {	include("assessments.php"); }
  else    
  if ($surveyid || $action=="listurveys" || $action=="checksettings" || $action=="changelang" || $action=="adduser" || 
      $action=="deluser" || $action=="moduser" || $action=="userrights" || $action=="modifyuser" ||
      $action=="editusers" || $action=="addusergroup" || $action=="editusergroup" || $action=="mailusergroup" ||
      $action=="delusergroup" || $action=="usergroupindb" || $action=="mailsendusergroup" || $action=="editusergroupindb" ||
      $action=="addquestion" || $action=="copyquestion" || $action=="editquestion"  || $action=="editusergroups" ||
      $action=="editattribute" || $action=="delattribute" || $action=="addattribute" || $action=="deleteuserfromgroup" ||
      $action=="editsurvey" || $action=="updatesurvey" || $action=="ordergroups" || $action=="addusertogroup" ||
      $action=="uploadf" || $action=="newsurvey" || $action=="listsurveys" ||
      $action=="addgroup" || $action=="editgroup" || $action=="surveyrights" ) include("html.php");
// echo $action.$subaction;
  if (!isset($printablesurveyoutput) && $subaction!='export' )   // For a few actions we dont want to have the header
    {  
    if (!isset($_SESSION['metaHeader'])) {$_SESSION['metaHeader']='';}
    $adminoutput = getAdminHeader($_SESSION['metaHeader']).$adminoutput;  // Alle future output is written into this and then outputted at the end of file
    $_SESSION['metaHeader']='';    
    $adminoutput .= "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n"
    ."\t<tr>\n"
    ."\t\t<td valign='top' align='center' bgcolor='#BBBBBB'>\n";
    }

  
  


  
  // For some output we dont want to have the standard admin menu bar
  if (!isset($labelsoutput)  && !isset($templatesoutput) && !isset($printablesurveyoutput) && 
      !isset($assessmentsoutput) && !isset($tokenoutput) && !isset($browseoutput) &&
      !isset($dataentryoutput) && !isset($statisticsoutput)&& !isset($exportoutput)) 
      {
        $adminoutput.= showadminmenu();
      }
    
  
  if (isset($templatesoutput)) {$adminoutput.= $templatesoutput;}
  if (isset($accesssummary  )) {$adminoutput.= $accesssummary;}	// added by Dennis
  if (isset($surveysummary  )) {$adminoutput.= $surveysummary;}
  if (isset($usergroupsummary)){$adminoutput.= $usergroupsummary;}
  if (isset($usersummary    )) {$adminoutput.= $usersummary;}
  if (isset($logoutsummary  )) {$adminoutput.= $logoutsummary;}	// added by Dennis
  if (isset($groupsummary   )) {$adminoutput.= $groupsummary;}
  if (isset($questionsummary)) {$adminoutput.= $questionsummary;}
  if (isset($vasummary      )) {$adminoutput.= $vasummary;}
  if (isset($addsummary     )) {$adminoutput.= $addsummary;}
  if (isset($answersummary  )) {$adminoutput.= $answersummary;}
  if (isset($cssummary      )) {$adminoutput.= $cssummary;}
  
  if (isset($editgroup)) {$adminoutput.= $editgroup;}
  if (isset($editquestion)) {$adminoutput.= $editquestion;}
  if (isset($editsurvey)) {$adminoutput.= $editsurvey;}
  if (isset($labelsoutput)) {$adminoutput.= $labelsoutput;}
  if (isset($listsurveys)) {$adminoutput.= $listsurveys; }
  if (isset($integritycheck)) {$adminoutput.= $integritycheck;}
  if (isset($ordergroups)){$adminoutput.= $ordergroups;}
  if (isset($orderquestions)) {$adminoutput.= $orderquestions;}
  if (isset($surveysecurity)) {$adminoutput.= $surveysecurity;}
  if (isset($newsurvey)) {$adminoutput.= $newsurvey;}
  if (isset($newgroup)) {$adminoutput.= $newgroup;}
  if (isset($newquestion)) {$adminoutput.= $newquestion;}
  if (isset($newanswer)) {$adminoutput.= $newanswer;}
  if (isset($editanswer)) {$adminoutput.= $editanswer;}
  if (isset($assessmentsoutput)) {$adminoutput.= $assessmentsoutput;}
  if (isset($importsurvey)) {$adminoutput.= $importsurvey;}
  if (isset($importgroup)) {$adminoutput.= $importgroup;}
  if (isset($importquestion)) {$adminoutput.= $importquestion;}
  if (isset($printablesurveyoutput)) {$adminoutput.= $printablesurveyoutput;}
  if (isset($activateoutput)) {$adminoutput.= $activateoutput;} 	
  if (isset($deactivateoutput)) {$adminoutput.= $deactivateoutput;} 	
  if (isset($tokenoutput)) {$adminoutput.= $tokenoutput;} 	
  if (isset($browseoutput)) {$adminoutput.= $browseoutput;} 	
  if (isset($dataentryoutput)) {$adminoutput.= $dataentryoutput;} 	
  if (isset($statisticsoutput)) {$adminoutput.= $statisticsoutput;} 	
  if (isset($exportoutput)) {$adminoutput.= $exportoutput;} 	
  
  
  
  if (!isset($printablesurveyoutput) && $subaction!='export')
  {  
  $adminoutput.= "\t\t</td>\n".helpscreen()
              . "\t</tr>\n"
              . "</table>\n"
              . getAdminFooter("$langdir/instructions.html", $clang->gT("Using the PHPSurveyor Admin Script"));
  }
}
  
sendcacheheaders();
echo $adminoutput;

  
  function helpscreen()
  // This functions loads the nescessary helpscreens for each action and hides the help window
  // 
  {
  	global $homeurl, $langdir,  $imagefiles;
  	global $surveyid, $gid, $qid, $action, $clang;

  	$helpoutput= "<script type='text/javascript'>\n"
    ."\tfunction showhelp(action)\n"
    ."\t\t{\n"
    ."\t\tvar name='help';\n"
    ."\t\tif (action == \"hide\")\n"
    ."\t\t\t{\n"
    ."\t\t\tdocument.getElementById(name).style.display='none';\n"
    ."\t\t\t}\n"
    ."\t\telse if (action == \"show\")\n"
    ."\t\t\t{\n"
    ."\t\t\tdocument.getElementById(name).style.display='';\n"
    ."\t\t\t}\n"
    ."\t\t}\n"
    ."</script>\n" 
    ."\t\t<td id='help' width='200' valign='top' style='display: none' bgcolor='#CCCCCC'>\n"
  	."\t\t\t<table width='100%'><tr><td>"
  	."<table width='100%' align='center' cellspacing='0'>\n"
  	."\t\t\t\t<tr>\n"
  	."\t\t\t\t\t<td bgcolor='#555555' height='8'>\n"
  	."\t\t\t\t\t\t<font color='white' size='1'><strong>"
  	.$clang->gT("Help")."</strong>\n"
  	."\t\t\t\t\t</font></td>\n"
  	."\t\t\t\t</tr>\n"
  	."\t\t\t\t<tr>\n"
  	."\t\t\t\t\t<td align='center' bgcolor='#AAAAAA' style='border-style: solid; border-width: 1; border-color: #555555'>\n"
  	."\t\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' hspace='0' border='0' align='left' />\n"
  	."\t\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' name='CloseHelp' align='right' onClick=\"showhelp('hide')\" />\n"
  	."\t\t\t\t\t</td>\n"
  	."\t\t\t\t</tr>\n"
  	."\t\t\t\t<tr>\n"
  	."\t\t\t\t\t<td bgcolor='silver' height='100%' style='border-style: solid; border-width: 1; border-color: #333333'>\n";
  	//determine which help document to show
  	if (!$surveyid && $action != "editusers")
  	{
  		$helpdoc = "$langdir/admin.html";
  	}
  	elseif (!$surveyid && $action=="editusers")
  	{
  		$helpdoc = "$langdir/users.html";
  	}
  	elseif ($surveyid && !$gid)
  	{
  		$helpdoc = "$langdir/survey.html";
  	}
  	elseif ($surveyid && $gid && !$qid)
  	{
  		$helpdoc = "$langdir/group.html";
  	}
  	//elseif ($surveyid && $gid && $qid && !$_GET['viewanswer'] && !$_POST['viewanswer'])
  	elseif ($surveyid && $gid && $qid && !returnglobal('viewanswer'))
  	{
  		$helpdoc = "$langdir/question.html";
  	}
  	elseif ($surveyid && $gid && $qid && (returnglobal('viewanswer')))
  	{
  		$helpdoc = "$langdir/answer.html";
  	}
  	$helpoutput.= "\t\t\t\t\t\t<iframe width='200' height='400' src='$helpdoc' marginwidth='2' marginheight='2'>\n"
  	."\t\t\t\t\t\t</iframe>\n"
  	."\t\t\t\t\t</td>"
  	."\t\t\t\t</tr>\n"
  	."\t\t\t</table></td></tr></table>\n"
  	."\t\t</td>\n";
  	return $helpoutput;
  }
  

  

?>
