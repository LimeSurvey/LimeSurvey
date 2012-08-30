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
* $Id: group.php 12432 2012-02-10 15:24:45Z tmswhite $
*/
if (!isset($homedir) || isset($_REQUEST['$homedir'])) {die("Cannot run this script directly");}

require_once("save.php");   // for supporting functions only

// $LEMdebugLevel - customizable debugging for Lime Expression Manager
$LEMdebugLevel=0;   // LEM_DEBUG_TIMING;    // (LEM_DEBUG_TIMING + LEM_DEBUG_VALIDATION_SUMMARY + LEM_DEBUG_VALIDATION_DETAIL);
$LEMskipReprocessing=false; // true if used GetLastMoveResult to avoid generation of unneeded extra JavaScript
switch ($thissurvey['format'])
{
    case "A": //All in one
        $surveyMode='survey';
        break;
    default:
    case "S": //One at a time
        $surveyMode='question';
        break;
    case "G": //Group at a time
        $surveyMode='group';
        break;
}
$radix=getRadixPointData($thissurvey['surveyls_numberformat']);
$radix = $radix['seperator'];

global $deletenonvalues;    // set in config-defaults.php

$surveyOptions = array(
'active'=>($thissurvey['active']=='Y'),
'allowsave'=>($thissurvey['allowsave']=='Y'),
'anonymized'=>($thissurvey['anonymized']!='N'),
'assessments'=>($thissurvey['assessments']=='Y'),
'datestamp'=>($thissurvey['datestamp']=='Y'),
'deletenonvalues'=>(isset($deletenonvalues)? $deletenonvalues: 1),
'hyperlinkSyntaxHighlighting'=>(($LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY),     // TODO set this to true if in admin mode but not if running a survey
'ipaddr'=>($thissurvey['ipaddr']=='Y'),
'radix'=>$radix,
'refurl'=>(($thissurvey['refurl'] == "Y") ? $_SESSION['refurl'] : NULL),
'rooturl'=>(isset($rooturl) ? $rooturl : ''),
'savetimings'=>($thissurvey['savetimings'] == "Y"),
'surveyls_dateformat'=>(isset($thissurvey['surveyls_dateformat']) ? $thissurvey['surveyls_dateformat'] : 1),
'startlanguage'=>(isset($clang->langcode) ? $clang->langcode : $thissurvey['language']),
'target'=>(isset($uploaddir) ?  "{$uploaddir}/surveys/{$thissurvey['sid']}/files/" : "/temp/{$thissurvey['sid']}/files"),
'tempdir'=>(isset($tempdir) ? $tempdir : '/temp/'),
'timeadjust'=>(isset($timeadjust) ? $timeadjust : 0),
'token'=>(isset($clienttoken) ? $clienttoken : NULL),
);

//Security Checked: POST, GET, SESSION, REQUEST, returnglobal, DB
$previewgrp = false;
if ( $surveyMode=='group' && isset($_REQUEST['action']) && ($_REQUEST['action']=='previewgroup')){
    $previewgrp = true;
}
if (isset($_REQUEST['newtest']))
    if ($_REQUEST['newtest']=="Y")
        setcookie("limesurvey_timers", "0");
    $show_empty_group = false;

if ($previewgrp)
{
    $_SESSION['prevstep'] = 1;
    $_SESSION['maxstep'] = 0;
}
else
{
    //RUN THIS IF THIS IS THE FIRST TIME , OR THE FIRST PAGE ########################################
    if (!isset($_SESSION['step']))  //  || !$_SESSION['step']) - don't do this for step 0, else rebuild the session
    {
        $totalquestions = buildsurveysession();
        LimeExpressionManager::StartSurvey($thissurvey['sid'], $surveyMode, $surveyOptions, false,$LEMdebugLevel);
        $_SESSION['step'] = 0;
        if ($surveyMode == 'survey') {
            $move = "movenext"; // to force a call to NavigateForwards()
        }
        else if (isset($thissurvey['showwelcome']) && $thissurvey['showwelcome'] == 'N') {
                //If explicitply set, hide the welcome screen
                $_SESSION['step'] = 0;
                $move = "movenext";
            }
    }

    if (!isset($_SESSION['totalsteps'])) {$_SESSION['totalsteps']=0;}
    if (!isset($_SESSION['maxstep'])) {$_SESSION['maxstep']=0;}

    if (isset($_SESSION['LEMpostKey']) && isset($_POST['LEMpostKey']) && $_POST['LEMpostKey'] != $_SESSION['LEMpostKey'])
    {
        // then trying to resubmit (e.g. Next, Previous, Submit) from a cached copy of the page
        // Does not try to save anything from the page to the database
        $moveResult = LimeExpressionManager::GetLastMoveResult(true);
        if (isset($_POST['thisstep']) && isset($moveResult['seq']) && $_POST['thisstep'] == $moveResult['seq'])
        {
            // then pressing F5 or otherwise refreshing the current page, which is OK
            $LEMskipReprocessing=true;
            $move = "movenext"; // so will re-display the survey
        }
        else
        {
            // trying to use browser back buttons, which may be disallowed if no 'previous' button is present
            $LEMskipReprocessing=true;
            $move = "movenext"; // so will re-display the survey
            $invalidLastPage=true;
            $vpopup="<script type=\"text/javascript\">\n
            <!--\n $(document).ready(function(){
            alert(\"".$clang->gT("Please use the LimeSurvey navigation buttons or index.  It appears you attempted to use the browser back button to re-submit a page.", "js")."\");});\n //-->\n
            </script>\n";
        }
    }

    if (!(isset($_POST['saveall']) || isset($_POST['saveprompt']) || isset($_POST['loadall']) || isset($_GET['sid']) || $LEMskipReprocessing || (isset($move) && (preg_match('/^changelang_/',$move)))))
    {
        $_SESSION['prevstep']=$_SESSION['step'];
    }
    if (!isset($_SESSION['prevstep']))
    {
        $_SESSION['prevstep']=-1;   // this only happens on re-load
    }

    if (isset($_SESSION['LEMtokenResume']))
    {
        LimeExpressionManager::StartSurvey($thissurvey['sid'], $surveyMode, $surveyOptions, false,$LEMdebugLevel);
        $moveResult = LimeExpressionManager::JumpTo($_SESSION['step'],false,false);   // if late in the survey, will re-validate contents, which may be overkill
        unset($_SESSION['LEMtokenResume']);
        unset($_SESSION['LEMreload']);
    }
    else if (!$LEMskipReprocessing)
        {
            //Move current step ###########################################################################
            if (isset($move) && $move == 'moveprev' && ($thissurvey['allowprev']=='Y' || $thissurvey['allowjumps']=='Y'))
            {
                $moveResult = LimeExpressionManager::NavigateBackwards();
                if ($moveResult['at_start']) {
                    $_SESSION['step']=0;
                    unset($moveResult); // so display welcome page again
                }
        }
        if (isset($move) && $move == "movenext")
        {
            if (isset($_SESSION['LEMreload']))
            {
                LimeExpressionManager::StartSurvey($thissurvey['sid'], $surveyMode, $surveyOptions, false,$LEMdebugLevel);
                $moveResult = LimeExpressionManager::JumpTo($_SESSION['step'],false,false);   // if late in the survey, will re-validate contents, which may be overkill
                unset($_SESSION['LEMreload']);
            }
            else {
                $moveResult = LimeExpressionManager::NavigateForwards();
            }
        }
        if (isset($move) && ($move == 'movesubmit'))
        {
            if ($surveyMode == 'survey')
            {
                $moveResult = LimeExpressionManager::NavigateForwards();
            }
            else
            {
                // may be submitting from the navigation bar, in which case need to process all intervening questions
                // in order to update equations and ensure there are no intervening relevant mandatory or relevant invalid questions
                $moveResult = LimeExpressionManager::JumpTo($_SESSION['totalsteps']+1,false);
            }
        }
        if (isset($move) && (preg_match('/^changelang_/',$move))) {
            // jump to current step using new language, processing POST values
            $moveResult = LimeExpressionManager::JumpTo($_SESSION['step'],false,true,false,true);  // do process the POST data
        }
        if (isset($move) && bIsNumericInt($move) && $thissurvey['allowjumps']=='Y')
        {
            $move = (int)$move;
            if ($move > 0 && (($move <= $_SESSION['step']) || (isset($_SESSION['maxstep']) && $move <= $_SESSION['maxstep']))) {
                $moveResult = LimeExpressionManager::JumpTo($move,false);
            }
        }
        if (!isset($moveResult) && !($surveyMode != 'survey' && $_SESSION['step'] == 0)) {
            // Just in case not set via any other means, but don't do this if it is the welcome page
            $moveResult = LimeExpressionManager::GetLastMoveResult(true);
            $LEMskipReprocessing=true;
        }
    }

    if (isset($moveResult)) {
        if ($moveResult['finished']==true) {
            $move = 'movesubmit';
        }
        else
        {
            $_SESSION['step']= $moveResult['seq']+1;  // step is index base 1
            $stepInfo = LimeExpressionManager::GetStepIndexInfo($moveResult['seq']);
        }
        if ($move == "movesubmit" && $moveResult['finished'] == false) {
            // then there are errors, so don't finalize the survey
            $move = "movenext"; // so will re-display the survey
            $invalidLastPage=true;
        }
    }

    // We do not keep the participant session anymore when the same browser is used to answer a second time a survey (let's think of a library PC for instance).
    // Previously we used to keep the session and redirect the user to the
    // submit page.

    if ($surveyMode != 'survey' && $_SESSION['step'] == 0) {
        display_first_page();
        exit;
    }


    //CHECK IF ALL MANDATORY QUESTIONS HAVE BEEN ANSWERED ############################################
    //First, see if we are moving backwards or doing a Save so far, and its OK not to check:
    if (
    (isset($move) && ($move == "moveprev" || (is_int($move) && $_SESSION['prevstep'] == $_SESSION['maxstep']) || $_SESSION['prevstep'] == $_SESSION['step'])) ||
    (isset($_POST['saveall']) && $_POST['saveall'] == $clang->gT("Save your responses so far")))
    {
        if ($allowmandbackwards==1) {
            $backok="Y";
        }
        else
        {
            $backok="N";
        }
    }
    else
    {
        $backok="N";    // NA, since not moving backwards
    }

    if ($thissurvey['active'] == "Y" && isset($_POST['saveall']))
    {
        // must do this here to process the POSTed values
        //$moveResult = LimeExpressionManager::JumpTo($_SESSION['step'],false);   // by jumping to current step, saves data so far

        //showsaveform(); // generates a form and exits, awaiting input
        if(!tableExists('tokens_'.$surveyid) || $thissurvey['anonymized']=='Y' || (tableExists('tokens_'.$surveyid) && $thissurvey['tokenanswerspersistence'] == 'N' ))
        {
            $moveResult = LimeExpressionManager::JumpTo($_SESSION['step'],false,true,false,false,true);   // by jumping to current step, saves data so far
            showsaveform();
        }
        else
        {
            $moveResult = LimeExpressionManager::JumpTo($_SESSION['step'],false,true,false,false,true);   // by jumping to current step, saves data so far
        }
    }

    if ($thissurvey['active'] == "Y" && isset($_POST['saveprompt']))
    {
        // The response from the save form
        // CREATE SAVED CONTROL RECORD USING SAVE FORM INFORMATION
        $flashmessage = savedcontrol();

        if (isset($errormsg) && $errormsg != "")
        {
            showsaveform(); // reshow the form if there is an error
        }

        $moveResult = LimeExpressionManager::GetLastMoveResult(true);
        $LEMskipReprocessing=true;

        // TODO - does this work automatically for token answer persistence? Used to be savedsilent()

    }

    //Now, we check mandatory questions if necessary
    //CHECK IF ALL CONDITIONAL MANDATORY QUESTIONS THAT APPLY HAVE BEEN ANSWERED
    global $notanswered;

    if (isset($moveResult) && !$moveResult['finished'])
    {
        $unansweredSQList = $moveResult['unansweredSQs'];
        if (strlen($unansweredSQList) > 0 && $backok != "N") {
            $notanswered = explode('|',$unansweredSQList);
        }
        else {
            $notanswered = array();
        }

        //CHECK INPUT
        $invalidSQList = $moveResult['invalidSQs'];
        if (strlen($invalidSQList) > 0 && $backok != "N") {
            $notvalidated = explode('|',$invalidSQList);
        }
        else {
            $notvalidated = array();
        }
    }

    // CHECK UPLOADED FILES
    // TMSW - Move this into LEM::NavigateForwards?
    $filenotvalidated = checkUploadedFileValidity($move, $backok);

    //SEE IF THIS GROUP SHOULD DISPLAY
    $show_empty_group = false;

    if ($_SESSION['step']==0)
        $show_empty_group = true;

    //SUBMIT ###############################################################################
    if ((isset($move) && $move == "movesubmit"))
    {
        setcookie ("limesurvey_timers", "", time() - 3600);// remove the timers cookies
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

            sendcacheheaders();
            doHeader();

            echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

            //Check for assessments
            if ($thissurvey['assessments']== "Y" && $assessments)
            {
                echo templatereplace(file_get_contents("$thistpl/assessment.pstpl"));
            }

            // fetch all filenames from $_SESSIONS['files'] and delete them all
            // from the /upload/tmp/ directory
            /*echo "<pre>";print_r($_SESSION);echo "</pre>";
            for($i = 1; isset($_SESSION['files'][$i]); $i++)
            {
            unlink('upload/tmp/'.$_SESSION['files'][$i]['filename']);
            }
            */
            // can't kill session before end message, otherwise INSERTANS doesn't work.
            $completed = templatereplace($thissurvey['surveyls_endtext']);
            $completed .= "<br /><strong><font size='2' color='red'>".$clang->gT("Did Not Save")."</font></strong><br /><br />\n\n";
            $completed .= $clang->gT("Your survey responses have not been recorded. This survey is not yet active.")."<br /><br />\n";
            if ($thissurvey['printanswers'] == 'Y')
            {
                // ClearAll link is only relevant for survey with printanswers enabled
                // in other cases the session is cleared at submit time
                $completed .= "<a href='{$publicurl}/index.php?sid=$surveyid&amp;move=clearall'>".$clang->gT("Clear Responses")."</a><br /><br />\n";
            }
            if($thissurvey['printanswers'] != 'Y')
            {
                killSession();
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
            $thissurvey['surveyls_url']=passthruReplace($thissurvey['surveyls_url'], $thissurvey);
            $thissurvey['surveyls_url']=templatereplace($thissurvey['surveyls_url']);   // to do INSERTANS substitutions

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

            //Send notifications

            SendSubmitNotifications();


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


            if (trim(strip_tags($thissurvey['surveyls_endtext']))=='')
            {
                $completed = "<br /><span class='success'>".$clang->gT("Thank you!")."</span><br /><br />\n\n"
                . $clang->gT("Your survey responses have been recorded.")."<br /><br />\n";
            }
            else
            {
                $completed = templatereplace($thissurvey['surveyls_endtext']);
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
                ."<a class='publicstatisticslink' href='statistics_user.php?sid=$surveyid&lang=".$_SESSION['s_lang']."' target='_blank'>"
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

                $url = templatereplace($thissurvey['surveyls_url']);    // TODO - check safety of this - provides access to any replacement value
                $url = passthruReplace($url, $thissurvey);
                $url = str_replace("{SAVEDID}",$saved_id, $url);               // to activate the SAVEDID in the END URL
                $url = str_replace("{TOKEN}",$clienttoken, $url);          // to activate the TOKEN in the END URL
                $url = str_replace("{SID}", $surveyid, $url);              // to activate the SID in the END URL
                $url = str_replace("{LANG}", $clang->getlangcode(), $url); // to activate the LANG in the END URL
                header("Location: {$url}");
            }


            if($thissurvey['printanswers'] != 'Y')
            {
                killSession();
            }

            doHeader();
            echo $content;

        }

        echo templatereplace(file_get_contents("$thistpl/completed.pstpl"));
        echo "\n<br />\n";
        if ((($LEMdebugLevel & LEM_DEBUG_TIMING) == LEM_DEBUG_TIMING)) {
            echo LimeExpressionManager::GetDebugTimingMessage();
        }
        if ((($LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY)) {
            echo "<table><tr><td align='left'><b>Group/Question Validation Results:</b>".$moveResult['message']."</td></tr></table>\n";
        }
        echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
        doFooter();
        exit;
    }
}

// IF GOT THIS FAR, THEN DISPLAY THE ACTIVE GROUP OF QUESTIONSs

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

//GET GROUP DETAILS

if ($surveyMode == 'group' && $previewgrp)
{
    setcookie("limesurvey_timers", "0");
    $_gid = sanitize_int($_REQUEST['gid']);

    LimeExpressionManager::StartSurvey($thissurvey['sid'], 'group', $surveyOptions, false,$LEMdebugLevel);
    $gseq = LimeExpressionManager::GetGroupSeq($_gid);
    if ($gseq == -1) {
        echo $clang->gT('Invalid group number for this survey: ') . $_gid;
        exit;
    }
    $moveResult = LimeExpressionManager::JumpTo($gseq+1,true);
    if (is_null($moveResult)) {
        echo $clang->gT('This group contains no questions.  You must add questions to this group before you can preview it');
        exit;
    }
    if (isset($moveResult)) {
        $_SESSION['step']= $moveResult['seq']+1;  // step is index base 1?
    }

    $stepInfo = LimeExpressionManager::GetStepIndexInfo($moveResult['seq']);
    $gid = $stepInfo['gid'];
    $groupname = $stepInfo['gname'];
    $groupdescription = $stepInfo['gtext'];
}
else
{
    if (($show_empty_group)||!isset($_SESSION['grouplist']))
    {
        $gid=-1; // Make sure the gid is unused. This will assure that the foreach (fieldarray as ia) has no effect.
        $groupname=$clang->gT("Submit your answers");
        $groupdescription=$clang->gT("There are no more questions. Please press the <Submit> button to finish this survey.");
    }
    else if ($surveyMode != 'survey')
        {
            $stepInfo = LimeExpressionManager::GetStepIndexInfo($moveResult['seq']);
            $gid = $stepInfo['gid'];
            $groupname = $stepInfo['gname'];
            $groupdescription = $stepInfo['gtext'];
        }
}

if ($_SESSION['step'] > $_SESSION['maxstep'])
{
    $_SESSION['maxstep'] = $_SESSION['step'];
}

//******************************************************************************************************
//PRESENT SURVEY
//******************************************************************************************************

$okToShowErrors = (!$previewgrp && (isset($invalidLastPage) ||  $_SESSION['prevstep'] == $_SESSION['step']));



require_once("qanda.php");

//store id's of all the question in $idlist array.
$idlist = array();
//Iterate through the questions about to be displayed:
$inputnames=array();
if (isset($_SESSION['grouplist']))
    foreach ($_SESSION['grouplist'] as $gl)
    {
        $gid = $gl[0];
        $qnumber = 0;

        if ($surveyMode != 'survey')
        {
            $onlyThisGID = $stepInfo['gid'];
            if ($onlyThisGID != $gid)
            {
                continue;
            }
        }

        // TMSW - could iterate through LEM::currentQset instead
        foreach ($_SESSION['fieldarray'] as $key => $ia)
        {
            ++$qnumber;
            $ia[9] = $qnumber; // incremental question count;

            if ((isset($ia[10]) && $ia[10] == $gid) || (!isset($ia[10]) && $ia[5] == $gid))
            {
                if ($surveyMode == 'question' && $ia[0] != $stepInfo['qid'])
                {
                    continue;
                }
                $qidattributes = getQuestionAttributes($ia[0], $ia[4]);
                if ($ia[4] != '*' && ($qidattributes === false || $qidattributes['hidden'] == 1))
                {
                    continue;
                }

                //Get the answers/inputnames
                // TMSW - can content of retrieveAnswers() be provided by LEM?  Review scope of what it provides.
                // TODO - retrieveAnswers is slow - queries database separately for each question. May be fixed in _CI or _YII ports, so ignore for now
                list($plus_qanda, $plus_inputnames) = retrieveAnswers($ia);

                //can eliminate extra space for these 2 arrays if $_SESSION['fieldmap'] is used directly!

                $idlist[] = $ia[1];
                if ($plus_qanda)
                {
                    $plus_qanda[] = $ia[4];
                    $plus_qanda[] = $ia[6]; // adds madatory identifyer for adding mandatory class to question wrapping div
                    $qanda[] = $plus_qanda;
                }
                if ($plus_inputnames)
                {
                    $inputnames = addtoarray_single($inputnames, $plus_inputnames);
                }

                //Display the "mandatory" popup if necessary
                // TMSW - get question-level error messages - don't call **_popup() directly
                if ($okToShowErrors && $stepInfo['mandViolation'])
                {
                    list($mandatorypopup, $popup) = mandatory_popup($ia, $notanswered);
                }

                //Display the "validation" popup if necessary
                if ($okToShowErrors && !$stepInfo['valid'])
                {
                    list($validationpopup, $vpopup) = validation_popup($ia, $notvalidated);
                }

                // Display the "file validation" popup if necessary
                if ($okToShowErrors && isset($filenotvalidated))
                {
                    list($filevalidationpopup, $fpopup) = file_validation_popup($ia, $filenotvalidated);
                }
            }
            if ($ia[4] == "|")
                $upload_file = TRUE;
        } //end iteration
}

if ($surveyMode != 'survey' && isset($thissurvey['showprogress']) && $thissurvey['showprogress'] == 'Y')
{
    if ($show_empty_group)
    {
        $percentcomplete = makegraph($_SESSION['totalsteps']+1, $_SESSION['totalsteps']);
    }
    else
    {
        $percentcomplete = makegraph($_SESSION['step'], $_SESSION['totalsteps']);
    }
}
if (!(isset($languagechanger) && strlen($languagechanger) > 0) && function_exists('makelanguagechanger')) {
    $languagechanger = makelanguagechanger();
}

//READ TEMPLATES, INSERT DATA AND PRESENT PAGE
sendcacheheaders();
doHeader();

if (isset($popup)) {echo $popup;}
if (isset($vpopup)) {echo $vpopup;}
if (isset($fpopup)) {echo $fpopup;}

echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

//ALTER PAGE CLASS TO PROVIDE WHOLE-PAGE ALTERNATION
if ($surveyMode != 'survey' && $_SESSION['step'] != $_SESSION['prevstep'] ||
(isset($_SESSION['stepno']) && $_SESSION['stepno'] % 2))
{
    if (!isset($_SESSION['stepno'])) $_SESSION['stepno'] = 0;
    if ($_SESSION['step'] != $_SESSION['prevstep']) ++$_SESSION['stepno'];
    if ($_SESSION['stepno'] % 2)
    {
        echo "<script type=\"text/javascript\">\n"
        . "  $(\"body\").addClass(\"page-odd\");\n"
        . "</script>\n";
    }
}

$hiddenfieldnames=implode("|", $inputnames);

if (isset($upload_file) && $upload_file)
    echo "<form enctype=\"multipart/form-data\" method='post' action='{$publicurl}/index.php' id='limesurvey' name='limesurvey' autocomplete='off'>
    <!-- INPUT NAMES -->
    <input type='hidden' name='fieldnames' value='{$hiddenfieldnames}' id='fieldnames' />\n";
else
    echo "<form method='post' action='{$publicurl}/index.php' id='limesurvey' name='limesurvey' autocomplete='off'>
    <!-- INPUT NAMES -->
    <input type='hidden' name='fieldnames' value='{$hiddenfieldnames}' id='fieldnames' />\n";
echo sDefaultSubmitHandler();

if ($surveyMode == 'survey')
{
    if(isset($thissurvey['showwelcome']) && $thissurvey['showwelcome'] == 'N') {
        //Hide the welcome screen if explicitly set
    } else {
        echo templatereplace(file_get_contents("$thistpl/welcome.pstpl"))."\n";
    }

    if ($thissurvey['anonymized'] == "Y")
    {
        echo templatereplace(file_get_contents("$thistpl/privacy.pstpl"))."\n";
    }
}

// <-- START THE SURVEY -->
if ($surveyMode != 'survey') {
    echo templatereplace(file_get_contents("{$thistpl}/survey.pstpl"));
}

// the runonce element has been changed from a hidden to a text/display:none one
// in order to workaround an not-reproduced issue #4453 (lemeur)
echo "<input type='text' id='runonce' value='0' style='display: none;'/>
<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->
<script type='text/javascript'>
<!--\n";

echo "var LEMradix='" . $radix . "';\n";
echo "var numRegex = new RegExp('[^-' + LEMradix + '0-9]','g');\n";
echo "var intRegex = new RegExp('[^-0-9]','g');\n";

print <<<END
	function fixnum_checkconditions(value, name, type, evt_type, intonly)
	{
        newval = new String(value);
        if (typeof intonly !=='undefined' && intonly==1) {
            newval = newval.replace(intRegex,'');
        }
        else {
            newval = newval.replace(numRegex,'');
        }
        if (LEMradix === ',') {
            newval = newval.split(',').join('.');
        }
        if (newval != '-' && newval != '.' && newval != '-.' && newval != parseFloat(newval)) {
            newval = '';
        }
        displayVal = newval;
        if (LEMradix === ',') {
            displayVal = displayVal.split('.').join(',');
        }
        if (name.match(/other$/)) {
            $('#answer'+name+'text').val(displayVal);
        }
        $('#answer'+name).val(displayVal);

        if (typeof evt_type === 'undefined')
        {
            evt_type = 'onchange';
        }
        checkconditions(newval, name, type, evt_type);
	}

	function checkconditions(value, name, type, evt_type)
	{
        if (typeof evt_type === 'undefined')
        {
            evt_type = 'onchange';
        }
        if (type == 'radio' || type == 'select-one')
        {
            var hiddenformname='java'+name;
            document.getElementById(hiddenformname).value=value;
        }
        else if (type == 'checkbox')
        {
            if (document.getElementById('answer'+name).checked)
            {
                $('#java'+name).val('Y');
            } else
            {
                $('#java'+name).val('');
            }
        }
        else if (type == 'text' && name.match(/other$/) && typeof document.getElementById('java'+name) !== 'undefined' && document.getElementById('java'+name) != null)
        {
            $('#java'+name).val(value);
        }
        ExprMgr_process_relevance_and_tailoring(evt_type,name,type);

END;

if ($previewgrp)
{
    // force the group to be visible, even if irrelevant - will not always work
    print <<<END
    $('#relevanceG' + LEMgseq).val(1);
    $(document).ready(function() {
        $('#group-' + LEMgseq).show();
    });
    $(document).change(function() {
        $('#group-' + LEMgseq).show();
    });
    $(document).bind('keydown',function(e) {
                if (e.keyCode == 9) {
                    $('#group-' + LEMgseq).show();
                    return true;
                }
                return true;
            });

END;
}

print <<<END
	}
// -->
</script>
END;

//Display the "mandatory" message on page if necessary
if (isset($showpopups) && $showpopups == 0 && $stepInfo['mandViolation'] && $okToShowErrors)
{
    echo "<p><span class='errormandatory'>" . $clang->gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.") . "</span></p>";
}

//Display the "validation" message on page if necessary
if (isset($showpopups) && $showpopups == 0 && !$stepInfo['valid'] && $okToShowErrors)
{
    echo "<p><span class='errormandatory'>" . $clang->gT("One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid.") . "</span></p>";
}

//Display the "file validation" message on page if necessary
if (isset($showpopups) && $showpopups == 0 && isset($filenotvalidated) && $filenotvalidated == true && $okToShowErrors)
{
    echo "<p><span class='errormandatory'>" . $clang->gT("One or more uploaded files are not in proper format/size. You cannot proceed until these files are valid.") . "</span></p>";
}


if (isset($_SESSION['grouplist']))
    $_gseq = -1;
foreach ($_SESSION['grouplist'] as $gl)
{
    $gid=$gl[0];
    ++$_gseq;
    $groupname=$gl[1];
    $groupdescription=$gl[2];

    if ($surveyMode != 'survey' && $gid != $onlyThisGID) {
        continue;
    }

    echo "\n\n<!-- START THE GROUP -->\n";
    echo "\n\n<div id='group-$_gseq'";
    $gnoshow = LimeExpressionManager::GroupIsIrrelevantOrHidden($_gseq);
    if  ($gnoshow && !$previewgrp)
    {
        echo " style='display: none;'";
    }
    echo ">\n";
    echo templatereplace(file_get_contents("$thistpl/startgroup.pstpl"));
    echo "\n";

    if ($groupdescription)
    {
        echo templatereplace(file_get_contents("$thistpl/groupdescription.pstpl"));
    }
    echo "\n";

    echo "\n\n<!-- PRESENT THE QUESTIONS -->\n";
    $i=0;
    foreach ($qanda as $qa) // one entry per QID
    {
        if ($gid != $qa[6]) {
            continue;
        }

        $qid = $qa[4];
        $qinfo = LimeExpressionManager::GetQuestionStatus($qid);
        $lastgrouparray = explode("X",$qa[7]);
        $lastgroup = $lastgrouparray[0]."X".$lastgrouparray[1]; // id of the last group, derived from question id
        $lastanswer = $qa[7];

        $q_class = question_class($qinfo['info']['type']);

        $man_class = '';
        if ($qinfo['info']['mandatory']=='Y') {
            $man_class .= ' mandatory';
        }

        if ($qinfo['anyUnanswered'] && $_SESSION['maxstep'] != $_SESSION['step']) {
            $man_class .= ' missing';
        }

        $n_q_display = '';
        if ($qinfo['hidden'] && $qinfo['info']['type'] != '*') {
            continue;	// skip this one
        }

        if (!$qinfo['relevant'] || ($qinfo['hidden'] && $qinfo['info']['type'] == '*')) {
            $n_q_display = ' style="display: none;"';
        }

        $question= $qa[0];

        //===================================================================
        // The following four variables offer the templating system the
        // capacity to fully control the HTML output for questions making the
        // above echo redundant if desired.
        $question['essentials'] = 'id="question'.$qa[4].'"'.$n_q_display;
        $question['class'] = $q_class;
        $question['man_class'] = $man_class;
        $question['code']=$qa[5];
        $question['sgq']=$qa[7];
        $question['aid']=$qinfo['info']['aid'];
        $question['sqid']=$qinfo['info']['sqid'];
        $question['type']=$qinfo['info']['type'];
        //===================================================================
        $answer=$qa[1];

        $help=$qinfo['info']['help'];   // $qa[2];

        $answer_id = $idlist[$i];
        $i++;
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
            echo templatereplace($question_template,NULL,false,$qa[4]);
            echo '
            </div>
            ';
        }
        else
        {
            // TMSW - eventually refactor so that only substitutes the QUESTION_** fields - doesn't need full power of template replace
            // TMSW - also, want to return a string, and call templatereplace once on that result string once all done.
            echo templatereplace($question_template,NULL,false,$qa[4]);
        };
    }
    if ($surveyMode == 'group') {
        echo "<input type='hidden' name='lastgroup' value='$lastgroup' id='lastgroup' />\n"; // for counting the time spent on each group
    }
    if ($surveyMode == 'question') {
        echo "<input type='hidden' name='lastanswer' value='$lastanswer' id='lastanswer' />\n";
    }
    echo "\n\n<!-- END THE GROUP -->\n";
    echo templatereplace(file_get_contents("$thistpl/endgroup.pstpl"));
    echo "\n\n</div>\n";

}

LimeExpressionManager::FinishProcessingGroup($LEMskipReprocessing);
echo LimeExpressionManager::GetRelevanceAndTailoringJavaScript();
LimeExpressionManager::FinishProcessingPage();

if (!$previewgrp){
    $navigator = surveymover(); //This gets globalised in the templatereplace function

    echo "\n\n<!-- PRESENT THE NAVIGATOR -->\n";
    echo templatereplace(file_get_contents("$thistpl/navigator.pstpl"));
    echo "\n";

    if ($thissurvey['active'] != "Y")
    {
        echo "<p style='text-align:center' class='error'>".$clang->gT("This survey is currently not active. You will not be able to save your responses.")."</p>\n";
    }


    if($surveyMode != 'survey' && $thissurvey['allowjumps']=='Y')
    {
        echo "\n\n<!-- PRESENT THE INDEX -->\n";

        echo '<div id="index"><div class="container"><h2>' . $clang->gT("Question index") . '</h2>';

        $stepIndex = LimeExpressionManager::GetStepIndexInfo();
        $lastGseq=-1;
        $gseq = -1;
        $grel=true;
        for($v = 0, $n = 0; $n != $_SESSION['maxstep']; ++$n)
        {
            if (!isset($stepIndex[$n])) {
                continue;   // this is an invalid group - skip it
            }
            $stepInfo = $stepIndex[$n];

            if ($surveyMode == 'question')
            {
                if ($lastGseq != $stepInfo['gseq']) {
                    // show the group label
                    ++$gseq;
                    $g = $_SESSION['grouplist'][$gseq];
                    $grel = !LimeExpressionManager::GroupIsIrrelevantOrHidden($gseq);
                    if ($grel)
                    {
                        $gtitle = LimeExpressionManager::ProcessString($g[1]);
                        echo '<h3>' . FlattenText($gtitle) . "</h3>";
                    }
                    $lastGseq = $stepInfo['gseq'];
                }
                if (!$grel || !$stepInfo['show'])
                    continue;
                $q = $_SESSION['fieldarray'][$n];
            }
            else
            {
                ++$gseq;
                if (!$stepInfo['show'])
                    continue;
                $g = $_SESSION['grouplist'][$gseq];
            }

            if ($surveyMode == 'group')
            {
                $indexlabel = LimeExpressionManager::ProcessString($g[1]);
            }
            else
            {
                $indexlabel = LimeExpressionManager::ProcessString($q[3]);
            }

            $sText = (($surveyMode == 'group') ? FlattenText($indexlabel) : FlattenText($indexlabel));
            $bGAnsw = !$stepInfo['anyUnanswered'];

            ++$v;

            $class = ($n == $_SESSION['step'] - 1? 'current': ($bGAnsw? 'answer': 'missing'));
            if($v % 2) $class .= " odd";

            $s = $n + 1;
            echo "<div class=\"row $class\" onclick=\"javascript:document.limesurvey.move.value = '$s'; document.limesurvey.submit();\"><span class=\"hdr\">$v</span><span title=\"$sText\">$sText</span></div>";
        }

        if($_SESSION['maxstep'] == $_SESSION['totalsteps'])
        {
            echo "<input class='submit' type='submit' accesskey='l' onclick=\"javascript:document.limesurvey.move.value = 'movesubmit';\" value=' "
            . $clang->gT("Submit")." ' name='move2' />\n";
        }

        echo '</div></div>';
        /* Can be replaced by php or in global js */
        echo "<script type=\"text/javascript\">\n"
        . "  $(\".outerframe\").addClass(\"withindex\");\n"
        . "  var idx = $(\"#index\");\n"
        . "  var row = $(\"#index .row.current\");\n"
        . "  idx.scrollTop(row.position().top - idx.height() / 2 - row.height() / 2);\n"
        . "</script>\n";
        echo "\n";
    }

    echo "<input type='hidden' name='thisstep' value='{$_SESSION['step']}' id='thisstep' />\n";
    echo "<input type='hidden' name='sid' value='$surveyid' id='sid' />\n";
    echo "<input type='hidden' name='start_time' value='".time()."' id='start_time' />\n";
    $_SESSION['LEMpostKey'] = mt_rand();
    echo "<input type='hidden' name='LEMpostKey' value='{$_SESSION['LEMpostKey']}' id='LEMpostKey' />\n";

    if (isset($token) && !empty($token)) {
        echo "\n<input type='hidden' name='token' value='$token' id='token' />\n";
    }
}

if (($LEMdebugLevel & LEM_DEBUG_TIMING) == LEM_DEBUG_TIMING) {
    echo LimeExpressionManager::GetDebugTimingMessage();
}
if (($LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY) {
    echo "<table><tr><td align='left'><b>Group/Question Validation Results:</b>".$moveResult['message']."</td></tr></table>\n";
}
echo "</form>\n";

echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));

echo "\n";

doFooter();

// Closing PHP tag intentionally left out - yes, it is okay
