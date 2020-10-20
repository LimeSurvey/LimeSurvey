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

// Security Checked: POST, GET, SESSION, REQUEST, returnGlobal, DB

//if (!isset($homedir) || isset($_REQUEST['$homedir'])) {die("Cannot run this script directly");}

/*
* Let's explain what this strange $ia var means
*
* The $ia string comes from the $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['insertarray'] variable which is built at the commencement of the survey.
* See index.php, function "buildsurveysession()"
* One $ia array zexists for every question in the survey. The $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['insertarray']
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

// ==================================================================
// setting constants for 'checked' and 'selected' inputs
define('CHECKED', ' checked="checked"');
define('SELECTED', ' selected="selected"');

/**
* setNoAnswerMode
*/
function setNoAnswerMode($thissurvey)
{
    if (getGlobalSetting('shownoanswer') == 1) {
        define('SHOW_NO_ANSWER', 1);
    } elseif (getGlobalSetting('shownoanswer') == 0) {
        define('SHOW_NO_ANSWER', 0);
    } elseif ($thissurvey['shownoanswer'] == 'N') {
        define('SHOW_NO_ANSWER', 0);
    } else {
        define('SHOW_NO_ANSWER', 1);
    }
}

/**
* This function returns an array containing the "question/answer" html display
* and a list of the question/answer fieldnames associated. It is called from
* question.php, group.php, survey.php or preview.php
*
* @param array $ia Details of $ia can be found at top of this file
* @return array Array like [array $qanda, array $inputnames] where
*               $qanda has elements [
*                 $qtitle (question_text) : array [
                        all : string; complete HTML?; all has been added for backwards compatibility with templates that use question_start.pstpl (now redundant)
                        'text'               => $qtitle, question?? $ia[3]?
                        'code'               => $ia[2] or title??
                        'number'             => $number
                        'help'               => ''
                        'mandatory'          => ''
                        man_message : string; message when mandatory is not answered
                        'valid_message'      => ''
                        file_valid_message : string; only relevant for file upload
                        'class'              => ''
                        'man_class'          => ''
                        'input_error_class'  => ''              // provides a class.
                        'essentials'         => ''
*                 ]
*                 $answer ?
*                 'help' : string
*                 $display : ?
*                 $qid  : integer
*                 $ia[2] = title;
*                 $ia[5] = group id : int
*                 $ia[1] = fieldname : string
*               ]
*               and $inputnames is ? used for hiddenfieldnames and upload file?
*
*/
function retrieveAnswers($ia)
{
    //globalise required config variables
    global $thissurvey; //These are set by index.php

    $display    = $ia[7]; //DISPLAY
    $qid        = $ia[0]; // Question ID
    $qtitle     = $ia[3];
    $inputnames = array();
    $answer     = ""; //Create the question/answer html
    $number     = isset($ia[9]) ? $ia[9] : ''; // Previously in limesurvey, it was virtually impossible to control how the start of questions were formatted. // this is an attempt to allow users (or rather system admins) some control over how the starting text is formatted.
    $lang       = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang'];
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

    $question_text = array(
        'all'                 => ''              // All has been added for backwards compatibility with templates that use question_start.pstpl (now redundant)
        ,'text'               => $qtitle
        ,'code'               => $ia[2]
        ,'number'             => $number
        ,'help'               => ''
        ,'mandatory'          => ''
        ,'man_message'        => ''
        ,'valid_message'      => ''
        ,'file_valid_message' => ''
        ,'class'              => ''
        ,'man_class'          => ''
        ,'input_error_class'  => ''              // provides a class.
        ,'essentials'         => ''
    );

    $oQuestion = Question::model()->findByPk(array('qid'=>$ia[0], 'language'=>$lang));
    $oQuestionTemplate = QuestionTemplate::getNewInstance($oQuestion);
    $oQuestionTemplate->registerAssets(); // Register the custom assets of the question template, if needed

    switch ($ia[4]) {
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
            ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['prevstep'])) {
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['qattribute_answer'.$ia[1]])) {
                    $message = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['qattribute_answer'.$ia[1]];
                    $question_text['help'] = doRender('/survey/questions/question_help/error', array('message'=>$message, 'classes'=>''), true);
                }
            }
            break;

        case 'L': //LIST drop-down/radio-button list
            $values = do_list_radio($ia);
            break;

        case '!': //List - dropdown
            $values = do_list_dropdown($ia);
            break;

        case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
            $values = do_listwithcomment($ia);
            break;

        case 'R': //RANKING STYLE
            $values = do_ranking($ia);
            break;

        case 'M': //Multiple choice checkbox
            $values = do_multiplechoice($ia);
            break;

        case 'I': //Language Question
            $values = do_language($ia);
            break;

        case 'P': //Multiple choice with comments checkbox + text
            $values = do_multiplechoice_withcomments($ia);
            break;

        case '|': //File Upload
            $values = do_file_upload($ia);
            break;

        case 'Q': //MULTIPLE SHORT TEXT
            $values = do_multipleshorttext($ia);
            break;

        case 'K': //MULTIPLE NUMERICAL QUESTION
            $values = do_multiplenumeric($ia);
            break;

        case 'N': //NUMERICAL QUESTION TYPE
            $values = do_numerical($ia);
            break;

        case 'S': //SHORT FREE TEXT
            $values = do_shortfreetext($ia);
            break;

        case 'T': //LONG FREE TEXT
            $values = do_longfreetext($ia);
            break;

        case 'U': //HUGE FREE TEXT
            $values = do_hugefreetext($ia);
            break;

        case 'Y': //YES/NO radio-buttons
            $values = do_yesno($ia);
            break;

        case 'G': //GENDER drop-down list
            $values = do_gender($ia);
            break;

        case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
            $values = do_array_5point($ia);
            break;

        case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
            $values = do_array_10point($ia);
            break;

        case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
            $values = do_array_yesnouncertain($ia);
            break;

        case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
            $values = do_array_increasesamedecrease($ia);
            break;

        case 'F': //ARRAY (Flexible) - Row Format
            $values = do_array($ia);
            break;

        case 'H': //ARRAY (Flexible) - Column Format
            $values = do_arraycolumns($ia);
            break;

        case ':': //ARRAY (Multi Flexi) 1 to 10
            $values = do_array_multiflexi($ia);
            break;

        case ';': //ARRAY (Multi Flexi) Text
            $values = do_array_texts($ia); //It's like the "5th element" movie, come to life
            break;

        case '1': //Array (Flexible Labels) dual scale
            $values = do_array_dual($ia);
            break;

        case '*': // Equation
            $values = do_equation($ia);
            break;
    }


    if (isset($values)) {
        //Break apart $values array returned from switch
        //$answer is the html code to be printed
        //$inputnames is an array containing the names of each input field
        list($answer, $inputnames) = $values;
    }

    if ($ia[6] == 'Y') {

        //$qtitle .= doRender('/survey/questions/question_help/asterisk', array(), true);
        //$qtitle .= $qtitle;
        //$question_text['mandatory'] = gT('*');
        $question_text['mandatory'] = doRender('/survey/questions/question_help/asterisk', array(), true);
    }

    //If this question is mandatory but wasn't answered in the last page
    //add a message HIGHLIGHTING the question
    $mandatory_msg = (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['prevstep'])) ?mandatory_message($ia) : '';
    $qtitle .= $mandatory_msg;
    $question_text['man_message'] = $mandatory_msg;

    $_vshow = (!isset($aQuestionAttributes['hide_tip']) || $aQuestionAttributes['hide_tip'] == 0) ?true:false; // whether should initially be visible - TODO should also depend upon 'hidetip'?

    list($validation_msg, $isValid) = validation_message($ia, $_vshow);

    $qtitle .= $validation_msg;
    $question_text['valid_message'] = $validation_msg;

    if (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['prevstep'])) {
        $file_validation_msg = file_validation_message($ia);
    } else {
        $file_validation_msg = '';
        $isValid = true; // don't want to show any validation messages.
    }

    $qtitle .= $ia[4] == "|" ? $file_validation_msg : "";
    $question_text['file_valid_message'] = $ia[4] == "|" ? $file_validation_msg : "";

    if (!empty($question_text['man_message']) || !$isValid || !empty($question_text['file_valid_message'])) {
        $question_text['input_error_class'] = ' input-error'; // provides a class to style question wrapper differently if there is some kind of user input error;
    }

    // =====================================================
    // START: legacy question_start.pstpl code
    // The following section adds to the templating system by allowing
    // templaters to control where the various parts of the question text
    // are put.

    $sTemplate = isset($thissurvey['template']) ? $thissurvey['template'] : null;
    if (is_file('templates/'.$sTemplate.'/question_start.pstpl')) {
        $replace = array();
        $find    = array();
        foreach ($question_text as $key => $value) {
            $find[] = '{QUESTION_'.strtoupper($key).'}'; // Match key words from template
            $replace[] = $value; // substitue text
        };

        if (!defined('QUESTION_START')) {
            define('QUESTION_START', file_get_contents(getTemplatePath($thissurvey['template']).'/question_start.pstpl', true));
        };

        $qtitle_custom = str_replace($find, $replace, QUESTION_START);

        $c = 1;
        // START: <EMBED> work-around step 1
        $qtitle_custom = preg_replace('/(<embed[^>]+>)(<\/embed>)/i', '\1NOT_EMPTY\2', $qtitle_custom);
        // END <EMBED> work-around step 1
        while ($c > 0) {
            // This recursively strips any empty tags to minimise rendering bugs.
            $oldtitle = $qtitle_custom;
            $qtitle_custom = preg_replace('/<([^ >]+)[^>]*>[\r\n\t ]*<\/\1>[\r\n\t ]*/isU', '', $qtitle_custom, -1); // I removed the $count param because it is PHP 5.1 only.

            $c = ($qtitle_custom != $oldtitle) ? 1 : 0;
        };
        // START <EMBED> work-around step 2
        $qtitle_custom = preg_replace('/(<embed[^>]+>)NOT_EMPTY(<\/embed>)/i', '\1\2', $qtitle_custom);
        // END <EMBED> work-around step 2
        while ($c > 0) {
            // This recursively strips any empty tags to minimise rendering bugs.
            $oldtitle = $qtitle_custom;
            $qtitle_custom = preg_replace('/(<br(?: ?\/)?>(?:&nbsp;|\r\n|\n\r|\r|\n| )*)+$/i', '', $qtitle_custom, -1); // I removed the $count param because it is PHP 5.1 only.
            $c = ($qtitle_custom != $oldtitle) ? 1 : 0;
        };

        $question_text['all'] = $qtitle_custom;
    } else {
        $question_text['all'] = $qtitle;
    };
    // END: legacy question_start.pstpl code
    //===================================================================
    $qtitle = $question_text;
    // =====================================================

    $qanda = array($qtitle, $answer, 'help', $display, $qid, $ia[2], $ia[5], $ia[1]);
    //New Return
    return array($qanda, $inputnames);
}

function mandatory_message($ia)
{
    $qinfo = LimeExpressionManager::GetQuestionStatus($ia[0]);
    $qinfoValue = ($qinfo['mandViolation']) ? $qinfo['mandTip'] : "";
    return $qinfoValue;
}

/**
*
* @param array $ia
* @param boolean $show - true if should initially be visible
* @return array
*/
function validation_message($ia, $show)
{
    $qinfo      = LimeExpressionManager::GetQuestionStatus($ia[0]);
    $class      = (!$show) ? ' hide-tip' : '';
    $id         = "vmsg_".$ia[0];
    $message    = $qinfo['validTip'];
    if ($message != "") {
        $tip = doRender('/survey/questions/question_help/help', array('message'=>$message, 'classes'=>$class, 'id'=>$id), true);
    } else {
        $tip = "";
    }

    $isValid = $qinfo['valid'];
    return array($tip, $isValid);
}

// TMSW Validation -> EM
function file_validation_message($ia)
{
    global $filenotvalidated;
    $qtitle = "";
    if (isset($filenotvalidated) && is_array($filenotvalidated) && $ia[4] == "|") {
        foreach ($filenotvalidated as $k => $v) {
            if ($ia[1] == $k || strpos($k, "_") && $ia[1] == substr(0, strpos($k, "_") - 1)) {
                $message = gT($filenotvalidated[$k]);
                $qtitle .= doRender('/survey/questions/question_help/error', array('message'=>$message, 'classes'=>''), true);
            }
        }
    }
    return $qtitle;
}

// TMSW Validation -> EM
function mandatory_popup($ia, $notanswered = null)
{
    //This sets the mandatory popup message to show if required
    //Called from question.php, group.php or survey.php
    if ($notanswered === null) {
        unset($notanswered);
    }
    if (isset($notanswered) && is_array($notanswered)) {
        //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
        global $mandatorypopup, $popup;
        //POPUP WARNING
        if (!isset($mandatorypopup) && ($ia[4] == 'T' || $ia[4] == 'S' || $ia[4] == 'U')) {
            $popup = gT("You cannot proceed until you enter some text for one or more questions.");
            $mandatorypopup = "Y";
        } else {
            $popup = gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.");
            $mandatorypopup = "Y";
        }
        return array($mandatorypopup, $popup);
    } else {
        return false;
    }
}

// TMSW Validation -> EM
function validation_popup($ia, $notvalidated = null)
{
    //This sets the validation popup message to show if required
    //Called from question.php, group.php or survey.php
    if ($notvalidated === null) {
        unset($notvalidated);
    }
    if (isset($notvalidated) && is_array($notvalidated)) {
        //ADD WARNINGS TO QUESTIONS IF THEY ARE NOT VALID
        global $validationpopup, $vpopup;
        //POPUP WARNING
        if (!isset($validationpopup)) {
            $vpopup = gT("One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid.");
            $validationpopup = "Y";
        }
        return array($validationpopup, $vpopup);
    } else {
        return false;
    }
}

// TMSW Validation -> EM
/**
* @param boolean $filenotvalidated
*/
function file_validation_popup($ia, $filenotvalidated = null)
{
    if ($filenotvalidated === null) {
        unset($filenotvalidated);
    }
    if (isset($filenotvalidated) && is_array($filenotvalidated)) {
        global $filevalidationpopup, $fpopup;

        if (!isset($filevalidationpopup)) {
            $fpopup = gT("One or more file have either exceeded the filesize/are not in the right format or the minimum number of required files have not been uploaded. You cannot proceed until these have been completed");
            $filevalidationpopup = "Y";
        }
        return array($filevalidationpopup, $fpopup);
    } else {
        return false;
    }
}

/**
* @param string $disable
* @return string
*/
function return_timer_script($aQuestionAttributes, $ia, $disable = null)
{
    global $thissurvey;

    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts").'coookies.js', CClientScript::POS_BEGIN);
    Yii::app()->getClientScript()->registerPackage('timer-addition');

    $langTimer = array(
        'hours'=>gT("hours"),
        'mins'=>gT("mins"),
        'seconds'=>gT("seconds"),
    );
    /* Registering script : don't go to EM : no need usage of ls_json_encode */
    App()->getClientScript()->registerScript("LSVarLangTimer", "LSvar.lang.timer=".json_encode($langTimer).";", CClientScript::POS_BEGIN);
    /**
     * The following lines cover for previewing questions, because no $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldarray'] exists.
     * This just stops error messages occuring
     */
    if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldarray'])) {
        $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldarray'] = array();
    }
    /* End */

    //Used to count how many timer questions in a page, and ensure scripts only load once
    $thissurvey['timercount'] = (isset($thissurvey['timercount'])) ? $thissurvey['timercount']++ : 1;

    /* Work in all mode system : why disable it ? */
    //~ if ($thissurvey['format'] != "S")
    //~ {
    //~ if ($thissurvey['format'] != "G")
    //~ {
    //~ return "\n\n<!-- TIMER MODE DISABLED DUE TO INCORRECT SURVEY FORMAT -->\n\n";
    //~ //We don't do the timer in any format other than question-by-question
    //~ }
    //~ }

    $time_limit = $aQuestionAttributes['time_limit'];
    $disable_next = trim($aQuestionAttributes['time_limit_disable_next']) != '' ? $aQuestionAttributes['time_limit_disable_next'] : 0;
    $disable_prev = trim($aQuestionAttributes['time_limit_disable_prev']) != '' ? $aQuestionAttributes['time_limit_disable_prev'] : 0;
    $time_limit_action = trim($aQuestionAttributes['time_limit_action']) != '' ? $aQuestionAttributes['time_limit_action'] : 1;
    $time_limit_message = trim($aQuestionAttributes['time_limit_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '' ? htmlspecialchars($aQuestionAttributes['time_limit_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']], ENT_QUOTES) : gT("Your time to answer this question has expired");
    $time_limit_warning = trim($aQuestionAttributes['time_limit_warning']) != '' ? $aQuestionAttributes['time_limit_warning'] : 0;
    $time_limit_warning_2 = trim($aQuestionAttributes['time_limit_warning_2']) != '' ? $aQuestionAttributes['time_limit_warning_2'] : 0;
    $time_limit_countdown_message = trim($aQuestionAttributes['time_limit_countdown_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '' ? htmlspecialchars($aQuestionAttributes['time_limit_countdown_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']], ENT_QUOTES) : gT("Time remaining");
    $time_limit_warning_message = trim($aQuestionAttributes['time_limit_warning_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '' ? htmlspecialchars($aQuestionAttributes['time_limit_warning_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']], ENT_QUOTES) : gT("Your time to answer this question has nearly expired. You have {TIME} remaining.");

    //Render timer
    $timer_html = doRender('/survey/questions/question_timer/timer', array('iQid'=>$ia[0], 'sWarnId'=>''), true);
    $time_limit_warning_message = str_replace("{TIME}", $timer_html, $time_limit_warning_message);
    $time_limit_warning_display_time = trim($aQuestionAttributes['time_limit_warning_display_time']) != '' ? $aQuestionAttributes['time_limit_warning_display_time'] + 1 : 0;
    $time_limit_warning_2_message = trim($aQuestionAttributes['time_limit_warning_2_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '' ? htmlspecialchars($aQuestionAttributes['time_limit_warning_2_message'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']], ENT_QUOTES) : gT("Your time to answer this question has nearly expired. You have {TIME} remaining.");

    //Render timer 2
    $timer_html = doRender('/survey/questions/question_timer/timer', array('iQid'=>$ia[0], 'sWarnId'=>'_Warning_2'), true);
    $time_limit_message_delay = trim($aQuestionAttributes['time_limit_message_delay']) != '' ? $aQuestionAttributes['time_limit_message_delay'] * 1000 : 1000;
    $time_limit_warning_2_message = str_replace("{TIME}", $timer_html, $time_limit_warning_2_message);
    $time_limit_warning_2_display_time = trim($aQuestionAttributes['time_limit_warning_2_display_time']) != '' ? $aQuestionAttributes['time_limit_warning_2_display_time'] + 1 : 0;
    $time_limit_message_style = trim($aQuestionAttributes['time_limit_message_style']) != '' ? $aQuestionAttributes['time_limit_message_style'] : "";
    $time_limit_message_class = "hidden ls-timer-content ls-timer-message ls-no-js-hidden";
    $time_limit_warning_style = trim($aQuestionAttributes['time_limit_warning_style']) != '' ? $aQuestionAttributes['time_limit_warning_style'] : "";
    $time_limit_warning_class = "hidden ls-timer-content ls-timer-warning ls-no-js-hidden";
    $time_limit_warning_2_style = trim($aQuestionAttributes['time_limit_warning_2_style']) != '' ? $aQuestionAttributes['time_limit_warning_2_style'] : "";
    $time_limit_warning_2_class = "hidden ls-timer-content ls-timer-warning2 ls-no-js-hidden";
    $time_limit_timer_style = trim($aQuestionAttributes['time_limit_timer_style']) != '' ? $aQuestionAttributes['time_limit_timer_style'] : "position: relative;";
    $time_limit_timer_class = "ls-timer-content ls-timer-countdown ls-no-js-hidden";

    $timersessionname = "timer_question_".$ia[0];
    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$timersessionname])) {
        $time_limit = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$timersessionname];
    }

    $output = doRender('/survey/questions/question_timer/timer_header', array('timersessionname'=>$timersessionname, 'time_limit'=>$time_limit), true);

    if ($thissurvey['timercount'] < 2) {
        $iAction = '';
        if (isset($thissurvey['format']) && $thissurvey['format'] == "G") {
            global $gid;
            $qcount = 0;
            foreach ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldarray'] as $ib) {
                if ($ib[5] == $gid) {
                    $qcount++;
                }
            }
            // Override all other options and just allow freezing, survey is presented in group by group mode
            // Why don't allow submit in Group by group mode, this surely broke 'mandatory' question, but this remove a great system for user (Denis 140224)
            if ($qcount > 1) {
                $iAction = '3';
            }
        }

        /* If this is a preview, don't allow the page to submit/reload */
        $thisaction = returnglobal('action');
        if ($thisaction == "previewquestion" || $thisaction == "previewgroup") {
            $iAction = '3';
        }

        $output .= doRender('/survey/questions/question_timer/timer_javascript', array(
            'timersessionname'=>$timersessionname,
            'time_limit'=>$time_limit,
            'iAction'=>$iAction,
            'disable_next'=>$disable_next,
            'disable_prev'=>$disable_prev,
            'time_limit_countdown_message' =>$time_limit_countdown_message,
            'time_limit_message_delay' => $time_limit_message_delay
            ), true);
    }

    $output .= doRender(
        '/survey/questions/question_timer/timer_content',
        array(
            'iQid'=>$ia[0],
            'time_limit_message_style'=>$time_limit_message_style,
            'time_limit_message_class'=>$time_limit_message_class,
            'time_limit_message'=>$time_limit_message,
            'time_limit_warning_style'=>$time_limit_warning_style,
            'time_limit_warning_class'=>$time_limit_warning_class,
            'time_limit_warning_message'=>$time_limit_warning_message,
            'time_limit_warning_2_style'=>$time_limit_warning_2_style,
            'time_limit_warning_2_class'=>$time_limit_warning_2_class,
            'time_limit_warning_2_message'=>$time_limit_warning_2_message,
            'time_limit_timer_style'=>$time_limit_timer_style,
            'time_limit_timer_class'=>$time_limit_timer_class,
        ),
        true
    );

    $output .= doRender(
        '/survey/questions/question_timer/timer_footer',
        array(
            'iQid'=>$ia[0],
            'iSid'=>Yii::app()->getConfig('surveyID'),
            'time_limit'=>$time_limit,
            'time_limit_action'=>$time_limit_action,
            'time_limit_warning'=>$time_limit_warning,
            'time_limit_warning_2'=>$time_limit_warning_2,
            'time_limit_warning_display_time'=>$time_limit_warning_display_time,
            'time_limit_warning_2_display_time'=>$time_limit_warning_2_display_time,
            'disable'=>$disable,
        ),
        true
    );
    return $output;
}

/**
* Return class of a specific row (hidden by relevance)
* @param int $surveyId actual survey id
* @param string $baseName the base name of the question
* @param string $name The name of the question/row to test
* @param array $aQuestionAttributes the question attributes
* @return string
*/

function currentRelevecanceClass($surveyId, $baseName, $name, $aQuestionAttributes)
{
    $relevanceStatus = !isset($_SESSION["survey_{$surveyId}"]['relevanceStatus'][$name]) || $_SESSION["survey_{$surveyId}"]['relevanceStatus'][$name];
    if ($relevanceStatus) {
        return "";
    }
    $sExcludeAllOther = isset($aQuestionAttributes['exclude_all_others']) ? trim($aQuestionAttributes['exclude_all_others']) : '';
    /* EM don't set difference between relevance in session, if exclude_all_others is set , just ls-disabled */
    if ($sExcludeAllOther) {
        foreach (explode(';', $sExcludeAllOther) as $sExclude) {
            $sExclude = $baseName.$sExclude;
            if ((!isset($_SESSION["survey_{$surveyId}"]['relevanceStatus'][$sExclude]) || $_SESSION["survey_{$surveyId}"]['relevanceStatus'][$sExclude])
            && (isset($_SESSION["survey_{$surveyId}"][$sExclude]) && $_SESSION["survey_{$surveyId}"][$sExclude] == "Y")
            ) {
                return "ls-irrelevant ls-disabled";
            }
        }
    }

    $filterStyle = !empty($aQuestionAttributes['array_filter_style']); // Currently null/0/false=> hidden , 1 : disabled
    if ($filterStyle) {
        return "ls-irrelevant ls-disabled";
    }
    return "ls-irrelevant ls-hidden";
}
/**
* @param string $rowname
*/
function return_display_style($ia, $aQuestionAttributes, $thissurvey, $rowname)
{
    /* Disabled actually : no inline style */
    return "";
    //~ $htmltbody2 = '';
    //~ $surveyid=$thissurvey['sid'];
    //~ if (isset($_SESSION["survey_{$surveyid}"]['relevanceStatus'][$rowname]) && !$_SESSION["survey_{$surveyid}"]['relevanceStatus'][$rowname])
    //~ {
    //~ // If using exclude_all_others, then need to know whether irrelevant rows should be hidden or disabled

    //~ }

    //~ return $htmltbody2;
}

/**
* @param string $rowname
* @param string $valuename
*/
function return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $rowname, $trbc = '', $valuename, $method = "tbody", $class = null)
{
    $htmltbody2 = "\n\n\t<$method id='javatbd$rowname'";
    $htmltbody2 .= ($class !== null) ? " class='$class'" : "";
    $surveyid = $thissurvey['sid'];
    if (isset($_SESSION["survey_{$surveyid}"]['relevanceStatus'][$rowname]) && !$_SESSION["survey_{$surveyid}"]['relevanceStatus'][$rowname]) {
        // If using exclude_all_others, then need to know whether irrelevant rows should be hidden or disabled
        if (isset($aQuestionAttributes['exclude_all_others'])) {
            $disableit = false;
            foreach (explode(';', trim($aQuestionAttributes['exclude_all_others'])) as $eo) {
                $eorow = $ia[1].$eo;
                if ((!isset($_SESSION["survey_{$surveyid}"]['relevanceStatus'][$eorow]) || $_SESSION["survey_{$surveyid}"]['relevanceStatus'][$eorow])
                && (isset($_SESSION[$eorow]) && $_SESSION[$eorow] == "Y")) {
                    $disableit = true;
                }
            }
            if ($disableit) {
                $htmltbody2 .= " disabled='disabled'";
            } else {
                if (!isset($aQuestionAttributes['array_filter_style']) || $aQuestionAttributes['array_filter_style'] == '0') {
                    $htmltbody2 .= " style='display: none'";
                } else {
                    $htmltbody2 .= " disabled='disabled'";
                }
            }
        } else {
            if (!isset($aQuestionAttributes['array_filter_style']) || $aQuestionAttributes['array_filter_style'] == '0') {
                $htmltbody2 .= " style='display: none'";
            } else {
                $htmltbody2 .= " disabled='disabled'";
            }
        }
    }
    $htmltbody2 .= ">\n";
    return array($htmltbody2, "");
}

/**
* @param string $sUseKeyPad
* @return string
*/
function testKeypad($sUseKeyPad)
{
    if ($sUseKeyPad == 'Y') {
        includeKeypad();
        $kpclass = "text-keypad";
    } else {
        $kpclass = "";
    }
    return $kpclass;
}



// ==================================================================
// QUESTION METHODS =================================================

function do_boilerplate($ia)
{
    //$aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $answer = '';
    $inputnames = array();
    $sTimer              = (trim($aQuestionAttributes['time_limit']) != '') ? return_timer_script($aQuestionAttributes, $ia) : ''; //Time Limit

    $answer .= doRender('/survey/questions/answer/boilerplate/answer', array(
        'sTimer'=>$sTimer,
        'ia'=>$ia,
        'name'=>$ia[1],
        'basename'=>$ia[1], /* is this needed ? */
        'coreClass'=>'ls-answers hidden',
        ), true);
    $inputnames[] = $ia[1];

    return array($answer, $inputnames);
}

function do_equation($ia)
{
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $sEquation           = (trim($aQuestionAttributes['equation'])) ? $aQuestionAttributes['equation'] : $ia[3];
    $sValue              = htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]], ENT_QUOTES);
    $inputnames = array();

    $answer = doRender('/survey/questions/answer/equation/answer', array(
        'name'      => $ia[1],
        'basename'  => $ia[1],
        'sValue'    => $sValue,
        'sEquation' => $sEquation,
        'coreClass' => 'ls-answers em_equation hidden'
        ), true);

    $inputnames[] = $ia[1];
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_5pointchoice($ia)
{
    $checkconditionFunction = "checkconditions";
    //$aQuestionAttributes=  QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $inputnames = array();

    $aRows = array();
    ;
    for ($fp = 1; $fp <= 5; $fp++) {
        $checkedState = '';
        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $fp) {
            //$answer .= CHECKED;
            $checkedState = ' CHECKED ';
        }

        $aRows[] = array(
            'name'                   => $ia[1],
            'value'                  => $fp,
            'id'                     => $ia[1].$fp,
            'labelText'              => $fp,
            'itemExtraClass'         => '',
            'checkedState'           => $checkedState,
            'checkconditionFunction' => $checkconditionFunction,
            );
    }

    if ($ia[6] != "Y" && SHOW_NO_ANSWER == 1) {
        // Add "No Answer" option if question is not mandatory
        $checkedState = '';
        if (!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) {
            $checkedState = ' CHECKED ';
        }
        $aRows[] = array(
            'name'                   => $ia[1],
            'value'                  => "",
            'id'                     => $ia[1],
            'labelText'              => gT('No answer'),
            'itemExtraClass'         => 'noanswer-item',
            'checkedState'           => $checkedState,
            'checkconditionFunction' => $checkconditionFunction,
        );
    }
    $sessionValue = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]];

    $inputnames[] = $ia[1];

    $slider_rating = 0;

    if ($aQuestionAttributes['slider_rating'] == 1) {
        $slider_rating = 1;
        Yii::app()->getClientScript()->registerPackage('question-5pointchoice-star');
        Yii::app()->getClientScript()->registerScript('doRatingStar_'.$ia[0], "doRatingStar('".$ia[0]."'); ", LSYii_ClientScript::POS_POSTSCRIPT);
    }
    
    if ($aQuestionAttributes['slider_rating'] == 2) {
        $slider_rating = 2;
        Yii::app()->getClientScript()->registerPackage('question-5pointchoice-slider');
        Yii::app()->getClientScript()->registerScript('doRatingSlider_'.$ia[0], "
            var doRatingSlider_".$ia[1]."= new getRatingSlider('".$ia[0]."');
            doRatingSlider_".$ia[1]."();
        ", LSYii_ClientScript::POS_POSTSCRIPT);
    }


    $answer = doRender('/survey/questions/answer/5pointchoice/answer', array(
        'coreClass'     => "ls-answers answers-list radio-list",
        'sliderId'      => $ia[0],
        'name'          => $ia[1],
        'basename'      => $ia[1],
        'sessionValue'  => $sessionValue,
        'aRows'         => $aRows,
        'slider_rating' => $slider_rating,

        ), true);

    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_date($ia)
{
    global $thissurvey;
    $aQuestionAttributes    = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $checkconditionFunction = "checkconditions";
    $dateformatdetails      = getDateFormatDataForQID($aQuestionAttributes, $thissurvey);
    $inputnames = array();
    $coreClass = "ls-answers answer-item date-item";

    $sDateLangvarJS = " translt = {
    alertInvalidDate: '" . gT('Date entered is invalid!', 'js')."',
    };";

    $dateparts = [
        'year' => gT('Year'),
        'month' => gT('Month'),
        'day' => gT('Day'),
        'hour' => gT('Hour'),
        'minute' => gT('Minute'),
        'second' => gT('Second'),
        'millisecond' => gT('Millisecond')
    ];

    App()->getClientScript()->registerScript("sDateLangvarJS", $sDateLangvarJS, CClientScript::POS_BEGIN);
    App()->getClientScript()->registerPackage('moment');
    App()->getClientScript()->registerPackage('bootstrap-datetimepicker');
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts").'date.js', CClientScript::POS_END);

    // date_min: Determine whether we have an expression, a full date (YYYY-MM-DD) or only a year(YYYY)
    if (trim($aQuestionAttributes['date_min']) != '') {
        $date_min      = trim($aQuestionAttributes['date_min']);
        $date_time_em  = strtotime(LimeExpressionManager::ProcessString("{".$date_min."}", $ia[0]));

        if (ctype_digit($date_min) && (strlen($date_min) == 4) && ($date_min >= 1900) && ($date_min <= 2099)) {
            $mindate = $date_min.'-01-01'; // backward compatibility: if only a year is given, add month and day
        } elseif (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/", $date_min)) {
            // it's a YYYY-MM-DD date (use http://www.yiiframework.com/doc/api/1.1/CDateValidator ?)
            $mindate = $date_min;
        } elseif ($date_time_em !== false) {
            $mindate = (string) date("Y-m-d", $date_time_em);
        } else {
            $mindate = '{'.$aQuestionAttributes['date_min'].'}';
        }
    } else {
        $mindate = '1900-01-01'; // Why 1900 ?
    }

    // date_max: Determine whether we have an expression, a full date (YYYY-MM-DD) or only a year(YYYY)
    if (trim($aQuestionAttributes['date_max']) != '') {
        $date_max     = trim($aQuestionAttributes['date_max']);
        $date_time_em = strtotime(LimeExpressionManager::ProcessString("{".$date_max."}", $ia[0]));

        if (ctype_digit($date_max) && (strlen($date_max) == 4) && ($date_max >= 1900) && ($date_max <= 2099)) {
            $maxdate = $date_max.'-12-31'; // backward compatibility: if only a year is given, add month and day
        } elseif (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/", $date_max)) {
            // it's a YYYY-MM-DD date (use http://www.yiiframework.com/doc/api/1.1/CDateValidator ?)
            $maxdate = $date_max;
        } elseif ($date_time_em !== false) {
            $maxdate = (string) date("Y-m-d", $date_time_em);
        } else {
            $maxdate = '{'.$aQuestionAttributes['date_max'].'}';
        }
    } else {
        $maxdate = '2037-12-31'; // Why 2037 ?
    }

    $dateoutput = trim($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]);
    if (!empty($dateoutput)) {
        $dateoutput = fillDate($dateoutput);
    }
    if (trim($aQuestionAttributes['dropdown_dates']) == 1) {
        $coreClass .= " dropdown-item"; // items ?
        if (!empty($dateoutput)) {
            $datetimeobj   = new Date_Time_Converter($dateoutput, "Y-m-d H:i");
            $currentyear   = $datetimeobj->years;
            $currentmonth  = $datetimeobj->months;
            $currentday   = $datetimeobj->days;
            $currenthour   = $datetimeobj->hours;
            $currentminute = $datetimeobj->minutes;
        } else {
            // If date is invalid get the POSTED value
            $currentday   = App()->request->getPost("day{$ia[1]}", '');
            $currentmonth  = App()->request->getPost("month{$ia[1]}", '');
            $currentyear   = App()->request->getPost("year{$ia[1]}", '');
            $currenthour   = App()->request->getPost("hour{$ia[1]}", '');
            $currentminute = App()->request->getPost("minute{$ia[1]}", '');
        }
        $dateorder = preg_split('/([-\.\/ :])/', $dateformatdetails['phpdate'], -1, PREG_SPLIT_DELIM_CAPTURE);

        $sRows = '';
        $montharray = array();
        foreach ($dateorder as $datepart) {
            switch ($datepart) {
                // Show day select box
                case 'j':
                case 'd':
                    $sRows .= doRender('/survey/questions/answer/date/dropdown/rows/day', array('dayId'=>$ia[1], 'currentday'=>$currentday), true);
                    break;
                    // Show month select box
                case 'n':
                case 'm':
                switch ((int) trim($aQuestionAttributes['dropdown_dates_month_style'])) {
                    case 0:
                        $montharray = array(
                            gT('Jan'),
                            gT('Feb'),
                            gT('Mar'),
                            gT('Apr'),
                            gT('May'),
                            gT('Jun'),
                            gT('Jul'),
                            gT('Aug'),
                            gT('Sep'),
                            gT('Oct'),
                            gT('Nov'),
                            gT('Dec'));
                        break;
                    case 1:
                        $montharray = array(
                            gT('January'),
                            gT('February'),
                            gT('March'),
                            gT('April'),
                            gT('May'),
                            gT('June'),
                            gT('July'),
                            gT('August'),
                            gT('September'),
                            gT('October'),
                            gT('November'),
                            gT('December'));
                        break;
                    case 2:
                        $montharray = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
                        break;
                }

                $sRows .= doRender('/survey/questions/answer/date/dropdown/rows/month', array('monthId'=>$ia[1], 'currentmonth'=>$currentmonth, 'montharray'=>$montharray), true);
                break;
                // Show year select box
                case 'y':
                case 'Y':
                    /*
                    * yearmin = Minimum year value for dropdown list, if not set default is 1900
                    * yearmax = Maximum year value for dropdown list, if not set default is 2037
                    * if full dates (format: YYYY-MM-DD) are given, only the year is used
                    * expressions are not supported because contents of dropbox cannot be easily updated dynamically
                    */
                    $yearmin = (int) substr($mindate, 0, 4);
                    if (!isset($yearmin) || $yearmin < 1900 || $yearmin > 2037) {
                        $yearmin = 1900;
                    }

                    $yearmax = (int) substr($maxdate, 0, 4);
                    if (!isset($yearmax) || $yearmax < 1900 || $yearmax > 2037) {
                        $yearmax = 2037;
                    }

                    if ($yearmin > $yearmax) {
                        $yearmin = 1900;
                        $yearmax = 2037;
                    }

                    if ($aQuestionAttributes['reverse'] == 1) {
                        $tmp = $yearmin;
                        $yearmin = $yearmax;
                        $yearmax = $tmp;
                        $step = 1;
                        $reverse = true;
                    } else {
                        $step = -1;
                        $reverse = false;
                    }
                    $sRows .= doRender('/survey/questions/answer/date/dropdown/rows/year', array('yearId'=>$ia[1], 'currentyear'=>$currentyear, 'yearmax'=>$yearmax, 'reverse'=>$reverse, 'yearmin'=>$yearmin, 'step'=>$step), true);
                    break;
                case 'H':
                case 'h':
                case 'g':
                case 'G':
                    $sRows .= doRender('/survey/questions/answer/date/dropdown/rows/hour', array('hourId'=>$ia[1], 'currenthour'=>$currenthour, 'datepart'=>$datepart), true);
                    break;
                case 'i':
                    $sRows .= doRender('/survey/questions/answer/date/dropdown/rows/minute', array('minuteId'=>$ia[1], 'currentminute'=>$currentminute, 'dropdown_dates_minute_step'=>$aQuestionAttributes['dropdown_dates_minute_step'], 'datepart'=>$datepart), true);
                    break;
                default:
                    $sRows .= doRender('/survey/questions/answer/date/dropdown/rows/datepart', array('datepart'=>$datepart), true);

            }
        }
        // Format the date  for output
        if ($dateoutput != '') {
            $datetimeobj = DateTime::createFromFormat('!Y-m-d H:i', $dateoutput);
            if ($datetimeobj) {
                $dateoutput = $datetimeobj->format($dateformatdetails['phpdate']);
            } else {
                $dateoutput = '';
            }
        }


        // ==> answer
        $answer = doRender('/survey/questions/answer/date/dropdown/answer', array(
            'sRows'                  => $sRows,
            'coreClass'              => $coreClass,
            'name'                   => $ia[1],
            'basename'               => $ia[1],
            'dateoutput'             => htmlspecialchars($dateoutput, ENT_QUOTES, 'utf-8'),
            'checkconditionFunction' => $checkconditionFunction.'(this.value, this.name, this.type)',
            'dateformatdetails'      => $dateformatdetails['jsdate'],
            'dateformat'             => $dateformatdetails['jsdate'],
            ), true);

        App()->getClientScript()->registerScript('doDropDownDate'.$ia[0], "doDropDownDate({$ia[0]});", LSYii_ClientScript::POS_POSTSCRIPT);
    } else {
        $coreClass .= " text-item";
        // Format the date  for output
        if ($dateoutput != '') {
            $datetimeobj = DateTime::createFromFormat('!Y-m-d H:i', $dateoutput);
            if ($datetimeobj) {
                $dateoutput = $datetimeobj->format($dateformatdetails['phpdate']);
            } else {
                $dateoutput = '';
            }
        }

        // Max length of date : Get the date of 1999-12-30 at 32:59:59 to be sure to have space with non leading 0 format
        // "+1" makes room for a trailing space in date/time values
        $iLength = strlen(date($dateformatdetails['phpdate'], mktime(23, 59, 59, 12, 30, 1999))) + 1;

        // Hide calendar (but show hour/minute) if there's no year, month or day in format
        $hideCalendar = strpos($dateformatdetails['jsdate'], 'Y') === false
        && strpos($dateformatdetails['jsdate'], 'D') === false
        && strpos($dateformatdetails['jsdate'], 'M') === false;
        /* Global datepicker configuration, muts be done before view (and twig from template can extend it then :) */
        if (!App()->getClientScript()->isScriptRegistered("setDatePickerGlobalOption", LSYii_ClientScript::POS_POSTSCRIPT)) {
            App()->getClientScript()->registerPackage('bootstrap-datetimepicker');
            
            $aDefaultDatePicker = array(
                'locale'=>convertLStoDateTimePickerLocale(App()->language),
                'tooltips' => array(
                    'clear'=> gT('Clear selection'),
                    'prevMonth'=> gT('Previous month'),
                    'nextMonth'=> gT('Next month'),
                    'selectYear'=> gT('Select year'),
                    'prevYear'=> gT('Previous year'),
                    'nextYear'=> gT('Next year'),
                    'selectDecade'=> gT('Select decade'),
                    'prevDecade'=> gT('Previous decade'),
                    'nextDecade'=> gT('Next decade'),
                    'prevCentury'=> gT('Previous century'),
                    'nextCentury'=> gT('Next century'),
                    'selectTime'=> gT('Select time')
                ),
                'icons' => array(
                    'time'=> 'fa fa-clock-o',
                    'date'=> 'fa fa-calendar',
                    'up'=> 'fa fa-chevron-up',
                    'down'=> 'fa fa-chevron-down',
                    'previous'=> 'fa fa-chevron-left',
                    'next'=> 'fa fa-chevron-right',
                    'today'=> 'fa fa-calendar-check-o',
                    'clear'=> 'fa fa-trash-o',
                    'close'=> 'fa fa-closee'
                ),
                'allowInputToggle' =>true,
                'showClear' => true,
                'sideBySide' => true,
                //~ 'debug'=>true
            );
            App()->getClientScript()->registerScript("setDatePickerGlobalOption", "$.extend( $.fn.datetimepicker.defaults, ".json_encode($aDefaultDatePicker)." )", LSYii_ClientScript::POS_POSTSCRIPT);
        }
        // HTML for date question using datepicker
        $answer = doRender('/survey/questions/answer/date/selector/answer', array(
            'name'                   => $ia[1],
            'basename'               => $ia[1],
            'coreClass'              => $coreClass,
            'iLength'                => $iLength,
            'mindate'                => $mindate,
            'maxdate'                => $maxdate,
            'dateformatdetails'      => $dateformatdetails['dateformat'],
            'dateformatdetailsjs'    => $dateformatdetails['jsdate'],
            'dateformatdetailsphp'   => $dateformatdetails['phpdate'],
            'minuteStep'             => $aQuestionAttributes['dropdown_dates_minute_step'],
            'goodchars'              => "", // "return window.LS.goodchars(event,'".$goodchars."')", //  This won't work with non-latin keyboards
            'checkconditionFunction' => $checkconditionFunction.'(this.value, this.name, this.type)',
            'language'               => App()->language,
            'hidetip'                => trim($aQuestionAttributes['hide_tip']) == 0,
            'dateoutput'             => $dateoutput,
            'qid'                    => $ia[0],
            'hideCalendar'           => $hideCalendar
            ), true);
        App()->getClientScript()->registerScript('doPopupDate'.$ia[0], "doPopupDate({$ia[0]});", LSYii_ClientScript::POS_POSTSCRIPT);
    }
    $inputnames[] = $ia[1];

    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_language($ia)
{
    $checkconditionFunction = "checkconditions";
    $answerlangs            = Survey::model()->findByPk(Yii::app()->getConfig('surveyID'))->additionalLanguages;
    $answerlangs[]          = Survey::model()->findByPk(Yii::app()->getConfig('surveyID'))->language;
    $sLang                  = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang'];
    $coreClass              = "ls-answers answer-item dropdow-item langage-item";
    $inputnames = array();

    if (!in_array($sLang, $answerlangs)) {
        $sLang = Survey::model()->findByPk(Yii::app()->getConfig('surveyID'))->language;
    }

    $inputnames[] = $ia[1];

    $languageData = array(
        'name'=>$ia[1],
        'basename'=> $ia[1],
        'checkconditionFunction'=>$checkconditionFunction.'(this.value, this.name, this.type)',
        'answerlangs'=>$answerlangs,
        'sLang'=>$sLang,
        'coreClass'=>$coreClass,
    );

    $answer = doRender('/survey/questions/answer/language/answer', $languageData, true);
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_list_dropdown($ia)
{
    //// Init variables
    $inputnames = array();

    // General variables
    $checkconditionFunction = "checkconditions";

    // Question attribute variables
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $iSurveyId              = Yii::app()->getConfig('surveyID'); // survey id
    $sSurveyLang            = $_SESSION['survey_'.$iSurveyId]['s_lang']; // survey language
    $othertext              = (trim($aQuestionAttributes['other_replace_text'][$sSurveyLang]) != '') ? $aQuestionAttributes['other_replace_text'][$sSurveyLang] : gT('Other:'); // text for 'other'
    $optCategorySeparator   = (trim($aQuestionAttributes['category_separator']) != '') ? $aQuestionAttributes['category_separator'] : '';
    $coreClass              = "ls-answers answer-item dropdown-item";

    if ($optCategorySeparator == '') {
        unset($optCategorySeparator);
    }

    //// Retrieving datas

    // Getting question
    $oQuestion = Question::model()->findByPk(array('qid'=>$ia[0], 'language'=>$sSurveyLang));
    $other     = $oQuestion->other;

    // Getting answers
    $ansresult = $oQuestion->getOrderedAnswers($aQuestionAttributes['random_order'], $aQuestionAttributes['alphasort']);

    $dropdownSize = null;

    if (isset($aQuestionAttributes['dropdown_size']) && $aQuestionAttributes['dropdown_size'] > 0) {
        $_height    = sanitize_int($aQuestionAttributes['dropdown_size']);
        $_maxHeight = count($ansresult);

        if ((!is_null($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] === '') && $ia[6] != 'Y' && $ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
            ++$_maxHeight; // for No Answer
        }

        if (isset($other) && $other == 'Y') {
            ++$_maxHeight; // for Other
        }

        if (is_null($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]])) {
            ++$_maxHeight; // for 'Please choose:'
        }

        if ($_height > $_maxHeight) {
            $_height = $_maxHeight;
        }
        $dropdownSize = $_height;
    }

    $prefixStyle = 0;

    if (isset($aQuestionAttributes['dropdown_prefix'])) {
        $prefixStyle = sanitize_int($aQuestionAttributes['dropdown_prefix']);
    }

    $_rowNum = 0;
    $_prefix = '';

    $value            = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]];
    $sOptions         = '';

    // If no answer previously selected
    if (is_null($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] === '') {
        $sOptions .= doRender('/survey/questions/answer/list_dropdown/rows/option', array(
            'name'=> $ia[1],
            'value'=>'',
            'opt_select'=> ($dropdownSize) ? SELECTED : "", /* needed width size, not for single first one */
            'answer'=>gT('Please choose...')
            ), true);
    }

    if (!isset($optCategorySeparator)) {
        foreach ($ansresult as $ansrow) {
            $opt_select = '';
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow['code']) {
                $opt_select = SELECTED;
            }
            if ($prefixStyle == 1) {
                $_prefix = ++$_rowNum.') ';
            }
            // ==> rows
            $sOptions .= doRender('/survey/questions/answer/list_dropdown/rows/option', array(
                'name'=> $ia[1],
                'value'=>$ansrow['code'],
                'opt_select'=>$opt_select,
                'answer'=>$_prefix.$ansrow['answer'],
                ), true);
        }
    } else {
        $defaultopts = array();
        $optgroups = array();
        foreach ($ansresult as $ansrow) {
            // Let's sort answers in an array indexed by subcategories
            @list($categorytext, $answertext) = explode($optCategorySeparator, $ansrow['answer']);
            // The blank category is left at the end outside optgroups
            if ($categorytext == '') {
                $defaultopts[] = array('code' => $ansrow['code'], 'answer' => $answertext);
            } else {
                $optgroups[$categorytext][] = array('code' => $ansrow['code'], 'answer' => $answertext);
            }
        }

        foreach ($optgroups as $categoryname => $optionlistarray) {
            $sOptGroupOptions = '';
            foreach ($optionlistarray as $optionarray) {
                if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $optionarray['code']) {
                    $opt_select = SELECTED;
                } else {
                    $opt_select = '';
                }

                // ==> rows
                $sOptGroupOptions .= doRender('/survey/questions/answer/list_dropdown/rows/option', array(
                    'name'=> $ia[1],
                    'value'=>$optionarray['code'],
                    'opt_select'=>$opt_select,
                    'answer'=>flattenText($optionarray['answer'])
                    ), true);
            }


            $sOptions .= doRender('/survey/questions/answer/list_dropdown/rows/optgroup', array(
                'categoryname'      => flattenText($categoryname),
                'sOptGroupOptions'  => $sOptGroupOptions,
                ), true);
        }
        foreach ($defaultopts as $optionarray) {
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $optionarray['code']) {
                $opt_select = SELECTED;
            } else {
                $opt_select = '';
            }

            // ==> rows
            $sOptions .= doRender('/survey/questions/answer/list_dropdown/rows/option', array(
                'name'=> $ia[1],
                'value'=>$optionarray['code'],
                'opt_select'=>$opt_select,
                'answer'=>flattenText($optionarray['answer'])
                ), true);
        }
    }

    if (isset($other) && $other == 'Y') {
        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '-oth-') {
            $opt_select = SELECTED;
        } else {
            $opt_select = '';
        }
        if ($prefixStyle == 1) {
            $_prefix = ++$_rowNum.') ';
        }

        $sOptions .= doRender('/survey/questions/answer/list_dropdown/rows/option', array(
            'name'=> $ia[1],
            'classes'=>'other-item',
            'value'=>'-oth-',
            'opt_select'=>$opt_select,
            'answer'=>flattenText($_prefix.$othertext)
            ), true);
    }

    if (!(is_null($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] === "") && $ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        if ($prefixStyle == 1) {
            $_prefix = ++$_rowNum.') ';
        }

        $optionData = array(
            'name'=> $ia[1],
            'classes'=>'noanswer-item',
            'value'=>'',
            'opt_select'=> '', // Never selected
            'answer'=>$_prefix.gT('No answer')
        );
        // ==> rows
        $sOptions .= doRender('/survey/questions/answer/list_dropdown/rows/option', $optionData, true);
    }

    $sOther = '';
    if (isset($other) && $other == 'Y') {
        $aData = array();
        $aData['name'] = $ia[1];
        $aData['checkconditionFunction'] = $checkconditionFunction;
        $aData['display'] = ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] != '-oth-') ? 'display: none;' : '';
        $aData['label'] = $othertext;
        $thisfieldname = "$ia[1]other";
        $aData['value'] = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname])) ?htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname], ENT_QUOTES) : '';

        // ==> other
        $sOther .= doRender('/survey/questions/answer/list_dropdown/rows/othertext', $aData, true);

        $inputnames[] = $ia[1].'other';
    }

    // ==> answer
    $answer = doRender('/survey/questions/answer/list_dropdown/answer', array(
        'sOptions'               => $sOptions,
        'sOther'                 => $sOther,
        'name'                   => $ia[1],
        'basename'               => $ia[1],
        'dropdownSize'           => $dropdownSize,
        'checkconditionFunction' => $checkconditionFunction,
        'value'                  => $value,
        'coreClass'              => $coreClass
        ), true);


    $inputnames[] = $ia[1];

    //Time Limit Code
    if (trim($aQuestionAttributes['time_limit']) != '') {
        $answer .= return_timer_script($aQuestionAttributes, $ia);
    }
    //End Time Limit Code

    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM

function do_list_radio($ia)
{
    //// Init variables

    // General variables
    global $thissurvey;
    $kpclass                = testKeypad($thissurvey['nokeyboard']); // Virtual keyboard (probably obsolete today)
    $checkconditionFunction = "checkconditions"; // name of the function to check condition TODO : check is used more than once
    $iSurveyId              = Yii::app()->getConfig('surveyID'); // survey id
    $sSurveyLang            = $_SESSION['survey_'.$iSurveyId]['s_lang']; // survey language
    $inputnames = array();
    $coreClass = "ls-answers answers-list radio-list";
    // Question attribute variables

    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $othertext           = (trim($aQuestionAttributes['other_replace_text'][$sSurveyLang]) != '') ? $aQuestionAttributes['other_replace_text'][$sSurveyLang] : gT('Other:'); // text for 'other'
    $iNbCols             = (trim($aQuestionAttributes['display_columns']) != '') ? $aQuestionAttributes['display_columns'] : 1; // number of columns
    $sTimer              = (trim($aQuestionAttributes['time_limit']) != '') ?return_timer_script($aQuestionAttributes, $ia) : ''; //Time Limit
    //// Retrieving datas

    // Getting question
    $oQuestion = Question::model()->findByPk(array('qid'=>$ia[0], 'language'=>$sSurveyLang));
    $other     = $oQuestion->other;

    // Getting answers
    $ansresult = $oQuestion->getOrderedAnswers($aQuestionAttributes['random_order'], $aQuestionAttributes['alphasort']);
    $anscount  = count($ansresult);
    $anscount  = ($other == 'Y') ? $anscount + 1 : $anscount; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
    $anscount  = ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) ? $anscount + 1 : $anscount; //Count up if "No answer" is showing

    //// Columns containing answer rows, set by user in question attribute
    /// TODO : move to a dedicated function

    // setting variables
    $iRowCount        = 0;
    $isOpen           = false; // Is a column opened

    if ($iNbCols > 1) {
        // Add a class on the wrapper
        $coreClass .= " multiple-list nbcol-{$iNbCols}";
        // First we calculate the width of each column
        // Max number of column is 12 http://getbootstrap.com/css/#grid
        $iColumnWidth = round(12 / $iNbCols);
        $iColumnWidth = ($iColumnWidth >= 1) ? $iColumnWidth : 1;
        $iColumnWidth = ($iColumnWidth <= 12) ? $iColumnWidth : 12;

        // Then, we calculate how many answer rows in each column
        $iMaxRowsByColumn = ceil($anscount / $iNbCols);
    } else {
        $iColumnWidth = 12;
        $iMaxRowsByColumn = $anscount + 3; // No max : anscount + no answer + other + 1 by security
    }

    // Get array_filter stuff

    $i = 0;

    $sRows = '';
    foreach ($ansresult as $key=>$ansrow) {
        $i++; // general count of loop, to check if the item is the last one for column process. Never reset.
        $iRowCount++; // counter of number of row by column. Is reset to zero each time a column is full.
        $myfname = $ia[1].$ansrow['code'];

        $checkedState = '';
        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow['code']) {
            $checkedState = 'CHECKED';
        }

        //list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, '', $myfname, "div","form-group answer-item radio-item");
        /* Check for array_filter */
        $sDisplayStyle = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);

        ////
        // Open Column
        // The column is opened if user set more than one column in question attribute
        // and if this is the first answer row, or if the column has been closed and the row count reset before.
        if ($iRowCount == 1) {
            $sRows  .= doRender('/survey/questions/answer/listradio/columns/column_header', array('iColumnWidth' => $iColumnWidth), true);
            $isOpen  = true; // If a column is not closed, it will be closed at the end of the process
        }


        ////
        // Insert row
        // Display the answer row
        $sRows .= doRender('/survey/questions/answer/listradio/rows/answer_row', array(
            'sDisplayStyle' => $sDisplayStyle,
            'name'          => $ia[1],
            'code'          => $ansrow['code'],
            'answer'        => $ansrow['answer'],
            'checkedState'  => $checkedState,
            'myfname'       => $myfname,
            'i'             => $i,
            ), true);

        ////
        // Close column
        // The column is closed if the user set more than one column in question attribute
        // and if the max answer rows by column is reached.
        // If max answer rows by column is not reached while there is no more answer,
        // the column will remain opened, and it will be closed by 'other' answer row if set or at the end of the process
        if ($iRowCount == $iMaxRowsByColumn) {
            $last      = ($i == $anscount) ?true:false; // If this loop count equal to the number of answers, then this answer is the last one.
            $sRows    .= doRender('/survey/questions/answer/listradio/columns/column_footer', array('last'=>$last), true);
            $iRowCount = 0;
            $isOpen    = false;
        }
    }

    if (isset($other) && $other == 'Y') {
        $iRowCount++;
        $i++;
        $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator = $sSeparator['separator'];

        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '-oth-') {
            $checkedState = CHECKED;
        } else {
            $checkedState = '';
        }

        $myfname = $thisfieldname = $ia[1].'other';

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname])) {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname];
            if ($aQuestionAttributes['other_numbers_only'] == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $answer_other = ' value="'.htmlspecialchars($dispVal, ENT_QUOTES).'"';
        } else {
            $answer_other = ' value=""';
        }

        ////
        // Open Column
        // The column is opened if user set more than one column in question attribute
        // and if this is the first answer row (should never happen for 'other'),
        // or if the column has been closed and the row count reset before.
        if ($iRowCount == 1) {
            $sRows .= doRender('/survey/questions/answer/listradio/columns/column_header', array('iColumnWidth' => $iColumnWidth, 'first'=>false), true);
        }
        $sDisplayStyle = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);

        ////
        // Insert row
        // Display the answer row
        $sRows .= doRender('/survey/questions/answer/listradio/rows/answer_row_other', array(
            'name' => $ia[1],
            'answer_other'=>$answer_other,
            'myfname'=>$myfname,
            'sDisplayStyle' => $sDisplayStyle,
            'othertext'=>$othertext,
            'checkedState'=>$checkedState,
            'kpclass'=>$kpclass,
            'checkconditionFunction'=>$checkconditionFunction,
            'numbers_only' => ($aQuestionAttributes['other_numbers_only'] == 1),
            ), true);

        $inputnames[] = $thisfieldname;

        ////
        // Close column
        // The column is closed if the user set more than one column in question attribute
        // We can't be sure it's the last one because of 'no answer' item
        if ($iRowCount == $iMaxRowsByColumn) {
            $sRows .= doRender('/survey/questions/answer/listradio/columns/column_footer', array(), true);
            $iRowCount = 0;
            $isOpen = false;
        }
    }

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        $iRowCount++;

        if ((!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '') || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == ' ')) {
            $check_ans = CHECKED; //Check the "no answer" radio button if there is no answer in session.
        } else {
            $check_ans = '';
        }

        if ($iRowCount == 1) {
            $sRows .= doRender('/survey/questions/answer/listradio/columns/column_header', array('iColumnWidth' => $iColumnWidth), true);
        }

        $sRows .= doRender('/survey/questions/answer/listradio/rows/answer_row_noanswer', array(
            'name'=>$ia[1],
            'check_ans'=>$check_ans,
            'checkconditionFunction'=>$checkconditionFunction,
            ), true);


        ////
        // Close column
        // 'No answer' is always the last answer, so it's always closing the col and the bootstrap row containing the columns
        $sRows .= doRender('/survey/questions/answer/listradio/columns/column_footer', array('last'=>true), true);
        $isOpen = false;
    }

    ////
    // Close column
    // if on column has been opened and not closed
    // That can happen only when no 'other' option is set, and the maximum answer rows has not been reached in the last question
    if ($isOpen) {
        $sRows .= doRender('/survey/questions/answer/listradio/columns/column_footer', array('last'=>true), true);
    }

    //END OF ITEMS

    // ==> answer
    $answer = doRender('/survey/questions/answer/listradio/answer', array(
        'sTimer'=>$sTimer,
        'sRows' => $sRows,
        'name'  => $ia[1],
        'basename' => $ia[1],
        'value' => $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]],
        'coreClass'=>$coreClass,
        ), true);

    $inputnames[] = $ia[1];
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_listwithcomment($ia)
{
    //// Init variables

    // General variables
    global $thissurvey;
    $kpclass                = testKeypad($thissurvey['nokeyboard']); // Virtual keyboard (probably obsolete today)
    $checkconditionFunction = "checkconditions";
    $iSurveyId              = Yii::app()->getConfig('surveyID'); // survey id
    $sSurveyLang            = $_SESSION['survey_'.$iSurveyId]['s_lang']; // survey language
    $maxoptionsize          = 35;
    $coreClass              = "ls-answers";
    $inputnames = array();

    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]); // Question attribute variables
    $oQuestion           = Question::model()->findByPk(array('qid'=>$ia[0], 'language'=>$sSurveyLang)); // Getting question

    // Getting answers
    $ansresult    = $oQuestion->getOrderedAnswers($aQuestionAttributes['random_order'], $aQuestionAttributes['alphasort']);
    $anscount     = count($ansresult);
    $hint_comment = gT('Please enter your comment here');

    if ($aQuestionAttributes['use_dropdown'] != 1) {
        $sRows = '';
        $li_classes = 'answer-item radio-item';
        foreach ($ansresult as $ansrow) {
            $check_ans = '';

            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow['code']) {
                $check_ans = CHECKED;
            }

            $itemData = array(
                'li_classes'=>$li_classes,
                'name'                   => $ia[1],
                'id'                     => 'answer'.$ia[1].$ansrow['code'],
                'value'                  => $ansrow['code'],
                'check_ans'              => $check_ans,
                'checkconditionFunction' => $checkconditionFunction.'(this.value, this.name, this.type);',
                'labeltext'              => $ansrow['answer'],
            );
            $sRows .= doRender('/survey/questions/answer/list_with_comment/list/rows/answer_row', $itemData, true);
        }

        // ==> rows
        $check_ans = '';
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
            if ((!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '') || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == ' ')) {
                $check_ans = CHECKED;
            } elseif (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] != '')) {
                $check_ans = '';
            }

            $itemData = array(
                'li_classes'=>$li_classes.' noanswer-item',
                'name'=>$ia[1],
                'id'=>'answer'.$ia[1],
                'value'=>'',
                'check_ans'=>$check_ans,
                'checkconditionFunction'=>$checkconditionFunction.'(this.value, this.name, this.type)',
                'labeltext'=>gT('No answer'),
            );

            $sRows .= doRender('/survey/questions/answer/list_with_comment/list/rows/answer_row', $itemData, true);
        }

        $fname2 = $ia[1].'comment';
        $tarows = ($anscount > 8) ? $anscount / 1.2 : 4;


        $answer = doRender('/survey/questions/answer/list_with_comment/list/answer', array(
            'sRows'             => $sRows,
            'id'                => 'answer'.$ia[1].'comment',
            'basename'          => $ia[1],
            'coreClass'         => $coreClass,
            'hint_comment'      => $hint_comment,
            'kpclass'           => $kpclass,
            'name'              => $ia[1].'comment',
            'tarows'            => floor($tarows),
            'has_comment_saved' => isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2],
            'comment_saved'     => htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]),
            'java_name'         => 'java'.$ia[1],
            'java_id'           => 'java'.$ia[1],
            'java_value'        => $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]
            ), true);


        $inputnames[] = $ia[1];
        $inputnames[] = $ia[1].'comment';
    } else {
        //Dropdown list
        $sOptions = '';
        foreach ($ansresult as $ansrow) {
            $check_ans = '';
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow['code']) {
                $check_ans = SELECTED;
            }

            $itemData = array(
                'value' => $ansrow['code'],
                'check_ans' => $check_ans,
                'option_text' => $ansrow['answer'],
            );
            $sOptions .= doRender('/survey/questions/answer/list_with_comment/dropdown/rows/option', $itemData, true);

            if (strlen($ansrow['answer']) > $maxoptionsize) {
                $maxoptionsize = strlen($ansrow['answer']);
            }
        }
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1 && !is_null($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]])) {
            $check_ans = "";
            if (trim($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) == '') {
                $check_ans = SELECTED;
            }
            $itemData = array(
                'classes' => ' noanswer-item ',
                'value' => '',
                'check_ans' => $check_ans,
                'option_text' => gT('No answer'),
            );
            $sOptions .= doRender('/survey/questions/answer/list_with_comment/dropdown/rows/option', $itemData, true);
        }
        $fname2 = $ia[1].'comment';

        if ($anscount > 8) {
            $tarows = $anscount / 1.2;
        } else {
            $tarows = 4;
        }

        if ($tarows > 15) {
            $tarows = 15;
        }
        $maxoptionsize = $maxoptionsize * 0.72;

        if ($maxoptionsize < 33) {
            $maxoptionsize = 33;
        }
        if ($maxoptionsize > 70) {
            $maxoptionsize = 70;
        }


        $answer = doRender('/survey/questions/answer/list_with_comment/dropdown/answer', array(
            'sOptions'               => $sOptions,
            'name'                   => $ia[1],
            'coreClass'              => $coreClass,
            'id'                     => 'answer'.$ia[1],
            'basename'               => $ia[1],
            'show_noanswer'          => is_null($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]),
            'label_text'             => $hint_comment,
            'kpclass'                => $kpclass,
            'tarows'                 => $tarows,
            'maxoptionsize'          => $maxoptionsize,
            'comment_saved'          => htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]), /* htmlspecialchars(null)=="" right ? */
            'value'                  => $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]],
            ), true);

        $inputnames[] = $ia[1];
        $inputnames[] = $ia[1].'comment';
    }
    return array($answer, $inputnames);
}

function do_ranking($ia)
{
    $aQuestionAttributes    = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $coreClass              = "ls-answers answers-lists select-sortable-lists";
    if ($aQuestionAttributes['random_order'] == 1) {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
    } else {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
    }

    $ansresult = Yii::app()->db->createCommand($ansquery)->query()->readAll();
    $anscount  = count($ansresult);
    $max_subquestions = intval($aQuestionAttributes['max_subquestions']) > 0 ? intval($aQuestionAttributes['max_subquestions']) : $anscount;
    $max_subquestions = min($max_subquestions,$anscount); // Can not be upper than current answers #14899
    if (trim($aQuestionAttributes["max_answers"]) != '') {
        $max_answers = "min(".trim($aQuestionAttributes["max_answers"]).",".$max_subquestions.")";
    } else {
        $max_answers = $max_subquestions;
    }
    $max_answers = LimeExpressionManager::ProcessString("{{$max_answers}}", $ia[0]);
    // Get the max number of line needed
    if (ctype_digit($max_answers) && intval($max_answers) < $max_subquestions) {
        $iMaxLine = $max_answers;
    } else {
        $iMaxLine = $max_subquestions;
    }
    if (trim($aQuestionAttributes["min_answers"]) != '') {
        $min_answers = trim($aQuestionAttributes["min_answers"]);
    } else {
        $min_answers = 0;
    }
    $min_answers = LimeExpressionManager::ProcessString("{{$min_answers}}", $ia[0]);

    $answer = '';
    // First start by a ranking without javascript : just a list of select box
    // construction select box
    $answers = array();

    foreach ($ansresult as $ansrow) {
        $answers[] = $ansrow;
    }

    $inputnames = array();
    $sSelects   = '';
    $myfname    = '';

    $thisvalue = "";
    for ($i = 1; $i <= $iMaxLine; $i++) {
        $myfname = $ia[1].$i;
        $labeltext = ($i == 1) ?gT('First choice') : sprintf(gT('Choice of rank %s'), $i);
        $itemDatas = array();

        if (!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) {
            $itemDatas[] = array(
                'value'      => '',
                'selected'   => 'SELECTED',
                'classes'    => '',
                'id'         => '',
                'optiontext' => gT('Please choose...'),
            );
        }

        foreach ($answers as $ansrow) {
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $ansrow['code']) {
                $selected = SELECTED;
                $thisvalue = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            } else {
                $selected = '';
            }

            $itemDatas[] = array(
                'value' => $ansrow['code'],
                'selected'=>$selected,
                'classes'=>'',
                'optiontext'=>flattenText($ansrow['answer'])
            );
        }

        $sSelects .= doRender(
            '/survey/questions/answer/ranking/rows/answer_row',
            array(
                'myfname' => $myfname,
                'labeltext' => $labeltext,
                'options' => $itemDatas,
                'thisvalue' => $thisvalue
            ),
            true
        );

        $inputnames[] = $myfname;
    }

    $rankingTranslation = 'LSvar.lang.rankhelp="'.gT("Double-click or drag-and-drop items in the left list to move them to the right - your highest ranking item should be on the top right, moving through to your lowest ranking item.", 'js').'";';
    App()->getClientScript()->registerScript("rankingTranslation", $rankingTranslation, CClientScript::POS_BEGIN);

    if (trim($aQuestionAttributes['choice_title'][App()->language]) != '') {
        $choice_title = htmlspecialchars(trim($aQuestionAttributes['choice_title'][App()->language]), ENT_QUOTES);
    } else {
        $choice_title = gT("Your Choices", 'html');
    }
    if (trim($aQuestionAttributes['rank_title'][App()->language]) != '') {
        $rank_title = htmlspecialchars(trim($aQuestionAttributes['rank_title'][App()->language]), ENT_QUOTES);
    } else {
        $rank_title = gT("Your Ranking", 'html');
    }

    $answer .= doRender('/survey/questions/answer/ranking/answer', array(
        'coreClass'         => $coreClass,
        'sSelects'          => $sSelects,
        'thisvalue'         => $thisvalue,
        'answers'           => $answers,
        'myfname'           => $myfname,
        'labeltext'         => (isset($labeltext)) ? $labeltext : '',
        'qId'               => $ia[0],
        'rankingName'       => $ia[1],
        'basename'          => $ia[1],
        'max_answers'       => $max_answers,
        'min_answers'       => $min_answers,
        'choice_title'      => $choice_title,
        'rank_title'        => $rank_title,
        'showpopups'        => $aQuestionAttributes["showpopups"],
        'samechoiceheight'  => $aQuestionAttributes["samechoiceheight"],
        'samelistheight'    => $aQuestionAttributes["samelistheight"],
        ), true);
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_multiplechoice($ia)
{
    //// Init variables

    // General variables
    global $thissurvey;
    $kpclass                = testKeypad($thissurvey['nokeyboard']); // Virtual keyboard (probably obsolete today)
    $inputnames             = array(); // It is used!
    $checkconditionFunction = "checkconditions"; // name of the function to check condition TODO : check is used more than once
    $iSurveyId              = Yii::app()->getConfig('surveyID'); // survey id
    $sSurveyLang            = $_SESSION['survey_'.$iSurveyId]['s_lang']; // survey language
    $coreClass = "ls-answers checkbox-list answers-list";
    // Question attribute variables
    $aQuestionAttributes    = (array) QuestionAttribute::model()->getQuestionAttributes($ia[0]); // Question attributes
    $othertext              = (trim($aQuestionAttributes['other_replace_text'][$sSurveyLang]) != '') ? $aQuestionAttributes['other_replace_text'][$sSurveyLang] : gT('Other:'); // text for 'other'
    $iNbCols                = (trim($aQuestionAttributes['display_columns']) != '') ? $aQuestionAttributes['display_columns'] : 1; // number of columns
    $aSeparator             = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeparator             = $aSeparator['separator'];
    
    $oth_checkconditionFunction = ($aQuestionAttributes['other_numbers_only'] == 1) ? "fixnum_checkconditions" : "checkconditions";

    //// Retrieving datas

    // Getting question
    $oQuestion = Question::model()->findByPk(array('qid'=>$ia[0], 'language'=>$sSurveyLang));
    $other     = $oQuestion->other;

    // Getting answers
    $ansresult = $oQuestion->getOrderedSubQuestions($aQuestionAttributes['random_order'], $aQuestionAttributes['exclude_all_others']);
    $anscount  = count($ansresult);
    $anscount  = ($other == 'Y') ? $anscount + 1 : $anscount; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!

    // First we calculate the width of each column
    // Max number of column is 12 http://getbootstrap.com/css/#grid
    $iColumnWidth = round(12 / $iNbCols);
    $iColumnWidth = ($iColumnWidth >= 1) ? $iColumnWidth : 1;
    $iColumnWidth = ($iColumnWidth <= 12) ? $iColumnWidth : 12;
    $iMaxRowsByColumn = ceil($anscount / $iNbCols);
    
    if ($iNbCols > 1) {
        $coreClass .= " multiple-list nbcol-{$iNbCols}";
    }

    $aRows = [];
    foreach ($ansresult as $ansrow) {
        $myfname = $ia[1].$ansrow['title'];

        $relevanceClass = currentRelevecanceClass($iSurveyId, $ia[1], $myfname, $aQuestionAttributes);
        $checkedState = '';
        /* If the question has already been ticked, check the checkbox */
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'Y') {
                $checkedState = 'CHECKED';
            }
        }

        $sCheckconditionFunction = $checkconditionFunction.'(this.value, this.name, this.type)';
        $sValue                  = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';
        $inputnames[]            = $myfname;


        ////
        // Insert row
        // Display the answer row
        $aRows[] = array(
            'name'                    => $ia[1], // field name
            'title'                   => $ansrow['title'],
            'question'                => $ansrow['question'],
            'ansrow'                  => $ansrow,
            'checkedState'            => $checkedState,
            'sCheckconditionFunction' => $sCheckconditionFunction,
            'myfname'                 => $myfname,
            'sValue'                  => $sValue,
            'relevanceClass'          => $relevanceClass,
            );
    }

    //==>  rows
    if ($other == 'Y') {
        $myfname = $ia[1].'other';
        $relevanceClass = currentRelevecanceClass($iSurveyId, $ia[1], $myfname, $aQuestionAttributes);
        $checkedState = '';
        // othercbox can be not display, because only input text goes to database
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && trim($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) != '') {
            $checkedState = 'CHECKED';
        }

        $sValue = '';
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            if ($aQuestionAttributes['other_numbers_only'] == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $sValue .= htmlspecialchars($dispVal, ENT_QUOTES);
        }

        // TODO : check if $sValueHidden === $sValue
        $sValueHidden = '';
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            if ($aQuestionAttributes['other_numbers_only'] == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $sValueHidden = htmlspecialchars($dispVal, ENT_QUOTES);
            ;
        }

        $inputnames[] = $myfname;
        ++$anscount;

        ////
        // Insert row
        // Display the answer row
        $aRows[] = array(
            'myfname'                    => $myfname,
            'othertext'                  => $othertext,
            'checkedState'               => $checkedState,
            'kpclass'                    => $kpclass,
            'sValue'                     => $sValue,
            'oth_checkconditionFunction' => $oth_checkconditionFunction,
            'checkconditionFunction'     => $checkconditionFunction,
            'sValueHidden'               => $sValueHidden,
            'relevanceClass'             => $relevanceClass,
            'other'                      => true
            );
    }

  

    // ==> answer
    $answer = doRender('/survey/questions/answer/multiplechoice/answer', array(
        'aRows'            => $aRows,
        'name'             => $ia[1],
        'basename'         => $ia[1],
        'anscount'         => $anscount,
        'iColumnWidth'     => $iColumnWidth,
        'iMaxRowsByColumn' => $iMaxRowsByColumn,
        'iNbCols'          => $iNbCols,
        'coreClass'        => $coreClass,
        ), true);

    return array($answer, $inputnames);
}

function do_multiplechoice_withcomments($ia)
{
    global $thissurvey;
    $kpclass    = testKeypad($thissurvey['nokeyboard']); // Virtual keyboard (probably obsolete today)
    $inputnames = array();
    $coreClass = "ls-answers answers-list checkbox-text-list";
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

    if ($aQuestionAttributes['other_numbers_only'] == 1) {
        $sSeparator                 = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator                 = $sSeparator['separator'];
        $otherNumber = 1;
    } else {
        $otherNumber = 0;
        $sSeparator = '.';
    }

    if (trim($aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $othertext = $aQuestionAttributes['other_replace_text'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
    } else {
        $othertext = gT('Other:');
    }

    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and parent_qid=0";
    $other  = Yii::app()->db->createCommand($qquery)->queryScalar(); //Checked
    if ($aQuestionAttributes['random_order'] == 1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    } else {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }

    $ansresult = Yii::app()->db->createCommand($ansquery)->query(); //Checked
    $anscount  = count($ansresult) * 2;

    $fn = 1;
    if (!isset($other)) {
        $other = 'N';
    }
    if ($other == 'Y') {
        $label_width = 25;
    } else {
        $label_width = 0;
    }

    /* Find the col-sm width : if none is set : default, if one is set, set another one to be 12, if two is set : no change*/
    $attributeInputContainerWidth = intval(trim($aQuestionAttributes['text_input_columns']));
    if ($attributeInputContainerWidth < 1 || $attributeInputContainerWidth > 12) {
        $attributeInputContainerWidth = null;
    }
    $attributeLabelWidth = intval(trim($aQuestionAttributes['choice_input_columns']));
    if ($attributeLabelWidth < 1 || $attributeLabelWidth > 12) {
        /* old system or imported */
        $attributeLabelWidth = null;
    }
    if ($attributeInputContainerWidth === null && $attributeLabelWidth === null) {
        $sInputContainerWidth = 8;
        $sLabelWidth = 4;
    } else {
        if ($attributeInputContainerWidth !== null) {
            $sInputContainerWidth = $attributeInputContainerWidth;
        } elseif ($attributeLabelWidth == 12) {
            $sInputContainerWidth = 12;
        } else {
            $sInputContainerWidth = 12 - $attributeLabelWidth;
        }
        if ($attributeLabelWidth !== null) {
            $sLabelWidth = $attributeLabelWidth;
        } elseif ($attributeInputContainerWidth == 12) {
            $sLabelWidth = 12;
        } else {
            $sLabelWidth = 12 - $attributeInputContainerWidth;
        }
    }

    // Size of elements depends on longest text item
    $toIterate = $ansresult->readAll();
    $longest_question = 0;
    foreach ($toIterate as $ansrow) {
        $current_length = round((strlen($ansrow['question']) / 10) + 1);
        $longest_question = ($longest_question > $current_length) ? $longest_question : $current_length;
    }

    $sRows = "";
    $inputCOmmentValue = '';
    $checked = '';
    foreach ($toIterate as $ansrow) {
        $myfname = $ia[1].$ansrow['title'];

        if ($label_width < strlen(trim(strip_tags($ansrow['question'])))) {
            $label_width = strlen(trim(strip_tags($ansrow['question'])));
        }

        $myfname2 = $myfname."comment";

        /* If the question has already been ticked, check the checkbox */
        $checked = '';
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'Y') {
                $checked = CHECKED;
            }
        }

        $javavalue = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';

        $fn++;
        $fn++;
        $inputnames[] = $myfname;
        $inputnames[] = $myfname2;

        $inputCOmmentValue = htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2], ENT_QUOTES);
        $sRows .= doRender('/survey/questions/answer/multiplechoice_with_comments/rows/answer_row', array(
            'kpclass'                       => $kpclass,
            'title'                         => '',
            'liclasses'                     => 'responsive-content question-item answer-item checkbox-text-item',
            'name'                          => $myfname,
            'id'                            => 'answer'.$myfname,
            'value'                         => 'Y', // TODO : check if it should be the same than javavalue
            'classes'                       => '',
            'otherNumber'                   => $otherNumber,
            'labeltext'                     => $ansrow['question'],
            'javainput'                     => true,
            'javaname'                      => 'java'.$myfname,
            'javavalue'                     => $javavalue,
            'checked'                       => $checked,
            'inputCommentId'                => 'answer'.$myfname2,
            'commentLabelText'              => gT('Make a comment on your choice here:'),
            'inputCommentName'              => $myfname2,
            'inputCOmmentValue'             => (isset($inputCOmmentValue)) ? $inputCOmmentValue : '',
            'sInputContainerWidth'          => $sInputContainerWidth,
            'sLabelWidth'                   => $sLabelWidth,
            ), true);
    }
    if ($other == 'Y') {
        $myfname = $ia[1].'other';
        $myfname2 = $myfname.'comment';
        $anscount = $anscount + 2;
        // SPAN LABEL OPTION //////////////////////////
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            if ($aQuestionAttributes['other_numbers_only'] == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $value = htmlspecialchars($dispVal, ENT_QUOTES);
        }

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2])) {
            $inputCOmmentValue = htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2], ENT_QUOTES);
        }

        // TODO: $value is not defined for some execution paths.
        if (!isset($value)) {
            $value = '';
        }

        $sRows .= doRender('/survey/questions/answer/multiplechoice_with_comments/rows/answer_row_other', array(
            'liclasses'                     => 'other question-item answer-item checkbox-text-item other-item',
            'liid'                          => 'javatbd'.$myfname,
            'kpclass'                       => $kpclass,
            'title'                         => gT('Other'),
            'name'                          => $myfname,
            'id'                            => 'answer'.$myfname,
            'value'                         => $value, // TODO : check if it should be the same than javavalue
            'classes'                       => '',
            'otherNumber'                   => $otherNumber,
            'labeltext'                     => $othertext,
            'inputCommentId'                => 'answer'.$myfname2,
            'commentLabelText'              => gT('Make a comment on your choice here:'),
            'inputCommentName'              => $myfname2,
            'inputCOmmentValue'             => $inputCOmmentValue,
            'checked'                       => $checked,
            'javainput'                     => false,
            'javaname'                      => '',
            'javavalue'                     => '',
            'sInputContainerWidth'          => $sInputContainerWidth,
            'sLabelWidth'                   => $sLabelWidth
            ), true);
        $inputnames[] = $myfname;
        $inputnames[] = $myfname2;
    }

    $answer = doRender('/survey/questions/answer/multiplechoice_with_comments/answer', array(
        'sRows' => $sRows,
        'coreClass'=>$coreClass,
        'name'=>'MULTI'.$ia[1], /* ? name is not $ia[1] */
        'basename'=> $ia[1],
        'value'=> $anscount
        ), true);


    if ($aQuestionAttributes['commented_checkbox'] != "allways" && $aQuestionAttributes['commented_checkbox_auto']) {
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."multiplechoice_withcomments.js", LSYii_ClientScript::POS_BEGIN);
        Yii::app()->getClientScript()->registerScript(
            'doMultipleChoiceWithComments'.$ia[0],
        "doMultipleChoiceWithComments({$ia[0]},'{$aQuestionAttributes["commented_checkbox"]}');",
        LSYii_ClientScript::POS_POSTSCRIPT
        );
    }

    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_file_upload($ia)
{
    global $thissurvey;
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $coreClass = "ls-answers upload-item";
    // Fetch question attributes
    $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldname'] = $ia[1];
    $scriptloc = Yii::app()->getController()->createUrl('uploader/index');
    $bPreview = Yii::app()->request->getParam('action') == "previewgroup" || Yii::app()->request->getParam('action') == "previewquestion" || $thissurvey['active'] != "Y";
    if ($bPreview) {
        $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['preview'] = 1;
        $questgrppreview = 1; // Preview is launched from Question or group level
    } elseif ($thissurvey['active'] != "Y") {
        $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['preview'] = 1;
        $questgrppreview = 0;
    } else {
        $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['preview'] = 0;
        $questgrppreview = 0;
    }
    $answer = "<script type='text/javascript'>
        function upload_$ia[1]() {
            var uploadurl = '{$scriptloc}?sid=".Yii::app()->getConfig('surveyID')."&fieldname={$ia[1]}&qid={$ia[0]}';
            uploadurl += '&preview={$questgrppreview}&show_title={$aQuestionAttributes['show_title']}';
            uploadurl += '&show_comment={$aQuestionAttributes['show_comment']}';
            uploadurl += '&minfiles=' + LEMval('{$aQuestionAttributes['min_num_of_files']}');
            uploadurl += '&maxfiles=' + LEMval('{$aQuestionAttributes['max_num_of_files']}');
            $('#upload_$ia[1]').attr('href',uploadurl);
        }
        var uploadLang = {
             title: '".gT('Upload your files', 'js')."',
             returnTxt: '" . gT('Return to survey', 'js')."',
             headTitle: '" . gT('Title', 'js')."',
             headComment: '" . gT('Comment', 'js')."',
             headFileName: '" . gT('File name', 'js')."',
             deleteFile : '".gT('Delete')."',
             editFile : '".gT('Edit')."'
            };
        var imageurl =  '".Yii::app()->getConfig('imageurl')."';
        var uploadurl =  '".$scriptloc."';
    </script>\n";
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."modaldialog.js", LSYii_ClientScript::POS_BEGIN);
    Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl')."uploader-files.css");
    // Modal dialog
    //$answer .= $uploadbutton;
    $filecountvalue = '0';
    if (array_key_exists($ia[1]."_filecount", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')])) {
        $tempval = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]."_filecount"];
        if (is_numeric($tempval)) {
            $filecountvalue = $tempval;
        }
    }
    $fileuploadData = array(
        'fileid' => $ia[1],
        'value' => $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]],
        'filecountvalue'=>$filecountvalue,
        'coreClass'=>$coreClass,
        'basename' => $ia[1],
    );
    $answer .= doRender('/survey/questions/answer/file_upload/answer', $fileuploadData, true);
    $answer .= '<script type="text/javascript">
    var surveyid = '.Yii::app()->getConfig('surveyID').';
    $(document).on("ready pjax:scriptcomplete", function(){
    var fieldname = "'.$ia[1].'";
    var filecount = $("#"+fieldname+"_filecount").val();
    var json = $("#"+fieldname).val();
    var show_title = "'.$aQuestionAttributes["show_title"].'";
    var show_comment = "'.$aQuestionAttributes["show_comment"].'";
    displayUploadedFiles(json, filecount, fieldname, show_title, show_comment);
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
    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['show_title'])) {
        $answer .= '\"title\":\""+$("#'.$ia[1].'_title_"+i).val()+"\",';
    } else {
        $answer .= '\"title\":\"\",';
    }
    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['show_comment'])) {
        $answer .= '\"comment\":\""+$("#'.$ia[1].'_comment_"+i).val()+"\",';
    } else {
        $answer .= '\"comment\":\"\",';
    }
    $answer .= '\"size\":\"\",\"name\":\"\",\"ext\":\"\"}";
    }
    jsonstring += "]";
    $("#'.$ia[1].'").val(jsonstring);
    $("#'.$ia[1].'_filecount").val(filecount);
    });
    </script>';
    $uploadurl  = $scriptloc."?sid=".Yii::app()->getConfig('surveyID')."&fieldname=".$ia[1]."&qid=".$ia[0];
    $uploadurl .= "&preview=".$questgrppreview."&show_title=".$aQuestionAttributes['show_title'];
    $uploadurl .= "&show_comment=".$aQuestionAttributes['show_comment'];
    $uploadurl .= "&minfiles=".$aQuestionAttributes['min_num_of_files']; // TODO: Regression here? Should use LEMval(minfiles) like above
    $uploadurl .= "&maxfiles=".$aQuestionAttributes['max_num_of_files']; // Same here.
    $answer .= '
    <!-- Trigger the modal with a button -->
        <!-- Modal -->
        <div id="file-upload-modal-' . $ia[1].'" class="modal fade file-upload-modal" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header file-upload-modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <div class="h4 modal-title">' . ngT("Upload file|Upload files", $aQuestionAttributes['max_num_of_files']).'</div>
                    </div>
                    <div class="modal-body file-upload-modal-body">
                        <iframe id="uploader' . $ia[1].'" name="uploader'.$ia[1].'" class="uploader-frame" src="'.$uploadurl.'" title="'.gT("Upload").'"></iframe>
                    </div>
                    <div class="modal-footer file-upload-modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">' . gT("Save changes").'</button>
                    </div>
                </div>
            </div>
        </div>
    ';
    $inputnames = array();
    $inputnames[] = $ia[1];
    $inputnames[] = $ia[1]."_filecount";
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_multipleshorttext($ia)
{
    global $thissurvey;
    $extraclass          = "";
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $coreClass = "ls-answers subquestion-list questions-list text-list";
    if ($aQuestionAttributes['numbers_only'] == 1) {
        $sSeparator             = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator             = $sSeparator['separator'];
        $extraclass            .= " numberonly";
        $coreClass             .= " number-list";
    } else {
        $sSeparator = '';
    }

    if (intval(trim($aQuestionAttributes['maximum_chars'])) > 0) {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maxlength = intval(trim($aQuestionAttributes['maximum_chars']));
        $extraclass .= " ls-input-maxchars";
    } else {
        $maxlength = "";
    }
    if (ctype_digit(trim($aQuestionAttributes['input_size']))) {
        $inputsize = trim($aQuestionAttributes['input_size']);
        $extraclass .= " ls-input-sized";
    } else {
        $inputsize = null;
    }

    /* Find the col-sm width : if non is set : default, if one is set, set another one to be 12, if two is set : no change*/
    /* Find the col-sm width : if none is set : default, if one is set, set another one to be 12, if two is set : no change*/
    list($sLabelWidth, $sInputContainerWidth, $defaultWidth) = getLabelInputWidth($aQuestionAttributes['label_input_columns'], $aQuestionAttributes['text_input_columns']);


    if (trim($aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $prefix      = $aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withprefix";
    } else {
        $prefix = '';
    }

    if (trim($aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $suffix      = $aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withsuffix";
    } else {
        $suffix = '';
    }
    $kpclass = testKeypad($thissurvey['nokeyboard']); // Virtual keyboard (probably obsolete today)

    if ($aQuestionAttributes['random_order'] == 1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    } else {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }

    $ansresult     = dbExecuteAssoc($ansquery); //Checked
    $aSubquestions = $ansresult->readAll();
    $anscount      = count($aSubquestions) * 2;
    $fn            = 1;
    $sRows         = '';
    $inputnames = array();

    if ($anscount != 0) {
        $alert = false;
        foreach ($aSubquestions as $ansrow) {
            $myfname = $ia[1].$ansrow['title'];
            $ansrow['question'] = ($ansrow['question'] == "") ? "&nbsp;" : $ansrow['question'];

            // color code missing mandatory questions red
            if (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['prevstep'])) {
                if ($ia[6] == 'Y' && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] === '') {
                    $alert = true;
                }
            }

            $sDisplayStyle = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);
            $dispVal       = '';

            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
                $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                if ($aQuestionAttributes['numbers_only'] == 1) {
                    $dispVal = str_replace('.', $sSeparator, $dispVal);
                }
                $dispVal = htmlspecialchars($dispVal, ENT_QUOTES, 'UTF-8');
            }
            $numbersonly = ($aQuestionAttributes['numbers_only'] == 1);
            if (trim($aQuestionAttributes['display_rows']) != '') {
                $sRows .= doRender('/survey/questions/answer/multipleshorttext/rows/answer_row_textarea', array(
                    'alert'                  => $alert,
                    'labelname'              => 'answer'.$myfname,
                    'maxlength'              => $maxlength,
                    'rows'                   => $aQuestionAttributes['display_rows'],
                    'numbersonly'            => $numbersonly,
                    'sInputContainerWidth'   => $sInputContainerWidth,
                    'sLabelWidth'            => $sLabelWidth,
                    'inputsize'              => $inputsize,
                    'extraclass'             => $extraclass,
                    'sDisplayStyle'          => $sDisplayStyle,
                    'prefix'                 => $prefix,
                    'myfname'                => $myfname,
                    'question'               => $ansrow['question'],
                    'kpclass'                => $kpclass,
                    'dispVal'                => $dispVal,
                    'suffix'                 => $suffix,
                    ), true);
            } else {
                $sRows .= doRender('/survey/questions/answer/multipleshorttext/rows/answer_row_inputtext', array(
                    'alert'                  => $alert,
                    'labelname'              => 'answer'.$myfname,
                    'maxlength'              => $maxlength,
                    'numbersonly'            => $numbersonly,
                    'sInputContainerWidth'   => $sInputContainerWidth,
                    'sLabelWidth'            => $sLabelWidth,
                    'inputsize'              => $inputsize,
                    'extraclass'             => $extraclass,
                    'sDisplayStyle'          => $sDisplayStyle,
                    'prefix'                 => $prefix,
                    'myfname'                => $myfname,
                    'question'               => $ansrow['question'],
                    'kpclass'                => $kpclass,
                    'dispVal'                => $dispVal,
                    'suffix'                 => $suffix,
                    ), true);
            }
            $fn++;
            $inputnames[] = $myfname;
        }

        $answer = doRender('/survey/questions/answer/multipleshorttext/answer', array(
            'sRows' => $sRows,
            'coreClass'=>$coreClass,
            'basename'=>$ia[1],
            ), true);
    } else {
        $inputnames   = array();
        $answer       = doRender('/survey/questions/answer/multipleshorttext/empty', array(), true);
    }

    return array($answer, $inputnames);
}

// -----------------------------------------------------------------
// @todo: Can remove DB query by passing in answer list from EM
function do_multiplenumeric($ia)
{
    global $thissurvey;
    $extraclass             = "";
    $aQuestionAttributes    = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $sSeparator             = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeparator             = $sSeparator['separator'];
    $extraclass            .= " numberonly"; //Must turn on the "numbers only javascript"
    $coreClass              = "ls-answers subquestion-list questions-list ";
    if (intval(trim($aQuestionAttributes['maximum_chars'])) > 0) {
        $maxlength = intval(trim($aQuestionAttributes['maximum_chars'])); /* must be limited to 32 : -(10 number)dot(20 numbers) ! DECIMAL sql */
        $extraclass .= " ls-input-maxchars";
    } else {
        $maxlength = 20;
    }
    if (ctype_digit(trim($aQuestionAttributes['input_size']))) {
        $inputsize = trim($aQuestionAttributes['input_size']);
        $extraclass .= " ls-input-sized";
    } else {
        $inputsize = null;
    }

    if ($aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']] != '') {
        $prefix      = $aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withprefix";
    } else {
        $prefix = ''; /* slider js need it */
    }

    if ($aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']] != '') {
        $suffix      = $aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withsuffix";
    } else {
        $suffix = ''; /* slider js need it */
    }

    $kpclass = testKeypad($thissurvey['nokeyboard']); // Virtual keyboard (probably obsolete today)
    
    /* Find the col-sm width : if none is set : default, if one is set, set another one to be 12, if two is set : no change*/
    list($sLabelWidth, $sInputContainerWidth, $defaultWidth) = getLabelInputWidth($aQuestionAttributes['label_input_columns'], $aQuestionAttributes['text_input_width']);

    $prefixclass = "numeric";
    $sliders = 0;
    $slider_position = '';
    $slider_default_set = false;
    
    if ($aQuestionAttributes['slider_layout'] == 1) {
        $coreClass           .= " slider-list";
        $slider_layout        = true;
        $extraclass          .= " withslider";
        $slider_step          = trim(LimeExpressionManager::ProcessString("{{$aQuestionAttributes['slider_accuracy']}}", $ia[0], array(), 1, 1, false, false, true));
        $slider_step          = (is_numeric($slider_step)) ? $slider_step : 1;
        $slider_min           = trim(LimeExpressionManager::ProcessString("{{$aQuestionAttributes['slider_min']}}", $ia[0], array(), 1, 1, false, false, true));
        $slider_mintext       = $slider_min = (is_numeric($slider_min)) ? $slider_min : 0;
        $slider_max           = trim(LimeExpressionManager::ProcessString("{{$aQuestionAttributes['slider_max']}}", $ia[0], array(), 1, 1, false, false, true));
        $slider_maxtext       = $slider_max = (is_numeric($slider_max)) ? $slider_max : 100;
        $slider_default       = trim(LimeExpressionManager::ProcessString("{{$aQuestionAttributes['slider_default']}}", $ia[0], array(), 1, 1, false, false, true));
        $slider_default       = (is_numeric($slider_default)) ? $slider_default : "";
        $slider_default_set   = (bool) ($aQuestionAttributes['slider_default_set'] && $slider_default !== '');
        $slider_orientation   = (trim($aQuestionAttributes['slider_orientation']) == 0) ? 'horizontal' : 'vertical';
        $slider_custom_handle = (trim($aQuestionAttributes['slider_custom_handle']));

        switch (trim($aQuestionAttributes['slider_handle'])) {
            case 0:
                $slider_handle = 'round';
                break;

            case 1:
                $slider_handle = 'square';
                break;

            case 2:
                $slider_handle = 'triangle';
                break;

            case 3:
                $slider_handle = 'custom';
                break;
        }

        /* Put the slider init to initial state (when no click is set or when 'reset') */
        if ($slider_default !== '') {
            /* can be 0 */
            $slider_position = $slider_default;
        } elseif ($aQuestionAttributes['slider_middlestart'] == 1) {
            $slider_position = intval(($slider_max + $slider_min) / 2);
        }
        $slider_separator = (trim($aQuestionAttributes['slider_separator']) != '') ? $aQuestionAttributes['slider_separator'] : "";
        $slider_reset = ($aQuestionAttributes['slider_reset']) ? 1 : 0;

        /* Slider reversed value */
        if ($aQuestionAttributes['slider_reversed'] == 1) {
            $slider_reversed = 'true';
        } else {
            $slider_reversed = 'false';
        }
    } else {
        $coreClass .= " text-list number-list";
        $slider_layout  = false;
        $slider_step    = '';
        $slider_min     = '';
        $slider_mintext = '';
        $slider_max     = '';
        $slider_maxtext = '';
        $slider_default = null;
        $slider_orientation = '';
        $slider_handle = '';
        $slider_custom_handle = '';
        $slider_separator = '';
        $slider_reset = 0;
        $slider_reversed = 'false';
    }

    if ($aQuestionAttributes['random_order'] == 1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    } else {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }

    $ansresult     = dbExecuteAssoc($ansquery); //Checked
    $aSubquestions = $ansresult->readAll();
    $anscount      = count($aSubquestions) * 2;
    $fn            = 1;
    $sRows         = "";

    $inputnames = array();

    if ($anscount == 0) {
        $answer = doRender('/survey/questions/answer/multiplenumeric/empty', array(), true);
    } else {
        foreach ($aSubquestions as $ansrow) {
            $sliderWidth = 12; /* reset sliderWidth for each row : left and right can be different for each #14127 */
            $labelText = $ansrow['question'];
            $myfname   = $ia[1].$ansrow['title'];

            if ($ansrow['question'] == "") {
                $ansrow['question'] = "&nbsp;";
            }

            if ($slider_layout) {
                if ($slider_separator != '') {
                    $aAnswer     = explode($slider_separator, $ansrow['question']);
                    $theanswer   = (isset($aAnswer[0])) ? $aAnswer[0] : "";
                    $labelText   = $theanswer;
                    $sliderleft  = (isset($aAnswer[1])) ? $aAnswer[1] : null;
                    $sliderright = (isset($aAnswer[2])) ? $aAnswer[2] : null;

                    /* sliderleft and sliderright is in input, but is part of answers then take label width */
                    if (!empty($sliderleft)) {
                        $sliderWidth = 10;
                    }
                    if (!empty($sliderright)) {
                        $sliderWidth = $sliderWidth==10 ? 8 : 10 ;
                    }
                    $sliders   = true; // What is the usage ?
                } else {
                    $theanswer = $ansrow['question'];
                    $sliders   = false;
                }
            } else {
                $theanswer = $ansrow['question'];
                $sliders   = false;
            }

            $aAnswer     = (isset($aAnswer)) ? $aAnswer : '';
            $sliderleft  = (isset($sliderleft)) ? $sliderleft : null;
            $sliderright = (isset($sliderright)) ? $sliderright : null;

            // color code missing mandatory questions red
            $alert = '';

            if (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['prevstep'])) {
                if ($ia[6] == 'Y' && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] === '') {
                    $alert = true;
                }
            }

            //list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, '', $myfname, "div","form-group question-item answer-item text-item numeric-item".$extraclass);
            $sDisplayStyle = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);

            // The value of the slider depends on many possible different parameters, by order of priority :
            // 1. The value stored in the session
            // 2. Else the default Answer   (set by EM and stored in session, so same case than 1)
            // 3. Else the slider_default value : if slider_default_set set the value here
            // 4. Else the middle start or slider_default or nothing : leave the value to "" for the input, show slider pos at this position
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
                $sValue                = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            } elseif ($slider_layout && $slider_default !== "" && $slider_default_set) {
                $sValue                = $slider_default;
            } else {
                $sValue                = null;
            }

            // Fix the display value : Value is stored as decimal in SQL. Issue when reloading survey
            if($sValue && $sValue[0] == ".") {
                // issue #15684 mssql SAVE 0.01 AS .0100000000, set it at 0.0100000000
                $sValue = "0" . $sValue;
            }
            if (strpos($sValue, ".")) {
                $sValue = rtrim(rtrim($sValue, "0"), ".");
            }
            // End of DECIMAL fix : get the nulber value
            $sUnformatedValue = $sValue ? $sValue : '';
            if (strpos($sValue, ".")) {
                $sValue = str_replace('.', $sSeparator, $sValue);
            }

            if (trim($aQuestionAttributes['num_value_int_only']) == 1) {
                $extraclass .= " integeronly";
                $answertypeclass = " integeronly";
                $integeronly = 1;
            } else {
                $answertypeclass = "";
                $integeronly = 0;
            }

            if (!$slider_layout) {
                $sRows .= doRender('/survey/questions/answer/multiplenumeric/rows/input/answer_row', array(
                    'qid'                    => $ia[0],
                    'extraclass'             => $extraclass,
                    'answertypeclass'        => $answertypeclass,
                    'sDisplayStyle'          => $sDisplayStyle,
                    'kpclass'                => $kpclass,
                    'alert'                  => $alert,
                    'theanswer'              => $theanswer,
                    'labelname'              => 'answer'.$myfname,
                    'prefixclass'            => $prefixclass,
                    'prefix'                 => $prefix,
                    'suffix'                 => $suffix,
                    'sInputContainerWidth'   => $sInputContainerWidth,
                    'sLabelWidth'            => $sLabelWidth,
                    'inputsize'              => $inputsize,
                    'myfname'                => $myfname,
                    'dispVal'                => $sValue,
                    'maxlength'              => $maxlength,
                    'labelText'              => $labelText,
                    'integeronly'=> $integeronly,
                    ), true);
            } else {
                $sRows .= doRender('/survey/questions/answer/multiplenumeric/rows/sliders/answer_row', array(
                    'qid'                    => $ia[0],
                    'basename'               => $ia[1],
                    'extraclass'             => $extraclass,
                    'sDisplayStyle'          => $sDisplayStyle,
                    'kpclass'                => $kpclass,
                    'alert'                  => $alert,
                    'theanswer'              => $theanswer,
                    'labelname'              => 'answer'.$myfname,
                    'prefixclass'            => $prefixclass,
                    'sliders'                => $sliders,
                    'sliderleft'             => $sliderleft,
                    'sliderright'            => $sliderright,
                    'prefix'                 => $prefix,
                    'suffix'                 => $suffix,
                    'sInputContainerWidth'   => $sInputContainerWidth,
                    'sLabelWidth'            => $sLabelWidth,
                    'sliderWidth'            => $sliderWidth,
                    'inputsize'              => $inputsize,
                    'myfname'                => $myfname,
                    'dispVal'                => $sValue,
                    'maxlength'              => $maxlength,
                    'labelText'              => $labelText,
                    'slider_orientation'     => $slider_orientation,
                    'slider_value'           => $slider_position !== '' ?  $slider_position : $sUnformatedValue,
                    'slider_step'            => $slider_step,
                    'slider_min'             => $slider_min,
                    'slider_mintext'         => $slider_mintext,
                    'slider_max'             => $slider_max,
                    'slider_maxtext'         => $slider_maxtext,
                    'slider_position'        => $slider_position,
                    'slider_reset_set'       => $slider_default_set,
                    'slider_handle'          => (isset($slider_handle)) ? $slider_handle : '',
                    'slider_reset'           => $slider_reset,
                    'slider_reversed'        => $slider_reversed,
                    'slider_custom_handle'   => $slider_custom_handle,
                    'slider_showminmax'      => $aQuestionAttributes['slider_showminmax'],
                    'sSeparator'             => $sSeparator,
                    'sUnformatedValue'       => $sUnformatedValue,
                    'integeronly'=> $integeronly,
                    ), true);
            }
            $fn++;
            $inputnames[] = $myfname;

            //~ $aJsData=array(
            //~ 'slider_custom_handle'=>$slider_custom_handle
            //~ );
        }
        $displaytotal     = false;
        $equals_num_value = false;

        if (trim($aQuestionAttributes['equals_num_value']) != ''
        || trim($aQuestionAttributes['min_num_value']) != ''
        || trim($aQuestionAttributes['max_num_value']) != ''
        ) {
            $qinfo = LimeExpressionManager::GetQuestionStatus($ia[0]);

            if (trim($aQuestionAttributes['equals_num_value']) != '') {
                $equals_num_value = true;
            }
            $displaytotal = true;
        }

        // TODO: Slider and multiple-numeric input should really be two different question types
        $templateFile = $sliders ? 'answer' : 'answer_input';
        $answer = doRender('/survey/questions/answer/multiplenumeric/'.$templateFile, array(
            'sRows'            => $sRows,
            'coreClass'        => $coreClass,
            'prefixclass'      => $prefixclass,
            'equals_num_value' => $equals_num_value,
            'id'               => $ia[0],
            'basename'         => $ia[1],
            'suffix'           => $suffix,
            'sumRemainingEqn'  => (isset($qinfo)) ? $qinfo['sumRemainingEqn'] : '',
            'displaytotal'     => $displaytotal,
            'sumEqn'           => (isset($qinfo)) ? $qinfo['sumEqn'] : '',
            'prefix'           => $prefix, // Need to know this to place sum/remaining correctly
            'sInputContainerWidth'   => $sInputContainerWidth,
            'sLabelWidth'            => $sLabelWidth,
            ), true);
    }

    if ($aQuestionAttributes['slider_layout'] == 1) {
        /* Add some data for javascript */
        $sliderTranslation = array(
            'help'=>gT('Please click and drag the slider handles to enter your answer.')
        );
        App()->getClientScript()->registerScript("sliderTranslation", "var sliderTranslation=".json_encode($sliderTranslation).";\n", CClientScript::POS_BEGIN);
        App()->getClientScript()->registerPackage("question-numeric-slider");
    }

    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_numerical($ia)
{
    global $thissurvey;
    $extraclass             = "";
    $answertypeclass        = "numeric";
    $checkconditionFunction = "fixnum_checkconditions";
    $aQuestionAttributes    = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $coreClass = "ls-answers answer-item text-item numeric-item";

    if (trim($aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $prefix      = $aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withprefix";
    } else {
        $prefix = '';
    }

    if (trim($aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $suffix      = $aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withsuffix";
    } else {
        $suffix = '';
    }
    if (intval(trim($aQuestionAttributes['maximum_chars'])) > 0 && intval(trim($aQuestionAttributes['maximum_chars'])) < 20) {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maxlength = intval(trim($aQuestionAttributes['maximum_chars']));
        $extraclass .= " ls-input-maxchars";
    } else {
        $maxlength = 20;
    }
    if (trim($aQuestionAttributes['text_input_width']) != '') {
        $col         = ($aQuestionAttributes['text_input_width'] <= 12) ? $aQuestionAttributes['text_input_width'] : 12;
        $extraclass .= " col-sm-".trim($col);
        $withColumn = true;
    } else {
        $withColumn = false;
    }
    if (ctype_digit(trim($aQuestionAttributes['input_size']))) {
        $inputsize = trim($aQuestionAttributes['input_size']);
        $extraclass .= " ls-input-sized";
    } else {
        $inputsize = null;
    }
    if (trim($aQuestionAttributes['num_value_int_only']) == 1) {
        $extraclass      .= " integeronly";
        $answertypeclass .= " integeronly";
        $integeronly      = 1;
    } else {
        $integeronly = 0;
    }

    $fValue     = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]];
    $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeparator = $sSeparator['separator'];

    // Fix the display value : Value is stored as decimal in SQL
    if($fValue && $fValue[0] == ".") {
        // issue #15684 mssql SAVE 0.01 AS .0100000000, set it at 0.0100000000
        $fValue = "0" . $fValue;
    }
    if (strpos($fValue, ".")) {
        $fValue = rtrim(rtrim($fValue, "0"), ".");
    }
    $fValue = str_replace('.', $sSeparator, $fValue);

    if ($thissurvey['nokeyboard'] == 'Y') {
        includeKeypad();
        $extraclass      .= " inputkeypad";
        $answertypeclass .= " num-keypad";
    }

    $answer = doRender('/survey/questions/answer/numerical/answer', array(
        'extraclass'             => $extraclass,
        'coreClass'              => $coreClass,
        'withColumn'             => $withColumn,
        'id'                     => $ia[1],
        'basename'               => $ia[1],
        'prefix'                 => $prefix,
        'answertypeclass'        => $answertypeclass,
        'inputsize'              => $inputsize,
        'fValue'                 => $fValue,
        'checkconditionFunction' => $checkconditionFunction,
        'integeronly'            => $integeronly,
        'maxlength'              => $maxlength,
        'suffix'                 => $suffix,
        ), true);

    $inputnames = array();
    $inputnames[] = $ia[1];
    $mandatory = null;
    return array($answer, $inputnames, $mandatory);
}

// ---------------------------------------------------------------
function do_shortfreetext($ia)
{
    global $thissurvey;

    $sGoogleMapsAPIKey = trim(Yii::app()->getConfig("googleMapsAPIKey"));
    $coreClass = "ls-answers answer-item text-item";
    if ($sGoogleMapsAPIKey != '') {
        $sGoogleMapsAPIKey = '&key='.$sGoogleMapsAPIKey;
    }

    $extraclass = "";
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

    if ($aQuestionAttributes['numbers_only'] == 1) {
        $sSeparator             = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator             = $sSeparator['separator'];
        $extraclass            .= " numberonly";
        $coreClass             .= " numeric-item";
        $checkconditionFunction = "fixnum_checkconditions";
    } else {
        $sSeparator = '';
        $checkconditionFunction = "checkconditions";
    }
    if (intval(trim($aQuestionAttributes['maximum_chars'])) > 0) {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maxlength      = intval(trim($aQuestionAttributes['maximum_chars']));
        $extraclass    .= " ls-input-maxchars";
    } else {
        $maxlength = "";
    }

    if (trim($aQuestionAttributes['text_input_width']) != '' && intval(trim($aQuestionAttributes['location_mapservice'])) == 0) {
        $col         = ($aQuestionAttributes['text_input_width'] <= 12) ? $aQuestionAttributes['text_input_width'] : 12;
        $extraclass .= " col-sm-".trim($col);
        $withColumn = true;
    } else {
        $withColumn = false;
    }
    if (ctype_digit(trim($aQuestionAttributes['input_size']))) {
        $inputsize = trim($aQuestionAttributes['input_size']);
        $extraclass .= " ls-input-sized";
    } else {
        $inputsize = null;
    }
    if (trim($aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $prefix      = $aQuestionAttributes['prefix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withprefix";
    } else {
        $prefix = '';
    }
    if (trim($aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $suffix      = $aQuestionAttributes['suffix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withsuffix";
    } else {
        $suffix = '';
    }
    if ($thissurvey['nokeyboard'] == 'Y') {
        includeKeypad();
        $kpclass     = "text-keypad";
        $extraclass .= " inputkeypad";
    } else {
        $kpclass = "";
    }
    $answer = "";
    $sQuestionHelpText = '';

    if (trim($aQuestionAttributes['display_rows']) != '') {
        //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
        $drows = $aQuestionAttributes['display_rows'];

        $dispVal = "";

        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) {
            $dispVal = str_replace("\\", "", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]);

            if ($aQuestionAttributes['numbers_only'] == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $dispVal = htmlspecialchars($dispVal);
        }

        $answer .= doRender('/survey/questions/answer/shortfreetext/textarea/item', array(
            'extraclass'             => $extraclass,
            'coreClass'              => $coreClass,
            'freeTextId'             => 'answer'.$ia[1],
            'name'                   => $ia[1],
            'basename'               => $ia[1],
            'drows'                  => $drows,
            'checkconditionFunction' => $checkconditionFunction.'(this.value, this.name, this.type)',
            'dispVal'                => $dispVal,
            'maxlength'              => $maxlength,
            'kpclass'                => $kpclass,
            'prefix'                 => $prefix,
            'suffix'                 => $suffix,
            'inputsize'              => $inputsize,
            'withColumn'             => $withColumn
            ), true);
    } elseif ((int) ($aQuestionAttributes['location_mapservice']) == 1) {
        $coreClass       = "ls-answers map-item geoloc-item";
        $currentLocation = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]];
        $currentLatLong  = null;
        // Get the latitude/longtitude for the point that needs to be displayed by default
        if (strlen($currentLocation) > 2 && strpos($currentLocation, ";")) { // Quick check if current location is OK
            $currentLatLong = explode(';', $currentLocation);
            $currentLatLong = array($currentLatLong[0], $currentLatLong[1]);
        } else {
            if ((int) ($aQuestionAttributes['location_nodefaultfromip']) == 0) {
                $currentLatLong = getLatLongFromIp(getIPAddress());
            }

            if (empty($currentLatLong)) {
                $floatLat = "";
                $floatLng = "";
                $sDefaultcoordinates=trim(LimeExpressionManager::ProcessString($aQuestionAttributes['location_defaultcoordinates'], $ia[0], array(), 3, 1, false, false, true));/* static var is the last one */
                if (strlen($sDefaultcoordinates) > 2 && strpos($sDefaultcoordinates, " ")) {
                    $LatLong = explode(" ", $sDefaultcoordinates);
                    if (isset($LatLong[0]) && isset($LatLong[1])) {
                        $floatLat = $LatLong[0];
                        $floatLng = $LatLong[1];
                    }
                }
                $currentLatLong = array($floatLat, $floatLng);
            }
        }
        // 2 - city; 3 - state; 4 - country; 5 - postal
        $strBuild = "";
        if ($aQuestionAttributes['location_city']) {
            $strBuild .= "2";
        }
        if ($aQuestionAttributes['location_state']) {
            $strBuild .= "3";
        }
        if ($aQuestionAttributes['location_country']) {
            $strBuild .= "4";
        }
        if ($aQuestionAttributes['location_postal']) {
            $strBuild .= "5";
        }

        $currentLocation = $currentLatLong[0]." ".$currentLatLong[1];

        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."map.js", LSYii_ClientScript::POS_END);
        if ($aQuestionAttributes['location_mapservice'] == 1 && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") {
            Yii::app()->getClientScript()->registerScriptFile("https://maps.googleapis.com/maps/api/js?sensor=false$sGoogleMapsAPIKey", LSYii_ClientScript::POS_BEGIN);
        } elseif ($aQuestionAttributes['location_mapservice'] == 1) {
            Yii::app()->getClientScript()->registerScriptFile("http://maps.googleapis.com/maps/api/js?sensor=false$sGoogleMapsAPIKey", LSYii_ClientScript::POS_BEGIN);
        } elseif ($aQuestionAttributes['location_mapservice'] == 2) {
            /* 2019-04-01 : openlayers auto redirect to https (on firefox) , but always good to use automatic protocol */
            Yii::app()->getClientScript()->registerScriptFile("//www.openlayers.org/api/OpenLayers.js", LSYii_ClientScript::POS_BEGIN);
        }

        $questionHelp = false;
        if (isset($aQuestionAttributes['hide_tip']) && $aQuestionAttributes['hide_tip'] == 0) {
            $questionHelp = true;
            $sQuestionHelpText = gT('Drag and drop the pin to the desired location. You may also right click on the map to move the pin.');
        }
        $answer = doRender('/survey/questions/answer/shortfreetext/location_mapservice/item', array(
            'extraclass'             => $extraclass,
            'coreClass'              => $coreClass,
            'freeTextId'             => 'answer'.$ia[1],
            'name'                   => $ia[1],
            'qid'                    => $ia[0],
            'basename'               => $ia[1],
            'checkconditionFunction' => $checkconditionFunction.'(this.value, this.name, this.type)',
            'value'                  => $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]],
            'kpclass'                => $kpclass,
            'currentLocation'        => $currentLocation,
            'strBuild'               => $strBuild,
            'location_mapservice'    => $aQuestionAttributes['location_mapservice'],
            'location_mapzoom'       => $aQuestionAttributes['location_mapzoom'],
            'location_mapheight'     => $aQuestionAttributes['location_mapheight'],
            'questionHelp'           => $questionHelp,
            'question_text_help'     => $sQuestionHelpText,
            'inputsize'              => $inputsize,
            'withColumn'             => $withColumn
            ), true);
    } elseif ((int) ($aQuestionAttributes['location_mapservice']) == 100) {
        $coreClass       = "ls-answers map-item geoloc-item";
        $currentLocation = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]];
        $currentCenter   = $currentLatLong = null;
        // Get the latitude/longtitude for the point that needs to be displayed by default
        if (strlen($currentLocation) > 2 && strpos($currentLocation, ";")) {
            $currentLatLong = explode(';', $currentLocation);
            $currentCenter  = $currentLatLong = array($currentLatLong[0], $currentLatLong[1]);
        } elseif ((int) ($aQuestionAttributes['location_nodefaultfromip']) == 0) {
            $currentCenter = $currentLatLong = getLatLongFromIp(getIPAddress());
        }

        // If it's not set : set the center to the default position, but don't set the marker
        if (!$currentLatLong) {
            $currentLatLong = array("", "");
            $sDefaultcoordinates=trim(LimeExpressionManager::ProcessString($aQuestionAttributes['location_defaultcoordinates'], $ia[0], array(), 3, 1, false, false, true));/* static var is the last one */
            $currentCenter = explode(" ", $sDefaultcoordinates);
            if (count($currentCenter) != 2) {
                $currentCenter = array("", "");
            }
        }
        $strBuild = "";

        $aGlobalMapScriptVar = array(
            'geonameUser'=>getGlobalSetting('GeoNamesUsername'), // Did we need to urlencode ?
            'geonameLang'=>Yii::app()->language,
        );
        $aThisMapScriptVar = array(
            'zoomLevel'=>$aQuestionAttributes['location_mapzoom'],
            'latitude'=>$currentCenter[0],
            'longitude'=>$currentCenter[1],

        );
        App()->getClientScript()->registerPackage('leaflet');
        App()->getClientScript()->registerPackage('devbridge-autocomplete'); /* for autocomplete */
        Yii::app()->getClientScript()->registerScript('sGlobalMapScriptVar', "LSmap=".ls_json_encode($aGlobalMapScriptVar).";\nLSmaps= new Array();", CClientScript::POS_BEGIN);
        Yii::app()->getClientScript()->registerScript('sThisMapScriptVar'.$ia[1], "LSmaps['{$ia[1]}']=".ls_json_encode($aThisMapScriptVar).";", CClientScript::POS_BEGIN);
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."map.js", CClientScript::POS_END);
        Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl').'map.css');

        if (isset($aQuestionAttributes['hide_tip']) && $aQuestionAttributes['hide_tip'] == 0) {
            $questionHelp = true;
            $sQuestionHelpText = gT('Click to set the location or drag and drop the pin. You may may also enter coordinates');
        }

        $itemDatas = array(
            'extraclass'=>$extraclass,
            'coreClass'=> $coreClass,
            'name'=>$ia[1],
            'qid'=>$ia[0],
            'basename'               => $ia[1],
            'checkconditionFunction'=>$checkconditionFunction.'(this.value, this.name, this.type)',
            'value'=>$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]],
            'strBuild'=>$strBuild,
            'location_mapservice'=>$aQuestionAttributes['location_mapservice'],
            'location_mapzoom'=>$aQuestionAttributes['location_mapzoom'],
            'location_mapheight'=>$aQuestionAttributes['location_mapheight'],
            'questionHelp'=>(isset($questionHelp)) ? $questionHelp : '',
            'question_text_help'=>$sQuestionHelpText,
            'location_value'=> $currentLatLong[0].' '.$currentLatLong[1],
            'currentLat'=>$currentLatLong[0],
            'currentLong'=>$currentLatLong[1],
            'inputsize'              => $inputsize,
            'withColumn'             => $withColumn
        );
        $answer = doRender('/survey/questions/answer/shortfreetext/location_mapservice/item_100', $itemDatas, true);
    } else {
        //no question attribute set, use common input text field
        $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]];
        if ($aQuestionAttributes['numbers_only'] == 1) {
            $dispVal = str_replace('.', $sSeparator, $dispVal);
        }
        $dispVal = htmlspecialchars($dispVal, ENT_QUOTES, 'UTF-8');

        $itemDatas = array(
            'extraclass'=>$extraclass,
            'coreClass'=> $coreClass,
            'name'=>$ia[1],
            'basename'               => $ia[1],
            'prefix'=>$prefix,
            'suffix'=>$suffix,
            'kpclass'=>$kpclass,
            'dispVal'=>$dispVal,
            'maxlength'=>$maxlength,
            'inputsize'              => $inputsize,
            'withColumn'             => $withColumn
        );
        $answer = doRender('/survey/questions/answer/shortfreetext/text/item', $itemDatas, true);
    }

    if (trim($aQuestionAttributes['time_limit']) != '') {
        $answer .= return_timer_script($aQuestionAttributes, $ia, "answer".$ia[1]);
    }

    $inputnames = array();
    $inputnames[] = $ia[1];
    return array($answer, $inputnames);
}

function getLatLongFromIp($sIPAddress)
{
    $ipInfoDbAPIKey = Yii::app()->getConfig("ipInfoDbAPIKey");
    if ($ipInfoDbAPIKey) {
        // ipinfodb.com needs a key
        $oXML = simplexml_load_file("http://api.ipinfodb.com/v3/ip-city/?key=$ipInfoDbAPIKey&ip=$sIPAddress&format=xml");
        if ($oXML->{'statusCode'} == "OK") {
            $lat = (float) $oXML->{'latitude'};
            $lng = (float) $oXML->{'longitude'};

            return(array($lat, $lng));
        } else {
            return false;
        }
    }
}



// ---------------------------------------------------------------
function do_longfreetext($ia)
{
    global $thissurvey;
    $extraclass = "";
    $coreClass = "ls-answers answer-item text-item";
    if ($thissurvey['nokeyboard'] == 'Y') {
        includeKeypad();
        $kpclass     = "text-keypad";
        $extraclass .= " inputkeypad";
    } else {
        $kpclass = "";
    }

    $checkconditionFunction = "checkconditions";
    $aQuestionAttributes    = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

    if (intval(trim($aQuestionAttributes['maximum_chars'])) > 0) {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maxlength = intval(trim($aQuestionAttributes['maximum_chars']));
        $extraclass .= " ls-input-maxchars";
    } else {
        $maxlength = "";
    }

    if (trim($aQuestionAttributes['display_rows']) != '') {
        $drows = $aQuestionAttributes['display_rows'];
    } else {
        $drows = 5;
    }

    if (trim($aQuestionAttributes['text_input_width']) != '') {
        // text_input_width can not be empty, except with old survey (wher can be empty or up to 12 see bug #11743
        $col         = ($aQuestionAttributes['text_input_width'] <= 12) ? $aQuestionAttributes['text_input_width'] : 12;
        $extraclass .= " col-sm-".trim($col);
        $withColumn = true;
    } else {
        $withColumn = false;
    }
    if (ctype_digit(trim($aQuestionAttributes['input_size']))) {
        $inputsize = trim($aQuestionAttributes['input_size']);
        $extraclass .= " ls-input-sized";
    } else {
        $inputsize = null;
    }

    $dispVal = ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) ?htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) : '';

    $answer = doRender('/survey/questions/answer/longfreetext/answer', array(
        'extraclass'             => $extraclass,
        'coreClass'              =>$coreClass,
        'withColumn'             =>$withColumn,
        'kpclass'                => $kpclass,
        'name'                   => $ia[1],
        'basename'               => $ia[1],
        'drows'                  => $drows,
        'checkconditionFunction' => $checkconditionFunction.'(this.value, this.name, this.type)',
        'dispVal'                => $dispVal,
        'inputsize'              => $inputsize,
        'maxlength'              => $maxlength,
        ), true);


    if (trim($aQuestionAttributes['time_limit']) != '') {
        $answer .= return_timer_script($aQuestionAttributes, $ia, "answer".$ia[1]);
    }

    $inputnames = array();
    $inputnames[] = $ia[1];
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_hugefreetext($ia)
{
    global $thissurvey;
    $extraclass = "";
    $coreClass = "ls-answers answer-item text-item";
    if ($thissurvey['nokeyboard'] == 'Y') {
        includeKeypad();
        $kpclass = "text-keypad";
        $extraclass .= " inputkeypad";
    } else {
        $kpclass = "";
    }

    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

    if (intval(trim($aQuestionAttributes['maximum_chars'])) > 0) {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maxlength = intval(trim($aQuestionAttributes['maximum_chars']));
        $extraclass .= " ls-input-maxchars";
    } else {
        $maxlength = "";
    }

    if (trim($aQuestionAttributes['display_rows']) != '') {
        $drows = $aQuestionAttributes['display_rows'];
    } else {
        $drows = 30;
    }
    if (trim($aQuestionAttributes['text_input_width']) != '') {
        $col = ($aQuestionAttributes['text_input_width'] <= 12) ? $aQuestionAttributes['text_input_width'] : 12;
        $extraclass .= " col-sm-".trim($col);
        $withColumn = true;
    } else {
        $withColumn = false;
    }
    if (ctype_digit(trim($aQuestionAttributes['input_size']))) {
        $inputsize = trim($aQuestionAttributes['input_size']);
        $extraclass .= " ls-input-sized";
    } else {
        $inputsize = null;
    }

    $dispVal = "";
    if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) {
        $dispVal = htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]);
    }

    $itemDatas = array(
        'extraclass'=>$extraclass,
        'coreClass'=>$coreClass,
        'withColumn'=>$withColumn,
        'kpclass'=>$kpclass,
        'name'=>$ia[1],
        'basename'=> $ia[1],
        'drows'=>$drows,
        'checkconditionFunction'=>$checkconditionFunction.'(this.value, this.name, this.type)',
        'dispVal'=>$dispVal,
        'inputsize'=>$inputsize,
        'maxlength'=>$maxlength,
    );
    $answer = doRender('/survey/questions/answer/longfreetext/answer', $itemDatas, true);

    if (trim($aQuestionAttributes['time_limit']) != '') {
        $answer .= return_timer_script($aQuestionAttributes, $ia, "answer".$ia[1]);
    }

    $inputnames = array();
    $inputnames[] = $ia[1];
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_yesno($ia)
{
    $coreClass = "ls-answers answers-list";
    $yChecked = $nChecked = $naChecked = '';
    if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == 'Y') {
        $yChecked = CHECKED;
    }

    if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == 'N') {
        $nChecked = CHECKED;
    }

    $noAnswer = false;
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        $noAnswer = true;
        if (empty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]])) {
            $naChecked = CHECKED;
        }
    }

    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $displayType = $aQuestionAttributes['display_type'];
    $noAnswer = (isset($noAnswer)) ? $noAnswer : false;
    $itemDatas = array(
        'name'=>$ia[1],
        'basename'=>$ia[1],
        'yChecked' => $yChecked,
        'nChecked' => $nChecked,
        'naChecked'=> $naChecked,
        'noAnswer' => $noAnswer,
        'value' => $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]],
        'displayType'=>$displayType,
    );
    if ($displayType === 0) {
        $itemDatas['coreClass'] = "{$coreClass} button-list yesno-button";
        $answer = doRender('/survey/questions/answer/yesno/buttons/item', $itemDatas, true);
    } else {
        $itemDatas['coreClass'] = "{$coreClass} radio-list yesno-radio-list";
        $answer = doRender('/survey/questions/answer/yesno/radio/item', $itemDatas, true);
    }

    $inputnames = array();
    $inputnames[] = $ia[1];
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_gender($ia)
{
    $fChecked               = ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == 'F') ? 'CHECKED' : '';
    $mChecked               = ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == 'M') ? 'CHECKED' : '';
    $naChecked              = '';
    $aQuestionAttributes    = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $displayType            = $aQuestionAttributes['display_type'];
    $coreClass              = "ls-answers answers-list radio-list";
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        $noAnswer = true;
        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '') {
            $naChecked = CHECKED;
        }
    }

    $noAnswer = (isset($noAnswer)) ? $noAnswer : false;

    $itemDatas = array(
        'name'                   => $ia[1],
        'basename'               => $ia[1],
        'fChecked'               => $fChecked,
        'mChecked'               => $mChecked,
        'naChecked'              => $naChecked,
        'noAnswer'               => $noAnswer,
        'value'                  => $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]],
    );

    if ($displayType === 0) {
        $itemDatas['coreClass'] = "{$coreClass} button-list gender-button";
        $answer = doRender('/survey/questions/answer/gender/buttons/answer', $itemDatas, true);
    } else {
        $itemDatas['coreClass'] = "{$coreClass} radio-list gender-radio-list";
        $answer = doRender('/survey/questions/answer/gender/radio/answer', $itemDatas, true);
    }

    $inputnames   = array();
    $inputnames[] = $ia[1];

    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
/**
* Construct answer part array_5point
* @param array $ia
* @return array
*/
function do_array_5point($ia)
{
    global $thissurvey;
    $aLastMoveResult         = LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|", $aLastMoveResult['unansweredSQs']) : array();
    $coreClass               = "ls-answers subquestion-list questions-list radio-array";
    $checkconditionFunction  = "checkconditions";
    $aQuestionAttributes     = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $inputnames              = array();

    if (trim($aQuestionAttributes['answer_width']) != '') {
        $answerwidth = $aQuestionAttributes['answer_width'];
        $defaultWidth = false;
    } else {
        $answerwidth = 33;
        $defaultWidth = true;
    }
    $columnswidth = 100 - $answerwidth;
    $colCount = 5; // number of columns

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        ++$colCount; // add another column
    }

    if ($aQuestionAttributes['random_order'] == 1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    } else {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }

    $ansresult     = dbExecuteAssoc($ansquery); //Checked
    $aSubquestions = $ansresult->readAll();
    $fn            = 1;
    $sColumns      = $sHeaders = $sRows = $answer_tds = '';

    // Check if any subquestion use suffix/prefix
    $right_exists  = false;
    foreach ($aSubquestions as $j => $ansrow) {
        $answertext2 = $ansrow['question'];
        if (strpos($answertext2, '|')) {
            $right_exists = true;
        }
    }
    if ($right_exists) {
        /* put the right answer to same width : take place in answer width only if it's not default */
        if ($defaultWidth) {
            $columnswidth -= $answerwidth;
        } else {
            $answerwidth = $answerwidth / 2;
        }
    }
    $cellwidth = $columnswidth / $colCount;
    for ($xc = 1; $xc <= 5; $xc++) {
        $sColumns .= doRender('/survey/questions/answer/arrays/5point/columns/col', array('cellwidth'=>$cellwidth), true);
    }

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        $sColumns .= doRender('/survey/questions/answer/arrays/5point/columns/col', array('cellwidth'=>$cellwidth), true);
    }

    // Column for suffix
    if ($right_exists) {
        $sColumns .= doRender('/survey/questions/answer/arrays/5point/columns/col', array('cellwidth'=>$answerwidth), true);
    }

    $sHeaders .= doRender('/survey/questions/answer/arrays/5point/rows/cells/header_information', array(
        'class'=>'',
        'content'=>'',
        ), true);
    for ($xc = 1; $xc <= 5; $xc++) {
        $sHeaders .= doRender('/survey/questions/answer/arrays/5point/rows/cells/header_answer', array(
            'class'=>'answer-text',
            'content'=>$xc,
            ), true);
    }

    // Header for suffix
    if ($right_exists) {
        $sHeaders .= doRender('/survey/questions/answer/arrays/5point/rows/cells/header_information', array(
            'class'=>'answertextright',
            'content'=>'',
            ), true);
    }

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        $sHeaders .= doRender('/survey/questions/answer/arrays/5point/rows/cells/header_answer', array(
            'class'=>'answer-text noanswer-text',
            'content'=>gT('No answer'),
            ), true);
    }


    foreach ($aSubquestions as $j => $ansrow) {
        $myfname = $ia[1].$ansrow['title'];
        $answertext = $ansrow['question'];
        if (strpos($answertext, '|') !== false) {
            $answertext = substr($answertext, 0, strpos($answertext, '|'));
        }

        /* Check if this item has not been answered */
        $error = ($ia[6] == 'Y' && in_array($myfname, $aMandatoryViolationSubQ)) ?true:false;

        /* Check for array_filter  */
        $sDisplayStyle = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);

        // Value
        $value = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';

        for ($i = 1; $i <= 5; $i++) {
            $CHECKED = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $i) ? 'CHECKED' : '';
            $answer_tds .= doRender('/survey/questions/answer/arrays/5point/rows/cells/answer_td_input', array(
                'i'=>$i,
                'labelText'=>$i,
                'myfname'=>$myfname,
                'CHECKED'=>$CHECKED,
                'checkconditionFunction'=>$checkconditionFunction,
                'value'=>$i,
                ), true);
        }

        // Suffix
        $answertext2 = $ansrow['question'];
        if (strpos($answertext2, '|')) {
            $answertext2 = substr($answertext2, strpos($answertext2, '|') + 1);
            $answer_tds .= doRender('/survey/questions/answer/arrays/5point/rows/cells/answer_td_answertext', array(
                'class'=>'answertextright',
                'style'=>'text-align:left',
                'answertext2'=>$answertext2,
                ), true);
        } elseif ($right_exists) {
            $answer_tds .= doRender('/survey/questions/answer/arrays/5point/rows/cells/answer_td_answertext', array(
                'answerwidth'=>$answerwidth,
                'answertext2'=>'',
                ), true);
        }

        // ==>tds
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
            $CHECKED = (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') ? 'CHECKED' : '';
            $answer_tds .= doRender('/survey/questions/answer/arrays/5point/rows/cells/answer_td_input', array(
                'i'=>"",
                'labelText'=>gT('No answer'),
                'myfname'=>$myfname,
                'CHECKED'=>$CHECKED,
                'checkconditionFunction'=>$checkconditionFunction,
                'value'=>'',
                ), true);
        }

        $sRows .= doRender('/survey/questions/answer/arrays/5point/rows/answer_row', array(
            'answer_tds'    => $answer_tds,
            'myfname'       => $myfname,
            'answertext'    => $answertext,
            'answerwidth'=>$answerwidth,
            'value'         => $value,
            'error'         => $error,
            'sDisplayStyle' => $sDisplayStyle,
            'odd'           => ($j % 2), // true for odd, false for even
            ), true);
        $answer_tds = '';
        $fn++;
        $inputnames[] = $myfname;
    }

    $answer = doRender('/survey/questions/answer/arrays/5point/answer', array(
        'coreClass' => $coreClass,
        'sColumns'   => $sColumns,
        'answerwidth'   => $answerwidth,
        'sHeaders'   => $sHeaders,
        'sRows'      => $sRows,
        'basename' => $ia[1],
        ), true);

    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
/**
* Construct answer part array_10point
* @param array $ia
* @return array
*/
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array_10point($ia)
{
    global $thissurvey;
    $aLastMoveResult = LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|", $aLastMoveResult['unansweredSQs']) : array();
    $coreClass = "ls-answers subquestion-list questions-list radio-array";

    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    if (ctype_digit(trim($aQuestionAttributes['answer_width']))) {
        $answerwidth = trim($aQuestionAttributes['answer_width']);
    } else {
        $answerwidth = 33;
    }
    $cellwidth = 10; // number of columns
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        ++$cellwidth; // add another column
    }
    $cellwidth = round(((100 - $answerwidth) / $cellwidth), 1); // convert number of columns to percentage of table width

    if ($aQuestionAttributes['random_order'] == 1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    } else {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }
    $ansresult = dbExecuteAssoc($ansquery); //Checked
    $aSubquestions = $ansresult->readAll();

    $fn = 1;

    $odd_even = '';

    $sColumns = '';
    for ($xc = 1; $xc <= 10; $xc++) {
        $odd_even = alternation($odd_even);
        $sColumns .= doRender('/survey/questions/answer/arrays/10point/columns/col', array('odd_even'=>$odd_even, 'cellwidth'=>$cellwidth), true);
    }

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        $odd_even = alternation($odd_even);
        $sColumns .= doRender('/survey/questions/answer/arrays/10point/columns/col', array('odd_even'=>$odd_even, 'cellwidth'=>$cellwidth), true);
    }

    $sHeaders = '';
    $sHeaders .= doRender('/survey/questions/answer/arrays/10point/rows/cells/header_information', array(
        'class'=>'',
        'content'=>'',
        ), true);
    for ($xc = 1; $xc <= 10; $xc++) {
        $sHeaders .= doRender('/survey/questions/answer/arrays/10point/rows/cells/header_answer', array(
            'class'=>'answer-text',
            'content'=>$xc,
            ), true);
    }

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        $sHeaders .= doRender('/survey/questions/answer/arrays/10point/rows/cells/header_answer', array(
            'class'=>'answer-text noanswer-text',
            'content'=>gT('No answer'),
            ), true);
    }

    $trbc = '';

    $sRows = '';
    $inputnames = array();
    foreach ($aSubquestions as $j => $ansrow) {
        $myfname = $ia[1].$ansrow['title'];
        $answertext = $ansrow['question'];
        /* Check if this item has not been answered */
        $error = ($ia[6] == 'Y' && in_array($myfname, $aMandatoryViolationSubQ)) ?true:false;
        $trbc = alternation($trbc, 'row');

        //Get array filter stuff
        //list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc answers-list radio-list");
        $sDisplayStyle = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);

        // Value
        $value = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';

        $answer_tds = '';
        for ($i = 1; $i <= 10; $i++) {
            $CHECKED = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $i) ? 'CHECKED' : '';

            $answer_tds .= doRender('/survey/questions/answer/arrays/10point/rows/cells/answer_td_input', array(
                'i'=>$i,
                'labelText'=>$i,
                'myfname'=>$myfname,
                'CHECKED'=>$CHECKED,
                'checkconditionFunction'=>$checkconditionFunction,
                'value'=>$i,
                ), true);
        }

        if ($ia[6] != "Y" && SHOW_NO_ANSWER == 1) {
            $CHECKED = (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') ? 'CHECKED' : '';
            $answer_tds .= doRender('/survey/questions/answer/arrays/10point/rows/cells/answer_td_input', array(
                'i'=>'',
                'labelText'=>gT('No answer'),
                'myfname'=>$myfname,
                'CHECKED'=>$CHECKED,
                'checkconditionFunction'=>$checkconditionFunction,
                'value'=>'',
                ), true);
        }

        $sRows .= doRender('/survey/questions/answer/arrays/10point/rows/answer_row', array(
            'myfname'       => $myfname,
            'answerwidth'   => $answerwidth,
            'answertext'    => $answertext,
            'value'         => $value,
            'error'         => $error,
            'sDisplayStyle' => $sDisplayStyle,
            'odd'           => ($j % 2),
            'answer_tds'    => $answer_tds,
            ), true);

        $inputnames[] = $myfname;
        $fn++;
    }

    $answer = doRender(
        '/survey/questions/answer/arrays/10point/answer',
        array(
        'coreClass'     => $coreClass,
        'answerwidth'   => $answerwidth,
        'sColumns'      => $sColumns,
        'sHeaders'      => $sHeaders,
        'sRows'         => $sRows,
        'basename' => $ia[1],
        ),
        true
    );
    return array($answer, $inputnames);
}


function do_array_yesnouncertain($ia)
{
    $aLastMoveResult         = LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|", $aLastMoveResult['unansweredSQs']) : array();
    $coreClass               = "ls-answers subquestion-list questions-list radio-array";
    $checkconditionFunction  = "checkconditions";
    $aQuestionAttributes     = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    if (ctype_digit(trim($aQuestionAttributes['answer_width']))) {
        $answerwidth = trim($aQuestionAttributes['answer_width']);
    } else {
        $answerwidth = 33;
    }
    $cellwidth               = 3; // number of columns

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        ++$cellwidth; // add another column
    }

    $cellwidth = round(((100 - $answerwidth) / $cellwidth), 1); // convert number of columns to percentage of table width

    if ($aQuestionAttributes['random_order'] == 1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    } else {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }

    $ansresult      = dbExecuteAssoc($ansquery); //Checked
    $aSubquestions  = $ansresult->readAll();
    $anscount       = count($aSubquestions);
    $fn             = 1;

    $odd_even = '';
    $sColumns = '';

    for ($xc = 1; $xc <= 3; $xc++) {
        $odd_even  = alternation($odd_even);
        $sColumns .= doRender('/survey/questions/answer/arrays/yesnouncertain/columns/col', array('odd_even'=>$odd_even, 'cellwidth'=>$cellwidth), true);
    }

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        $odd_even  = alternation($odd_even);
        $sColumns .= doRender('/survey/questions/answer/arrays/yesnouncertain/columns/col', array('odd_even'=>$odd_even, 'cellwidth'=>$cellwidth, 'no_answer'=>true), true);
    }

    $no_answer = ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) ?true:false;
    $sHeaders  = doRender('/survey/questions/answer/arrays/yesnouncertain/rows/cells/thead', array('no_answer'=>$no_answer, 'anscount'=>$anscount), true);

    $inputnames = array();
    if ($anscount > 0) {
        $sRows = '';

        foreach ($aSubquestions as $i => $ansrow) {
            $myfname = $ia[1].$ansrow['title'];
            $answertext = $ansrow['question'];
            /* Check the sub question mandatory violation */
            $error = ($ia[6] == 'Y' && in_array($myfname, $aMandatoryViolationSubQ)) ?true:false;

            // Get array_filter stuff
            $no_answer = ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) ?true:false;
            $value     = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';
            $Ychecked  = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'Y') ? 'CHECKED' : '';
            $Uchecked  = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'U') ? 'CHECKED' : '';
            $Nchecked  = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'N') ? 'CHECKED' : '';
            $NAchecked = (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') ? 'CHECKED' : '';

            $sRows .= doRender('/survey/questions/answer/arrays/yesnouncertain/rows/answer_row', array(
                'myfname'                => $myfname,
                'answertext'             => $answertext,
                'answerwidth'=>$answerwidth,
                'Ychecked'               => $Ychecked,
                'Uchecked'               => $Uchecked,
                'Nchecked'               => $Nchecked,
                'NAchecked'              => $NAchecked,
                'value'                  => $value,
                'checkconditionFunction' => $checkconditionFunction,
                'error'                  => $error,
                'no_answer'              => $no_answer,
                'odd'                    => ($i % 2)
                ), true);
            $inputnames[] = $myfname;
            $fn++;
        }
    }

    $answer = doRender('/survey/questions/answer/arrays/yesnouncertain/answer', array(
        'answerwidth' => $answerwidth,
        'coreClass'   => $coreClass,
        'sColumns'    => $sColumns,
        'sHeaders'    => $sHeaders,
        'sRows'       => (isset($sRows)) ? $sRows : '',
        'anscount'    => $anscount,
        'basename' => $ia[1],
        ), true);

    return array($answer, $inputnames);
}


function do_array_increasesamedecrease($ia)
{
    $aLastMoveResult         = LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|", $aLastMoveResult['unansweredSQs']) : array();
    $coreClass               = "ls-answers subquestion-list questions-list radio-array";
    $checkconditionFunction  = "checkconditions";
    $aQuestionAttributes     = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    if (ctype_digit(trim($aQuestionAttributes['answer_width']))) {
        $answerwidth = trim($aQuestionAttributes['answer_width']);
    } else {
        $answerwidth = 33;
    }
    $cellwidth               = 3; // number of columns
    $inputnames              = array();

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        ++$cellwidth; // add another column
    }

    $cellwidth = round(((100 - $answerwidth) / $cellwidth), 1); // convert number of columns to percentage of table width

    if ($aQuestionAttributes['random_order'] == 1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    } else {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }

    $ansresult      = dbExecuteAssoc($ansquery); //Checked
    $aSubquestions  = $ansresult->readAll();
    $anscount       = count($aSubquestions);
    $fn             = 1;
    $odd_even       = '';
    $sColumns       = "";

    for ($xc = 1; $xc <= 3; $xc++) {
        $odd_even  = alternation($odd_even);
        $sColumns .= doRender('/survey/questions/answer/arrays/increasesamedecrease/columns/col', array('odd_even'=>$odd_even, 'cellwidth'=>$cellwidth), true);
    }
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        $odd_even  = alternation($odd_even);
        $sColumns .= doRender('/survey/questions/answer/arrays/increasesamedecrease/columns/col', array('odd_even'=>$odd_even, 'cellwidth'=>$cellwidth), true);
    }

    $no_answer = ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) ?true:false; //Question is not mandatory

    $sHeaders = doRender('/survey/questions/answer/arrays/increasesamedecrease/rows/cells/thead', array('no_answer'=>$no_answer), true);


    // rows
    $sRows = '';
    foreach ($aSubquestions as $i => $ansrow) {
        $myfname        = $ia[1].$ansrow['title'];
        $answertext     = $ansrow['question'];
        $error          = ($ia[6] == 'Y' && in_array($myfname, $aMandatoryViolationSubQ)) ?true:false; /* Check the sub Q mandatory violation */
        $value          = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';
        $Ichecked       = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'I') ? 'CHECKED' : '';
        $Schecked       = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'S') ? 'CHECKED' : '';
        $Dchecked       = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'D') ? 'CHECKED' : '';
        $NAchecked      = (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') ? 'CHECKED' : '';
        $no_answer      = ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) ?true:false;

        $sRows .= doRender('/survey/questions/answer/arrays/increasesamedecrease/rows/answer_row', array(
            'myfname'=> $myfname,
            'answertext'=> $answertext,
            'answerwidth'=>$answerwidth,
            'Ichecked'=>$Ichecked,
            'Schecked'=> $Schecked,
            'Dchecked'=>$Dchecked,
            'NAchecked'=>$NAchecked,
            'value'=>$value,
            'checkconditionFunction'=>$checkconditionFunction,
            'error'=>$error,
            'no_answer'=>$no_answer,
            'odd' => ($i % 2)
            ), true);

        $inputnames[] = $myfname;
        $fn++;
    }

    $answer = doRender('/survey/questions/answer/arrays/increasesamedecrease/answer', array(
        'coreClass'  => $coreClass,
        'answerwidth'=> $answerwidth,
        'sColumns'   => $sColumns,
        'sHeaders'   => $sHeaders,
        'sRows'      => $sRows,
        'anscount'   => $anscount,
        'basename' => $ia[1],
        ), true);

    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array($ia)
{
    $aLastMoveResult         = LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|", $aLastMoveResult['unansweredSQs']) : array();
    $repeatheadings          = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings       = Yii::app()->getConfig("minrepeatheadings");
    $coreClass = "ls-answers subquestion-list questions-list";
    $checkconditionFunction  = "checkconditions";
    $aQuestionAttributes     = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

    if ($aQuestionAttributes['use_dropdown'] == 1) {
        $useDropdownLayout = true;
        $coreClass .= " dropdown-array";
        $caption           = gT("A table with a subquestion on each row. You have to select your answer.");
    } else {
        $useDropdownLayout = false;
        $coreClass .= " radio-array";
        $caption           = gT("A table with a subquestion on each row. The answer options are contained in the table header.");
    }

    if (ctype_digit(trim($aQuestionAttributes['repeat_headings'])) && trim($aQuestionAttributes['repeat_headings'] != "")) {
        $repeatheadings    = intval($aQuestionAttributes['repeat_headings']);
        $minrepeatheadings = 0;
    }

    $lresult    = Answer::model()->findAll(array('order'=>'sortorder, code', 'condition'=>'qid=:qid AND language=:language AND scale_id=0', 'params'=>array(':qid'=>$ia[0], ':language'=>$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang'])));
    $labelans   = array();
    $labelcode  = array();

    foreach ($lresult as $lrow) {
        $labelans[]  = $lrow->answer;
        $labelcode[] = $lrow->code;
    }

    // No-dropdown layout
    if ($useDropdownLayout === false && count($lresult) > 0) {
        if (ctype_digit(trim($aQuestionAttributes['answer_width']))) {
            $answerwidth = trim($aQuestionAttributes['answer_width']);
            $defaultWidth = false;
        } else {
            $answerwidth = 33;
            $defaultWidth = true;
        }
        $columnswidth = 100 - $answerwidth;
        $iCount = intval(Question::model()->count("parent_qid=:qid and question like :separator", array(':qid'=>$ia[0], ":separator"=>'%|%')));
        if ($iCount > 0) {
            $right_exists = true;
            /* put the right answer to same width : take place in answer width only if it's not default */
            if ($defaultWidth) {
                $columnswidth -= $answerwidth;
            } else {
                $answerwidth = $answerwidth / 2;
            }
        } else {
            $right_exists = false;
        }
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($aQuestionAttributes['random_order'] == 1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        } else {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }

        $ansresult  = dbExecuteAssoc($ansquery); //Checked
        $aQuestions = $ansresult->readAll();
        $anscount   = count($aQuestions);
        $fn         = 1;
        $numrows    = count($labelans);

        if ($right_exists) {
            ++$numrows;
            $caption .= gT("After the answer options a cell does give some information.");
        }
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
            ++$numrows;
        }

        $cellwidth = round(($columnswidth / $numrows), 1);

        $sHeaders = doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/cells/header_information', array(
            'class'   => '',
            'content' => '',
            ), true);

        foreach ($labelans as $ld) {
            $sHeaders .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/cells/header_answer', array(
                'class'   => "answer-text",
                'content' => $ld,
                ), true);
        }

        if ($right_exists) {
            $sHeaders .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/cells/header_information', array(
                'class'     => '',
                'content'   => '',
                ), true);
        }

        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
            //Question is not mandatory and we can show "no answer"
            $sHeaders .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/cells/header_answer', array(
                'class'   => 'answer-text noanswer-text',
                'content' => gT('No answer'),
                ), true);
        }

        $inputnames = array();

        $sRows = '';
        foreach ($aQuestions as $i => $ansrow) {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn - 1) > 0 && ($fn - 1) % $repeatheadings == 0) {
                if (($anscount - $fn + 1) >= $minrepeatheadings) {
                    // Close actual body and open another one
                    $sRows .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/repeat_header', array(
                        'sHeaders'=>$sHeaders
                        ), true);
                }
            }

            $myfname        = $ia[1].$ansrow['title'];
            $answertext     = $ansrow['question'];
            $answertext     = (strpos($answertext, '|') !== false) ? substr($answertext, 0, strpos($answertext, '|')) : $answertext;

            if ($right_exists && strpos($ansrow['question'], '|') !== false) {
                $answertextright = substr($ansrow['question'], strpos($ansrow['question'], '|') + 1);
            } else {
                $answertextright = '';
            }

            $error          = (in_array($myfname, $aMandatoryViolationSubQ)) ?true:false; /* Check the mandatory sub Q violation */
            $value          = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';
            $thiskey        = 0;
            $answer_tds     = '';
            $fn++;

            foreach ($labelcode as $ld) {
                $CHECKED     = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $ld) ? 'CHECKED' : '';
                $answer_tds .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/cells/answer_td', array(
                    'myfname'=>$myfname,
                    'ld'=>$ld,
                    'label'=>$labelans[$thiskey],
                    'CHECKED'=>$CHECKED,
                    'checkconditionFunction'=>$checkconditionFunction,
                    ), true);
                $thiskey++;
            }

            // NB: $ia[6] = mandatory
            $no_answer_td = '';
            if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
                $CHECKED = (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') ? 'CHECKED' : '';
                $no_answer_td .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/cells/answer_td', array(
                    'myfname'                => $myfname,
                    'ld'                     => '',
                    'label'                  => gT('No answer'),
                    'CHECKED'                => $CHECKED,
                    'checkconditionFunction' => $checkconditionFunction,
                    ), true);
            }
            $sRows .= doRender('/survey/questions/answer/arrays/array/no_dropdown/rows/answer_row', array(
                'answer_tds' => $answer_tds,
                'no_answer_td' => $no_answer_td,
                'myfname'    => $myfname,
                'answertext' => $answertext,
                'answerwidth'=>$answerwidth,
                'answertextright' => $answertextright,
                'right_exists' => $right_exists,
                'value'      => $value,
                'error'      => $error,
                'odd'        => ($i % 2), // true for odd, false for even
                ), true);
            $inputnames[] = $myfname;
        }


        $odd_even = '';
        $sColumns = '';
        foreach ($labelans as $c) {
            $odd_even = alternation($odd_even);
            $sColumns .= doRender('/survey/questions/answer/arrays/array/no_dropdown/columns/col', array(
                'class'     => $odd_even,
                'cellwidth' => $cellwidth,
                ), true);
        }

        if ($right_exists) {
            $odd_even = alternation($odd_even);
            $sColumns .= doRender('/survey/questions/answer/arrays/array/no_dropdown/columns/col', array(
                'class'     => 'answertextright '.$odd_even,
                'cellwidth' => $answerwidth,
                ), true);
        }

        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
            //Question is not mandatory
            $odd_even = alternation($odd_even);
            $sColumns .= doRender('/survey/questions/answer/arrays/array/no_dropdown/columns/col', array(
                'class'     => 'col-no-answer '.$odd_even,
                'cellwidth' => $cellwidth,
                ), true);
        }

        $answer = doRender('/survey/questions/answer/arrays/array/no_dropdown/answer', array(
            'answerwidth'=> $answerwidth,
            'anscount'   => $anscount,
            'sRows'      => $sRows,
            'coreClass'  => $coreClass,
            'sHeaders'   => $sHeaders,
            'sColumns'   => $sColumns,
            'basename' => $ia[1],
            ), true);
    }

    // Dropdown layout
    elseif ($useDropdownLayout === true && count($lresult) > 0) {
        if (ctype_digit(trim($aQuestionAttributes['answer_width']))) {
            $answerwidth = trim($aQuestionAttributes['answer_width']);
            $defaultWidth = false;
        } else {
            $answerwidth = 33;
            $defaultWidth = true;
        }
        $columnswidth = 100 - $answerwidth;
        $labels = [];
        foreach ($lresult as $lrow) {
            $labels[] = array(
                'code'   => $lrow->code,
                'answer' => $lrow->answer
            );
        }

        $sQuery = "SELECT count(question) FROM {{questions}} WHERE parent_qid={$ia[0]} AND question like '%|%' ";
        $iCount = Yii::app()->db->createCommand($sQuery)->queryScalar();

        if ($iCount > 0) {
            $right_exists = true;
            /* put the right answer to same width : take place in answer width only if it's not default */
            if ($defaultWidth) {
                $columnswidth -= $answerwidth;
            } else {
                $answerwidth = $answerwidth / 2;
            }
        } else {
            $right_exists = false;
        }
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($aQuestionAttributes['random_order'] == 1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        } else {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }

        $ansresult  = dbExecuteAssoc($ansquery); //Checked
        $aQuestions = $ansresult->readAll();
        $fn         = 1;

        $inputnames = array();

        $sRows = "";
        foreach ($aQuestions as $j => $ansrow) {
            $myfname        = $ia[1].$ansrow['title'];
            $answertext     = $ansrow['question'];
            $answertext     = (strpos($answertext, '|') !== false) ? substr($answertext, 0, strpos($answertext, '|')) : $answertext;
            $error          = (in_array($myfname, $aMandatoryViolationSubQ)) ?true:false; /* Check the mandatory sub Q violation */
            $value          = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';

            if ($right_exists && (strpos($ansrow['question'], '|') !== false)) {
                $answertextright = substr($ansrow['question'], strpos($ansrow['question'], '|') + 1);
            } else {
                $answertextright = null;
            }

            $options = array();

            /* Dropdown representation : first choice (activated) must be Please choose... if there are no actual answer */
            $showNoAnswer = $ia[6] != 'Y' && SHOW_NO_ANSWER == 1; // Tag if we must show no-answer
            if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] === '') {
                $options[] = array(
                    'text'=> gT('Please choose...'),
                    'value'=> '',
                    'selected'=>''
                );
                $showNoAnswer = false;
            }
            // Real options
            foreach ($labels as $i=>$lrow) {
                $options[] = array(
                    'value'=>$lrow['code'],
                    'selected'=>($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $lrow['code']) ? SELECTED :'',
                    'text'=> flattenText($lrow['answer'])
                );
            }
            /* Add the now answer if needed */
            if ($showNoAnswer) {
                $options[] = array(
                    'text'=> gT('No answer'),
                    'value'=> '',
                    'selected'=> ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] === '') ?  SELECTED :'',
                );
            }
            unset($showNoAnswer);
            $sRows .= doRender('/survey/questions/answer/arrays/array/dropdown/rows/answer_row', array(
                'myfname'                => $myfname,
                'answertext'             => $answertext,
                'answerwidth'=>$answerwidth,
                'value'                  => $value,
                'error'                  => $error,
                'checkconditionFunction' => $checkconditionFunction,
                'right_exists'           => $right_exists,
                'answertextright'        => $answertextright,
                'options'                => $options,
                'odd'                    => ($j % 2), // true for odd, false for even
                ), true);

            $inputnames[] = $myfname;
            $fn++;
        }

        $answer = doRender('/survey/questions/answer/arrays/array/dropdown/answer', array(
                'coreClass' => $coreClass,
                'basename' => $ia[1],
                'sRows'      => $sRows,
                'answerwidth'=> $answerwidth,
                'columnswidth'=> $columnswidth,
                'right_exists'=> $right_exists,
            ), true);
    } else {
        $answer = doRender('/survey/questions/answer/arrays/array/dropdown/empty', array(), true);
        $inputnames = '';
    }
    return array($answer, $inputnames);
}


function do_array_texts($ia)
{
    global $thissurvey;
    $aLastMoveResult            = LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ    = ($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|", $aLastMoveResult['unansweredSQs']) : array();
    $repeatheadings             = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings          = Yii::app()->getConfig("minrepeatheadings");
    $coreClass                  = "ls-answers subquestion-list questions-list text-array";
    $extraclass                 = "";
    $coreRowClass               = "subquestion-list questions-list";
    $caption                    = gT("A table of subquestions on each cell. The subquestion texts are in the column header and relate the particular row header.");

    if ($thissurvey['nokeyboard'] == 'Y') {
        includeKeypad();
        $kpclass = "text-keypad";
    } else {
        $kpclass = "";
    }

    $checkconditionFunction = "checkconditions";
    $sSeparator             = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeparator             = $sSeparator['separator'];
    $aQuestionAttributes    = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $show_grand             = $aQuestionAttributes['show_grand_total'];
    $totals_class           = '';
    $show_totals            = '';
    $col_total              = '';
    $row_total              = '';
    $col_head               = '';
    $row_head               = '';
    $grand_total            = '';
    $q_table_id             = '';
    $q_table_id_HTML        = '';
    $isNumber = intval($aQuestionAttributes['numbers_only'] == 1);
    $isInteger = 0;
    $inputnames             = array();

    if (ctype_digit(trim($aQuestionAttributes['repeat_headings'])) && trim($aQuestionAttributes['repeat_headings'] != "")) {
        $repeatheadings     = intval($aQuestionAttributes['repeat_headings']);
        $minrepeatheadings  = 0;
    }
    if (intval(trim($aQuestionAttributes['maximum_chars'])) > 0) {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maxlength = intval(trim($aQuestionAttributes['maximum_chars']));
        $extraclass .= " ls-input-maxchars";
    } else {
        $maxlength = "";
    }
    if (ctype_digit(trim($aQuestionAttributes['input_size']))) {
        $inputsize = trim($aQuestionAttributes['input_size']);
        $extraclass .= " ls-input-sized";
    } else {
        $inputsize = null;
    }
    if ($aQuestionAttributes['numbers_only'] == 1) {
        $checkconditionFunction = "fixnum_checkconditions";

        if (in_array($aQuestionAttributes['show_totals'], array("R", "C", "B"))) {
            $q_table_id      = 'totals_'.$ia[0];
            $q_table_id_HTML = ' id="'.$q_table_id.'"';
        }

        $coreClass .= " number-array";
        $coreRowClass .= " number-list";
        $caption    .= gT("Each answer may only be a number.");
        $col_head    = '';
        switch ($aQuestionAttributes['show_totals']) {

            case 'R':
                $totals_class   = $show_totals = 'row';
                $row_total      = doRender('/survey/questions/answer/arrays/texts/rows/cells/td_total', array('empty'=>false, 'inputsize'=>$inputsize), true);
                $col_head       = doRender('/survey/questions/answer/arrays/texts/rows/cells/thead', array('totalText'=>gT('Total'), 'classes'=>''), true);

                if ($show_grand == true) {
                    $row_head    = doRender('/survey/questions/answer/arrays/texts/rows/cells/thead', array('totalText'=>gT('Grand total'), 'classes'=>'answertext'), true);
                    $col_total   = doRender('/survey/questions/answer/arrays/texts/columns/col_total', array('empty'=>true, 'inputsize'=>$inputsize), true);
                    $grand_total = doRender('/survey/questions/answer/arrays/texts/rows/cells/td_grand_total', array('empty'=>false, 'inputsize'=>$inputsize), true);
                };

                $caption .= gT("The last row shows the total for the column.");
                break;

            case 'C':
                $totals_class = $show_totals = 'col';
                $col_total    = doRender('/survey/questions/answer/arrays/texts/columns/col_total', array('empty'=>false, 'inputsize'=>$inputsize, 'label'=>true), true);
                $row_head     = doRender('/survey/questions/answer/arrays/texts/rows/cells/thead', array('totalText'=>gT('Total'), 'classes'=>'answertext'), true);

                if ($show_grand == true) {
                    $row_total   = doRender('/survey/questions/answer/arrays/texts/rows/cells/td_total', array('empty'=>true, 'inputsize'=>$inputsize), true);
                    $col_head    = doRender('/survey/questions/answer/arrays/texts/rows/cells/thead', array('totalText'=>gT('Grand total'), 'classes'=>''), true);
                    $grand_total = doRender('/survey/questions/answer/arrays/texts/rows/cells/td_grand_total', array('empty'=>false, 'inputsize'=>$inputsize), true);
                };
                $caption .= gT("The last column shows the total for the row.");
                break;

            case 'B':
                $totals_class = $show_totals = 'both';
                $row_total    = doRender('/survey/questions/answer/arrays/texts/rows/cells/td_total', array('empty'=>false, 'inputsize'=>$inputsize), true);
                $col_total    = doRender('/survey/questions/answer/arrays/texts/columns/col_total', array('empty'=>false, 'inputsize'=>$inputsize, 'label'=>false), true);
                $col_head     = doRender('/survey/questions/answer/arrays/texts/rows/cells/thead', array('totalText'=>gT('Total'), 'classes'=>''), true);
                $row_head     = doRender('/survey/questions/answer/arrays/texts/rows/cells/thead', array('totalText'=>gT('Total'), 'classes'=>'answertext'), true);

                if ($show_grand == true) {
                    $grand_total = doRender('/survey/questions/answer/arrays/texts/rows/cells/td_grand_total', array('empty'=>false, 'inputsize'=>$inputsize), true);
                } else {
                    $grand_total = doRender('/survey/questions/answer/arrays/texts/rows/cells/td_grand_total', array('empty'=>true, 'inputsize'=>$inputsize), true);
                };

                $caption .= gT("The last row shows the total for the column and the last column shows the total for the row.");
                break;
        };

        if (!empty($totals_class)) {
            $totals_class = ' show-totals '.$totals_class;

            if ($aQuestionAttributes['show_grand_total']) {
                $totals_class  .= ' grand';
                $show_grand     = true;
            };
        };
    }

    if (ctype_digit(trim($aQuestionAttributes['answer_width']))) {
        $answerwidth = trim($aQuestionAttributes['answer_width']);
        $defaultWidth = false;
    } else {
        $answerwidth = 33;
        $defaultWidth = true;
    }
    $columnswidth = 100 - ($answerwidth);
    $lquery       = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]}  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=1 ORDER BY question_order";
    $lresult      = Yii::app()->db->createCommand($lquery)->query();
    $labelans     = array();
    $labelcode    = array();
    foreach ($lresult->readAll() as $lrow) {
        $labelans[]  = $lrow['question'];
        $labelcode[] = $lrow['title'];
    }

    if ($numrows = count($labelans)) {
        // There are no "No answer" column
        if (($show_grand == true && $show_totals == 'col') || $show_totals == 'row' || $show_totals == 'both') {
            ++$numrows;
        }

        $cellwidth = $columnswidth / $numrows;

        $ansquery  = "SELECT count(question) FROM {{questions}} WHERE parent_qid={$ia[0]} and scale_id=0 AND question like '%|%'";
        $ansresult = Yii::app()->db->createCommand($ansquery)->queryScalar(); //Checked

        if ($ansresult > 0) {
            $right_exists = true;
            if (!$defaultWidth) {
                $answerwidth = $answerwidth / 2;
            }
        } else {
            $right_exists = false;
        }

        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($aQuestionAttributes['random_order'] == 1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] and scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        } else {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] and scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }

        $ansresult  = dbExecuteAssoc($ansquery);
        $aQuestions = $ansresult->readAll();
        $anscount   = count($aQuestions);
        $fn         = 1;

        $showGrandTotal = (($show_grand == true && $show_totals == 'col') || $show_totals == 'row' || $show_totals == 'both') ?true:false;

        $sRows = '';
        $answertext = '';
        foreach ($aQuestions as $j => $ansrow) {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn - 1) > 0 && ($fn - 1) % $repeatheadings == 0) {
                if (($anscount - $fn + 1) >= $minrepeatheadings) {
                    // Close actual body and open another one
                    $sRows .= doRender('/survey/questions/answer/arrays/texts/rows/repeat_header', array(
                        'answerwidth'  => $answerwidth,
                        'labelans'     => $labelans,
                        'right_exists' => $right_exists,
                        'col_head'     => $col_head,
                        ), true);
                }
            }

            $myfname = $ia[1].$ansrow['title'];
            $answertext = $ansrow['question'];
            $answertextsave = $answertext;
            $error = false;

            if ($ia[6] == 'Y' && !empty($aMandatoryViolationSubQ)) {
                //Go through each labelcode and check for a missing answer! If any are found, highlight this line
                $emptyresult = 0;
                foreach ($labelcode as $ld) {
                    $myfname2 = $myfname.'_'.$ld;
                    if (in_array($myfname2, $aMandatoryViolationSubQ)) {
                        $emptyresult = 1;
                    }
                }
                $error = false;
                if ($emptyresult == 1) {
                    $error = true;
                }
            }
            $value = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';

            if (strpos($answertext, '|') !== false) {
                $answertext = (string) substr($answertext, 0, strpos($answertext, '|'));
            }

            $thiskey = 0;
            $answer_tds = '';

            foreach ($labelcode as $ld) {
                $myfname2 = $myfname."_$ld";
                $myfname2value = isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2] : "";

                if ($aQuestionAttributes['numbers_only'] == 1) {
                    $myfname2value = str_replace('.', $sSeparator, $myfname2value);
                }

                $inputnames[] = $myfname2;
                $value        = str_replace('"', "'", str_replace('\\', '', $myfname2value));
                $answer_tds  .= doRender('/survey/questions/answer/arrays/texts/rows/cells/answer_td', array(
                    'ld'         => $ld,
                    'myfname2'   => $myfname2,
                    'labelText'  => $labelans[$thiskey],
                    'kpclass'    => $kpclass,
                    'maxlength'  => $maxlength,
                    'inputsize'  => $inputsize,
                    'value'      => $myfname2value,
                    'isNumber'   => $isNumber,
                    'isInteger'  => $isInteger,
                    'error'      => ($error && $myfname2value === ''),
                    ), true);
                $thiskey += 1;
            }

            $rightTd = $rightTdEmpty = false;

            if (strpos($answertextsave, '|') !== false) {
                $answertext = (string) substr($answertextsave, strpos($answertextsave, '|') + 1);
                $rightTd    = true;
                $rightTdEmpty = false;
            } elseif ($right_exists) {
                $rightTd      = true;
                $rightTdEmpty = true;
            }
            $formatedRowTotal = str_replace(array('[[ROW_NAME]]', '[[INPUT_WIDTH]]'), array(strip_tags($answertext), $inputsize), $row_total);
            $sRows .= doRender('/survey/questions/answer/arrays/texts/rows/answer_row', array(
                'myfname'           =>  $myfname,
                'coreRowClass'      => $coreRowClass,
                'answertext'        =>  $answertext,
                'error'             =>  $error,
                'value'             =>  $value,
                'answer_tds'        =>  $answer_tds,
                'rightTd'           =>  $rightTd,
                'rightTdEmpty'      =>  $rightTdEmpty,
                'answerwidth'       =>  $answerwidth,
                'formatedRowTotal'  =>  $formatedRowTotal,
                'odd'               => ($j % 2),
                ), true);

            $fn++;
        }

        $showtotals = false;
        $total = '';

        if ($show_totals == 'col' || $show_totals == 'both' || $grand_total !== '') {
            $showtotals = true;

            $iLabelCodeCount = count($labelcode);
            for ($a = 0; $a < $iLabelCodeCount; ++$a) {
                $total .= str_replace(array('[[ROW_NAME]]', '[[INPUT_WIDTH]]'), array(strip_tags($answertext), $inputsize), $col_total);
            };
            $total .= str_replace(array('[[ROW_NAME]]', '[[INPUT_WIDTH]]'), array(strip_tags($answertext), $inputsize), $grand_total);
        }

        $radix = '';

        if (!empty($q_table_id)) {
            if ($aQuestionAttributes['numbers_only'] == 1) {
                $radix = $sSeparator;
            } else {
                $radix = 'X'; // to indicate that should not try to change entered values
            }
        }

        $answer = doRender('/survey/questions/answer/arrays/texts/answer', array(
            'answerwidth'               => $answerwidth,
            'col_head'                  => $col_head,
            'cellwidth'                 => $cellwidth,
            'labelans'                  => $labelans,
            'right_exists'              => $right_exists,
            'showGrandTotal'            => $showGrandTotal,
            'q_table_id_HTML'           => $q_table_id_HTML,
            'coreClass'                 => $coreClass,
            'basename' => $ia[1],
            'extraclass'                => $extraclass,
            'totals_class'              => $totals_class,
            'showtotals'                => $showtotals,
            'row_head'                  => $row_head,
            'total'                     => $total,
            'q_table_id'                => $q_table_id,
            'radix'                     => $radix,
            'name'                      => $ia[0],
            'sRows'                     => $sRows,
            'checkconditionFunction'    => $checkconditionFunction
            ), true);
    } else {
        $inputnames = '';
        $answer = doRender('/survey/questions/answer/arrays/texts/empty_error', array(), true);
    }
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
// Used by array numbers, array_numbers (for searching)
function do_array_multiflexi($ia)
{
    global $thissurvey;

    $inputnames                 = array();
    $aLastMoveResult            = LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ    = ($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|", $aLastMoveResult['unansweredSQs']) : array();
    $repeatheadings             = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings          = Yii::app()->getConfig("minrepeatheadings");
    $coreClass                  = "ls-answers subquestion-list questions-list";
    $coreRowClass = "subquestion-list questions-list";
    $extraclass                 = "";
    $answertypeclass            = "";
    $caption                    = gT("A table of subquestions on each cell. The subquestion texts are in the colum header and concern the row header.");
    $checkconditionFunction     = "fixnum_checkconditions";
    $minvalue                   = '';
    $maxvalue                   = '';

    /*
    * Question Attributes
    */
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

    // Define min and max value
    if (trim($aQuestionAttributes['multiflexible_max']) != '' && trim($aQuestionAttributes['multiflexible_min']) == '') {
        $maxvalue    = $aQuestionAttributes['multiflexible_max'];
        $minvalue    = 1;
        $extraclass .= " maxvalue maxvalue-".trim($aQuestionAttributes['multiflexible_max']); // @todo : move to data
    }

    if (trim($aQuestionAttributes['multiflexible_min']) != '' && trim($aQuestionAttributes['multiflexible_max']) == '') {
        $minvalue    = $aQuestionAttributes['multiflexible_min'];
        $maxvalue    = $aQuestionAttributes['multiflexible_min'] + 10;
        $extraclass .= " minvalue minvalue-".trim($aQuestionAttributes['multiflexible_max']); // @todo : move to data
    }

    if (trim($aQuestionAttributes['multiflexible_min']) == '' && trim($aQuestionAttributes['multiflexible_max']) == '') {
        $maxvalue   = 10;
        $minvalue   = (isset($minvalue['value']) && $minvalue['value'] == 0) ? 0 : 1;
    }

    if (trim($aQuestionAttributes['multiflexible_min']) != '' && trim($aQuestionAttributes['multiflexible_max']) != '') {
        if ($aQuestionAttributes['multiflexible_min'] < $aQuestionAttributes['multiflexible_max']) {
            $minvalue   = $aQuestionAttributes['multiflexible_min'];
            $maxvalue   = $aQuestionAttributes['multiflexible_max'];
        }
    }

    $stepvalue = (trim($aQuestionAttributes['multiflexible_step']) != '' && $aQuestionAttributes['multiflexible_step'] > 0) ? $aQuestionAttributes['multiflexible_step'] : 1;

    if ($aQuestionAttributes['reverse'] == 1) {
        $tmp        = $minvalue;
        $minvalue   = $maxvalue;
        $maxvalue   = $tmp;
        $reverse    = true;
        $stepvalue  = -$stepvalue;
    } else {
        $reverse    = false;
    }

    $checkboxlayout = false;
    $inputboxlayout = false;
    $textAlignment  = 'right';

    if ($aQuestionAttributes['multiflexible_checkbox'] != 0) {
        $layout = "checkbox";
        $minvalue            = 0;
        $maxvalue            = 1;
        $checkboxlayout      = true;
        $answertypeclass     = " checkbox-item";
        $coreClass          .= " checkbox-array";
        $coreRowClass .= " checkbox-list";
        $caption            .= gT("Please check the matching combinations.");
        $textAlignment       = 'center';
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."array-number-checkbox.js", CClientScript::POS_BEGIN);
        App()->getClientScript()->registerScript("doArrayNumberCheckbox", "doArrayNumberCheckbox();\n", LSYii_ClientScript::POS_POSTSCRIPT);
    } elseif ($aQuestionAttributes['input_boxes'] != 0) {
        $layout = "text";
        $inputboxlayout      = true;
        $answertypeclass    .= " numeric-item text-item";
        $coreClass          .= " text-array number-array";
        $coreRowClass .= " text-list number-list";
        $extraclass         .= " numberonly";
        $caption            .= gT("Please enter only numbers.");
    } else {
        $layout = "dropdown";
        $answertypeclass     = " dropdown-item";
        $coreClass          .= " dropdown-array";
        $coreRowClass .= " dropdown-list";
        $caption            .= gT("Please select an answer for each combination.");
    }

    if (ctype_digit(trim($aQuestionAttributes['repeat_headings'])) && trim($aQuestionAttributes['repeat_headings'] != "")) {
        $repeatheadings     = intval($aQuestionAttributes['repeat_headings']);
        $minrepeatheadings  = 0;
    }

    if (intval(trim($aQuestionAttributes['maximum_chars'])) > 0) {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maxlength = intval(trim($aQuestionAttributes['maximum_chars']));
        $extraclass .= " ls-input-maxchars"; // @todo : move to data or fix class
    } else {
        $maxlength = "";
    }
    if (ctype_digit(trim($aQuestionAttributes['input_size']))) {
        $inputsize = trim($aQuestionAttributes['input_size']);
        $extraclass .= " ls-input-sized";
    } else {
        $inputsize = null;
    }

    if ($thissurvey['nokeyboard'] == 'Y') {
        includeKeypad();
        $kpclass     = " num-keypad";
        $extraclass .= " inputkeypad";
    } else {
        $kpclass = "";
    }

    if (ctype_digit(trim($aQuestionAttributes['answer_width']))) {
        $answerwidth = trim($aQuestionAttributes['answer_width']);
        $defaultWidth = false;
    } else {
        $answerwidth = 33;
        $defaultWidth = true;
    }

    $columnswidth   = 100 - ($answerwidth);
    $lquery         = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]}  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=1 ORDER BY question_order";
    $lresult        = dbExecuteAssoc($lquery);
    $aQuestions     = $lresult->readAll();
    $labelans       = array();
    $labelcode      = array();

    foreach ($aQuestions as $lrow) {
        $labelans[]  = $lrow['question'];
        $labelcode[] = $lrow['title'];
    }

    if ($numrows = count($labelans)) {
        // There are no "No answer" column
        $cellwidth  = $columnswidth / $numrows;

        $sQuery     = "SELECT count(question) FROM {{questions}} WHERE parent_qid=".$ia[0]." AND scale_id=0 AND question like '%|%'";
        $iCount     = Yii::app()->db->createCommand($sQuery)->queryScalar();

        if ($iCount > 0) {
            $right_exists = true;
            if (!$defaultWidth) {
                $answerwidth = $answerwidth / 2;
            }
        } else {
            $right_exists = false;
        }

        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($aQuestionAttributes['random_order'] == 1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        } else {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }

        $ansresult = dbExecuteAssoc($ansquery)->readAll(); //Checked

        if (trim($aQuestionAttributes['parent_order'] != '')) {
            $iParentQID = (int) $aQuestionAttributes['parent_order'];
            $aResult    = array();
            $sessionao  = isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['answer_order']) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['answer_order'] : array();

            if (isset($sessionao[$iParentQID])) {
                foreach ($sessionao[$iParentQID] as $aOrigRow) {
                    $sCode = $aOrigRow['title'];

                    foreach ($ansresult as $aRow) {
                        if ($sCode == $aRow['title']) {
                            $aResult[] = $aRow;
                        }
                    }
                }
                $ansresult = $aResult;
            }
        }
        $anscount = count($ansresult);
        $fn = 1;

        $sAnswerRows = '';
        foreach ($ansresult as $j => $ansrow) {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn - 1) > 0 && ($fn - 1) % $repeatheadings == 0) {
                if (($anscount - $fn + 1) >= $minrepeatheadings) {
                    $sAnswerRows .= doRender('/survey/questions/answer/arrays/multiflexi/rows/repeat_header', array(
                        'labelans'      =>  $labelans,
                        'right_exists'  =>  $right_exists,
                        'cellwidth'     =>  $cellwidth,
                        'answerwidth'   =>  $answerwidth,
                        'textAlignment' => $textAlignment,
                        ), true);
                }
            }

            $myfname        = $ia[1].$ansrow['title'];
            $answertext     = $ansrow['question'];
            $answertextsave = $answertext;

            /* Check the sub Q mandatory violation */
            $error = false;

            if ($ia[6] == 'Y' && !empty($aMandatoryViolationSubQ)) {
                //Go through each labelcode and check for a missing answer! Default :If any are found, highlight this line, checkbox : if one is not found : don't highlight
                // PS : we really need a better system : event for EM !
                $emptyresult = ($aQuestionAttributes['multiflexible_checkbox'] != 0) ? 1 : 0;

                foreach ($labelcode as $ld) {
                    $myfname2 = $myfname.'_'.$ld;
                    if ($aQuestionAttributes['multiflexible_checkbox'] != 0) {
                        if (!in_array($myfname2, $aMandatoryViolationSubQ)) {
                            $emptyresult = 0;
                        }
                    } else {
                        if (in_array($myfname2, $aMandatoryViolationSubQ)) {
                            $emptyresult = 1;
                        }
                    }
                }

                $error = ($emptyresult == 1) ?true:false;
            }

            $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
            $sSeparator = $sSeparator['separator'];

            // Get array_filter stuff
            $sDisplayStyle = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);


            if (strpos($answertext, '|') !== false) {
                $answertext = (string) substr($answertext, 0, strpos($answertext, '|'));
            }

            $row_value = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] : '';

            $thiskey            = 0;
            $answer_tds         = '';

            foreach ($labelcode as $i => $ld) {
                $myfname2   = $myfname."_$ld";
                $value      = (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2])) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2] : '';

                // Possibly replace '.' with ','
                $surveyId = Yii::app()->getConfig('surveyID');
                $surveyLabel = 'survey_'.$surveyId;
                $fieldnameIsNumeric = isset($_SESSION[$surveyLabel][$myfname2])
                && is_numeric($_SESSION[$surveyLabel][$myfname2]);
                if ($fieldnameIsNumeric) {
                    $value = str_replace('.', $sSeparator, $_SESSION[$surveyLabel][$myfname2]);
                }

                if ($checkboxlayout === false) {
                    $answer_tds .= doRender('/survey/questions/answer/arrays/multiflexi/rows/cells/answer_td', array(
                        'dataTitle'                 => $labelans[$i],
                        'ld'                        => $ld,
                        'answertypeclass'           => $answertypeclass,
                        'answertext'                => $answertext,
                        'stepvalue'                 => $stepvalue,
                        'extraclass'                => $extraclass,
                        'myfname2'                  => $myfname2,
                        'error'                     => $error,
                        'inputboxlayout'            => $inputboxlayout,
                        'checkconditionFunction'    => $checkconditionFunction,
                        'minvalue'                  => $minvalue,
                        'maxvalue'                  => $maxvalue,
                        'reverse'                   => $reverse,
                        'value'                     => $value,
                        'sSeparator'                => $sSeparator,
                        'kpclass'                   => $kpclass,
                        'maxlength'                 => $maxlength,
                        'inputsize'                 => $inputsize,
                        'error'                     => ($error && $value === '')
                        ), true);


                    $inputnames[] = $myfname2;
                    $thiskey++;
                } else {
                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2] == '1') {
                        $myvalue    = '1';
                        $setmyvalue = CHECKED;
                    } else {
                        $myvalue    = '';
                        $setmyvalue = '';
                    }

                    $answer_tds .= doRender('/survey/questions/answer/arrays/multiflexi/rows/cells/answer_td_checkboxes', array(
                        'dataTitle'                 => $labelans[$i],
                        'ld'                        => $ld,
                        'answertypeclass'           => $answertypeclass,
                        'value'                     => $myvalue,
                        'setmyvalue'                => $setmyvalue,
                        'myfname2'                  => $myfname2,
                        'checkconditionFunction'    => $checkconditionFunction,
                        'extraclass'                => $extraclass,
                        ), true);
                    $inputnames[] = $myfname2;
                    $thiskey++;
                }
            }

            $rightTd = false;
            $answertextright = '';

            if (strpos($answertextsave, '|')) {
                $answertextright    = substr($answertextsave, strpos($answertextsave, '|') + 1);
                $rightTd            = true;
            } elseif ($right_exists) {
                $rightTd = true;
            }

            $sAnswerRows .= doRender('/survey/questions/answer/arrays/multiflexi/rows/answer_row', array(
                'sDisplayStyle'     => $sDisplayStyle,
                'coreRowClass'      => $coreRowClass,
                'answerwidth'       => $answerwidth,
                'myfname'           => $myfname,
                'error'             => $error,
                'row_value'         => $row_value,
                'answertext'        => $answertext,
                'answertextright'   => $answertextright,
                'answer_tds'        => $answer_tds,
                'rightTd'           => $rightTd,
                'odd'               => ($j % 2),
                'layout'            => $layout
                ), true);
            $fn++;
        }

        $answer = doRender('/survey/questions/answer/arrays/multiflexi/answer', array(
            'answertypeclass'   => $answertypeclass,
            'coreClass'         => $coreClass,
            'basename' => $ia[1],
            'extraclass'        => $extraclass,
            'answerwidth'       => $answerwidth,
            'labelans'          => $labelans,
            'cellwidth'         => $cellwidth,
            'right_exists'      => $right_exists,
            'sAnswerRows'       => $sAnswerRows,
            'textAlignment'     => $textAlignment,
            ), true);
    } else {
        $answer     = doRender('/survey/questions/answer/arrays/multiflexi/empty_error', array(), true);
        $inputnames = '';
    }
    return array($answer, $inputnames);
}


// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_arraycolumns($ia)
{
    $aLastMoveResult = LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|", $aLastMoveResult['unansweredSQs']) : array();
    $coreClass = "ls-answers subquestion-list questions-list array-radio";
    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

    $lquery = "SELECT * FROM {{answers}} WHERE qid=".$ia[0]."  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY sortorder, code";
    $oAnswers = dbExecuteAssoc($lquery);
    $aAnswers = $oAnswers->readAll();
    $labelans = array();
    $labelcode = array();
    $labels = array();

    foreach ($aAnswers as $lrow) {
        $labelans[] = $lrow['answer'];
        $labelcode[] = $lrow['code'];
        $labels[] = array("answer"=>$lrow['answer'], "code"=>$lrow['code']);
    }

    $inputnames = array();
    if (count($labelans) > 0) {
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {
            $labelcode[] = '';
            $labelans[] = gT('No answer');
            $labels[] = array('answer'=>gT('No answer'), 'code'=>'');
        }
        if ($aQuestionAttributes['random_order'] == 1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        } else {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }
        $ansresult = dbExecuteAssoc($ansquery); //Checked
        $aQuestions = $ansresult->readAll();
        $anscount = count($aQuestions);

        $aData = array();
        $aData['labelans'] = $labelans;
        $aData['labelcode'] = $labelcode;

        if ($anscount > 0) {
            if (ctype_digit(trim($aQuestionAttributes['answer_width_bycolumn']))) {
                $answerwidth = trim($aQuestionAttributes['answer_width_bycolumn']);
            } else {
                $answerwidth = 33;
            }
            $cellwidth = (100 - $answerwidth) / $anscount;

            $aData['anscount'] = $anscount;
            $aData['cellwidth'] = $cellwidth;
            $aData['answerwidth'] = $answerwidth;
            $aData['aQuestions'] = $aQuestions;

            $anscode = [];
            $answers = [];
            foreach ($aQuestions as $ansrow) {
                $anscode[] = $ansrow['title'];
                $answers[] = $ansrow['question'];
            }

            $aData['anscode'] = $anscode;
            $aData['answers'] = $answers;

            $iAnswerCount = count($answers);
            for ($_i = 0; $_i < $iAnswerCount; ++$_i) {
                $myfname = $ia[1].$anscode[$_i];
                /* Check the Sub Q mandatory violation */
                if ($ia[6] == 'Y' && in_array($myfname, $aMandatoryViolationSubQ)) {
                    $aData['aQuestions'][$_i]['errormandatory'] = true;
                } else {
                    $aData['aQuestions'][$_i]['errormandatory'] = false;
                }
            }

            $aData['labels'] = $labels;
            $aData['checkconditionFunction'] = $checkconditionFunction;

            foreach ($labels as $ansrow) {
                foreach ($anscode as $j => $ld) {
                    $myfname = $ia[1].$ld;
                    $aData['aQuestions'][$j]['myfname'] = $myfname;
                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] === $ansrow['code']) {
                        $aData['checked'][$ansrow['code']][$ld] = CHECKED;
                    } elseif (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $ansrow['code'] == '') {
                        $aData['checked'][$ansrow['code']][$ld] = CHECKED;
                    // Humm.. (by lemeur), not sure this section can be reached
                        // because I think $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] is always set (by save.php ??) !
                        // should remove the !isset part I think !!
                    } else {
                        $aData['checked'][$ansrow['code']][$ld] = "";
                    }
                }
            }

            foreach ($anscode as $j => $ld) {
                $myfname = $ia[1].$ld;
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {
                    $aData['aQuestions'][$j]['myfname_value'] = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                } else {
                    $aData['aQuestions'][$j]['myfname_value'] = '';
                }

                $inputnames[] = $myfname;
            }
            $aData['coreClass'] = $coreClass;
            $aData['basename'] = $ia[1];
            // Render question
            $answer = doRender(
                '/survey/questions/answer/arrays/column/answer',
                $aData,
                true
            );
        } else {
            $answer = '<p class="error">'.gT('Error: There are no answers defined for this question.')."</p>";
            $inputnames = "";
        }
    } else {
        $answer = "<p class='error'>".gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames = '';
    }
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_array_dual($ia)
{
    global $thissurvey;
    $aLastMoveResult            = LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ    = ($aLastMoveResult['mandViolation'] && $ia[6] == 'Y') ? explode("|", $aLastMoveResult['unansweredSQs']) : array();
    $repeatheadings             = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings          = Yii::app()->getConfig("minrepeatheadings");
    $coreClass                  = "ls-answers subquestion-list questions-list";
    $answertypeclass            = ""; // Maybe not
    $inputnames                 = array();

    /*
    * Get Question Attributes
    */
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

    // Get questions and answers by defined order
    if ($aQuestionAttributes['random_order'] == 1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
    } else {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY question_order";
    }

    $ansresult      = dbExecuteAssoc($ansquery); //Checked
    $aSubQuestions  = $ansresult->readAll();
    $anscount       = count($aSubQuestions);
    $lquery         = "SELECT * FROM {{answers}} WHERE scale_id=0 AND qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY sortorder, code";
    $lresult        = dbExecuteAssoc($lquery); //Checked
    $aAnswersScale0 = $lresult->readAll();
    $lquery1        = "SELECT * FROM {{answers}} WHERE scale_id=1 AND qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY sortorder, code";
    $lresult1       = dbExecuteAssoc($lquery1); //Checked
    $aAnswersScale1 = $lresult1->readAll();

    // Set attributes
    if ($aQuestionAttributes['use_dropdown'] == 1) {
        $useDropdownLayout = true;
        $coreClass .= " dropdown-array";
        $answertypeclass .= " dropdown";
        $doDualScaleFunction = "doDualScaleDropDown"; // javascript funtion to lauch at end of answers
    } else {
        $useDropdownLayout = false;
        $coreClass .= " radio-array";
        $answertypeclass .= " radio";
        $doDualScaleFunction = "doDualScaleRadio";
    }
    if (ctype_digit(trim($aQuestionAttributes['repeat_headings'])) && trim($aQuestionAttributes['repeat_headings'] != "")) {
        $repeatheadings = intval($aQuestionAttributes['repeat_headings']);
        $minrepeatheadings = 0;
    }

    $leftheader     = (trim($aQuestionAttributes['dualscale_headerA'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '') ? $aQuestionAttributes['dualscale_headerA'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']] : '';
    $rightheader    = (trim($aQuestionAttributes['dualscale_headerB'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '') ? $aQuestionAttributes['dualscale_headerB'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']] : '';
    if (ctype_digit(trim($aQuestionAttributes['answer_width']))) {
        $answerwidth = trim($aQuestionAttributes['answer_width']);
        $defaultWidth = false;
    } else {
        $answerwidth = 33;
        $defaultWidth = true;
    }
    // Find if we have rigth and center text
    /* All of this part seem broken actually : we don't send it to view and don't explode it */
    $sQuery         = "SELECT count(question) FROM {{questions}} WHERE parent_qid=".$ia[0]." and scale_id=0 AND question like '%|%'";
    $rigthCount     = Yii::app()->db->createCommand($sQuery)->queryScalar();
    $rightexists    = ($rigthCount > 0); // $right_exists: flag to find out if there are any right hand answer parts. leaving right column but don't force with
    $sQuery         = "SELECT count(question) FROM {{questions}} WHERE parent_qid=".$ia[0]." and scale_id=0 AND question like '%|%|%'";
    $centerCount    = Yii::app()->db->createCommand($sQuery)->queryScalar();
    $centerexists   = ($centerCount > 0); // $center_exists: flag to find out if there are any center hand answer parts. leaving center column but don't force with
    /* Then always set to false : see bug https://bugs.limesurvey.org/view.php?id=11750 */
    //~ $rightexists=false;
    //~ $centerexists=false;
    // Label and code for input
    $labels0 = [];
    $labels1 = [];
    foreach ($aAnswersScale0 as $lrow) {
        $labels0[] = array('code' => $lrow['code'],
            'title' => $lrow['answer']);
    }
    foreach ($aAnswersScale1 as $lrow) {
        $labels1[] = array('code' => $lrow['code'],
            'title' => $lrow['answer']);
    }
    if (count($aAnswersScale0) > 0 && $anscount) {
        $answer = "";
        $fn = 1; // Used by repeat_heading

        // No drop-down
        if ($useDropdownLayout === false) {
            $aData = array();
            $aData['coreClass'] = $coreClass;
            $aData['basename'] = $ia[1];
            $aData['answertypeclass'] = $answertypeclass;

            $columnswidth = 100 - $answerwidth;
            $labelans0 = array();
            $labelans1 = array();
            $labelcode0 = array();
            $labelcode1 = array();
            foreach ($aAnswersScale0 as $lrow) {
                $labelans0[] = $lrow['answer'];
                $labelcode0[] = $lrow['code'];
            }
            foreach ($aAnswersScale1 as $lrow) {
                $labelans1[] = $lrow['answer'];
                $labelcode1[] = $lrow['code'];
            }
            $numrows = count($labelans0) + count($labelans1);
            // Add needed row and fill some boolean: shownoanswer, rightexists, centerexists
            $shownoanswer = ($ia[6] != "Y" && SHOW_NO_ANSWER == 1);
            if ($shownoanswer) {
                $numrows++;
            }
            /* right and center come from answer => go to answer part*/
            $numColExtraAnswer = 0;
            $rightwidth = 0;
            $separatorwidth = 4;
            if ($rightexists) {
                $numColExtraAnswer++;
            } elseif ($shownoanswer) {
                $columnswidth -= 4;
                $rightwidth = 4;
            }
            if ($centerexists) {
                $numColExtraAnswer++;
            } else {
                $columnswidth -= 4;
            }
            if ($numColExtraAnswer > 0) {
                $extraanswerwidth = $answerwidth / $numColExtraAnswer; /* If there are 2 separator : set to 1/2 else to same */
                if ($defaultWidth) {
                    $columnswidth -= $answerwidth;
                } else {
                    $answerwidth  = $answerwidth / 2;
                }
            } else {
                $extraanswerwidth = $separatorwidth;
            }
            $cellwidth = $columnswidth / $numrows;

            // Header row and colgroups
            $aData['answerwidth'] = $answerwidth;
            $aData['cellwidth'] = $cellwidth;
            $aData['labelans0'] = $labelans0;
            $aData['labelcode0'] = $labelcode0;
            $aData['labelans1'] = $labelans1;
            $aData['labelcode1'] = $labelcode1;
            $aData['separatorwidth'] = $centerexists ? $extraanswerwidth : $separatorwidth;
            $aData['shownoanswer'] = $shownoanswer;
            $aData['rightexists'] = $rightexists;
            $aData['rightwidth'] = $rightexists ? $extraanswerwidth : $rightwidth;

            // build first row of header if needed
            $aData['leftheader'] = $leftheader;
            $aData['rightheader'] = $rightheader;
            $aData['rightclass'] = ($rightexists) ? " header_answer_text_right" : "";

            // And no each line of body
            $trbc = '';
            $aData['aSubQuestions'] = $aSubQuestions;
            foreach ($aSubQuestions as $i => $ansrow) {

                // Build repeat headings if needed
                if (isset($repeatheadings) && $repeatheadings > 0 && ($fn - 1) > 0 && ($fn - 1) % $repeatheadings == 0) {
                    if (($anscount - $fn + 1) >= $minrepeatheadings) {
                        $aData['aSubQuestions'][$i]['repeatheadings'] = true;
                    }
                } else {
                    $aData['aSubQuestions'][$i]['repeatheadings'] = false;
                }

                $trbc = alternation($trbc, 'row');
                $answertext = $ansrow['question'];

                // right and center answertext: not explode for ? Why not
                if (strpos($answertext, '|') !== false) {
                    $answertextright = (string) substr($answertext, strpos($answertext, '|') + 1);
                    $answertext = (string) substr($answertext, 0, strpos($answertext, '|'));
                } else {
                    $answertextright = "";
                }
                if (strpos($answertextright, '|')) {
                    $answertextcenter = (string) substr($answertextright, 0, strpos($answertextright, '|'));
                    $answertextright = (string) substr($answertextright, strpos($answertextright, '|') + 1);
                } else {
                    $answertextcenter = "";
                }

                $myfname = $ia[1].$ansrow['title'];
                $myfname0 = $ia[1].$ansrow['title'].'#0';
                $myfid0 = $ia[1].$ansrow['title'].'_0';
                $myfname1 = $ia[1].$ansrow['title'].'#1'; // new multi-scale-answer
                $myfid1 = $ia[1].$ansrow['title'].'_1';
                $aData['aSubQuestions'][$i]['myfname'] = $myfname;
                $aData['aSubQuestions'][$i]['myfname0'] = $myfname0;
                $aData['aSubQuestions'][$i]['myfid0'] = $myfid0;
                $aData['aSubQuestions'][$i]['myfname1'] = $myfname1;
                $aData['aSubQuestions'][$i]['myfid1'] = $myfid1;

                $aData['aSubQuestions'][$i]['answertext'] = $answertext;
                $aData['aSubQuestions'][$i]['answertextcenter'] = $answertextcenter;
                $aData['aSubQuestions'][$i]['answertextright'] = $answertextright;

                $aData['aSubQuestions'][$i]['odd'] = ($i % 2);
                // Check the Sub Q mandatory violation
                if ($ia[6] == 'Y' && (in_array($myfname0, $aMandatoryViolationSubQ) || in_array($myfname1, $aMandatoryViolationSubQ))) {
                    $aData['aSubQuestions'][$i]['showmandatoryviolation'] = true;
                } else {
                    $aData['aSubQuestions'][$i]['showmandatoryviolation'] = false;
                }

                // Get array_filter stuff
                $aData['aSubQuestions'][$i]['sDisplayStyle'] = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);
                array_push($inputnames, $myfname0);

                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0])) {
                    $aData['aSubQuestions'][$i]['sessionfname0'] = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0];
                } else {
                    $aData['aSubQuestions'][$i]['sessionfname0'] = '';
                }

                if (count($labelans1) > 0) {
                    // if second label set is used
                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1])) {
                        //$answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1];
                        $aData['aSubQuestions'][$i]['sessionfname1'] = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1];
                    } else {
                        $aData['aSubQuestions'][$i]['sessionfname1'] = '';
                    }
                }

                foreach ($labelcode0 as $j => $ld) {
                    // First label set
                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0] == $ld) {
                        $aData['labelcode0_checked'][$ansrow['title']][$ld] = CHECKED;
                    } else {
                        $aData['labelcode0_checked'][$ansrow['title']][$ld] = "";
                    }
                }

                if (count($labelans1) > 0) {
                    // if second label set is used
                    if ($shownoanswer) {
                        // No answer for accessibility and no javascript (but hide hide even with no js: need reworking)
                        if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0] == "") {
                            $answer .= CHECKED;
                            $aData['myfname0_notset'] = CHECKED;
                        } else {
                            $aData['myfname0_notset'] = "";
                        }
                    }

                    array_push($inputnames, $myfname1);

                    foreach ($labelcode1 as $j => $ld) {
                        // second label set
                        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1] == $ld) {
                            $aData['labelcode1_checked'][$ansrow['title']][$ld] = CHECKED;
                        } else {
                            $aData['labelcode1_checked'][$ansrow['title']][$ld] = "";
                        }
                    }
                }
                $aData['answertextright'] = $answertextright;
                if ($shownoanswer) {
                    if (count($labelans1) > 0) {
                        if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1] == "") {
                            $answer .= CHECKED;
                            $aData['myfname1_notset'] = CHECKED;
                        } else {
                            $aData['myfname1_notset'] = "";
                        }
                    } else {
                        if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0] == "") {
                            $answer .= CHECKED;
                            $aData['myfname0_notset'] = CHECKED;
                        } else {
                            $aData['myfname0_notset'] = '';
                        }
                    }
                }
                $fn++;
            }

            $answer = doRender(
                '/survey/questions/answer/arrays/dualscale/answer',
                $aData,
                true
            );
        }

        // Dropdown Layout
        elseif ($useDropdownLayout === true) {
            $aData = array();
            $aData['coreClass'] = $coreClass;
            $aData['basename'] = $ia[1];

            // Get attributes for Headers and Prefix/Suffix
            if (trim($aQuestionAttributes['dropdown_prepostfix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
                list($ddprefix, $ddsuffix) = explode("|", $aQuestionAttributes['dropdown_prepostfix'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]);
            } else {
                $ddprefix = null;
                $ddsuffix = null;
            }
            if (trim($aQuestionAttributes['dropdown_separators']) != '') {
                $aSeparator = explode('|', $aQuestionAttributes['dropdown_separators']);
                if (isset($aSeparator[1])) {
                    $interddSep = $aSeparator[1];
                } else {
                    $interddSep = $aSeparator[0];
                }
            } else {
                $interddSep = '';
            }
            if ($interddSep) {
                $separatorwidth = 8;
            } else {
                $separatorwidth = 4;
            }
            $cellwidth = (100 - $answerwidth - $separatorwidth) / 2;
            $aData['answerwidth'] = $answerwidth;
            $aData['ddprefix'] = $ddprefix;
            $aData['ddsuffix'] = $ddsuffix;
            $aData['cellwidth'] = $cellwidth;

            $aData['separatorwidth'] = $separatorwidth;

            $aData['leftheader'] = $leftheader;
            $aData['rightheader'] = $rightheader;

            $aData['aSubQuestions'] = $aSubQuestions;
            foreach ($aSubQuestions as $i => $ansrow) {
                $myfname = $ia[1].$ansrow['title'];
                $myfname0 = $ia[1].$ansrow['title']."#0";
                $myfid0 = $ia[1].$ansrow['title']."_0";
                $myfname1 = $ia[1].$ansrow['title']."#1";
                $myfid1 = $ia[1].$ansrow['title']."_1";
                $sActualAnswer0 = isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0]) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0] : "";
                $sActualAnswer1 = isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1]) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1] : "";

                $aData['aSubQuestions'][$i]['myfname'] = $myfname;
                $aData['aSubQuestions'][$i]['myfname0'] = $myfname0;
                $aData['aSubQuestions'][$i]['myfid0'] = $myfid0;
                $aData['aSubQuestions'][$i]['myfname1'] = $myfname1;
                $aData['aSubQuestions'][$i]['myfid1'] = $myfid1;
                $aData['aSubQuestions'][$i]['sActualAnswer0'] = $sActualAnswer0;
                $aData['aSubQuestions'][$i]['sActualAnswer1'] = $sActualAnswer1;
                $aData['aSubQuestions'][$i]['odd'] = ($i % 2);
                // Set mandatory alert
                $aData['aSubQuestions'][$i]['alert'] = ($ia[6] == 'Y' && (in_array($myfname0, $aMandatoryViolationSubQ) || in_array($myfname1, $aMandatoryViolationSubQ)));
                $aData['aSubQuestions'][$i]['mandatoryviolation'] = ($ia[6] == 'Y' && (in_array($myfname0, $aMandatoryViolationSubQ) || in_array($myfname1, $aMandatoryViolationSubQ)));
                // Array filter : maybe leave EM do the trick
                $aData['aSubQuestions'][$i]['sDisplayStyle'] = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);

                //~ list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc subquestion-list questions-list dropdown-list");
                //~ $aData['aSubQuestions'][$i]['htmltbody2'] = $htmltbody2;
                //~ $aData['aSubQuestions'][$i]['hiddenfield'] = $hiddenfield;
                $aData['labels0'] = $labels0;
                $aData['labels1'] = $labels1;
                $aData['aSubQuestions'][$i]['showNoAnswer0'] = ($sActualAnswer0 != '' && $ia[6] != 'Y' && SHOW_NO_ANSWER);
                $aData['aSubQuestions'][$i]['showNoAnswer1'] = ($sActualAnswer1 != '' && $ia[6] != 'Y' && SHOW_NO_ANSWER);
                $aData['interddSep'] = $interddSep;

                $inputnames[] = $myfname0;

                $inputnames[] = $myfname1;
            }

            $answer = doRender(
                '/survey/questions/answer/arrays/dualscale/answer_dropdown',
                $aData,
                true
            );
        }
    } else {
        $answer = "<p class='error'>".gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames = "";
    }
    if (!Yii::app()->getClientScript()->isScriptFileRegistered(Yii::app()->getConfig('generalscripts')."dualscale.js", LSYii_ClientScript::POS_BEGIN)) {
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."dualscale.js", LSYii_ClientScript::POS_BEGIN);
    }
    Yii::app()->getClientScript()->registerScript('doDualScaleFunction'.$ia[0], "{$doDualScaleFunction}({$ia[0]});", LSYii_ClientScript::POS_POSTSCRIPT);
    return array($answer, $inputnames);
}

/**
* Find the label / input width
* @param string|int $labelAttributeWidth label width from attribute
* @param string|int $inputAttributeWidth input width from attribute
* @return array labelWidth as integer,inputWidth as integer,defaultWidth as boolean
*/
function getLabelInputWidth($labelAttributeWidth, $inputAttributeWidth)
{
    $attributeInputContainerWidth = intval(trim($inputAttributeWidth));
    if ($attributeInputContainerWidth < 1 || $attributeInputContainerWidth > 12) {
        $attributeInputContainerWidth = null;
    }

    $attributeLabelWidth = trim($labelAttributeWidth);
    if ($attributeLabelWidth === 'hidden') {
        $attributeLabelWidth = 0;
    } else {
        $attributeLabelWidth = intval($attributeLabelWidth);
        if ($attributeLabelWidth < 1 || $attributeLabelWidth > 12) {
            /* old system or imported or '' */
            $attributeLabelWidth = null;
        }
    }
    if ($attributeInputContainerWidth === null && $attributeLabelWidth === null) {
        $sInputContainerWidth = 8;
        $sLabelWidth = 4;
        $defaultWidth = true;
    } else {
        if ($attributeInputContainerWidth !== null) {
            $sInputContainerWidth = $attributeInputContainerWidth;
        } elseif ($attributeLabelWidth == 12) {
            $sInputContainerWidth = 12;
        } else {
            $sInputContainerWidth = 12 - $attributeLabelWidth;
        }
        if (!is_null($attributeLabelWidth)) {
            $sLabelWidth = $attributeLabelWidth;
        } elseif ($attributeInputContainerWidth == 12) {
            $sLabelWidth = 12;
        } else {
            $sLabelWidth = 12 - $attributeInputContainerWidth;
        }
        $defaultWidth = false;
    }
    return array(
        $sLabelWidth,
        $sInputContainerWidth,
        $defaultWidth,
    );
}

/**
* Take a date string and fill out missing parts, like day, hour, minutes
* (not seconds).
* If string is NOT in standard date format (Y-m-d H:i), this methods makes no
* sense.
* Used when fetching answer for do_date, where answer can come from a default
* answer expression like date('Y').
* Will also truncate date('c') to format Y-m-d H:i.
* @param string $dateString
* @return string
*/
function fillDate($dateString)
{
    switch (strlen($dateString)) {
        // Only year
        case 4:
            return $dateString.'-01-01 00:00';
            // Year and month
        case 7:
            return $dateString.'-01 00:00';
            // Year, month and day
        case 10:
            return $dateString.' 00:00';
            // Year, month day and hour
        case 13:
            return $dateString.':00';
            // Complete, return as is.
        case 16:
            return $dateString;
        case 19:
        case 21: // Y-m-d H:i.s.n (n==1)
        case 22: // Y-m-d H:i.s.n (n==2)
        case 23: // mssql Y-m-d H:i.s.n (n==3)
        case 24: // Y-m-d H:i.s.n (n==4)
        case 25: // Assume date('c')
            $date = new DateTime($dateString);
            if ($date) {
                return $date->format('Y-m-d H:i');
            }
            // no break
        default:
            return '';
    }
}

/**
* Render the question view.
*
* By default, it just renders the required core view from application/views/survey/...
* If user added a question template in the upload dirctory, add applied it to the question in its display settings, then the function will check if the required view exist in this directory
* and then will use this one to render the question.
*
* Rem: all the logic has been moved to LSETwigViewRenderer::renderQuestion()
* We keep the function doRender here for convenience (it will probably be removed in further cycles of dev).
**
* @param string    $sView      name of the view to be rendered.
* @param array     $aData      data to be extracted into PHP variables and made available to the view script
* @param boolean   $bReturn    whether the rendering result should be returned instead of being displayed to end users (should be always true)
*/
function doRender($sView, $aData, $bReturn = true)
{
    return App()->twigRenderer->renderQuestion($sView, $aData);
}
