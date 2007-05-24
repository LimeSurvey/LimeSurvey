<?php
/*
#############################################################
# >>> LimeSurvey 	 										#
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

// Performance optimized	: Nov 27, 2006
// Performance Improvement	: 41% (Call to templatereplace())
// Optimized By				: swales

if (empty($homedir)) {die ("Cannot run this script directly");}

//Move current step ###########################################################################
if (!isset($_SESSION['step'])) {$_SESSION['step']=0;}
if (!isset($_SESSION['totalsteps'])) {$_SESSION['totalsteps']=0;}
if (!isset($_POST['thisstep'])) {$_POST['thisstep'] = "";}
if (!isset($gl)) {$gl=array("null");}
if (isset($_POST['move']) && $_POST['move'] == "moveprev") {$_SESSION['step'] = $_POST['thisstep']-1;}
if (isset($_POST['move']) && $_POST['move'] == "movenext") {$_SESSION['step']=$_POST['thisstep']+1;}
if (isset($_POST['move']) && $_POST['move'] == "movelast") {$_SESSION['step'] = $_POST['thisstep']+1;}

// If on SUBMIT page and select SAVE SO FAR it will return to SUBMIT page
if ($_SESSION['step'] > $_SESSION['totalsteps'] && $_POST['move'] != "movesubmit")
{
	$_POST['move'] = "movelast";
}

//CHECK IF ALL MANDATORY QUESTIONS HAVE BEEN ANSWERED ############################################
//First, see if we are moving backwards or doing a Save so far, and its OK not to check:
if ($allowmandbackwards==1 && ((isset($_POST['move']) &&  $_POST['move'] == "moveprev") || (isset($_POST['saveall']) && $_POST['saveall'] == $clang->gT("Save your responses so far"))))
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

//SEE IF THIS GROUP SHOULD DISPLAY
if (isset($_POST['move']) && $_SESSION['step'] != 0 && $_POST['move'] != "movelast" && $_POST['move'] != "movesubmit")
{
	while(checkgroupfordisplay($_SESSION['grouplist'][$_SESSION['step']-1][0]) === false)
	{
		if (isset($_POST['move']) && $_POST['move'] == "moveprev") 
        {
            $_SESSION['step']=$_SESSION['step']-1;
        }
		if (isset($_POST['move']) && $_POST['move'] == "movenext") 
        {
            $_SESSION['step']=$_SESSION['step']+1;
        }
		if ($_SESSION['step']-1 == $_SESSION['totalsteps'])
		{
			$_POST['move'] = "movelast";
			break;
		}
	}
}

//SUBMIT ###############################################################################
if (isset($_POST['move']) && $_POST['move'] == "movesubmit")
{
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

		echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

		//Check for assessments
		$assessments = doAssessment($surveyid);
		if ($assessments)
		{
			echo templatereplace(file_get_contents("$thistpl/assessment.pstpl"));
		}

		$completed = "<br /><strong><font size='2' color='red'>".$clang->gT("Did Not Save")."</font></strong><br /><br />\n\n";
		$completed .= $clang->gT("Your survey responses have not been recorded. This survey is not yet active.")."<br /><br />\n";
		$completed .= "<a href='{$_SERVER['PHP_SELF']}?sid=$surveyid&amp;move=clearall'>".$clang->gT("Clear Responses")."</a><br /><br />\n";
	}
	else
	{
		if ($thissurvey['usecookie'] == "Y" && $tokensexist != 1)
		{
			$cookiename="PHPSID".returnglobal('sid')."STATUS";
			setcookie("$cookiename", "COMPLETE", time() + 31536000);
		}

		$content='';
		$content .= templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

		//echo $thissurvey['url'];
		//Check for assessments
		$assessments = doAssessment($surveyid);
		if ($assessments)
		{
			$content .= templatereplace(file_get_contents("$thistpl/assessment.pstpl"));
		}
        /* Here I must study the possibility to branch if the questionnarie were
           completed or not */
		$completed = "<br /><font size='2'><font color='green'><strong>"
		           . $clang->gT("Thank you")."</strong></font><br /><br />\n\n"
		           . $clang->gT("Your survey responses have been recorded.")."<br />\n"
			       . "<a href='javascript:window.close()'>"
			       . $clang->gT("Close this Window")."</a></font><br /><br />\n";

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

		sendcacheheaders();
		if (isset($thissurvey['autoredirect']) && $thissurvey['autoredirect'] == "Y" && $thissurvey['url'])
		{
			//Automatically redirect the page to the "url" setting for the survey
			session_write_close();
            $redir = $thissurvey['url'];
                
            // Add the token to the redirect just in case
            if (isset($mytoken)) 
            {
            			$redir .= "?token=".$mytoken;
			}
			header("Location: {$redir}");
		}

		doHeader();
		echo $content;
	}

//	foreach(file("$thistpl/completed.pstpl") as $op)
//	{
//		echo templatereplace($op);
//	}
	echo templatereplace(file_get_contents("$thistpl/completed.pstpl"));

	echo "\n<br />\n";
//	foreach(file("$thistpl/endpage.pstpl") as $op)
//	{
//		echo templatereplace($op);
//	}
	echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));

	exit;
}

//LAST PHASE ###########################################################################
if (isset($_POST['move']) && $_POST['move'] == "movelast" && (!isset($notanswered) || !$notanswered) && (!isset($notvalidated) && !$notvalidated))
{
	//READ TEMPLATES, INSERT DATA AND PRESENT PAGE
	sendcacheheaders();
	doHeader();
	if ($thissurvey['private'] != "N")
	{
		$privacy="";
		$privacy .= templatereplace(file_get_contents("$thistpl/privacy.pstpl"));

	}
	
    echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
	echo "\n<form method='post' action='{$_SERVER['PHP_SELF']}' id='limesurvey' name='limesurvey'>\n";
	echo "\n\n<!-- START THE SURVEY -->\n";
    echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));

	//READ SUBMIT TEMPLATE
	echo templatereplace(file_get_contents("$thistpl/submit.pstpl"));
	$navigator = surveymover();
	echo "\n\n<!-- PRESENT THE NAVIGATOR -->\n";
	echo templatereplace(file_get_contents("$thistpl/navigator.pstpl"));
print <<<END
	<input type='hidden' name='thisstep' value='{$_SESSION['step']}' id='thisstep' />
	<input type='hidden' name='sid' value='$surveyid' id='sid' />
	<input type='hidden' name='token' value='$token' id='token' />
	</form>
END;
	echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));

	doFooter();
	exit;
}

//SEE IF $surveyid EXISTS ####################################################################
if ($surveyexists <1)
{
	//SURVEY DOES NOT EXIST. POLITELY EXIT.
	echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
    echo "\t<center><br />\n";
	echo "\t".$clang->gT("Sorry. There is no matching survey.")."<br />&nbsp;\n";
	echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
	exit;
}

//RUN THIS IF THIS IS THE FIRST TIME , OR THE FIRST PAGE ########################################
if (!isset($_SESSION['step']) || !$_SESSION['step'])
{
	$totalquestions = buildsurveysession();
	sendcacheheaders();
	doHeader();
	echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
	echo "\n<form method='post' action='{$_SERVER['PHP_SELF']}' id='limesurvey' name='limesurvey'>\n";
	echo "\n\n<!-- START THE SURVEY -->\n";
	echo templatereplace(file_get_contents("$thistpl/welcome.pstpl"));
	echo "\n";
	$navigator = surveymover();
	echo templatereplace(file_get_contents("$thistpl/navigator.pstpl"));

	if ($thissurvey['active'] != "Y")
	{
		echo "\t\t<center><font color='red' size='2'>".$clang->gT("This survey is not currently active. You will not be able to save your responses.")."</font></center>\n";
	}
	echo "\n<input type='hidden' name='sid' value='$surveyid' id='sid' />\n";
	echo "\n<input type='hidden' name='token' value='$token' id='token' />\n";
	echo "\n</form>\n";
	echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
	doFooter();
	exit;
}

//******************************************************************************************************
//PRESENT SURVEY
//******************************************************************************************************

//GET GROUP DETAILS
$grouparrayno=$_SESSION['step']-1;
$gid=$_SESSION['grouplist'][$grouparrayno][0];
$groupname=$_SESSION['grouplist'][$grouparrayno][1];
$groupdescription=$_SESSION['grouplist'][$grouparrayno][2];

require_once("qanda.php"); //This should be qanda.php when finished

//Iterate through the questions about to be displayed:
$mandatorys=array();
$mandatoryfns=array();
$conmandatorys=array();
$conmandatoryfns=array();
$conditions=array();
$inputnames=array();

// Latter is needed an array to get the tittle of the question and
// its id for the form, with this will be more easy locate the
// key for to construct the javascript code

$titlejsid = array();

foreach ($_SESSION['fieldarray'] as $ia)
{
    $titlejsid[$ia[2]] = $ia[1];

	if ($ia[5] == $gid)
	{
		//Get the answers/inputnames
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
	}
} //end iteration


$percentcomplete = makegraph($_SESSION['step'], $_SESSION['totalsteps']);

//READ TEMPLATES, INSERT DATA AND PRESENT PAGE
sendcacheheaders();
doHeader();

if (isset($popup)) {echo $popup;}
if (isset($vpopup)) {echo $vpopup;}
//foreach(file("$thistpl/startpage.pstpl") as $op)
//{
//  echo templatereplace($op);
//}
	echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

$hiddenfieldnames=implode("|", $inputnames);
print <<<END
<form method='post' action='{$_SERVER['PHP_SELF']}' id='limesurvey' name='limesurvey'>

<!-- INPUT NAMES -->
<input type='hidden' name='fieldnames' value='{$hiddenfieldnames}' id='fieldnames' />
END;

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

echo "' id='modfields' />\n";
echo "\n";
echo "\n\n<!-- JAVASCRIPT FOR MODIFIED QUESTIONS -->\n";
echo " <script type='text/javascript'>\n";
echo " <!--\n";
echo "  function modfield(name)\n";
echo "   {\n";
echo "    temp=document.getElementById('modfields').value;\n";
echo "    if (temp=='') {\n";
echo "     document.getElementById('modfields').value=name;\n";
echo "    }\n";
echo "    else {\n";
echo "     myarray=temp.split('|');\n";
echo "     if (!inArray(name, myarray)) {\n";
echo "      myarray.push(name);\n";
echo "      document.getElementById('modfields').value=myarray.join('|');\n";
echo "     }\n";
echo "    }\n";
echo "   }\n";
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
echo "\n";
echo "    function ValidDate(oObject)\n";
echo "    {// --- Regular expression used to check if date is in correct format\n";
echo "     var str_regexp = /[1-9][0-9]{3}-(0[1-9]|1[0-2])-([0-2][0-9]|3[0-1])/;\n";
echo "     var pattern = new RegExp(str_regexp);\n";
echo "     if ( oObject.value=='')\n";
echo "     { return true;\n";
echo "     }\n";
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
echo " //-->\n";
echo " </script>\n\n";
// <-- END NEW FEATURE - SAVE

// <-- START THE SURVEY -->

//foreach(file("$thistpl/survey.pstpl") as $op)
//{
//	echo "\t".templatereplace($op);
//}
	echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));

print <<<END

<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->
<script type='text/javascript'>
<!--

END;
// Find out if there are any array_filter questions in this group
$array_filterqs = getArrayFiltersForGroup($gid);
// Put in the radio button reset javascript for the array filter unselect
if (isset($array_filterqs) && is_array($array_filterqs)) 
{
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

print <<<END
	function checkconditions(value, name, type)
	{
    
END;

// If there are conditions or arrray_filter questions then include the appropriate Javascript
if ((isset($conditions) && is_array($conditions)) ||
    (isset($array_filterqs) && is_array($array_filterqs)))
{
    if (!isset($endzone))
    {
        $endzone="";
    }
//        if (type == 'radio' || type == 'select-one' || type == 'text')
print <<<END
        if (type == 'radio' || type == 'select-one')
        {
            var hiddenformname='java'+name;
            document.getElementById(hiddenformname).value=value;
        }

        if (type == 'checkbox')
        {
            var hiddenformname='java'+name;
            var chkname='answer'+name;
            if (document.getElementById(chkname).checked)
            {
                document.getElementById(hiddenformname).value='Y';
            } else
            {
		        document.getElementById(hiddenformname).value='';
            }
        }
END;
    $java="";
	$cqcount=1;

    /* $conditions element structure
    * $condition[n][0] => question id
    * $condition[n][1] => question with value to evaluate
    * $condition[n][2] => internal field name of element [1]
    * $condition[n][3] => value to be evaluated on answers labeled. 
    *                     *NEW* tittle of questions to evaluate.
    * $condition[n][4] => type of question
    * $condition[n][5] => equal to [2], but concatenated in this time (why the same value 2 times?)
    * $condition[n][6] => method used to evaluate *NEW*
    */
	foreach ($conditions as $cd)
	{
		if ((isset($oldq) && $oldq != $cd[0]) || !isset($oldq)) //New if statement
		{
			$java .= $endzone;
			$endzone = "";
			$cqcount=1;
            $java .= "\n\tif ((";
        }
        if (!isset($oldcq) || !$oldcq)
        {
            $oldcq = $cd[2];
		}
		if ($cd[4] == "L") //Just in case the dropdown threshold is being applied, check number of answers here
        {
			$cccquery="SELECT COUNT(*) FROM {$dbprefix}answers WHERE qid={$cd[1]} AND language='".$_SESSION['s_lang']."'";
			$cccresult=db_execute_num($cccquery);
			list($cccount) = $cccresult->FetchRow();
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
                $cd[4] == "H" ||
                $cd[4] == "G" ||
                $cd[4] == "Y" ||
               ($cd[4] == "L" && $cccount <= $dropdownthreshold))
        {
            $idname="java$cd[2]";
        }
        elseif ($cd[4] == "M" ||
                $cd[4] == "P")
        {
            $idname="java$cd[2]$cd[3]";
        }
        elseif ($cd[4] == "D" ||
                $cd[4] == "S")
        {
            $idname="answer$cd[2]";
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

        // The [3] element is for the value used to be compared with
        // If it is '' (empty) means not answered
        // then a space or a false are interpreted as no answer
        // as we let choose if the questions is answered or not
        // and doesn´t care the answer, so we wait for a == or !=
        if ($cd[3] == '')
        {
            if ($cd[6] == '==')
            {
                $java .= "document.getElementById('$idname').value == ' ' || !document.getElementById('$idname').value";
            } else 
            {
                // strange thing, isn´t? whell 0, ' ', '' or false are all false logic values then...
                $java .= "document.getElementById('$idname').value";
            }
        }
	    elseif ($cd[4] == "M" || 
                $cd[4] == "P" ||
                $cd[4] == "!")
	    {
		    //$java .= "!document.getElementById('$idname') || document.getElementById('$idname').value == ' '";
            $java .= "document.getElementById('$idname').value == 'Y'"; //
	    } else
        {
            /* NEW
            * If the value is enclossed by @
            * the value of this question must be evaluated instead.
            * Remember $titlejs array? It will be used now
            * Another note: It´s not clear why is used a norma to call
            * some elements like java*, answer* or just the fieldname
            * as far I can see now it´s used the field name for text elements
            * so, I´ll use by id with the prefix answer
            */
            if (ereg('^@[^@]+@', $cd[3]))
            {
                $auxqtitle = substr($cd[3],1,strlen($cd[3])-2);
                $java .= "(document.getElementById('answer" . $cd[2] . "').value != '') && ";

                if (in_array($titlejsid[$auxqtitle], $_SESSION['insertarray']))
		        {
                    $java .= "(document.getElementById('answer" . $titlejsid[$auxqtitle] . "').value != '') && ";
                    $java .= "(document.getElementById('answer" . $cd[2] . "').value $cd[6] document.getElementById('answer".$titlejsid[$auxqtitle]."').value)";
                } else 
                {
                    $java .= "(document.getElementById('answer" . $titlejsid[$auxqtitle] . "').value != '') && ";
                    $java .= "(document.getElementById('answer" . $cd[2] . "').value $cd[6] document.getElementById('answer".$titlejsid[$auxqtitle]."').value)";
	            }
		    } else
		    {
	            if ($cd[3]) //Well supose that we are comparing a non empty value
	            {
	                $java .= "document.getElementById('$idname').value != '' && ";
	            }
                $java .= "document.getElementById('$idname').value $cd[6] '$cd[3]'";
	        }
	    }
	    if ((isset($oldq) && $oldq != $cd[0]) || !isset($oldq))//Close if statement
	    {
		    $endzone = "))\n";
		    $endzone .= "\t{\n";
		    $endzone .= "\t\tdocument.getElementById('question$cd[0]').style.display='';\n";
		    $endzone .= "\t\tdocument.getElementById('display$cd[0]').value='on';\n";
		    $endzone .= "\t}\n";
		    $endzone .= "\telse\n";
		    $endzone .= "\t{\n";
		    $endzone .= "\t\tdocument.getElementById('question$cd[0]').style.display='none';\n";
		    $endzone .= "\t\tdocument.getElementById('display$cd[0]').value='';\n";
		    $endzone .= "\t}\n";
		    $cqcount++;
	    }
	    $oldq = $cd[0]; //Update oldq for next loop
	    $oldcq = $cd[2];  //Update oldcq for next loop
    } // end foreach
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
			$qquery = "SELECT code FROM {$dbprefix}answers WHERE qid='".$attralist['qid']."' AND language=".$_SESSION['s_lang']." order by code;";
			$qresult = db_execute_assoc($qquery);
			while ($fansrows = $qresult->FetchRow())
			{
				$fquestans = "java".$qfbase.$fansrows['code'];
				$tbody = "javatbd".$qbase.$fansrows['code'];
				$dtbody = "tbdisp".$qbase.$fansrows['code'];
				$tbodyae = $qbase.$fansrows['code'];
                $appendj .= "\n";
                $appendj .= "\tif ((document.getElementById('$fquestans').value == 'Y'))\n";
				$appendj .= "\t{\n";
				$appendj .= "\t\tdocument.getElementById('$tbody').style.display='';\n";
				$appendj .= "\t\tdocument.getElementById('$dtbody').value='on';\n";
				$appendj .= "\t}\n";
				$appendj .= "\telse\n";
				$appendj .= "\t{\n";
				$appendj .= "\t\tdocument.getElementById('$tbody').style.display='none';\n";
				$appendj .= "\t\tdocument.getElementById('$dtbody').value='off';\n";
				$appendj .= "\t\tradio_unselect(document.forms['phpsurveyor'].elements['$tbodyae']);\n";
				$appendj .= "\t}\n";
			}
		}
	}
	$java .= $appendj;
}

if (isset($java)) {echo $java;}
echo "\t\t\tif (navigator.appVersion.indexOf('Safari')>-1 && name !== undefined )\n"
."\t\t\t{ // Safari eats the onchange so run modfield manually, expect when called at onload time\n"
."\t\t\t\t//alert('For Safari calling modfield for ' + name);\n"
."\t\t\t\tmodfield(name);\n"
."\t\t\t}\n"
."\t\t}\n"
."\t//-->\n"
."\t</script>\n\n"; // End checkconditions javascript function

echo "\n\n<!-- START THE GROUP -->\n";
echo templatereplace(file_get_contents("$thistpl/startgroup.pstpl"));
echo "\n";

if ($groupdescription)
{
	echo templatereplace(file_get_contents("$thistpl/groupdescription.pstpl"));
}
echo "\n";


echo "\n\n<!-- PRESENT THE QUESTIONS -->\n";
if (isset($qanda) && is_array($qanda))
{
	foreach ($qanda as $qa)
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
echo "\n\n<!-- END THE GROUP -->\n";
echo templatereplace(file_get_contents("$thistpl/endgroup.pstpl"));
echo "\n";

$navigator = surveymover(); //This gets globalised in the templatereplace function

echo "\n\n<!-- PRESENT THE NAVIGATOR -->\n";
echo templatereplace(file_get_contents("$thistpl/navigator.pstpl"));
echo "\n";

if ($thissurvey['active'] != "Y")
{
	echo "\t\t<center><font color='red' size='2'>".$clang->gT("This survey is not currently active. You will not be able to save your responses.")."</font></center>\n";
}

echo "<!-- group2.php -->\n"; //This can go eventually - it's redundent for debugging

if (isset($conditions) && is_array($conditions) && count($conditions) != 0)
{
	//if conditions exist, create hidden inputs for 'previously' answered questions
	// Note that due to move 'back' possibility, there may be answers from next pages
	// However we make sure that no answer from this page are inserted here
	foreach (array_keys($_SESSION) as $SESak)
	{
		if (in_array($SESak, $_SESSION['insertarray'])  && !in_array($SESak, $inputnames))
		{
			echo "<input type='hidden' name='java$SESak' id='java$SESak' value='" . htmlspecialchars($_SESSION[$SESak],ENT_QUOTES). "' />\n";
		}
	}
}
//SOME STUFF FOR MANDATORY QUESTIONS
if (remove_nulls_from_array($mandatorys))
{
	$mandatory=implode("|", remove_nulls_from_array($mandatorys));
	echo "<input type='hidden' name='mandatory' value='$mandatory' id='mandatory' />\n";
}
if (remove_nulls_from_array($conmandatorys))
{
	$conmandatory=implode("|", remove_nulls_from_array($conmandatorys));
	echo "<input type='hidden' name='conmandatory' value='$conmandatory' id='conmandatory' />\n";
}
if (remove_nulls_from_array($mandatoryfns))
{
	$mandatoryfn=implode("|", remove_nulls_from_array($mandatoryfns));
	echo "<input type='hidden' name='mandatoryfn' value='$mandatoryfn' id='mandatoryfn' />\n";
}
if (remove_nulls_from_array($conmandatoryfns))
{
	$conmandatoryfn=implode("|", remove_nulls_from_array($conmandatoryfns));
	echo "<input type='hidden' name='conmandatoryfn' value='$conmandatoryfn' id='conmandatoryfn' />\n";
}

echo "<input type='hidden' name='thisstep' value='{$_SESSION['step']}' id='thisstep' />\n";
echo "<input type='hidden' name='sid' value='$surveyid' id='sid' />\n";
echo "<input type='hidden' name='token' value='$token' id='token' />\n";
echo "</form>\n";

echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));

echo "\n";
doFooter();

?>
