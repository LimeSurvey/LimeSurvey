<?php
/**
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* 
* $Id$
*/

// Performance optimized	: Nov 27, 2006
// Performance Improvement	: 41% (Call to templatereplace())
// Optimized By				: swales

if (empty($homedir)) {die ("Cannot run this script directly");}

//Move current step ###########################################################################
if (!isset($_SESSION['step'])) {$_SESSION['step'] = 0;}
if (!isset($_SESSION['totalsteps'])) {$_SESSION['totalsteps']=0;}
if (!isset($_POST['thisstep'])) {$_POST['thisstep'] = "";}

if (isset($_POST['move']) && $_POST['move'] == "moveprev") {$_SESSION['step'] = $_POST['thisstep']-1;}
if (isset($_POST['move']) && $_POST['move'] == "movenext") {$_SESSION['step'] = $_POST['thisstep']+1;}
if (isset($_POST['move']) && $_POST['move'] == "movelast") {$_SESSION['step'] = $_POST['thisstep']+1;}

// This prevents the user from going back to the question pages and keeps him on the final page
// That way his session can be kept so he can still print his answers until he closes the browser
if (isset($_SESSION['finished'])) {$_POST['move']="movesubmit"; }

// If on SUBMIT page and select SAVE SO FAR it will return to SUBMIT page
if ($_SESSION['step'] > $_SESSION['totalsteps'])
{
	$_POST['move'] = "movelast";
}


//CHECK IF ALL MANDATORY QUESTIONS HAVE BEEN ANSWERED ############################################
//CHECK IF ALL CONDITIONAL MANDATORY QUESTIONS THAT APPLY HAVE BEEN ANSWERED
$notanswered=addtoarray_single(checkmandatorys(),checkconditionalmandatorys());

//CHECK PREGS
$notvalidated=checkpregs();

//SUBMIT
if ((isset($_POST['move']) && $_POST['move'] == "movesubmit") && (!isset($notanswered) || !$notanswered) && (!isset($notvalidated) && !$notvalidated))
{
	if ($thissurvey['private'] == "Y")
	{
		$privacy="";
//		foreach (file("$thistpl/privacy.pstpl") as $op)
//		{
//			$privacy .= templatereplace($op);
//		}
			$privacy .= templatereplace(file_get_contents("$thistpl/privacy.pstpl"));

	}
	if ($thissurvey['refurl'] == "Y")
	{
		if (!in_array("refurl", $_SESSION['insertarray'])) //Only add this if it doesn't already exist
		{
			$_SESSION['insertarray'][] = "refurl";
		}
		//$_SESSION['refurl'] = $_SESSION['refurl'];
	}


	//COMMIT CHANGES TO DATABASE
	if ($thissurvey['active'] != "Y")
	{
		sendcacheheaders();
		doHeader();
//		foreach(file("$thistpl/startpage.pstpl") as $op)
//		{
//			echo templatereplace($op);
//		}
		echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

		//Check for assessments
		$assessments = doAssessment($surveyid);
		if ($assessments)
		{
//			foreach(file("$thistpl/assessment.pstpl") as $op)
//			{
//				echo templatereplace($op);
//			}
			echo templatereplace(file_get_contents("$thistpl/assessment.pstpl"));
		}

		$completed = "<br /><strong><font size='2' color='red'>".$clang->gT("Did Not Save")."</strong></font><br /><br />\n\n"
		. $clang->gT("Your survey responses have not been recorded. This survey is not yet active.")."<br /><br />\n"
		. "<a href='{$_SERVER['PHP_SELF']}?sid=$surveyid&amp;move=clearall'>".$clang->gT("Clear Responses")."</a><br /><br />\n"
		. "<font size='1'>$subquery</font>\n";
	}
	else
	{

		if ($thissurvey['usecookie'] == "Y" && $tokensexist != 1)
		{
			$cookiename="PHPSID".returnglobal('sid')."STATUS";
			setcookie("$cookiename", "COMPLETE", time() + 31536000); //365 days
		}

		$content='';

		//Start to print the final page
			$content .= templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

		//Check for assessments
		$assessments = doAssessment($surveyid);
		if ($assessments)
		{
			$content .= templatereplace(file_get_contents("$thistpl/assessment.pstpl"));
		}

        // this three lines are to set the submitdate
        unset($_POST['modfields']);
        $subquery = createinsertquery();
        $connect->Execute($subquery);

		//Create text for use in later print section
		$completed = "<br /><strong><font size='2'><font color='green'>"
		. $clang->gT("Thank you")."</strong></font><br /><br />\n\n"
		. $clang->gT("Your survey responses have been recorded.")."<br />\n"
		. "<a href='javascript:window.close()'>"
		. $clang->gT("Close this Window")."</a></font><br /><br />\n";

         // Link to Print Answer Preview  **********
         if ($thissurvey['printanswers']=='Y')
         {
            $completed .= "<br /><br />"
            ."<a class='printlink' href='printanswers.php' target='_blank'>"
            .$clang->gT("Click here to print your answers.")
            ."</a><br />\n";
         }
        //*****************************************

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

        $_SESSION['finished']=true; 
        $_SESSION['sid']=$surveyid;

		sendcacheheaders();
		if (!$embedded && isset($thissurvey['autoredirect']) && $thissurvey['autoredirect'] == "Y" && $thissurvey['url'])
		{
			//Automatically redirect the page to the "url" setting for the survey
			session_write_close();
			
			$url = $thissurvey['url'];
			$url=str_replace("{SAVEDID}",$saved_id, $url);			// to activate the SAVEDID in the END URL
            $url=str_replace("{TOKEN}",$_POST['token'], $url);            // to activate the TOKEN in the END URL
            $url=str_replace("{SID}", $surveyid, $url);       // to activate the SID in the RND URL

			header("Location: {$url}");
		}

		doHeader();
		echo $content;

	}

	echo templatereplace(file_get_contents("$thistpl/completed.pstpl"));

	echo "\n<br />\n";
	echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));

	exit;
}

//LAST PHASE
if (isset($_POST['move']) && $_POST['move'] == "movelast" && (!isset($notanswered) && !$notanswered) && (!isset($notvalidated) && !$notvalidated))
{
	//READ TEMPLATES, INSERT DATA AND PRESENT PAGE
	sendcacheheaders();
	doHeader();
	echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

	echo "\n<form method='post' action='{$_SERVER['PHP_SELF']}' id='limesurvey' name='limesurvey'>\n"
	."\n\n<!-- START THE SURVEY -->\n";
	echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));

	//READ SUBMIT TEMPLATE
	echo templatereplace(file_get_contents("$thistpl/submit.pstpl"));

	$navigator = surveymover();
	echo "\n\n<!-- PRESENT THE NAVIGATOR -->\n";
	echo templatereplace(file_get_contents("$thistpl/navigator.pstpl"));
	echo "\n";
	echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
	echo "\n"
	."\n<input type='hidden' name='thisstep' value='{$_SESSION['step']}' id='thisstep' />\n"
	."\n<input type='hidden' name='sid' value='$surveyid' id='sid' />\n"
	."\n<input type='hidden' name='token' value='$token' id='token' />\n"
	."\n</form>\n";
	doFooter();
	exit;
}

//SEE IF $surveyid EXISTS
if ($surveyexists <1)
{
	sendcacheheaders();
	doHeader();
	//SURVEY DOES NOT EXIST. POLITELY EXIT.
	echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
	echo "\t<center><br />\n"
	."\t".$clang->gT("Sorry. There is no matching survey.")."<br /></center>&nbsp;\n";
	echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
	doFooter();
	exit;
}

//RUN THIS IF THIS IS THE FIRST TIME
if (!isset($_SESSION['step']) || !$_SESSION['step'])
	{
	$totalquestions = buildsurveysession();
	$_SESSION['step'] = 1;
	}


//******************************************************************************************************
//PRESENT SURVEY
//******************************************************************************************************

//GET GROUP DETAILS
require_once("qanda.php");
$mandatorys=array();
$mandatoryfns=array();
$conmandatorys=array();
$conmandatoryfns=array();
$conditions=array();
$inputnames=array();
foreach ($_SESSION['grouplist'] as $gl)
{
	$gid=$gl[0];
	foreach ($_SESSION['fieldarray'] as $ia)
	{
		if ($ia[5] == $gid)
		{
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
		}
	}
}

//READ TEMPLATES, INSERT DATA AND PRESENT PAGE
sendcacheheaders();
doHeader();
if(isset($popup)) {echo $popup;}
if(isset($vpopup)) {echo $vpopup;}
echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
echo "\n<form method='post' action='{$_SERVER['PHP_SELF']}' id='limesurvey' name='limesurvey'>\n";
//PUT LIST OF FIELDS INTO HIDDEN FORM ELEMENT
echo "\n\n<!-- INPUT NAMES -->\n"
."\t<input type='hidden' name='fieldnames' id='fieldnames' value='"
.implode("|", $inputnames)
."'>\n";

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
echo "  function inArray(needle, haystack)\n";
echo "   {\n";
echo "    for (h in haystack) {\n";
echo "     if (haystack[h] == needle) {\n";
echo "      return true;\n";
echo "     }\n";
echo "    }\n";
echo "   return false;\n";
echo "   } \n";
echo "    function ValidDate(oObject)\n";
echo "    {// --- Regular expression used to check if date is in correct format\n";
echo "     var str_regexp = /[1-9][0-9]{3}-(0[1-9]|1[0-2])-([0-2][0-9]|3[0-1])/;\n";
echo "     var pattern = new RegExp(str_regexp);\n";
echo "     if ((oObject.value.match(pattern)!=null))\n";
echo "     {var date_array = oObject.value.split('-');\n";
echo "      var day = date_array[2];\n";
echo "      var month = date_array[1];\n";
echo "      var year = date_array[0];\n";
echo "      str_regexp = /1|3|5|7|8|10|12/;\n";
echo "      pattern = new RegExp(str_regexp);\n";
echo "      if ( day <= 31 && (month.match(pattern)!=null))\n";
echo "      { return true;\n";
echo "      }\n";
echo "      str_regexp = /4|6|9|11/;\n";
echo "      pattern = new RegExp(str_regexp);\n";
echo "      if ( day <= 30 && (month.match(pattern)!=null))\n";
echo "      { return true;\n";
echo "      }\n";
echo "      if (day == 29 && month == 2 && (year % 4 == 0))\n";
echo "      { return true;\n";
echo "      }\n";
echo "      if (day <= 28 && month == 2)\n";
echo "      { return true;\n";
echo "      }        \n";
echo "     }\n";
echo "     window.alert('".$clang->gT("Date is not valid!")."');\n";
echo "     oObject.focus();\n";
echo "     oObject.select();\n";
echo "     return false;\n";
echo "    }\n";
//echo "  }\n";
echo "\t//-->\n";
echo "\t</script>\n\n";
// <-- END NEW FEATURE - SAVE

echo templatereplace(file_get_contents("$thistpl/welcome.pstpl"));

echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
."\t<script type='text/javascript'>\n"
."\t<!--\n";


// Find out if there are any array_filter questions in this group
$array_filterqs = getArrayFiltersForGroup($gid);
// Put in the radio button reset javascript for the array filter unselect
if (isset($array_filterqs) && is_array($array_filterqs)) {
	print <<<END

		function radio_unselect(radioObj)
		{
			var radioLength = radioObj.length;
			for(var i = 0; i < radioLength; i++)
			{
				radioObj[i].checked = false;
			}
		}


END;
}

echo  "\t\tfunction checkconditions(value, name, type)\n"
."\t\t\t{\n";

if ((isset($conditions) && is_array($conditions)) || (isset($array_filterqs) && is_array($array_filterqs)))
{
	if (!isset($endzone)) {$endzone="";}
	echo "\t\t\tif (type == 'radio' || type == 'select-one')\n"
	."\t\t\t\t{\n"
	."\t\t\t\tvar hiddenformname='java'+name;\n"
	."\t\t\t\tdocument.getElementById(hiddenformname).value=value;\n"
	."\t\t\t\t}\n"
	."\t\t\tif (type == 'checkbox')\n"
	."\t\t\t\t{\n"
	."\t\t\t\tvar hiddenformname='java'+name;\n"
	."\t\t\t\tvar chkname='answer'+name;\n"
	."\t\t\t\tif (document.getElementById(chkname).checked) {\n"
	."\t\t\t\t\tdocument.getElementById(hiddenformname).value='Y';}\n"
	."\t\t\t\telse {\n"
	."\t\t\t\t\tdocument.getElementById(hiddenformname).value='';}\n"
	."\t\t\t\t}\n";
	$java="";
	$cqcount=1;
	foreach ($conditions as $cd)
	{
		if ((isset($oldq) && $oldq != $cd[0]) || !isset($oldq)) //New if statement
		{
			$java .= $endzone;
			$endzone = "";
			$cqcount=1;
      $java .= "\n   if ((";
    }

    if (!isset($oldcq) || !$oldcq)
    {
        $oldcq = $cd[2];
		}

    //Just in case the dropdown threshold is being applied, check number of answers here
    if ($cd[4] == "L")
		{
			$cccquery="SELECT code FROM {$dbprefix}answers WHERE qid={$cd[1]}";
			$cccresult=$connect->Execute($cccquery);
			$cccount=$cccresult->RecordCount();
		}
    if ($cd[4] == "R")
    {
       $idname="fvalue_".$cd[1].substr($cd[2], strlen($cd[2])-1,1);
    }
    elseif ($cd[4] == "5" ||
            $cd[4] == "A" ||
            $cd[4] == "B" ||
            $cd[4] == "C" ||
            $cd[4] == "E" ||
            $cd[4] == "F" ||
            $cd[4] == "G" ||
            $cd[4] == "Y" ||
            $cd[4] == "1" ||
            ($cd[4] == "L" &&
             $cccount <= $dropdownthreshold))
    {
        $idname="java$cd[2]";
    }
		elseif ($cd[4] == "M" || $cd[4] == "P")
	{
        $idname="java$cd[2]$cd[3]";
    }
    else
    {
        $idname="java".$cd[2];
    }

    if ($cqcount > 1 && $oldcq ==$cd[2])
    {
        $java .= " || ";
    }
    elseif ($cqcount >1 && $oldcq != $cd[2])
    {
        $java .= ") && (";
    }

    if ($cd[3] == '' || $cd[3] == ' ')
    {
      $java .= "document.getElementById('$idname').value == ' ' || !document.getElementById('$idname').value";
        }
		elseif($cd[4] == "M" || $cd[4] == "P")
		{
			$java .= "document.getElementById('$idname').value == 'Y'";
		}
		else
		{
      // NEW
      // If the value is enclossed by @
      // the value of this question must be evaluated instead.
      if (ereg('^@[^@]+@', $cd[3]))
      {
        $java .= "document.getElementById('$idname').value $cd[6] document.getElementById('$cd[2]').value";
	  }
	  else
	  {
			$java .= "document.getElementById('$idname').value == '$cd[3]'";
		}
    }

		if ((isset($oldq) && $oldq != $cd[0]) || !isset($oldq))//Close if statement
		{
			$endzone = "))\n"
      . "    {\n"
      . "    document.getElementById('question$cd[0]').style.display='';\n"
      . "    document.getElementById('display$cd[0]').value='on';\n"
      . "    }\n"
      . "   else\n"
      . "    {\n"
      . "    document.getElementById('question$cd[0]').style.display='none';\n"
      . "    document.getElementById('display$cd[0]').value='';\n"
      . "    }\n";
			$cqcount++;
		}
		$oldq = $cd[0]; //Update oldq for next loop
		$oldcq = $cd[2];  //Update oldcq for next loop
	}
	$java .= $endzone;
}


if (isset($array_filterqs) && is_array($array_filterqs))
{
	if (!isset($appendj)) {$appendj="";}

	foreach ($array_filterqs as $attralist)
	{
		//die(print_r($attrflist));
		$qbase = $surveyid."X".$gid."X".$attralist['qid'];
		$qfbase = $surveyid."X".$gid."X".$attralist['fid'];
		if ($attralist['type'] == "M")
		{
			$qquery = "SELECT code FROM {$dbprefix}answers WHERE qid='".$attralist['qid']."' AND language='".$_SESSION['s_lang']."' order by code;";
			$qresult = db_execute_assoc($qquery);
			while ($fansrows = $qresult->FetchRow())
			{
				$fquestans = "java".$qfbase.$fansrows['code'];
				$tbody = "javatbd".$qbase.$fansrows['code'];
				$dtbody = "tbdisp".$qbase.$fansrows['code'];
				$tbodyae = $qbase.$fansrows['code'];
				$appendj .= "\n\t\t\tif ((document.getElementById('$fquestans').value == 'Y'))\n";
				$appendj .= "\t\t\t{\n";
				$appendj .= "\t\t\t\tdocument.getElementById('$tbody').style.display='';\n";
				$appendj .= "\t\t\t\tdocument.getElementById('$dtbody').value='on';\n";
				$appendj .= "\t\t\t}\n";
				$appendj .= "\t\t\telse\n";
				$appendj .= "\t\t\t{\n";
				$appendj .= "\t\t\t\tdocument.getElementById('$tbody').style.display='none';\n";
				$appendj .= "\t\t\t\tdocument.getElementById('$dtbody').value='off';\n";
				$appendj .= "\t\t\t\tradio_unselect(document.forms['limesurvey'].elements['$tbodyae']);\n";
				$appendj .= "\t\t\t}\n";
			}
		}
	}
	$java .= $appendj;
}


if (isset($java)) {echo $java;}
echo "\t\t\t\tif (navigator.userAgent.indexOf('Safari')>-1 && name !== undefined )\n"
."\t\t\t\t{ // Safari eats the onchange so run modfield manually, except when called at onload time\n"
."\t\t\t\t\t//alert('For Safari calling modfield for ' + name);\n"
."\t\t\t\t\tmodfield(name);\n"
."\t\t\t\t}\n"
."\t\t\t}\n"
."\t//-->\n"
."\t</script>\n\n"; // End checkconditions javascript function

foreach ($_SESSION['grouplist'] as $gl)
{
	$gid=$gl[0];
	$groupname=$gl[1];
	$groupdescription=$gl[2];
	echo "\n\n<!-- START THE GROUP -->\n";
	echo templatereplace(file_get_contents("$thistpl/startgroup.pstpl"));
	echo "\n";

	if ($groupdescription)
	{
		echo templatereplace(file_get_contents("$thistpl/groupdescription.pstpl"));
	}
	echo "\n";

	echo "\n\n<!-- PRESENT THE QUESTIONS -->\n";
	if (is_array($qanda))
	{
		foreach ($qanda as $qa)
		{
			if ($gl[0] == $qa[6])
			{
				echo "\n\t<!-- NEW QUESTION -->\n";
				echo "\t\t\t\t<div id='question$qa[4]'";
				if ($qa[3] != "Y") {echo ">\n";} else {echo " style='display: none'>\n";}
				$question="<label for='$qa[7]'>" . $qa[0] . "</label>";
				$answer=$qa[1];
				$help=$qa[2];
				$questioncode=$qa[5];
				echo templatereplace(file_get_contents("$thistpl/question.pstpl"));
				echo "\t\t\t\t</div>\n";
			}
		}
	}

	echo "\n\n<!-- END THE GROUP -->\n";
	echo templatereplace(file_get_contents("$thistpl/endgroup.pstpl"));
	echo "\n";
}
//echo "&nbsp;\n";
$navigator = surveymover();
echo "\n\n<!-- PRESENT THE NAVIGATOR -->\n";
echo templatereplace(file_get_contents("$thistpl/navigator.pstpl"));
echo "\n";

if ($thissurvey['active'] != "Y") {echo "\t\t<center><font color='red' size='2'>".$clang->gT("This survey is not currently active. You will not be able to save your responses.")."</font></center>\n";}


if (is_array($conditions) && count($conditions) != 0 ) 
{
	//if conditions exist, create hidden inputs for 'previously' answered questions
	// Note that due to move 'back' possibility, there may be answers from next pages
	// However we make sure that no answer from this page are inserted here
	foreach (array_keys($_SESSION) as $SESak)
	{
		if (in_array($SESak, $_SESSION['insertarray']) && !in_array($SESak, $inputnames))
		{
			echo "<input type='hidden' name='java$SESak' id='java$SESak' value='" . $_SESSION[$SESak] . "' />\n";
		}
	}
}
//SOME STUFF FOR MANDATORY QUESTIONS
if (remove_nulls_from_array($mandatorys))
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

echo "<input type='hidden' name='thisstep' value='{$_SESSION['step']}' id='thisstep'>\n"
."<input type='hidden' name='sid' value='$surveyid' id='sid'>\n"
."<input type='hidden' name='token' value='$token' id='token'>\n"
."</form>\n";
echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
echo "\n";
doFooter();

?>
