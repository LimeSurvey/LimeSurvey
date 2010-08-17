<?php
/*
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

//Security Checked: POST, GET, SESSION, REQUEST, returnglobal, DB

if (!isset($homedir) || isset($_REQUEST['$homedir'])) {die("Cannot run this script directly");}

//Move current step ###########################################################################
if (!isset($_SESSION['step'])) {$_SESSION['step']=0;}
if (!isset($_SESSION['totalsteps'])) {$_SESSION['totalsteps']=0;}
if (!isset($gl)) {$gl=array('null');}
if (isset($move) && $move == 'moveprev' && $thissurvey['allowprev']=='Y') {$_SESSION['step'] = $thisstep-1;}
if (isset($move) && $move == "movenext") {
    if ($_SESSION['step']==$thisstep)
    $_SESSION['step'] = $thisstep+1;
}
include_once 'common.php';
// We do not keep the participant session anymore when the same browser is used to answer a second time a survey (let's think of a library PC for instance).
// Previously we used to keep the session and redirect the user to the
// submit page.
//if (isset($_SESSION['finished'])) {$move='movesubmit'; }



//CHECK IF ALL MANDATORY QUESTIONS HAVE BEEN ANSWERED ############################################
//First, see if we are moving backwards or doing a Save so far, and its OK not to check:
if ($allowmandbackwards==1 && ((isset($move) &&  $move == 'moveprev') || (isset($_POST['saveall']) && $_POST['saveall'] == $clang->gT("Save your responses so far"))))
{
    $backok="Y";
}
else
{
    $backok="N";
}

//Now, we check mandatory questions if necessary
//CHECK IF ALL CONDITIONAL MANDATORY QUESTIONS THAT APPLY HAVE BEEN ANSWERED
$notanswered=addtoarray_single(checkmandatorys($move,$backok),checkconditionalmandatorys($move,$backok));

//CHECK PREGS
$notvalidated=checkpregs($move,$backok);

// CHECK UPLOADED FILES
$filenotvalidated = checkUploadedFileValidity($move, $backok);

//SEE IF THIS GROUP SHOULD DISPLAY
$show_empty_group = false;
if (isset($move) && $_SESSION['step'] != 0 && $move != "movesubmit")
{
    while(isset($_SESSION['grouplist'][$_SESSION['step']-1]) && checkgroupfordisplay($_SESSION['grouplist'][$_SESSION['step']-1][0]) === false)
    {
        if (isset($move) && $move == "moveprev")
        {
            $_SESSION['step']=$_SESSION['step']-1;
        }
        if (isset($move) && $move == "movenext")
        {
            $_SESSION['step']=$_SESSION['step']+1;
        }
        if ($_SESSION['step']>$_SESSION['totalsteps'])
        {
            // We are skipping groups, but we moved 'off' the last group.
            // Now choose to implement an implicit submit (old behaviour),
            // or create an empty page giving the user the explicit option to submit.
            if (isset($show_empty_group_if_the_last_group_is_hidden) && $show_empty_group_if_the_last_group_is_hidden == true)
            {
                $show_empty_group = true;
                break;
            } else
            {
                $move = "movesubmit";
                submitanswer(); // complete this answer (submitdate)
                break;
            }
        }
    }
}

//SUBMIT ###############################################################################
if ((isset($move) && $move == "movesubmit")  && (!isset($notanswered) || !$notanswered) && (!isset($notvalidated) || !$notvalidated ) && (!isset($filenotvalidated) || !$filenotvalidated))
{
    if ($thissurvey['refurl'] == "Y")
    {
        if (!in_array("refurl", $_SESSION['insertarray'])) //Only add this if it doesn't already exist
        {
            $_SESSION['insertarray'][] = "refurl";
        }
    }

    //COMMIT CHANGES TO DATABASE
    if ($thissurvey['active'] != "Y") //If survey is not active, don't really commit
    {
        if ($thissurvey['assessments']== "Y")
        {
            $assessments = doAssessment($surveyid);
        }

        if($thissurvey['printanswers'] != 'Y')
        {
            killSession();
        }

        sendcacheheaders();
        doHeader();

        echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

        //Check for assessments
        if ($thissurvey['assessments']== "Y" && $assessments)
        {
            echo templatereplace(file_get_contents("$thistpl/assessment.pstpl"));
        }

        $completed = $thissurvey['surveyls_endtext'];
        $completed .= "<br /><strong><font size='2' color='red'>".$clang->gT("Did Not Save")."</font></strong><br /><br />\n\n";
        $completed .= $clang->gT("Your survey responses have not been recorded. This survey is not yet active.")."<br /><br />\n";
        if ($thissurvey['printanswers'] == 'Y')
        {
            // ClearAll link is only relevant for survey with printanswers enabled
            // in other cases the session is cleared at submit time
            $completed .= "<a href='{$publicurl}/index.php?sid=$surveyid&amp;move=clearall'>".$clang->gT("Clear Responses")."</a><br /><br />\n";
        }
    }
    else //THE FOLLOWING DEALS WITH SUBMITTING ANSWERS AND COMPLETING AN ACTIVE SURVEY
    {
        if ($thissurvey['usecookie'] == "Y" && $tokensexist != 1) //don't use cookies if tokens are being used
        {
            $cookiename="PHPSID".returnglobal('sid')."STATUS";
            setcookie("$cookiename", "COMPLETE", time() + 31536000); //Cookie will expire in 365 days
        }

        //Before doing the "templatereplace()" function, check the $thissurvey['url']
        //field for limereplace stuff, and do transformations!
        $thissurvey['surveyls_url']=insertansReplace($thissurvey['surveyls_url']);
        $thissurvey['surveyls_url']=passthruReplace($thissurvey['surveyls_url'], $thissurvey);

        $content='';
        $content .= templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

        //Check for assessments
        if ($thissurvey['assessments']== "Y")
        {
            $assessments = doAssessment($surveyid);
            if ($assessments)
            {
                $content .= templatereplace(file_get_contents("$thistpl/assessment.pstpl"));
            }
        }

        //Update the token if needed and send a confirmation email
        if (isset($clienttoken) && $clienttoken)
        {
            submittokens();
        }

        //Send notification to survey administrator //Thanks to Jeff Clement http://jclement.ca
        if ($thissurvey['sendnotification'] > 0 && $thissurvey['adminemail'])
        {
            sendsubmitnotification($thissurvey['sendnotification']);
        }

        $content='';

        $content .= templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

        //echo $thissurvey['url'];
        //Check for assessments
        if ($thissurvey['assessments']== "Y")
        {
            $assessments = doAssessment($surveyid);
            if ($assessments)
            {
                $content .= templatereplace(file_get_contents("$thistpl/assessment.pstpl"));
            }
        }


        if (trim(strip_tags($thissurvey['surveyls_endtext']))=='')
        {
            $completed = "<br /><span class='success'>".$clang->gT("Thank you!")."</span><br /><br />\n\n"
            . $clang->gT("Your survey responses have been recorded.")."<br /><br />\n";
        }
        else
        {
            $completed = $thissurvey['surveyls_endtext'];
        }

        // Link to Print Answer Preview  **********
        if ($thissurvey['printanswers']=='Y')
        {
            $completed .= "<br /><br />"
            ."<a class='printlink' href='printanswers.php?sid=$surveyid'  target='_blank'>"
            .$clang->gT("Print your answers.")
            ."</a><br />\n";
        }
        //*****************************************

        if ($thissurvey['publicstatistics']=='Y' && $thissurvey['printanswers']=='Y') {$completed .='<br />'.$clang->gT("or");}

        // Link to Public statistics  **********
        if ($thissurvey['publicstatistics']=='Y')
        {
            $completed .= "<br /><br />"
            ."<a class='publicstatisticslink' href='statistics_user.php?sid=$surveyid' target='_blank'>"
            .$clang->gT("View the statistics for this survey.")
            ."</a><br />\n";
        }
        //*****************************************

        $_SESSION['finished']=true;
        $_SESSION['sid']=$surveyid;

        sendcacheheaders();
        if (isset($thissurvey['autoredirect']) && $thissurvey['autoredirect'] == "Y" && $thissurvey['surveyls_url'])
        {
            //Automatically redirect the page to the "url" setting for the survey

            $url = $thissurvey['surveyls_url'];
            $url = insertansReplace($thissurvey['surveyls_url']);
            $url = passthruReplace($url, $thissurvey);
            $url=str_replace("{SAVEDID}",$saved_id, $url);			   // to activate the SAVEDID in the END URL
            $url=str_replace("{TOKEN}",$clienttoken, $url);          // to activate the TOKEN in the END URL
            $url=str_replace("{SID}", $surveyid, $url);              // to activate the SID in the END URL
            $url=str_replace("{LANG}", $clang->getlangcode(), $url); // to activate the LANG in the END URL

            header("Location: {$url}");

        }


        //if($thissurvey['printanswers'] != 'Y' && $thissurvey['usecookie'] != 'Y' && $tokensexist !=1)
        if($thissurvey['printanswers'] != 'Y')
        {
            killSession();
        }

        doHeader();
        echo $content;

    }

    echo templatereplace(file_get_contents("$thistpl/completed.pstpl"));
    echo "\n<br />\n";
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
    echo "\t".$clang->gT("Sorry. There is no matching survey.")."<br /></center>&nbsp;\n";
    echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
    doFooter();
    exit;
}

//RUN THIS IF THIS IS THE FIRST TIME , OR THE FIRST PAGE ########################################
if (!isset($_SESSION['step']) || !$_SESSION['step'])
{
    $totalquestions = buildsurveysession();
    sendcacheheaders();
    doHeader();
    echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
    echo "\n<form method='post' action='{$publicurl}/index.php' id='limesurvey' name='limesurvey' autocomplete='off'>\n";
    echo "\n\n<!-- START THE SURVEY -->\n";
    echo templatereplace(file_get_contents("$thistpl/welcome.pstpl"))."\n";
    if ($thissurvey['private'] == "Y")
    {
        echo templatereplace(file_get_contents("$thistpl/privacy.pstpl"))."\n";
    }
    $navigator = surveymover();
    echo templatereplace(file_get_contents("$thistpl/navigator.pstpl"));

    if ($thissurvey['active'] != "Y")
    {
        echo "<center><font color='red' size='2'>".$clang->gT("This survey is not currently active. You will not be able to save your responses.")."</font></center>\n";
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
if ($show_empty_group) {
    $gid=-1; // Make sure the gid is unused. This will assure that the foreach (fieldarray as ia) has no effect.
    $groupname=$clang->gT("Submit your answers");
    $groupdescription=$clang->gT("There are no more questions. Please press the <Submit> button to finish this survey.");
} else
{
    $grouparrayno=$_SESSION['step']-1;
    $gid=$_SESSION['grouplist'][$grouparrayno][0];
    $groupname=$_SESSION['grouplist'][$grouparrayno][1];
    $groupdescription=$_SESSION['grouplist'][$grouparrayno][2];
}

require_once("qanda.php"); //This should be qanda.php when finished

//Iterate through the questions about to be displayed:
$mandatorys=array();
$mandatoryfns=array();
$conmandatorys=array();
$conmandatoryfns=array();
$conditions=array();
$inputnames=array();

$qtypesarray = array();

$qnumber = 0;

foreach ($_SESSION['fieldarray'] as $ia)
{
    $qtypesarray[$ia[1]] = $ia[4];
    ++$qnumber;
    $ia[9] = $qnumber; // incremental question count;

    if ($ia[5] == $gid)
    {
        $qidattributes=getQuestionAttributes($ia[0]);
        if ($qidattributes['hidden']==1) {
            // Should we really skip the question here, maybe the result won't be stored if we do that
            continue;
        }

        //Get the answers/inputnames
        list($plus_qanda, $plus_inputnames)=retrieveAnswers($ia);
        if ($plus_qanda)
        {
            $plus_qanda[] = $ia[4];
            $plus_qanda[] = $ia[6]; // adds madatory identifyer for adding mandatory class to question wrapping div
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

        // Display the "file validation" popup if necessary
        if (isset($filenotvalidated))
        {
            list($filevalidationpopup, $fpopup) = file_validation_popup($ia, $filenotvalidated);
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
    if ($ia[4] == "|")
        $upload_file = TRUE;
} //end iteration

if ($show_empty_group) {
    $percentcomplete = makegraph($_SESSION['totalsteps']+1, $_SESSION['totalsteps']);
} else
{
    $percentcomplete = makegraph($_SESSION['step'], $_SESSION['totalsteps']);
}
$languagechanger = makelanguagechanger();

//READ TEMPLATES, INSERT DATA AND PRESENT PAGE
sendcacheheaders();
doHeader();

if (isset($popup)) {echo $popup;}
if (isset($vpopup)) {echo $vpopup;}
if (isset($fpopup)) {echo $fpopup;}

//foreach(file("$thistpl/startpage.pstpl") as $op)
//{
//  echo templatereplace($op);
//}
echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

$hiddenfieldnames=implode("|", $inputnames);

if (isset($upload_file) && $upload_file)
    echo "<form enctype=\"multipart/form-data\" method='post' action='{$_SERVER['PHP_SELF']}' id='limesurvey' name='limesurvey' autocomplete='off'>
      <!-- INPUT NAMES -->
      <input type='hidden' name='fieldnames' value='{$hiddenfieldnames}' id='fieldnames' />\n";
else
    echo "<form method='post' action='{$_SERVER['PHP_SELF']}' id='limesurvey' name='limesurvey' autocomplete='off'>
      <!-- INPUT NAMES -->
      <input type='hidden' name='fieldnames' value='{$hiddenfieldnames}' id='fieldnames' />\n";

// <-- END FEATURE - SAVE

// <-- START THE SURVEY -->

echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));

// the runonce element has been changed from a hidden to a text/display:none one
// in order to workaround an not-reproduced issue #4453 (lemeur)
echo "<input type='text' id='runonce' value='0' style='display: none;'/>
    <!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->
    <script type='text/javascript'>
    <!--\n";

// Find out if there are any array_filter questions in this group
if ($show_empty_group) {
    unset($array_filterqs);
    unset($array_filterXqs);
    unset($array_filterXqs_cascades);
} else
{
    $array_filterqs = getArrayFiltersForGroup($surveyid,$gid);
    $array_filterXqs = getArrayFilterExcludesForGroup($surveyid,$gid);
    $array_filterXqs_cascades = getArrayFilterExcludesCascadesForGroup($surveyid, $gid);
}

print <<<END
	function noop_checkconditions(value, name, type)
	{
	}

	function checkconditions(value, name, type)
	{
    
END;

// If there are conditions or arrray_filter questions then include the appropriate Javascript
if ((isset($conditions) && is_array($conditions)) ||
(isset($array_filterqs) && is_array($array_filterqs)) ||
(isset($array_filterXqs) && is_array($array_filterXqs)))
{
    if (!isset($endzone))
    {
        $endzone="";
    }

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
     * $condition[n][5] => full SGQ code of question [1]
     * $condition[n][6] => method used to evaluate *NEW*
     * $condition[n][7] => scenario *NEW BY R.L.J. van den Burg*
     */

    for ($i=0;$i<count($conditions);$i++)
    {
        $cd=$conditions[$i]; // this is the currently evaluated condition
        if (trim($cd[6])=='') $cd[6]='=='; // assume operator == when not defined

        // If this is a New Question ('New If Statement'):
        // * add the endzone to output and reset it
        // * reset the cqcount (used to append && or || to conditions
        // * set the runonce flag to true (will stay true if no condition is on a question from this group)
        // * initialize the new if statement code in newjava to the empty string (will be appended to $java at 'After If Statement')
        if ((isset($oldq) && $oldq != $cd[0]) || !isset($oldq))
        {
            $java .= $endzone;
            $endzone = "";
            $cqcount=1;
            $newjava_runonce = true;
            $newjava ="";

            $newjava .= "\n\tif (((";

        }

        // try to determine if local eval (in php) of this simple condition is possible
        $localEvaluationPossible = false;
        unset($localEvaluation);

        if ($thissurvey['private'] == "N" && preg_match('/^{TOKEN:([^}]*)}$/', $cd[2], $sourceconditiontokenattr))
        { // Source of this simple condition is TokenAttr
            if ( isset($_SESSION['token']) &&
            in_array(strtolower($sourceconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
            {
                $tokenAttrSourceValue=GetAttributeValue($surveyid,strtolower($sourceconditiontokenattr[1]),$_SESSION['token']);
                // local evaluation avoids transmitting
                // the comparison values to the client in Javascript
                // It is possible if target value is not an @SGQA@ answer
                // or if target value is  an @SGQA@ answer from a previous page (group)
                if (preg_match('/^@([0-9]+X([0-9]+)X[^@]+)@/', $cd[3], $comparedfieldname))
                { // target condition value is an SGQA code

                    $targetSgqa_gid = $comparedfieldname[2];
                    if ($targetSgqa_gid != $gid)
                    { // target answer was on a previous page
                        $localEvaluationPossible = true;

                        if (isset($_SESSION[$comparedfieldname[1]]))
                        {
                            $answercvalue=$_SESSION[$comparedfieldname[1]];
                            if (eval('if (trim($tokenAttrSourceValue) '.$cd[6].' trim($answercvalue)) return true; else return false;'))
                            {
                                $localEvaluation = 'true';
                            }
                            else
                            {
                                $localEvaluation = 'false';
                            }
                        }
                        else
                        {
                            $localEvaluation = 'false';
                        }
                    }
                    else
                    { // target answer is on the same page
                        $localEvaluationPossible = false;
                        $newjava_runonce = false; // the whole conditions set must be evaluated each time for this question
                        $JSsourceElt = "document"; // let's use an always existing Elt
                        $JSsourceVal = "'".javascript_escape($tokenAttrSourceValue)."'";
                    }
                }
                else
                { // other cases cwith TokenAttr as source an be evaluated locally
                    $localEvaluationPossible = true;

                    if ($cd[6] == 'RX')
                    { // the comparison right operand is a RegExp
                        if (preg_match('/'.trim($cd[3]).'/',trim($tokenAttrSourceValue)))
                        {
                            $localEvaluation = 'true';
                        }
                        else
                        {
                            $localEvaluation = 'false';
                        }
                    }
                    elseif (preg_match('/^{TOKEN:([^}]*)}$/', $cd[3], $comparedtokenattr))
                    {
                        // the following is Not usefull
                        // because $conditionSourceOnPreviousPage is only used when right operand is Token Attr
                        // but "TokenAttr oper TokenAttr" conditions are evaluated locally here
                        // $conditionSourceOnPreviousPage = true;
                        if ( isset($_SESSION['token']) &&
                        in_array(strtolower($comparedtokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
                        {
                            $comparedtokenattrValue = GetAttributeValue($surveyid,strtolower($comparedtokenattr[1]),$_SESSION['token']);
                            if (eval('if (trim($tokenAttrSourceValue) '.$cd[6].' trim($comparedtokenattrValue)) return true; else return false;'))
                            { // conditin matches
                                $localEvaluation = 'true';
                                //$localEvaluation = "'tokenmatch' == 'tokenmatch'
                            }
                            else
                            { // no match
                                $localEvaluation = 'false';
                            }
                        }
                        else
                        { // No token in session, or no such atribute ==> false
                            $localEvaluation = 'false';
                        }
                    }
                    else
                    { // the comparison right operand is a constant
                        if (eval('if (trim($tokenAttrSourceValue) '.$cd[6].' trim($cd[3])) return true; else return false;'))
                        {
                            $localEvaluation = 'true';
                        }
                        else
                        {
                            $localEvaluation = 'false';
                        }
                    }
                }
                 
                 
            }
            else
            { // Can't evaluate ==> False
                $localEvaluationPossible = true;
                $localEvaluation = false;
            }
        }
        elseif (preg_match("/[0-9]+X([0-9]+)X.*/",$cd[2],$sourceQuestionGid))
        {
            // If the Gid of the question used for the condition is on the same group,
            // the set the runconce flag to False, because we'll need to evaluate this condition
            //each time another question in this page is modified
            $conditionSourceOnPreviousPage = false; // used later in TokenAttr conditions
            if (isset($sourceQuestionGid[1]) && $sourceQuestionGid[1] == $gid)
            {
                $newjava_runonce = false; // this param is cumulated for all conditions on this fieldname
            }
            else
            {
                $conditionSourceOnPreviousPage = true; // this param is specific to this basic condition
            }

            $localEvaluationPossible = false;
            unset($localEvaluation);
            $idname=retrieveJSidname($cd,$gid);
            $JSsourceElt = "document.getElementById('$idname')";
            $JSsourceVal = "document.getElementById('$idname').value";
        }
        else
        { // Abnormal. MAybe the token table doesn't contain the required TokenAttrs !
            $localEvaluationPossible = true;
            $localEvaluation = "'seriousError' == 'VerySeriousError'";
        }


        if (!isset($oldcq) || !$oldcq)
        {
            $oldcq = $cd[2];
        }

        // Different scenario's are or-ed; within 1 scenario, conditions are and-ed.
        if ($cqcount > 1 && isset($oldscenario) && $oldscenario != $cd[7])
        {	// We have a new scenario, so "or" the scenario.
            $newjava .= ")) || ((";
        }
        elseif ($cqcount > 1 && $oldcq ==$cd[2])
        {	// Multiple values for the same question will be ORed.
            $newjava .= " || ";
        }
        elseif ($cqcount >1 && $oldcq != $cd[2])
        {	// DIffent questions within the same scenario will be ANDed.
            $newjava .= ") && (";
        }
        $oldscenario=$cd[7];


        if ($localEvaluationPossible == true && isset($localEvaluation))
        {
            $newjava .= "$localEvaluation";
        } // end local evaluations of conditions
        else
        {
            // The [3] element is for the value used to be compared with
            // If it is '' (empty) means not answered
            // then a space or a false are interpreted as no answer
            // as we let choose if the questions is answered or not
            // and doesnt care the answer, so we wait for a == or !=
            // TempFix by lemeur ==> add a check on cd[3]=' ' as well because
            // condition editor seems not updated yet
            if ($cd[3] == '' || $cd[3] == ' ')
            {
                if ($cd[6] == '==')
                {
                    $newjava .= "$JSsourceElt != null && ($JSsourceVal == ' ' || !$JSsourceVal)";
                }
                else
                {
                    // strange thing, isn't it ? well 0, ' ', '' or false are all false logic values then...
                    $newjava .= "$JSsourceElt != null && $JSsourceVal";
                }
            } // end specific case of No Answer
            elseif ($cd[4] == "M" ||
            $cd[4] == "P")
            {
                //$newjava .= "!document.getElementById('$idname') || document.getElementById('$idname').value == ' '";
                $newjava .= "$JSsourceElt != null && $JSsourceVal $cd[6] 'Y'"; //
            } // end specific case of M or P questions
            else
            {
                /* NEW
                 * If the value is enclossed by @
                 * the value of this question must be evaluated instead.
                 */
                if (preg_match('/^@([0-9]+X([0-9]+)X[^@]+)@/', $cd[3], $comparedfieldname))
                {
                    $sgq_from_sgqa = $_SESSION['fieldnamesInfo'][$comparedfieldname[1]];
                    $qid_from_sgq=$comparedfieldname[2];
                    $q2type=$qtypesarray[$sgq_from_sgqa];
                    $idname2 = retrieveJSidname(Array('',$qid_from_sgq,$comparedfieldname[1],'Y',$q2type,$sgq_from_sgqa));

                    $newjava .= "( $JSsourceElt != null && $JSsourceVal != '') && ";

                    $newjava .= "( document.getElementById('$idname2') != null && document.getElementById('$idname2').value != '') && ";
                    $cqidattributes = getQuestionAttributes($cd[1]);
                    if (in_array($cd[4],array("A","B","K","N","5",":")) || (in_array($cd[4],array("Q",";")) && $cqidattributes['numbers_only']==1))
                    { // Numerical questions
                        //$newjava .= "(parseFloat(document.getElementById('" . $idname. "').value) $cd[6] parseFloat(document.getElementById('".$idname2."').value))";
                        $newjava .= "(parseFloat($JSsourceVal) $cd[6] parseFloat(document.getElementById('$idname2').value))";
                    }
                    else
                    {
                        //				$newjava .= "(document.getElementById('" . $idname. "').value $cd[6] document.getElementById('".$idname2."').value)";
                        $newjava .= "($JSsourceVal $cd[6] document.getElementById('$idname2').value)";
                    }

                } // end target @SGQA@
                elseif ($thissurvey['private'] == "N" && preg_match('/^{TOKEN:([^}]*)}$/', $cd[3], $targetconditiontokenattr))
                {
                    if ( isset($_SESSION['token']) &&
                    in_array(strtolower($targetconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
                    {
                        $cvalue=GetAttributeValue($surveyid,strtolower($targetconditiontokenattr[1]),$_SESSION['token']);
                        if ($conditionSourceOnPreviousPage === false)
                        {
                            if (in_array($cd[4],array("A","B","K","N","5",":"))  || (in_array($cd[4],array("Q",";")) && $cqidattributes['numbers_only']==1))
                            {
                                $newjava .= "parseFloat($JSsourceVal) $cd[6] parseFloat('".javascript_escape($cvalue)."')";
                            }
                            else
                            {
                                //$newjava .= "document.getElementById('$idname').value $cd[6] '".javascript_escape($cvalue)."'";
                                $newjava .= "$JSsourceVal $cd[6] '".javascript_escape($cvalue)."'";
                            }
                        }
                        else
                        { // note that source of condition is not a TokenAttr because this case is processed
                            // earlier
                            // get previous qecho "<pre>";print_r($_SESSION);echo "</pre>";die();uestion answer value: $cd[2]
                            if (isset($_SESSION[$cd[2]]))
                            {
                                $prevanswerToCompare=$_SESSION[$cd[2]];
                                if ($cd[6] != 'RX')
                                {
                                    if (eval('if (trim($prevanswerToCompare) '.$cd[6].' trim($cvalue)) return true; else return false;'))
                                    {
                                        //$newjava .= "'tokenMatch' == 'tokenMatch'";
                                        $newjava .= "true";
                                    }
                                    else
                                    {
                                        //$newjava .= "'tokenNoMatch' == 'tokenMatchNot'";
                                        $newjava .= "false";
                                    }
                                }
                                else
                                {
                                    if (preg_match('/'.trim($cvalue).'/',trim($prevanswerToCompare)))
                                    {
                                        //$newjava .= "'tokenMatch' == 'tokenMatch'";
                                        $newjava .= "true";
                                    }
                                    else
                                    {
                                        //$newjava .= "'tokenNoMatch' == 'tokenMatchNot'";
                                        $newjava .= "false";
                                    }
                                }
                            }
                            else
                            {
                                //$newjava .= "'impossible to evaluate prevQ' == 'tokenAttr'";
                                $newjava .= "false";
                            }
                        }
                    }
                    else
                    {
                        //$newjava .= "'Missing tokenAttr' == 'tokenAttr'";
                        $newjava .= "false";
                    }
                } // end target as TokenAttr
                else
                { // right operand is a Constant or an Answer Code
                    $newjava .= "$JSsourceElt != null &&";
                    if ($cd[3] && $cd[6] != '!=')
                    { // if the target value isn't 'No answer' AND if operator isn't !=
                        $newjava .= "$JSsourceVal != '' && ";
                    }
                    if ($cd[6] == 'RX')
                    {
                        $newjava .= "match_regex($JSsourceVal,'$cd[3]')";
                    }
                    else
                    {
                        $cqidattributes = getQuestionAttributes($cd[1]);
                        if (in_array($cd[4],array("A","B","K","N","5",":")) || (in_array($cd[4],array("Q",";")) && $cqidattributes['numbers_only']==1))
                        { // Numerical questions
                            //$newjava .= "parseFloat(document.getElementById('" . $idname. "').value) $cd[6] parseFloat('".$cd[3]."')";
                            $newjava .= "parseFloat($JSsourceVal) $cd[6] parseFloat('".$cd[3]."')";
                        }
                        else
                        {
                            $newjava .= "$JSsourceVal $cd[6] '$cd[3]'";
                        }
                    }
                } // end target as Constant or Answer Code
            } // generic cases for javasript evals
        } // end not local eval

        if ((isset($oldq) && $oldq != $cd[0]) || !isset($oldq))//End If Statement
        {
            $endzone = ")))\n";
            $endzone .= "\t{\n";
            $endzone .= "document.getElementById('question$cd[0]').style.display='';\n";
            $endzone .= "document.getElementById('display$cd[0]').value='on';\n";
            $endzone .= "\t}\n";
            $endzone .= "\telse\n";
            $endzone .= "\t{\n";
            $endzone .= "document.getElementById('question$cd[0]').style.display='none';\n";
            $endzone .= "document.getElementById('display$cd[0]').value='';\n";
            $endzone .= "\t}\n";
            $cqcount++;
        }

        // If next condition doesn't exist, or if nex condition is on a different question
        // then current If statemement is over. We just need to check if it should be wrapped in an
        // additionnal runonce If statement
        if ( ( isset($conditions[$i+1]) && $conditions[$i+1][0] != $cd[0]) || (! isset($conditions[$i+1])) )
        { // After If Statement

            if ($newjava_runonce == true)
            {
                $java .= "    if (document.getElementById('runonce').value == '0')\n"
                ."    {\n";
                $java .= $newjava;
                $endzone .= "    }\n";
            }
            else
            {
                $java .= $newjava;
            }
            $newjava = "";
        }

        $oldq = $cd[0]; //Update oldq for next loop
        $oldcq = $cd[2];  //Update oldcq for next loop
    } // end foreach

    //Close the expression for those where the question source is not on this page
    //echo "OLDQ: $oldq, CD[0]: $cd[0], GID: $gid, sourceQuestionGid: $sourceQuestionGid[1]\n";
    if (isset($sourceQuestionGid[1]) && ((isset($oldq) && $oldq != $cd[0] || !isset($oldq)) && $sourceQuestionGid[1] != $gid))
    {
        $endzone .= "    }\n";
    }
    $java .= $endzone;
}

if ((isset($array_filterqs) && is_array($array_filterqs)) ||
(isset($array_filterXqs) && is_array($array_filterXqs)))
{
    $qattributes=questionAttributes(1);
    $array_filter_types=$qattributes['array_filter']['types'];
    $array_filter_exclude_types=$qattributes['array_filter_exclude']['types'];
    unset($qattributes);
    if (!isset($appendj)) {$appendj="";}
    foreach ($array_filterqs as $attralist)
    {
        $qbase = $surveyid."X".$gid."X".$attralist['qid'];
        $qfbase = $surveyid."X".$gid."X".$attralist['fid'];
        if ($attralist['type'] == "M" || $attralist['type'] == "P")
        {
            $tqquery = "SELECT type FROM {$dbprefix}questions WHERE qid='".$attralist['qid']."';"; 
            $tqresult = db_execute_assoc($tqquery); //Checked   
            $OrigQuestion = $tqresult->FetchRow();
            
            if($OrigQuestion['type'] == "L" || $OrigQuestion['type'] == "O")
            {
                $qquery = "SELECT {$dbprefix}answers.code as title, {$dbprefix}questions.type, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}answers.qid='".$attralist['qid']."' AND {$dbprefix}answers.language='".$_SESSION['s_lang']."' order by code;"; 
            } else {
                $qquery = "SELECT title, type, other FROM {$dbprefix}questions WHERE parent_qid='".$attralist['qid']."' AND language='".$_SESSION['s_lang']."' order by title;";
            } 
            $qresult = db_execute_assoc($qquery); //Checked
            $other=null;
            while ($fansrows = $qresult->FetchRow())
            {
                if($fansrows['other']== "Y") $other="Y";
                if(strpos($array_filter_types, $fansrows['type']) === false) {} else
                {
                    $fquestans = "java".$qfbase.$fansrows['title'];
                    $tbody = "javatbd".$qbase.$fansrows['title'];
					if($OrigQuestion['type']=="1") {
					    //for a dual scale array question type we have to massage the system
						$dtbody = "tbdisp".$qbase.$fansrows['title']."#0";
						$dtbody2= "tbdisp".$qbase.$fansrows['title']."#1";
					} else {
                    $dtbody = "tbdisp".$qbase.$fansrows['title'];
					}
                    $tbodyae = $qbase.$fansrows['title'];
                    $appendj .= "\n";
                    $appendj .= "\tif ((document.getElementById('$fquestans') != null && document.getElementById('$fquestans').value == 'Y'))\n";
                    $appendj .= "\t{\n";
                    $appendj .= "\t\tdocument.getElementById('$tbody').style.display='';\n";
					$appendj .= "\t\tdocument.getElementById('$dtbody').value = 'on';\n"; //Note - do not use jquery format here (ie: "$('#$dtbody').val('on')" - the hash in dual scale breaks the javascript
                    if($OrigQuestion['type']=="1") {
					    //for a dual scale array question type we have to massage the system
						$appendj .= "\t\tdocument.getElementById('$dtbody2').value = 'on';\n"; //Note - do not use jquery format here (ie: "$('#$dtbody').val('on')" - the hash in dual scale breaks the javascript
					}
                    $appendj .= "\t}\n";
                    $appendj .= "\telse\n";
                    $appendj .= "\t{\n";
                    $appendj .= "\t\tdocument.getElementById('$tbody').style.display='none';\n";
					$appendj .= "\t\tdocument.getElementById('$dtbody').value = 'off';\n"; //Note - do not use jquery format here (ie: "$('#$dtbody').val('off')" - the hash in dual scale breaks the javascript
                    if($OrigQuestion['type']=="1") {
					    //for a dual scale array question type we have to massage the system
						$appendj .= "\t\tdocument.getElementById('$dtbody2').value = 'off';\n"; //Note - do not use jquery format here (ie: "$('#$dtbody').val('off')" - the hash in dual scale breaks the javascript
					}
                    // This line resets the text fields in the hidden row
                    $appendj .= "\t\t$('#$tbody input[type=text]').val('');\n";
                    // This line resets any radio group in the hidden row
                    $appendj .= "\t\t$('#$tbody input[type=checkbox]').attr('checked', false); ";
                    $appendj .= "\t}\n";
                }
            }

            if($other=="Y") {
                $fquestans = "answer".$qfbase."other";
                $tbody = "javatbd".$qbase."other";
                $dtbody = "tbdisp".$qbase."other";
                $tbodyae = $qbase."other";
                $appendj .= "\n";
                $appendj .= "\tif (document.getElementById('$fquestans').value !== '')\n";
                $appendj .= "\t{\n";
                $appendj .= "\t\tdocument.getElementById('$tbody').style.display='';\n";
                $appendj .= "\t\t$('#$dtbody').val('on');\n";
                $appendj .= "\t}\n";
                $appendj .= "\telse\n";
                $appendj .= "\t{\n";
                $appendj .= "\t\tdocument.getElementById('$tbody').style.display='none';\n";
				$appendj .= "\t\tdocument.getElementById('$dtbody').value = 'off';\n"; //Note - do not use jquery format here (ie: "$('#$dtbody').val('off')" - the hash in dual scale breaks the javascript
                // This line resets the text fields in the hidden row
                $appendj .= "\t\t$('#$tbody input[type=text]').val('');";
                // This line resets any radio group in the hidden row
                $appendj .= "\t\t$('#$tbody input[type=radio]').attr('checked', false); ";
                $appendj .= "\t}\n";
            }
        }
    }
    $java .= $appendj;
    foreach ($array_filterXqs as $attralist)
    {
        $qbase = $surveyid."X".$gid."X".$attralist['qid'];
        $qfbase = $surveyid."X".$gid."X".$attralist['fid'];
        if ($attralist['type'] == "M" || $attralist['type'] == "P")
        {
            $tqquery = "SELECT type FROM {$dbprefix}questions WHERE qid='".$attralist['qid']."';"; 
            $tqresult = db_execute_assoc($tqquery); //Checked   
            $OrigQuestion = $tqresult->FetchRow();
            
            if($OrigQuestion['type'] == "L" || $OrigQuestion['type'] == "O")
            {
                $qquery = "SELECT {$dbprefix}answers.code as title, {$dbprefix}questions.type, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}answers.qid='".$attralist['qid']."' AND {$dbprefix}answers.language='".$_SESSION['s_lang']."' order by code;"; 
            } else {
                $qquery = "SELECT title, type, other FROM {$dbprefix}questions WHERE parent_qid='".$attralist['qid']."' AND language='".$_SESSION['s_lang']."' order by title;";
            } 
            $qresult = db_execute_assoc($qquery); //Checked
            $other=null;
            while ($fansrows = $qresult->FetchRow())
            {
                if($fansrows['other']== "Y") $other="Y";
                if(strpos($array_filter_exclude_types, $fansrows['type']) === false) {} else
                {
                    $fquestans = "java".$qfbase.$fansrows['title'];
                    $tbody = "javatbd".$qbase.$fansrows['title'];
					if($OrigQuestion['type']=="1") {
					    //for a dual scale array question type we have to massage the system
						$dtbody = "tbdisp".$qbase.$fansrows['title']."#0";
						$dtbody2 = "tbdisp".$qbase.$fansrows['title']."#1";
					} else {
                    $dtbody = "tbdisp".$qbase.$fansrows['title'];
					}
                    $tbodyae = $qbase.$fansrows['title'];
                    $appendj .= "\n";
                    $appendj .= "\tif (\n";
                    $appendj .= "\t\t(document.getElementById('$fquestans') != null && document.getElementById('$fquestans').value == 'Y')\n";

                    /* If this question is a cascading question, then it also needs to check the status of the question that this one relies on */
                    if(isset($array_filterXqs_cascades[$attralist['qid']]))
                    {

                        foreach($array_filterXqs_cascades[$attralist['qid']] as $cascader)
                        {
                            $cascadefqa ="java".$surveyid."X".$gid."X".$cascader.$fansrows['code'];
                            $appendj .= "\t\t||\n";
                            $appendj .= "\t\t(document.getElementById('$cascadefqa') != null && document.getElementById('$cascadefqa').value == 'Y')\n";
                        }
                    }
                    /* */
                    $appendj .= "\t)\n";
                    $appendj .= "\t{\n";
                    $appendj .= "\t\tdocument.getElementById('$tbody').style.display='none';\n";
					$appendj .= "\t\tdocument.getElementById('$dtbody').value = 'off';\n"; //Note - do not use jquery format here (ie: "$('#$dtbody').val('off')" - the hash in dual scale breaks the javascript
					if($OrigQuestion['type'] == "1") {
						$appendj .= "\t\tdocument.getElementById('$dtbody2').value = 'off';\n"; //Note - do not use jquery format here (ie: "$('#$dtbody').val('off')" - the hash in dual scale breaks the javascript
					}
                    // This line resets the text fields in the hidden row
                    $appendj .= "\t\t$('#$tbody input[type=text]').val('');\n";
                    // This line resets any radio group in the hidden row
                    $appendj .= "\t\t$('#$tbody input[type=radio]').attr('checked', false);\n";
                    $appendj .= "\t}\n";
                    $appendj .= "\telse\n";
                    $appendj .= "\t{\n";
                    $appendj .= "\t\tdocument.getElementById('$tbody').style.display='';\n";
					$appendj .= "\t\tdocument.getElementById('$dtbody').value='on';\n"; //Note - do not use jquery format here (ie: "$('#$dtbody').val('off')" - the hash in dual scale breaks the javascript
					if($OrigQuestion['type'] == "1") {
					    $appendj .= "\t\tdocument.getElementById('$dtbody2').value='on';\n"; //Note - do not use jquery format here (ie: "$('#$dtbody').val('off')" - the hash in dual scale breaks the javascript
					}
                    $appendj .= "\t}\n";
                }
            }
            if($other=="Y") {
                $fquestans = "answer".$qfbase."other";
                $tbody = "javatbd".$qbase."other";
                $dtbody = "tbdisp".$qbase."other";
                $tbodyae = $qbase."other";
                $appendj .= "\n";
                $appendj .= "\tif (document.getElementById('$fquestans').value !== '')\n";
                $appendj .= "\t{\n";
                $appendj .= "\t\tdocument.getElementById('$tbody').style.display='none';\n";
                $appendj .= "\t\t$('#$dtbody').val('on');\n";
                $appendj .= "\t}\n";
                $appendj .= "\telse\n";
                $appendj .= "\t{\n";
                $appendj .= "\t\tdocument.getElementById('$tbody').style.display='';\n";
                $appendj .= "\t\t$('#$dtbody').val('off');\n";
                // This line resets the text fields in the hidden row
                $appendj .= "\t\t$('#$tbody input[type=text]').val('');";
                // This line resets any radio group in the hidden row
                $appendj .= "\t\t$('#$tbody input[type=radio]').attr('checked', false); ";
                $appendj .= "\t}\n";
            }
        }
    }
    $java .= $appendj;
}

if (isset($java)) {echo $java;}
echo "\n\tdocument.getElementById('runonce').value=1\n"
. "\t}\n"
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

//Display the "mandatory" message on page if necessary
if (isset($showpopups) && $showpopups == 0 && isset($notanswered) && $notanswered == true)
{
    echo "<p><span class='errormandatory'>" . $clang->gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.") . "</span></p>";
}

//Display the "validation" message on page if necessary
if (isset($showpopups) && $showpopups == 0 && isset($notvalidated) && $notvalidated == true)
{
    echo "<p><span class='errormandatory'>" . $clang->gT("One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid.") . "</span></p>";
}

//Display the "file validation" message on page if necessary
if (isset($showpopups) && $showpopups == 0 && isset($filenotvalidated) && $filenotvalidated == true)
{
    echo "<p><span class='errormandatory'>" . $clang->gT("One or more uploaded files are not in proper format/size. You cannot proceed until these files are valid.") . "</span></p>";
}

echo "\n\n<!-- PRESENT THE QUESTIONS -->\n";
if (isset($qanda) && is_array($qanda))
{
    foreach ($qanda as $qa)
    {
        $q_class = question_class($qa[8]); // render question class (see common.php)

        if ($qa[9] == 'Y')
        {
            $man_class = ' mandatory';
        }
        else
        {
            $man_class = '';
        }

        if ($qa[3] != 'Y') {$n_q_display = '';} else { $n_q_display = ' style="display: none;"';}

        $question= $qa[0];
        //===================================================================
        // The following four variables offer the templating system the
        // capacity to fully control the HTML output for questions making the
        // above echo redundant if desired.
        $question['essentials'] = 'id="question'.$qa[4].'"'.$n_q_display;
        $question['class'] = $q_class;
        $question['man_class'] = $man_class;
        $question['code']=$qa[5];
        //===================================================================
        $answer=$qa[1];
        $help=$qa[2];
        $questioncode=$qa[5];

        $question_template = file_get_contents($thistpl.'/question.pstpl');

        if( preg_match( '/\{QUESTION_ESSENTIALS\}/' , $question_template ) === false || preg_match( '/\{QUESTION_CLASS\}/' , $question_template ) === false )
        {
            // if {QUESTION_ESSENTIALS} is present in the template but not {QUESTION_CLASS} remove it because you don't want id="" and display="" duplicated.
            $question_template = str_replace( '{QUESTION_ESSENTIALS}' , '' , $question_template );
            $question_template = str_replace( '{QUESTION_CLASS}' , '' , $question_template );
            echo '
	<!-- NEW QUESTION -->
				<div id="question'.$qa[4].'" class="'.$q_class.$man_class.'"'.$n_q_display.'>
';
            echo templatereplace($question_template);
            echo '
				</div>
';
        }
        else
        {
            echo templatereplace($question_template);
        };
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
    echo "<center><font color='red' size='2'>".$clang->gT("This survey is not currently active. You will not be able to save your responses.")."</font></center>\n";
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

// Closing PHP tag intentionally left out - yes, it is okay       
