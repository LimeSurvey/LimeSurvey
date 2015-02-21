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
*/

/*
* Let's explain what this strange $ia var means
*
* The $ia string comes from the $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['insertarray'] variable which is built at the commencement of the survey.
* See index.php, function "buildsurveysession()"
* One $ia array exists for every question in the survey. The $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['insertarray']
* string is an array of $ia arrays.
*
* $ia[0] => question id
* $ia[1] => fieldname
* $ia[2] => title
* $ia[3] => question text
* $ia[4] => type --  text, radio, select, array, etc
* $ia[5] => group id
* $ia[6] => mandatory Y || N
* $ia[7] => conditions exist for this question
* $ia[8] => other questions have conditions which rely on this question (including array_filter and array_filter_exclude attributes)
* $ia[9] => incremental question count (used by {QUESTION_NUMBER})
*
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
    if (getGlobalSetting('shownoanswer') == 1)
    {
        define('SHOW_NO_ANSWER', 1);
    }
    elseif (getGlobalSetting('shownoanswer') == 0)
    {
        define('SHOW_NO_ANSWER', 0);
    }
    elseif ($thissurvey['shownoanswer'] == 'N')
    {
        define('SHOW_NO_ANSWER', 0);
    }
    else
    {
        define('SHOW_NO_ANSWER', 1);
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
function retrieveAnswers($ia)
{
    //globalise required config variables
    global $thissurvey; //These are set by index.php

    //$clang = Yii::app()->lang;
    $clang = Yii::app()->lang;

    //DISPLAY
    $display = $ia[7];

    //QUESTION NAME
    $name = $ia[0];

    $qtitle=$ia[3];
    $inputnames=array();

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    //Create the question/answer html
    $answer = "";
    // Previously in limesurvey, it was virtually impossible to control how the start of questions were formatted.
    // this is an attempt to allow users (or rather system admins) some control over how the starting text is formatted.
    $number = isset($ia[9]) ? $ia[9] : '';

    // TMSW - populate this directly from LEM? - this this is global
    $question_text = array(
    'all' => '' // All has been added for backwards compatibility with templates that use question_start.pstpl (now redundant)
    ,'text' => $qtitle
    ,'code' => $ia[2]
    ,'number' => $number
    ,'help' => ''
    ,'mandatory' => ''
    ,'man_message' => ''
    ,'valid_message' => ''
    ,'file_valid_message' => ''
    ,'class' => ''
    ,'man_class' => ''
    ,'input_error_class' => ''// provides a class.
    ,'essentials' => ''
    );

    switch ($ia[4])
    {
        case 'X': //BOILERPLATE QUESTION
            $values = do_boilerplate($ia);
            break;
        case '5': //5 POINT CHOICE radio-buttons
            $values = do_5pointchoice($ia);
            break;
        case 'D': //DATE
            $values = do_date($ia);
            // if a drop box style date was answered incompletely (dropbox), print an error/help message
            if (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['maxstep']) ||
                ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['prevstep']))
            {
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['qattribute_answer'.$ia[1]]))
                $question_text['help'] = '<span class="error">'.$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['qattribute_answer'.$ia[1]].'</span>';
            }
            break;
        case 'L': //LIST drop-down/radio-button list
            $values = do_list_radio($ia);
            if ($aQuestionAttributes['hide_tip']==0)
            {
                $qtitle .= "<br />\n<span class=\"questionhelp\">"
                . $clang->gT('Choose one of the following answers').'</span>';
                $question_text['help'] = $clang->gT('Choose one of the following answers');
            }
            break;
        case '!': //List - dropdown
            $values=do_list_dropdown($ia);
            if ($aQuestionAttributes['hide_tip']==0)
            {
                $qtitle .= "<br />\n<span class=\"questionhelp\">"
                . $clang->gT('Choose one of the following answers').'</span>';
                $question_text['help'] = $clang->gT('Choose one of the following answers');
            }
            break;
        case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
            $values=do_listwithcomment($ia);
            if (count($values[1]) > 1 && $aQuestionAttributes['hide_tip']==0)
            {
                $qtitle .= "<br />\n<span class=\"questionhelp\">"
                . $clang->gT('Choose one of the following answers').'</span>';
                $question_text['help'] = $clang->gT('Choose one of the following answers');
            }
            break;
        case 'R': //RANKING STYLE
            $values=do_ranking($ia);
            break;
        case 'M': //Multiple choice checkbox
            $values=do_multiplechoice($ia);
            if (count($values[1]) > 1 && $aQuestionAttributes['hide_tip']==0)
            {
                $maxansw=trim($aQuestionAttributes['max_answers']);
                $minansw=trim($aQuestionAttributes['min_answers']);
                if (!($maxansw || $minansw))
                {
                    $qtitle .= "<br />\n<span class=\"questionhelp\">"
                    . $clang->gT('Check any that apply').'</span>';
                    $question_text['help'] = $clang->gT('Check any that apply');
                }
                //                else
                //                {
                //                    if ($maxansw && $minansw)
                //                    {
                //                        $qtitle .= "<br />\n<span class=\"questionhelp\">"
                //                        . sprintf($clang->gT("Check between %d and %d answers"), $minansw, $maxansw)."</span>";
                //                        $question_text['help'] = sprintf($clang->gT("Check between %d and %d answers"), $minansw, $maxansw);
                //                    } elseif ($maxansw)
                //                    {
                //                        $qtitle .= "<br />\n<span class=\"questionhelp\">"
                //                        . sprintf($clang->gT("Check at most %d answers"), $maxansw)."</span>";
                //                        $question_text['help'] = sprintf($clang->gT("Check at most %d answers"), $maxansw);
                //                    } else
                //                    {
                //                        $qtitle .= "<br />\n<span class=\"questionhelp\">"
                //                        . sprintf($clang->ngT("Check at least %d answer","Check at least %d answers",$minansw),$minansw)."</span>";
                //                        $question_text['help'] = sprintf($clang->ngT("Check at least %d answer","Check at least %d answers",$minansw),$minansw);
                //                    }
                //                }
            }
            break;

        case 'I': //Language Question
            $values=do_language($ia);
            if (count($values[1]) > 1)
            {
                $qtitle .= "<br />\n<span class=\"questionhelp\">"
                . $clang->gT('Choose your language').'</span>';
                $question_text['help'] = $clang->gT('Choose your language');
            }
            break;
        case 'P': //Multiple choice with comments checkbox + text
            $values=do_multiplechoice_withcomments($ia);
            if (count($values[1]) > 1 && $aQuestionAttributes['hide_tip']==0)
            {
                $maxansw=trim($aQuestionAttributes["max_answers"]);
                $minansw=trim($aQuestionAttributes["min_answers"]);
                if (!($maxansw || $minansw))
                {
                    $qtitle .= "<br />\n<span class=\"questionhelp\">"
                    . $clang->gT('Check any that apply').'</span>';
                    $question_text['help'] = $clang->gT('Check any that apply');
                }
            }
            break;
        case '|': //File Upload
            $values=do_file_upload($ia);
            break;
        case 'Q': //MULTIPLE SHORT TEXT
            $values=do_multipleshorttext($ia);
            break;
        case 'K': //MULTIPLE NUMERICAL QUESTION
            $values=do_multiplenumeric($ia);
            break;
        case 'N': //NUMERICAL QUESTION TYPE
            $values=do_numerical($ia);
            break;
        case 'S': //SHORT FREE TEXT
            $values=do_shortfreetext($ia);
            break;
        case 'T': //LONG FREE TEXT
            $values=do_longfreetext($ia);
            break;
        case 'U': //HUGE FREE TEXT
            $values=do_hugefreetext($ia);
            break;
        case 'Y': //YES/NO radio-buttons
            $values=do_yesno($ia);
            break;
        case 'G': //GENDER drop-down list
            $values=do_gender($ia);
            break;
        case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
            $values=do_array_5point($ia);
            break;
        case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
            $values=do_array_10point($ia);
            break;
        case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
            $values=do_array_yesnouncertain($ia);
            break;
        case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
            $values=do_array_increasesamedecrease($ia);
            break;
        case 'F': //ARRAY (Flexible) - Row Format
            $values=do_array($ia);
            break;
        case 'H': //ARRAY (Flexible) - Column Format
            $values=do_arraycolumns($ia);
            break;
        case ':': //ARRAY (Multi Flexi) 1 to 10
            $values=do_array_multiflexi($ia);
            break;
        case ';': //ARRAY (Multi Flexi) Text
            $values=do_array_multitext($ia);  //It's like the "5th element" movie, come to life
            break;
        case '1': //Array (Flexible Labels) dual scale
            $values=do_array_dual($ia);
            break;
        case '*': // Equation
            $values=do_equation($ia);
            break;
    } //End Switch

    if (isset($values)) //Break apart $values array returned from switch
    {
        //$answer is the html code to be printed
        //$inputnames is an array containing the names of each input field
        list($answer, $inputnames)=$values;
    }

    if ($ia[6] == 'Y')
    {
        $qtitle = '<span class="asterisk">'.$clang->gT('*').'</span>'.$qtitle;
        $question_text['mandatory'] = $clang->gT('*');
    }
    //If this question is mandatory but wasn't answered in the last page
    //add a message HIGHLIGHTING the question
    if (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['prevstep'])) {
        $mandatory_msg = mandatory_message($ia);
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
    list($validation_msg,$isValid) = validation_message($ia,$_vshow);

    $qtitle .= $validation_msg;
    $question_text['valid_message'] = $validation_msg;

    if (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['prevstep'])) {
        $file_validation_msg = file_validation_message($ia);
    }
    else {
        $file_validation_msg = '';
        $isValid = true;    // don't want to show any validation messages.
    }
    $qtitle .= $ia[4] == "|" ? $file_validation_msg : "";
    $question_text['file_valid_message'] = $ia[4] == "|" ? $file_validation_msg : "";

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

    $qanda=array($qtitle, $answer, 'help', $display, $name, $ia[2], $ia[5], $ia[1] );
    //New Return
    return array($qanda, $inputnames);
}

function mandatory_message($ia)
{
    $qinfo = LimeExpressionManager::GetQuestionStatus($ia[0]);
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
function validation_message($ia,$show)
{
    $qinfo = LimeExpressionManager::GetQuestionStatus($ia[0]);
    $class = "questionhelp";
    if (!$show) {
        $class .= ' hide-tip';
    }
    $tip = '<span class="' . $class . '" id="vmsg_' . $ia[0] . '">' . $qinfo['validTip'] . "</span>";
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
function file_validation_message($ia)
{
    global $filenotvalidated;

    $clang = Yii::app()->lang;
    $qtitle = "";
    if (isset($filenotvalidated) && is_array($filenotvalidated) && $ia[4] == "|")
    {
        global $filevalidationpopup, $popup;

        foreach ($filenotvalidated as $k => $v)
        {
            if ($ia[1] == $k || strpos($k, "_") && $ia[1] == substr(0, strpos($k, "_") - 1));
            $qtitle .= '<br /><span class="errormandatory">'.$clang->gT($filenotvalidated[$k]).'</span><br />';
        }
    }
    return $qtitle;
}

// TMSW Validation -> EM
function mandatory_popup($ia, $notanswered=null)
{
    $clang = Yii::app()->lang;
    //This sets the mandatory popup message to show if required
    //Called from question.php, group.php or survey.php
    if ($notanswered === null) {unset($notanswered);}
    if (isset($notanswered) && is_array($notanswered)) //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
    {
        global $mandatorypopup, $popup;
        //POPUP WARNING
        if (!isset($mandatorypopup) && ($ia[4] == 'T' || $ia[4] == 'S' || $ia[4] == 'U'))
        {
            $popup=$clang->gT("You cannot proceed until you enter some text for one or more questions.");
            $mandatorypopup="Y";
        }else
        {
            $popup=$clang->gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.");
            $mandatorypopup="Y";
        }
        return array($mandatorypopup, $popup);
    }
    else
    {
        return false;
    }
}

// TMSW Validation -> EM
function validation_popup($ia, $notvalidated=null)
{
    $clang = Yii::app()->lang;
    //This sets the validation popup message to show if required
    //Called from question.php, group.php or survey.php
    if ($notvalidated === null) {unset($notvalidated);}
    $qtitle="";
    if (isset($notvalidated) && is_array($notvalidated) )  //ADD WARNINGS TO QUESTIONS IF THEY ARE NOT VALID
    {
        global $validationpopup, $vpopup;
        //POPUP WARNING
        if (!isset($validationpopup))
        {
            $vpopup=$clang->gT("One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid.");
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
function file_validation_popup($ia, $filenotvalidated = null)
{
    $clang = Yii::app()->lang;
    if ($filenotvalidated === null) { unset($filenotvalidated); }
    if (isset($filenotvalidated) && is_array($filenotvalidated))
    {
        global $filevalidationpopup, $fpopup;

        if (!isset($filevalidationpopup))
        {
            $fpopup=$clang->gT("One or more file have either exceeded the filesize/are not in the right format or the minimum number of required files have not been uploaded. You cannot proceed until these have been completed");
            $filevalidationpopup = "Y";
        }
        return array($filevalidationpopup, $fpopup);
    }
    else
        return false;
}

function return_timer_script($aQuestionAttributes, $ia, $disable=null) {
    global $thissurvey;

    $clang = Yii::app()->lang;
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts").'coookies.js');

    /* The following lines cover for previewing questions, because no $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldarray'] exists.
    This just stops error messages occuring */
    if(!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldarray']))
    {
        $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldarray'] = array();
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
    $time_limit_warning_message=str_replace("{TIME}", "<div style='display: inline' id='LS_question".$ia[0]."_Warning'> </div>", $time_limit_warning_message);
    $time_limit_warning_display_time=trim($aQuestionAttributes['time_limit_warning_display_time']) != '' ? $aQuestionAttributes['time_limit_warning_display_time']+1 : 0;
    $time_limit_warning_2_message=trim($aQuestionAttributes['time_limit_warning_2_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '' ? htmlspecialchars($aQuestionAttributes['time_limit_warning_2_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']], ENT_QUOTES) : $clang->gT("Your time to answer this question has nearly expired. You have {TIME} remaining.");
    $time_limit_warning_2_message=str_replace("{TIME}", "<div style='display: inline' id='LS_question".$ia[0]."_Warning_2'> </div>", $time_limit_warning_2_message);
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
    $time_limit_message_style.="\n        display: none;"; //Important to hide time limit message at start
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
    $time_limit_warning_style.="\n        display: none;"; //Important to hide time limit warning at the start
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
    $time_limit_warning_2_style.="\n        display: none;"; //Important to hide time limit warning at the start
    $time_limit_timer_style=trim($aQuestionAttributes['time_limit_timer_style']) != '' ? $aQuestionAttributes['time_limit_timer_style'] : "position: relative;
    width: 150px;
    margin-left: auto;
    margin-right: auto;
    border: 1px solid #111;
    text-align: center;
    background-color: #EEE;
    margin-bottom: 5px;
    font-size: 8pt;";
    $timersessionname="timer_question_".$ia[0];
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
            $('#'+elementid).prop('readonly',true);
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
            foreach($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldarray'] as $ib)
            {
                if($ib[5] == $gid)
                {
                    $qcount++;
                }
            }
            // Override all other options and just allow freezing, survey is presented in group by group mode
            // Why don't allow submit in Group by group mode, this surely broke 'mandatory' question, but this remove a great system for user (Denis 140224)
            if($qcount > 1) {
                $output .="
                action = 3;";
            }
        }

        /* If this is a preview, don't allow the page to submit/reload */
        $thisaction=returnglobal('action');
        if($thisaction == "previewquestion" || $thisaction == "previewgroup") {
            $output .="
            action = 3;";
        }

        $output .="
        var timerdisplay='LS_question'+questionid+'_Timer';
        var warningtimedisplay='LS_question'+questionid+'_Warning';
        var warningdisplay='LS_question'+questionid+'_warning';
        var warning2timedisplay='LS_question'+questionid+'_Warning_2';
        var warning2display='LS_question'+questionid+'_warning_2';
        var expireddisplay='question'+questionid+'_timer';
        var timersessionname='timer_question_'+questionid;
        $('#'+timersessionname).val(timeleft);
        timeleft--;
        cookietimer=subcookiejar.fetch('limesurvey_timers',timersessionname);
        if(cookietimer && cookietimer <= timeleft) {
            timeleft=cookietimer;
        }
        var timeleftobject=new Object();
        subcookiejar.crumble('limesurvey_timers', timersessionname);
        timeleftobject[timersessionname]=timeleft;
        subcookiejar.bake('limesurvey_timers', timeleftobject, 7)\n";
        if($disable_next > 0) {// $disable_next can be 1 or 0 (it's a select).
            $output .= "
            if(timeleft > $disable_next) {
            $('#movenextbtn').prop('disabled',true);$('#movenextbtn.ui-button').button( 'option', 'disabled', true );
            } else if ($disable_next >= 1 && timeleft <= $disable_next) {
            $('#movenextbtn').prop('disabled',false);$('#movenextbtn.ui-button').button( 'option', 'disabled', false );
            }\n";
        }
        if($disable_prev > 0) {
            $output .= "
            if(timeleft > $disable_prev) {
            $('#moveprevbtn').prop('disabled',true);$('#moveprevbtn.ui-button').button( 'option', 'disabled', true );
            } else if ($disable_prev >= 1 && timeleft <= $disable_prev) {
            $('#moveprevbtn').prop('disabled',false);$('#moveprevbtn.ui-button').button( 'option', 'disabled', false );
            }\n";
        }
        if(!is_numeric($disable_prev) && false) {
            $output .= "
            $('#moveprevbtn').prop('disabled',true);$('#moveprevbtn.ui-button').button( 'option', 'disabled', true );
            ";
        }
        $output .="
        if(warning > 0 && timeleft<=warning) {
            var wsecs=warning%60;
            if(wsecs<10) wsecs='0' + wsecs;
            var WT1 = (warning - wsecs) / 60;
            var wmins = WT1 % 60; if (wmins < 10) wmins = '0' + wmins;
            var whours = (WT1 - wmins) / 60;
            var dmins='';
            var dhours='';
            var dsecs='';
            if (whours < 10) whours = '0' + whours;
            if (whours > 0) dhours = whours + ' ".$clang->gT('hours').", ';
            if (wmins > 0) dmins = wmins + ' ".$clang->gT('mins').", ';
            if (wsecs > 0) dsecs = wsecs + ' ".$clang->gT('seconds')."';
            $('#'+warningtimedisplay).html(dhours+dmins+dsecs);
            $('#'+warningdisplay).show();
            if(warninghide > 0 ) {
                setTimeout(function(){ $('#'+warningdisplay).hide(); },warninghide*1000);
            }
            warning=0;
        }
        if(warning2 > 0 && timeleft<=warning2) {
            var w2secs=warning2%60;
            if(wsecs<10) w2secs='0' + wsecs;
            var W2T1 = (warning2 - w2secs) / 60;
            var w2mins = W2T1 % 60; if (w2mins < 10) w2mins = '0' + w2mins;
            var w2hours = (W2T1 - w2mins) / 60;
            var d2mins='';
            var d2hours='';
            var d2secs='';
            if (w2hours < 10) w2hours = '0' + w2hours;
            if (w2hours > 0) d2hours = w2hours + ' ".$clang->gT('hours').", ';
            if (w2mins > 0) d2mins = w2mins + ' ".$clang->gT('mins').", ';
            if (w2secs > 0) d2secs = w2secs + ' ".$clang->gT('seconds')."';
            $('#'+warning2timedisplay).html(dhours+dmins+dsecs);
            $('#'+warning2display).show();
            if(warning2hide > 0 ) {
                setTimeout(function(){ $('#'+warning2display).hide(); },warning2hide*1000);
            }
            warning2=0;
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
        $('#'+timerdisplay).html('".$time_limit_countdown_message."<br />'+d2hours + d2mins + d2secs);
        if (timeleft>0){
            var text='countdown('+questionid+', '+timeleft+', '+action+', '+warning+', '+warning2+', '+warninghide+', '+warning2hide+', \"'+disable+'\")';
            setTimeout(text,1000);
        } else {
            //Countdown is finished, now do action
            switch(action) {
                case 2: //Just move on, no warning
                    $('#movenextbtn').prop('disabled',false);$('#movenextbtn.ui-button').button( 'option', 'disabled', false );
                    $('#moveprevbtn').prop('disabled',false);$('#moveprevbtn.ui-button').button( 'option', 'disabled', false );
                    freezeFrame(disable);
                    subcookiejar.crumble('limesurvey_timers', timersessionname);
                    $('#defaultbtn').click();
                    break;
                case 3: //Just warn, don't move on
                    $('#'+expireddisplay).show();
                    $('#movenextbtn').prop('disabled',false);$('#movenextbtn.ui-button').button( 'option', 'disabled', false );
                    $('#moveprevbtn').prop('disabled',false);$('#moveprevbtn.ui-button').button( 'option', 'disabled', false );
                    freezeFrame(disable);
                    $('#limesurvey').submit(function(){ subcookiejar.crumble('limesurvey_timers', timersessionname); });
                    break;
                default: //Warn and move on
                    $('#'+expireddisplay).show();
                    $('#movenextbtn').prop('disabled',false);$('#movenextbtn.ui-button').button( 'option', 'disabled', false );
                    $('#moveprevbtn').prop('disabled',false);$('#moveprevbtn.ui-button').button( 'option', 'disabled', false );
                    freezeFrame(disable);
                    subcookiejar.crumble('limesurvey_timers', timersessionname);
                    setTimeout($('#defaultbtn').click(), ".$time_limit_message_delay.");
                    break;
            }
        }
        }
        //-->
        </script>";
    }
    $output .= "<div id='question".$ia[0]."_timer' style='".$time_limit_message_style."'>".$time_limit_message."</div>\n\n";

    $output .= "<div id='LS_question".$ia[0]."_warning' style='".$time_limit_warning_style."'>".$time_limit_warning_message."</div>\n\n";
    $output .= "<div id='LS_question".$ia[0]."_warning_2' style='".$time_limit_warning_2_style."'>".$time_limit_warning_2_message."</div>\n\n";
    $output .= "<div id='LS_question".$ia[0]."_Timer' style='".$time_limit_timer_style."'></div>\n\n";
    //Call the countdown script
    $output .= "<script type='text/javascript'>
    $(document).ready(function() {
    countdown(".$ia[0].", ".$time_limit.", ".$time_limit_action.", ".$time_limit_warning.", ".$time_limit_warning_2.", ".$time_limit_warning_display_time.", ".$time_limit_warning_2_display_time.", '".$disable."');
    });
    </script>\n\n";
    return $output;
}

function return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $rowname, $trbc='', $valuename, $method="tbody", $class=null) {
    $htmltbody2 = "\n\n\t<$method id='javatbd$rowname'";
    $htmltbody2 .= ($class !== null) ? " class='$class'": "";
    $surveyid=$thissurvey['sid'];
    if (isset($_SESSION["survey_{$surveyid}"]['relevanceStatus'][$rowname]) && !$_SESSION["survey_{$surveyid}"]['relevanceStatus'][$rowname])
    {
        // If using exclude_all_others, then need to know whether irrelevant rows should be hidden or disabled
        if (isset($aQuestionAttributes['exclude_all_others']))
        {
            $disableit=false;
            foreach(explode(';',trim($aQuestionAttributes['exclude_all_others'])) as $eo)
            {
                $eorow = $ia[1] . $eo;
                if ((!isset($_SESSION["survey_{$surveyid}"]['relevanceStatus'][$eorow]) || $_SESSION["survey_{$surveyid}"]['relevanceStatus'][$eorow])
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

// ==================================================================
// QUESTION METHODS =================================================

function do_boilerplate($ia)
{
    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    $answer='';

    if (trim($aQuestionAttributes['time_limit'])!='')
    {
        $answer .= return_timer_script($aQuestionAttributes, $ia);
    }

    $answer .= '<input type="hidden" name="'.$ia[1].'" id="answer'.$ia[1].'" value="" />';
    $inputnames[]=$ia[1];

    return array($answer, $inputnames);
}

function do_equation($ia)
{
    $answer='<input type="hidden" name="'.$ia[1].'" id="java'.$ia[1].'" value="';
    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]))
    {
        $answer .= htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]],ENT_QUOTES);
    }
    $answer .= '">';
    $inputnames[]=$ia[1];
    $mandatory=null;

    return array($answer, $inputnames, $mandatory);
}

// ---------------------------------------------------------------
function do_5pointchoice($ia)
{
    $clang=Yii::app()->lang;
    $imageurl = Yii::app()->getConfig("imageurl");
    $checkconditionFunction = "checkconditions";
    $aQuestionAttributes=  getQuestionAttributeValues($ia[0], $ia[4]);
    $id = 'slider'.time().rand(0,100);
    $answer = "\n<ul id=\"{$id}\" class=\"answers-list radio-list\">\n";
    for ($fp=1; $fp<=5; $fp++)
    {
        $answer .= "\t<li class=\"answer-item radio-item\">\n<input class=\"radio\" type=\"radio\" name=\"$ia[1]\" id=\"answer$ia[1]$fp\" value=\"$fp\"";
        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $fp)
        {
            $answer .= CHECKED;
        }
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer$ia[1]$fp\" class=\"answertext\">$fp</label>\n\t</li>\n";
    }
    if ($ia[6] != "Y"  && SHOW_NO_ANSWER == 1) // Add "No Answer" option if question is not mandatory
    {
        $answer .= "\t<li class=\"answer-item radio-item noanswer-item\">\n<input class=\"radio\" type=\"radio\" name=\"$ia[1]\" id=\"answer".$ia[1]."NANS\" value=\"\"";
        if (!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]])
        {
            $answer .= CHECKED;
        }
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer".$ia[1]."NANS\" class=\"answertext\">".$clang->gT('No answer')."</label>\n\t</li>\n";

    }
    $answer .= "</ul>\n<input type=\"hidden\" name=\"java$ia[1]\" id=\"java$ia[1]\" value=\"".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]."\" />\n";
    $inputnames[]=$ia[1];

    if($aQuestionAttributes['slider_rating']==1){
        Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'star-rating.css');
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."star-rating.js");
        $answer .= "<script type='text/javascript'>\n"
        . "  <!--\n"
        ." doRatingStar({$ia[0]});\n"
        ." -->\n"
        ."</script>\n";
    }

    if($aQuestionAttributes['slider_rating']==2){
        Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'slider-rating.css');
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."slider-rating.js");
        $answer .= "<script type='text/javascript'>\n"
        . " <!--\n"
        ." doRatingSlider({$ia[0]});\n"
        ." -->\n"
        ."</script>\n";
    }
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_date($ia)
{
    global $thissurvey;
    $clang=Yii::app()->lang;

    $aQuestionAttributes=getQuestionAttributeValues($ia[0],$ia[4]);
    $sDateLangvarJS=" translt = {
         alertInvalidDate: '" . $clang->gT('Date entered is invalid!','js') . "',
         infoCompleteAll: '" . $clang->gT('Please complete all parts of the date!','js') . "'
        };";
    App()->getClientScript()->registerScript("sDateLangvarJS",$sDateLangvarJS,CClientScript::POS_HEAD);
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts").'date.js');
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig("third_party").'/jstoolbox/date.js');
    $checkconditionFunction = "checkconditions";

    $dateformatdetails = getDateFormatDataForQID($aQuestionAttributes,$thissurvey);
    $numberformatdatat = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sMindatetailor='';
    $sMaxdatetailor='';

    // date_min: Determine whether we have an expression, a full date (YYYY-MM-DD) or only a year(YYYY)
    if (trim($aQuestionAttributes['date_min'])!='')
    {
        $date_min=$aQuestionAttributes['date_min'];
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/",$date_min))
        {
            $mindate=$date_min;
        }
        elseif ((strlen($date_min)==4) && ($date_min>=1900) && ($date_min<=2099))
        {
            // backward compatibility: if only a year is given, add month and day
            $mindate=$date_min.'-01-01';
        }
        else
        {
            $mindate='{'.$aQuestionAttributes['date_min'].'}';
            // get the LEMtailor ID, remove the span tags
            $sMindatespan=LimeExpressionManager::ProcessString($mindate, $ia[0],NULL, false, 1, 1);
            preg_match("/LEMtailor_Q_[0-9]{1,7}_[0-9]{1,3}/", $sMindatespan, $matches);
            if (isset($matches[0]))
                $sMindatetailor=$matches[0];
        }
    }
    else
    {
        $mindate='1900-01-01';
    }

    // date_max: Determine whether we have an expression, a full date (YYYY-MM-DD) or only a year(YYYY)
    if (trim($aQuestionAttributes['date_max'])!='')
    {
        $date_max=$aQuestionAttributes['date_max'];
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/",$date_max))
        {
            $maxdate=$date_max;
        }
        elseif ((strlen($date_max)==4) && ($date_max>=1900) && ($date_max<=2099))
        {
            // backward compatibility: if only a year is given, add month and day
            $maxdate=$date_max.'-12-31';
        }
        else
        {
            $maxdate='{'.$aQuestionAttributes['date_max'].'}';
            // get the LEMtailor ID, remove the span tags
            $sMaxdatespan=LimeExpressionManager::ProcessString($maxdate, $ia[0],NULL, false, 1, 1);
            preg_match("/LEMtailor_Q_[0-9]{1,7}_[0-9]{1,3}/", $sMaxdatespan, $matches);
            if (isset($matches[0]))
                $sMaxdatetailor=$matches[0];
        }
    }
    else
    {
        $maxdate='2037-12-31';
    }

    if (trim($aQuestionAttributes['dropdown_dates'])==1) {
        if (!empty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) &
           ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]!='INVALID'))
        {
            $datetimeobj = new Date_Time_Converter($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]], "Y-m-d H:i:s");
            $currentyear = $datetimeobj->years;
            $currentmonth = $datetimeobj->months;
            $currentdate = $datetimeobj->days;
            $currenthour = $datetimeobj->hours;
            $currentminute = $datetimeobj->minutes;
        } else {
            $currentdate='';
            $currentmonth='';
            $currentyear='';
            $currenthour = '';
            $currentminute = '';
        }

        $dateorder = preg_split('/([-\.\/ :])/', $dateformatdetails['phpdate'],-1,PREG_SPLIT_DELIM_CAPTURE );
        $answer='<p class="question answer-item dropdown-item date-item">';
        foreach($dateorder as $datepart)
        {
            switch($datepart)
            {
                // Show day select box
                case 'j':
                case 'd':   $answer .= '<label for="day'.$ia[1].'" class="hide">'.$clang->gT('Day').'</label><select id="day'.$ia[1].'" name="day'.$ia[1].'" class="day">
                    <option value="">'.$clang->gT('Day')."</option>\n";
                    for ($i=1; $i<=31; $i++) {
                        if ($i == $currentdate)
                        {
                            $i_date_selected = SELECTED;
                        }
                        else
                        {
                            $i_date_selected = '';
                        }
                        $answer .= '<option value="'.sprintf('%02d', $i).'"'.$i_date_selected.'>'.sprintf('%02d', $i)."</option>\n";
                    }
                    $answer .='</select>';
                    break;
                    // Show month select box
                case 'n':
                case 'm':   $answer .= '<label for="month'.$ia[1].'" class="hide">'.$clang->gT('Month').'</label><select id="month'.$ia[1].'" name="month'.$ia[1].'" class="month">
                    <option value="">'.$clang->gT('Month')."</option>\n";
                    switch ((int)trim($aQuestionAttributes['dropdown_dates_month_style']))
                    {
                        case 0:
                            $montharray=array(
                             $clang->gT('Jan'),
                             $clang->gT('Feb'),
                             $clang->gT('Mar'),
                             $clang->gT('Apr'),
                             $clang->gT('May'),
                             $clang->gT('Jun'),
                             $clang->gT('Jul'),
                             $clang->gT('Aug'),
                             $clang->gT('Sep'),
                             $clang->gT('Oct'),
                             $clang->gT('Nov'),
                             $clang->gT('Dec'));
                             break;
                        case 1:
                            $montharray=array(
                             $clang->gT('January'),
                             $clang->gT('February'),
                             $clang->gT('March'),
                             $clang->gT('April'),
                             $clang->gT('May'),
                             $clang->gT('June'),
                             $clang->gT('July'),
                             $clang->gT('August'),
                             $clang->gT('September'),
                             $clang->gT('October'),
                             $clang->gT('November'),
                             $clang->gT('December'));
                             break;
                        case 2:
                            $montharray=array('01','02','03','04','05','06','07','08','09','10','11','12');
                            break;
                    }

                    for ($i=1; $i<=12; $i++) {
                        if ($i == $currentmonth)
                        {
                            $i_date_selected = SELECTED;
                        }
                        else
                        {
                            $i_date_selected = '';
                        }
                        $answer .= '<option value="'.sprintf('%02d', $i).'"'.$i_date_selected.'>'.$montharray[$i-1].'</option>';
                    }
                    $answer .= '</select>';
                    break;
                    // Show year select box
                case 'y':
                case 'Y':   $answer .= '<label for="year'.$ia[1].'" class="hide">'.$clang->gT('Year').'</label><select id="year'.$ia[1].'" name="year'.$ia[1].'" class="year">
                    <option value="">'.$clang->gT('Year').'</option>';

                    /*
                    * yearmin = Minimum year value for dropdown list, if not set default is 1900
                    * yearmax = Maximum year value for dropdown list, if not set default is 2037
                    * if full dates (format: YYYY-MM-DD) are given, only the year is used
                    * expressions are not supported because contents of dropbox cannot be easily updated dynamically
                    */
                    $yearmin = (int)substr($mindate,0,4);
                    if (!isset($yearmin) || $yearmin<1900 || $yearmin>2037)
                    {
                        $yearmin = 1900;
                    }

                    $yearmax = (int)substr($maxdate, 0, 4);
                    if (!isset($yearmax) || $yearmax<1900 || $yearmax>2037)
                    {
                        $yearmax = 2037;
                    }

                    if ($yearmin > $yearmax)
                    {
                        $yearmin = 1900;
                        $yearmax = 2037;
                    }

                    if ($aQuestionAttributes['reverse']==1)
                    {
                        $tmp = $yearmin;
                        $yearmin = $yearmax;
                        $yearmax = $tmp;
                        $step = 1;
                        $reverse = true;
                    }
                    else
                    {
                        $step = -1;
                        $reverse = false;
                    }

                    for ($i=$yearmax; ($reverse? $i<=$yearmin: $i>=$yearmin); $i+=$step) {
                        if ($i == $currentyear)
                        {
                            $i_date_selected = SELECTED;
                        }
                        else
                        {
                            $i_date_selected = '';
                        }
                        $answer .= '<option value="'.$i.'"'.$i_date_selected.'>'.$i.'</option>';
                    }
                    $answer .= '</select>';

                    break;
                case 'H':
                case 'h':
                case 'g':
                case 'G':
                    $answer .= '<label for="hour'.$ia[1].'" class="hide">'.$clang->gT('Hour').'</label><select id="hour'.$ia[1].'" name="hour'.$ia[1].'" class="hour"><option value="">'.$clang->gT('Hour').'</option>';
                    for ($i=0; $i<24; $i++) {
                        if ($i === (int)$currenthour && is_numeric($currenthour))
                        {
                            $i_date_selected = SELECTED;
                        }
                        else
                        {
                            $i_date_selected = '';
                        }
                        if ($datepart=='H')
                        {
                            $answer .= '<option value="'.$i.'"'.$i_date_selected.'>'.sprintf('%02d', $i).'</option>';
                        }
                        else
                        {
                            $answer .= '<option value="'.$i.'"'.$i_date_selected.'>'.$i.'</option>';

                        }
                    }
                    $answer .= '</select>';

                    break;
                case 'i':   $answer .= '<label for="minute'.$ia[1].'" class="hide">'.$clang->gT('Minute').'</label><select id="minute'.$ia[1].'" name="minute'.$ia[1].'" class="minute">
                    <option value="">'.$clang->gT('Minute').'</option>';

                    for ($i=0; $i<60; $i+=$aQuestionAttributes['dropdown_dates_minute_step']) {
                        if ($i === (int)$currentminute && is_numeric($currentminute))
                        {
                            $i_date_selected = SELECTED;
                        }
                        else
                        {
                            $i_date_selected = '';
                        }
                        if ($datepart=='i')
                        {
                            $answer .= '<option value="'.$i.'"'.$i_date_selected.'>'.sprintf('%02d', $i).'</option>';
                        }
                        else
                        {
                            $answer .= '<option value="'.$i.'"'.$i_date_selected.'>'.$i.'</option>';

                        }
                    }
                    $answer .= '</select>';

                    break;
                default:  $answer .= $datepart;
            }
        }

        // Format the date  for output
        $dateoutput=trim($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]);
        if ($dateoutput!='' & $dateoutput!='INVALID')
        {
            $datetimeobj = new Date_Time_Converter($dateoutput , "Y-m-d H:i");
            $dateoutput = $datetimeobj->convert($dateformatdetails['phpdate']);
        }

        $answer .= '<input class="text" type="text" size="10" name="'.$ia[1].'" style="display: none" id="answer'.$ia[1].'" value="'.htmlspecialchars($dateoutput,ENT_QUOTES,'utf-8').'" maxlength="10" alt="'.$clang->gT('Answer').'" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type)" title="'.sprintf($clang->gT('Date in the format : %s'),$dateformatdetails['dateformat']).'" />
        </p>';
        $answer .= '
        <input type="hidden" id="qattribute_answer'.$ia[1].'" name="qattribute_answer'.$ia[1].'" value="'.$ia[1].'"/>
        <input type="hidden" id="dateformat'.$ia[1].'" value="'.$dateformatdetails['jsdate'].'"/>';
        App()->getClientScript()->registerScript("doDropDownDate{$ia[0]}","doDropDownDate({$ia[0]});",CClientScript::POS_HEAD);
        // MayDo:
        // add js code to
        //        - fill dropdown boxes according to min/max
        //        - if one datefield box is changed update all others
        //        - would need a LOT of JS
    }
    else
    {
        //register timepicker extension
        App()->getClientScript()->registerPackage('jqueryui-timepicker');

        // Locale for datepicker and timpicker extension

        if ($clang->langcode !== 'en')
        {
            Yii::app()->getClientScript()->registerScriptFile(App()->getConfig('third_party')."/jqueryui/development-bundle/ui/i18n/jquery.ui.datepicker-{$clang->langcode}.js");
            Yii::app()->getClientScript()->registerScriptFile(App()->getConfig('third_party')."/jquery-ui-timepicker-addon/i18n/jquery-ui-timepicker-{$clang->langcode}.js");
        }
        // Format the date  for output
        $dateoutput=trim($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]);
        if ($dateoutput!='' & $dateoutput!='INVALID')
        {
            $datetimeobj = new Date_Time_Converter($dateoutput , "Y-m-d H:i");
            $dateoutput = $datetimeobj->convert($dateformatdetails['phpdate']);
        }

        $goodchars = str_replace( array("m","d","y"), "", $dateformatdetails['jsdate']);
        $goodchars = "0123456789".substr($goodchars,0,1);
        // Max length of date : Get the date of 1999-12-30 at 32:59:59 to be sure to have space with non leading 0 format
        // "+1" makes room for a trailing space in date/time values
        $iLength=strlen(date($dateformatdetails['phpdate'],mktime(23,59,59,12,30,1999)))+1;


        // HTML for date question using datepicker
        $answer="<p class='question answer-item text-item date-item'><label for='answer{$ia[1]}' class='hide label'>{$clang->gT('Date picker')}</label>
        <input class='popupdate' type=\"text\" size=\"{$iLength}\" name=\"{$ia[1]}\" title='".sprintf($clang->gT('Format: %s'),$dateformatdetails['dateformat'])."' id=\"answer{$ia[1]}\" value=\"$dateoutput\" maxlength=\"{$iLength}\" onkeypress=\"return goodchars(event,'".$goodchars."')\" onchange=\"$checkconditionFunction(this.value, this.name, this.type)\" />
        <input  type='hidden' name='dateformat{$ia[1]}' id='dateformat{$ia[1]}' value='{$dateformatdetails['jsdate']}'  />
        <input  type='hidden' name='datelanguage{$ia[1]}' id='datelanguage{$ia[1]}' value='{$clang->langcode}'  />
        <input  type='hidden' name='datemin{$ia[1]}' id='datemin{$ia[1]}' value=\"{$mindate}\"    />
        <input  type='hidden' name='datemax{$ia[1]}' id='datemax{$ia[1]}' value=\"{$maxdate}\"   />
        </p>";

        // adds min and max date as a hidden element to the page so EM creates the needed LEM_tailor_Q_XX sections
        $sHiddenHtml="";
        if (!empty($sMindatetailor))
        {
            $sHiddenHtml.=$sMindatespan;
        }
        if (!empty($sMaxdatetailor))
        {
            $sHiddenHtml.=$sMaxdatespan;
        }
        if (!empty($sHiddenHtml))
        {
            $answer.="<div class='hidden nodisplay' style='display:none'>{$sHiddenHtml}</div>";
        }

        // following JS is for setting datepicker limits on-the-fly according to variables given in date_min/max attributes
        // works with full dates (format: YYYY-MM-DD, js not needed), only a year, for backward compatibility (YYYY, js not needed),
        // variable names which refer to another date question or expressions.
        // Actual conversion of date formats is handled in LEMval()


        if (!empty($sMindatetailor) || !empty($sMaxdatetailor))
        {
            $answer.="<script>
                $(document).ready(function() {
                        $('.popupdate').change(function() {

                            ";
                if (!empty($sMindatetailor))
                    $answer.="
                        $('#datemin{$ia[1]}').attr('value',
                        document.getElementById('{$sMindatetailor}').innerHTML);
                    ";
                if (!empty($sMaxdatetailor))
                    $answer.="
                        $('#datemax{$ia[1]}').attr('value',
                        document.getElementById('{$sMaxdatetailor}').innerHTML);
                    ";

            $answer.="
                        });
                    });
                </script>";
        }

        if (trim($aQuestionAttributes['hide_tip'])==1) {
            $answer.="<p class=\"tip\">".sprintf($clang->gT('Format: %s'),$dateformatdetails['dateformat'])."</p>";
        }
        //App()->getClientScript()->registerScript("doPopupDate{$ia[0]}","doPopupDate({$ia[0]})",CClientScript::POS_END);// Beter if just afetre answers part
        $answer .= "<script type='text/javascript'>\n"
        . "  /*<![CDATA[*/\n"
        ." doPopupDate({$ia[0]});\n"
        ." /*]]>*/\n"
        ."</script>\n";
    }
    $inputnames[]=$ia[1];

    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_language($ia)
{

    $clang = Yii::app()->lang;

    $checkconditionFunction = "checkconditions";

    $answerlangs = Survey::model()->findByPk(Yii::app()->getConfig('surveyID'))->additionalLanguages;
    $answerlangs [] = Survey::model()->findByPk(Yii::app()->getConfig('surveyID'))->language;
    // Get actual answer
    $sLang=$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang'];
    if(!in_array($sLang,$answerlangs))
    {
        $sLang=Survey::model()->findByPk(Yii::app()->getConfig('surveyID'))->language;
    }
    $answer = "\n\t<p class=\"question answer-item dropdown-item langage-item\">\n"
    ."<label for='answer{$ia[1]}' class='hide label'>{$clang->gT('Choose your language')}</label>"
    ."<select name=\"$ia[1]\" id=\"answer$ia[1]\" onchange=\"$checkconditionFunction(this.value, this.name, this.type);\" class=\"languagesurvey\">\n";
    foreach ($answerlangs as $ansrow)
    {
        $answer .= "\t<option value=\"{$ansrow}\"";
        if ($sLang == $ansrow)
        {
            $answer .= SELECTED;
        }
        $aLanguage=getLanguageNameFromCode($ansrow, true);
        $answer .= '>'.$aLanguage[1]."</option>\n";
    }
    $answer .= "</select>\n";
    $answer .= "<input type=\"hidden\" name=\"java{$ia[1]}\" id=\"java{$ia[1]}\" value=\"{$sLang}\" />\n";
    $inputnames[]=$ia[1];

    $answer .= "<script type='text/javascript'>\n"
    . "/*<![CDATA[*/\n"
    ."$('#answer{$ia[1]}').change(function(){ "
    ."$('<input type=\"hidden\">').attr('name','lang').val($(this).val()).appendTo($('form#limesurvey'));"
    ." })\n"
    ." /*]]>*/\n"
    ."</script>\n";
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_list_dropdown($ia)
{
    global $dropdownthreshold;

    $clang=Yii::app()->lang;

    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    if (trim($aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='')
    {
        $othertext=$aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
    }
    else
    {
        $othertext=$clang->gT('Other:');
    }

    if (trim($aQuestionAttributes['category_separator'])!='')
    {
        $optCategorySeparator = $aQuestionAttributes['category_separator'];
    }

    $answer='';

    //Time Limit Code
    if (trim($aQuestionAttributes['time_limit'])!='')
    {
        $answer .= return_timer_script($aQuestionAttributes, $ia);
    }
    //End Time Limit Code

    $query = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ";
    $other = Yii::app()->db->createCommand($query)->queryScalar();     //Checked

    //question attribute random order set?
    if ($aQuestionAttributes['random_order']==1)
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
    }
    //question attribute alphasort set?
    elseif ($aQuestionAttributes['alphasort']==1)
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY answer";
    }
    //no question attributes -> order by sortorder
    else
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
    }

    $ansresult = Yii::app()->db->createCommand($ansquery)->query() or safeDie('Couldn\'t get answers<br />'.$ansquery.'<br />');    //Checked
    $ansresult= $ansresult->readAll();
    $dropdownSize = '';
    if (isset($aQuestionAttributes['dropdown_size']) && $aQuestionAttributes['dropdown_size'] > 0)
    {
        $_height = sanitize_int($aQuestionAttributes['dropdown_size']) ;
        $_maxHeight = count($ansresult);
        if ((!empty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]])) && $ia[6] != 'Y' && $ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
            ++$_maxHeight;  // for No Answer
        }
        if (isset($other) && $other=='Y') {
            ++$_maxHeight;  // for Other
        }
        if (!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) {
            ++$_maxHeight;  // for 'Please choose:'
        }

        if ($_height > $_maxHeight) {
            $_height = $_maxHeight;
        }
        $dropdownSize = ' size="'.$_height.'"';
    }

    $prefixStyle = 0;
    if (isset($aQuestionAttributes['dropdown_prefix']))
    {
        $prefixStyle = sanitize_int($aQuestionAttributes['dropdown_prefix']) ;
    }
    $_rowNum=0;
    $_prefix='';

    if (!isset($optCategorySeparator))
    {
        foreach ($ansresult as $ansrow)
        {
            $opt_select = '';
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow['code'])
            {
                $opt_select = SELECTED;
            }
            if ($prefixStyle == 1) {
                $_prefix = ++$_rowNum . ') ';
            }
            $answer .= "<option value='{$ansrow['code']}' {$opt_select}>".flattenText($_prefix.$ansrow['answer'])."</option>\n";
        }
    }
    else
    {
        $defaultopts = Array();
        $optgroups = Array();
        foreach ($ansresult as $ansrow)
        {
            // Let's sort answers in an array indexed by subcategories
            @list ($categorytext, $answertext) = explode($optCategorySeparator,$ansrow['answer']);
            // The blank category is left at the end outside optgroups
            if ($categorytext == '')
            {
                $defaultopts[] = array ( 'code' => $ansrow['code'], 'answer' => $answertext);
            }
            else
            {
                $optgroups[$categorytext][] = array ( 'code' => $ansrow['code'], 'answer' => $answertext);
            }
        }

        foreach ($optgroups as $categoryname => $optionlistarray)
        {
            $answer .= '                                   <optgroup class="dropdowncategory" label="'.flattenText($categoryname).'">
            ';

            foreach ($optionlistarray as $optionarray)
            {
                if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $optionarray['code'])
                {
                    $opt_select = SELECTED;
                }
                else
                {
                    $opt_select = '';
                }

                $answer .= '                         <option value="'.$optionarray['code'].'"'.$opt_select.'>'.flattenText($optionarray['answer']).'</option>
                ';
            }

            $answer .= '                                   </optgroup>';
        }
        $opt_select='';
        foreach ($defaultopts as $optionarray)
        {
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $optionarray['code'])
            {
                $opt_select = SELECTED;
            }
            else
            {
                $opt_select = '';
            }

            $answer .= '                         <option value="'.$optionarray['code'].'"'.$opt_select.'>'.flattenText($optionarray['answer']).'</option>
            ';
        }
    }

    if (!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]])
    {
        $answer = '                    <option value=""'.SELECTED.'>'.$clang->gT('Please choose...').'</option>'."\n".$answer;
    }

    if (isset($other) && $other=='Y')
    {
        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '-oth-')
        {
            $opt_select = SELECTED;
        }
        else
        {
            $opt_select = '';
        }
        if ($prefixStyle == 1) {
            $_prefix = ++$_rowNum . ') ';
        }
        $answer .= '                    <option value="-oth-"'.$opt_select.'>'.flattenText($_prefix.$othertext)."</option>\n";
    }

    if (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] != '') && $ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
    {
        if ($prefixStyle == 1) {
            $_prefix = ++$_rowNum . ') ';
        }
        $answer .= '<option class="noanswer-item" value="">'.$_prefix.$clang->gT('No answer')."</option>\n";
    }
    $answer .= '                </select>
    <input type="hidden" name="java'.$ia[1].'" id="java'.$ia[1].'" value="'.$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]].'" />';

    if (isset($other) && $other=='Y')
    {
        $sselect_show_hide = ' showhideother(this.name, this.value);';
    }
    else
    {
        $sselect_show_hide = '';
    }
    $sselect = '
    <p class="question answer-item dropdown-item"><label for="answer'.$ia[1].'" class="hide label">'.$clang->gT('Please choose').'</label>
    <select name="'.$ia[1].'" id="answer'.$ia[1].'"'.$dropdownSize.' onchange="'.$checkconditionFunction.'(this.value, this.name, this.type);'.$sselect_show_hide.'">
    ';
    $answer = $sselect.$answer;

    if (isset($other) && $other=='Y')
    {
        $answer = "\n<script type=\"text/javascript\">\n"
        ."<!--\n"
        ."function showhideother(name, value)\n"
        ."\t{\n"
        ."\tvar hiddenothername='othertext'+name;\n"
        ."\tif (value == \"-oth-\")\n"
        ."{\n"
        ."document.getElementById(hiddenothername).style.display='';\n"
        ."document.getElementById(hiddenothername).focus();\n"
        ."}\n"
        ."\telse\n"
        ."{\n"
        ."document.getElementById(hiddenothername).style.display='none';\n"
        ."document.getElementById(hiddenothername).value='';\n" // reset othercomment field
        ."}\n"
        ."\t}\n"
        ."//--></script>\n".$answer;
        $answer .= '                <input type="text" id="othertext'.$ia[1].'" name="'.$ia[1].'other" style="display:';

        $inputnames[]=$ia[1].'other';

        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] != '-oth-')
        {
            $answer .= 'none';
        }

        //        // --> START BUG FIX - text field for other was not repopulating when returning to page via << PREV
        $answer .= '"';
        //        $thisfieldname=$ia[1].'other';
        //        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname])) { $answer .= ' value="'.htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname],ENT_QUOTES).'" ';}
        //        // --> END BUG FIX

        // --> START NEW FEATURE - SAVE
        $answer .= "  alt='".$clang->gT('Other answer')."' onchange='$checkconditionFunction(this.value, this.name, this.type);'";
        $thisfieldname="$ia[1]other";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname])) { $answer .= " value='".htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname],ENT_QUOTES)."' ";}
        $answer .= ' />';
        $answer .= "</p>";
        // --> END NEW FEATURE - SAVE
        $inputnames[]=$ia[1]."other";
    }
    else
    {
        $answer .= "</p>";
    }

    //    $checkotherscript = "";
    //    if (isset($other) && $other == 'Y' && $aQuestionAttributes['other_comment_mandatory']==1)
    //    {
    //        $checkotherscript = "\n<script type='text/javascript'>\n"
    //        . "\t<!--\n"
    //        . "oldonsubmitOther_{$ia[0]} = document.limesurvey.onsubmit;\n"
    //        . "function ensureOther_{$ia[0]}()\n"
    //        . "{\n"
    //        . "\tothercommentval=document.getElementById('othertext{$ia[1]}').value;\n"
    //        . "\totherval=document.getElementById('answer{$ia[1]}').value;\n"
    //        . "\tif (otherval == '-oth-' && othercommentval == '') {\n"
    //        . "alert('".sprintf($clang->gT("You've selected the \"%s\" answer for question \"%s\". Please also fill in the accompanying \"other comment\" field.","js"),trim(javascriptEscape($othertext,true,true)),trim(javascriptEscape($ia[3],true,true)))."');\n"
    //        . "return false;\n"
    //        . "\t}\n"
    //        . "\telse {\n"
    //        . "if(typeof oldonsubmitOther_{$ia[0]} == 'function') {\n"
    //        . "\treturn oldonsubmitOther_{$ia[0]}();\n"
    //        . "}\n"
    //        . "\t}\n"
    //        . "}\n"
    //        . "document.limesurvey.onsubmit = ensureOther_{$ia[0]};\n"
    //        . "\t-->\n"
    //        . "</script>\n";
    //    }
    //    $answer = $checkotherscript . $answer;

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_list_radio($ia)
{
    global $dropdownthreshold;
    global $thissurvey;
    $clang=Yii::app()->lang;
    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $kpclass = "text-keypad";
    }
    else
    {
        $kpclass = "";
    }

    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    $query = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ";
    $result = Yii::app()->db->createCommand($query)->query();
    foreach ($result->readAll() as $row)
    {
        $other = $row['other'];
    }

    //question attribute random order set?
    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
    }

    //question attribute alphasort set?
    elseif ($aQuestionAttributes['alphasort']==1)
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY answer";
    }

    //no question attributes -> order by sortorder
    else
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
    }

    $ansresult = dbExecuteAssoc($ansquery)->readAll();  //Checked
    $anscount = count($ansresult);

    if (trim($aQuestionAttributes['display_columns'])!='') {
        $dcols = $aQuestionAttributes['display_columns'];
    }
    else
    {
        $dcols= 1;
    }

    if (trim($aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='')
    {
        $othertext=$aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
    }
    else
    {
        $othertext=$clang->gT('Other:');
    }

    if (isset($other) && $other=='Y') {$anscount++;} //Count up for the Other answer
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {$anscount++;} //Count up if "No answer" is showing

    $wrapper = setupColumns($dcols , $anscount,"answers-list radio-list","answer-item radio-item");
    $answer = $wrapper['whole-start'];

    //Time Limit Code
    if (trim($aQuestionAttributes['time_limit'])!='')
    {
        $answer .= return_timer_script($aQuestionAttributes, $ia);
    }
    //End Time Limit Code

    // Get array_filter stuff

    $rowcounter = 0;
    $colcounter = 1;
    $trbc='';

    foreach ($ansresult as $key=>$ansrow)
    {
        $myfname = $ia[1].$ansrow['code'];
        $check_ans = '';
        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow['code'])
        {
            $check_ans = CHECKED;
        }

        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname, "li","answer-item radio-item");
        if(substr($wrapper['item-start'],0,4) == "\t<li")
        {
            $startitem = "\t$htmltbody2\n";
        } else {
            $startitem = $wrapper['item-start'];
        }

        $answer .= $startitem;
        $answer .= "\t$hiddenfield\n";
        $answer .='        <input class="radio" type="radio" value="'.$ansrow['code'].'" name="'.$ia[1].'" id="answer'.$ia[1].$ansrow['code'].'"'.$check_ans.' onclick="if (document.getElementById(\'answer'.$ia[1].'othertext\') != null) document.getElementById(\'answer'.$ia[1].'othertext\').value=\'\';'.$checkconditionFunction.'(this.value, this.name, this.type)" />
        <label for="answer'.$ia[1].$ansrow['code'].'" class="answertext">'.$ansrow['answer'].'</label>
        '.$wrapper['item-end'];

        ++$rowcounter;
        if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'] || (count($ansresult)-$key)==$wrapper['cols']-$colcounter)
        {
            if($colcounter == $wrapper['cols'] - 1 )
            {
                $answer .= $wrapper['col-devide-last'];
            }
            else
            {
                $answer .= $wrapper['col-devide'];
            }
            $rowcounter = 0;
            ++$colcounter;
        }
    }

    if (isset($other) && $other=='Y')
    {

        $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator = $sSeparator['separator'];

        if ($aQuestionAttributes['other_numbers_only']==1)
        {
            $oth_checkconditionFunction = 'fixnum_checkconditions';
        }
        else
        {
            $oth_checkconditionFunction = 'checkconditions';
        }


        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '-oth-')
        {
            $check_ans = CHECKED;
        }
        else
        {
            $check_ans = '';
        }

        $thisfieldname=$ia[1].'other';
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname]))
        {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname];
            if ($aQuestionAttributes['other_numbers_only']==1)
            {
                $dispVal = str_replace('.',$sSeparator,$dispVal);
            }
            $answer_other = ' value="'.htmlspecialchars($dispVal,ENT_QUOTES).'"';
        }
        else
        {
            $answer_other = ' value=""';
        }

        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, array("code"=>"other"), $thisfieldname, $trbc, $myfname, "li", "answer-item radio-item other-item other");

        if(substr($wrapper['item-start-other'],0,4) == "\t<li")
        {
            $startitem = "\t$htmltbody2\n";
        } else {
            $startitem = $wrapper['item-start-other'];
        }
        $answer .= $startitem;
        $answer .= "\t$hiddenfield\n";
        $answer .= '        <input class="radio" type="radio" value="-oth-" name="'.$ia[1].'" id="SOTH'.$ia[1].'"'.$check_ans.' onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)" />
        <label for="SOTH'.$ia[1].'" class="answertext">'.$othertext.'</label>
        <label for="answer'.$ia[1].'othertext">
        <input type="text" class="text '.$kpclass.'" id="answer'.$ia[1].'othertext" name="'.$ia[1].'other" title="'.$clang->gT('Other').'"'.$answer_other.' onkeyup="if($.trim($(this).val())!=\'\'){ $(\'#SOTH'.$ia[1].'\').click(); }; '.$oth_checkconditionFunction.'(this.value, this.name, this.type);" />
        </label>
        '.$wrapper['item-end'];

        $inputnames[]=$thisfieldname;

        ++$rowcounter;
        if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
        {
            if($colcounter == $wrapper['cols'] - 1)
            {
                $answer .= $wrapper['col-devide-last'];
            }
            else
            {
                $answer .= $wrapper['col-devide'];
            }
            $rowcounter = 0;
            ++$colcounter;
        }
    }

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
    {
        if ((!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '') || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == ' ' ))
        {
            $check_ans = CHECKED; //Check the "no answer" radio button if there is no answer in session.
        }
        else
        {
            $check_ans = '';
        }

        $answer .= $wrapper['item-start-noanswer'].'        <input class="radio" type="radio" name="'.$ia[1].'" id="answer'.$ia[1].'NANS" value=""'.$check_ans.' onclick="if (document.getElementById(\'answer'.$ia[1].'othertext\') != null) document.getElementById(\'answer'.$ia[1].'othertext\').value=\'\';'.$checkconditionFunction.'(this.value, this.name, this.type)" />
        <label for="answer'.$ia[1].'NANS" class="answertext">'.$clang->gT('No answer').'</label>
        '.$wrapper['item-end'];
        // --> END NEW FEATURE - SAVE

        ++$rowcounter;
        if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
        {
            if($colcounter == $wrapper['cols'] - 1)
            {
                $answer .= $wrapper['col-devide-last'];
            }
            else
            {
                $answer .= $wrapper['col-devide'];
            }
            $rowcounter = 0;
            ++$colcounter;
        }

    }
    //END OF ITEMS
    $answer .= $wrapper['whole-end'].'
    <input type="hidden" name="java'.$ia[1].'" id="java'.$ia[1]."\" value=\"".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]."\" />\n";

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_listwithcomment($ia)
{
    global $maxoptionsize, $thissurvey;
    $clang=Yii::app()->lang;
    $dropdownthreshold = Yii::app()->getConfig("dropdownthreshold");

    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $kpclass = "text-keypad";
    }
    else
    {
        $kpclass = "";
    }

    $checkconditionFunction = "checkconditions";

    $answer = '';

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    if (!isset($maxoptionsize)) {$maxoptionsize=35;}

    //question attribute random order set?
    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
    }
    //question attribute alphasort set?
    elseif ($aQuestionAttributes['alphasort']==1)
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY answer";
    }
    //no question attributes -> order by sortorder
    else
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
    }

    $ansresult=Yii::app()->db->createCommand($ansquery)->query()->readAll();
    $anscount = count($ansresult);


    $hint_comment = $clang->gT('Please enter your comment here');
    if ($aQuestionAttributes['use_dropdown']!=1 && $anscount <= $dropdownthreshold)
    {
        $answer .= '<div class="list">
        <ul class="answers-list radio-list">
        ';

        foreach ($ansresult as $ansrow)
        {
            $check_ans = '';
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow['code'])
            {
                $check_ans = CHECKED;
            }
            $answer .= '        <li class="answer-item radio-item">
            <input type="radio" name="'.$ia[1].'" id="answer'.$ia[1].$ansrow['code'].'" value="'.$ansrow['code'].'" class="radio" '.$check_ans.' onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)" />
            <label for="answer'.$ia[1].$ansrow['code'].'" class="answertext">'.$ansrow['answer'].'</label>
            </li>
            ';
        }

        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            if ((!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '') ||($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == ' ' ))
            {
                $check_ans = CHECKED;
            }
            elseif (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] != ''))
            {
                $check_ans = '';
            }
            $answer .= '        <li class="answer-item radio-item noanswer-item">
            <input class="radio" type="radio" name="'.$ia[1].'" id="answer'.$ia[1].'" value=" " onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)"'.$check_ans.' />
            <label for="answer'.$ia[1].'" class="answertext">'.$clang->gT('No answer').'</label>
            </li>
            ';
        }

        $fname2 = $ia[1].'comment';
        if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
        // --> START NEW FEATURE - SAVE
        //    --> START ORIGINAL
        //        $answer .= "\t<td valign='top'>\n"
        //                 . "<textarea class='textarea' name='$ia[1]comment' id='answer$ia[1]comment' rows='$tarows' cols='30'>";
        //    --> END ORIGINAL
        $answer .= '    </ul>
        </div>

        <p class="comment answer-item text-item">
        <label for="answer'.$ia[1].'comment">'.$hint_comment.':</label>

        <textarea class="textarea '.$kpclass.'" name="'.$ia[1].'comment" id="answer'.$ia[1].'comment" rows="'.floor($tarows).'" cols="30" >';
        // --> END NEW FEATURE - SAVE
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2])
        {
            $answer .= str_replace("\\", "", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]);
        }
        $answer .= '</textarea>
        </p>

        <input class="radio" type="hidden" name="java'.$ia[1].'" id="java'.$ia[1].'" value="'.$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]].'" />
        ';
        $inputnames[]=$ia[1];
        $inputnames[]=$ia[1].'comment';
    }
    else //Dropdown list
    {
        $answer .= '<p class="select answer-item dropdown-item">
        <select class="select" name="'.$ia[1].'" id="answer'.$ia[1].'" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type)" >
        ';
        if (is_null($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]))
        {
            $answer .= '<option value=""'.SELECTED.'>'.$clang->gT('Please choose...').'</option>'."\n";
        }
        foreach ($ansresult as $ansrow)
        {
            $check_ans = '';
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow['code'])
            {
                $check_ans = SELECTED;
            }
            $answer .= '        <option value="'.$ansrow['code'].'"'.$check_ans.'>'.$ansrow['answer']."</option>\n";

            if (strlen($ansrow['answer']) > $maxoptionsize)
            {
                $maxoptionsize = strlen($ansrow['answer']);
            }
        }
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1 && !is_null($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]))
        {
            $check_ans="";
            if ( $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '' || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == ' ' )
            {
                $check_ans = SELECTED;
            }
            $answer .= '<option class="noanswer-item" value=""'.$check_ans.'>'.$clang->gT('No answer')."</option>\n";
        }
        $answer .= '    </select>
        </p>
        ';
        $fname2 = $ia[1].'comment';
        if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
        if ($tarows > 15) {$tarows=15;}
        $maxoptionsize=$maxoptionsize*0.72;
        if ($maxoptionsize < 33) {$maxoptionsize=33;}
        if ($maxoptionsize > 70) {$maxoptionsize=70;}
        $answer .= '<p class="comment answer-item text-item">
        <label for="answer'.$ia[1].'comment">'.$hint_comment.':</label>
        <textarea class="textarea '.$kpclass.'" name="'.$ia[1].'comment" id="answer'.$ia[1].'comment" rows="'.$tarows.'" cols="'.$maxoptionsize.'" >';
        // --> END NEW FEATURE - SAVE
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2])
        {
            $answer .= str_replace("\\", "", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]);
        }
        $answer .= '</textarea>
        <input class="radio" type="hidden" name="java'.$ia[1].'" id="java'.$ia[1].'" value="'.$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]].'" /></p>';
        $inputnames[]=$ia[1];
        $inputnames[]=$ia[1].'comment';
    }
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_ranking($ia)
{
    // note to self: this function needs to define:
    // inputnames, answer, among others
    global $thissurvey;

    $clang=Yii::app()->lang;
    $imageurl = Yii::app()->getConfig("imageurl");

    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
    } else {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
    }
    $ansresult = Yii::app()->db->createCommand($ansquery)->query()->readAll();   //Checked
    $anscount= count($ansresult);
    if (trim($aQuestionAttributes["max_answers"])!='')
    {
        $max_answers=trim($aQuestionAttributes["max_answers"]);
    } else {
        $max_answers=$anscount;
    }
    // Get the max number of line needed
    if(ctype_digit($max_answers) && intval($max_answers)<$anscount)
    {
        $iMaxLine=$max_answers;
    }
    else
    {
        $iMaxLine=$anscount;
    }
    if (trim($aQuestionAttributes["min_answers"])!='')
    {
        $min_answers=trim($aQuestionAttributes["min_answers"]);
    } else {
        $min_answers=0;
    }
    $answer = '';
    // First start by a ranking without javascript : just a list of select box
    // construction select box
    $answers= array();
    foreach ($ansresult as $ansrow)
    {
        $answers[] = $ansrow;
    }
    $answer .= '<div class="ranking-answers">
    <ul class="answers-list select-list">';
    for ($i=1; $i<=$iMaxLine; $i++)
    {
        $myfname=$ia[1].$i;
        $answer .= "\n<li class=\"select-item\">";
        $answer .="<label for=\"answer{$myfname}\">";
        if($i==1){
            $answer .=$clang->gT('First choice');
        }else{
            $answer .=sprintf($clang->gT('Choice of rank %s'),$i);
        }
        $answer .= "</label>";
        $answer .= "<select name=\"{$myfname}\" id=\"answer{$myfname}\">\n";
        if (!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])
        {
            $answer .= "\t<option value=\"\"".SELECTED.">".$clang->gT('Please choose...')."</option>\n";
        }
        foreach ($answers as $ansrow)
        {
            $thisvalue="";
            $answer .="\t<option value=\"{$ansrow['code']}\"";
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $ansrow['code'])
                {
                    $answer .= SELECTED;
                    $thisvalue=$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                }
            $answer .=">".flattenText($ansrow['answer'])."</option>\n";
        }
        $answer .="</select>";
        // Hidden form: maybe can be replaced with ranking.js
        $answer .="<input type=\"hidden\" id=\"java{$myfname}\" disabled=\"disabled\" value=\"{$thisvalue}\"/>";
        $answer .="</li>";
        $inputnames[]=$myfname;
    }
    $answer .="</ul>"
        . "<div style='display:none' id='ranking-{$ia[0]}-maxans'>{".$max_answers."}</div>"
        . "<div style='display:none' id='ranking-{$ia[0]}-minans'>{".$min_answers."}</div>"
        . "<div style='display:none' id='ranking-{$ia[0]}-name'>".$ia[1]."</div>"
        . "</div>";
    // The list with HTML answers
    $answer .="<div style=\"display:none\">";
    foreach ($answers as $ansrow)
    {
        $answer.="<div id=\"htmlblock-{$ia['0']}-{$ansrow['code']}\">{$ansrow['answer']}</div>";
    }
    $answer .="</div>";
    App()->getClientScript()->registerPackage('jquery-actual'); // Needed to with jq1.9 ?
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."ranking.js");
    Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . "ranking.css");

    if(trim($aQuestionAttributes['choice_title'][$clang->langcode]) != '')
    {
        $choice_title=htmlspecialchars(trim($aQuestionAttributes['choice_title'][$clang->langcode]), ENT_QUOTES);
    }
    else
    {
        $choice_title=$clang->gT("Your Choices",'js');
    }
    if(trim($aQuestionAttributes['rank_title'][$clang->langcode]) != '')
    {
        $rank_title=htmlspecialchars(trim($aQuestionAttributes['rank_title'][$clang->langcode]), ENT_QUOTES);
    }
    else
    {
        $rank_title=$clang->gT("Your Ranking",'js');
    }
    // hide_tip is managed by css with EM
    $rank_help = $clang->gT("Double-click or drag-and-drop items in the left list to move them to the right - your highest ranking item should be on the top right, moving through to your lowest ranking item.",'js');

    $answer .= "<script type='text/javascript'>\n"
    . "  <!--\n"
    . "var aRankingTranslations = {
             choicetitle: '{$choice_title}',
             ranktitle: '{$rank_title}',
             rankhelp: '{$rank_help}'
            };\n"
    ." doDragDropRank({$ia[0]},{$aQuestionAttributes["showpopups"]},{$aQuestionAttributes["samechoiceheight"]},{$aQuestionAttributes["samelistheight"]});\n"
    ." -->\n"
    ."</script>\n";
    return array($answer, $inputnames);
}


// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_multiplechoice($ia)
{
    global $thissurvey;

    $clang = Yii::app()->lang;
    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $kpclass = "text-keypad";
    }
    else
    {
        $kpclass = "";
    }

    // Find out if any questions have attributes which reference this questions
    // based on value of attribute. This could be array_filter and array_filter_exclude

    $attribute_ref=false;
    $inputnames=array();

    $qaquery = "SELECT qid,attribute FROM {{question_attributes}} WHERE value LIKE '".strtolower($ia[2])."' and (attribute='array_filter' or attribute='array_filter_exclude')";
    $qaresult = Yii::app()->db->createCommand($qaquery)->query();     //Checked
    foreach ($qaresult->readAll() as $qarow)
    {
        $qquery = "SELECT count(qid) FROM {{questions}} WHERE sid=".$thissurvey['sid']." AND scale_id=0 AND qid=".$qarow['qid'];
        $qresult = Yii::app()->db->createCommand($qquery)->queryScalar();     //Checked
        if ($qresult > 0)
        {
            $attribute_ref = true;
        }
    }

    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    if (trim($aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='')
    {
        $othertext=$aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
    }
    else
    {
        $othertext=$clang->gT('Other:');
    }

    if (trim($aQuestionAttributes['display_columns'])!='')
    {
        $dcols = $aQuestionAttributes['display_columns'];
    }
    else
    {
        $dcols = 1;
    }

    if ($aQuestionAttributes['other_numbers_only']==1)
    {
        $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator= $sSeparator['separator'];
        $oth_checkconditionFunction = "fixnum_checkconditions";
    }
    else
    {
        $oth_checkconditionFunction = "checkconditions";
    }

    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and parent_qid=0";
    $other = Yii::app()->db->createCommand($qquery)->queryScalar(); //Checked

    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    }
    else
    {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }

    $ansresult = dbExecuteAssoc($ansquery)->readAll();  //Checked
    $anscount = count($ansresult);

    if (trim($aQuestionAttributes['exclude_all_others'])!='' && $aQuestionAttributes['random_order']==1)
    {
        //if  exclude_all_others is set then the related answer should keep its position at all times
        //thats why we have to re-position it if it has been randomized
        $position=0;
        foreach ($ansresult as $answer)
        {
            if ((trim($aQuestionAttributes['exclude_all_others']) != '')  &&    ($answer['title']==trim($aQuestionAttributes['exclude_all_others'])))
            {
                if ($position==$answer['question_order']-1) break; //already in the right position
                $tmp  = array_splice($ansresult, $position, 1);
                array_splice($ansresult, $answer['question_order']-1, 0, $tmp);
                break;
            }
            $position++;
        }
    }

    if ($other == 'Y')
    {
        $anscount++; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
    }

    $wrapper = setupColumns($dcols, $anscount,"subquestions-list questions-list checkbox-list","question-item answer-item checkbox-item");

    $answer = '<input type="hidden" name="MULTI'.$ia[1].'" value="'.$anscount."\" />\n\n".$wrapper['whole-start'];

    $fn = 1;
    if (!isset($multifields))
    {
        $multifields = '';
    }

    $rowcounter = 0;
    $colcounter = 1;
    $startitem='';
    $postrow = '';
    $trbc='';
    foreach ($ansresult as $ansrow)
    {
        $myfname = $ia[1].$ansrow['title'];
        $extra_class="";

        $trbc='';
        /* Check for array_filter */
        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname, "li","question-item answer-item checkbox-item".$extra_class);

        if(substr($wrapper['item-start'],0,4) == "\t<li")
        {
            $startitem = "\t$htmltbody2\n";
        } else {
            $startitem = $wrapper['item-start'];
        }

        /* Print out the checkbox */
        $answer .= $startitem;
        $answer .= "\t$hiddenfield\n";
        $answer .= '        <input class="checkbox" type="checkbox" name="'.$ia[1].$ansrow['title'].'" id="answer'.$ia[1].$ansrow['title'].'" value="Y"';

        /* If the question has already been ticked, check the checkbox */
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'Y')
            {
                $answer .= CHECKED;
            }
        }
        $answer .= " onclick='cancelBubbleThis(event);";

        $answer .= ''
        .  "$checkconditionFunction(this.value, this.name, this.type)' />\n"
        .  "<label for=\"answer$ia[1]{$ansrow['title']}\" class=\"answertext\">"
        .  $ansrow['question']
        .  "</label>\n";


        //        if ($maxansw > 0) {$maxanswscript .= "\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}
        //        if ($minansw > 0) {$minanswscript .= "\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}

        ++$fn;
        /* Now add the hidden field to contain information about this answer */
        $answer .= '        <input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="';
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
        }
        $answer .= "\" />\n{$wrapper['item-end']}";

        $inputnames[]=$myfname;

        ++$rowcounter;
        if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
        {
            if($colcounter == $wrapper['cols'] - 1)
            {
                $answer .= $wrapper['col-devide-last'];
            }
            else
            {
                $answer .= $wrapper['col-devide'];
            }
            $rowcounter = 0;
            ++$colcounter;
        }
    }

    if ($other == 'Y')
    {
        $myfname = $ia[1].'other';
        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, array("code"=>"other"), $myfname, $trbc, $myfname, "li","question-item answer-item checkbox-item other-item");

        if(substr($wrapper['item-start-other'],0,4) == "\t<li")
        {
            $startitem = "\t$htmltbody2\n";
        } else {
            $startitem = $wrapper['item-start-other'];
        }
        $answer .= $startitem;
        $answer .= $hiddenfield.'
        <input class="checkbox other-checkbox" style="visibility:hidden" type="checkbox" name="'.$myfname.'cbox" title="'.$clang->gT('Other').'" id="answer'.$myfname.'cbox"';
        // othercbox can be not display, because only input text goes to database

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && trim($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])!='')
        {
            $answer .= CHECKED;
        }
        $answer .= " />
        <label for=\"answer$myfname\" class=\"answertext\">".$othertext."</label>
        <input class=\"text ".$kpclass."\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\" value=\"";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            if ($aQuestionAttributes['other_numbers_only']==1)
            {
                $dispVal = str_replace('.',$sSeparator,$dispVal);
            }
            $answer .= htmlspecialchars($dispVal,ENT_QUOTES);
        }
        $answer .="\" />\n";
        $answer .="<script type='text/javascript'>\n/*<![CDATA[*/\n";
        $answer .="$('#answer{$myfname}cbox').prop('aria-hidden', 'true').css('visibility','');";
        $answer .="$('#answer{$myfname}').bind('keyup focusout',function(event){\n";
        $answer .= " if ($.trim($(this).val()).length>0) { $(\"#answer{$myfname}cbox\").prop(\"checked\",true); } else { \$(\"#answer{$myfname}cbox\").prop(\"checked\",false); }; $(\"#java{$myfname}\").val($(this).val());LEMflagMandOther(\"$myfname\",$('#answer{$myfname}cbox').is(\":checked\")); $oth_checkconditionFunction(this.value, this.name, this.type); \n";
        $answer .="});\n";
        $answer .="$('#answer{$myfname}cbox').click(function(event){\n";
        $answer .= " if (($(this)).is(':checked') && $.trim($(\"#answer{$myfname}\").val()).length==0) { $(\"#answer{$myfname}\").focus();LEMflagMandOther(\"$myfname\",true);return false; } else {  $(\"#answer{$myfname}\").val('');{$checkconditionFunction}(\"\", \"{$myfname}\", \"text\");LEMflagMandOther(\"$myfname\",false); return true; }; \n";
        $answer .="});\n";
        $answer .="/*]]>*/\n</script>\n";
        $answer .= '<input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="';

        //        if ($maxansw > 0)
        //        {
        //            // For multiplechoice question there is no DB field for the other Checkbox
        //            // I've added a javascript which will warn a user if no other comment is given while the other checkbox is checked
        //            // For the maxanswer script, I will alert the participant
        //            // if the limit is reached when he checks the other cbox
        //            // even if the -other- input field is still empty
        //            $maxanswscript .= "\tif (document.getElementById('answer".$myfname."cbox').checked ) { count += 1; }\n";
        //        }
        //        if ($minansw > 0)
        //        {
        //            //
        //            // For multiplechoice question there is no DB field for the other Checkbox
        //            // We only count the -other- as valid if both the cbox and the other text is filled
        //            $minanswscript .= "\tif (document.getElementById('answer".$myfname."').value != '' && document.getElementById('answer".$myfname."cbox').checked ) { count += 1; }\n";
        //        }


        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            if ($aQuestionAttributes['other_numbers_only']==1)
            {
                $dispVal = str_replace('.',$sSeparator,$dispVal);
            }
            $answer .= htmlspecialchars($dispVal,ENT_QUOTES);
        }

        $answer .= "\" />\n{$wrapper['item-end']}";
        $inputnames[]=$myfname;
        ++$anscount;

        ++$rowcounter;
        if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
        {
            if($colcounter == $wrapper['cols'] - 1)
            {
                $answer .= $wrapper['col-devide-last'];
            }
            else
            {
                $answer .= $wrapper['col-devide'];
            }
            $rowcounter = 0;
            ++$colcounter;
        }
    }
    $answer .= $wrapper['whole-end'];
    //    if ( $maxansw > 0 )
    //    {
    //        $maxanswscript .= "
    //        if (count > max)
    //        {
    //            alert('".sprintf($clang->gT("Please choose at most %d answers for question \"%s\"","js"), $maxansw, trim(javascriptEscape(str_replace(array("\n", "\r"), "", $ia[3]),true,true)))."');
    //            if (me.type == 'checkbox') { me.checked = false; }
    //            if (me.type == 'text') {
    //                me.value = '';
    //                if (document.getElementById('answer'+me.name + 'cbox') ){
    //                    document.getElementById('answer'+me.name + 'cbox').checked = false;
    //                }
    //            }
    //            return max;
    //        }
    //        }
    //        //-->
    //        </script>\n";
    //        $answer = $maxanswscript . $answer;
    //    }
    //
    //
    //    if ( $minansw > 0 )
    //    {
    //        $minanswscript .=
    //        "\tif (count < {$minansw} && document.getElementById('display{$ia[0]}').value == 'on'){\n"
    //        . "alert('".sprintf($clang->gT("Please choose at least %d answer(s) for question \"%s\"","js"),
    //        $minansw, trim(javascriptEscape(str_replace(array("\n", "\r"), "",$ia[3]),true,true)))."');\n"
    //        . "return false;\n"
    //        . "\t} else {\n"
    //        . "if (oldonsubmit_{$ia[0]}){\n"
    //        . "\treturn oldonsubmit_{$ia[0]}();\n"
    //        . "}\n"
    //        . "return true;\n"
    //        . "\t}\n"
    //        . "}\n"
    //        . "document.limesurvey.onsubmit = ensureminansw_{$ia[0]}\n"
    //        . "-->\n"
    //        . "\t</script>\n";
    //        //$answer = $minanswscript . $answer;
    //    }

#   No need $checkotherscript : already done by check mandatory
#   TODO move it to EM
#    $checkotherscript = "";
#    if ($other == 'Y')
#    {
#        // Multiple choice with 'other' is a specific case as the checkbox isn't recorded into DB
#        // this means that if it is cehcked We must force the end-user to enter text in the input
#        // box
#        $checkotherscript = "<script type='text/javascript'>\n"
#        . "\t<!--\n"
#        . "oldonsubmitOther_{$ia[0]} = document.limesurvey.onsubmit;\n"
#        . "function ensureOther_{$ia[0]}()\n"
#        . "{\n"
#        . "\tothercboxval=document.getElementById('answer".$myfname."cbox').checked;\n"
#        . "\totherval=document.getElementById('answer".$myfname."').value;\n"
#        . "\tif (otherval != '' || othercboxval != true) {\n"
#        . "if(typeof oldonsubmitOther_{$ia[0]} == 'function') {\n"
#        . "\treturn oldonsubmitOther_{$ia[0]}();\n"
#        . "}\n"
#        . "\t}\n"
#        . "\telse {\n"
#        . "alert('".sprintf($clang->gT("You've marked the 'Other:' field for question '%s'. Please also fill in the accompanying comment field.","js"),trim(javascriptEscape($ia[3],true,true)))."');\n"
#        . "return false;\n"
#        . "\t}\n"
#        . "}\n"
#        . "document.limesurvey.onsubmit = ensureOther_{$ia[0]};\n"
#        . "\t-->\n"
#        . "</script>\n";
#    }

#    $answer = $checkotherscript . $answer;

    $answer .= $postrow;
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_multiplechoice_withcomments($ia)
{
    global $thissurvey;

    $clang = Yii::app()->lang;
    $inputnames= array();
    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $kpclass = "text-keypad";
    }
    else
    {
        $kpclass = "";
    }

    $inputnames = array();
    $attribute_ref=false;
    $qaquery = "SELECT qid,attribute FROM {{question_attributes}} WHERE value LIKE '".strtolower($ia[2])."'";
    $qaresult = Yii::app()->db->createCommand($qaquery)->query();     //Checked

    $attribute_ref=false;
    foreach($qaresult->readAll() as $qarow)
    {
        $qquery = "SELECT qid FROM {{questions}} WHERE sid=".$thissurvey['sid']." AND qid=".$qarow['qid'];
        $qresult = Yii::app()->db->createCommand($qquery)->query(); //Checked
        if (count($qresult)> 0)
        {
            $attribute_ref = true;
        }
    }

    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    if ($aQuestionAttributes['other_numbers_only']==1)
    {
        $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator = $sSeparator['separator'];
        $oth_checkconditionFunction = "fixnum_checkconditions";
    }
    else
    {
        $oth_checkconditionFunction = "checkconditions";
    }

    if (trim($aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='')
    {
        $othertext=$aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
    }
    else
    {
        $othertext=$clang->gT('Other:');
    }

    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and parent_qid=0";
    $other = Yii::app()->db->createCommand($qquery)->queryScalar(); //Checked
    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    } else {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }
    $ansresult = Yii::app()->db->createCommand($ansquery)->query();  //Checked
    $anscount = count($ansresult)*2;

    $answer = "<input type='hidden' name='MULTI$ia[1]' value='$anscount' />\n";
    $answer_main = '';

    $fn = 1;
    if (!isset($other)){
        $other = 'N';
    }
    if($other == 'Y')
    {
        $label_width = 25;
    }
    else
    {
        $label_width = 0;
    }

    foreach ($ansresult->readAll() as $ansrow)
    {
        $myfname = $ia[1].$ansrow['title'];
        $trbc='';
        /* Check for array_filter */

        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname, "li","question-item answer-item checkbox-text-item");

        if($label_width < strlen(trim(strip_tags($ansrow['question']))))
        {
            $label_width = strlen(trim(strip_tags($ansrow['question'])));
        }

        $myfname2 = $myfname."comment";
        $startitem = "\t$htmltbody2\n";
        /* Print out the checkbox */
        $answer_main .= $startitem;
        $answer_main .= "\t$hiddenfield\n";
        $answer_main .= "<span class=\"option\">\n"
        . "\t<input class=\"checkbox\" type=\"checkbox\" name=\"$myfname\" id=\"answer$myfname\" value=\"Y\"";

        /* If the question has already been ticked, check the checkbox */
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'Y')
            {
                $answer_main .= CHECKED;
            }
        }
        $answer_main .=" onclick='$checkconditionFunction(this.value, this.name, this.type);' />\n"
        . "\t<label for=\"answer$myfname\" class=\"answertext\">\n"
        . $ansrow['question']."</label>\n";

        $answer_main .= "<input type='hidden' name='java$myfname' id='java$myfname' value='";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $answer_main .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
        }
        $answer_main .= "' />\n";
        $fn++;
        $answer_main .= "</span>\n<span class=\"comment\">\n\t<label for='answer$myfname2' class=\"answer-comment hide \">".$clang->gT('Make a comment on your choice here:')."</label>\n"
        ."<input class='text ".$kpclass."' type='text' size='40' id='answer$myfname2' name='$myfname2' value='";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2])) {$answer_main .= htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2],ENT_QUOTES);}
        $answer_main .= "' onkeyup='$checkconditionFunction(this.value,this.name,this.type);' />\n</span>\n"
        . "\t</li>\n";

        $fn++;
        $inputnames[]=$myfname;
        $inputnames[]=$myfname2;
    }
    if ($other == 'Y')
    {
        $myfname = $ia[1].'other';
        $myfname2 = $myfname.'comment';
        $anscount = $anscount + 2;
        $answer_main .= "\t<li class=\"other question-item answer-item checkbox-text-item other-item\" id=\"javatbd$myfname\">\n<span class=\"option\">\n"
        . "\t<label for=\"answer$myfname\" class=\"answertext\">\n".$othertext."\n<input class=\"text other ".$kpclass."\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\" title=\"".$clang->gT('Other').'" size="10"';
        $answer_main .= " onkeyup='$oth_checkconditionFunction(this.value, this.name, this.type);'";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])
        {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            if ($aQuestionAttributes['other_numbers_only']==1)
            {
                $dispVal = str_replace('.',$sSeparator,$dispVal);
            }
            $answer_main .= ' value="'.htmlspecialchars($dispVal,ENT_QUOTES).'"';
        }
        $fn++;
        // --> START NEW FEATURE - SAVE
        $answer_main .= " />\n\t</label>\n</span>\n"
        . "<span class=\"comment\">\n\t<label for=\"answer$myfname2\" class=\"answer-comment hide\">".$clang->gT('Make a comment on your choice here:')."\t</label>\n"
        . '<input class="text '.$kpclass.'" type="text" size="40" name="'.$myfname2.'" id="answer'.$myfname2.'"'
        . " onkeyup='$checkconditionFunction(this.value,this.name,this.type);'"
        . ' title="'.$clang->gT('Make a comment on your choice here:').'" value="';
        // --> END NEW FEATURE - SAVE

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2])) {$answer_main .= htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2],ENT_QUOTES);}
        $answer_main .= "\"/>\n";
        $answer_main .= "</span>\n\t</li>\n";

        $inputnames[]=$myfname;
        $inputnames[]=$myfname2;
    }
    $answer .= "<ul class=\"subquestions-list questions-list checkbox-text-list\">\n".$answer_main."</ul>\n";
    if($aQuestionAttributes['commented_checkbox']!="allways" && $aQuestionAttributes['commented_checkbox_auto'])
    {
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."multiplechoice_withcomments.js");
#        $script= " doMultipleChoiceWithComments({$ia[0]},'{$aQuestionAttributes["commented_checkbox"]}');\n";
#        App()->getClientScript()->registerScript("doMultipleChoiceWithComments",$script,CClientScript::POS_HEAD);// Deactivate now: need to be after question, and just after
        $answer .= "<script type='text/javascript'>\n"
        . "  /*<![CDATA[*/\n"
        ." doMultipleChoiceWithComments({$ia[0]},'{$aQuestionAttributes["commented_checkbox"]}');\n"
        ." /*]]>*/\n"
        ."</script>\n";
    }
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_file_upload($ia)
{
    global $thissurvey;

    $clang = Yii::app()->lang;

    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes=getQuestionAttributeValues($ia[0]);

    // Fetch question attributes
    $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldname'] = $ia[1];

    $currentdir = getcwd();
    $pos = stripos($currentdir, "admin");
    $scriptloc = Yii::app()->getController()->createUrl('uploader/index');

    if ($pos)
    {
        $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['preview'] = 1 ;
        $questgrppreview = 1;   // Preview is launched from Question or group level

    }
    else if ($thissurvey['active'] != "Y")
        {
            $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['preview'] = 1;
            $questgrppreview = 0;
        }
        else
        {
            $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['preview'] = 0;
            $questgrppreview = 0;
    }

    $uploadbutton = "<h2><a id='upload_".$ia[1]."' class='upload' ";
    $uploadbutton .= " href='#' onclick='javascript:upload_$ia[1]();'";
    $uploadbutton .=">" .$clang->gT('Upload files'). "</a></h2>";

    $answer = "<script type='text/javascript'>
        function upload_$ia[1]() {
            var uploadurl = '{$scriptloc}?sid=".Yii::app()->getConfig('surveyID')."&fieldname={$ia[1]}&qid={$ia[0]}';
            uploadurl += '&preview={$questgrppreview}&show_title={$aQuestionAttributes['show_title']}';
            uploadurl += '&show_comment={$aQuestionAttributes['show_comment']}&pos=".($pos?1:0)."';
            uploadurl += '&minfiles=' + LEMval('{$aQuestionAttributes['min_num_of_files']}');
            uploadurl += '&maxfiles=' + LEMval('{$aQuestionAttributes['max_num_of_files']}');
            $('#upload_$ia[1]').attr('href',uploadurl);
        }
        var translt = {
             title: '" . $clang->gT('Upload your files','js') . "',
             returnTxt: '" . $clang->gT('Return to survey','js') . "',
             headTitle: '" . $clang->gT('Title','js') . "',
             headComment: '" . $clang->gT('Comment','js') . "',
             headFileName: '" . $clang->gT('File name','js') . "'
            };
        var imageurl =  '".Yii::app()->getConfig('imageurl')."';
        var uploadurl =  '".$scriptloc."';
    </script>\n";
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."modaldialog.js");

    // Modal dialog
    $answer .= $uploadbutton;

    $answer .= "<input type='hidden' id='".$ia[1]."' name='".$ia[1]."' value='".htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]],ENT_QUOTES,'utf-8')."' />";
    $answer .= "<input type='hidden' id='".$ia[1]."_filecount' name='".$ia[1]."_filecount' value=";

    if (array_key_exists($ia[1]."_filecount", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]))
    {
        $tempval = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]."_filecount"];
        if (is_numeric($tempval))
        {
            $answer .= $tempval . " />";
        }
        else
        {
            $answer .= "0 />";
        }
    }
    else {
        $answer .= "0 />";
    }

    $answer .= "<div id='".$ia[1]."_uploadedfiles'></div>";

    $answer .= '<script type="text/javascript">
    var surveyid = '.Yii::app()->getConfig('surveyID').';
    $(document).ready(function(){
    var fieldname = "'.$ia[1].'";
    var filecount = $("#"+fieldname+"_filecount").val();
    var json = $("#"+fieldname).val();
    var show_title = "'.$aQuestionAttributes["show_title"].'";
    var show_comment = "'.$aQuestionAttributes["show_comment"].'";
    var pos = "'.($pos ? 1 : 0).'";
    displayUploadedFiles(json, filecount, fieldname, show_title, show_comment, pos);
    });
    </script>';

    $answer .= '<script type="text/javascript">
    $(".basic_'.$ia[1].'").change(function() {
    var i;
    var jsonstring = "[";

    for (i = 1, filecount = 0; i <= LEMval("'.$aQuestionAttributes['max_num_of_files'].'"); i++)
    {
    if ($("#'.$ia[1].'_"+i).val() == "")
    continue;

    filecount++;
    if (i != 1)
    jsonstring += ", ";

    if ($("#answer'.$ia[1].'_"+i).val() != "")
    jsonstring += "{ ';

    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['show_title']))
        $answer .= '\"title\":\""+$("#'.$ia[1].'_title_"+i).val()+"\",';
    else
        $answer .= '\"title\":\"\",';

    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['show_comment']))
        $answer .= '\"comment\":\""+$("#'.$ia[1].'_comment_"+i).val()+"\",';
    else
        $answer .= '\"comment\":\"\",';

    $answer .= '\"size\":\"\",\"name\":\"\",\"ext\":\"\"}";
    }
    jsonstring += "]";

    $("#'.$ia[1].'").val(jsonstring);
    $("#'.$ia[1].'_filecount").val(filecount);
    });
    </script>';

    $inputnames[] = $ia[1];
    $inputnames[] = $ia[1]."_filecount";
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_multipleshorttext($ia)
{
    global $thissurvey;

    $clang = Yii::app()->lang;
    $extraclass ="";
    $answer='';
    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    if ($aQuestionAttributes['numbers_only']==1)
    {
        $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator = $sSeparator['separator'];
        $extraclass .=" numberonly";
        $checkconditionFunction = "fixnum_checkconditions";
    }
    else
    {
        $checkconditionFunction = "checkconditions";
    }
    if (intval(trim($aQuestionAttributes['maximum_chars']))>0)
    {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maximum_chars= intval(trim($aQuestionAttributes['maximum_chars']));
        $maxlength= "maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= "";
    }
    if (trim($aQuestionAttributes['text_input_width'])!='')
    {
        $tiwidth=$aQuestionAttributes['text_input_width'];
        $extraclass .=" inputwidth".trim($aQuestionAttributes['text_input_width']);
    }
    else
    {
        $tiwidth=20;
    }

    if (trim($aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='') {
        $prefix=$aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .=" withprefix";
    }
    else
    {
        $prefix = '';
    }

    if (trim($aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='') {
        $suffix=$aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .=" withsuffix";
    }
    else
    {
        $suffix = '';
    }

    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $kpclass = "text-keypad";
        $extraclass .=" inputkeypad";
    }
    else
    {
        $kpclass = "";
    }

    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    }
    else
    {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }

    $ansresult = dbExecuteAssoc($ansquery);    //Checked
    $aSubquestions = $ansresult->readAll();
    $anscount = count($aSubquestions)*2;
    //$answer .= "\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n";
    $fn = 1;

    $answer_main = '';

    $label_width = 0;

    if ($anscount==0)
    {
        $inputnames=array();
        $answer_main .= '    <li>'.$clang->gT('Error: This question has no answers.')."</li>\n";
    }
    else
    {
        if (trim($aQuestionAttributes['display_rows'])!='')
        {
            //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
            $drows=$aQuestionAttributes['display_rows'];

            foreach ($aSubquestions as $ansrow)
            {
                $myfname = $ia[1].$ansrow['title'];
                if ($ansrow['question'] == "")
                {
                    $ansrow['question'] = "&nbsp;";
                }

                //NEW: textarea instead of input=text field
                list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, '', $myfname, "li","question-item answer-item text-item".$extraclass);

                $answer_main .= "\t$htmltbody2\n"
                . "<label for=\"answer$myfname\">{$ansrow['question']}</label>\n"
                . "\t<span>\n".$prefix."\n".'
                <textarea class="textarea '.$kpclass.'" name="'.$myfname.'" id="answer'.$myfname.'"
                rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type);">';

                if($label_width < strlen(trim(strip_tags($ansrow['question']))))
                {
                    $label_width = strlen(trim(strip_tags($ansrow['question'])));
                }

                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
                {
                    $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                    if ($aQuestionAttributes['numbers_only']==1)
                    {
                        $dispVal = str_replace('.',$sSeparator,$dispVal);
                    }
                    $answer_main .= $dispVal;
                }

                $answer_main .= "</textarea>\n".$suffix."\n\t</span>\n"
                . "\t</li>\n";

                $fn++;
                $inputnames[]=$myfname;
            }

        }
        else
        {
            foreach ($aSubquestions as $ansrow)
            {
                $myfname = $ia[1].$ansrow['title'];
                if ($ansrow['question'] == "") {$ansrow['question'] = "&nbsp;";}

                // color code missing mandatory questions red
                if ($ia[6]=='Y' &&  $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] === '') {
                    $ansrow['question'] = "<span class='errormandatory'>{$ansrow['question']}</span>";
                }

                list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, '', $myfname, "li","question-item answer-item text-item".$extraclass);
                $answer_main .= "\t$htmltbody2\n"
                . "<label for=\"answer$myfname\">{$ansrow['question']}</label>\n"
                . "\t<span>\n".$prefix."\n".'<input class="text '.$kpclass.'" type="text" size="'.$tiwidth.'" name="'.$myfname.'" id="answer'.$myfname.'" value="';

                if($label_width < strlen(trim(strip_tags($ansrow['question']))))
                {
                    $label_width = strlen(trim(strip_tags($ansrow['question'])));
                }

                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
                {
                    $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                    if ($aQuestionAttributes['numbers_only']==1)
                    {
                        $dispVal = str_replace('.',$sSeparator,$dispVal);
                    }
                    $answer_main .= htmlspecialchars($dispVal,ENT_QUOTES,'UTF-8');
                }

                // --> START NEW FEATURE - SAVE
                $answer_main .= '" onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type);" '.$maxlength.' />'."\n".$suffix."\n\t</span>\n"
                . "\t</li>\n";
                // --> END NEW FEATURE - SAVE

                $fn++;
                $inputnames[]=$myfname;
            }

        }
    }

    $answer = "<ul class=\"subquestions-list questions-list text-list\">\n".$answer_main."</ul>\n";

    return array($answer, $inputnames);
}

// -----------------------------------------------------------------
// @todo: Can remove DB query by passing in answer list from EM
function do_multiplenumeric($ia)
{
    global $thissurvey;

    $clang = Yii::app()->lang;
    $extraclass ="";
    $checkconditionFunction = "fixnum_checkconditions";
    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    $answer='';
    $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeparator = $sSeparator['separator'];
    //Must turn on the "numbers only javascript"
    $extraclass .=" numberonly";
    if (intval(trim($aQuestionAttributes['maximum_chars']))>0)
    {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maximum_chars= intval(trim($aQuestionAttributes['maximum_chars']));
        $maxlength= "maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= " maxlength='25' ";
    }

    if (trim($aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='') {
        $prefix=$aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .=" withprefix";
    }
    else
    {
        $prefix = '';
    }

    if (trim($aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='') {
        $suffix=$aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .=" withsuffix";
    }
    else
    {
        $suffix = '';
    }

    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $kpclass = "num-keypad";
        $extraclass .=" keypad";
    }
    else
    {
        $kpclass = "";
    }

    $numbersonly_slider = ''; // DEPRECATED

    if (trim($aQuestionAttributes['text_input_width'])!='')
    {
        $tiwidth=$aQuestionAttributes['text_input_width'];
        $extraclass .=" inputwidth".trim($aQuestionAttributes['text_input_width']);
    }
    else
    {
        $tiwidth=10;
    }
    $prefixclass="numeric";
    if ($aQuestionAttributes['slider_layout']==1)
    {
        $prefixclass="slider";
        $slider_layout=true;
        $extraclass .=" withslider";
        $slider_step=trim(LimeExpressionManager::ProcessString("{{$aQuestionAttributes['slider_accuracy']}}",$ia[0],array(),false,1,1,false,false,true));
        $slider_step =  (is_numeric($slider_step))?$slider_step:1;
        $slider_min = trim(LimeExpressionManager::ProcessString("{{$aQuestionAttributes['slider_min']}}",$ia[0],array(),false,1,1,false,false,true));
        $slider_mintext = $slider_min =  (is_numeric($slider_min))?$slider_min:0;
        $slider_max = trim(LimeExpressionManager::ProcessString("{{$aQuestionAttributes['slider_max']}}",$ia[0],array(),false,1,1,false,false,true));
        $slider_maxtext = $slider_max =  (is_numeric($slider_max))?$slider_max:100;
        $slider_default=trim(LimeExpressionManager::ProcessString("{{$aQuestionAttributes['slider_default']}}",$ia[0],array(),false,1,1,false,false,true));
        $slider_default =  (is_numeric($slider_default))?$slider_default:"";

        if ($slider_default == '' && $aQuestionAttributes['slider_middlestart']==1)
        {
            $slider_middlestart = intval(($slider_max + $slider_min)/2);
        }
        else
        {
            $slider_middlestart = '';
        }

        $slider_separator= (trim($aQuestionAttributes['slider_separator'])!='')?$aQuestionAttributes['slider_separator']:"";
        $slider_reset=($aQuestionAttributes['slider_reset'])?1:0;
    }
    else
    {
        $slider_layout = false;
    }
    $hidetip=$aQuestionAttributes['hide_tip'];

    if ($aQuestionAttributes['random_order']==1)
    {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    }
    else
    {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }

    $ansresult = dbExecuteAssoc($ansquery);    //Checked
    $aSubquestions = $ansresult->readAll();
    $anscount = count($aSubquestions)*2;
    $fn = 1;

    $answer_main = '';

    if ($anscount==0)
    {
        $inputnames=array();
        $answer_main .= '    <li>'.$clang->gT('Error: This question has no answers.')."</li>\n";
    }
    else
    {
        foreach($aSubquestions as $ansrow)
        {
            $myfname = $ia[1].$ansrow['title'];
            if ($ansrow['question'] == "") {$ansrow['question'] = "&nbsp;";}
            if ($slider_layout === false || $slider_separator == '')
            {
                $theanswer = $ansrow['question'];
                $sliderleft='';
                $sliderright='';
            }
            else
            {
                $aAnswer=explode($slider_separator,$ansrow['question']);
                $theanswer=(isset($aAnswer[0]))?$aAnswer[0]:"";
                $sliderleft=(isset($aAnswer[1]))?$aAnswer[1]:"";
                $sliderright=(isset($aAnswer[2]))?$aAnswer[2]:"";
                $sliderleft="<div class=\"slider_lefttext\">$sliderleft</div>";
                $sliderright="<div class=\"slider_righttext\">$sliderright</div>";
            }

            // color code missing mandatory questions red
            if ($ia[6]=='Y' && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] === '')
            {
                $theanswer = "<span class='errormandatory'>{$theanswer}</span>";
            }

            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, '', $myfname, "li","question-item answer-item text-item numeric-item".$extraclass);
            $answer_main .= "\t$htmltbody2\n";
            $answer_main .= "<label for=\"answer$myfname\" class=\"{$prefixclass}-label\">{$theanswer}</label>\n";

                $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
                $sSeparator = $sSeparator['separator'];

                $answer_main .= "{$sliderleft}<span class=\"input\">\n\t".$prefix."\n\t<input class=\"text $kpclass\" type=\"text\" size=\"".$tiwidth."\" name=\"".$myfname."\" id=\"answer".$myfname."\" title=\"".$clang->gT('Only numbers may be entered in this field.')."\" value=\"";
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
                {
                    $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                    if(strpos($dispVal,"."))
                    {
                        $dispVal=rtrim(rtrim($dispVal,"0"),".");
                    }
                    $dispVal = str_replace('.',$sSeparator,$dispVal);
                    $answer_main .= $dispVal;
                }

                $answer_main .= '" onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type);" '." {$maxlength} />\n\t".$suffix."\n</span>{$sliderright}\n\t</li>\n";

            $fn++;
            $inputnames[]=$myfname;
        }
        if (trim($aQuestionAttributes['equals_num_value']) != ''
        || trim($aQuestionAttributes['min_num_value']) != ''
        || trim($aQuestionAttributes['max_num_value']) != ''
        )
        {
            $qinfo = LimeExpressionManager::GetQuestionStatus($ia[0]);
            if (trim($aQuestionAttributes['equals_num_value']) != '')
            {
                $answer_main .= "\t<li class='multiplenumerichelp help-item'>\n"
                . "<span class=\"label\">".$clang->gT('Remaining: ')."</span>\n"
                . "<span id=\"remainingvalue_{$ia[0]}\" class=\"dynamic_remaining\">$prefix\n"
                . "{" . $qinfo['sumRemainingEqn'] . "}\n"
                . "$suffix</span>\n"
                . "\t</li>\n";
            }

            $answer_main .= "\t<li class='multiplenumerichelp  help-item'>\n"
            . "<span class=\"label\">".$clang->gT('Total: ')."</span>\n"
            . "<span id=\"totalvalue_{$ia[0]}\" class=\"dynamic_sum\">$prefix\n"
            . "{" . $qinfo['sumEqn'] . "}\n"
            . "$suffix</span>\n"
            . "\t</li>\n";
        }
        $answer .= "<ul class=\"subquestions-list questions-list text-list {$prefixclass}-list\">\n".$answer_main."</ul>\n";
    }

    if($aQuestionAttributes['slider_layout']==1)
    {
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."numeric-slider.js");
        Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . "numeric-slider.css");
        if ($slider_default != "")
        {
            $slider_startvalue = $slider_default;
            $slider_displaycallout=1;
        }
        elseif ($slider_middlestart != '')
        {
            $slider_startvalue = $slider_middlestart;
            $slider_displaycallout=0;
        }
        else
        {
            $slider_startvalue = 'NULL';
            $slider_displaycallout=0;
        }
        $slider_showminmax=($aQuestionAttributes['slider_showminmax']==1)?1:0;
        //some var for slider
        $aJsLang=array(
            'reset' => $clang->gT('Reset'),
            'tip' => $clang->gT('Please click and drag the slider handles to enter your answer.'),
            );
        $aJsVar=array(
            'slider_showminmax'=>$slider_showminmax,
            'slider_min' => $slider_min,
            'slider_mintext'=>$slider_mintext,
            'slider_max' => $slider_max,
            'slider_maxtext'=>$slider_maxtext,
            'slider_step'=>$slider_step,
            'slider_startvalue'=>$slider_startvalue,
            'slider_displaycallout'=>$slider_displaycallout,
            'slider_prefix' => $prefix,
            'slider_suffix' => $suffix,
            'slider_reset' => $slider_reset,
            'lang'=> $aJsLang,
            );
        $answer .= "<script type='text/javascript'><!--\n"
                    . " doNumericSlider({$ia[0]},".ls_json_encode($aJsVar).");\n"
                    . " //--></script>";
    }
    $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeparator = $sSeparator['separator'];


    return array($answer, $inputnames);
}





// ---------------------------------------------------------------
function do_numerical($ia)
{
    global $thissurvey;

    $clang = Yii::app()->lang;
    $extraclass ="";
    $answertypeclass = "numeric";
    $checkconditionFunction = "fixnum_checkconditions";
    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    if (trim($aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='') {
        $prefix=$aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .=" withprefix";
    }
    else
    {
        $prefix = '';
    }
    if (trim($aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='') {
        $suffix=$aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .=" withsuffix";
    }
    else
    {
        $suffix = '';
    }
    if (intval(trim($aQuestionAttributes['maximum_chars']))>0 && intval(trim($aQuestionAttributes['maximum_chars']))<20)
    {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maximum_chars= intval(trim($aQuestionAttributes['maximum_chars']));
        $maxlength= " maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= " maxlength='20' ";
    }
    if (trim($aQuestionAttributes['text_input_width'])!='')
    {
        $tiwidth=$aQuestionAttributes['text_input_width'];
        $extraclass .=" inputwidth-".trim($aQuestionAttributes['text_input_width']);
    }
    else
    {
        $tiwidth=10;
    }

    if (trim($aQuestionAttributes['num_value_int_only'])==1)
    {
        $acomma="";
        $extraclass .=" integeronly";
        $answertypeclass .= " integeronly";
        $integeronly=1;
    }
    else
    {
        $acomma=getRadixPointData($thissurvey['surveyls_numberformat']);
        $acomma = $acomma['separator'];
        $integeronly=0;
    }

    $fValue=$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]];
    $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeparator = $sSeparator['separator'];
    // Fix the display value : Value is stored as decimal in SQL then return dot and 0 after dot. Seems only for numerical question type
    if(strpos($fValue,"."))
    {
        $fValue=rtrim(rtrim($fValue,"0"),".");
    }
    $fValue = str_replace('.',$sSeparator,$fValue);

    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $extraclass .=" inputkeypad";
        $answertypeclass .= " num-keypad";
    }
    else
    {
        $kpclass = "";
    }
    // --> START NEW FEATURE - SAVE
    $answer = "<p class='question answer-item text-item numeric-item {$extraclass}'>"
    . " <label for='answer{$ia[1]}' class='hide label'>{$clang->gT('Answer')}</label>\n$prefix\t"
    . "<input class='text {$answertypeclass}' type=\"text\" size=\"$tiwidth\" name=\"$ia[1]\"  title=\"".$clang->gT('Only numbers may be entered in this field.')."\" "
    . "id=\"answer{$ia[1]}\" value=\"{$fValue}\" onkeyup=\"{$checkconditionFunction}(this.value, this.name, this.type,'onchange',{$integeronly})\" "
    . " {$maxlength} />\t{$suffix}\n</p>\n";
    // --> END NEW FEATURE - SAVE

    $inputnames[]=$ia[1];
    $mandatory=null;
    return array($answer, $inputnames, $mandatory);
}




// ---------------------------------------------------------------
function do_shortfreetext($ia)
{
    global $thissurvey;

    $clang = Yii::app()->lang;
    $sGoogleMapsAPIKey = trim(Yii::app()->getConfig("googleMapsAPIKey"));
    if ($sGoogleMapsAPIKey!='')
    {
        $sGoogleMapsAPIKey='&key='.$sGoogleMapsAPIKey;
    }
    $extraclass ="";
    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    if ($aQuestionAttributes['numbers_only']==1)
    {
        $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator = $sSeparator['separator'];
        $extraclass .=" numberonly";
        $checkconditionFunction = "fixnum_checkconditions";
    }
    else
    {
        $checkconditionFunction = "checkconditions";
    }
    if (Yii::app()->db->driverName != 'mysql' && Yii::app()->db->driverName != 'mysqli' && (!intval(trim($aQuestionAttributes['maximum_chars'])>0) || intval(trim($aQuestionAttributes['maximum_chars'])>255)))
    {
        $aQuestionAttributes['maximum_chars']=255;
    }
    if (intval(trim($aQuestionAttributes['maximum_chars']))>0)
    {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maximum_chars= intval(trim($aQuestionAttributes['maximum_chars']));
        $maxlength= "maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= "";
    }
    if (trim($aQuestionAttributes['text_input_width'])!='')
    {
        $tiwidth=$aQuestionAttributes['text_input_width'];
        $extraclass .=" inputwidth-".trim($aQuestionAttributes['text_input_width']);
    }
    else
    {
        $tiwidth=50;
    }
    if (trim($aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='') {
        $prefix=$aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .=" withprefix";
    }
    else
    {
        $prefix = '';
    }
    if (trim($aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='') {
        $suffix=$aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .=" withsuffix";
    }
    else
    {
        $suffix = '';
    }
    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $kpclass = "text-keypad";
        $extraclass .=" inputkeypad";
    }
    else
    {
        $kpclass = "";
    }
    if (trim($aQuestionAttributes['display_rows'])!='')
    {
        //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
        $drows=$aQuestionAttributes['display_rows'];

        //if a textarea should be displayed we make it equal width to the long text question
        //this looks nicer and more continuous
        if($tiwidth == 50)
        {
            $tiwidth=40;
        }

        //NEW: textarea instead of input=text field

        // --> START NEW FEATURE - SAVE
        $answer ="<p class='question answer-item text-item {$extraclass}'><label for='answer{$ia[1]}' class='hide label'>{$clang->gT('Answer')}</label>"
        . '<textarea class="textarea '.$kpclass.'" name="'.$ia[1].'" id="answer'.$ia[1].'" '
        .'rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type);">';
        // --> END NEW FEATURE - SAVE

        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) {
            $dispVal = str_replace("\\", "", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]);
            if ($aQuestionAttributes['numbers_only']==1)
            {
                $dispVal = str_replace('.',$sSeparator,$dispVal);
            }
            $answer .= $dispVal;
        }

        $answer .= "</textarea></p>\n";
    }
    elseif((int)($aQuestionAttributes['location_mapservice'])!=0){
        $mapservice = $aQuestionAttributes['location_mapservice'];
        $currentLocation = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]];
        $currentLatLong = null;

        $floatLat = 0;
        $floatLng = 0;

        // Get the latitude/longtitude for the point that needs to be displayed by default
        if (strlen($currentLocation) > 2){
            $currentLatLong = explode(';',$currentLocation);
            $currentLatLong = array($currentLatLong[0],$currentLatLong[1]);
        }
        else{
            if ((int)($aQuestionAttributes['location_nodefaultfromip'])==0)
                $currentLatLong = getLatLongFromIp(getIPAddress());
            if (!isset($currentLatLong) || $currentLatLong==false){
                $floatLat = 0;
                $floatLng = 0;
                $LatLong = explode(" ",trim($aQuestionAttributes['location_defaultcoordinates']));

                if (isset($LatLong[0]) && isset($LatLong[1])){
                    $floatLat = $LatLong[0];
                    $floatLng = $LatLong[1];
                }

                $currentLatLong = array($floatLat,$floatLng);
            }
        }
        // 2 - city; 3 - state; 4 - country; 5 - postal
        $strBuild = "";
        if ($aQuestionAttributes['location_city'])
            $strBuild .= "2";
        if ($aQuestionAttributes['location_state'])
            $strBuild .= "3";
        if ($aQuestionAttributes['location_country'])
            $strBuild .= "4";
        if ($aQuestionAttributes['location_postal'])
            $strBuild .= "5";

        $currentLocation = $currentLatLong[0] . " " . $currentLatLong[1];
        $answer = "
        <script type=\"text/javascript\">
        zoom['$ia[1]'] = {$aQuestionAttributes['location_mapzoom']};
        </script>
        <div class=\"question answer-item geoloc-item {$extraclass}\">
        <input type=\"hidden\" name=\"$ia[1]\" id=\"answer$ia[1]\" value=\"{$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]}\">

        <input class=\"text location ".$kpclass."\" type=\"text\" size=\"20\" name=\"$ia[1]_c\"
        id=\"answer$ia[1]_c\" value=\"$currentLocation\"
        onchange=\"$checkconditionFunction(this.value, this.name, this.type)\" />

        <input type=\"hidden\" name=\"boycott_$ia[1]\" id=\"boycott_$ia[1]\"
        value = \"{$strBuild}\" >
        <input type=\"hidden\" name=\"mapservice_$ia[1]\" id=\"mapservice_$ia[1]\"
        class=\"mapservice\" value = \"{$aQuestionAttributes['location_mapservice']}\" >
        <div id=\"gmap_canvas_$ia[1]_c\" style=\"width: {$aQuestionAttributes['location_mapwidth']}px; height: {$aQuestionAttributes['location_mapheight']}px\"></div>
        </div>";
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."map.js");
        if ($aQuestionAttributes['location_mapservice']==1 && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off")
            Yii::app()->getClientScript()->registerScriptFile("https://maps.googleapis.com/maps/api/js?sensor=false$sGoogleMapsAPIKey");
        else if ($aQuestionAttributes['location_mapservice']==1)
            Yii::app()->getClientScript()->registerScriptFile("http://maps.googleapis.com/maps/api/js?sensor=false$sGoogleMapsAPIKey");
        elseif ($aQuestionAttributes['location_mapservice']==2)
            Yii::app()->getClientScript()->registerScriptFile("http://www.openlayers.org/api/OpenLayers.js");

        if (isset($aQuestionAttributes['hide_tip']) && $aQuestionAttributes['hide_tip']==0)
        {
            $answer .= "<div class=\"questionhelp\">"
            . $clang->gT('Drag and drop the pin to the desired location. You may also right click on the map to move the pin.').'</div>';
            $question_text['help'] = $clang->gT('Drag and drop the pin to the desired location. You may also right click on the map to move the pin.');
        }
    }
    else
    {
        //no question attribute set, use common input text field
        $answer = "<p class=\"question answer-item text-item {$extraclass}\">\n"
        ."<label for='answer{$ia[1]}' class='hide label'>{$clang->gT('Answer')}</label>"
        ."$prefix\t<input class=\"text $kpclass\" type=\"text\" size=\"$tiwidth\" name=\"$ia[1]\" id=\"answer$ia[1]\"";

        $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]];
        if ($aQuestionAttributes['numbers_only']==1)
        {
            $dispVal = str_replace('.',$sSeparator,$dispVal);
        }
        $dispVal = htmlspecialchars($dispVal,ENT_QUOTES,'UTF-8');
        $answer .= " value=\"$dispVal\"";

        $answer .=" {$maxlength} onkeyup=\"$checkconditionFunction(this.value, this.name, this.type)\"/>\n\t$suffix\n</p>\n";
    }

    if (trim($aQuestionAttributes['time_limit'])!='')
    {
        $answer .= return_timer_script($aQuestionAttributes, $ia, "answer".$ia[1]);
    }

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);

}

function getLatLongFromIp($sIPAddress){
    $ipInfoDbAPIKey = Yii::app()->getConfig("ipInfoDbAPIKey");
    $oXML = simplexml_load_file("http://api.ipinfodb.com/v3/ip-city/?key=$ipInfoDbAPIKey&ip=$sIPAddress&format=xml");
    if ($oXML->{'statusCode'} == "OK"){
        $lat = (float)$oXML->{'latitude'};
        $lng = (float)$oXML->{'longitude'};

        return(array($lat,$lng));
    }
    else
        return false;
}



// ---------------------------------------------------------------
function do_longfreetext($ia)
{
    global $thissurvey;
    $extraclass ="";


    $clang=Yii::app()->lang;

    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $kpclass = "text-keypad";
        $extraclass .=" inputkeypad";
    }
    else
    {
        $kpclass = "";
    }

    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    if (intval(trim($aQuestionAttributes['maximum_chars']))>0)
    {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maximum_chars= intval(trim($aQuestionAttributes['maximum_chars']));
        $maxlength= "maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= "";
    }

    // --> START ENHANCEMENT - DISPLAY ROWS
    if (trim($aQuestionAttributes['display_rows'])!='')
    {
        $drows=$aQuestionAttributes['display_rows'];
    }
    else
    {
        $drows=5;
    }
    // <-- END ENHANCEMENT - DISPLAY ROWS

    // --> START ENHANCEMENT - TEXT INPUT WIDTH
    if (trim($aQuestionAttributes['text_input_width'])!='')
    {
        $tiwidth=$aQuestionAttributes['text_input_width'];
        $extraclass .=" inputwidth-".trim($aQuestionAttributes['text_input_width']);
    }
    else
    {
        $tiwidth=40;
    }
    // <-- END ENHANCEMENT - TEXT INPUT WIDTH

    // --> START NEW FEATURE - SAVE
    $answer = "<p class='question answer-item text-item {$extraclass}'><label for='answer{$ia[1]}' class='hide label'>{$clang->gT('Answer')}</label>";
    $answer .='<textarea class="textarea '.$kpclass.'" name="'.$ia[1].'" id="answer'.$ia[1].'" alt="'.$clang->gT('Answer').'" '
    .'rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type)" >';
    // --> END NEW FEATURE - SAVE

    if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) {$answer .= str_replace("\\", "", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]);}

    $answer .= "</textarea></p>\n";

    if (trim($aQuestionAttributes['time_limit'])!='')
    {
        $answer .= return_timer_script($aQuestionAttributes, $ia, "answer".$ia[1]);
    }

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_hugefreetext($ia)
{
    global $thissurvey;
    $clang =Yii::app()->lang;
    $extraclass ="";
    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $kpclass = "text-keypad";
        $extraclass .=" inputkeypad";
    }
    else
    {
        $kpclass = "";
    }

    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    if (intval(trim($aQuestionAttributes['maximum_chars']))>0)
    {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maximum_chars= intval(trim($aQuestionAttributes['maximum_chars']));
        $maxlength= "maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= "";
    }

    // --> START ENHANCEMENT - DISPLAY ROWS
    if (trim($aQuestionAttributes['display_rows'])!='')
    {
        $drows=$aQuestionAttributes['display_rows'];
    }
    else
    {
        $drows=30;
    }
    // <-- END ENHANCEMENT - DISPLAY ROWS

    // --> START ENHANCEMENT - TEXT INPUT WIDTH
    if (trim($aQuestionAttributes['text_input_width'])!='')
    {
        $tiwidth=$aQuestionAttributes['text_input_width'];
        $extraclass .=" inputwidth-".trim($aQuestionAttributes['text_input_width']);
    }
    else
    {
        $tiwidth=70;
    }
    // <-- END ENHANCEMENT - TEXT INPUT WIDTH

    // --> START NEW FEATURE - SAVE
    $answer = "<p class=\"question answer-item text-item {$extraclass}\"><label for='answer{$ia[1]}' class='hide label'>{$clang->gT('Answer')}</label>";
    $answer .='<textarea class="textarea '.$kpclass.'" name="'.$ia[1].'" id="answer'.$ia[1].'" '
    .'rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type)" >';
    // --> END NEW FEATURE - SAVE

    if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) {$answer .= str_replace("\\", "", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]);}
    $answer .= "</textarea>\n";
    $answer .="</p>";
    if (trim($aQuestionAttributes['time_limit']) != '')
    {
        $answer .= return_timer_script($aQuestionAttributes, $ia, "answer".$ia[1]);
    }

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_yesno($ia)
{
    $clang = Yii::app()->lang;

    $checkconditionFunction = "checkconditions";

    $answer = "<ul class=\"answers-list radio-list\">\n"
    . "\t<li class=\"answer-item radio-item\">\n<input class=\"radio\" type=\"radio\" name=\"{$ia[1]}\" id=\"answer{$ia[1]}Y\" value=\"Y\"";

    if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == 'Y')
    {
        $answer .= CHECKED;
    }
    // --> START NEW FEATURE - SAVE
    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$ia[1]}Y\" class=\"answertext\">\n\t".$clang->gT('Yes')."\n</label>\n\t</li>\n"
    . "\t<li class=\"answer-item radio-item\">\n<input class=\"radio\" type=\"radio\" name=\"{$ia[1]}\" id=\"answer{$ia[1]}N\" value=\"N\"";
    // --> END NEW FEATURE - SAVE

    if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == 'N')
    {
        $answer .= CHECKED;
    }
    // --> START NEW FEATURE - SAVE
    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$ia[1]}N\" class=\"answertext\" >\n\t".$clang->gT('No')."\n</label>\n\t</li>\n";
    // --> END NEW FEATURE - SAVE
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
    {
        $answer .= "\t<li class=\"answer-item radio-item noanswer-item\">\n<input class=\"radio\" type=\"radio\" name=\"{$ia[1]}\" id=\"answer{$ia[1]}\" value=\"\"";
        if (empty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]))
        {
            $answer .= CHECKED;
        }
        // --> START NEW FEATURE - SAVE
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$ia[1]}\" class=\"answertext\">\n\t".$clang->gT('No answer')."\n</label>\n\t</li>\n";
        // --> END NEW FEATURE - SAVE
    }

    $answer .= "</ul>\n\n<input type=\"hidden\" name=\"java{$ia[1]}\" id=\"java{$ia[1]}\" value=\"".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]."\" />\n";
    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_gender($ia)
{
    $clang = Yii::app()->lang;

    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    $answer = "<ul class=\"answers-list radio-list\">\n"
    . "\t<li class=\"answer-item radio-item\">\n"
    . '        <input class="radio" type="radio" name="'.$ia[1].'" id="answer'.$ia[1].'F" value="F"';
    if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == 'F')
    {
        $answer .= CHECKED;
    }
    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
    . '        <label for="answer'.$ia[1].'F" class="answertext">'.$clang->gT('Female')."</label>\n\t</li>\n";

    $answer .= "\t<li class=\"answer-item radio-item\">\n<input class=\"radio\" type=\"radio\" name=\"$ia[1]\" id=\"answer".$ia[1].'M" value="M"';

    if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == 'M')
    {
        $answer .= CHECKED;
    }
    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer$ia[1]M\" class=\"answertext\">".$clang->gT('Male')."</label>\n\t</li>\n";

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
    {
        $answer .= "\t<li class=\"answer-item radio-item noanswer-item\">\n<input class=\"radio\" type=\"radio\" name=\"$ia[1]\" id=\"answer".$ia[1].'" value=""';
        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '')
        {
            $answer .= CHECKED;
        }
        // --> START NEW FEATURE - SAVE
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer$ia[1]\" class=\"answertext\">".$clang->gT('No answer')."</label>\n\t</li>\n";
        // --> END NEW FEATURE - SAVE

    }
    $answer .= "</ul>\n\n<input type=\"hidden\" name=\"java$ia[1]\" id=\"java$ia[1]\" value=\"".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]."\" />\n";

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
/**
* DONE: well-formed valid HTML is appreciated
* Enter description here...
* @param $ia
* @return unknown_type
*/
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array_5point($ia)
{
    global $thissurvey;
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $extraclass ="";
    $clang = Yii::app()->lang;
    $caption=$clang->gT("An array with sub-question on each line. The answers are value from 1 to 5 and are contained in the table header. ");
    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    if (trim($aQuestionAttributes['answer_width'])!='')
    {
        $answerwidth=$aQuestionAttributes['answer_width'];
        $extraclass .=" answerwidth-".trim($aQuestionAttributes['answer_width']);
    }
    else
    {
        $answerwidth = 20;
    }
    $cellwidth  = 5; // number of columns

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        ++$cellwidth; // add another column
    }
    $cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

    $sQuery = "SELECT question FROM {{questions}} WHERE parent_qid=".$ia[0]." AND question like '%|%'";
    $iCount = Yii::app()->db->createCommand($sQuery)->queryScalar();

    if ($iCount>0) {
        $right_exists=true;
        $answerwidth=$answerwidth/2;
    } else {
        $right_exists=false;
    }
    // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column


    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    }
    else
    {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }

    $ansresult = dbExecuteAssoc($ansquery);     //Checked
    $aSubquestions = $ansresult->readAll();
    $anscount = count($aSubquestions);

    $fn = 1;
    $answer = "\n<table class=\"question subquestion-list questions-list {$extraclass}\" summary=\"{$caption}\">\n"
    . "\t<colgroup class=\"col-responses\">\n"
    . "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";
    $odd_even = '';

    for ($xc=1; $xc<=5; $xc++)
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
    }
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
    }
    $answer .= "\t</colgroup>\n\n"
    . "\t<thead>\n<tr class=\"array1 dontread\">\n"
    . "\t<td>&nbsp;</td>\n";
    for ($xc=1; $xc<=5; $xc++)
    {
        $answer .= "\t<th>$xc</th>\n";
    }
    if ($right_exists) {$answer .= "\t<td width='$answerwidth%'>&nbsp;</td>\n";}
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
    }
    $answer .= "</tr></thead>\n";

    $answer_t_content = '<tbody>';
    $trbc = '';
    $n=0;
    //return array($answer, $inputnames);
    foreach ($aSubquestions as $ansrow)
    {
        $myfname = $ia[1].$ansrow['title'];

        $answertext = $ansrow['question'];
        if (strpos($answertext,'|')) {$answertext=substr($answertext,0,strpos($answertext,'|'));}

        /* Check if this item has not been answered */
        if ($ia[6]=='Y' && in_array($myfname,$aMandatoryViolationSubQ))
        {
            $answertext = "<span class=\"errormandatory\">{$answertext}</span>";
        }

        $trbc = alternation($trbc , 'row');

        // Get array_filter stuff
        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc answers-list radio-list");

        $answer_t_content .= $htmltbody2
        . "\t<th class=\"answertext\" width=\"$answerwidth%\">\n$answertext\n"
        . $hiddenfield
        . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $answer_t_content .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
        }
        $answer_t_content .= "\" />\n\t</th>\n";
        for ($i=1; $i<=5; $i++)
        {
            $answer_t_content .= "\t<td class=\"answer_cell_00$i answer-item radio-item\">\n"
            . "<label class=\"hide read\" for=\"answer$myfname-$i\">{$i}</label>\n"
            ."\n\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-$i\" value=\"$i\"";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $i)
            {
                $answer_t_content .= CHECKED;
            }
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</td>\n";
        }

        $answertext2 = $ansrow['question'];
        if (strpos($answertext2,'|'))
        {
            $answertext2=substr($answertext2,strpos($answertext2,'|')+1);
            $answer_t_content .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">$answertext2</td>\n";
        }
        elseif ($right_exists)
        {
            $answer_t_content .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">&nbsp;</td>\n";
        }


        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            $answer_t_content .= "\t<td class=\"answer-item radio-item noanswer-item\">\n"
            ."<label class=\"hide read\" for=\"answer$myfname-\">".$clang->gT('No answer')."</label>"
            ."\n\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" ";
            if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
            {
                $answer_t_content .= CHECKED;
            }
            $answer_t_content .= " onclick='$checkconditionFunction(this.value, this.name, this.type)'  />\n</td>\n";
        }

        $answer_t_content .= "</tr>\n";
        $fn++;
        $inputnames[]=$myfname;
    }

    $answer .= $answer_t_content . "\n</tbody>\t</table>\n";
    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
/**
* DONE: well-formed valid HTML is appreciated
* Enter description here...
* @param $ia
* @return unknown_type
*/
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array_10point($ia)
{
    global $thissurvey;
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $extraclass ="";
    $clang = Yii::app()->lang;
    $caption=$clang->gT("An array with sub-question on each line. The answers are value from 1 to 10 and are contained in the table header. ");
    $checkconditionFunction = "checkconditions";

    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]."  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."'";
    $other = Yii::app()->db->createCommand($qquery)->queryScalar(); //Checked

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    if (trim($aQuestionAttributes['answer_width'])!='')
    {
        $answerwidth=$aQuestionAttributes['answer_width'];
    }
    else
    {
        $answerwidth = 20;
    }
    $cellwidth  = 10; // number of columns
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        ++$cellwidth; // add another column
        $caption.=$clang->gT("The last cell are for no answer. ");
    }
    $cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    }
    else
    {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }
    $ansresult = dbExecuteAssoc($ansquery);   //Checked
    $aSubquestions = $ansresult->readAll();
    $anscount = count($aSubquestions);

    $fn = 1;
    $answer = "\n<table class=\"question subquestion-list questions-list {$extraclass}\" summary=\"{$caption}\">\n"
    . "\t<colgroup class=\"col-responses\">\n"
    . "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";

    $odd_even = '';
    for ($xc=1; $xc<=10; $xc++)
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
    }
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
    }
    $answer .= "\t</colgroup>\n\n"
    . "\t<thead>\n<tr class=\"array1 dontread\">\n"
    . "\t<td>&nbsp;</td>\n";
    for ($xc=1; $xc<=10; $xc++)
    {
        $answer .= "\t<th>$xc</th>\n";
    }
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
    }
    $answer .= "</tr>\n</thead>";
    $answer_t_content = '<tbody>';
    $trbc = '';
    foreach ($aSubquestions as $ansrow)
    {
        $myfname = $ia[1].$ansrow['title'];
        $answertext = $ansrow['question'];
        /* Check if this item has not been answered */
        if ($ia[6]=='Y' && in_array($myfname, $aMandatoryViolationSubQ) )
        {
            $answertext = "<span class='errormandatory'>{$answertext}</span>";
        }
        $trbc = alternation($trbc , 'row');

        //Get array filter stuff
        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc answers-list radio-list");

        $answer_t_content .= $htmltbody2
        . "\t<th class=\"answertext\">\n$answertext\n"
        . $hiddenfield
        . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $answer_t_content .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
        }
        $answer_t_content .= "\" />\n\t</th>\n";

        for ($i=1; $i<=10; $i++)
        {
            $answer_t_content .= "\t<td class=\"answer_cell_00$i answer-item radio-item\">\n"
            ."<label class=\"hide read\" for=\"answer$myfname-$i\">{$i}</label>\n"
            ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-$i\" value=\"$i\"";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $i)
            {
                $answer_t_content .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n\t</td>\n";
            // --> END NEW FEATURE - SAVE
        }
        if ($ia[6] != "Y" && SHOW_NO_ANSWER == 1)
        {
            $answer_t_content .= "\t<td class=\"answer-item radio-item noanswer-item\">\n"
            ."<label class=\"hide read\" for=\"answer$myfname-\">".$clang->gT('No answer')."</label>"
            ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" ";
            if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
            {
                $answer_t_content .= CHECKED;
            }
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n\t</td>\n";

        }
        $answer_t_content .= "</tr>\n";
        $inputnames[]=$myfname;
        $fn++;
    }
    $answer .=  $answer_t_content . "\t\n</tbody>\n</table>\n";
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array_yesnouncertain($ia)
{
    global $thissurvey;
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $extraclass ="";
    $clang = Yii::app()->lang;
    $caption=$clang->gT("An array with sub-question on each line. The answers are yes, no, uncertain and are in the table header. ");
    $checkconditionFunction = "checkconditions";

    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."'";
    $qresult = dbExecuteAssoc($qquery);    //Checked
    $qrow = $qresult->readAll();
    $other = isset($qrow['other']) ? $qrow['other'] : '';
    $aQuestionAttributes=getQuestionAttributeValues($ia[0],$ia[4]);
    if (trim($aQuestionAttributes['answer_width'])!='')
    {
        $answerwidth=$aQuestionAttributes['answer_width'];
    }
    else
    {
        $answerwidth = 20;
    }
    $cellwidth  = 3; // number of columns
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        ++$cellwidth; // add another column
        $caption.=$clang->gT("The last cell are for no answer. ");
    }
    $cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    }
    else
    {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }
    $ansresult = dbExecuteAssoc($ansquery);    //Checked
    $aSubquestions = $ansresult->readAll();
    $anscount = count($aSubquestions);
    $fn = 1;
    $answer = "\n<table class=\"question subquestions-list questions-list {$extraclass}\" summary=\"{$caption}\">\n"
    . "\t<colgroup class=\"col-responses\">\n"
    . "\n\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";
    $odd_even = '';
    for ($xc=1; $xc<=3; $xc++)
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
    }
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
    }
    $answer .= "\t</colgroup>\n\n"
    . "\t<thead>\n<tr class=\"array1\">\n"
    . "\t<td>&nbsp;</td>\n"
    . "\t<th class=\"dontread\">".$clang->gT('Yes')."</th>\n"
    . "\t<th class=\"dontread\">".$clang->gT('Uncertain')."</th>\n"
    . "\t<th class=\"dontread\">".$clang->gT('No')."</th>\n";
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $answer .= "\t<th class=\"dontread\">".$clang->gT('No answer')."</th>\n";
    }
    $answer .= "</tr>\n\t</thead>";
    $answer_t_content = '<tbody>';
    if ($anscount==0)
    {
        $inputnames=array();
        $answer.="<tr>\t<th class=\"answertext\">".$clang->gT('Error: This question has no answers.')."</th>\n</tr>\n";
    }
    else
    {
        $trbc = '';
        foreach($aSubquestions as $ansrow)
        {
            $myfname = $ia[1].$ansrow['title'];
            $answertext = $ansrow['question'];
            /* Check the sub question mandatory violation */
            if ($ia[6]=='Y' && in_array($myfname, $aMandatoryViolationSubQ))
            {
                $answertext = "<span class='errormandatory'>{$answertext}</span>";
            }
            $trbc = alternation($trbc , 'row');

            // Get array_filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc answers-list radio-list");

            $answer_t_content .= $htmltbody2;

            $answer_t_content .= "\t<th class=\"answertext\">\n"
            . $hiddenfield
            . "\t\t\t\t$answertext</th>\n"
            . "\t<td class=\"answer_cell_Y answer-item radio-item\">\n"
            . "<label class=\"hide read\" for=\"answer$myfname-Y\">".$clang->gT('Yes')."</label>\n"
            . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-Y\" value=\"Y\" ";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'Y')
            {
                $answer_t_content .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n\t</td>\n"
            . "\t<td class=\"answer_cell_U answer-item radio-item\">\n"
            . "<label class=\"hide read\" for=\"answer$myfname-U\">".$clang->gT('Uncertain')."</label>\n"
            . "<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-U\" value=\"U\" ";
            // --> END NEW FEATURE - SAVE

            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'U')
            {
                $answer_t_content .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n\t</td>\n"
            . "\t<td class=\"answer_cell_N answer-item radio-item\">\n"
            . "<label class=\"hide read\" for=\"answer$myfname-N\">".$clang->gT('No')."</label>\n"
            . "<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-N\" value=\"N\" ";
            // --> END NEW FEATURE - SAVE

            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'N')
            {
                $answer_t_content .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            // --> END NEW FEATURE - SAVE
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                $answer_t_content .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            }
            $answer_t_content .= "\" />\n\t</td>\n";

            if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
            {
                $answer_t_content .= "\t<td class=\"answer-item radio-item noanswer-item\">\n"
                . "\t<label class=\"hide read\" for=\"answer$myfname-\">".$clang->gT('No answer')."</label>\n"
                . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" ";
                if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
                {
                    $answer_t_content .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";
                // --> END NEW FEATURE - SAVE
            }
            $answer_t_content .= "</tr>";
            $inputnames[]=$myfname;
            $fn++;
        }
    }
    $answer .=  $answer_t_content . "\t\n</tbody>\n</table>\n";
    return array($answer, $inputnames);
}

// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array_increasesamedecrease($ia)
{
    global $thissurvey;
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $extraclass ="";
    $clang = Yii::app()->lang;
    $caption=$clang->gT("An array with sub-question on each line. The answers are increase, same, decrease and are contained in the table header. ");
    $checkconditionFunction = "checkconditions";

    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."'";
    $qresult = dbExecuteAssoc($qquery);   //Checked
    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    if (trim($aQuestionAttributes['answer_width'])!='')
    {
        $answerwidth=$aQuestionAttributes['answer_width'];
    }
    else
    {
        $answerwidth = 20;
    }
    $cellwidth  = 3; // number of columns
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        ++$cellwidth; // add another column
        $caption.=$clang->gT("The last cell are for no answer. ");
    }
    $cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

    foreach($qresult->readAll() as $qrow)
    {
        $other = $qrow['other'];
    }
    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    }
    else
    {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }
    $ansresult = dbExecuteAssoc($ansquery);  //Checked
    $aSubquestions = $ansresult->readAll();
    $anscount = count($aSubquestions);

    $fn = 1;

    $answer = "\n<table class=\"question subquestions-list questions-list {$extraclass}\" summary=\"{$caption}\">\n"
    . "\t<colgroup class=\"col-responses\">\n"
    . "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";

    $odd_even = '';
    for ($xc=1; $xc<=3; $xc++)
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
    }
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
    }
    $answer .= "\t</colgroup>\n"
    . "\t<thead>\n"
    . "<tr class=\"dontread\">\n"
    . "\t<td>&nbsp;</td>\n"
    . "\t<th>".$clang->gT('Increase')."</th>\n"
    . "\t<th>".$clang->gT('Same')."</th>\n"
    . "\t<th>".$clang->gT('Decrease')."</th>\n";
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
    }
    $answer .= "</tr>\n"
    ."\t</thead>\n";
    $answer_body = '<tbody>';
    $trbc = '';
    foreach($aSubquestions as $ansrow)
    {
        $myfname = $ia[1].$ansrow['title'];
        $answertext = $ansrow['question'];
        /* Check the sub Q mandatory violation */
        if ($ia[6]=='Y' && in_array($myfname, $aMandatoryViolationSubQ))
        {
            $answertext = "<span class=\"errormandatory\">{$answertext}</span>";
        }

        $trbc = alternation($trbc , 'row');

        // Get array_filter stuff
        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,'tr',"$trbc answers-list radio-list");

        $answer_body .= $htmltbody2;

        $answer_body .= "\t<th class=\"answertext\">\n"
        . "$answertext\n"
        . $hiddenfield
        . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $answer_body .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
        }
        $answer_body .= "\" />\n\t</th>\n";

        $answer_body .= "\t<td class=\"answer_cell_I answer-item radio-item\">\n"
        . "<label class=\"hide read\" for=\"answer$myfname-I\">".$clang->gT('Increase')."</label>\n"
        ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-I\" value=\"I\" ";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'I')
        {
            $answer_body .= CHECKED;
        }
        $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
        . "\t</td>\n"
        . "\t<td class=\"answer_cell_S answer-item radio-item\">\n"
        . "<label class=\"hide read\" for=\"answer$myfname-S\">".$clang->gT('Same')."</label>\n"
        . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-S\" value=\"S\" ";

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'S')
        {
            $answer_body .= CHECKED;
        }

        $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
        . "\t</td>\n"
        . "\t<td class=\"answer_cell_D answer-item radio-item\">\n"
        . "<label class=\"hide read\" for=\"answer$myfname-D\">".$clang->gT('Decrease')."</label>\n"
        . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-D\" value=\"D\" ";
        // --> END NEW FEATURE - SAVE
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'D')
        {
            $answer_body .= CHECKED;
        }

        $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
        . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {$answer_body .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];}
        $answer_body .= "\" />\n\t</td>\n";

        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            $answer_body .= "\t<td class=\"answer-item radio-item noanswer-item\">\n"
            . "<label class=\"hide read\" for=\"answer$myfname-\">".$clang->gT('No answer')."</label>\n"
            . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" ";
            if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
            {
                $answer_body .= CHECKED;
            }
            $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
            . "\t</td>\n";
        }
        $answer_body .= "</tr>\n";
        $inputnames[]=$myfname;
        $fn++;
    }
    $answer .=  $answer_body . "\t</tbody>\n</table>\n";
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array($ia)
{
    global $thissurvey;
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $repeatheadings = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
    $extraclass ="";
    $clang = Yii::app()->lang;
    $caption="";// Just leave empty, are replaced after
    $checkconditionFunction = "checkconditions";
    $qquery = "SELECT other FROM {{questions}} WHERE qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."'";
    $other = Yii::app()->db->createCommand($qquery)->queryScalar(); //Checked

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    if (trim($aQuestionAttributes['answer_width'])!='')
    {
        $answerwidth=$aQuestionAttributes['answer_width'];
    }
    else
    {
        $answerwidth=20;
    }
    $columnswidth=100-$answerwidth;

    if ($aQuestionAttributes['use_dropdown'] == 1)
    {
        $useDropdownLayout = true;
        $extraclass .=" dropdown-list";
        $caption=$clang->gT("An array with sub-question on each line. You have to select your answer.");
    }
    else
    {
        $useDropdownLayout = false;
        $caption=$clang->gT("An array with sub-question on each line. The answers are contained in the table header. ");
    }
    if(ctype_digit(trim($aQuestionAttributes['repeat_headings'])) && trim($aQuestionAttributes['repeat_headings']!=""))
    {
        $repeatheadings = intval($aQuestionAttributes['repeat_headings']);
        $minrepeatheadings = 0;
    }

    $lresult= Answer::model()->findAll(array('order'=>'sortorder, code', 'condition'=>'qid=:qid AND language=:language AND scale_id=0', 'params'=>array(':qid'=>$ia[0],':language'=>$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang'])));
    $labelans=array();
    $labelcode=array();
    foreach ($lresult as $lrow)
    {
        $labelans[]=$lrow->answer;
        $labelcode[]=$lrow->code;
    }
    if ($useDropdownLayout === false && count($lresult) > 0)
    {
        $sQuery = "SELECT count(qid) FROM {{questions}} WHERE parent_qid={$ia[0]} AND question like '%|%' ";
        $iCount = Yii::app()->db->createCommand($sQuery)->queryScalar();

        if ($iCount>0) {
            $right_exists=true;
            $answerwidth=$answerwidth/2;
        }
        else
        {
            $right_exists=false;
        }
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        }
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }
        $ansresult = dbExecuteAssoc($ansquery); //Checked
        $aQuestions=$ansresult->readAll();
        $anscount = count($aQuestions);
        $fn=1;

        $numrows = count($labelans);
        if ($right_exists)
        {
            ++$numrows;
            $caption.=$clang->gT("After answers, a cell give some information. ");
        }
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            ++$numrows;
            $caption.=$clang->gT("The last cell are for no answer. ");
        }
        $cellwidth = round( ($columnswidth / $numrows ) , 1 );

        $answer_start = "\n<table class=\"question subquestions-list questions-list {$extraclass}\" summary=\"{$caption}\">\n";
        $answer_head_line= "\t<td>&nbsp;</td>\n";
            foreach ($labelans as $ld)
            {
                $answer_head_line .= "\t<th>".$ld."</th>\n";
            }
            if ($right_exists) {$answer_head_line .= "\t<td>&nbsp;</td>\n";}
            if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory and we can show "no answer"
            {
                $answer_head_line .= "\t<th>".$clang->gT('No answer')."</th>\n";
            }
        $answer_head = "\t<thead><tr class=\"dontread\">\n".$answer_head_line."</tr></thead>\n\t\n";

        $answer = '<tbody>';
        $trbc = '';
        $inputnames=array();
        foreach($aQuestions as $ansrow)
        {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
            {
                if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                {
                    $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                    $answer .= "<tr class=\"dontread repeat headings\">{$answer_head_line}</tr>";
                }
            }
            $myfname = $ia[1].$ansrow['title'];
            $answertext = $ansrow['question'];
            $answertextsave=$answertext;
            if (strpos($answertext,'|'))
            {
                $answertext=substr($answertext,0, strpos($answertext,'|'));
            }
            if (strpos($answertext,'|')) {$answerwidth=$answerwidth/2;}
            /* Check the mandatory sub Q violation */
            if (in_array($myfname, $aMandatoryViolationSubQ))
            {
                $answertext = '<span class="errormandatory">'.$answertext.'</span>';
            }
            // Get array_filter stuff
            //
            // TMSW - is this correct?
            $trbc = alternation($trbc , 'row');
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc answers-list radio-list");
            $fn++;
            $answer .= $htmltbody2;

            $answer .= "\t<th class=\"answertext\">\n$answertext"
            . $hiddenfield
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                $answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            }
            $answer .= "\" />\n\t</th>\n";

            $thiskey=0;
            foreach ($labelcode as $ld)
            {
                $answer .= "\t\t\t<td class=\"answer_cell_00$ld answer-item radio-item\">\n"
                . "<label class=\"hide read\" for=\"answer$myfname-$ld\">{$labelans[$thiskey]}</label>\n"
                . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"$ld\" id=\"answer$myfname-$ld\" title=\""
                . HTMLEscape(strip_tags($labelans[$thiskey])).'"';
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $ld)
                {
                    $answer .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
                . "\t</td>\n";
                // --> END NEW FEATURE - SAVE

                $thiskey++;
            }
            if (strpos($answertextsave,'|'))
            {
                $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                $answer .= "\t<th class=\"answertextright\">$answertext</th>\n";
            }
            elseif ($right_exists)
            {
                $answer .= "\t<td class=\"answertextright\">&nbsp;</td>\n";
            }

            if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
            {
                $answer .= "\t<td class=\"answer-item radio-item noanswer-item\">\n"
                ."<label class=\"hide read\" for=\"answer$myfname-\">".$clang->gT('No answer')."</label>\n"
                ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"\" id=\"answer$myfname-\" ";
                if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
                {
                    $answer .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\"  />\n\t</td>\n";
                // --> END NEW FEATURE - SAVE
            }

            $answer .= "</tr>\n";
            $inputnames[]=$myfname;
            //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
        }
        $answer .= "</tbody>\n";
        $answer_cols = "\t<colgroup class=\"col-responses\">\n"
        ."\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n" ;

        $odd_even = '';
        foreach ($labelans as $c)
        {
            $odd_even = alternation($odd_even);
            $answer_cols .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        if ($right_exists)
        {
            $odd_even = alternation($odd_even);
            $answer_cols .= "<col class=\"answertextright $odd_even\" width=\"$answerwidth%\" />\n";
        }
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
        {
            $odd_even = alternation($odd_even);
            $answer_cols .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
        }
        $answer_cols .= "\t</colgroup>\n";

        $answer = $answer_start . $answer_cols . $answer_head .$answer ."</table>\n";
    }
    elseif ($useDropdownLayout === true && count($lresult)> 0)
    {
        foreach($lresult as $lrow)
            $labels[]=Array('code' => $lrow->code,
            'answer' => $lrow->answer);
        $sQuery = "SELECT count(question) FROM {{questions}} WHERE parent_qid={$ia[0]} AND question like '%|%' ";
        $iCount = Yii::app()->db->createCommand($sQuery)->queryScalar();
        if ($iCount>0) {
            $right_exists=true;
            $answerwidth=$answerwidth/2;
        } else {
            $right_exists=false;
        }
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        }
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }
        $ansresult = dbExecuteAssoc($ansquery); //Checked
        $aQuestions = $ansresult->readAll();
        $anscount = count($aQuestions);
        $fn=1;

        $numrows = count($labels);
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            ++$numrows;
        }
        if ($right_exists)
        {
            ++$numrows;
        }
        $cellwidth = round( ($columnswidth / $numrows ) , 1 );

        $answer_start = "\n<table class=\"question subquestions-list questions-list {$extraclass}\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an array type question\" >\n";

        $answer = "\t<tbody>\n";
        $trbc = '';
        $inputnames=array();

        foreach ($aQuestions as $ansrow)
        {
            $myfname = $ia[1].$ansrow['title'];
            $trbc = alternation($trbc , 'row');
            $answertext=$ansrow['question'];
            $answertextsave=$answertext;
            if (strpos($answertext,'|'))
            {
                $answertext=substr($answertext,0, strpos($answertext,'|'));
            }
            if (strpos($answertext,'|')) {$answerwidth=$answerwidth/2;}

            if ($ia[6]=='Y' && in_array($myfname, $aMandatoryViolationSubQ))
            {
                $answertext = '<span class="errormandatory">'.$answertext.'</span>';
            }
            // Get array_filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc question-item answer-item dropdown-item");
            $answer .= $htmltbody2;

            $answer .= "\t<th class=\"answertext\">\n$answertext"
            . $hiddenfield
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                $answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            }
            $answer .= "\" />\n\t</th>\n";

            $answer .= "\t<td >\n"
            . "<select name=\"$myfname\" id=\"answer$myfname\" onchange=\"$checkconditionFunction(this.value, this.name, this.type);\">\n";

            // Dropdown representation is en exception - even if mandatory or  SHOW_NO_ANSWER is disable a neutral option needs to be shown where the mandatory case asks actively
            if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
            {
                $sOptionText=$clang->gT('No answer');
            }
            else
            {
                $sOptionText=$clang->gT('Please choose...');
            }
            $answer .= "\t<option value=\"\" ";
            if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
            {
                $answer .= SELECTED;
            }
            $answer .= '>'.$sOptionText."</option>\n";
            foreach ($labels as $lrow)
            {
                $answer .= "\t<option value=\"".$lrow['code'].'" ';
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $lrow['code'])
                {
                    $answer .= SELECTED;
                }
                $answer .= '>'.flattenText($lrow['answer'])."</option>\n";
            }
            $answer .= "</select>\n";

            if (strpos($answertextsave,'|'))
            {
                $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                $answer .= "\t<th class=\"answertextright\">$answertext</th>\n";
            }
            elseif ($right_exists)
            {
                $answer .= "\t<td class=\"answertextright\">&nbsp;</td>\n";
            }

            $answer .= "</tr>\n";
            $inputnames[]=$myfname;
            //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
            $fn++;
        }
        $answer .= "\t</tbody>";
        $answer = $answer_start . $answer . "\n</table>\n";
    }
    else
    {
        $answer = "\n<p class=\"error\">".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames='';
    }
    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array_multitext($ia)
{
    global $thissurvey;
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $repeatheadings = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
    $extraclass ="";
    $clang = Yii::app()->lang;
    $caption=$clang->gT("An array of sub-question on each cell. The sub-question text are in the table header and concerns line header. ");
    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $kpclass = "text-keypad";
    }
    else
    {
        $kpclass = "";
    }

    $checkconditionFunction = "checkconditions";
    $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeparator = $sSeparator['separator'];

    $defaultvaluescript = "";
    $qquery = "SELECT other FROM {{questions}} WHERE qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."'";
    $other = Yii::app()->db->createCommand($qquery)->queryScalar(); //Checked


    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    $show_grand = $aQuestionAttributes['show_grand_total'];
    $totals_class = '';
    $num_class = '';
    $show_totals = '';
    $col_total = '';
    $row_total = '';
    $total_col = '';
    $col_head = '';
    $row_head = '';
    $grand_total = '';
    $q_table_id = '';
    $q_table_id_HTML = '';

    if(ctype_digit(trim($aQuestionAttributes['repeat_headings'])) && trim($aQuestionAttributes['repeat_headings']!=""))
    {
        $repeatheadings = intval($aQuestionAttributes['repeat_headings']);
        $minrepeatheadings = 0;
    }
    if (intval(trim($aQuestionAttributes['maximum_chars']))>0)
    {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maximum_chars= intval(trim($aQuestionAttributes['maximum_chars']));
        $maxlength= "maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= "";
    }
    if ($aQuestionAttributes['numbers_only']==1)
    {
        $checkconditionFunction = "fixnum_checkconditions";
        if(in_array($aQuestionAttributes['show_totals'],array("R","C","B")))
        {
            $q_table_id = 'totals_'.$ia[0];
            $q_table_id_HTML = ' id="'.$q_table_id.'"';
        }
        $num_class = ' numbers-only';
        $extraclass.=" numberonly";
        $caption.=$clang->gT("Each answer is a number. ");
        switch ($aQuestionAttributes['show_totals'])
        {
            case 'R':
                $totals_class = $show_totals = 'row';
                $row_total = '<td class="total information-item">
                <label>
                <input name="[[ROW_NAME]]_total" title="[[ROW_NAME]] total" size="[[INPUT_WIDTH]]" value="" type="text" disabled="disabled" class="disabled" />
                </label>
                </td>';
                $col_head = '            <th class="total">'.$clang->gT('Total').'</th>';
                if($show_grand == true)
                {
                    $row_head = '
                    <th class="answertext total">'.$clang->gT('Grand total').'</th>';
                    $col_total = '
                    <td>&nbsp;</td>';
                    $grand_total = '
                    <td class="total grand information-item">
                    <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                    </td>';
                };
                $caption.=$clang->gT("The last row shows the total for the column. ");
                break;
            case 'C':
                $totals_class = $show_totals = 'col';
                $col_total = '
                <td class="total information-item">
                <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                </td>';
                $row_head = '
                <th class="answertext total">Total</th>';
                if($show_grand == true)
                {
                    $row_total = '
                    <td class="total information-item">&nbsp;</td>';
                    $col_head = '            <th class="total">Grand Total</th>';
                    $grand_total = '
                    <td class="total grand">
                    <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                    </td>';
                };
                $caption.=$clang->gT("The last column shows the total for the row. ");
                break;
            case 'B':
                $totals_class = $show_totals = 'both';
                $row_total = '            <td class="total information-item">
                <label>
                <input name="[[ROW_NAME]]_total" title="[[ROW_NAME]] total" size="[[INPUT_WIDTH]]" value="" type="text" disabled="disabled" class="disabled" />
                </label>
                </td>';
                $col_total = '
                <td  class="total information-item">
                <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                </td>';
                $col_head = '            <th class="total">'.$clang->gT('Total').'</th>';
                $row_head = '
                <th class="answertext">Total</th>';
                if($show_grand == true)
                {
                    $grand_total = '
                    <td class="total grand information-item">
                    <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled"/>
                    </td>';
                }
                else
                {
                    $grand_total = '
                    <td>&nbsp;</td>';
                };
                $caption.=$clang->gT("The last row shows the total for the column and the last column shows the total for the row. ");
                break;
        };
        if(!empty($totals_class))
        {
            $totals_class = ' show-totals '.$totals_class;
            if($aQuestionAttributes['show_grand_total'])
            {
                $totals_class .= ' grand';
                $show_grand = true;
            };
        };
    }
    else
    {
    };
    if (trim($aQuestionAttributes['answer_width'])!='')
    {
        $answerwidth=$aQuestionAttributes['answer_width'];
    }
    else
    {
        $answerwidth=20;
    };
    if (trim($aQuestionAttributes['text_input_width'])!='')
    {
        $inputwidth=$aQuestionAttributes['text_input_width'];
        $extraclass .=" inputwidth-".trim($aQuestionAttributes['text_input_width']);
    }
    else
    {
        $inputwidth = 20;
    }
    $columnswidth=100-($answerwidth*2);

    $lquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]}  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=1 ORDER BY question_order";
    $lresult = Yii::app()->db->createCommand($lquery)->query();
    $labelans=array();
    $labelcode=array();
    foreach($lresult->readAll() as $lrow)
    {
        $labelans[]=$lrow['question'];
        $labelcode[]=$lrow['title'];
    }
    if ($numrows=count($labelans))
    {
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {$numrows++;}
        if( ($show_grand == true &&  $show_totals == 'col' ) || $show_totals == 'row' ||  $show_totals == 'both' )
        {
            ++$numrows;
        };
        $cellwidth=$columnswidth/$numrows;

        $cellwidth=sprintf('%02d', $cellwidth);

        $ansquery = "SELECT count(question) FROM {{questions}} WHERE parent_qid={$ia[0]} and scale_id=0 AND question like '%|%'";
        $ansresult = Yii::app()->db->createCommand($ansquery)->queryScalar(); //Checked
        if ($ansresult>0)
        {
            $right_exists=true;
            $answerwidth=$answerwidth/2;
            $caption.=$clang->gT("The last cell give some information. ");
        }
        else
        {
            $right_exists=false;
        }
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] and scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        }
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] and scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }
        $ansresult = dbExecuteAssoc($ansquery);
        $aQuestions = $ansresult->readAll();
        $anscount = count($aQuestions);
        $fn=1;

        $answer_cols = "\t<colgroup class=\"col-responses\">\n"
        ."\n\t\t<col class=\"answertext\" width=\"$answerwidth%\" />\n";
        $answer_head_line= "\t\t\t<td width='$answerwidth%'>&nbsp;</td>\n";

        $odd_even = '';
        foreach ($labelans as $ld)
        {
            $answer_head_line .= "\t<th class=\"answertext\">".$ld."</th>\n";
            $odd_even = alternation($odd_even);
            $answer_cols .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        if ($right_exists)
        {
            $answer_head_line .= "\t<td>&nbsp;</td>\n";// class=\"answertextright\"
            $odd_even = alternation($odd_even);
            $answer_cols .= "<col class=\"answertextright $odd_even\" width=\"$cellwidth%\" />\n";
        }

        if( ($show_grand == true &&  $show_totals == 'col' ) || $show_totals == 'row' ||  $show_totals == 'both' )
        {
            $answer_head_line .= $col_head;
            $odd_even = alternation($odd_even);
            $answer_cols .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        $answer_cols .= "\t</colgroup>\n";

        $answer_head = "\n\t<thead>\n\t\t<tr class=\"dontread\">\n"
        . $answer_head_line
        . "</tr>\n\t</thead>\n";

        $answer = "\n<table$q_table_id_HTML class=\"question subquestions-list questions-list {$extraclass} {$num_class} {$totals_class}\"  summary=\"{$caption}\">\n"
        . $answer_cols
        . $answer_head;
        $answer .= "<tbody>";
        $trbc = '';
        foreach ($aQuestions as $ansrow)
        {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
            {
                if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                {
                    $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                    $answer .= "<tr class=\"repeat headings dontread\">\n"
                    . $answer_head_line
                    . "</tr>\n";
                }
            }
            $myfname = $ia[1].$ansrow['title'];
            $answertext = $ansrow['question'];
            $answertextsave=$answertext;
            /* Check the sub Q mandatory volation */
            if ($ia[6]=='Y' && !empty($aMandatoryViolationSubQ))
            {
                //Go through each labelcode and check for a missing answer! If any are found, highlight this line
                $emptyresult=0;
                foreach($labelcode as $ld)
                {
                    $myfname2=$myfname.'_'.$ld;
                    if(in_array($myfname2, $aMandatoryViolationSubQ))
                    {
                        $emptyresult=1;
                    }
                }
                if ($emptyresult == 1)
                {
                    $answertext = "<span class=\"errormandatory\">{$answertext}</span>";
                }
            }

            // Get array_filter stuff
            $trbc = alternation($trbc , 'row');
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc subquestion-list questions-list");

            $answer .= $htmltbody2;

            if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
            $answer .= "\t\t\t<th class=\"answertext\">\n"
            . "\t\t\t\t".$hiddenfield
            . "$answertext\n"
            . "\t\t\t\t<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
                $answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            }
            $answer .= "\" />\n\t\t\t</th>\n";
            $thiskey=0;
            foreach ($labelcode as $ld)
            {

                $myfname2=$myfname."_$ld";
                $myfname2value = isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2] : "";
                if ($aQuestionAttributes['numbers_only']==1)
                {
                    $myfname2value = str_replace('.',$sSeparator,$myfname2value);
                }
                $answer .= "\t<td class=\"answer_cell_00$ld answer-item text-item\">\n"
                . "\t\t\t\t<label class=\"hide read\" for=\"answer{$myfname2}\">{$labelans[$thiskey]}</label>\n"
                . "\t\t\t\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" />\n"
                . "\t\t\t\t<input type=\"text\" name=\"$myfname2\" id=\"answer{$myfname2}\" class=\"".$kpclass."\" {$maxlength} size=\"$inputwidth\" "
                . ' value="'.str_replace ('"', "'", str_replace('\\', '', $myfname2value))."\" />\n";
                $inputnames[]=$myfname2;
                $answer .= "\t\t\t</td>\n";
                $thiskey += 1;
            }
            if (strpos($answertextsave,'|'))
            {
                $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                $answer .= "\t\t\t<td class=\"answertextright\" style=\"text-align:left;\" width=\"$answerwidth%\">$answertext</td>\n";
            }
            elseif ($right_exists)
            {
                $answer .= "\t\t\t<td class=\"answertextright\" style='text-align:left;' width='$answerwidth%'>&nbsp;</td>\n";
            }

            $answer .= str_replace(array('[[ROW_NAME]]','[[INPUT_WIDTH]]') , array(strip_tags($answertext),$inputwidth) , $row_total);
            $answer .= "\n\t\t</tr>\n";
            //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
            $fn++;
        }
        if($show_totals == 'col' || $show_totals == 'both' || $grand_total == true)
        {
            $answer .= "\t\t<tr class=\"total\">$row_head";
            for( $a = 0; $a < count($labelcode) ; ++$a )
            {
                $answer .= str_replace(array('[[ROW_NAME]]','[[INPUT_WIDTH]]') , array(strip_tags($answertext),$inputwidth) , $col_total);
            };
            $answer .= str_replace(array('[[ROW_NAME]]','[[INPUT_WIDTH]]') , array(strip_tags($answertext),$inputwidth) , $grand_total)."\n\t\t</tr>\n";
        }
        $answer .= "\t</tbody>\n</table>\n";
        if(!empty($q_table_id))
        {
            if ($aQuestionAttributes['numbers_only']==1)
            {
                $radix = $sSeparator;
            }
            else {
                $radix = 'X';   // to indicate that should not try to change entered values
            }
            Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."array-totalsum.js");
            $answer .= "\n<script type=\"text/javascript\">new multi_set('$q_table_id','$radix');</script>\n";
        }
        else
        {
            $addcheckcond = <<< EOD
<script type="text/javascript">
<!--
    $('#question{$ia[0]} .question').delegate('input[type=text]:visible:enabled','blur keyup',function(event){
        {$checkconditionFunction}($(this).val(), $(this).attr('name'), 'text');
        return true;
    })
// -->
</script>
EOD;
            $answer .= $addcheckcond;
        }
    }
    else
    {
        $answer = "\n<p class=\"error\">".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames='';
    }
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array_multiflexi($ia)
{
    global $thissurvey;
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $repeatheadings = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
    $extraclass ="";
    $answertypeclass = "";
    $clang = Yii::app()->lang;
    $caption=$clang->gT("An array of sub-question on each cell. The sub-question text are in the table header and concerns line header. ");
    $checkconditionFunction = "fixnum_checkconditions";
    //echo '<pre>'; print_r($_POST); echo '</pre>';
    $defaultvaluescript = '';
    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and parent_qid=0";
    $other = Yii::app()->db->createCommand($qquery)->queryScalar(); //Checked

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    if (trim($aQuestionAttributes['multiflexible_max'])!='' && trim($aQuestionAttributes['multiflexible_min']) ==''){
        $maxvalue=$aQuestionAttributes['multiflexible_max'];
        $extraclass .=" maxvalue maxvalue-".trim($aQuestionAttributes['multiflexible_max']);
        if(isset($minvalue['value']) && $minvalue['value'] == 0) {$minvalue = 0;} else {$minvalue=1;}
    }
    if (trim($aQuestionAttributes['multiflexible_min'])!='' && trim($aQuestionAttributes['multiflexible_max']) ==''){
        $minvalue=$aQuestionAttributes['multiflexible_min'];
        $extraclass .=" minvalue minvalue-".trim($aQuestionAttributes['multiflexible_max']);
        $maxvalue=$aQuestionAttributes['multiflexible_min'] + 10;
    }
    if (trim($aQuestionAttributes['multiflexible_min'])=='' && trim($aQuestionAttributes['multiflexible_max']) ==''){
        if(isset($minvalue['value']) && $minvalue['value'] == 0) {$minvalue = 0;} else {$minvalue=1;}
        $maxvalue=10;
    }
    if (trim($aQuestionAttributes['multiflexible_min']) !='' && trim($aQuestionAttributes['multiflexible_max']) !=''){
        if($aQuestionAttributes['multiflexible_min'] < $aQuestionAttributes['multiflexible_max']){
            $minvalue=$aQuestionAttributes['multiflexible_min'];
            $maxvalue=$aQuestionAttributes['multiflexible_max'];
        }
    }

    if (trim($aQuestionAttributes['multiflexible_step'])!='' && $aQuestionAttributes['multiflexible_step'] > 0)
    {
        $stepvalue=$aQuestionAttributes['multiflexible_step'];
    }
    else
    {
        $stepvalue=1;
    }

    if($aQuestionAttributes['reverse']==1)
    {
        $tmp = $minvalue;
        $minvalue = $maxvalue;
        $maxvalue = $tmp;
        $reverse=true;
        $stepvalue=-$stepvalue;
    }
    else
    {
        $reverse=false;
    }

    $checkboxlayout=false;
    $inputboxlayout=false;
    if ($aQuestionAttributes['multiflexible_checkbox']!=0)
    {
        $minvalue=0;
        $maxvalue=1;
        $checkboxlayout=true;
        $answertypeclass =" checkbox";
        $caption.=$clang->gT("Check or uncheck the answer for each subquestion. ");
    }
    elseif ($aQuestionAttributes['input_boxes']!=0 )
    {
        $inputboxlayout=true;
        $answertypeclass .=" numeric-item text";
        $extraclass .= " numberonly";
        $caption.=$clang->gT("Each answers are a number. ");
    }
    else
    {
        $answertypeclass =" dropdown";
        $caption.=$clang->gT("Select the answer for each subquestion. ");
    }
    if(ctype_digit(trim($aQuestionAttributes['repeat_headings'])) && trim($aQuestionAttributes['repeat_headings']!=""))
    {
        $repeatheadings = intval($aQuestionAttributes['repeat_headings']);
        $minrepeatheadings = 0;
    }
    if (intval(trim($aQuestionAttributes['maximum_chars']))>0)
    {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maximum_chars= intval(trim($aQuestionAttributes['maximum_chars']));
        $maxlength= "maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= "";
    }

    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $kpclass = " num-keypad";
        $extraclass .=" inputkeypad";
    }
    else
    {
        $kpclass = "";
    }

    if (trim($aQuestionAttributes['answer_width'])!='')
    {
        $answerwidth=$aQuestionAttributes['answer_width'];
    }
    else
    {
        $answerwidth=20;
    }
    $columnswidth=100-($answerwidth*2);

    $lquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]}  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=1 ORDER BY question_order";
    $lresult = dbExecuteAssoc($lquery);
    $aQuestions=$lresult->readAll();
    $labelans=array();
    $labelcode=array();
    foreach ($aQuestions as $lrow)
    {
        $labelans[]=$lrow['question'];
        $labelcode[]=$lrow['title'];
    }
    if ($numrows=count($labelans))
    {
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {$numrows++;}
        $cellwidth=$columnswidth/$numrows;

        $cellwidth=sprintf('%02d', $cellwidth);

        $sQuery = "SELECT count(question) FROM {{questions}} WHERE parent_qid=".$ia[0]." AND scale_id=0 AND question like '%|%'";
        $iCount = Yii::app()->db->createCommand($sQuery)->queryScalar();
        if ($iCount>0) {
            $right_exists=true;
            $answerwidth=$answerwidth/2;
            $caption.=$clang->gT("The last cell give some information. ");
        } else {
            $right_exists=false;
        }
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        }
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }
        $ansresult = dbExecuteAssoc($ansquery)->readAll();  //Checked
        if (trim($aQuestionAttributes['parent_order']!=''))
        {
            $iParentQID=(int) $aQuestionAttributes['parent_order'];
            $aResult=array();
            $sessionao = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['answer_order'];
            foreach ($sessionao[$iParentQID] as $aOrigRow)
            {
                $sCode=$aOrigRow['title'];
                foreach ($ansresult as $aRow)
                {
                    if ($sCode==$aRow['title'])
                    {
                        $aResult[]=$aRow;
                    }
                }
            }
            $ansresult=$aResult;
        }
        $anscount = count($ansresult);
        $fn=1;

        $mycols = "\t<colgroup class=\"col-responses\">\n"
        . "\n\t<col class=\"answertext\" width=\"$answerwidth%\" />\n";
        $answer_head_line = "\t<td >&nbsp;</td>\n";
        $odd_even = '';
        foreach ($labelans as $ld)
        {
            $answer_head_line .= "\t<th>".$ld."</th>\n";
            $odd_even = alternation($odd_even);
            $mycols .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        if ($right_exists)
        {
            $answer_head_line .= "\t<td>&nbsp;</td>";
            $odd_even = alternation($odd_even);
            $mycols .= "<col class=\"answertextright $odd_even\" width=\"$answerwidth%\" />\n";
        }
        $answer_head = "\n\t<thead>\n<tr class=\"dontread\">\n"
        . $answer_head_line
        . "</tr>\n\t</thead>\n";
        $mycols .= "\t</colgroup>\n";

        $trbc = '';
        $answer = "\n<table class=\"question subquestions-list questions-list {$answertypeclass}-list {$extraclass}\" summary=\"{$caption}\">\n"
        . $mycols
        . $answer_head . "\n";
        $answer .= "<tbody>";
        foreach ($ansresult as $ansrow)
        {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
            {
                if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                {
                    $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                    $answer .= "<tr class=\"repeat headings dontread\">\n"
                    . $answer_head_line
                    . "</tr>\n\n";
                }
            }
            $myfname = $ia[1].$ansrow['title'];
            $answertext = $ansrow['question'];
            $answertextsave=$answertext;
            /* Check the sub Q mandatory violation */
            if ($ia[6]=='Y' && !empty($aMandatoryViolationSubQ))
            {
                //Go through each labelcode and check for a missing answer! Default :If any are found, highlight this line, checkbox : if one is not found : don't highlight
                // PS : we really need a better system : event for EM !
                $emptyresult=($aQuestionAttributes['multiflexible_checkbox']!=0) ? 1 : 0;
                foreach($labelcode as $ld)
                {
                    $myfname2=$myfname.'_'.$ld;
                    if($aQuestionAttributes['multiflexible_checkbox']!=0)
                    {
                        if(!in_array($myfname2, $aMandatoryViolationSubQ))
                        {
                            $emptyresult=0;
                        }
                    }
                    else
                    {
                        if(in_array($myfname2, $aMandatoryViolationSubQ))
                        {
                            $emptyresult=1;
                        }
                    }
                }
                if ($emptyresult == 1)
                {
                    $answertext = '<span class="errormandatory">'.$answertext.'</span>';
                }
            }

            // Get array_filter stuff
            $trbc = alternation($trbc , 'row');
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc subquestions-list questions-list {$answertypeclass}-list");

            $answer .= $htmltbody2;

            if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
            $answer .= "\t<th class=\"answertext\" width=\"$answerwidth%\">\n"
            . "$answertext\n"
            . $hiddenfield
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                $answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            }
            $answer .= "\" />\n\t</th>\n";
            $first_hidden_field = '';
            $thiskey=0;
            foreach ($labelcode as $ld)
            {
                if ($checkboxlayout == false)
                {
                    $myfname2=$myfname."_$ld";

                    if(isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]))
                    {
                        $myfname2_java_value = " value=\"{$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]}\" ";
                    }
                    else
                    {
                        $myfname2_java_value = "";
                    }
                    $answer .= "\t<td class=\"answer_cell_00$ld question-item answer-item {$answertypeclass}-item $extraclass\">\n"
                    . "\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" $myfname2_java_value />\n"
                    . "<label class=\"hide read\" for=\"answer{$myfname2}\">{$labelans[$thiskey]}</label>\n";
                    $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
                    $sSeparator = $sSeparator['separator'];
                    if($inputboxlayout == false) {
                        $answer .= "\t<select class=\"multiflexiselect\" name=\"$myfname2\" id=\"answer{$myfname2}\" title=\""
                        . HTMLEscape($labelans[$thiskey]).'"'
                        . " onchange=\"$checkconditionFunction(this.value, this.name, this.type)\">\n"
                        . "<option value=\"\">".$clang->gT('...')."</option>\n";

                        for($ii=$minvalue; ($reverse? $ii>=$maxvalue:$ii<=$maxvalue); $ii+=$stepvalue) {
                            $answer .= '<option value="'.str_replace('.',$sSeparator,$ii).'"';
                            if(isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]) && (string)$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2] == (string)($ii)) {
                                $answer .= SELECTED;
                            }
                            $answer .= ">".str_replace('.',$sSeparator,$ii)."</option>\n";
                        }
                        $answer .= "\t</select>\n";
                    } elseif ($inputboxlayout == true)
                    {
                        $answer .= "\t<input type='text' class=\"multiflexitext text {$kpclass}\" name=\"$myfname2\" id=\"answer{$myfname2}\" {$maxlength} size=5 title=\""
                        . HTMLEscape($labelans[$thiskey]).'"'
                        . " onkeyup=\"$checkconditionFunction(this.value, this.name, this.type)\""
                        . " value=\"";
                        if(isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]) && is_numeric($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2])) {
                            $answer .= str_replace('.',$sSeparator,$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]);
                        }
                        $answer .= "\" />\n";
                    }
                    $answer .= "\t</td>\n";

                    $inputnames[]=$myfname2;
                    $thiskey++;
                }
                else
                {
                    $myfname2=$myfname."_$ld";
                    if(isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2] == '1')
                    {
                        $myvalue = '1';
                        $setmyvalue = CHECKED;
                    }
                    else
                    {
                        $myvalue = '';
                        $setmyvalue = '';
                    }
                    $answer .= "\t<td class=\"answer_cell_00$ld question-item answer-item {$answertypeclass}-item\">\n"
                    . "\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" value=\"$myvalue\"/>\n"
                    . "\t<input type=\"hidden\" name=\"$myfname2\" id=\"answer{$myfname2}\" value=\"$myvalue\" />\n";
                    $answer .= "\t<input type=\"checkbox\" class=\"checkbox {$extraclass}\" name=\"cbox_$myfname2\" id=\"cbox_$myfname2\" $setmyvalue "
                    . " onclick=\"cancelBubbleThis(event); "
                    . " aelt=document.getElementById('answer{$myfname2}');"
                    . " jelt=document.getElementById('java{$myfname2}');"
                    . " if(this.checked) {"
                    . "  aelt.value=1;jelt.value=1;$checkconditionFunction(1,'{$myfname2}',aelt.type);"
                    . " } else {"
                    . "  aelt.value='';jelt.value='';$checkconditionFunction('','{$myfname2}',aelt.type);"
                    . " }; return true;\" "
                    //                    . " onchange=\"checkconditions(this.value, this.name, this.type)\" "
                    . " />\n";
                    $answer .=  "<label class=\"hide read\" for=\"cbox_{$myfname2}\">{$labelans[$thiskey]}</label>\n";
                    $inputnames[]=$myfname2;
                    //                    $answer .= "</label>\n"
                    $answer .= ""
                    . "\t</td>\n";
                    $thiskey++;
                }
            }
            if (strpos($answertextsave,'|'))
            {
                $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                $answer .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">$answertext</td>\n";
            }
            elseif ($right_exists)
            {
                $answer .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">&nbsp;</td>\n";
            }

            $answer .= "</tr>\n";
            //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
            $fn++;
        }
        $answer .= "\t</tbody>\n</table>\n";
    }
    else
    {
        $answer = "\n<p class=\"error\">".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames = '';
    }
    return array($answer, $inputnames);
}


// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_arraycolumns($ia)
{
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $clang = Yii::app()->lang;
    $extraclass = "";
    $checkconditionFunction = "checkconditions";
    $caption=$clang->gT("An array with sub-question on each column. The sub-question are on table header, the answers are in each line header. ");

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."'";
    $other = Yii::app()->db->createCommand($qquery)->queryScalar(); //Checked

    $lquery = "SELECT * FROM {{answers}} WHERE qid=".$ia[0]."  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY sortorder, code";
    $oAnswers = dbExecuteAssoc($lquery);
    $aAnswers = $oAnswers->readAll();
    $labelans=array();
    $labelcode=array();
    $labels=array();
    foreach ($aAnswers as $lrow)
    {
        $labelans[]=$lrow['answer'];
        $labelcode[]=$lrow['code'];
        $labels[]=array("answer"=>$lrow['answer'], "code"=>$lrow['code']);
    }
    if (count($labelans) > 0)
    {
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            $labelcode[]='';
            $labelans[]=$clang->gT('No answer');
            $labels[]=array('answer'=>$clang->gT('No answer'), 'code'=>'');
        }
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        }
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }
        $ansresult = dbExecuteAssoc($ansquery);  //Checked
        $aQuestions = $ansresult->readAll();
        $anscount = count($aQuestions);
        if ($anscount>0)
        {
            $fn=1;
            $cellwidth=$anscount;
            $cellwidth=round(( 50 / $cellwidth ) , 1);
            $answer = "\n<table class=\"question subquestions-list questions-list\" summary=\"{$caption}\">\n"
            . "\t<colgroup class=\"col-responses\">\n"
            . "\t<col class=\"col-answers\" width=\"50%\" />\n";
            $odd_even = '';
            for( $c = 0 ; $c < $anscount ; ++$c )
            {
                $odd_even = alternation($odd_even);
                $answer .= "<col class=\"$odd_even question-item answers-list radio-list\" width=\"$cellwidth%\" />\n";
            }
            $answer .= "\t</colgroup>\n\n"
            . "\t<thead>\n"
            . "<tr>\n"
            . "\t<td>&nbsp;</td>\n";

            foreach ($aQuestions as $ansrow)
            {
                $anscode[]=$ansrow['title'];
                $answers[]=$ansrow['question'];
            }
            $trbc = '';
            $odd_even = '';
            for ($_i=0;$_i<count($answers);++$_i)
            {
                $ld = $answers[$_i];
                $myfname = $ia[1].$anscode[$_i];
                $trbc = alternation($trbc , 'row');
                /* Check the Sub Q mandatory violation */
                if ($ia[6]=='Y' && in_array($myfname, $aMandatoryViolationSubQ))
                {
                    $ld = "<span class=\"errormandatory\">{$ld}</span>";
                }
                $odd_even = alternation($odd_even);
                $answer .= "\t<th class=\"$odd_even\">$ld</th>\n";
            }
            unset($trbc);
            $answer .= "</tr>\n\t</thead>\n\n\t<tbody>\n";
            $ansrowcount=0;
            $ansrowtotallength=0;
            foreach($aQuestions as $ansrow)
            {
                $ansrowcount++;
                $ansrowtotallength=$ansrowtotallength+strlen($ansrow['question']);
            }
            $percwidth=100 - ($cellwidth*$anscount);
            foreach($labels as $ansrow)
            {
                $answer .= "<tr>\n"
                . "\t<th class=\"arraycaptionleft dontread\">{$ansrow['answer']}</th>\n";
                foreach ($anscode as $ld)
                {
                    //if (!isset($trbc) || $trbc == 'array1') {$trbc = 'array2';} else {$trbc = 'array1';}
                    $myfname=$ia[1].$ld;
                    $answer .= "\t<td class=\"answer_cell_00$ld answer-item radio-item\">\n"
                    . "<label class=\"hide read\" for=\"answer".$myfname.'-'.$ansrow['code']."\">{$ansrow['answer']}</label>\n"
                    . "\t<input class=\"radio\" type=\"radio\" name=\"".$myfname.'" value="'.$ansrow['code'].'" '
                    . 'id="answer'.$myfname.'-'.$ansrow['code'].'" ';
                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $ansrow['code'])
                    {
                        $answer .= CHECKED;
                    }
                    elseif (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $ansrow['code'] == '')
                    {
                        $answer .= CHECKED;
                        // Humm.. (by lemeur), not sure this section can be reached
                        // because I think $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] is always set (by save.php ??) !
                        // should remove the !isset part I think !!
                    }
                    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n\t</td>\n";
                }
                unset($trbc);
                $answer .= "</tr>\n";
                $fn++;
            }

            $answer .= "\t</tbody>\n</table>\n";
            foreach($anscode as $ld)
            {
                $myfname=$ia[1].$ld;
                $answer .= '<input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="';
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
                {
                    $answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                }
                $answer .= "\" />\n";
                $inputnames[]=$myfname;
            }
        }
        else
        {
            $answer = '<p class="error">'.$clang->gT('Error: There are no answers defined for this question.')."</p>";
            $inputnames="";
        }
    }
    else
    {
        $answer = "<p class='error'>".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames = '';
    }
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_array_dual($ia)
{
    $clang = Yii::app()->lang;
    global $thissurvey;
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $repeatheadings = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
    $extraclass ="";
    $answertypeclass = ""; // Maybe not
    $caption="";// Just leave empty, are replaced after
    $inputnames=array();
    $labelans1=array();
    $labelans=array();
    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
    }
    else
    {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY question_order";
    }
    $ansresult = dbExecuteAssoc($ansquery);   //Checked
    $aSubQuestions=$ansresult->readAll();
    $anscount = count($aSubQuestions);

    $lquery =  "SELECT * FROM {{answers}} WHERE scale_id=0 AND qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY sortorder, code";
    $lresult = dbExecuteAssoc($lquery); //Checked
    $aAnswersScale0=$lresult->readAll();

    $lquery1 = "SELECT * FROM {{answers}} WHERE scale_id=1 AND qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY sortorder, code";
    $lresult1 = dbExecuteAssoc($lquery1); //Checked
    $aAnswersScale1=$lresult1->readAll();

    if ($aQuestionAttributes['use_dropdown']==1)
    {
        $useDropdownLayout = true;
        $extraclass .=" dropdown-list";
        $answertypeclass .=" dropdown";
        $doDualScaleFunction="doDualScaleDropDown";// javascript funtion to lauch at end of answers
        $caption=$clang->gT("An array with sub-question on each line, with 2 answers to provide on each line. You have to select the answer.");
    }
    else
    {
        $useDropdownLayout = false;
        $extraclass .=" radio-list";
        $answertypeclass .=" radio";
        $doDualScaleFunction="doDualScaleRadio";
        $caption=$clang->gT("An array with sub-question on each line, with 2 answers to provide on each line. The answers are contained in the table header. ");
    }
    if(ctype_digit(trim($aQuestionAttributes['repeat_headings'])) && trim($aQuestionAttributes['repeat_headings']!=""))
    {
        $repeatheadings = intval($aQuestionAttributes['repeat_headings']);
        $minrepeatheadings = 0;
    }
    if (trim($aQuestionAttributes['dualscale_headerA'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='') {
        $leftheader= $aQuestionAttributes['dualscale_headerA'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
    }
    else
    {
        $leftheader ='';
    }

    if (trim($aQuestionAttributes['dualscale_headerB'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='')
    {
        $rightheader= $aQuestionAttributes['dualscale_headerB'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
    }
    else
    {
        $rightheader ='';
    }
    if (trim($aQuestionAttributes['answer_width'])!='')
    {
        $answerwidth=$aQuestionAttributes['answer_width'];
    }
    else
    {
        $answerwidth=20;
    }
    // Find if we have rigth and center text
    // TODO move "|" to attribute
    $sQuery = "SELECT count(question) FROM {{questions}} WHERE parent_qid=".$ia[0]." and scale_id=0 AND question like '%|%'";
    $rigthCount = Yii::app()->db->createCommand($sQuery)->queryScalar();
    $rightexists= ($rigthCount>0);// $right_exists: flag to find out if there are any right hand answer parts. leaving right column but don't force with
    $sQuery = "SELECT count(question) FROM {{questions}} WHERE parent_qid=".$ia[0]." and scale_id=0 AND question like '%|%|%'";
    $centerCount = Yii::app()->db->createCommand($sQuery)->queryScalar();
    $centerexists= ($centerCount>0);// $center_exists: flag to find out if there are any center hand answer parts. leaving center column but don't force with

    // Label and code for input
    foreach ($aAnswersScale0 as $lrow)
    {
        $labels0[]=Array('code' => $lrow['code'],
        'title' => $lrow['answer']);
    }
    foreach ($aAnswersScale1 as $lrow)
    {
        $labels1[]=Array('code' => $lrow['code'],
        'title' => $lrow['answer']);
    }

    if (count($aAnswersScale0) > 0 && $anscount)
    {
        $answer = "";
        $fn=1;// Used by repeat_heading
        if ($useDropdownLayout === false)
        {
            $columnswidth = 100 - $answerwidth;
            foreach ($aAnswersScale0 as $lrow)
            {
                $labelans0[]=$lrow['answer'];
                $labelcode0[]=$lrow['code'];
            }
            foreach ($aAnswersScale1 as $lrow)
            {
                $labelans1[]=$lrow['answer'];
                $labelcode1[]=$lrow['code'];
            }
            $numrows=count($labelans0) + count($labelans1);
            // Add needed row and fill some boolean: shownoanswer, rightexists, centerexists
            $shownoanswer=($ia[6] != "Y" && SHOW_NO_ANSWER == 1);
            if($shownoanswer) {
                $numrows++;
                $caption.=$clang->gT("The last cell are for no answer. ");
            }
            if($rightexists) {$numrows++;}
            if($centerexists) {$numrows++;}
            $cellwidth=$columnswidth/$numrows;
            //$cellwidth=sprintf("%02d", $cellwidth); // No reason to do this, except to leave place for separator ?  But then table can not be the same in all browser

            // Header row and colgroups
            $mycolumns = "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";
            $answer_head_line = "\t<th class=\"header_answer_text\">&nbsp;</th>\n\n";
            $mycolumns .= "\t<colgroup class=\"col-responses group-1\">\n";
            $odd_even = '';
            foreach ($labelans0 as $ld)
            {
                $answer_head_line .= "\t<th>".$ld."</th>\n";
                $odd_even = alternation($odd_even);
                $mycolumns .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
            }
            $mycolumns .= "\t</colgroup>\n";
            if (count($labelans1)>0) // if second label set is used
            {
                $separatorwidth=($centerexists)? "width=\"$cellwidth%\" ":"";
                $mycolumns .=  "\t<col class=\"separator\" {$separatorwidth}/>\n";
                $mycolumns .= "\t<colgroup class=\"col-responses group-2\">\n";
                $answer_head_line .= "\n\t<td class=\"header_separator\">&nbsp;</td>\n\n"; // Separator : and No answer for accessibility for first colgroup
                foreach ($labelans1 as $ld)
                {
                    $answer_head_line .= "\t<th>".$ld."</th>\n";
                    $odd_even = alternation($odd_even);
                    $mycolumns .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
                }
                $mycolumns .= "\t</colgroup>\n";
            }
            if($shownoanswer || $rightexists)
            {
                $rigthwidth=($rightexists)? "width=\"$cellwidth%\" ":"";
                $mycolumns .=  "\t<col class=\"separator rigth_separator\" {$rigthwidth}/>\n";
                $answer_head_line .= "\n\t<td class=\"header_separator rigth_separator\">&nbsp;</td>\n";
            }
            if($shownoanswer)
            {
                $mycolumns .=  "\t<col class=\"col-no-answer\"  width=\"$cellwidth%\" />\n";
                $answer_head_line .= "\n\t<th class=\"header_no_answer\">".$clang->gT('No answer')."</th>\n";
            }
            $answer_head2 = "\n<tr class=\"array1 header_row dontread\">\n"
            . $answer_head_line
            . "</tr>\n";
            // build first row of header if needed
            if ($leftheader != '' || $rightheader !='')
            {
                $answer_head1 = "<tr class=\"array1 groups header_row\">\n"
                . "\t<th class=\"header_answer_text\">&nbsp;</th>\n"
                . "\t<th colspan=\"".count($labelans0)."\" class=\"dsheader\">$leftheader</th>\n";
                if (count($labelans1)>0)
                {
                    $answer_head1 .= "\t<td class=\"header_separator\">&nbsp;</td>\n" // Separator
                    ."\t<th colspan=\"".count($labelans1)."\" class=\"dsheader\">$rightheader</th>\n";
                }
                if($shownoanswer || $rightexists)
                {
                    $rigthclass=($rightexists)?" header_answer_text_right":"";
                    $answer_head1 .= "\t<td class=\"header_separator {$rigthclass}\">&nbsp;</td>\n";
                    if($shownoanswer)
                    {
                        $answer_head1 .= "\t<th class=\"header_no_answer\">&nbsp;</th>\n";
                    }
                }
                $answer_head1 .= "</tr>\n";
            }
            else
            {
                $answer_head1 = "";
            }
            $answer .= "\n<table class=\"question subquestions-list questions-list\" summary=\"{$caption}\">\n"
            . $mycolumns
            . "\n\t<thead>\n"
            . $answer_head1
            . $answer_head2
            . "\n\t</thead>\n"
            . "<tbody>\n";

            // And no each line of body
            $trbc = '';
            foreach ($aSubQuestions as $ansrow)
            {
                // Build repeat headings if needed
                if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
                {
                    if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                    {
                        $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                        //$answer .= $answer_head1;
                        $answer .= "\n<tr class=\"repeat headings\">\n"
                        . $answer_head_line
                        . "</tr>\n";
                    }
                }
                $trbc = alternation($trbc , 'row');
                $answertext=$ansrow['question'];

                // rigth and center answertext: not explode for ? Why not
                if(strpos($answertext,'|'))
                {
                    $answertextrigth=substr($answertext,strpos($answertext,'|')+1);
                    $answertext=substr($answertext,0, strpos($answertext,'|'));
                }
                else
                {
                    $answertextrigth="";
                }
                if($centerexists)
                {
                    $answertextcenter=substr($answertextrigth,0, strpos($answertextrigth,'|'));
                    $answertextrigth=substr($answertextrigth,strpos($answertextrigth,'|')+1);
                }
                else
                {
                    $answertextcenter="";
                }

                $myfname= $ia[1].$ansrow['title'];
                $myfname0 = $ia[1].$ansrow['title'].'#0';
                $myfid0 = $ia[1].$ansrow['title'].'_0';
                $myfname1 = $ia[1].$ansrow['title'].'#1'; // new multi-scale-answer
                $myfid1 = $ia[1].$ansrow['title'].'_1';
                /* Check the Sub Q mandatory violation */
                if ($ia[6]=='Y' && (in_array($myfname0, $aMandatoryViolationSubQ) || in_array($myfname1, $aMandatoryViolationSubQ)))
                {
                    $answertext = "<span class='errormandatory'>{$answertext}</span>";
                }
                // Get array_filter stuff
                list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc answers-list radio-list");
                $answer .= $htmltbody2;


                array_push($inputnames,$myfname0);
                $answer .= "\t<th class=\"answertext\">\n"
                . $hiddenfield
                . "$answertext\n";
                // Hidden answers used by EM: sure can be added in javascript
                $answer .= "<input type=\"hidden\" disabled=\"disabled\" name=\"java$myfid0\" id=\"java$myfid0\" value=\"";
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0])) {$answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0];}
                $answer .= "\" />\n";
                if (count($labelans1)>0) // if second label set is used
                {
                    $answer .= "<input type=\"hidden\" disabled=\"disabled\" name=\"java$myfid1\" id=\"java$myfid1\" value=\"";
                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1])) {$answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1];}
                    $answer .= "\" />\n";
                }
                $answer .= "\t</th>\n";
                $hiddenanswers='';
                $thiskey=0;
                foreach ($labelcode0 as $ld)
                {
                    $answer .= "\t<td class=\"answer_cell_1_00$ld answer-item {$answertypeclass}-item\">\n"
                    . "<label class=\"hide read\" for=\"answer{$myfid0}-{$ld}\">$labelans0[$thiskey]</label>\n"
                    . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname0\" value=\"$ld\" id=\"answer$myfid0-$ld\" ";
                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0] == $ld)
                    {
                        $answer .= CHECKED;
                    }
                    $answer .= "  />\n";
                    $answer .= "\n\t</td>\n";
                    $thiskey++;
                }
                if (count($labelans1)>0) // if second label set is used
                {
                    $answer .= "\t<td class=\"dual_scale_separator information-item\">";
                    if ($shownoanswer)// No answer for accessibility and no javascript (but hide hide even with no js: need reworking)
                    {
                        $answer .=  "<label for='answer$myfid0-' class= \"hide read\">".$clang->gT("No answer")."</label>"
                        . "\t<input class='radio jshide read' type='radio' name='$myfname0' value='' id='answer$myfid0-' ";
                        if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0] == "")
                        {
                            $answer .= CHECKED;
                        }
                        $answer .= " />\n";
                    }
                    $answer .= "{$answertextcenter}</td>\n"; // separator
                    array_push($inputnames,$myfname1);
                    $thiskey=0;
                    foreach ($labelcode1 as $ld) // second label set
                    {
                        $answer .= "\t<td class=\"answer_cell_2_00$ld  answer-item radio-item\">\n";
                        $answer .= "<label class=\"hide read\" for=\"answer{$myfid1}-{$ld}\">{$labelans1[$thiskey]}</label>\n"
                        . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname1\" value=\"$ld\" id=\"answer$myfid1-$ld\" ";
                        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1] == $ld)
                        {
                            $answer .= CHECKED;
                        }
                        $answer .= " />\n";
                        $answer .= "\t</td>\n";
                        $thiskey++;
                    }
                }
                if ($shownoanswer || $rightexists)
                {
                    $answer .= "\t<td class=\"answertextright dual_scale_separator information-item\">{$answertextrigth}</td>\n";
                }
                if ($shownoanswer)
                {
                    $answer .= "\t<td class=\"dual_scale_no_answer answer-item radio-item noanswer-item\">\n";
                    if (count($labelans1)>0)
                    {
                        $answer .= "<label class='hide read' for='answer$myfid1-'>".$clang->gT("No answer")."</label>"
                        . "\t<input class='radio' type='radio' name='$myfname1' value='' id='answer$myfid1-' ";
                        if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1] == "")
                        {
                            $answer .= CHECKED;
                        }
                        // --> START NEW FEATURE - SAVE
                        $answer .= " />\n";
                    }
                    else
                    {
                        $answer .= "<label class='hide read' for='answer$myfid0-'>".$clang->gT("No answer")."<label>\n"
                        . "\t<input class='radio' type='radio' name='$myfname0' value='' id='answer$myfid0-' ";
                        if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0] == "")
                        {
                            $answer .= CHECKED;
                        }
                        // --> START NEW FEATURE - SAVE
                        $answer .= " />\n";
                    }
                    $answer .= "\t</td>\n";
                }
                $answer .= "</tr>\n";
                $fn++;
            }
            $answer.="</tbody>\n";
            $answer.="</table>";
        }
        elseif($useDropdownLayout === true)
        {
            $separatorwidth=(100-$answerwidth)/10;
            $cellwidth=(100-$answerwidth-$separatorwidth)/2;

            $answer = "";

            // Get attributes for Headers and Prefix/Suffix
            if (trim($aQuestionAttributes['dropdown_prepostfix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='') {
                list ($ddprefix, $ddsuffix) =explode("|",$aQuestionAttributes['dropdown_prepostfix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]);
                $ddprefix = $ddprefix;
                $ddsuffix = $ddsuffix;
            }
            else
            {
                $ddprefix ='';
                $ddsuffix='';
            }
            if (trim($aQuestionAttributes['dropdown_separators'])!='') {
                $aSeparator =explode('|',$aQuestionAttributes['dropdown_separators']);
                if (isset($aSeparator[1])) {
                    $interddSep=$aSeparator[1];
                }
                else {
                    $interddSep=$aSeparator[0];
                }
            }
            else {
                $interddSep = '';
            }
            $colspan_1 = '';
            $colspan_2 = '';
            $suffix_cell = '';
            $answer .= "\n<table class=\"question subquestion-list questions-list dropdown-list\" summary=\"{$caption}\">\n"
            . "\t<col class=\"answertext\" width=\"$answerwidth%\" />\n";

            if($ddprefix != '' || $ddsuffix != '')
            {
                $answer .= "\t<colgroup width=\"$cellwidth%\">\n";
            }
            if($ddprefix != '')
            {
                $answer .= "\t\t<col class=\"ddprefix\" />\n";
                $colspan_1 = ' colspan="2"';
            }
            $headcolwidth=($ddprefix != '' || $ddsuffix != '')?"":" width=\"$cellwidth%\"";
            $answer .= "\t<col class=\"dsheader\"{$headcolwidth} />\n";
            if($ddsuffix != '')
            {
                $answer .= "\t<col class=\"ddsuffix\" />\n";
            }
            if($ddprefix != '' || $ddsuffix != '')
            {
                $answer .= "\t</colgroup>\n";
            }
            $answer .= "\t<col class=\"ddarrayseparator\" width=\"{$separatorwidth}%\" />\n";
            if($ddprefix != '' || $ddsuffix != '')
            {
                $answer .= "\t<colgroup width=\"$cellwidth%\">\n";
            }
            if($ddprefix != '')
            {
                $answer .= "\t\t<col class=\"ddprefix\" />\n";
            }
            $answer .= "\t<col class=\"dsheader\"{$headcolwidth} />\n";
            if($ddsuffix != '')
            {
                $answer .= "\t<col class=\"ddsuffix\" />\n";
            }
            if($ddprefix != '' || $ddsuffix != '')
            {
                $answer .= "\t</colgroup>\n";
            }
            // colspan : for header only
            if($ddprefix != '' && $ddsuffix != '')
                $colspan=' colspan="3"';
            elseif($ddprefix != '' || $ddsuffix != '')
                $colspan=' colspan="2"';
            else
                $colspan="";
            // headers
            $answer .= "\n\t<thead>\n"
            . "<tr>\n"
            . "\t<td>&nbsp;</td>\n"
            . "\t<th{$colspan}>$leftheader</th>\n"
            . "\t<td>&nbsp;</td>\n"
            . "\t<th{$colspan}>$rightheader</th>\n";
            $answer .="\t</tr>\n"
            . "\t</thead>\n";
            $answer .= "\n<tbody>\n";
            $trbc = '';
            foreach ($aSubQuestions as $ansrow)
            {

                $myfname = $ia[1].$ansrow['title'];
                $myfname0 = $ia[1].$ansrow['title']."#0";
                $myfid0 = $ia[1].$ansrow['title']."_0";
                $myfname1 = $ia[1].$ansrow['title']."#1";
                $myfid1 = $ia[1].$ansrow['title']."_1";
                $sActualAnswer0=isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0])?$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0]:"";
                $sActualAnswer1=isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1])?$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1]:"";
                if ($ia[6]=='Y' && (in_array($myfname0, $aMandatoryViolationSubQ) || in_array($myfname1, $aMandatoryViolationSubQ)))
                {
                    $answertext="<span class='errormandatory'>".$ansrow['question']."</span>";
                }
                else
                {
                    $answertext=$ansrow['question'];
                }
                list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc subquestion-list questions-list dropdown-list");
                $answer .= $htmltbody2;
                $answer .= "\t<th class=\"answertext\">\n"
                . "<label for=\"answer$myfid0\">{$answertext}</label>\n";
                // Hidden answers used by EM: sure can be added in javascript
                $answer .= "<input type=\"hidden\" disabled=\"disabled\" name=\"java$myfid0\" id=\"java$myfid0\" value=\"{$sActualAnswer0}\" />\n";
                $answer .= "<input type=\"hidden\" disabled=\"disabled\" name=\"java$myfid1\" id=\"java$myfid1\" value=\"{$sActualAnswer1}\" />\n";
                $answer . "\t</th>\n";
                // Selector 0
                if($ddprefix != '')
                {
                    $answer .= "\t<td class=\"ddprefix information-item\">$ddprefix</td>\n";
                }
                $answer .= "\t<td class=\"answer-item dropdown-item\">\n"
                . "<select name=\"$myfname0\" id=\"answer$myfid0\">\n";

                // Show the 'Please choose' if there are no answer actually
                if ($sActualAnswer0 == '')
                {
                    $answer .= "\t<option value=\"\" ".SELECTED.">".$clang->gT('Please choose...')."</option>\n";
                }
                foreach ($labels0 as $lrow)
                {
                    $answer .= "\t<option value=\"".$lrow['code'].'" ';
                    if ($sActualAnswer0 == $lrow['code'])
                    {
                        $answer .= SELECTED;
                    }
                    $answer .= '>'.flattenText($lrow['title'])."</option>\n";
                }
                if ($sActualAnswer0 != '' && $ia[6] != 'Y' && SHOW_NO_ANSWER)
                {
                    $answer .= "\t<option value=\"\">".$clang->gT('No answer')."</option>\n";
                }
                $answer .= "</select>\n";
                $answer .= "</td>\n";
                if($ddsuffix != '')
                {
                    $answer .= "\t<td class=\"ddsuffix information-item\">$ddsuffix</td>\n";
                }
                $inputnames[]=$myfname0;

                $answer .= "\t<td class=\"ddarrayseparator information-item\">$interddSep</td>\n"; //Separator

                // Selector 1
                if($ddprefix != '')
                {
                    $answer .= "\t<td class='ddprefix information-item'>$ddprefix</td>\n";
                }
                $answer .= "\t<td class=\"answer-item dropdown-item\">\n"
                . "<label class=\"hide read\" for=\"answer{$myfid1}\">{$answertext}</label>"
                . "<select name=\"$myfname1\" id=\"answer$myfid1\">\n";
                // Show the 'Please choose' if there are no answer actually
                if ($sActualAnswer1 == '')
                {
                    $answer .= "\t<option value=\"\" ".SELECTED.">".$clang->gT('Please choose...')."</option>\n";
                }
                foreach ($labels1 as $lrow1)
                {
                    $answer .= "\t<option value=\"".$lrow1['code'].'" ';
                    if ($sActualAnswer1 == $lrow1['code'])
                    {
                        $answer .= SELECTED;
                    }
                    $answer .= '>'.flattenText($lrow1['title'])."</option>\n";
                }
                if ($sActualAnswer1 != '' && $ia[6] != 'Y' && SHOW_NO_ANSWER)
                {
                    $answer .= "\t<option value=\"\">".$clang->gT('No answer')."</option>\n";
                }
                $answer .= "</select>\n";
                $answer .= "</td>\n";
                if($ddsuffix != '')
                {
                    $answer .= "\t<td class=\"ddsuffix information-item\">$ddsuffix</td>\n";
                }
                $inputnames[]=$myfname1;

                $answer .= "</tr>\n";
            }
            $answer .= "\t</tbody>\n";
            $answer .= "</table>\n";
        }
    }
    else
    {
        $answer = "<p class='error'>".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames="";
    }
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."dualscale.js");
    $answer .= "<script type='text/javascript'>\n"
    . "  <!--\n"
    ." {$doDualScaleFunction}({$ia[0]});\n"
    ." -->\n"
    ."</script>\n";
    return array($answer, $inputnames);
}
