<?php
/*
    #############################################################
    # >>> PHPSurveyor                                           #
    #############################################################
    # > Author:  Jason Cleeland                                 #
    # > E-mail:  jason@cleeland.org                             #
    # > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
    # >          CARLTON SOUTH 3053, AUSTRALIA                  #
    # > Date:    20 February 2003                               #
    #                                                           #
    # This set of scripts allows you to develop, publish and    #
    # perform data-entry on surveys.                            #
    #############################################################
    #                                                           #
    #   Copyright (C) 2003  Jason Cleeland                      #
    #                                                           #
    # This program is free software; you can redistribute       #
    # it and/or modify it under the terms of the GNU General    #
    # Public License as published by the Free Software          #
    # Foundation; either version 2 of the License, or (at your  #
    # option) any later version.                                #
    #                                                           #
    # This program is distributed in the hope that it will be   #
    # useful, but WITHOUT ANY WARRANTY; without even the        #
    # implied warranty of MERCHANTABILITY or FITNESS FOR A      #
    # PARTICULAR PURPOSE.  See the GNU General Public License   #
    # for more details.                                         #
    #                                                           #
    # You should have received a copy of the GNU General        #
    # Public License along with this program; if not, write to  #
    # the Free Software Foundation, Inc., 59 Temple Place -     #
    # Suite 330, Boston, MA  02111-1307, USA.                   #
    #############################################################
*/
if (empty($homedir)) {die ("Cannot run this script directly");}

//Move current step
if (!isset($_SESSION['step'])) {$_SESSION['step']=0;}
if (!isset($_POST['thisstep'])) {$_POST['thisstep'] = "";}
if (!isset($_POST['newgroupondisplay'])) {$_POST['newgroupondisplay'] = "";}
if (isset($_POST['move']) && $_POST['move'] == " << "._("prev")." " && !$_POST['newgroupondisplay']) {$_SESSION['step'] = $_POST['thisstep']-1;}
elseif (isset($_POST['move']) && $_POST['move'] == " << "._("prev")." " && $_POST['newgroupondisplay'] == "Y") {$_SESSION['step'] = $_POST['thisstep'];}
if (isset($_POST['move']) && $_POST['move'] == " "._("next")." >> ") {$_SESSION['step'] = $_POST['thisstep']+1;}
if (isset($_POST['move']) && $_POST['move'] == " "._("last")." ") {$_SESSION['step'] = $_POST['thisstep']+1;}

// --> START NEW FEATURE - SAVE
// If on SUBMIT page and select SAVE SO FAR it will return to SUBMIT page
if (isset($_POST['saveprompt']) && $_POST['saveprompt'] == "Y" && $_SESSION['step'] > $_SESSION['totalsteps'])
	{
	$_POST['move'] = " "._("Last Step")." ";
	}
// <-- END NEW FEATURE - SAVE

//CHECK IF ALL MANDATORY QUESTIONS HAVE BEEN ANSWERED ############################################
//First, see if we are moving backwards or doing a Save so far, and its OK not to check:
if ($allowmandbackwards==1 && ((isset($_POST['move']) &&  $_POST['move'] == " << "._("Previous")." ") || (isset($_POST['saveall']) && $_POST['saveall'] == _SAVE_AND_RETURN)))
	{
	$backok="Y";
	}
else
	{
	$backok="N";
	}

//Now, we check mandatory questions if necessary
//CHECK IF ALL CONDITIONAL MANDATORY QUESTIONS THAT APPLY HAVE BEEN ANSWERED
$notanswered=addtoarray_single(checkmandatorys($backok),checkconditionalmandatorys($backok));

//CHECK PREGS
$notvalidated=checkpregs($backok);

// Check for session timeout
if (session_id()=='') 
 {
 	 echo "Sorry, your sessions seems to have expired. Please restart the survey. (1)";
 	 die();
 }

//SUBMIT
if (isset($_POST['move']) && $_POST['move'] == " "._("submit")." ")
	{
	if ($thissurvey['refurl'] == "Y")
		{
		if (!in_array("refurl", $_SESSION['insertarray'])) //Only add this if it doesn't already exist
			{
			$_SESSION['insertarray'][] = "refurl";
			}
		$_SESSION['refurl'] = $_SESSION['refurl']; 
		}


	//COMMIT CHANGES TO DATABASE
	if ($thissurvey['active'] != "Y")
		{
		sendcacheheaders();
		doHeader();
		foreach(file("$thistpl/startpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}

		//Check for assessments
		$assessments = doAssessment($surveyid);
		if ($assessments)
			{
			foreach(file("$thistpl/assessment.pstpl") as $op)
				{
				echo templatereplace($op);
				}
			}

		$completed = "<br /><strong><font size='2' color='red'>"._("Did Not Save")."</font></strong><br /><br />\n\n";
		$completed .= _("Your survey responses have not been recorded. This survey is not yet active.")."<br /><br />\n";
		$completed .= "<a href='".$_SERVER['PHP_SELF']."?sid=$surveyid&amp;move=clearall'>"._("Clear Responses")."</a><br /><br />\n";
		$completed .= "<font size='1'>$subquery</font>\n";
		}
	else
		{

            
			if ($thissurvey['usecookie'] == "Y" && $tokensexist != 1) //don't use cookies if tokens are being used
				{
				$cookiename="PHPSID".returnglobal('sid')."STATUS";
				setcookie("$cookiename", "COMPLETE", time() + 31536000);
				}

            
			$content='';
			foreach(file("$thistpl/startpage.pstpl") as $op)
				{
				$content.= templatereplace($op);
				}
            
			//Check for assessments
			$assessments = doAssessment($surveyid);
			if ($assessments)
				{
				foreach(file("$thistpl/assessment.pstpl") as $op)
					{
					$content.= templatereplace($op);
					}
				}
                
			$completed = "<br /><font size='2'><font color='green'><strong>"
						._("Thank you")."</strong></font><br /><br />\n\n"
						._("Your survey responses have been recorded.")."<br />\n"
						."<a href='javascript:window.close()'>"
						._("Close this Window")."</a></font><br /><br />\n";
						
			//Update the token if needed and send a confirmation email
			if (isset($_POST['token']) && $_POST['token'])
				{
				submittokens();
				}

			//Send notification to survey administrator //Thanks to Jeff Clement http://jclement.ca
			if ($thissurvey['sendnotification'] > 0 && $thissurvey['adminemail']) 
				{
				sendsubmitnotification($thissurvey['sendnotification']);
				}

            
			session_unset();
			session_destroy();

			if (isset($thissurvey['autoredirect']) && $thissurvey['autoredirect'] == "Y" && $thissurvey['url'])
				{
				//Automatically redirect the page to the "url" setting for the survey
				header("Location: {$thissurvey['url']}");
				}

			doHeader();
    if (isset($content)) {echo $content;}

		}
		
	foreach(file("$thistpl/completed.pstpl") as $op)
		{
		echo templatereplace($op);
		}
    
	echo "\n<br />\n";
	foreach(file("$thistpl/endpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	exit;
	}

//LAST PHASE ###########################################################################
if (isset($_POST['move']) && $_POST['move'] == " "._("last")." " && (!isset($notanswered) || !$notanswered) && (!isset($notvalidated) && !$notvalidated))
	{
	last();
	exit;
	}

//SEE IF $surveyid EXISTS ####################################################################
if ($surveyexists <1)
	{
	sendcacheheaders();
	doHeader();
	//SURVEY DOES NOT EXIST. POLITELY EXIT.
	foreach(file("$thistpl/startpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	echo "\t<center><br />\n";
	echo "\t"._("Sorry. There is no matching survey.")."<br />&nbsp;\n";  
	foreach(file("$thistpl/endpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	exit;
	}

//RUN THIS IF THIS IS THE FIRST TIME
if (!isset($_SESSION['step']) || !$_SESSION['step'])
	{
	$totalquestions = buildsurveysession();
	sendcacheheaders();
	doHeader();
	foreach(file("$thistpl/startpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	echo "\n<form method='post' action='{$_SERVER['PHP_SELF']}' id='phpsurveyor' name='phpsurveyor'>\n";

	echo "\n\n<!-- START THE SURVEY -->\n";

	foreach(file("$thistpl/welcome.pstpl") as $op)
		{
		echo "\t\t\t".templatereplace($op);
		}
	echo "\n";
	$navigator = surveymover();
	foreach(file("$thistpl/navigator.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	if ($thissurvey['active'] != "Y")
		{
		echo "\t\t<center><font color='red' size='2'>"._("This survey is not currently active. You will not be able to save your responses.")."</font></center>\n";
		}
	echo "\n<input type='hidden' name='sid' value='$surveyid' id='sid'>\n";
	echo "\n<input type='hidden' name='token' value='$token' id='token'>\n";
	echo "\n<input type='hidden' name='lastgroupname' value='_WELCOME_SCREEN_' id='lastgroupname'>\n"; //This is to ensure consistency with mandatory checks, and new group test
	echo "\n</form>\n";
	foreach(file("$thistpl/endpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	doFooter();
	exit;
	}
    
//******************************************************************************************************
//PRESENT SURVEY
//******************************************************************************************************

//GET GROUP DETAILS

if ($_SESSION['step'] == "0") {$currentquestion=$_SESSION['step'];}
else {$currentquestion=$_SESSION['step']-1;}





$ia=$_SESSION['fieldarray'][$currentquestion];

list($newgroup, $gid, $groupname, $groupdescription, $gl)=checkIfNewGroup($ia);

// MANAGE CONDITIONAL QUESTIONS
$conditionforthisquestion=$ia[7];
$questionsSkipped=0;
while ($conditionforthisquestion == "Y") //IF CONDITIONAL, CHECK IF CONDITIONS ARE MET
    {
    $cquery="SELECT distinct cqid FROM {$dbprefix}conditions WHERE qid={$ia[0]}";
    $cresult=db_execute_assoc($cquery) or die("Couldn't count cqids<br />$cquery<br />".htmlspecialchars($connect->ErrorMsg()));
    $cqidcount=$cresult->RecordCount();
    $cqidmatches=0;
    while ($crows=$cresult->FetchRow())//Go through each condition for this current question
        {
        //Check if the condition is multiple type
        $ccquery="SELECT type FROM {$dbprefix}questions WHERE qid={$crows['cqid']}";
        $ccresult=db_execute_assoc($ccquery) or die ("Coudn't get type from questions<br />$ccquery<br />".htmlspecialchars($connect->ErrorMsg()));
        while($ccrows=$ccresult->FetchRow())
            {
            $thistype=$ccrows['type'];
            }
        $cqquery = "SELECT cfieldname, value, cqid FROM {$dbprefix}conditions WHERE qid={$ia[0]} AND cqid={$crows['cqid']}";
        $cqresult = db_execute_assoc($cqquery) or die("Couldn't get conditions for this question/cqid<br />$cquery<br />".htmlspecialchars($connect->ErrorMsg()));
        $amatchhasbeenfound="N";
        while ($cqrows=$cqresult->FetchRow()) //Check each condition
            {
            $currentcqid=$cqrows['cqid'];
            $conditionfieldname=$cqrows['cfieldname'];
            if (!$cqrows['value']) {$conditionvalue="NULL";} else {$conditionvalue=$cqrows['value'];}
            if ($thistype == "M" || $thistype == "P") //Adjust conditionfieldname for multiple option type questions
                {
                $conditionfieldname .= $conditionvalue;
                $conditionvalue = "Y";
                }
            if (!isset($_SESSION[$conditionfieldname]) || !$_SESSION[$conditionfieldname]) {$currentvalue="NULL";} else {$currentvalue=$_SESSION[$conditionfieldname];}
            if ($currentvalue == $conditionvalue) {$amatchhasbeenfound="Y";}
            }
        if ($amatchhasbeenfound == "Y") {$cqidmatches++;}
        }
    if ($cqidmatches == $cqidcount)
        {
        //a match has been found in ALL distinct cqids. The question WILL be displayed
        $conditionforthisquestion="N";
        }
    else
        {
        //matches have not been found in ALL distinct cqids. The question WILL NOT be displayed
        $questionsSkipped++;
        if (returnglobal('move') == " "._("next")." >> ")
            {
            $currentquestion++;
            if(isset($_SESSION['fieldarray'][$currentquestion]))
                {
                $ia=$_SESSION['fieldarray'][$currentquestion];
                }
            $_SESSION['step']++;
            foreach ($_SESSION['grouplist'] as $gl)
                {
                if ($gl[0] == $ia[5])
                    {
                    $gid=$gl[0];
                    $groupname=$gl[1];
                    $groupdescription=$gl[2];
                    if ($_POST['lastgroupname'] != $groupname && $groupdescription) {$newgroup = "Y";} else {$newgroup == "N";}
                    }
                }
    
            if ($_SESSION['step'] > $_SESSION['totalsteps']) 
                {
                //The last question was conditional and has been skipped. Move into panic mode.
                $conditionforthisquestion="N";
                last();
                exit;
                }
            }
        elseif (returnglobal('move') == " << "._("prev")." ")
            {
            $currentquestion--;
            $ia=$_SESSION['fieldarray'][$currentquestion];
            $_SESSION['step']--;
            }
        $conditionforthisquestion=$ia[7];
        }
    }

if ($questionsSkipped == 0 && $newgroup == "Y" && isset($_POST['move']) && $_POST['move'] == " << "._("prev")." " && (isset($_POST['grpdesc']) && $_POST['grpdesc']=="Y")) //a small trick to manage moving backwards from a group description
    {
    //This does not work properly in all instances.
    $currentquestion++; 
    $ia=$_SESSION['fieldarray'][$currentquestion]; 
    $_SESSION['step']++;
    }

list($newgroup, $gid, $groupname, $groupdescription, $gl)=checkIfNewGroup($ia);

require_once("qanda.php");
$mandatorys=array();
$mandatoryfns=array();
$conmandatorys=array();
$conmandatoryfns=array();
$conditions=array();
$inputnames=array();

list($plus_qanda, $plus_inputnames)=retrieveAnswers($ia);
if ($plus_qanda)
	{
        $qanda[]=$plus_qanda;
	}
if ($plus_inputnames)
	{
	$inputnames = addtoarray_single($inputnames, $plus_inputnames);
	}

//Display the "mandatory" popup if necessary
if (isset($notanswered)) 
	{
	list($mandatorypopup, $popup)=mandatory_popup($ia, $notanswered);
	}

//Display the "validation" popup if necessary
if (isset($notvalidated))
	{
	list($validationpopup, $vpopup)=validation_popup($ia, $notvalidated);
	}   

//Get list of mandatory questions
list($plusman, $pluscon)=create_mandatorylist($ia);
if ($plusman !== null)
	{
	list($plus_man, $plus_manfns)=$plusman;
	$mandatorys=addtoarray_single($mandatorys, $plus_man);
	$mandatoryfns=addtoarray_single($mandatoryfns, $plus_manfns);
	}
if ($pluscon !== null)
	{
	list($plus_conman, $plus_conmanfns)=$pluscon;
	$conmandatorys=addtoarray_single($conmandatorys, $plus_conman);
	$conmandatoryfns=addtoarray_single($conmandatoryfns, $plus_conmanfns);
	}

//Build an array containing the conditions that apply for this page
$plus_conditions=retrieveConditionInfo($ia); //Returns false if no conditions
if ($plus_conditions)
	{
	$conditions = addtoarray_single($conditions, $plus_conditions);
	}
//------------------------END DEVELOPMENT OF QUESTION

$percentcomplete = makegraph($_SESSION['step'], $_SESSION['totalsteps']);

//READ TEMPLATES, INSERT DATA AND PRESENT PAGE
sendcacheheaders();
doHeader();

if (isset($popup)) {echo $popup;}
if (isset($vpopup)) {echo $vpopup;}
foreach(file("$thistpl/startpage.pstpl") as $op)
	{
	echo templatereplace($op);
	}

echo "\n<form method='post' action='{$_SERVER['PHP_SELF']}' id='phpsurveyor' name='phpsurveyor'>\n";

//PUT LIST OF FIELDS INTO HIDDEN FORM ELEMENT
echo "\n\n<!-- INPUT NAMES -->\n";
echo "\t<input type='hidden' name='fieldnames' value='";
echo implode("|", $inputnames);
echo "' id='fieldnames'>\n";

// --> START NEW FEATURE - SAVE
// Used to keep track of the fields modified, so only those are updated during save
echo "\t<input type='hidden' name='modfields' value='";

// Debug - uncomment if you want to see the value of modfields on the next page source (to see what was modified)
//         however doing so will cause the save routine to save all fields that have ever been modified whether
//	   they are on the current page or not.  Recommend just using this for debugging.
//if (isset($_POST['modfields']) && $_POST['modfields']) {
//	$inputmodfields=explode("|", $_POST['modfields']);
//	echo implode("|", $inputmodfields);
//}

echo "' id='modfields'>\n";
echo "\n";
echo "\n\n<!-- JAVASCRIPT FOR MODIFIED QUESTIONS -->\n";
echo "\t<script type='text/javascript'>\n";
echo "\t<!--\n";
echo "\t\tfunction modfield(name)\n";
echo "\t\t\t{\n";
echo "\t\t\t\ttemp=document.getElementById('modfields').value;\n";
echo "\t\t\t\tif (temp=='') {\n";
echo "\t\t\t\t\tdocument.getElementById('modfields').value=name;\n";
echo "\t\t\t\t}\n";
echo "\t\t\t\telse {\n";
echo "\t\t\t\t\tmyarray=temp.split('|');\n";
echo "\t\t\t\t\tif (!inArray(name, myarray)) {\n";
echo "\t\t\t\t\t\tmyarray.push(name);\n";
echo "\t\t\t\t\t\tdocument.getElementById('modfields').value=myarray.join('|');\n";
echo "\t\t\t\t\t}\n";
echo "\t\t\t\t}\n";
echo "\t\t\t}\n";
echo "\n";
echo "\t\tfunction inArray(needle, haystack)\n";
echo "\t\t\t{\n";
echo "\t\t\t\tfor (h in haystack) {\n";
echo "\t\t\t\t\tif (haystack[h] == needle) {\n";
echo "\t\t\t\t\t\treturn true;\n";
echo "\t\t\t\t\t}\n";
echo "\t\t\t\t}\n";
echo "\t\t\treturn false;\n";
echo "\t\t\t} \n";
echo "\t//-->\n";
echo "\t</script>\n\n";
// <-- END NEW FEATURE - SAVE

echo "\n\n<!-- START THE SURVEY -->\n";
foreach(file("$thistpl/survey.pstpl") as $op)
	{
	echo "\t".templatereplace($op);
	}

if ($newgroup == "Y" && $groupdescription && (isset($_POST['move']) && $_POST['move'] != " << "._("prev")." "))
	{
	$presentinggroupdescription = "yes";
	echo "\n\n<!-- START THE GROUP DESCRIPTION -->\n";
	echo "\t\t\t<input type='hidden' name='grpdesc' value='Y' id='grpdesc'>\n";
	foreach(file("$thistpl/startgroup.pstpl") as $op)
		{
		echo "\t".templatereplace($op);
		}
	echo "\n<br />\n";
    
	if ($groupdescription)
		{
		foreach(file("$thistpl/groupdescription.pstpl") as $op)
			{
			echo "\t\t".templatereplace($op);
			}
		}
	echo "\n";
    
	echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n";
	echo "\t<script type='text/javascript'>\n";
	echo "\t<!--\n";
	echo "\t\tfunction checkconditions(value, name, type)\n";
	echo "\t\t\t{\n";
	echo "\t\t\t}\n";
	echo "\t//-->\n";
	echo "\t</script>\n\n";
	echo "\n\n<!-- END THE GROUP -->\n";
	foreach(file("$thistpl/endgroup.pstpl") as $op)
		{
		echo "\t\t\t\t".templatereplace($op);
		}
	echo "\n";

	$_SESSION['step']--;
	echo "\t\t\t<input type='hidden' name='newgroupondisplay' value='Y' id='newgroupondisplay'>\n";
	}
else
	{
	echo "\n\n<!-- START THE GROUP -->\n";
	foreach(file("$thistpl/startgroup.pstpl") as $op)
		{
		echo "\t".templatereplace($op);
		}
	echo "\n";
    
	echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n";
	echo "\t<script type='text/javascript'>\n";
	echo "\t<!--\n";
	echo "\t\tfunction checkconditions(value, name, type)\n";
	echo "\t\t\t{\n";
	echo "\t\t\t}\n";
	echo "\t//-->\n";
	echo "\t</script>\n\n";
    
	echo "\n\n<!-- PRESENT THE QUESTIONS -->\n";
	if (is_array($qanda))
		{
		foreach ($qanda as $qa)
			{
			echo "\n\t<!-- NEW QUESTION -->\n";
			echo "\n\t<!-- QUESTION TYPE ".$qa[5]."-->\n";
			echo "\t\t\t\t<div id='question$qa[4]'>";
			$question="<label for='$ia[7]'>" . $qa[0] . "</label>";
			$answer=$qa[1];
			$help=$qa[2];
			$questioncode=$qa[5];
			foreach(file("$thistpl/question.pstpl") as $op)
				{
				echo "\t\t\t\t\t".templatereplace($op)."\n";
				}
			echo "\t\t\t\t</div>\n";
			}
		}
	echo "\n\n<!-- END THE GROUP -->\n";
	foreach(file("$thistpl/endgroup.pstpl") as $op)
		{
		echo "\t\t\t\t".templatereplace($op);
		}
	echo "\n";
	}
	
$navigator = surveymover();

echo "\n\n<!-- PRESENT THE NAVIGATOR -->\n";
foreach(file("$thistpl/navigator.pstpl") as $op)
	{
	echo "\t\t".templatereplace($op);
	}
echo "\n";

if ($thissurvey['active'] != "Y") 
	{
	echo "\t\t<center><font color='red' size='2'>"._("This survey is not currently active. You will not be able to save your responses.")."</font></center>\n";
	}
	
echo "\n";
    
if (isset($conditions) && is_array($conditions)) //if conditions exist, create hidden inputs for previously answered questions
	{
	foreach (array_keys($_SESSION) as $SESak)
		{
		if (in_array($SESak, $_SESSION['insertarray']))
			{
			echo "<input type='hidden' name='java$SESak' id='java$SESak' value='" . htmlspecialchars($_SESSION[$SESak],ENT_QUOTES) . "'>\n";
			}
		}
	}


//SOME STUFF FOR MANDATORY QUESTIONS
if (remove_nulls_from_array($mandatorys) && $newgroup != "Y")
	{
	$mandatory=implode("|", remove_nulls_from_array($mandatorys));
	echo "<input type='hidden' name='mandatory' value='$mandatory' id='mandatory'>\n";
	}
if (remove_nulls_from_array($conmandatorys))
	{
	$conmandatory=implode("|", remove_nulls_from_array($conmandatorys));
	echo "<input type='hidden' name='conmandatory' value='$conmandatory' id='conmandatory'>\n";
	}
if (remove_nulls_from_array($mandatoryfns))
	{
	$mandatoryfn=implode("|", remove_nulls_from_array($mandatoryfns));
	echo "<input type='hidden' name='mandatoryfn' value='$mandatoryfn' id='mandatoryfn'>\n";
	}
if (remove_nulls_from_array($conmandatoryfns))
	{
	$conmandatoryfn=implode("|", remove_nulls_from_array($conmandatoryfns));
	echo "<input type='hidden' name='conmandatoryfn' value='$conmandatoryfn' id='conmandatoryfn'>\n";
	}

echo "<input type='hidden' name='thisstep' value='{$_SESSION['step']}' id='thisstep'>\n";
echo "<input type='hidden' name='sid' value='$surveyid' id='sid'>\n";
echo "<input type='hidden' name='token' value='$token' id='token'>\n";
echo "<input type='hidden' name='lastgroupname' value='".htmlspecialchars($groupname)."' id='lastgroupname'>\n";
echo "</form>\n";
foreach(file("$thistpl/endpage.pstpl") as $op)
	{
	echo templatereplace($op);
	}
doFooter();

function last()
	{
	global $thissurvey;
	global $thistpl, $surveyid, $token;
	if (!isset($privacy)) {$privacy="";}
	if ($thissurvey['private'] != "N")
		{
		foreach (file("$thistpl/privacy.pstpl") as $op)
			{
			$privacy .= templatereplace($op);
			}
		}
	//READ TEMPLATES, INSERT DATA AND PRESENT PAGE
	sendcacheheaders();
	doHeader();
	foreach(file("$thistpl/startpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n";
	echo "\t<script type='text/javascript'>\n";
	echo "\t<!--\n";
	echo "\t\tfunction checkconditions(value, name, type)\n";
	echo "\t\t\t{\n";
	echo "\t\t\t}\n";
	echo "\t//-->\n";
	echo "\t</script>\n\n";
	echo "\n<form method='post' action='{$_SERVER['PHP_SELF']}' id='phpsurveyor' name='phpsurveyor'>\n";
	$GLOBALS["privacy"]=$privacy;
	echo "\n\n<!-- START THE SURVEY -->\n";
	foreach(file("$thistpl/survey.pstpl") as $op)
		{
		echo "\t\t".templatereplace($op);
		}
	//READ SUBMIT TEMPLATE
	foreach(file("$thistpl/submit.pstpl") as $op)
		{
		echo "\t\t\t".templatereplace($op);
		}
    
	$GLOBALS["navigator"]=surveymover();
	echo "\n\n<!-- PRESENT THE NAVIGATOR -->\n";
	foreach(file("$thistpl/navigator.pstpl") as $op)
		{
		echo "\t\t".templatereplace($op);
		}
	echo "\n";
	echo "\n<input type='hidden' name='thisstep' value='{$_SESSION['step']}' id='thisstep'>\n";
	echo "\n<input type='hidden' name='sid' value='$surveyid' id='sid'>\n";
	echo "\n<input type='hidden' name='token' value='$token' id='token'>\n";
	echo "\n</form>\n";
	foreach(file("$thistpl/endpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	doFooter();
	}

function checkIfNewGroup($ia)
	{
	foreach ($_SESSION['grouplist'] as $gl)
		{
		if ($gl[0] == $ia[5])
			{
			$gid=$gl[0];
			$groupname=$gl[1];
			$groupdescription=$gl[2];
			if (isset($_POST['lastgroupname']) && $_POST['lastgroupname'] != $groupname && $groupdescription) 
				{
				$newgroup = "Y";
				}
			else 
				{
				$newgroup = "N";
				}
			if (!isset($_POST['lastgroupname'])) {$newgroup="Y";}
			}
		}
	return array($newgroup, $gid, $groupname, $groupdescription, $gl);
	}
?>
