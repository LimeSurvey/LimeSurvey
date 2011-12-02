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
 * $Id: group.php 11359 2011-11-12 06:12:48Z tmswhite $
 */

// $LEMdebugLevel - customizable debugging for Lime Expression Manager
// 0=none
// 1=timings only
// 2=timings + pretty-printed results of validating questions and groups
// 3=#1 + pretty-printed results of validating questions and groups
$LEMdebugLevel=0;
$surveyMode = (($thissurvey['format'] == 'G') ? 'group' : 'question');
$surveyOptions = array(
    'active'=>($thissurvey['active']=='Y'),
    'allowsave'=>($thissurvey['allowsave']=='Y'),
    'anonymized'=>($thissurvey['anonymized']!='N'),
    'datestamp'=>($thissurvey['datestamp']=='Y'),
    'hyperlinkSyntaxHighlighting'=>($LEMdebugLevel>=2),     // TODO set this to true if in admin mode but not if running a survey
    'ipaddr'=>($thissurvey['ipaddr']=='Y'),
    'refurl'=>(($thissurvey['refurl'] == "Y") ? $_SESSION['refurl'] : NULL),
    'rooturl'=>(isset($rooturl) ? $rooturl : ''),
    'surveyls_dateformat'=>(isset($thissurvey['surveyls_dateformat']) ? $thissurvey['surveyls_dateformat'] : 1),
    'startlanguage'=>(isset($_SESSION['s_lang']) ? $_SESSION['s_lang'] : 'en'),
    'target'=>(isset($uploaddir) ?  "{$uploaddir}/surveys/{$thissurvey['sid']}/files/" : "/temp/{$thissurvey['sid']}/files"),
    'tempdir'=>(isset($tempdir) ? $tempdir : '/temp/'),
    'timeadjust'=>(isset($timeadjust) ? $timeadjust : 0),
    'token'=>(isset($clienttoken) ? $clienttoken : NULL),
);

//Security Checked: POST, GET, SESSION, REQUEST, returnglobal, DB
$previewgrp = false;
if (isset($_REQUEST['action']) && ($_REQUEST['action']=='previewgroup') && $thissurvey['format'] == 'G'){
    $previewgrp = true;
}
if (isset($_REQUEST['newtest']))
	if ($_REQUEST['newtest']=="Y")
		setcookie("limesurvey_timers", "0");
$show_empty_group = false;
if (!isset($homedir) || isset($_REQUEST['$homedir'])) {die("Cannot run this script directly");}

if ($previewgrp)
{
	$_SESSION['prevstep'] = 1;
	$_SESSION['maxstep'] = 0;
}
else
{
    //RUN THIS IF THIS IS THE FIRST TIME , OR THE FIRST PAGE ########################################
    if (!isset($_SESSION['step']))  //  || !$_SESSION['step']) - don't do this for step0, else rebuild the session
    {
        $totalquestions = buildsurveysession();
        LimeExpressionManager::StartSurvey($thissurvey['sid'], $surveyMode, $surveyOptions, true,$LEMdebugLevel);
        $_SESSION['step'] = 0;
        if(isset($thissurvey['showwelcome']) && $thissurvey['showwelcome'] == 'N') {
            //If explicitply set, hide the welcome screen
            $_SESSION['step'] = 1;
        }
    }

    if (!isset($_SESSION['totalsteps'])) {$_SESSION['totalsteps']=0;}
    if (!isset($_SESSION['maxstep'])) {$_SESSION['maxstep']=0;}
    $_SESSION['prevstep']=$_SESSION['step'];

    //Move current step ###########################################################################
    if (isset($move) && $move == 'moveprev' && ($thissurvey['allowprev']=='Y' || $thissurvey['allowjumps']=='Y'))
    {
        $moveResult = LimeExpressionManager::NavigateBackwards();
        if ($moveResult['at_start']) {
            $_SESSION['step']=0;
            unset($moveResult); // so display welcome page again
        }
    }
    if (isset($move) && ($move == "movenext" || $move == "movesubmit"))
    {
        $moveResult = LimeExpressionManager::NavigateForwards(false);
    }
    if (isset($move) && bIsNumericInt($move) && $thissurvey['allowjumps']=='Y')
    {
        $move = (int)$move;
        if ($move > 0 && (($move <= $_SESSION['step']) || (isset($_SESSION['maxstep']) && $move <= $_SESSION['maxstep']))) {
            $moveResult = LimeExpressionManager::JumpTo($move,false);
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
    }

    // We do not keep the participant session anymore when the same browser is used to answer a second time a survey (let's think of a library PC for instance).
    // Previously we used to keep the session and redirect the user to the
    // submit page.

    if ($_SESSION['step'] == 0) {
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

    if (isset($move) || isset($_POST['saveprompt']))
    {
        require_once("save1.php");
    }

    //Now, we check mandatory questions if necessary
    //CHECK IF ALL CONDITIONAL MANDATORY QUESTIONS THAT APPLY HAVE BEEN ANSWERED
    if (isset($moveResult) && !$moveResult['finished'])
    {
        $unansweredSQList = $moveResult['unansweredSQs'];
        if (strlen($unansweredSQList) > 0 && $backok != "N") {
            $notanswered = explode('|',$unansweredSQList);
        }

        //CHECK INPUT
        $invalidSQList = $moveResult['invalidSQs'];
        if (strlen($invalidSQList) > 0 && $backok != "N") {
            $notvalidated = explode('|',$invalidSQList);
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
    if ((isset($move) && $move == "movesubmit")  && (!isset($notanswered) || is_array($notanswered)) && (!isset($notvalidated) || is_array($notvalidated) ) && (!isset($filenotvalidated) || !$filenotvalidated))
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
        // TMSW LEM::NavigateForwards() must know whether the survey is active?
        if ($thissurvey['active'] != "Y") //If survey is not active, don't really commit
        {
            if ($thissurvey['assessments']== "Y")
            {
                // TMSW - replace this with EM?
                $assessments = doAssessment($surveyid);
            }
            $thissurvey['surveyls_url']=dTexts__run($thissurvey['surveyls_url']);
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

            // fetch all filenames from $_SESSIONS['files'] and delete them all
            // from the /upload/tmp/ directory
            /*echo "<pre>";print_r($_SESSION);echo "</pre>";
            for($i = 1; isset($_SESSION['files'][$i]); $i++)
            {
                unlink('upload/tmp/'.$_SESSION['files'][$i]['filename']);
            }
            */
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
            // TMSW - TODO - check that this works as desired
            $thissurvey['surveyls_url']=dTexts__run($thissurvey['surveyls_url']);
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

            //Send notifications

            SendSubmitNotifications();


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
                $url = dTexts__run($thissurvey['surveyls_url']);
                $url = passthruReplace($url, $thissurvey);
                $url = str_replace("{SAVEDID}",$saved_id, $url);               // to activate the SAVEDID in the END URL
                $url = str_replace("{TOKEN}",$clienttoken, $url);          // to activate the TOKEN in the END URL
                $url = str_replace("{SID}", $surveyid, $url);              // to activate the SID in the END URL
                $url = str_replace("{LANG}", $clang->getlangcode(), $url); // to activate the LANG in the END URL
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

if ($previewgrp)
{
	setcookie("limesurvey_timers", "0");

    LimeExpressionManager::StartSurvey($thissurvey['sid'], 'group', $surveyOptions, false,$LEMdebugLevel);
    $gseq = LimeExpressionManager::GetGroupSeq($_REQUEST['gid']);
    if ($gseq == -1) {
        echo 'Invalid Group' . $_REQUEST['gid'];
    }
    $moveResult = LimeExpressionManager::JumpTo($gseq+1,false,true);
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
    else
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




require_once("qanda1.php"); //This should be qanda.php when finished

//Iterate through the questions about to be displayed:
$inputnames=array();

$qnumber = 0;

foreach ($_SESSION['fieldarray'] as $key=>$ia)
{
    ++$qnumber;
    $ia[9] = $qnumber; // incremental question count;

    if ((isset($ia[10]) && $ia[10] == $gid) || (!isset($ia[10]) && $ia[5] == $gid))
    {
        if ($surveyMode == 'question' && $ia[0] != $stepInfo['qid']) {
            continue;
        }
        $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
        if ($ia[4] != '*' && ($qidattributes===false || $qidattributes['hidden']==1)) {
            continue;
        }

        //Get the answers/inputnames
        // TMSW - can content of retrieveAnswers() be provided by LEM?  Review scope of what it provides.
        // TODO - retrieveAnswers is slow - queries database separately for each question. May be fixed in _CI or _YII ports, so ignore for now
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
        // TMSW - get question-level error messages - don't call **_popup() directly
        if (!$previewgrp && $stepInfo['mandViolation'] && $_SESSION['prevstep'] == $_SESSION['step'])
        {
            list($mandatorypopup, $popup)=mandatory_popup($ia, $notanswered);
        }

        //Display the "validation" popup if necessary
        if (!$previewgrp && !$stepInfo['valid'] && $_SESSION['prevstep'] == $_SESSION['step'])
        {
            list($validationpopup, $vpopup)=validation_popup($ia, $notvalidated);
        }

        // Display the "file validation" popup if necessary
        if (!$previewgrp && isset($filenotvalidated) && $_SESSION['prevstep'] == $_SESSION['step'])
        {
            list($filevalidationpopup, $fpopup) = file_validation_popup($ia, $filenotvalidated);
        }
    }
    if ($ia[4] == "|")
        $upload_file = TRUE;
} //end iteration

if (isset($thissurvey['showprogress']) && $thissurvey['showprogress'] == 'Y')
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
$languagechanger = makelanguagechanger();

//READ TEMPLATES, INSERT DATA AND PRESENT PAGE
sendcacheheaders();
doHeader();

if (isset($popup)) {echo $popup;}
if (isset($vpopup)) {echo $vpopup;}
if (isset($fpopup)) {echo $fpopup;}

echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));

//ALTER PAGE CLASS TO PROVIDE WHOLE-PAGE ALTERNATION
if ($_SESSION['step'] != $_SESSION['prevstep'] ||
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

// <-- END FEATURE - SAVE

// <-- START THE SURVEY -->

echo templatereplace(file_get_contents("{$thistpl}/survey.pstpl"));

// the runonce element has been changed from a hidden to a text/display:none one
// in order to workaround an not-reproduced issue #4453 (lemeur)
echo "<input type='text' id='runonce' value='0' style='display: none;'/>
    <!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->
    <script type='text/javascript'>
    <!--\n";

print <<<END
	function noop_checkconditions(value, name, type)
	{
        checkconditions(value, name, type);
	}

	function checkconditions(value, name, type)
	{
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
        ExprMgr_process_relevance_and_tailoring();
	}
// -->
</script>
END;

echo "\n\n<!-- START THE GROUP -->\n";
echo templatereplace(file_get_contents("$thistpl/startgroup.pstpl"));
echo "\n";

if ($groupdescription)
{
    echo templatereplace(file_get_contents("$thistpl/groupdescription.pstpl"));
}
echo "\n";

//Display the "mandatory" message on page if necessary
if (isset($showpopups) && $showpopups == 0 && $stepInfo['mandViolation'] && $_SESSION['prevstep'] == $_SESSION['step'])
{
    echo "<p><span class='errormandatory'>" . $clang->gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.") . "</span></p>";
}

//Display the "validation" message on page if necessary
if (isset($showpopups) && $showpopups == 0 && !$stepInfo['valid'] && $_SESSION['prevstep'] == $_SESSION['step'])
{
    echo "<p><span class='errormandatory'>" . $clang->gT("One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid.") . "</span></p>";
}

//Display the "file validation" message on page if necessary
if (isset($showpopups) && $showpopups == 0 && isset($filenotvalidated) && $filenotvalidated == true && $_SESSION['prevstep'] == $_SESSION['step'])
{
    echo "<p><span class='errormandatory'>" . $clang->gT("One or more uploaded files are not in proper format/size. You cannot proceed until these files are valid.") . "</span></p>";
}

echo "\n\n<!-- PRESENT THE QUESTIONS -->\n";
{
    foreach ($qanda as $qa) // one entry per QID
    {
        $qid = $qa[4];
        $qinfo = LimeExpressionManager::GetQuestionStatus($qid);
		$lastgrouparray = explode("X",$qa[7]);
		$lastgroup = $lastgrouparray[0]."X".$lastgrouparray[1]; // id of the last group, derived from question id

        $q_class = question_class($qinfo['info']['type']);

        $man_class = '';
        if ($qinfo['info']['mandatory']=='Y') {
            $man_class .= ' mandatory';
        }

        if (strlen($qinfo['unansweredSQs']) > 0  && $_SESSION['maxstep'] != $_SESSION['step']) {
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
        //===================================================================
        $answer=$qa[1];
        $help=$qa[2];

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
	echo "<input type='hidden' name='lastgroup' value='$lastgroup' id='lastgroup' />\n"; // for counting the time spent on each group


}
echo "\n\n<!-- END THE GROUP -->\n";
echo templatereplace(file_get_contents("$thistpl/endgroup.pstpl"));
echo "\n";

LimeExpressionManager::FinishProcessingGroup();
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


    if($thissurvey['allowjumps']=='Y')
    {
        echo "\n\n<!-- PRESENT THE INDEX -->\n";

        echo '<div id="index"><div class="container"><h2>' . $clang->gT("Question index") . '</h2>';

        $stepIndex = LimeExpressionManager::GetStepIndexInfo();
        $lastGseq=-1;
        for($v = 0, $n = 0; $n != $_SESSION['maxstep']; ++$n)
        {
            $stepInfo = $stepIndex[$n];

            if (!$stepInfo['show'])
                continue;

            if ($surveyMode == 'question' && $lastGseq != $stepInfo['gseq']) {
                // show the group label
                echo '<h3>' . FlattenText($stepInfo['gname']) . "</h3>";
                $lastGseq = $stepInfo['gseq'];
            }

            $sText = (($surveyMode == 'group') ? FlattenText($stepInfo['gname']) : FlattenText($stepInfo['qtext']));
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
    if (isset($token) && !empty($token)) {
        echo "\n<input type='hidden' name='token' value='$token' id='token' />\n";
    }
}

if ($LEMdebugLevel >= 1) {
    echo LimeExpressionManager::GetDebugTimingMessage();
}
if ($LEMdebugLevel >= 2) {
     echo "<table><tr><td align='left'><b>Group/Question Validation Results:</b>".$moveResult['message']."</td></tr></table>\n";
}
echo "</form>\n";

echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));

echo "\n";

doFooter();

// Closing PHP tag intentionally left out - yes, it is okay
