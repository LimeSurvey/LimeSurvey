<?php
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*	$Id$
*/
// Security Checked: POST, GET, SESSION, REQUEST, returnGlobal, DB

//if (!isset($homedir) || isset($_REQUEST['$homedir'])) {die("Cannot run this script directly");}

/*
* $conditions element structure
* $condition[n][0] => qid = question id
* $condition[n][1] => cqid = question id of the target question, or 0 for TokenAttr leftOperand
* $condition[n][2] => field name of element [1] (Except for type M or P)
* $condition[n][3] => value to be evaluated on answers labeled.
* $condition[n][4] => type of question
* $condition[n][5] => SGQ code of element [1] (sub-part of [2])
* $condition[n][6] => method used to evaluate
* $condition[n][7] => scenario *NEW BY R.L.J. van den Burg*
*/

/**
* setNoAnswerMode
*/
function setNoAnswerMode($thissurvey)
{
    if (getGlobalSetting('shownoanswer') > 0 && $thissurvey['shownoanswer'] != 'N')
    {
        define('SHOW_NO_ANSWER', 1);
    }
    else
    {
        define('SHOW_NO_ANSWER', 0);
    }
}

/**
* This function returns an array containing the "question/answer" html display
* and a list of the question/answer fieldnames associated. It is called from
* question.php, group.php, survey.php or preview.php
*
* @param mixed $ia
* @return mixed
*/
function retrieveAnswers($q)
{
    //globalise required config variables
    global $thissurvey; //These are set by index.php

    //$clang = Yii::app()->lang;
    $clang = Yii::app()->lang;

    // TMSW - eliminate this - get from LEM
    //A bit of housekeeping to stop PHP Notices
    if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$q->fieldname])) {$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$q->fieldname] = "";}
    $aQuestionAttributes = getQuestionAttributeValues($q);

    // Previously in limesurvey, it was virtually impossible to control how the start of questions were formatted.
    // this is an attempt to allow users (or rather system admins) some control over how the starting text is formatted.
    $number = isset($q->questioncount) ? $q->questioncount : '';

    // TMSW - populate this directly from LEM? - this this is global
    $question_text = array(
    'all' => '' // All has been added for backwards compatibility with templates that use question_start.pstpl (now redundant)
    ,'text' => $q->retrieveText()
    ,'code' => $q->title
    ,'number' => $number
    ,'mandatory' => ''
    ,'man_message' => ''
    ,'valid_message' => ''
    ,'file_valid_message' => ''
    ,'class' => ''
    ,'man_class' => ''
    ,'input_error_class' => ''// provides a class.
    ,'essentials' => ''
    );

    $qtitle = $q->getTitle();
    $question_text['help'] = $q->getHelp();
    $answer = $q->getAnswerHTML();

    if ($q->mandatory == 'Y')
    {
        $qtitle = '<span class="asterisk">'.$clang->gT('*').'</span>'.$qtitle;
        $question_text['mandatory'] = $clang->gT('*');
    }
    //If this question is mandatory but wasn't answered in the last page
    //add a message HIGHLIGHTING the question
    if (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['prevstep'])) {
        $mandatory_msg = mandatory_message($q);
    }
    else {
        $mandatory_msg = '';
    }
    $qtitle .= $mandatory_msg;
    $question_text['man_message'] = $mandatory_msg;

    //    if (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['prevstep'])) {
    if (!isset($aQuestionAttributes['hide_tip']) || $aQuestionAttributes['hide_tip']==0) {
        $_vshow = true; // whether should initially be visible - TODO should also depend upon 'hidetip'?
    }
    else {
        $_vshow = false;
    }
    list($validation_msg,$isValid) = validation_message($q,$_vshow);

    $qtitle .= $validation_msg;
    $question_text['valid_message'] = $validation_msg;

    if (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['prevstep'])) {
        $file_validation_msg = $q->getFileValidationMessage();
    }
    else {
        $file_validation_msg = '';
        $isValid = true;    // don't want to show any validation messages.
    }
    $qtitle .= $file_validation_msg;
    $question_text['file_valid_message'] = $file_validation_msg;

    if(!empty($question_text['man_message']) || !$isValid || !empty($question_text['file_valid_message']))
    {
        $question_text['input_error_class'] = ' input-error';// provides a class to style question wrapper differently if there is some kind of user input error;
    }

    // =====================================================
    // START: legacy question_start.pstpl code
    // The following section adds to the templating system by allowing
    // templaters to control where the various parts of the question text
    // are put.

    $sTemplate = isset($thissurvey['template']) ? $thissurvey['template'] : NULL;
    if(is_file('templates/'.validateTemplateDir($sTemplate).'/question_start.pstpl'))
    {
        $qtitle_custom = '';

        $replace=array();
        foreach($question_text as $key => $value)
        {
            $find[] = '{QUESTION_'.strtoupper($key).'}'; // Match key words from template
            $replace[] = $value; // substitue text
        };
        if(!defined('QUESTION_START'))
        {
            define('QUESTION_START' , file_get_contents(getTemplatePath($thissurvey['template']).'/question_start.pstpl' , true));
        };
        $qtitle_custom = str_replace( $find , $replace , QUESTION_START);

        $c = 1;
        // START: <EMBED> work-around step 1
        $qtitle_custom = preg_replace( '/(<embed[^>]+>)(<\/embed>)/i' , '\1NOT_EMPTY\2' , $qtitle_custom );
        // END <EMBED> work-around step 1
        while($c > 0) // This recursively strips any empty tags to minimise rendering bugs.
        {
            $matches = 0;
            $oldtitle=$qtitle_custom;
            $qtitle_custom = preg_replace( '/<([^ >]+)[^>]*>[\r\n\t ]*<\/\1>[\r\n\t ]*/isU' , '' , $qtitle_custom , -1); // I removed the $count param because it is PHP 5.1 only.

            $c = ($qtitle_custom!=$oldtitle)?1:0;
        };
        // START <EMBED> work-around step 2
        $qtitle_custom = preg_replace( '/(<embed[^>]+>)NOT_EMPTY(<\/embed>)/i' , '\1\2' , $qtitle_custom );
        // END <EMBED> work-around step 2
        while($c > 0) // This recursively strips any empty tags to minimise rendering bugs.
        {
            $matches = 0;
            $oldtitle=$qtitle_custom;
            $qtitle_custom = preg_replace( '/(<br(?: ?\/)?>(?:&nbsp;|\r\n|\n\r|\r|\n| )*)+$/i' , '' , $qtitle_custom , -1 ); // I removed the $count param because it is PHP 5.1 only.
            $c = ($qtitle_custom!=$oldtitle)?1:0;
        };

        $question_text['all'] = $qtitle_custom;
    }
    else
    {
        $question_text['all'] = $qtitle;
    };
    // END: legacy question_start.pstpl code
    //===================================================================
    $qtitle = $question_text;
    // =====================================================

    return $qtitle;
}

function mandatory_message($q)
{
    $qinfo = LimeExpressionManager::GetQuestionStatus($q->id);
    if ($qinfo['mandViolation']) {
        return $qinfo['mandTip'];
    }
    else {
        return "";
    }
}

/**
*
* @param <type> $ia
* @param <type> $show - true if should initially be visible
* @return <type>
*/
function validation_message($q,$show)
{
    $qinfo = LimeExpressionManager::GetQuestionStatus($q->id);
    $class = "questionhelp";
    if (!$show) {
        $class .= ' hide-tip';
    }
    $tip = '<span class="' . $class . '" id="vmsg_' . $q->id . '">' . $qinfo['validTip'] . "</span>";
    $isValid = $qinfo['valid'];
    return array($tip,$isValid);
    //    if (!$qinfo['valid']) {
    //        if (strlen($tip) == 0) {
    //            $help = $clang->gT('This question must be answered correctly');
    //        }
    //        else {
    //            $tip =' <span class="questionhelp">'.$tip.'</span>';
    //        }
    //        return '<br /><span class="errormandatory">'.$tip.'</span><br />';
    //    }
    //    else {
    //        return $tip;
    //    }
}

// TMSW Validation -> EM
function validation_popup($notvalidated=null)
{
    global $showpopups;

    $clang = Yii::app()->lang;
    //This sets the validation popup message to show if required
    //Called from question.php, group.php or survey.php
    if ($notvalidated === null) {unset($notvalidated);}
    $qtitle="";
    if (isset($notvalidated) && is_array($notvalidated) && isset($showpopups) && $showpopups == 1)  //ADD WARNINGS TO QUESTIONS IF THEY ARE NOT VALID
    {
        global $validationpopup, $vpopup;
        //POPUP WARNING
        if (!isset($validationpopup))
        {
            $vpopup="<script type=\"text/javascript\">\n
            <!--\n $(document).ready(function(){
            alert(\"".$clang->gT("One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid.", "js")."\");});\n //-->\n
            </script>\n";
            $validationpopup="Y";
        }
        return array($validationpopup, $vpopup);
    }
    else
    {
        return false;
    }
}

// TMSW Validation -> EM
function file_validation_popup($filenotvalidated = null)
{
    global $showpopups;

    $clang = Yii::app()->lang;
    if ($filenotvalidated === null) { unset($filenotvalidated); }
    if (isset($filenotvalidated) && is_array($filenotvalidated) && isset($showpopups) && $showpopups == 1)
    {
        global $filevalidationpopup, $fpopup;

        if (!isset($filevalidationpopup))
        {
            $fpopup="<script type=\"text/javascript\">\n
            <!--\n $(document).ready(function(){
            alert(\"".$clang->gT("One or more file have either exceeded the filesize/are not in the right format or the minimum number of required files have not been uploaded. You cannot proceed until these have been completed", "js")."\");});\n //-->\n
            </script>\n";
            $filevalidationpopup = "Y";
        }
        return array($filevalidationpopup, $fpopup);
    }
    else
        return false;
}

function return_timer_script($aQuestionAttributes, $q, $disable=null) {
    global $thissurvey;

    $clang = Yii::app()->lang;
    header_includes(Yii::app()->getConfig("generalscripts").'coookies.js', 'js');

    /* The following lines cover for previewing questions, because no $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldarray'] exists.
    This just stops error messages occuring */
    if(!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['questions']))
    {
        $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['questions'] = array();
    }
    /* End */

    if(isset($thissurvey['timercount']))
    {
        $thissurvey['timercount']++; //Used to count how many timer questions in a page, and ensure scripts only load once
    } else {
        $thissurvey['timercount']=1;
    }

    if($thissurvey['format'] != "S")
    {
        if($thissurvey['format'] != "G")
        {
            return "\n\n<!-- TIMER MODE DISABLED DUE TO INCORRECT SURVEY FORMAT -->\n\n";
            //We don't do the timer in any format other than question-by-question
        }
    }

    $time_limit=$aQuestionAttributes['time_limit'];

    $disable_next=trim($aQuestionAttributes['time_limit_disable_next']) != '' ? $aQuestionAttributes['time_limit_disable_next'] : 0;
    $disable_prev=trim($aQuestionAttributes['time_limit_disable_prev']) != '' ? $aQuestionAttributes['time_limit_disable_prev'] : 0;
    $time_limit_action=trim($aQuestionAttributes['time_limit_action']) != '' ? $aQuestionAttributes['time_limit_action'] : 1;
    $time_limit_message_delay=trim($aQuestionAttributes['time_limit_message_delay']) != '' ? $aQuestionAttributes['time_limit_message_delay']*1000 : 1000;
    $time_limit_message=trim($aQuestionAttributes['time_limit_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '' ? htmlspecialchars($aQuestionAttributes['time_limit_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']], ENT_QUOTES) : $clang->gT("Your time to answer this question has expired");
    $time_limit_warning=trim($aQuestionAttributes['time_limit_warning']) != '' ? $aQuestionAttributes['time_limit_warning'] : 0;
    $time_limit_warning_2=trim($aQuestionAttributes['time_limit_warning_2']) != '' ? $aQuestionAttributes['time_limit_warning_2'] : 0;
    $time_limit_countdown_message=trim($aQuestionAttributes['time_limit_countdown_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '' ? htmlspecialchars($aQuestionAttributes['time_limit_countdown_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']], ENT_QUOTES) : $clang->gT("Time remaining");
    $time_limit_warning_message=trim($aQuestionAttributes['time_limit_warning_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '' ? htmlspecialchars($aQuestionAttributes['time_limit_warning_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']], ENT_QUOTES) : $clang->gT("Your time to answer this question has nearly expired. You have {TIME} remaining.");
    $time_limit_warning_message=str_replace("{TIME}", "<div style='display: inline' id='LS_question".$q->id."_Warning'> </div>", $time_limit_warning_message);
    $time_limit_warning_display_time=trim($aQuestionAttributes['time_limit_warning_display_time']) != '' ? $aQuestionAttributes['time_limit_warning_display_time']+1 : 0;
    $time_limit_warning_2_message=trim($aQuestionAttributes['time_limit_warning_2_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '' ? htmlspecialchars($aQuestionAttributes['time_limit_warning_2_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']], ENT_QUOTES) : $clang->gT("Your time to answer this question has nearly expired. You have {TIME} remaining.");
    $time_limit_warning_2_message=str_replace("{TIME}", "<div style='display: inline' id='LS_question".$q->id."_Warning_2'> </div>", $time_limit_warning_2_message);
    $time_limit_warning_2_display_time=trim($aQuestionAttributes['time_limit_warning_2_display_time']) != '' ? $aQuestionAttributes['time_limit_warning_2_display_time']+1 : 0;
    $time_limit_message_style=trim($aQuestionAttributes['time_limit_message_style']) != '' ? $aQuestionAttributes['time_limit_message_style'] : "position: absolute;
    top: 10px;
    left: 35%;
    width: 30%;
    height: 60px;
    padding: 16px;
    border: 8px solid #555;
    background-color: white;
    z-index:1002;
    text-align: center;
    overflow: auto;";
    $time_limit_message_style.="\n		display: none;"; //Important to hide time limit message at start
    $time_limit_warning_style=trim($aQuestionAttributes['time_limit_warning_style']) != '' ? $aQuestionAttributes['time_limit_warning_style'] : "position: absolute;
    top: 10px;
    left: 35%;
    width: 30%;
    height: 60px;
    padding: 16px;
    border: 8px solid #555;
    background-color: white;
    z-index:1001;
    text-align: center;
    overflow: auto;";
    $time_limit_warning_style.="\n		display: none;"; //Important to hide time limit warning at the start
    $time_limit_warning_2_style=trim($aQuestionAttributes['time_limit_warning_2_style']) != '' ? $aQuestionAttributes['time_limit_warning_2_style'] : "position: absolute;
    top: 10px;
    left: 35%;
    width: 30%;
    height: 60px;
    padding: 16px;
    border: 8px solid #555;
    background-color: white;
    z-index:1001;
    text-align: center;
    overflow: auto;";
    $time_limit_warning_2_style.="\n		display: none;"; //Important to hide time limit warning at the start
    $time_limit_timer_style=trim($aQuestionAttributes['time_limit_timer_style']) != '' ? $aQuestionAttributes['time_limit_timer_style'] : "position: relative;
    width: 150px;
    margin-left: auto;
    margin-right: auto;
    border: 1px solid #111;
    text-align: center;
    background-color: #EEE;
    margin-bottom: 5px;
    font-size: 8pt;";
    $timersessionname="timer_question_".$q->id;
    if(isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$timersessionname])) {
        $time_limit=$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$timersessionname];
    }

    $output = "
    <input type='hidden' name='timerquestion' value='".$timersessionname."' />
    <input type='hidden' name='".$timersessionname."' id='".$timersessionname."' value='".$time_limit."' />\n";
    if($thissurvey['timercount'] < 2)
    {
        $output .="
        <script type='text/javascript'>
        <!--
        function freezeFrame(elementid) {
        if(document.getElementById(elementid) !== null) {
        var answer=document.getElementById(elementid);
        answer.blur();
        answer.onfocus=function() { answer.blur();};
        }
        };
        //-->
        </script>";
        $output .= "
        <script type='text/javascript'>
        <!--\n
        function countdown(questionid,timer,action,warning,warning2,warninghide,warning2hide,disable){
        if(!timeleft) { var timeleft=timer;}
        if(!warning) { var warning=0;}
        if(!warning2) { var warning2=0;}
        if(!warninghide) { var warninghide=0;}
        if(!warning2hide) { var warning2hide=0;}";

        if(isset($thissurvey['format']) && $thissurvey['format'] == "G")
        {
            global $gid;
            $qcount=0;
            foreach($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['questions'] as $ib)
            {
                if($ib->gid == $gid)
                {
                    $qcount++;
                }
            }
            //Override all other options and just allow freezing, survey is presented in group by group mode
            if($qcount > 1) {
                $output .="
                action = 3;";
            }
        }
        $output .="
        var timerdisplay='LS_question'+questionid+'_Timer';
        var warningtimedisplay='LS_question'+questionid+'_Warning';
        var warningdisplay='LS_question'+questionid+'_warning';
        var warning2timedisplay='LS_question'+questionid+'_Warning_2';
        var warning2display='LS_question'+questionid+'_warning_2';
        var expireddisplay='question'+questionid+'_timer';
        var timersessionname='timer_question_'+questionid;
        document.getElementById(timersessionname).value=timeleft;
        timeleft--;
        cookietimer=subcookiejar.fetch('limesurvey_timers',timersessionname);
        if(cookietimer) {
        if(cookietimer <= timeleft) {
        timeleft=cookietimer;
        }
        }
        var timeleftobject=new Object();
        subcookiejar.crumble('limesurvey_timers', timersessionname);
        timeleftobject[timersessionname]=timeleft;
        subcookiejar.bake('limesurvey_timers', timeleftobject, 7)\n";
        if($disable_next > 0) {
            $output .= "
            if(document.getElementById('movenextbtn') !== null && timeleft > $disable_next) {
            document.getElementById('movenextbtn').disabled=true;
            } else if (document.getElementById('movenextbtn') !== null && $disable_next > 1 && timeleft <= $disable_next) {
            document.getElementById('movenextbtn').disabled=false;
            }\n";
        }
        if($disable_prev > 0) {
            $output .= "
            if(document.getElementById('moveprevbtn') !== null && timeleft > $disable_prev) {
            document.getElementById('moveprevbtn').disabled=true;
            } else if (document.getElementById('moveprevbtn') !== null && $disable_prev > 1 && timeleft <= $disable_prev) {
            document.getElementById('moveprevbtn').disabled=false;
            }\n";
        }
        if(!is_numeric($disable_prev)) {
            $output .= "
            if(document.getElementById('moveprevbtn') !== null) {
            document.getElementById('moveprevbtn').disabled=true;
            }\n";
        }
        $output .="
        if(warning > 0 && timeleft<=warning) {
        var wsecs=warning%60;
        if(wsecs<10) wsecs='0' + wsecs;
        var WT1 = (warning - wsecs) / 60;
        var wmins = WT1 % 60; if (wmins < 10) wmins = '0' + wmins;
        var whours = (WT1 - wmins) / 60;
        var dmins=''
        var dhours=''
        var dsecs=''
        if (whours < 10) whours = '0' + whours;
        if (whours > 0) dhours = whours + ' ".$clang->gT('hours').", ';
        if (wmins > 0) dmins = wmins + ' ".$clang->gT('mins').", ';
        if (wsecs > 0) dsecs = wsecs + ' ".$clang->gT('seconds')."';
        if(document.getElementById(warningtimedisplay) !== null) {
        document.getElementById(warningtimedisplay).innerHTML = dhours+dmins+dsecs;
        }
        document.getElementById(warningdisplay).style.display='';
        }
        if(warning2 > 0 && timeleft<=warning2) {
        var w2secs=warning2%60;
        if(wsecs<10) w2secs='0' + wsecs;
        var W2T1 = (warning2 - w2secs) / 60;
        var w2mins = W2T1 % 60; if (w2mins < 10) w2mins = '0' + w2mins;
        var w2hours = (W2T1 - w2mins) / 60;
        var d2mins=''
        var d2hours=''
        var d2secs=''
        if (w2hours < 10) w2hours = '0' + w2hours;
        if (w2hours > 0) d2hours = w2hours + ' ".$clang->gT('hours').", ';
        if (w2mins > 0) d2mins = w2mins + ' ".$clang->gT('mins').", ';
        if (w2secs > 0) d2secs = w2secs + ' ".$clang->gT('seconds')."';
        if(document.getElementById(warning2timedisplay) !== null) {
        document.getElementById(warning2timedisplay).innerHTML = dhours+dmins+dsecs;
        }
        document.getElementById(warning2display).style.display='';
        }
        if(warning > 0 && warninghide > 0 && document.getElementById(warningdisplay).style.display != 'none') {
        if(warninghide == 1) {
        document.getElementById(warningdisplay).style.display='none';
        warning=0;
        }
        warninghide--;
        }
        if(warning2 > 0 && warning2hide > 0 && document.getElementById(warning2display).style.display != 'none') {
        if(warning2hide == 1) {
        document.getElementById(warning2display).style.display='none';
        warning2=0;
        }
        warning2hide--;
        }
        var secs = timeleft % 60;
        if (secs < 10) secs = '0'+secs;
        var T1 = (timeleft - secs) / 60;
        var mins = T1 % 60; if (mins < 10) mins = '0'+mins;
        var hours = (T1 - mins) / 60;
        if (hours < 10) hours = '0'+hours;
        var d2hours='';
        var d2mins='';
        var d2secs='';
        if (hours > 0) d2hours = hours+' ".$clang->gT('hours').": ';
        if (mins > 0) d2mins = mins+' ".$clang->gT('mins').": ';
        if (secs > 0) d2secs = secs+' ".$clang->gT('seconds')."';
        if (secs < 1) d2secs = '0 ".$clang->gT('seconds')."';
        document.getElementById(timerdisplay).innerHTML = '".$time_limit_countdown_message."<br />'+d2hours + d2mins + d2secs;
        if (timeleft>0){
        var text='countdown('+questionid+', '+timeleft+', '+action+', '+warning+', '+warning2+', '+warninghide+', '+warning2hide+', \"'+disable+'\")';
        setTimeout(text,1000);
        } else {
        //Countdown is finished, now do action
        switch(action) {
        case 2: //Just move on, no warning
        if(document.getElementById('movenextbtn') !== null) {
        if(document.getElementById('movenextbtn').disabled==true) document.getElementById('movenextbtn').disabled=false;
        }
        if(document.getElementById('moveprevbtn') !== null) {
        if(document.getElementById('moveprevbtn').disabled==true && '$disable_prev' > 0) document.getElementById('moveprevbtn').disabled=false;
        }
        freezeFrame(disable);
        subcookiejar.crumble('limesurvey_timers', timersessionname);
        if(document.getElementById('movenextbtn') != null) {
        document.limesurvey.submit();
        } else {
        setTimeout(\"document.limesurvey.submit();\", 1000);
        }
        break;
        case 3: //Just warn, don't move on
        document.getElementById(expireddisplay).style.display='';
        if(document.getElementById('movenextbtn') !== null) {
        if(document.getElementById('movenextbtn').disabled==true) document.getElementById('movenextbtn').disabled=false;
        }
        if(document.getElementById('moveprevbtn') !== null) {
        if(document.getElementById('moveprevbtn').disabled==true && '$disable_prev' > 0) document.getElementById('moveprevbtn').disabled=false;
        }
        freezeFrame(disable);
        this.onsubmit=function() { subcookiejar.crumble('limesurvey_timers', timersessionname);};
        break;
        default: //Warn and move on
        document.getElementById(expireddisplay).style.display='';
        if(document.getElementById('movenextbtn') !== null) {
        if(document.getElementById('movenextbtn').disabled==true) document.getElementById('movenextbtn').disabled=false;
        }
        if(document.getElementById('moveprevbtn') !== null) {
        if(document.getElementById('moveprevbtn').disabled==true && '$disable_prev' > 0) document.getElementById('moveprevbtn').disabled=false;
        }
        freezeFrame(disable);
        subcookiejar.crumble('limesurvey_timers', timersessionname);
        setTimeout('document.limesurvey.submit()', ".$time_limit_message_delay.");
        break;
        }
        }
        }
        //-->
        </script>";
    }
    $output .= "<div id='question".$q->id."_timer' style='".$time_limit_message_style."'>".$time_limit_message."</div>\n\n";

    $output .= "<div id='LS_question".$q->id."_warning' style='".$time_limit_warning_style."'>".$time_limit_warning_message."</div>\n\n";
    $output .= "<div id='LS_question".$q->id."_warning_2' style='".$time_limit_warning_2_style."'>".$time_limit_warning_2_message."</div>\n\n";
    $output .= "<div id='LS_question".$q->id."_Timer' style='".$time_limit_timer_style."'></div>\n\n";
    //Call the countdown script
    $output .= "<script type='text/javascript'>
    $(document).ready(function() {
    countdown(".$q->id.", ".$time_limit.", ".$time_limit_action.", ".$time_limit_warning.", ".$time_limit_warning_2.", ".$time_limit_warning_display_time.", ".$time_limit_warning_2_display_time.", '".$disable."');
    });
    </script>\n\n";
    return $output;
}

function return_array_filter_strings($q, $aQuestionAttributes, $thissurvey, $ansrow, $rowname, $trbc='', $valuename, $method="tbody", $class=null) {
    $htmltbody2 = "\n\n\t<$method id='javatbd$rowname'";
    $htmltbody2 .= ($class !== null) ? " class='$class'": "";
    if (isset($_SESSION['relevanceStatus'][$rowname]) && !$_SESSION['relevanceStatus'][$rowname])
    {
        // If using exclude_all_others, then need to know whether irrelevant rows should be hidden or disabled
        if (isset($aQuestionAttributes['exclude_all_others']))
        {
            $disableit=false;
            foreach(explode(';',trim($aQuestionAttributes['exclude_all_others'])) as $eo)
            {
                $eorow = $q->fieldname . $eo;
                if ((!isset($_SESSION['relevanceStatus'][$eorow]) || $_SESSION['relevanceStatus'][$eorow])
                    && (isset($_SESSION[$eorow]) && $_SESSION[$eorow] == "Y"))
                {
                    $disableit = true;
                }
            }
            if ($disableit)
            {
                $htmltbody2 .= " disabled='disabled'";
            }
            else
            {
                if (!isset($aQuestionAttributes['array_filter_style']) || $aQuestionAttributes['array_filter_style'] == '0')
                {
                $htmltbody2 .= " style='display: none'";
            }
                else
                {
                    $htmltbody2 .= " disabled='disabled'";
        }
            }
        }
        else
        {
            if (!isset($aQuestionAttributes['array_filter_style']) || $aQuestionAttributes['array_filter_style'] == '0')
            {
            $htmltbody2 .= " style='display: none'";
        }
            else
            {
                $htmltbody2 .= " disabled='disabled'";
    }
        }
    }
    $htmltbody2 .= ">\n";
    return array($htmltbody2, "");
}

// ==================================================================
// setting constants for 'checked' and 'selected' inputs
define('CHECKED' , ' checked="checked"' , true);
define('SELECTED' , ' selected="selected"' , true);

function getLatLongFromIp($ip){

    $ipInfoDbAPIKey = Yii::app()->getConfig("ipInfoDbAPIKey");

    $xml = simplexml_load_file("http://api.ipinfodb.com/v2/ip_query.php?key=$ipInfoDbAPIKey&ip=$ip&timezone=false");
    if ($xml->{'Status'} == "OK"){
        $lat = (float)$xml->{'Latitude'};
        $lng = (float)$xml->{'Longitude'};

        return(array($lat,$lng));
    }
    else
        return false;
}
