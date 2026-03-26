<?php

/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
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
 * @TODO: most functions here don't seem to be used anymore
* Let's explain what this strange $ia var means
*
* The $ia string comes from the $_SESSION['responses_'.Yii::app()->getConfig('surveyID')]['insertarray'] variable which is built at the commencement of the survey.
* See index.php, function "buildsurveysession()"
* One $ia array zexists for every question in the survey. The $_SESSION['responses_'.Yii::app()->getConfig('surveyID')]['insertarray']
* string is an array of $ia arrays.
*
* $ia[0] => question id
* $ia[1] => fieldname
* $ia[2] => title
* $ia[3] => question text
* $ia[4] => type --  text, radio, select, array, etc
* $ia[5] => group id
* $ia[6] => mandatory Y || S || N
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
    if (App()->getConfig('shownoanswer') == 2) {
        if ($thissurvey['shownoanswer'] == 'N') {
            define('SHOW_NO_ANSWER', 0);
        } else {
            define('SHOW_NO_ANSWER', 1);
        }
    } elseif (App()->getConfig('shownoanswer') == 1) {
        define('SHOW_NO_ANSWER', 1);
    } elseif (App()->getConfig('shownoanswer') == 0) {
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
 *                        all : string; complete HTML?; all has been added for backwards compatibility with templates that use question_start.pstpl (now redundant)
 *                        'text'               => $qtitle, question?? $ia[3]?
 *                        'code'               => $ia[2] or title??
 *                        'number'             => $number
 *                        'help'               => ''
 *                        'mandatory'          => ''
 *                        man_message : string; message when mandatory is not answered
 *                        'valid_message'      => ''
 *                        file_valid_message : string; only relevant for file upload
 *                        'class'              => ''
 *                        'man_class'          => ''
 *                        'input_error_class'  => ''              // provides a class.
 *                        'essentials'         => ''
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

    // TODO: This can be cached in some special cases.
    // 1. If move back is disabled
    // 2. No tokens
    // 3. Always first time it's shown to one user (and no tokens).
    // 4. No expressions with tokens or time or other dynamic features.
    if (EmCacheHelper::cacheQanda($ia, $_SESSION['responses_' . $thissurvey['sid']])) {
        $cacheKey = 'retrieveAnswers_' . sha1(implode('_', $ia));
        $value = EmCacheHelper::get($cacheKey);
        if ($value !== false) {
            return $value;
        }
    }

    $display    = $ia[7]; //DISPLAY
    $qid        = $ia[0]; // Question ID
    $qtitle     = $ia[3];
    $inputnames = [];
    $answer     = ""; //Create the question/answer html
    $number     = $ia[9] ?? ''; // Previously in limesurvey, it was virtually impossible to control how the start of questions were formatted. // this is an attempt to allow users (or rather system admins) some control over how the starting text is formatted.
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

    $oQuestion = Question::model()->findByPk($ia[0]);
    $oQuestionTemplate = QuestionTemplate::getNewInstance($oQuestion);
    $oQuestionTemplate->registerAssets(); // Register the custom assets of the question template, if needed
    $oRenderer = $oQuestion->getRenderererObject($ia);
    $values = $oRenderer->render();


    if (isset($values)) {
        //Break apart $values array returned from switch
        //$answer is the html code to be printed
        //$inputnames is an array containing the names of each input field
        list($answer, $inputnames) = $values;
    }

    $question_text['mandatory'] = $ia[6];

    //If this question is mandatory but wasn't answered in the last page
    //add a message HIGHLIGHTING the question
    $mandatory_msg = (($_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['prevstep'])) ? mandatory_message($ia) : '';
    $qtitle .= $mandatory_msg;
    $question_text['man_message'] = $mandatory_msg;

    //show or hide tip
    $_vshow = false;
    if (isset($aQuestionAttributes['hide_tip'])) {
        $_vshow = $aQuestionAttributes['hide_tip'] == 0; //hide_tip=0 means: show the tip
    }

    list($validation_msg, $isValid) = validation_message($ia, $_vshow);

    $qtitle .= $validation_msg;
    $question_text['valid_message'] = $validation_msg;

    if (($_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['prevstep'])) {
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

    $sTemplate = $thissurvey['template'] ?? null;
    if (is_file('templates/' . $sTemplate . '/question_start.pstpl')) {
        $replace = [];
        $find    = [];
        foreach ($question_text as $key => $value) {
            $find[] = '{QUESTION_' . strtoupper($key) . '}'; // Match key words from template
            $replace[] = $value; // substitue text
        };

        if (!defined('QUESTION_START')) {
            define('QUESTION_START', file_get_contents(getTemplatePath($thissurvey['template']) . '/question_start.pstpl'));
        };

        $qtitle_custom = str_replace($find, $replace, (string) QUESTION_START);

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
    if (EmCacheHelper::cacheQanda($ia, $_SESSION['responses_' . $thissurvey['sid']])) {
        EmCacheHelper::set($cacheKey, [$qanda, $inputnames]);
    }
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
    $id         = "vmsg_" . $ia[0];
    $message    = $qinfo['validTip'];
    if ($message != "") {
        $tip = doRender('/survey/questions/question_help/help', array('message' => $message, 'classes' => $class, 'id' => $id), true);
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
                $qtitle .= doRender('/survey/questions/question_help/error', array('message' => $message, 'classes' => ''), true);
            }
        }
    }
    return $qtitle;
}

// TMSW Validation -> EM
function mandatory_popup($ia, $notanswered = null)
{
    global $mandatorypopup, $popup;

    //This sets the mandatory popup message to show if required
    //Called from question.php, group.php or survey.php
    if ($notanswered === null) {
        unset($notanswered);
    }
    if (isset($notanswered) && is_array($notanswered)) {
        //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
        //POPUP WARNING
        // If there is no "hard" mandatory violation (both current and previous violations belong to Soft Mandatory questions),
        // we show the soft mandatory message.
        if ($ia[6] == 'S' && (!isset($mandatorypopup) || $mandatorypopup == 'S')) {
            $popup = gT("One or more mandatory questions have not been answered. If possible, please complete them before continuing to the next page.");
            $mandatorypopup = "S";
        } elseif (!isset($mandatorypopup) && ($ia[4] == 'T' || $ia[4] == 'S' || $ia[4] == 'U')) {
            // If
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
    global $validationpopup, $vpopup;
    //This sets the validation popup message to show if required
    //Called from question.php, group.php or survey.php
    if ($notvalidated === null) {
        unset($notvalidated);
    }
    if (isset($notvalidated) && is_array($notvalidated)) {
        //ADD WARNINGS TO QUESTIONS IF THEY ARE NOT VALID
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
    global $filevalidationpopup, $fpopup;
    if ($filenotvalidated === null) {
        unset($filenotvalidated);
    }
    if (isset($filenotvalidated) && is_array($filenotvalidated)) {

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
 * @todo : check if really deprecated (date : 20240902)
 */
function return_timer_script($aQuestionAttributes, $ia, $disable = null)
{
    global $thissurvey;
    global $gid;
    $time_limit = intval($aQuestionAttributes['time_limit']);
    if($time_limit <= 0) {
        return;
    }
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts") . 'coookies.js', CClientScript::POS_BEGIN);
    Yii::app()->getClientScript()->registerPackage('timer-addition');

    $questionId = $ia[0];
    $surveyId = App()->getConfig('surveyID');
    $langTimer = array(
        'hours' => gT("hours"),
        'mins' => gT("mins"),
        'seconds' => gT("seconds"),
    );
    /* Registering script : don't go to EM : no need usage of ls_json_encode */
    App()->getClientScript()->registerScript("LSVarLangTimer", "LSvar.lang.timer=" . json_encode($langTimer) . ";", CClientScript::POS_BEGIN);
    /**
     * The following lines cover for previewing questions, because no $_SESSION['responses_'.$surveyId]['fieldarray'] exists.
     * This just stops error messages occuring
     */
    if (!isset($_SESSION['responses_' . $surveyId]['fieldarray'])) {
        $_SESSION['responses_' . $surveyId]['fieldarray'] = [];
    }
    /* End */

    //Used to count how many timer questions in a page, and ensure scripts only load once
    $thissurvey['timercount'] = (isset($thissurvey['timercount'])) ? $thissurvey['timercount']++ : 1;

    $disable_next = trim((string) $aQuestionAttributes['time_limit_disable_next']) != '' ? $aQuestionAttributes['time_limit_disable_next'] : 0;
    $disable_prev = trim((string) $aQuestionAttributes['time_limit_disable_prev']) != '' ? $aQuestionAttributes['time_limit_disable_prev'] : 0;
    $time_limit_action = trim((string) $aQuestionAttributes['time_limit_action']) != '' ? $aQuestionAttributes['time_limit_action'] : 1;
    $time_limit_message = trim((string) $aQuestionAttributes['time_limit_message'][$_SESSION['responses_' . $surveyId]['s_lang']]) != '' ? htmlspecialchars((string) $aQuestionAttributes['time_limit_message'][$_SESSION['responses_' . $surveyId]['s_lang']], ENT_QUOTES) : gT("Your time to answer this question has expired");
    $time_limit_warning = trim((string) $aQuestionAttributes['time_limit_warning']) != '' ? intval($aQuestionAttributes['time_limit_warning']) : 0;
    $time_limit_warning_2 = trim((string) $aQuestionAttributes['time_limit_warning_2']) != '' ? intval($aQuestionAttributes['time_limit_warning_2']) : 0;
    $time_limit_countdown_message = trim((string) $aQuestionAttributes['time_limit_countdown_message'][$_SESSION['responses_' . $surveyId]['s_lang']]) != '' ? htmlspecialchars((string) $aQuestionAttributes['time_limit_countdown_message'][$_SESSION['responses_' . $surveyId]['s_lang']], ENT_QUOTES) : gT("Time remaining");
    $time_limit_warning_message = trim((string) $aQuestionAttributes['time_limit_warning_message'][$_SESSION['responses_' . $surveyId]['s_lang']]) != '' ? htmlspecialchars((string) $aQuestionAttributes['time_limit_warning_message'][$_SESSION['responses_' . $surveyId]['s_lang']], ENT_QUOTES) : gT("Your time to answer this question has nearly expired. You have {TIME} remaining.");

    //Render timer
    $timer_html = Yii::app()->twigRenderer->renderQuestion('/survey/questions/question_timer/timer', array('iQid' => $questionId, 'sWarnId' => ''), true);
    $time_limit_warning_message = str_replace("{TIME}", $timer_html, $time_limit_warning_message);
    $time_limit_warning_display_time = trim((string) $aQuestionAttributes['time_limit_warning_display_time']) != '' ? intval($aQuestionAttributes['time_limit_warning_display_time']) + 1 : 0;
    $time_limit_warning_2_message = trim((string) $aQuestionAttributes['time_limit_warning_2_message'][$_SESSION['responses_' . $surveyId]['s_lang']]) != '' ? htmlspecialchars((string) $aQuestionAttributes['time_limit_warning_2_message'][$_SESSION['responses_' . $surveyId]['s_lang']], ENT_QUOTES) : gT("Your time to answer this question has nearly expired. You have {TIME} remaining.");

    //Render timer 2
    $timer_html = Yii::app()->twigRenderer->renderQuestion('/survey/questions/question_timer/timer', array('iQid' => $questionId, 'sWarnId' => '_Warning_2'), true);
    $time_limit_message_delay = trim((string) $aQuestionAttributes['time_limit_message_delay']) != '' ? intval($aQuestionAttributes['time_limit_message_delay']) * 1000 : 1000;
    $time_limit_warning_2_message = str_replace("{TIME}", $timer_html, $time_limit_warning_2_message);
    $time_limit_warning_2_display_time = trim((string) $aQuestionAttributes['time_limit_warning_2_display_time']) != '' ? intval($aQuestionAttributes['time_limit_warning_2_display_time']) + 1 : 0;
    $time_limit_message_style = trim((string) $aQuestionAttributes['time_limit_message_style']) != '' ? $aQuestionAttributes['time_limit_message_style'] : "";
    $time_limit_message_class = "d-none ls-timer-content ls-timer-message ls-no-js-hidden";
    $time_limit_warning_style = trim((string) $aQuestionAttributes['time_limit_warning_style']) != '' ? $aQuestionAttributes['time_limit_warning_style'] : "";
    $time_limit_warning_class = "d-none ls-timer-content ls-timer-warning ls-no-js-hidden";
    $time_limit_warning_2_style = trim((string) $aQuestionAttributes['time_limit_warning_2_style']) != '' ? $aQuestionAttributes['time_limit_warning_2_style'] : "";
    $time_limit_warning_2_class = "d-none ls-timer-content ls-timer-warning2 ls-no-js-hidden";
    $time_limit_timer_style = trim((string) $aQuestionAttributes['time_limit_timer_style']) != '' ? $aQuestionAttributes['time_limit_timer_style'] : "position: relative;";
    $time_limit_timer_class = "ls-timer-content ls-timer-countdown ls-no-js-hidden";

    $timersessionname = "timer_question_" . $questionId;
    if (isset($_SESSION['responses_' . $surveyId][$timersessionname])) {
        $time_limit = $_SESSION['responses_' . $surveyId][$timersessionname];
    }

    App()->getClientScript()->registerScript(
        "TimerQuestion" . $questionId,
        "countdown($questionId, $surveyId, $time_limit, $time_limit_action, $time_limit_warning, $time_limit_warning_2, $time_limit_warning_display_time, $time_limit_warning_2_display_time, '$disable');",
        LSYii_ClientScript::POS_POSTSCRIPT
    );

    $output = Yii::app()->twigRenderer->renderQuestion('/survey/questions/question_timer/timer_header', array('timersessionname' => $timersessionname, 'time_limit' => $time_limit), true);

    if ($thissurvey['timercount'] < 2) {
        $iAction = '';
        if (isset($thissurvey['format']) && $thissurvey['format'] == "G") {
            $qcount = 0;
            foreach ($_SESSION['responses_' . $surveyId]['fieldarray'] as $ib) {
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

        $output .= Yii::app()->twigRenderer->renderQuestion('/survey/questions/question_timer/timer_javascript', array(
            'timersessionname' => $timersessionname,
            'time_limit' => $time_limit,
            'iAction' => $iAction,
            'disable_next' => $disable_next,
            'disable_prev' => $disable_prev,
            'time_limit_countdown_message' => $time_limit_countdown_message,
            'time_limit_message_delay' => $time_limit_message_delay
        ), true);
    }

    $output .= Yii::app()->twigRenderer->renderQuestion(
        '/survey/questions/question_timer/timer_content',
        array(
            'iQid' => $questionId,
            'time_limit_message_style' => $time_limit_message_style,
            'time_limit_message_class' => $time_limit_message_class,
            'time_limit_message' => $time_limit_message,
            'time_limit_warning_style' => $time_limit_warning_style,
            'time_limit_warning_class' => $time_limit_warning_class,
            'time_limit_warning_message' => $time_limit_warning_message,
            'time_limit_warning_2_style' => $time_limit_warning_2_style,
            'time_limit_warning_2_class' => $time_limit_warning_2_class,
            'time_limit_warning_2_message' => $time_limit_warning_2_message,
            'time_limit_timer_style' => $time_limit_timer_style,
            'time_limit_timer_class' => $time_limit_timer_class,
        ),
        true
    );

    $output .= "</div>";
    return $output;
}

/**
 * Return class of a specific row (hidden by relevance)
 * @param int $surveyId actual survey ID
 * @param string $baseName the base name of the question
 * @param string $name The name of the question/row to test
 * @param array $aQuestionAttributes the question attributes
 * @return string
 */

function currentRelevecanceClass($surveyId, $baseName, $name, $aQuestionAttributes)
{
    $relevanceStatus = !isset($_SESSION["responses_{$surveyId}"]['relevanceStatus'][$name]) || $_SESSION["responses_{$surveyId}"]['relevanceStatus'][$name];
    if ($relevanceStatus) {
        return "";
    }
    $sExcludeAllOther = isset($aQuestionAttributes['exclude_all_others']) ? trim((string) $aQuestionAttributes['exclude_all_others']) : '';
    /* EM don't set difference between relevance in session, if exclude_all_others is set , just ls-disabled */
    if ($sExcludeAllOther) {
        foreach (explode(';', $sExcludeAllOther) as $sExclude) {
            $sExclude = $baseName . $sExclude;
            if (
                (!isset($_SESSION["responses_{$surveyId}"]['relevanceStatus'][$sExclude]) || $_SESSION["responses_{$surveyId}"]['relevanceStatus'][$sExclude])
                && (isset($_SESSION["responses_{$surveyId}"][$sExclude]) && $_SESSION["responses_{$surveyId}"][$sExclude] == "Y")
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
}

/**
 * @param string $rowname
 * @param string $valuename
 */
function return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $rowname, $trbc, $valuename, $method = "tbody", $class = null)
{
    $htmltbody2 = "\n\n\t<$method id='javatbd$rowname'";
    $htmltbody2 .= ($class !== null) ? " class='$class'" : "";
    $surveyid = $thissurvey['sid'];
    if (isset($_SESSION["responses_{$surveyid}"]['relevanceStatus'][$rowname]) && !$_SESSION["responses_{$surveyid}"]['relevanceStatus'][$rowname]) {
        // If using exclude_all_others, then need to know whether irrelevant rows should be hidden or disabled
        if (isset($aQuestionAttributes['exclude_all_others'])) {
            $disableit = false;
            foreach (explode(';', trim((string) $aQuestionAttributes['exclude_all_others'])) as $eo) {
                $eorow = $ia[1] . $eo;
                if (
                    (!isset($_SESSION["responses_{$surveyid}"]['relevanceStatus'][$eorow]) || $_SESSION["responses_{$surveyid}"]['relevanceStatus'][$eorow])
                    && (isset($_SESSION[$eorow]) && $_SESSION[$eorow] == "Y")
                ) {
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

// ---------------------------------------------------------------
function do_file_upload($ia)
{
    global $thissurvey;
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $coreClass = "ls-answers upload-item";
    // Fetch question attributes
    $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['fieldname'] = $ia[1];
    $bPreview = Yii::app()->request->getParam('action') == "previewgroup" || Yii::app()->request->getParam('action') == "previewquestion" || $thissurvey['active'] != "Y";
    if ($bPreview) {
        $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['preview'] = 1;
        $questgrppreview = 1; // Preview is launched from Question or group level
    } elseif ($thissurvey['active'] != "Y") {
        $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['preview'] = 1;
        $questgrppreview = 0;
    } else {
        $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['preview'] = 0;
        $questgrppreview = 0;
    }
    $scriptloc = Yii::app()->getController()->createUrl(
        'uploader/index',
        [
            "sid" => Yii::app()->getConfig('surveyID'),
            "fieldname" => $ia[1],
            "qid" => $ia[0],
            "preview" => $questgrppreview,
            "show_title" => $aQuestionAttributes['show_title'],
            "show_comment" => $aQuestionAttributes['show_comment'],
            "minfiles" => $aQuestionAttributes['min_num_of_files'],
            "maxfiles" => $aQuestionAttributes['max_num_of_files'],
        ]
    );

    Yii::app()->getClientScript()->registerPackage('question-file-upload');
    // Modal dialog
    $filecountvalue = '0';
    if (array_key_exists($ia[1] . "_Cfilecount", $_SESSION['responses_' . Yii::app()->getConfig('surveyID')])) {
        $tempval = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1] . "_Cfilecount"];
        if (is_numeric($tempval)) {
            $filecountvalue = $tempval;
        }
    }
    $uploadurl  = $scriptloc . "?sid=" . Yii::app()->getConfig('surveyID') . "&fieldname=" . $ia[1] . "&qid=" . $ia[0];
    $uploadurl .= "&preview=" . $questgrppreview . "&show_title=" . $aQuestionAttributes['show_title'];
    $uploadurl .= "&show_comment=" . $aQuestionAttributes['show_comment'];
    $uploadurl .= "&minfiles=" . $aQuestionAttributes['min_num_of_files']; // TODO: Regression here? Should use LEMval(minfiles) like above
    $uploadurl .= "&maxfiles=" . $aQuestionAttributes['max_num_of_files']; // Same here.

    $fileuploadData = array(
        'fileid' => $ia[1],
        'value' => $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]],
        'filecountvalue' => $filecountvalue,
        'coreClass' => $coreClass,
        'maxFiles' =>  $aQuestionAttributes['max_num_of_files'],
        'basename' => $ia[1],
        'uploadurl' => $uploadurl,
        'scriptloc' => Yii::app()->getController()->createUrl('/uploader/index/mode/upload/'),
        'showTitle' => $aQuestionAttributes['show_title'],
        'showComment' => $aQuestionAttributes['show_comment'],
        'uploadButtonLabel' => ngT("Upload file|Upload files", $aQuestionAttributes['max_num_of_files'])
    );
    $answer = doRender('/survey/questions/answer/file_upload/answer', $fileuploadData, true);

    $inputnames = array();
    $inputnames[] = $ia[1];
    $inputnames[] = $ia[1] . "_Cfilecount";
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

    if (trim((string) $aQuestionAttributes['prefix'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $prefix      = $aQuestionAttributes['prefix'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withprefix";
    } else {
        $prefix = '';
    }

    if (trim((string) $aQuestionAttributes['suffix'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $suffix      = $aQuestionAttributes['suffix'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withsuffix";
    } else {
        $suffix = '';
    }
    if (intval(trim((string) $aQuestionAttributes['maximum_chars'])) > 0 && intval(trim((string) $aQuestionAttributes['maximum_chars'])) < 20) {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maxlength = intval(trim((string) $aQuestionAttributes['maximum_chars']));
        $extraclass .= " ls-input-maxchars";
    } else {
        $maxlength = 20;
    }
    if (trim((string) $aQuestionAttributes['text_input_width']) != '') {
        $col         = ($aQuestionAttributes['text_input_width'] <= 12) ? $aQuestionAttributes['text_input_width'] : 12;
        $extraclass .= " col-md-" . trim((string) $col);
        $withColumn = true;
    } else {
        $withColumn = false;
    }
    if (ctype_digit(trim((string) $aQuestionAttributes['input_size']))) {
        $inputsize = trim((string) $aQuestionAttributes['input_size']);
        $extraclass .= " ls-input-sized";
    } else {
        $inputsize = null;
    }
    if (trim((string) $aQuestionAttributes['num_value_int_only']) == 1) {
        $extraclass      .= " integeronly";
        $answertypeclass .= " integeronly";
        $integeronly      = 1;
    } else {
        $integeronly = 0;
    }
    if (trim((string) $aQuestionAttributes['placeholder'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $placeholder = $aQuestionAttributes['placeholder'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']];
    } else {
        $placeholder = '';
    }

    $fValue     = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]];
    $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeparator = $sSeparator['separator'];

    if ($fValue && is_string($fValue)) {
        // Fix reloaded DECIMAL value
        if ($fValue[0] == ".") {
            // issue #15684 mssql SAVE 0.01 AS .0100000000, set it at 0.0100000000
            $fValue = "0" . $fValue;
        }
        if (strpos($fValue, ".")) {
            $fValue = rtrim(rtrim($fValue, "0"), ".");
        }
    }
    $fValue = str_replace('.', $sSeparator, (string) $fValue);

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
        'placeholder'            => $placeholder,
    ), true);

    $inputnames = [];
    $inputnames[] = $ia[1];
    $mandatory = null;
    return array($answer, $inputnames, $mandatory);
}

// ---------------------------------------------------------------
function do_shortfreetext($ia)
{
    global $thissurvey;

    $coreClass = "ls-answers answer-item text-item";
    $extraclass = "";
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

    if ($aQuestionAttributes['numbers_only'] == 1) {
        $sSeparator             = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator             = $sSeparator['separator'];
        $extraclass            .= " numberonly";
        $coreClass             .= " numeric-item";
        $numberonly             = true;
    } else {
        $sSeparator = '';
        $numberonly             = false;
    }
    if (intval(trim((string) $aQuestionAttributes['maximum_chars'])) > 0) {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maxlength      = intval(trim((string) $aQuestionAttributes['maximum_chars']));
        $extraclass    .= " ls-input-maxchars";
    } else {
        $maxlength = "";
    }

    if (trim((string) $aQuestionAttributes['text_input_width']) != '' && intval(trim((string) $aQuestionAttributes['location_mapservice'])) == 0) {
        $col         = ($aQuestionAttributes['text_input_width'] <= 12) ? $aQuestionAttributes['text_input_width'] : 12;
        $extraclass .= " col-md-" . trim((string) $col);
        $withColumn = true;
    } else {
        $withColumn = false;
    }
    if (ctype_digit(trim((string) $aQuestionAttributes['input_size']))) {
        $inputsize = trim((string) $aQuestionAttributes['input_size']);
        $extraclass .= " ls-input-sized";
    } else {
        $inputsize = null;
    }
    if (trim((string) $aQuestionAttributes['prefix'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $prefix      = $aQuestionAttributes['prefix'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withprefix";
    } else {
        $prefix = '';
    }
    if (trim((string) $aQuestionAttributes['suffix'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $suffix      = $aQuestionAttributes['suffix'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']];
        $extraclass .= " withsuffix";
    } else {
        $suffix = '';
    }
    if (trim((string) $aQuestionAttributes['placeholder'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $placeholder = $aQuestionAttributes['placeholder'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']];
    } else {
        $placeholder = '';
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

    if (trim((string) $aQuestionAttributes['display_rows']) != '') {
        //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
        $drows = $aQuestionAttributes['display_rows'];

        $dispVal = "";

        if ($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]]) {
            $dispVal = str_replace("\\", "", (string) $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]]);

            if ($aQuestionAttributes['numbers_only'] == 1) {
                $dispVal = str_replace('.', $sSeparator, $dispVal);
            }
            $dispVal = htmlspecialchars($dispVal);
        }

        $answer .= doRender('/survey/questions/answer/shortfreetext/textarea/item', array(
            'extraclass'             => $extraclass,
            'coreClass'              => $coreClass,
            'freeTextId'             => 'answer' . $ia[1],
            'name'                   => $ia[1],
            'basename'               => $ia[1],
            'drows'                  => $drows,
            'dispVal'                => $dispVal,
            'maxlength'              => $maxlength,
            'kpclass'                => $kpclass,
            'prefix'                 => $prefix,
            'suffix'                 => $suffix,
            'inputsize'              => $inputsize,
            'placeholder'            => $placeholder,
            'withColumn'             => $withColumn,
            'numberonly'             => $numberonly,
        ), true);
    } elseif ((int) ($aQuestionAttributes['location_mapservice']) == 1) {
        $coreClass       = "ls-answers map-item geoloc-item";
        $currentLocation = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]];
        $currentLatLong  = null;
        // Get the latitude/longtitude for the point that needs to be displayed by default
        if (strlen((string) $currentLocation) > 2 && strpos((string) $currentLocation, ";")) { // Quick check if current location is OK
            $currentLatLong = explode(';', (string) $currentLocation);
            $currentLatLong = array($currentLatLong[0], $currentLatLong[1]);
        } else {
            if ((int) ($aQuestionAttributes['location_nodefaultfromip']) == 0) {
                $currentLatLong = getLatLongFromIp(getIPAddress());
            }

            if (empty($currentLatLong)) {
                $floatLat = "";
                $floatLng = "";
                $sDefaultcoordinates = trim(LimeExpressionManager::ProcessString($aQuestionAttributes['location_defaultcoordinates'], $ia[0], array(), 3, 1, false, false, true));/* static var is the last one */
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

        $currentLocation = $currentLatLong[0] . " " . $currentLatLong[1];

        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . "map.js", LSYii_ClientScript::POS_END);
        $sGoogleMapsAPIKey = trim((string) Yii::app()->getConfig("googleMapsAPIKey"));
        if ($aQuestionAttributes['location_mapservice'] == 1 && !empty($sGoogleMapsAPIKey)) {
            Yii::app()->getClientScript()->registerScriptFile("//maps.googleapis.com/maps/api/js?sensor=false&key={$sGoogleMapsAPIKey}", LSYii_ClientScript::POS_BEGIN);
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
            'freeTextId'             => 'answer' . $ia[1],
            'name'                   => $ia[1],
            'qid'                    => $ia[0],
            'basename'               => $ia[1],
            'value'                  => $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]],
            'kpclass'                => $kpclass,
            'currentLocation'        => $currentLocation,
            'strBuild'               => $strBuild,
            'location_mapservice'    => $aQuestionAttributes['location_mapservice'],
            'location_mapzoom'       => $aQuestionAttributes['location_mapzoom'],
            'location_mapheight'     => $aQuestionAttributes['location_mapheight'],
            'questionHelp'           => $questionHelp,
            'question_text_help'     => $sQuestionHelpText,
            'inputsize'              => $inputsize,
            'placeholder'            => $placeholder,
            'withColumn'             => $withColumn
        ), true);
    } elseif ((int) ($aQuestionAttributes['location_mapservice']) == 100) {
        $coreClass       = "ls-answers map-item geoloc-item";
        $currentLocation = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]];
        $currentCenter   = $currentLatLong = null;
        // Get the latitude/longtitude for the point that needs to be displayed by default
        if (strlen((string) $currentLocation) > 2 && strpos((string) $currentLocation, ";")) {
            $currentLatLong = explode(';', (string) $currentLocation);
            $currentCenter  = $currentLatLong = array($currentLatLong[0], $currentLatLong[1]);
        } elseif ((int) ($aQuestionAttributes['location_nodefaultfromip']) == 0) {
            $currentCenter = $currentLatLong = getLatLongFromIp(getIPAddress());
        }

        // If it's not set : set the center to the default position, but don't set the marker
        if (!$currentLatLong) {
            $currentLatLong = array("", "");
            $sDefaultcoordinates = trim(LimeExpressionManager::ProcessString($aQuestionAttributes['location_defaultcoordinates'], $ia[0], array(), 3, 1, false, false, true));/* static var is the last one */
            $currentCenter = explode(" ", $sDefaultcoordinates);
            if (count($currentCenter) != 2) {
                $currentCenter = array("", "");
            }
        }
        $strBuild = "";

        $aGlobalMapScriptVar = array(
            'geonameUser' => getGlobalSetting('GeoNamesUsername'), // Did we need to urlencode ?
            'geonameLang' => Yii::app()->language,
        );
        $aThisMapScriptVar = array(
            'zoomLevel' => $aQuestionAttributes['location_mapzoom'],
            'latitude' => $currentCenter[0],
            'longitude' => $currentCenter[1],

        );
        App()->getClientScript()->registerPackage('leaflet');
        App()->getClientScript()->registerPackage('devbridge-autocomplete'); /* for autocomplete */
        Yii::app()->getClientScript()->registerScript('sGlobalMapScriptVar', "LSmap=" . ls_json_encode($aGlobalMapScriptVar) . ";\nLSmaps=[];", CClientScript::POS_BEGIN);
        Yii::app()->getClientScript()->registerScript('sThisMapScriptVar' . $ia[1], "LSmaps['{$ia[1]}']=" . ls_json_encode($aThisMapScriptVar) . ";", CClientScript::POS_BEGIN);
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . "map.js", CClientScript::POS_END);
        Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'map.css');

        if (isset($aQuestionAttributes['hide_tip']) && $aQuestionAttributes['hide_tip'] == 0) {
            $questionHelp = true;
            $sQuestionHelpText = gT('Click to set the location or drag and drop the pin. You may may also enter coordinates');
        }

        $itemDatas = array(
            'extraclass' => $extraclass,
            'coreClass' => $coreClass,
            'name' => $ia[1],
            'qid' => $ia[0],
            'basename'               => $ia[1],
            'value' => $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]],
            'strBuild' => $strBuild,
            'location_mapservice' => $aQuestionAttributes['location_mapservice'],
            'location_mapzoom' => $aQuestionAttributes['location_mapzoom'],
            'location_mapheight' => $aQuestionAttributes['location_mapheight'],
            'questionHelp' => $questionHelp ?? '',
            'question_text_help' => $sQuestionHelpText,
            'location_value' => $currentLatLong[0] . ' ' . $currentLatLong[1],
            'currentLat' => $currentLatLong[0],
            'currentLong' => $currentLatLong[1],
            'inputsize'              => $inputsize,
            'placeholder'            => $placeholder,
            'withColumn'             => $withColumn
        );
        $answer = doRender('/survey/questions/answer/shortfreetext/location_mapservice/item_100', $itemDatas, true);
    } else {
        //no question attribute set, use common input text field
        $dispVal = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]];
        if ($aQuestionAttributes['numbers_only'] == 1) {
            $dispVal = str_replace('.', $sSeparator, (string) $dispVal);
        }
        $dispVal = htmlspecialchars((string) $dispVal, ENT_QUOTES, 'UTF-8');
        $itemDatas = array(
            'extraclass' => $extraclass,
            'coreClass' => $coreClass,
            'name' => $ia[1],
            'basename'               => $ia[1],
            'prefix' => $prefix,
            'suffix' => $suffix,
            'kpclass' => $kpclass,
            'dispVal' => $dispVal,
            'maxlength' => $maxlength,
            'numberonly' => $numberonly,
            'inputsize'              => $inputsize,
            'placeholder'            => $placeholder,
            'withColumn'             => $withColumn
        );
        $answer = doRender('/survey/questions/answer/shortfreetext/text/item', $itemDatas, true);
    }

    if (trim((string) $aQuestionAttributes['time_limit']) != '') {
        $answer .= return_timer_script($aQuestionAttributes, $ia, "answer" . $ia[1]);
    }

    $inputnames = [];
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

/**
 * Renders Yes/No Question Type.
 *
 * @param array $ia
 * @return array
 */
function do_yesno($ia)
{
    $yChecked = $nChecked = $naChecked = '';
    if ($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]] == 'Y') {
        $yChecked = CHECKED;
    }

    if ($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]] == 'N') {
        $nChecked = CHECKED;
    }

    $noAnswer = false;
    if (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) {
        $noAnswer = true;
        if (empty($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]])) {
            $naChecked = CHECKED;
        }
    }

    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $displayType = (int) $aQuestionAttributes['display_type'];
    $noAnswer = $noAnswer ?? false;
    $itemDatas = array(
        'name' => $ia[1],
        'basename' => $ia[1],
        'yChecked' => $yChecked,
        'nChecked' => $nChecked,
        'naChecked' => $naChecked,
        'noAnswer' => $noAnswer,
        'value' => $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]],
        'displayType' => $displayType,
    );
    if ($displayType === 0) {
        $answer = doRender('/survey/questions/answer/yesno/buttons/item', $itemDatas, true);
    } else {
        $answer = doRender('/survey/questions/answer/yesno/radio/item', $itemDatas, true);
    }

    $inputnames = [];
    $inputnames[] = $ia[1];
    return array($answer, $inputnames);
}

/**
 * Renders Gender Question Types.
 *
 * @param array $ia
 * @return array
 */
function do_gender($ia)
{
    $fChecked               = ($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]] == 'F') ? 'CHECKED' : '';
    $mChecked               = ($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]] == 'M') ? 'CHECKED' : '';
    $naChecked              = '';
    $aQuestionAttributes    = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $displayType            = (int) $aQuestionAttributes['display_type'];
    if (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) {
        $noAnswer = true;
        if ($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]] == '') {
            $naChecked = CHECKED;
        }
    }

    $noAnswer = $noAnswer ?? false;

    $itemDatas = array(
        'name'                   => $ia[1],
        'basename'               => $ia[1],
        'fChecked'               => $fChecked,
        'mChecked'               => $mChecked,
        'naChecked'              => $naChecked,
        'noAnswer'               => $noAnswer,
        'value'                  => $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$ia[1]],
    );

    if ($displayType === 0) {
        $answer = doRender('/survey/questions/answer/gender/buttons/answer', $itemDatas, true);
    } else {
        $answer = doRender('/survey/questions/answer/gender/radio/answer', $itemDatas, true);
    }

    $inputnames   = [];
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
    $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && ($ia[6] == 'Y' || $ia[6] == 'S')) ? explode("|", (string) $aLastMoveResult['unansweredSQs']) : [];
    $coreClass               = "ls-answers subquestion-list questions-list radio-array";
    $checkconditionFunction  = "checkconditions";
    $aQuestionAttributes     = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    $inputnames              = [];

    if (trim((string) $aQuestionAttributes['answer_width']) != '') {
        $answerwidth = $aQuestionAttributes['answer_width'];
        $defaultWidth = false;
    } else {
        $answerwidth = 33;
        $defaultWidth = true;
    }
    $columnswidth = 100 - $answerwidth;
    $colCount = 5; // number of columns

    $YorNorSvalue = $ia[6];
    $isNotYes = $YorNorSvalue !== 'Y';
    $isNotS   = $YorNorSvalue !== 'S';
    $showNoAnswer = SHOW_NO_ANSWER;

    if (($isNotYes && $isNotS) && $showNoAnswer) {
        //Question is not mandatory
        ++$colCount; // add another column
    }
    $sSurveyLanguage = $_SESSION['responses_' . App()->getConfig('surveyID')]['s_lang'];

    // Get questions and answers by defined order
    $question = Question::model()->findByPk($ia[0]);
    $orderingService = \LimeSurvey\DI::getContainer()->get(
        \LimeSurvey\Models\Services\QuestionOrderingService\QuestionOrderingService::class
    );
    $aSubquestions = $orderingService->getOrderedSubQuestions($question, 0, $sSurveyLanguage);

    $fn            = 1;
    $sColumns      = $sHeaders = $sRows = $answer_tds = '';

    // Check if any subquestion use suffix/prefix
    $right_exists  = false;
    foreach ($aSubquestions as $ansrow) {
        $answertext2 = $ansrow->questionl10ns[$sSurveyLanguage]->question;
        if (strpos((string) $answertext2, '|')) {
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
        // Add a class so we can style the left side text differently when there is a right side text
        $coreClass .= " semantic-differential-list";
    }
    $cellwidth = $columnswidth / $colCount;

    // Render Columns
    for ($xc = 1; $xc <= 5; $xc++) {
        $sColumns .= doRender('/survey/questions/answer/arrays/5point/columns/col', array('cellwidth' => $cellwidth), true);
    }

    if (($isNotYes && $isNotS) && $showNoAnswer) {
        //Question is not mandatory
        $sColumns .= doRender('/survey/questions/answer/arrays/5point/columns/col', array('cellwidth' => $cellwidth), true);
    }

    // Column for suffix
    if ($right_exists) {
        $sColumns .= doRender('/survey/questions/answer/arrays/5point/columns/col', array('cellwidth' => $answerwidth), true);
    }

    $sHeaders .= doRender('/survey/questions/answer/arrays/5point/rows/cells/header_information', array(
        'class' => '',
        'content' => '',
    ), true);
    $columnHeading = 0;
    for ($xc = 1; $xc <= 5; $xc++) {
        $columnHeading = $xc;
        $sHeaders .= doRender('/survey/questions/answer/arrays/5point/rows/cells/header_answer', array(
            'class' => 'answer-text',
            'content' => " " . $xc,
            'basename' => $ia[1],
            'code' => $xc,
        ), true);
    }

    // Header for suffix
    if ($right_exists) {
        $sHeaders .= doRender('/survey/questions/answer/arrays/5point/rows/cells/header_information', array(
            'class' => 'answertextright',
            'content' => '',
        ), true);
    }

    if (($isNotYes && $isNotS) && $showNoAnswer) {
        //Question is not mandatory
        $sHeaders .= doRender('/survey/questions/answer/arrays/5point/rows/cells/header_answer', array(
            'class' => 'answer-text noanswer-text',
            'content' => gT('No answer'),
            'basename' => $ia[1],
            'code' => '',
        ), true);
    }

    foreach ($aSubquestions as $j => $ansrow) {
        $myfname = $ia[1] . "_S" . $ansrow['qid'];
        $answertext = $ansrow->questionl10ns[$sSurveyLanguage]->question;
        if (strpos((string) $answertext, '|') !== false) {
            $answertext = substr((string) $answertext, 0, strpos((string) $answertext, '|'));
        }

        /* Check if this item has not been answered */
        $error = (($ia[6] == 'Y' || $ia[6] == 'S') && in_array($myfname, $aMandatoryViolationSubQ)) ? true : false;

        /* Check for array_filter  */
        $sDisplayStyle = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);

        // Value
        $value = $_SESSION['responses_' . App()->getConfig('surveyID')][$myfname] ?? '';

        for ($i = 1; $i <= 5; $i++) {
            $CHECKED = (isset($_SESSION['responses_' . App()->getConfig('surveyID')][$myfname]) && $_SESSION['responses_' . App()->getConfig('surveyID')][$myfname] == $i) ? 'CHECKED' : '';
            $answer_tds .= doRender('/survey/questions/answer/arrays/5point/rows/cells/answer_td_input', array(
                'i' => $i,
                'labelText' => (string) $i,
                'myfname' => $myfname,
                'basename' => $ia[1],
                'code' => $i,
                'CHECKED' => $CHECKED,
                'checkconditionFunction' => $checkconditionFunction,
                'value' => $i,
            ), true);
        }

        // Suffix
        $answertext2 = $ansrow->questionl10ns[$sSurveyLanguage]->question;
        $hasPipeInAnswerText2 = strpos((string) $answertext2, '|');

        if ($hasPipeInAnswerText2) {
            $answertext2 = substr((string) $answertext2, strpos((string) $answertext2, '|') + 1);
            $answer_tds .= doRender('/survey/questions/answer/arrays/5point/rows/cells/answer_td_answertext', array(
                'class' => 'answertextright',
                'style' => 'text-align:left',
                'answertext2' => $answertext2,
            ), true);
        } elseif ($right_exists) {
            $answer_tds .= doRender('/survey/questions/answer/arrays/5point/rows/cells/answer_td_answertext', array(
                'answerwidth' => $answerwidth,
                'answertext2' => '',
            ), true);
        }

        // ==>tds
        if (($isNotYes && $isNotS) && $showNoAnswer) {
            $CHECKED = (!isset($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname] == '') ? 'CHECKED' : '';
            $answer_tds .= doRender('/survey/questions/answer/arrays/5point/rows/cells/answer_td_input', array(
                'i' => "",
                'labelText' => gT('No answer'),
                'myfname' => $myfname,
                'basename' => $ia[1],
                'code' => '',
                'CHECKED' => $CHECKED,
                'checkconditionFunction' => $checkconditionFunction,
                'value' => '',
            ), true);
        }

        $sRows .= doRender('/survey/questions/answer/arrays/5point/rows/answer_row', array(
            'answer_tds'    => $answer_tds,
            'odd'           => ($j % 2),
            'myfname'       => $myfname,
            'answertext'    => $answertext,
            'answerwidth'   => $answerwidth,
            'value'         => $value,
            'error'         => $error,
            'sDisplayStyle' => $sDisplayStyle
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
    $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && ($ia[6] == 'Y' || $ia[6] == 'S')) ? explode("|", (string) $aLastMoveResult['unansweredSQs']) : [];
    $coreClass = "ls-answers subquestion-list questions-list radio-array";


    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    if (ctype_digit(trim((string) $aQuestionAttributes['answer_width']))) {
        $answerwidth = trim((string) $aQuestionAttributes['answer_width']);
    } else {
        $answerwidth = 33;
    }
    $cellwidth = 10; // number of columns
    if (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        ++$cellwidth; // add another column
    }
    $cellwidth = round(((100 - $answerwidth) / $cellwidth), 1); // convert number of columns to percentage of table width

    // Get subquestions using ordering service so keep_codes_order is respected
    $question = Question::model()->findByPk($ia[0]);
    $iSurveyId = $question->sid;
    $survey = $question->survey;
    $iSurveyId = Question::model()->findByPk($ia[0])->sid;
    $sSurveyLanguage = isset($_SESSION['responses_' . $iSurveyId]) ? $_SESSION['responses_' . $iSurveyId]['s_lang'] : Question::model()->findByPk($ia[0])->survey->language;

    $orderingService = \LimeSurvey\DI::getContainer()->get(
        \LimeSurvey\Models\Services\QuestionOrderingService\QuestionOrderingService::class
    );
    $aSubquestions = $orderingService->getOrderedSubQuestions($question, 0, $sSurveyLanguage);

    $fn = 1;
    $odd_even = '';
    $sColumns = '';
    for ($xc = 1; $xc <= 10; $xc++) {
        $odd_even = alternation($odd_even);
        $sColumns .= doRender('/survey/questions/answer/arrays/10point/columns/col', array('odd_even' => $odd_even, 'cellwidth' => $cellwidth), true);
    }

    if (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        $odd_even = alternation($odd_even);
        $sColumns .= doRender('/survey/questions/answer/arrays/10point/columns/col', array('odd_even' => $odd_even, 'cellwidth' => $cellwidth), true);
    }

    $sHeaders = '';
    $sHeaders .= doRender(
        '/survey/questions/answer/arrays/10point/rows/cells/header_information',
        array(
            'class' => '',
            'content' => '',
            'basename' => $ia[1],
            'code' => '',
        ),
        true
    );
    for ($xc = 1; $xc <= 10; $xc++) {
        $sHeaders .= doRender(
            '/survey/questions/answer/arrays/10point/rows/cells/header_answer',
            array(
                'class' => 'answer-text',
                'content' => " " . $xc,
                'basename' => $ia[1],
                'code' => $xc,
            ),
            true
        );
    }

    if (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        $sHeaders .= doRender(
            '/survey/questions/answer/arrays/10point/rows/cells/header_answer',
            array(
                'class' => 'answer-text noanswer-text',
                'content' => gT('No answer'),
                'basename' => $ia[1],
                'code' => '',
            ),
            true
        );
    }

    $trbc = '';

    $sRows = '';
    $inputnames = [];
    foreach ($aSubquestions as $j => $ansrow) {
        $myfname = $ia[1] . "_S" . $ansrow['qid'];
        $answertext = $ansrow->questionl10ns[$sSurveyLanguage]->question;
        /* Check if this item has not been answered */
        $error = (($ia[6] == 'Y' || $ia[6] == 'S') && in_array($myfname, $aMandatoryViolationSubQ)) ? true : false;
        $trbc = alternation($trbc, 'row');

        //Get array filter stuff
        $sDisplayStyle = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);

        // Value
        $value = $_SESSION['responses_' . $iSurveyId][$myfname] ?? '';

        $answer_tds = '';
        for ($i = 1; $i <= 10; $i++) {
            $CHECKED = (isset($_SESSION['responses_' . $iSurveyId][$myfname]) && $_SESSION['responses_' . $iSurveyId][$myfname] == $i) ? 'CHECKED' : '';

            $answer_tds .= doRender(
                '/survey/questions/answer/arrays/10point/rows/cells/answer_td_input',
                array(
                    'i' => $i,
                    'labelText' => (string) $i,
                    'myfname' => $myfname,
                    'basename' => $ia[1],
                    'code' => $i,
                    'CHECKED' => $CHECKED,
                    'value' => $i,
                ),
                true
            );
        }

        if ($ia[6] != "Y" && SHOW_NO_ANSWER == 1) {
            $CHECKED = (!isset($_SESSION['responses_' . $iSurveyId][$myfname]) || $_SESSION['responses_' . $iSurveyId][$myfname] == '') ? 'CHECKED' : '';
            $answer_tds .= doRender(
                '/survey/questions/answer/arrays/10point/rows/cells/answer_td_input',
                array(
                    'i' => '',
                    'labelText' => gT('No answer'),
                    'myfname' => $myfname,
                    'basename' => $ia[1],
                    'code' => '',
                    'CHECKED' => $CHECKED,
                    'value' => '',
                ),
                true
            );
        }

        $sRows .= doRender(
            '/survey/questions/answer/arrays/10point/rows/answer_row',
            array(
                'myfname'       => $myfname,
                'answerwidth'   => $answerwidth,
                'answertext'    => $answertext,
                'value'         => $value,
                'error'         => $error,
                'sDisplayStyle' => $sDisplayStyle,
                'odd'           => ($j % 2),
                'answer_tds'    => $answer_tds,
            ),
            true
        );

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
    $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && ($ia[6] == 'Y' || $ia[6] == 'S')) ? explode("|", (string) $aLastMoveResult['unansweredSQs']) : [];
    $coreClass               = "ls-answers subquestion-list questions-list radio-array";
    $checkconditionFunction  = "checkconditions";
    $aQuestionAttributes     = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    if (ctype_digit(trim((string) $aQuestionAttributes['answer_width']))) {
        $answerwidth = trim((string) $aQuestionAttributes['answer_width']);
    } else {
        $answerwidth = 33;
    }
    $cellwidth               = 3; // number of columns

    if (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        ++$cellwidth; // add another column
    }

    $cellwidth = round(((100 - $answerwidth) / $cellwidth), 1); // convert number of columns to percentage of table width

    // Get questions and answers by defined order
    $question = Question::model()->findByPk($ia[0]);
    $sSurveyLanguage = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang'];
    $orderingService = \LimeSurvey\DI::getContainer()->get(
        \LimeSurvey\Models\Services\QuestionOrderingService\QuestionOrderingService::class
    );
    $aSubquestions = $orderingService->getOrderedSubQuestions($question, 0, $sSurveyLanguage);
    $anscount       = count($aSubquestions);
    $fn             = 1;

    $odd_even = '';
    $sColumns = '';

    for ($xc = 1; $xc <= 3; $xc++) {
        $odd_even  = alternation($odd_even);
        $sColumns .= doRender('/survey/questions/answer/arrays/yesnouncertain/columns/col', array('odd_even' => $odd_even, 'cellwidth' => $cellwidth), true);
    }

    if (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        $odd_even  = alternation($odd_even);
        $sColumns .= doRender('/survey/questions/answer/arrays/yesnouncertain/columns/col', array('odd_even' => $odd_even, 'cellwidth' => $cellwidth, 'no_answer' => true), true);
    }

    $no_answer = (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) ? true : false;
    $sHeaders  = doRender(
        '/survey/questions/answer/arrays/yesnouncertain/rows/cells/thead',
        array('basename' => $ia[1], 'no_answer' => $no_answer, 'anscount' => $anscount),
        true
    );

    $inputnames = [];

    if ($anscount > 0) {
        $sRows = '';

        foreach ($aSubquestions as $i => $ansrow) {
            $myfname = $ia[1] . "_S" . $ansrow['qid'];
            $answertext = $ansrow->questionl10ns[$sSurveyLanguage]->question;
            /* Check the sub question mandatory violation */
            $error = (($ia[6] == 'Y' || $ia[6] == 'S') && in_array($myfname, $aMandatoryViolationSubQ)) ? true : false;

            // Get array_filter stuff
            $no_answer = (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) ? true : false;
            $value     = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname] ?? '';
            $Ychecked  = (isset($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname] == 'Y') ? 'CHECKED' : '';
            $Uchecked  = (isset($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname] == 'U') ? 'CHECKED' : '';
            $Nchecked  = (isset($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname] == 'N') ? 'CHECKED' : '';
            $NAchecked = (!isset($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname] == '') ? 'CHECKED' : '';

            $sRows .= doRender('/survey/questions/answer/arrays/yesnouncertain/rows/answer_row', array(
                'basename'               => $ia[1],
                'myfname'                => $myfname,
                'answertext'             => $answertext,
                'answerwidth'            => $answerwidth,
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
        'sRows'       => $sRows ?? '',
        'anscount'    => $anscount,
        'basename' => $ia[1],
    ), true);

    return array($answer, $inputnames);
}

function do_array_increasesamedecrease($ia)
{
    $aLastMoveResult         = LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ = ($aLastMoveResult['mandViolation'] && ($ia[6] == 'Y' || $ia[6] == 'S')) ? explode("|", (string) $aLastMoveResult['unansweredSQs']) : [];
    $coreClass               = "ls-answers subquestion-list questions-list radio-array";
    $checkconditionFunction  = "checkconditions";
    $aQuestionAttributes     = QuestionAttribute::model()->getQuestionAttributes($ia[0]);
    if (ctype_digit(trim((string) $aQuestionAttributes['answer_width']))) {
        $answerwidth = trim((string) $aQuestionAttributes['answer_width']);
    } else {
        $answerwidth = 33;
    }
    $cellwidth               = 3; // number of columns
    $inputnames              = [];

    if (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        ++$cellwidth; // add another column
    }

    $cellwidth = round(((100 - $answerwidth) / $cellwidth), 1); // convert number of columns to percentage of table width

    $sSurveyLanguage = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang'];
    // Get subquestions through ordering service so keep_codes_order is respected
    $question = Question::model()->findByPk($ia[0]);
    $orderingService = \LimeSurvey\DI::getContainer()->get(
        \LimeSurvey\Models\Services\QuestionOrderingService\QuestionOrderingService::class
    );
    $aSubquestions = $orderingService->getOrderedSubQuestions($question, 0, $sSurveyLanguage);
    $anscount       = count($aSubquestions);
    $fn             = 1;
    $odd_even       = '';
    $sColumns       = "";

    for ($xc = 1; $xc <= 3; $xc++) {
        $odd_even  = alternation($odd_even);
        $sColumns .= doRender('/survey/questions/answer/arrays/increasesamedecrease/columns/col', array('odd_even' => $odd_even, 'cellwidth' => $cellwidth), true);
    }
    if (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) {
        //Question is not mandatory
        $odd_even  = alternation($odd_even);
        $sColumns .= doRender('/survey/questions/answer/arrays/increasesamedecrease/columns/col', array('odd_even' => $odd_even, 'cellwidth' => $cellwidth), true);
    }

    $no_answer = (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) ? true : false; //Question is not mandatory

    $sHeaders = doRender(
        '/survey/questions/answer/arrays/increasesamedecrease/rows/cells/thead',
        array(
            'basename' => $ia[1],
            'no_answer' => $no_answer
        ),
        true
    );


    // rows
    $sRows = '';
    foreach ($aSubquestions as $i => $ansrow) {
        $myfname        = $ia[1] . "_S" . $ansrow['qid'];
        $answertext     = $ansrow->questionl10ns[$sSurveyLanguage]->question;
        $error          = (($ia[6] == 'Y' || $ia[6] == 'S') && in_array($myfname, $aMandatoryViolationSubQ)) ? true : false; /* Check the sub Q mandatory violation */
        $value          = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname] ?? '';
        $Ichecked       = (isset($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname] == 'I') ? 'CHECKED' : '';
        $Schecked       = (isset($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname] == 'S') ? 'CHECKED' : '';
        $Dchecked       = (isset($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname] == 'D') ? 'CHECKED' : '';
        $NAchecked      = (!isset($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname] == '') ? 'CHECKED' : '';
        $no_answer      = (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) ? true : false;

        $sRows .= doRender('/survey/questions/answer/arrays/increasesamedecrease/rows/answer_row', array(
            'basename' => $ia[1],
            'myfname' => $myfname,
            'answertext' => $answertext,
            'answerwidth' => $answerwidth,
            'Ichecked' => $Ichecked,
            'Schecked' => $Schecked,
            'Dchecked' => $Dchecked,
            'NAchecked' => $NAchecked,
            'value' => $value,
            'checkconditionFunction' => $checkconditionFunction,
            'error' => $error,
            'no_answer' => $no_answer,
            'odd' => ($i % 2)
        ), true);

        $inputnames[] = $myfname;
        $fn++;
    }

    $answer = doRender('/survey/questions/answer/arrays/increasesamedecrease/answer', array(
        'coreClass'  => $coreClass,
        'answerwidth' => $answerwidth,
        'sColumns'   => $sColumns,
        'sHeaders'   => $sHeaders,
        'sRows'      => $sRows,
        'anscount'   => $anscount,
        'basename' => $ia[1],
    ), true);

    return array($answer, $inputnames);
}

function do_array_texts($ia)
{
    global $thissurvey;
    $aLastMoveResult            = LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ    = ($aLastMoveResult['mandViolation'] && ($ia[6] == 'Y' || $ia[6] == 'S')) ? explode("|", (string) $aLastMoveResult['unansweredSQs']) : [];
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
    $inputnames             = [];

    if (ctype_digit(trim((string) $aQuestionAttributes['repeat_headings'])) && trim((string) $aQuestionAttributes['repeat_headings']) != "") {
        $repeatheadings     = intval($aQuestionAttributes['repeat_headings']);
        $minrepeatheadings  = 0;
    }
    if (intval(trim((string) $aQuestionAttributes['maximum_chars'])) > 0) {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maxlength = intval(trim((string) $aQuestionAttributes['maximum_chars']));
        $extraclass .= " ls-input-maxchars";
    } else {
        $maxlength = "";
    }
    if (ctype_digit(trim((string) $aQuestionAttributes['input_size']))) {
        $inputsize = trim((string) $aQuestionAttributes['input_size']);
        $extraclass .= " ls-input-sized";
    } else {
        $inputsize = null;
    }
    if (trim((string) $aQuestionAttributes['placeholder'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']]) != '') {
        $placeholder = $aQuestionAttributes['placeholder'][$_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang']];
    } else {
        $placeholder = '';
    }
    if ($aQuestionAttributes['numbers_only'] == 1) {
        $checkconditionFunction = "fixnum_checkconditions";

        if (in_array($aQuestionAttributes['show_totals'], array("R", "C", "B"))) {
            $q_table_id      = 'totals_' . $ia[0];
            $q_table_id_HTML = ' id="' . $q_table_id . '"';
        }

        $coreClass .= " number-array";
        $coreRowClass .= " number-list";
        $caption    .= gT("Each answer may only be a number.");
        $col_head    = '';
        switch ($aQuestionAttributes['show_totals']) {
            case 'R':
                $totals_class   = $show_totals = 'rowTotals';
                $row_total      = doRender(
                    '/survey/questions/answer/arrays/texts/rows/cells/td_total',
                    array('empty' => false, 'inputsize' => $inputsize, 'basename' => $ia[1]),
                    true
                );
                $col_head       = doRender(
                    '/survey/questions/answer/arrays/texts/rows/cells/thead',
                    array('totalText' => gT('Total'), 'classes' => '', 'basename' => $ia[1]),
                    true
                );

                if ($show_grand == true) {
                    $row_head    = doRender(
                        '/survey/questions/answer/arrays/texts/rows/cells/thead',
                        array('totalText' => gT('Grand total'), 'classes' => 'answertext', 'basename' => $ia[1]),
                        true
                    );
                    $col_total   = doRender(
                        '/survey/questions/answer/arrays/texts/columns/col_total',
                        array('empty' => true, 'inputsize' => $inputsize, 'basename' => $ia[1]),
                        true
                    );
                    $grand_total = doRender(
                        '/survey/questions/answer/arrays/texts/rows/cells/td_grand_total',
                        array('empty' => false, 'inputsize' => $inputsize, 'basename' => $ia[1]),
                        true
                    );
                };
                $caption .= gT("The last row shows the total for the column.");
                break;

            case 'C':
                $totals_class = $show_totals = 'col';
                $col_total    = doRender(
                    '/survey/questions/answer/arrays/texts/columns/col_total',
                    array('empty' => false, 'inputsize' => $inputsize, 'label' => true, 'basename' => $ia[1]),
                    true
                );
                $row_head     = doRender(
                    '/survey/questions/answer/arrays/texts/rows/cells/thead',
                    array('totalText' => gT('Total'), 'classes' => 'answertext', 'basename' => $ia[1]),
                    true
                );

                if ($show_grand == true) {
                    $row_total   = doRender(
                        '/survey/questions/answer/arrays/texts/rows/cells/td_total',
                        array('empty' => true, 'inputsize' => $inputsize, 'basename' => $ia[1]),
                        true
                    );
                    $col_head    = doRender(
                        '/survey/questions/answer/arrays/texts/rows/cells/thead',
                        array('totalText' => gT('Grand total'), 'classes' => '', 'basename' => $ia[1]),
                        true
                    );
                    $grand_total = doRender(
                        '/survey/questions/answer/arrays/texts/rows/cells/td_grand_total',
                        array('empty' => false, 'inputsize' => $inputsize, 'basename' => $ia[1]),
                        true
                    );
                };
                $caption .= gT("The last column shows the total for the row.");
                break;

            case 'B':
                $totals_class = $show_totals = 'both';
                $row_total    = doRender(
                    '/survey/questions/answer/arrays/texts/rows/cells/td_total',
                    array('empty' => false, 'inputsize' => $inputsize, 'basename' => $ia[1]),
                    true
                );
                $col_total    = doRender(
                    '/survey/questions/answer/arrays/texts/columns/col_total',
                    array('empty' => false, 'inputsize' => $inputsize, 'label' => false, 'basename' => $ia[1]),
                    true
                );
                $col_head     = doRender(
                    '/survey/questions/answer/arrays/texts/rows/cells/thead',
                    array('totalText' => gT('Total'), 'classes' => '', 'basename' => $ia[1]),
                    true
                );
                $row_head     = doRender(
                    '/survey/questions/answer/arrays/texts/rows/cells/thead',
                    array('totalText' => gT('Total'), 'classes' => 'answertext', 'basename' => $ia[1]),
                    true
                );

                if ($show_grand == true) {
                    $grand_total = doRender(
                        '/survey/questions/answer/arrays/texts/rows/cells/td_grand_total',
                        array('empty' => false, 'inputsize' => $inputsize, 'basename' => $ia[1]),
                        true
                    );
                } else {
                    $grand_total = doRender(
                        '/survey/questions/answer/arrays/texts/rows/cells/td_grand_total',
                        array('empty' => true, 'inputsize' => $inputsize, 'basename' => $ia[1]),
                        true
                    );
                };
                $caption .= gT("The last row shows the total for the column and the last column shows the total for the row.");
                break;
        };

        if (!empty($totals_class)) {
            $totals_class = ' show-totals ' . $totals_class;

            if ($aQuestionAttributes['show_grand_total']) {
                $totals_class  .= ' grand';
                $show_grand     = true;
            };
        };
    }

    if (ctype_digit(trim((string) $aQuestionAttributes['answer_width']))) {
        $answerwidth = trim((string) $aQuestionAttributes['answer_width']);
        $defaultWidth = false;
    } else {
        $answerwidth = 33;
        $defaultWidth = true;
    }
    $columnswidth = 100 - ($answerwidth);

    $question = Question::model()->findByPk($ia[0]);
    $orderingService = \LimeSurvey\DI::getContainer()->get(
        \LimeSurvey\Models\Services\QuestionOrderingService\QuestionOrderingService::class
    );

    $sSurveyLanguage = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang'];
    $aSubquestionsX = $orderingService->getOrderedSubQuestions($question, 1, $sSurveyLanguage);
    $labelans     = [];
    $labelans2    = [];

    foreach ($aSubquestionsX as $oSubquestion) {
        $labelans[$oSubquestion->qid] = [
            'label' => $oSubquestion->questionl10ns[$sSurveyLanguage]->question,
            'title' => $oSubquestion->title
        ];
        $labelans2[$oSubquestion->title] = $oSubquestion->questionl10ns[$sSurveyLanguage]->question;
    }

    if ($numrows = count($labelans)) {
        // There are no "No answer" column
        if (($show_grand == true && $show_totals == 'col') || $show_totals == 'rowTotals' || $show_totals == 'both') {
            ++$numrows;
        }

        $cellwidth = $columnswidth / $numrows;

        $iCount = Question::model()->with(array('questionl10ns' => array('condition' => "question like '%|%'")))->count('parent_qid=:parent_qid AND scale_id=0', array(':parent_qid' => $ia[0]));
        if ($iCount > 0) {
            $right_exists = true;
            if (!$defaultWidth) {
                $answerwidth = $answerwidth / 2;
            }
        } else {
            $right_exists = false;
        }


        $sSurveyLanguage = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang'];
        // Get questions and answers by defined order via ordering service (respects keep_codes_order)
        $aQuestionsY = $orderingService->getOrderedSubQuestions($question, 0, $sSurveyLanguage);
        $anscount   = count($aQuestionsY);
        $fn         = 1;

        $showGrandTotal = (($show_grand == true && $show_totals == 'col') || $show_totals == 'rowTotals' || $show_totals == 'both') ? true : false;

        $sRows = '';
        $answertext = '';
        foreach ($aQuestionsY as $j => $ansrow) {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn - 1) > 0 && ($fn - 1) % $repeatheadings == 0) {
                if (($anscount - $fn + 1) >= $minrepeatheadings) {
                    // Close actual body and open another one
                    $sRows .= doRender('/survey/questions/answer/arrays/texts/rows/repeat_header', array(
                        'basename'     => $ia[1],
                        'answerwidth'  => $answerwidth,
                        'labelans'     => $labelans2,
                        'right_exists' => $right_exists,
                        'col_head'     => $col_head,
                    ), true);
                }
            }

            $myfname = $ia[1] . "_S" . $ansrow['qid'];
            $answertext = $ansrow->questionl10ns[$sSurveyLanguage]->question;
            $answertextsave = $answertext;
            $error = false;

            if (($ia[6] == 'Y' || $ia[6] == 'S') && !empty($aMandatoryViolationSubQ)) {
                //Go through each labelcode and check for a missing answer! If any are found, highlight this line
                $emptyresult = 0;
                foreach ($labelans as $qid => $aLabel) {
                    $myfname2 = $myfname . '_S' . $qid;
                    if (in_array($myfname2, $aMandatoryViolationSubQ)) {
                        $emptyresult = 1;
                    }
                }
                $error = false;
                if ($emptyresult == 1) {
                    $error = true;
                }
            }
            $value = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname] ?? '';

            if (strpos((string) $answertext, '|') !== false) {
                $answertext = (string) substr((string) $answertext, 0, strpos((string) $answertext, '|'));
            }

            $answer_tds = '';

            foreach ($labelans as $qid => $aLabel) {
                $title = $aLabel['title'];
                $label = $aLabel['label'];
                $myfname2 = $myfname . "_S$qid";
                $myfname2value = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname2] ?? "";

                if ($aQuestionAttributes['numbers_only'] == 1) {
                    $myfname2value = str_replace('.', $sSeparator, (string) $myfname2value);
                }

                $inputnames[] = $myfname2;
                $value        = str_replace('"', "'", str_replace('\\', '', (string) $myfname2value));
                $answer_tds  .= doRender('/survey/questions/answer/arrays/texts/rows/cells/answer_td', array(
                    'ld'         => $title,
                    'basename'   => $ia[1],
                    'myfname2'   => $myfname2,
                    'labelText'  => $label,
                    'kpclass'    => $kpclass,
                    'maxlength'  => $maxlength,
                    'inputsize'  => $inputsize,
                    'value'      => $myfname2value,
                    'placeholder' => $placeholder,
                    'isNumber'   => $isNumber,
                    'isInteger'  => $isInteger,
                    'error'      => ($error && $myfname2value === ''),
                ), true);
            }

            $rightTd = $rightTdEmpty = false;

            if (strpos((string) $answertextsave, '|') !== false) {
                $answertext = (string) substr((string) $answertextsave, strpos((string) $answertextsave, '|') + 1);
                $rightTd    = true;
                $rightTdEmpty = false;
            } elseif ($right_exists) {
                $rightTd      = true;
                $rightTdEmpty = true;
            }
            $formatedRowTotal = str_replace(
                array('[[ROW_CODE]]', '[[ROW_NAME]]'),
                array($title, LimeExpressionManager::ProcessString($answertext, $ia[0])),
                strval($row_total)
            );
            $sRows .= doRender('/survey/questions/answer/arrays/texts/rows/answer_row', array(
                'myfname'           =>  $myfname,
                'basename'          => $ia[1],
                'coreRowClass'      => $coreRowClass,
                'answertext'        => $answertext,
                'error'             => $error,
                'value'             => $value,
                'placeholder'       => $placeholder,
                'answer_tds'        => $answer_tds,
                'rightTd'           => $rightTd,
                'rightTdEmpty'      => $rightTdEmpty,
                'answerwidth'       => $answerwidth,
                'formatedRowTotal'  => $formatedRowTotal,
                'odd'               => ($j % 2),
            ), true);

            $fn++;
        }

        $showtotals = false;
        $total = '';

        if ($show_totals == 'col' || $show_totals == 'both' || $grand_total !== '') {
            $showtotals = true;
            foreach ($labelans as $qid => $aLabel) {
                $total .= str_replace(
                    array('[[COL_CODE]]', '[[COL_NAME]]'),
                    array($title, LimeExpressionManager::ProcessString($aLabel['label'], $ia[0])),
                    strval($col_total)
                );
            }
            $total .= $grand_total;
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
            'basename'                  => $ia[1],
            'answerwidth'               => $answerwidth,
            'col_head'                  => $col_head,
            'cellwidth'                 => $cellwidth,
            'labelans'                  => $labelans2,
            'right_exists'              => $right_exists,
            'showGrandTotal'            => $showGrandTotal,
            'q_table_id_HTML'           => $q_table_id_HTML,
            'coreClass'                 => $coreClass,
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
        $answer = doRender('/survey/questions/answer/arrays/texts/empty_error', [], true);
    }
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
// Used by array numbers, array_numbers (for searching)
function do_array_multiflexi($ia)
{
    global $thissurvey;

    $inputnames                 = [];
    $aLastMoveResult            = LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ    = ($aLastMoveResult['mandViolation'] && ($ia[6] == 'Y' || $ia[6] == 'S')) ? explode("|", (string) $aLastMoveResult['unansweredSQs']) : [];
    $repeatheadings             = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings          = Yii::app()->getConfig("minrepeatheadings");
    $coreClass                  = "ls-answers subquestion-list questions-list";
    $coreRowClass = "subquestion-list questions-list";
    $extraclass                 = "";
    $answertypeclass            = "";
    $caption                    = gT("A table of subquestions on each cell. The subquestion texts are in the colum header and concern the row header.");
    $checkconditionFunction     = "fixnum_checkconditions";

    /*
    * Question Attributes
    */
    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

    // Define min and max value
    $minvalue = 1;
    $maxvalue = 10;
    if (trim((string) $aQuestionAttributes['multiflexible_max']) != '' && trim((string) $aQuestionAttributes['multiflexible_min']) == '') {
        $maxvalue = $aQuestionAttributes['multiflexible_max'];
        $minvalue = 1;
    }
    if (trim((string) $aQuestionAttributes['multiflexible_min']) != '' && trim((string) $aQuestionAttributes['multiflexible_max']) == '') {
        $minvalue = $aQuestionAttributes['multiflexible_min'];
        $maxvalue = $aQuestionAttributes['multiflexible_min'] + 10;
    }
    if (trim((string) $aQuestionAttributes['multiflexible_min']) != '' && trim((string) $aQuestionAttributes['multiflexible_max']) != '') {
        if ($aQuestionAttributes['multiflexible_min'] < $aQuestionAttributes['multiflexible_max']) {
            $minvalue = $aQuestionAttributes['multiflexible_min'];
            $maxvalue = $aQuestionAttributes['multiflexible_max'];
        }
    }

    $stepvalue = (trim((string) $aQuestionAttributes['multiflexible_step']) != '' && $aQuestionAttributes['multiflexible_step'] > 0) ? $aQuestionAttributes['multiflexible_step'] : 1;

    if ($aQuestionAttributes['reverse'] == 1) {
        $tmp = $minvalue;
        $minvalue = $maxvalue;
        $maxvalue = $tmp;
        $reverse = true;
        $stepvalue = -$stepvalue;
    } else {
        $reverse = false;
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
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . "array-number-checkbox.js", CClientScript::POS_BEGIN);
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
        $answertypeclass     = " ls-dropdown-item";

        $coreClass          .= " dropdown-array";
        $coreRowClass .= " dropdown-list";
        $caption            .= gT("Please select an answer for each combination.");
    }

    if (ctype_digit(trim((string) $aQuestionAttributes['repeat_headings'])) && trim((string) $aQuestionAttributes['repeat_headings']) != "") {
        $repeatheadings     = intval($aQuestionAttributes['repeat_headings']);
        $minrepeatheadings  = 0;
    }

    if (intval(trim((string) $aQuestionAttributes['maximum_chars'])) > 0) {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maxlength = intval(trim((string) $aQuestionAttributes['maximum_chars']));
        $extraclass .= " ls-input-maxchars"; // @todo : move to data or fix class
    } else {
        $maxlength = "";
    }
    if (ctype_digit(trim((string) $aQuestionAttributes['input_size']))) {
        $inputsize = trim((string) $aQuestionAttributes['input_size']);
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

    if (ctype_digit(trim((string) $aQuestionAttributes['answer_width']))) {
        $answerwidth = trim((string) $aQuestionAttributes['answer_width']);
        $defaultWidth = false;
    } else {
        $answerwidth = 33;
        $defaultWidth = false;
    }

    $columnswidth   = 100 - ($answerwidth);
    $question = Question::model()->findByPk($ia[0]);
    $orderingService = \LimeSurvey\DI::getContainer()->get(
        \LimeSurvey\Models\Services\QuestionOrderingService\QuestionOrderingService::class
    );
    $sSurveyLanguage = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang'];
    $aQuestions = $orderingService->getOrderedSubQuestions($question, 1, $sSurveyLanguage);
    $labelans       = [];
    $labelcode      = [];
    $labeltitle     = [];

    foreach ($aQuestions as $lrow) {
        $labelans[]  = $lrow->questionl10ns[$sSurveyLanguage]->question;
        $labelcode[] = [
            'title' => $lrow['title'],
            'qid' => $lrow['qid']
        ];
        $labeltitle[] = $lrow['title'];
    }

    if ($numrows = count($labelans)) {
        // There are no "No answer" column
        $cellwidth  = $columnswidth / $numrows;
        $iCount = Question::model()->with(array('questionl10ns' => array('condition' => "question like '%|%'")))->countByAttributes([], 'parent_qid=:parent_qid AND scale_id=0', array(':parent_qid' => $ia[0]));
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($iCount > 0) {
            $right_exists = true;
            if (!$defaultWidth) {
                $answerwidth = $answerwidth / 2;
            }
        } else {
            $right_exists = false;
        }

        $sSurveyLanguage = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['s_lang'];
        // Get questions and answers by defined order via ordering service (respects keep_codes_order)
        $aSubquestions = $orderingService->getOrderedSubQuestions($question, 0, $sSurveyLanguage);


        if (trim((string) $aQuestionAttributes['parent_order']) != '') {
            $iParentQID = (int) $aQuestionAttributes['parent_order'];
            $aResult    = [];
            $sessionao  = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['answer_order'] ?? [];

            if (isset($sessionao[$iParentQID])) {
                foreach ($sessionao[$iParentQID] as $aOrigRow) {
                    $sCode = $aOrigRow['title'];

                    foreach ($aSubquestions as $aRow) {
                        if ($sCode == $aRow['title']) {
                            $aResult[] = $aRow;
                        }
                    }
                }
                $aSubquestions = $aResult;
            }
        }
        $anscount = count($aSubquestions);
        $fn = 1;

        $sAnswerRows = '';
        foreach ($aSubquestions as $j => $aSubquestion) {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn - 1) > 0 && ($fn - 1) % $repeatheadings == 0) {
                if (($anscount - $fn + 1) >= $minrepeatheadings) {
                    $sAnswerRows .= doRender('/survey/questions/answer/arrays/multiflexi/rows/repeat_header', array(
                        'basename'      => $ia[1],
                        'labelans'      =>  $labelans,
                        'labelcode'     =>  $labeltitle,
                        'right_exists'  =>  $right_exists,
                        'cellwidth'     =>  $cellwidth,
                        'answerwidth'   =>  $answerwidth,
                        'textAlignment' => $textAlignment,
                    ), true);
                }
            }

            $myfname        = $ia[1] . "_S" . $aSubquestion['qid'];
            $answertext     = $aSubquestion->questionl10ns[$sSurveyLanguage]->question;
            $answertextsave = $answertext;

            /* Check the sub Q mandatory violation */
            $error = false;

            if (($ia[6] == 'Y' || $ia[6] == 'S') && !empty($aMandatoryViolationSubQ)) {
                //Go through each labelcode and check for a missing answer! Default :If any are found, highlight this line, checkbox : if one is not found : don't highlight
                // PS : we really need a better system : event for EM !
                $emptyresult = ($aQuestionAttributes['multiflexible_checkbox'] != 0) ? 1 : 0;

                foreach ($labelcode as $ld) {
                    $myfname2 = $myfname . '_S' . $ld['qid'];
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

                $error = ($emptyresult == 1) ? true : false;
            }

            $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
            $sSeparator = $sSeparator['separator'];

            // Get array_filter stuff
            $sDisplayStyle = return_display_style($ia, $aQuestionAttributes, $thissurvey, $myfname);


            if (strpos((string) $answertext, '|') !== false) {
                $answertext = (string) substr((string) $answertext, 0, strpos((string) $answertext, '|'));
            }

            $row_value = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname] ?? '';

            $thiskey            = 0;
            $answer_tds         = '';

            foreach ($labelcode as $i => $ld) {
                $myfname2   = $myfname . "_S{$ld['qid']}";
                $value      = $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname2] ?? '';

                // Possibly replace '.' with ','
                $surveyId = Yii::app()->getConfig('surveyID');
                $surveyLabel = 'responses_' . $surveyId;
                $fieldnameIsNumeric = isset($_SESSION[$surveyLabel][$myfname2])
                    && is_numeric($_SESSION[$surveyLabel][$myfname2]);
                if ($fieldnameIsNumeric) {
                    $value = str_replace('.', $sSeparator, (string) $_SESSION[$surveyLabel][$myfname2]);
                }

                if ($checkboxlayout === false) {
                    $answer_tds .= doRender('/survey/questions/answer/arrays/multiflexi/rows/cells/answer_td', array(
                        'basename'                  => $ia[1],
                        'dataTitle'                 => $labelans[$i],
                        'dataCode'                  => $labeltitle[$i],
                        'ld'                        => $ld['title'],
                        'answertypeclass'           => $answertypeclass,
                        'answertext'                => $answertext,
                        'stepvalue'                 => $stepvalue,
                        'extraclass'                => $extraclass,
                        'myfname2'                  => $myfname2,
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
                    if (isset($_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname2]) && $_SESSION['responses_' . Yii::app()->getConfig('surveyID')][$myfname2] == '1') {
                        $myvalue    = '1';
                        $setmyvalue = CHECKED;
                    } else {
                        $myvalue    = '';
                        $setmyvalue = '';
                    }

                    $answer_tds .= doRender('/survey/questions/answer/arrays/multiflexi/rows/cells/answer_td_checkboxes', array(
                        'basename'                  => $ia[1],
                        'dataTitle'                 => $labelans[$i],
                        'dataCode'                  => $labelcode[$i]['title'],
                        'ld'                        => $ld['title'],
                        'answertypeclass'           => $answertypeclass,
                        'value'                     => $myvalue,
                        'setmyvalue'                => $setmyvalue,
                        'myfname2'                  => $myfname2,
                        'checkconditionFunction'    => $checkconditionFunction,
                        'extraclass'                => $extraclass,
                        'qid'                       => $ld['qid'],
                    ), true);
                    $inputnames[] = $myfname2;
                    $thiskey++;
                }
            }

            $rightTd = false;
            $answertextright = '';

            if (strpos((string) $answertextsave, '|')) {
                $answertextright    = substr((string) $answertextsave, strpos((string) $answertextsave, '|') + 1);
                $rightTd            = true;
            } elseif ($right_exists) {
                $rightTd = true;
            }

            $sAnswerRows .= doRender('/survey/questions/answer/arrays/multiflexi/rows/answer_row', array(
                'basename'          => $ia[1],
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
            'basename'          => $ia[1],
            'extraclass'        => $extraclass,
            'answerwidth'       => $answerwidth,
            'labelans'          => $labelans,
            'labelcode'         => $labeltitle,
            'cellwidth'         => $cellwidth,
            'right_exists'      => $right_exists,
            'sAnswerRows'       => $sAnswerRows,
            'textAlignment'     => $textAlignment,
        ), true);
    } else {
        $answer     = doRender('/survey/questions/answer/arrays/multiflexi/empty_error', [], true);
        $inputnames = '';
    }
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
/**
 * Renders array by column question type.
 * @param array $ia
 * @return array
 * @throws CException
 */
function do_arraycolumns($ia)
{
    $aLastMoveResult = LimeExpressionManager::GetLastMoveResult();
    $YorNorSvalue = $ia[6];
    $isYes = ($YorNorSvalue == 'Y');
    $isS   = ($YorNorSvalue == 'S');

    if ($aLastMoveResult['mandViolation'] && ($isYes || $isS)) {
        $aMandatoryViolationSubQ = explode('|', (string) $aLastMoveResult['unansweredSQs']);
    } else {
        $aMandatoryViolationSubQ = [];
    }

    $coreClass = "ls-answers subquestion-list questions-list array-radio";
    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($ia[0]);

    $sSurveyLanguage = $_SESSION['responses_' . App()->getConfig('surveyID')]['s_lang'];
    $aAnswers = Answer::model()->findAll(array('order' => 'sortorder, code', 'condition' => 'qid=:qid AND scale_id=0', 'params' => array(':qid' => $ia[0])));

    $labelans = [];
    $labelcode = [];
    $labels = [];

    foreach ($aAnswers as $lrow) {
        $labelans[] = $lrow->answerl10ns[$sSurveyLanguage]->answer;
        $labelcode[] = $lrow['code'];
        $labels[] = array("answer" => $lrow->answerl10ns[$sSurveyLanguage]->answer, "code" => $lrow['code'], "aid" => $lrow->aid);
    }

    $inputnames = [];
    if (count($labelans) > 0) {
        if (($ia[6] != 'Y' && $ia[6] != 'S') && SHOW_NO_ANSWER == 1) {
            $labelcode[] = '';
            $labelans[] = gT('No answer');
            $labels[] = array('answer' => gT('No answer'), 'code' => '');
        }

        $question = Question::model()->findByPk($ia[0]);
        $orderingService = \LimeSurvey\DI::getContainer()->get(
            \LimeSurvey\Models\Services\QuestionOrderingService\QuestionOrderingService::class
        );
        $aQuestions = $orderingService->getOrderedSubQuestions($question, 0, $sSurveyLanguage);
        $anscount = count($aQuestions);

        $aData = [];
        $aData['labelans']  = $labelans;
        $aData['labelcode'] = $labelcode;

        if ($anscount > 0) {
            if (ctype_digit(trim((string) $aQuestionAttributes['answer_width_bycolumn']))) {
                $answerwidth = trim((string) $aQuestionAttributes['answer_width_bycolumn']);
                $defaultWidth = false;
            } else {
                $answerwidth = 33;
                $defaultWidth = false;
            }
            $cellwidth = (100 - $answerwidth) / $anscount;

            $aData['anscount']    = $anscount;
            $aData['cellwidth']   = $cellwidth;
            $aData['answerwidth'] = $answerwidth;
            $aData['aQuestions']  = [];

            foreach ($aQuestions as $aQuestion) {
                $aData['aQuestions'][] = array_merge($aQuestion->attributes, $aQuestion->questionl10ns[$sSurveyLanguage]->attributes);
            }

            $anscode = [];
            $answers = [];

            foreach ($aQuestions as $ansrow) {
                $anscode[] = $ansrow['qid'];
                $answers[] = $ansrow->questionl10ns[$sSurveyLanguage]->question;
            }

            $aData['anscode'] = $anscode;
            $aData['answers'] = $answers;

            $iAnswerCount = count($answers);
            for ($_i = 0; $_i < $iAnswerCount; ++$_i) {
                $myfname = $ia[1] . "_S" . $aQuestions[$_i]->qid;
                /* Check the Sub Q mandatory violation */
                if (($ia[6] == 'Y' || $ia[6] == 'S') && in_array($myfname, $aMandatoryViolationSubQ)) {
                    $aData['aQuestions'][$_i]['errormandatory'] = true;
                } else {
                    $aData['aQuestions'][$_i]['errormandatory'] = false;
                }
            }

            $aData['labels'] = $labels;
            $aData['checkconditionFunction'] = $checkconditionFunction;

            // TODO: What is this? What is happening here?
            foreach ($labels as $labelIdx => $ansrow) {

                // create the html ids for the table rows, which are
                // the answer options for this question type
                $aData['labels'][$labelIdx]['myfname'] = $ia[1];
                if (isset($ansrow['aid'])) {
                    $aData['labels'][$labelIdx]['myfname'] .= "_S" . $ansrow['aid'];
                }

                // AnswerCode
                foreach ($anscode as $j => $ld) {
                    $myfname = $ia[1] . "_S" . $ld;
                    $aData['aQuestions'][$j]['myfname'] = $myfname;
                    if (
                        isset($_SESSION['responses_' . App()->getConfig('surveyID')][$myfname]) &&
                        $_SESSION['responses_' . App()->getConfig('surveyID')][$myfname] === $ansrow['code']
                    ) {
                        $aData['checked'][$ansrow['code']][$ld] = CHECKED;
                    } elseif (
                        !isset($_SESSION['responses_' . App()->getConfig('surveyID')][$myfname]) &&
                        $ansrow['code'] == ''
                    ) {
                        $aData['checked'][$ansrow['code']][$ld] = CHECKED;
                        // Humm.. (by lemeur), not sure this section can be reached
                        // because I think $_SESSION['responses_'.Yii::app()->getConfig('surveyID')][$myfname] is always set (by save.php ??) !
                        // should remove the !isset part I think !!
                    } else {
                        $aData['checked'][$ansrow['code']][$ld] = "";
                    }
                }
            }

            // Whats happening here?
            foreach ($anscode as $j => $ld) {
                $myfname = $ia[1] . "_S" . $ld;

                if (isset($_SESSION['responses_' . App()->getConfig('surveyID')][$myfname])) {
                    $aData['aQuestions'][$j]['myfname_value'] = $_SESSION['responses_' . App()->getConfig('surveyID')][$myfname];
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
            $answer = '<p class="error">' . gT('Error: There are no answers defined for this question.') . "</p>";
            $inputnames = "";
        }
    } else {
        $answer = "<p class='error'>" . gT("Error: There are no answer options for this question and/or they don't exist in this language.") . "</p>\n";
        $inputnames = '';
    }
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
            return $dateString . '-01-01 00:00';
        // Year and month
        case 7:
            return $dateString . '-01 00:00';
        // Year, month and day
        case 10:
            return $dateString . ' 00:00';
        // Year, month day and hour
        case 13:
            return $dateString . ':00';
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
