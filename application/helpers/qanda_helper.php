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
    if (Yii::app()->getConfig('shownoanswer') == 2) {
        if ($thissurvey['shownoanswer'] == 'N') {
            define('SHOW_NO_ANSWER', 0);
        } else {
            define('SHOW_NO_ANSWER', 1);
        }
    } elseif (Yii::app()->getConfig('shownoanswer') == 1) {
        define('SHOW_NO_ANSWER', 1);
    } elseif (Yii::app()->getConfig('shownoanswer') == 0) {
        define('SHOW_NO_ANSWER', 0);
    } else {
        define('SHOW_NO_ANSWER', 1);
    }

    // Default to the historic behaviour when rendering legacy/imported data
    // that does not contain the setting yet.
    define('PRESELECT_NO_ANSWER', ($thissurvey['preselectnoanswer'] ?? 'Y') === 'Y' ? 1 : 0);
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

    if (!(($_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['step'] != $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['maxstep']) || ($_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['step'] == $_SESSION['responses_' . Yii::app()->getConfig('surveyID')]['prevstep']))) {
        $isValid = true; // don't want to show any validation messages.
    }

    /* File upload errors are shown in the file_validation_popup, kept for templates using it */
    $question_text['file_valid_message'] = '';

    if (!empty($question_text['man_message']) || !$isValid) {
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
        // This function is called for every question on the page, so only soft ('S') and hard ('Y')
        // mandatory questions may set the message; non-mandatory questions must leave it untouched.
        // If there is no "hard" mandatory violation (both current and previous violations belong to Soft Mandatory questions),
        // we show the soft mandatory message.
        if ($ia[6] == 'S' && (!isset($mandatorypopup) || $mandatorypopup == 'S')) {
            $popup = gT("One or more mandatory questions have not been answered. If possible, please complete them before continuing to the next page.");
            $mandatorypopup = "S";
        } elseif ($ia[6] == 'Y' && !isset($mandatorypopup) && ($ia[4] == 'T' || $ia[4] == 'S' || $ia[4] == 'U')) {
            $popup = gT("You cannot proceed until you enter some text for one or more questions.");
            $mandatorypopup = "Y";
        } elseif ($ia[6] == 'Y') {
            $popup = gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.");
            $mandatorypopup = "Y";
        }
        return array(
            isset($mandatorypopup) ? $mandatorypopup : false,
            isset($popup) ? $popup : false
        );
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
    if ($time_limit <= 0) {
        return;
    }
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts") . 'coookies.js', CClientScript::POS_BEGIN);
    Yii::app()->getClientScript()->registerPackage('timer-addition');

    $questionId = $ia[0];
    $surveyId = Yii::app()->getConfig('surveyID');
    /**
     * The following lines cover for previewing questions, because no $_SESSION['responses_'.$surveyId]['fieldarray'] exists.
     * This just stops error messages occurring
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

// ==================================================================
// QUESTION METHODS =================================================

/**
 * Render the question view.
 *
 * By default, it just renders the required core view from application/views/survey/...
 * If the user uploaded a question template to the upload directory and applied it to the question's display settings, this function will check whether the required view exists in that directory
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
