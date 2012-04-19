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

    // TMSW - eliminate this - get from LEM
    //A bit of housekeeping to stop PHP Notices
    $answer = "";
    if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]])) {$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] = "";}
    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    //Create the question/answer html

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
            if (count($values[1]) > 1 && $aQuestionAttributes['hide_tip']==0)
            {
                $question_text['help'] = $clang->gT("Click on an item in the list on the left, starting with your highest ranking item, moving through to your lowest ranking item.");
                if (trim($aQuestionAttributes['min_answers'])!='')
                {
                    $qtitle .= "<br />\n<span class=\"questionhelp\">"
                    . sprintf($clang->ngT("Check at least %d item.","Check at least %d items.",$aQuestionAttributes['min_answers']),$aQuestionAttributes['min_answers'])."</span>";
                    $question_text['help'] .=' '.sprintf($clang->ngT("Check at least %d item.","Check at least %d items.",$aQuestionAttributes['min_answers']),$aQuestionAttributes['min_answers']);
                }
            }
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
                //                        . sprintf($clang->gT("Check at least %d answers"), $minansw)."</span>";
                //                        $question_text['help'] = sprintf($clang->gT("Check at least %d answers"), $minansw);
                //                    }
                //                }
            }
            break;
        case '|': //File Upload
            $values=do_file_upload($ia);
            if ($aQuestionAttributes['min_num_of_files'] != 0)
            {
                if (trim($aQuestionAttributes['min_num_of_files']) != 0)
                {
                    $qtitle .= "<br />\n<span class = \"questionhelp\">"
                    .sprintf($clang->gT("At least %d files must be uploaded for this question"), $aQuestionAttributes['min_num_of_files'])."<span>";
                    $question_text['help'] .= ' '.sprintf($clang->gT("At least %d files must be uploaded for this question"), $aQuestionAttributes['min_num_of_files']);
                }
            }
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
    global $showpopups;

    $clang = Yii::app()->lang;
    //This sets the mandatory popup message to show if required
    //Called from question.php, group.php or survey.php
    if ($notanswered === null) {unset($notanswered);}
    if (isset($notanswered) && is_array($notanswered) && isset($showpopups) && $showpopups == 1) //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
    {
        global $mandatorypopup, $popup;
        //POPUP WARNING
        if (!isset($mandatorypopup) && ($ia[4] == 'T' || $ia[4] == 'S' || $ia[4] == 'U'))
        {
            $popup="<script type=\"text/javascript\">\n
            <!--\n $(document).ready(function(){
            alert(\"".$clang->gT("You cannot proceed until you enter some text for one or more questions.", "js")."\");});\n //-->\n
            </script>\n";
            $mandatorypopup="Y";
        }else
        {
            $popup="<script type=\"text/javascript\">\n
            <!--\n $(document).ready(function(){
            alert(\"".$clang->gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.", "js")."\");});\n //-->\n
            </script>\n";
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
function file_validation_popup($ia, $filenotvalidated = null)
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

function return_timer_script($aQuestionAttributes, $ia, $disable=null) {
    global $thissurvey;

    $clang = Yii::app()->lang;

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
            foreach($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['fieldarray'] as $ib)
            {
                if($ib[5] == $gid)
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
    if (isset($_SESSION['relevanceStatus'][$rowname]) && !$_SESSION['relevanceStatus'][$rowname])
    {
        $htmltbody2 .= " style='display: none'";
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
    global $js_header_includes;
    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    $answer='';

    if (trim($aQuestionAttributes['time_limit'])!='')
    {
        $js_header_includes[] = '/scripts/coookies.js';
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
    $answer .= '".>';
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
        $answer .= "\t<li class=\"answer-item radio-item noanswer-item\">\n<input class=\"radio\" type=\"radio\" name=\"$ia[1]\" id=\"NoAnswer\" value=\"\"";
        if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]))
        {
            $answer .= CHECKED;
        }
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer".$ia[1]."NANS\" class=\"answertext\">".$clang->gT('No answer')."</label>\n\t</li>\n";

    }
    $answer .= "</ul>\n<input type=\"hidden\" name=\"java$ia[1]\" id=\"java$ia[1]\" value=\"".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]."\" />\n";
    $inputnames[]=$ia[1];
    if($aQuestionAttributes['slider_rating']==1){
        $css_header_includes[]= '/admin/scripts/rating/jquery.rating.css';
        $js_header_includes[]='/admin/scripts/rating/jquery.rating.js';
        $answer.="
        <script type=\"text/javascript\">
        document.write('";
        $answer.='<ul id="'.$id.'div" class="answers-list stars-wrapper"><li class="item-list answer-star"><input type="radio" id="stars1" name="stars" class="'.$id.'st" value="1"/></li><li class="item-list answer-star"><input type="radio" id="stars2" name="stars" class="'.$id.'st" value="2"/></li><li class="item-list answer-star"><input type="radio" name="stars" id="stars3" class="'.$id.'st" value="3"/></li><li class="item-list answer-star"><input type="radio" id="stars4" name="stars" class="'.$id.'st" value="4"/></li><li class="item-list answer-star"><input type="radio" name="stars" id="stars5" class="'.$id.'st" value="5"/></li><li class="item-list answer-star"></u>';
        $answer.="');
        </script>
        ";
        $answer.="
        <script type=\"text/javascript\">
        $('#$id').hide();
        var checked = $('#$id input:checked').attr('value');
        if(checked!=''){
        $('#stars'+checked).attr('checked','checked');
        }
        $('.{$id}st').rating({
        callback: function(value,link){
        if(value==undefined || value==''){
        $('#$id input').each(function(){ $(this).removeAttr('checked');});
        $('#{$id} #NoAnswer').attr('checked','checked');
        }
        else{
        $('#$id input').each(function(){ $(this).removeAttr('checked');});
        $('#answer$ia[1]'+value).attr('checked','checked');
        }
        }

        });
        </script>
        ";
    }

    if($aQuestionAttributes['slider_rating']==2){
        if(!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) OR $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]==''){
            $value=1;
        }else{
            $value=$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]];
        }
        $answer.="
        <script type=\"text/javascript\">
        document.write('";
        $answer.="<div style=\"float:left;\">'+
        '<div style=\"text-align:center; margin-bottom:6px; width:370px;\"><div style=\"width:2%; float:left;\">1</div><div style=\"width:46%;float:left;\">2</div><div style=\"width:4%;float:left;\">3</div><div style=\"width:46%;float:left;\">4</div><div style=\"width:2%;float:left;\">5</div></div><br/>'+
        '<div id=\"{$id}sliderBg\" style=\"background-image:url(\'{$imageurl}/sliderBg.png\'); text-align:center; background-repeat:no-repeat; height:22px; width:396px;\">'+
        '<center>'+
        '<div id=\"{$id}slider\" style=\"width:365px;\"></div>'+
        '</center>'+
        '</div></div>'+
        '<div id=\"{$id}emoticon\" style=\"text-align:left; margin:10px; padding-left:10px;\"><img id=\"{$id}img1\" style=\"margin-left:10px;\" src=\".{$imageurl}/emoticons/{$value}.png\"/><img id=\"{$id}img2\" style=\"margin-left:-31px;margin-top:-31px;\" src=\"{$imageurl}/emoticons/{$value}.png\" />'+
        '</div>";
        $answer.="');
        </script>
        ";
        $answer.="
        <script type=\"text/javascript\">
        $('#$id').hide();
        var value=$value;
        var checked = $('#$id input:checked').attr('value');
        if(checked!=''){
        value=checked;
        }
        var time=200;
        var old=value;
        $('#{$id}slider').slider({
        value: value,
        min: 1,
        max: 5,
        step: 1,
        slide: function(event,ui){
        $('#{$id}img2').attr('src','{$imageurl}/emoticons/'+ui.value+'.png');
        $('#{$id}img2').fadeIn(time);
        $('#$id input').each(function(){ $(this).removeAttr('checked');});
        $('#answer$ia[1]'+ui.value).attr('checked','checked');
        $('#{$id}img1').fadeOut(time,function(){
        $('#{$id}img1').attr('src',$('#{$id}img2').attr('src'));
        $('#{$id}img1').show();
        $('#{$id}img2').hide();
        });
        $checkconditionFunction(ui.value,'$ia[1]','radio');
        }
        });
        $('#{$id}slider a').css('background-image', 'url(\'{$imageurl}/slider.png\')');
        $('#{$id}slider a').css('width', '11px');
        $('#{$id}slider a').css('height', '28px');
        $('#{$id}slider a').css('border', 'none');
        //$('#{$id}slider').css('background-image', 'url(\'{$imageurl}/sliderBg.png\')');
        $('#{$id}slider').css('visibility','hidden');
        $('#{$id}slider a').css('visibility', 'visible');
        </script>
        ";

    }
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_date($ia)
{
    global $thissurvey;

    $clang=Yii::app()->lang;

    $aQuestionAttributes=getQuestionAttributeValues($ia[0],$ia[4]);
    $js_admin_includes = Yii::app()->getConfig("js_admin_includes");
    $js_admin_includes[] = '/scripts/jquery/lime-calendar.js';
    Yii::app()->setConfig("js_admin_includes", $js_admin_includes);

    $checkconditionFunction = "checkconditions";

    $dateformatdetails = getDateFormatData($thissurvey['surveyls_dateformat']);
    $numberformatdatat = getRadixPointData($thissurvey['surveyls_numberformat']);

    if (trim($aQuestionAttributes['dropdown_dates'])!=0) {
        if (!empty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]))
        {
            $datetimeobj = getdate(DateTime::createFromFormat("Y-m-d H:i:s", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]])->getTimeStamp());
            $currentyear = $datetimeobj['year'];
            $currentmonth = $datetimeobj['mon'];
            $currentdate = $datetimeobj['mday'];
            $currenthour = $datetimeobj['hours'];
            $currentminute = $datetimeobj['minutes'];
        } else {
            $currentdate='';
            $currentmonth='';
            $currentyear='';
        }

        $dateorder = preg_split('/[-\.\/ ]/', $dateformatdetails['phpdate']);
        $answer='<p class="question answer-item dropdown-item date-item">';
        foreach($dateorder as $datepart)
        {
            switch($datepart)
            {
                // Show day select box
                case 'j':
                case 'd':   $answer .= ' <label for="day'.$ia[1].'" class="hide">'.$clang->gT('Day').'</label><select id="day'.$ia[1].'" name="day'.$ia[1].'" class="day">
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
                        $answer .= '    <option value="'.sprintf('%02d', $i).'"'.$i_date_selected.'>'.sprintf('%02d', $i)."</option>\n";
                    }
                    $answer .='</select>';
                    break;
                    // Show month select box
                case 'n':
                case 'm':   $answer .= ' <label for="month'.$ia[1].'" class="hide">'.$clang->gT('Month').'</label><select id="month'.$ia[1].'" name="month'.$ia[1].'" class="month">
                    <option value="">'.$clang->gT('Month')."</option>\n";
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
                    for ($i=1; $i<=12; $i++) {
                        if ($i == $currentmonth)
                        {
                            $i_date_selected = SELECTED;
                        }
                        else
                        {
                            $i_date_selected = '';
                        }

                        $answer .= '    <option value="'.sprintf('%02d', $i).'"'.$i_date_selected.'>'.$montharray[$i-1].'</option>';
                    }
                    $answer .= '    </select>';
                    break;
                    // Show year select box
                case 'Y':   $answer .= ' <label for="year'.$ia[1].'" class="hide">'.$clang->gT('Year').'</label><select id="year'.$ia[1].'" name="year'.$ia[1].'" class="year">
                    <option value="">'.$clang->gT('Year').'</option>';

                    /*
                    *  New question attributes used only if question attribute
                    * "dropdown_dates" is used (see IF(...) above).
                    *
                    * yearmin = Minimum year value for dropdown list, if not set default is 1900
                    * yearmax = Maximum year value for dropdown list, if not set default is 2020
                    */
                    if (trim($aQuestionAttributes['dropdown_dates_year_min'])!='')
                    {
                        $yearmin = $aQuestionAttributes['dropdown_dates_year_min'];
                    }
                    else
                    {
                        $yearmin = 1900;
                    }

                    if (trim($aQuestionAttributes['dropdown_dates_year_max'])!='')
                    {
                        $yearmax = $aQuestionAttributes['dropdown_dates_year_max'];
                    }
                    else
                    {
                        $yearmax = 2020;
                    }

                    if ($yearmin > $yearmax)
                    {
                        $yearmin = 1900;
                        $yearmax = 2020;
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
                        $answer .= '  <option value="'.$i.'"'.$i_date_selected.'>'.$i.'</option>';
                    }
                    $answer .= '</select>';

                    break;
            }
        }

        $answer .= '<input class="text" type="text" size="10" name="'.$ia[1].'" style="display: none" id="answer'.$ia[1].'" value="'.$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]].'" maxlength="10" alt="'.$clang->gT('Answer').'" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type)" />
        </p>';
        $answer .= '<input type="hidden" name="qattribute_answer[]" value="'.$ia[1].'" />
        <input type="hidden" id="qattribute_answer'.$ia[1].'" name="qattribute_answer'.$ia[1].'" />
        <input type="hidden" id="dateformat'.$ia[1].'" value="'.$dateformatdetails['jsdate'].'"/>';


    }
    else
    {
        if ($clang->langcode !== 'en')
        {
            $js_header_includes[] = '/scripts/jquery/locale/jquery.ui.datepicker-'.$clang->langcode.'.js';
        }
        $css_header_includes[]= '/scripts/jquery/css/start/jquery-ui.css';

        // Format the date  for output
        if (trim($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]])!='')
        {
            $datetimeobj = new Date_Time_Converter($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] , "Y-m-d");
            $dateoutput = $datetimeobj->convert($dateformatdetails['phpdate']);
        }
        else
        {
            $dateoutput='';
        }

        if (trim($aQuestionAttributes['dropdown_dates_year_min'])!='') {
            $minyear=$aQuestionAttributes['dropdown_dates_year_min'];
        }
        else
        {
            $minyear='1980';
        }

        if (trim($aQuestionAttributes['dropdown_dates_year_max'])!='') {
            $maxyear=$aQuestionAttributes['dropdown_dates_year_max'];
        }
        else
        {
            $maxyear='2020';
        }

        $goodchars = str_replace( array("m","d","y"), "", $dateformatdetails['jsdate']);
        $goodchars = "0123456789".$goodchars[0];

        $answer ="<p class='question answer-item text-item date-item'><label for='answer{$ia[1]}' class='hide label'>{$clang->gT('Date picker')}</label>
        <input class='popupdate' type=\"text\" size=\"10\" name=\"{$ia[1]}\" title='".sprintf($clang->gT('Format: %s'),$dateformatdetails['dateformat'])."' id=\"answer{$ia[1]}\" value=\"$dateoutput\" maxlength=\"10\" onkeypress=\"return goodchars(event,'".$goodchars."')\" onchange=\"$checkconditionFunction(this.value, this.name, this.type)\" />
        <input  type='hidden' name='dateformat{$ia[1]}' id='dateformat{$ia[1]}' value='{$dateformatdetails['jsdate']}'  />
        <input  type='hidden' name='datelanguage{$ia[1]}' id='datelanguage{$ia[1]}' value='{$clang->langcode}'  />
        <input  type='hidden' name='dateyearrange{$ia[1]}' id='dateyearrange{$ia[1]}' value='{$minyear}:{$maxyear}'  />

        </p>";
        if (trim($aQuestionAttributes['hide_tip'])==1) {
            $answer.="<p class=\"tip\">".sprintf($clang->gT('Format: %s'),$dateformatdetails['dateformat'])."</p>";
        }
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
    $answer = "\n\t<p class=\"question answer-item dropdown-item langage-item\">\n"
    ."<label for='answer{$ia[1]}' class='hide label'>{$clang->gT('Choose your language')}</label>"
    ."<select name=\"$ia[1]\" id=\"answer$ia[1]\" onchange=\"document.getElementById('lang').value=this.value; $checkconditionFunction(this.value, this.name, this.type);\">\n";
    if (!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) {$answer .= "\t<option value=\"\" selected=\"selected\">".$clang->gT('Please choose...')."</option>\n";}
    foreach ($answerlangs as $ansrow)
    {
        $answer .= "\t<option value=\"{$ansrow}\"";
        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow)
        {
            $answer .= SELECTED;
        }
        $answer .= '>'.getLanguageNameFromCode($ansrow, true)."</option>\n";
    }
    $answer .= "</select>\n";
    $answer .= "<input type=\"hidden\" name=\"java$ia[1]\" id=\"java$ia[1]\" value=\"".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]."\" />\n";

    $inputnames[]=$ia[1];
    $answer .= "\n<input type=\"hidden\" name=\"lang\" id=\"lang\" value=\"\" />\n\t</p>\n";

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

    $query = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ";
    $result = Yii::app()->db->createCommand($query)->query();     //Checked
    $row = $result->read(); $other = $row['other'];

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

    $dropdownSize = '';
    if (isset($aQuestionAttributes['dropdown_size']) && $aQuestionAttributes['dropdown_size'] > 0)
    {
        $_height = sanitize_int($aQuestionAttributes['dropdown_size']) ;
        $_maxHeight = $ansresult->RowCount();
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
        foreach ($ansresult->readAll() as $ansrow)
        {
            $opt_select = '';
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow['code'])
            {
                $opt_select = SELECTED;
            }
            if ($prefixStyle == 1) {
                $_prefix = ++$_rowNum . ') ';
            }
            $answer .= "<option value='{$ansrow['code']}' {$opt_select}>{$_prefix}{$ansrow['answer']}</option>\n";
        }
    }
    else
    {
        $defaultopts = Array();
        $optgroups = Array();
        foreach ($ansresult->readAll() as $ansrow)
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
            $answer .= '                                   <optgroup class="dropdowncategory" label="'.$categoryname.'">
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

                $answer .= '     					<option value="'.$optionarray['code'].'"'.$opt_select.'>'.$optionarray['answer'].'</option>
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

            $answer .= '     					<option value="'.$optionarray['code'].'"'.$opt_select.'>'.$optionarray['answer'].'</option>
            ';
        }
    }

    if (!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]])
    {
        $answer = '					<option value=""'.SELECTED.'>'.$clang->gT('Please choose...').'</option>'."\n".$answer;
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
        $answer .= '					<option value="-oth-"'.$opt_select.'>'.$_prefix.$othertext."</option>\n";
    }

    if (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] != '') && $ia[6] != 'Y' && $ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
    {
        if ($prefixStyle == 1) {
            $_prefix = ++$_rowNum . ') ';
        }
        $answer .= '<option class="noanswer-item" value="">'.$_prefix.$clang->gT('No answer')."</option>\n";
    }
    $answer .= '				</select>
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
        $answer .= '				<input type="text" id="othertext'.$ia[1].'" name="'.$ia[1].'other" style="display:';

        $inputnames[]=$ia[1].'other';

        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] != '-oth-')
        {
            $answer .= 'none';
        }

        //		// --> START BUG FIX - text field for other was not repopulating when returning to page via << PREV
        $answer .= '"';
        //		$thisfieldname=$ia[1].'other';
        //		if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname])) { $answer .= ' value="'.htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname],ENT_QUOTES).'" ';}
        //		// --> END BUG FIX

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
    // Get array_filter stuff

    $rowcounter = 0;
    $colcounter = 1;
    $trbc='';

    foreach ($ansresult as $ansrow)
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
        $answer .='		<input class="radio" type="radio" value="'.$ansrow['code'].'" name="'.$ia[1].'" id="answer'.$ia[1].$ansrow['code'].'"'.$check_ans.' onclick="if (document.getElementById(\'answer'.$ia[1].'othertext\') != null) document.getElementById(\'answer'.$ia[1].'othertext\').value=\'\';'.$checkconditionFunction.'(this.value, this.name, this.type)" />
        <label for="answer'.$ia[1].$ansrow['code'].'" class="answertext">'.$ansrow['answer'].'</label>
        '.$wrapper['item-end'];

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

    if (isset($other) && $other=='Y')
    {

        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeperator = $sSeperator['seperator'];

        if ($aQuestionAttributes['other_numbers_only']==1)
        {
            $numbersonly = 'onkeypress="return goodchars(event,\'-0123456789'.$sSeperator.'\')"';
            $oth_checkconditionFunction = 'fixnum_checkconditions';
        }
        else
        {
            $numbersonly = '';
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
                $dispVal = str_replace('.',$sSeperator,$dispVal);
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
        $answer .= '		<input class="radio" type="radio" value="-oth-" name="'.$ia[1].'" id="SOTH'.$ia[1].'"'.$check_ans.' onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)" />
        <label for="SOTH'.$ia[1].'" class="answertext">'.$othertext.'</label>
        <label for="answer'.$ia[1].'othertext">
        <input type="text" class="text '.$kpclass.'" id="answer'.$ia[1].'othertext" name="'.$ia[1].'other" title="'.$clang->gT('Other').'"'.$answer_other.' '.$numbersonly.' onchange="if($.trim($(this).val())!=\'\'){ $(\'#SOTH'.$ia[1].'\').attr(\'checked\',\'checked\'); }; '.$oth_checkconditionFunction.'(this.value, this.name, this.type);" />
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

        $answer .= $wrapper['item-start-noanswer'].'		<input class="radio" type="radio" name="'.$ia[1].'" id="answer'.$ia[1].'NANS" value=""'.$check_ans.' onclick="if (document.getElementById(\'answer'.$ia[1].'othertext\') != null) document.getElementById(\'answer'.$ia[1].'othertext\').value=\'\';'.$checkconditionFunction.'(this.value, this.name, this.type)" />
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

    //    $checkotherscript = "";
    //
    //    if (isset($other) && $other == 'Y' && $aQuestionAttributes['other_comment_mandatory']==1)
    //    {
    //        $checkotherscript = "<script type='text/javascript'>\n"
    //        . "\t<!--\n"
    //        . "oldonsubmitOther_{$ia[0]} = document.limesurvey.onsubmit;\n"
    //        . "function ensureOther_{$ia[0]}()\n"
    //        . "{\n"
    //        . "\tothercommentval=document.getElementById('answer{$ia[1]}othertext').value;\n"
    //        . "\totherval=document.getElementById('SOTH{$ia[1]}').checked;\n"
    //        . "\tif (otherval == true && othercommentval == '') {\n"
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
    //
    //    $answer = $checkotherscript . $answer;

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_listwithcomment($ia)
{
    global $maxoptionsize, $dropdownthreshold, $thissurvey;
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

    $ansresult=Yii::app()->db->createCommand($ansquery)->query();
    $anscount = $ansresult->getRowCount();


    $hint_comment = $clang->gT('Please enter your comment here');

    if (isset($lwcdropdowns) && $lwcdropdowns == 'R' && $anscount <= $dropdownthreshold)
    {
        $answer .= '<div class="list">
        <ul class="answers-list radio-list">
        ';

        foreach ($ansresult->readAll() as $ansrow)
        {
            $check_ans = '';
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow['code'])
            {
                $check_ans = CHECKED;
            }
            $answer .= '		<li class="answer-item radio-item">
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
            $answer .= '		<li class="answer-item radio-item noanswer-item">
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
        $answer .= '	</ul>
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
        // --> START NEW FEATURE - SAVE
        $answer .= '<p class="select answer-item dropdown-item">
        <select class="select" name="'.$ia[1].'" id="answer'.$ia[1].'" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type)" >
        ';
        // --> END NEW FEATURE - SAVE
        foreach ($ansresult->readAll() as $ansrow)
        {
            $check_ans = '';
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == $ansrow['code'])
            {
                $check_ans = SELECTED;
            }
            $answer .= '		<option value="'.$ansrow['code'].'"'.$check_ans.'>'.$ansrow['answer']."</option>\n";

            if (strlen($ansrow['answer']) > $maxoptionsize)
            {
                $maxoptionsize = strlen($ansrow['answer']);
            }
        }
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            if ((!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '') ||($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == ' '))
            {
                $check_ans = SELECTED;
            }
            elseif ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] != '')
            {
                $check_ans = '';
            }
            $answer .= '<option class="noanswer-item" value=""'.$check_ans.'>'.$clang->gT('No answer')."</option>\n";
        }
        $answer .= '	</select>
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
    global $thissurvey, $showpopups;

    // the future string that goes into the answer segment of templates
    $answer = '';

    $clang=Yii::app()->lang;
    $imageurl = Yii::app()->getConfig("imageurl");

    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    $answer = '';
    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
    } else {
        $ansquery = "SELECT * FROM {{answers}} WHERE qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
    }
    $ansresult = Yii::app()->db->createCommand($ansquery)->query();   //Checked
    $anscount= $ansresult->getRowCount();
    if (trim($aQuestionAttributes["max_answers"])!='')
    {
        $max_answers=trim($aQuestionAttributes["max_answers"]);
    } else {
        $max_answers=$anscount;
    }
    $finished=$anscount-$max_answers;
    $answer .= "\t<script type='text/javascript'>\n"
    . "\t<!--\n"
    . "function rankthis_{$ia[0]}(\$code, \$value)\n"
    . "\t{\n"
    . "\t\$index=document.getElementById('CHOICES_{$ia[0]}').selectedIndex;\n"
    . "\tfor (i=1; i<=$max_answers; i++)\n"
    . "{\n"
    . "\$b=i;\n"
    . "\$b += '';\n"
    . "\$inputname=\"RANK_{$ia[0]}\"+\$b;\n"
    . "\$hiddenname=\"fvalue_{$ia[0]}\"+\$b;\n"
    . "\$cutname=\"cut_{$ia[0]}\"+i;\n"
    . "document.getElementById(\$cutname).style.display='none';\n"
    . "if (!document.getElementById(\$inputname).value)\n"
    . "\t{\n"
    . "\t\t\t\t\t\t\tdocument.getElementById(\$inputname).value=\$value;\n"
    . "\t\t\t\t\t\t\tdocument.getElementById(\$hiddenname).value=\$code;\n"
    . "\t\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='';\n"
    . "\t\t\t\t\t\t\tfor (var b=document.getElementById('CHOICES_{$ia[0]}').options.length-1; b>=0; b--)\n"
    . "\t\t\t\t\t\t\t\t{\n"
    . "\t\t\t\t\t\t\t\tif (document.getElementById('CHOICES_{$ia[0]}').options[b].value == \$code)\n"
    . "\t\t\t\t\t\t\t\t\t{\n"
    . "\t\t\t\t\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').options[b] = null;\n"
    . "\t\t\t\t\t\t\t\t\t}\n"
    . "\t\t\t\t\t\t\t\t}\n"
    . "\t\t\t\t\t\t\ti=$max_answers;\n"
    . "\t\t\t\t\t\t\t}\n"
    . "\t\t\t\t\t\t}\n"
    . "\t\t\t\t\tif (document.getElementById('CHOICES_{$ia[0]}').options.length == $finished)\n"
    . "\t\t\t\t\t\t{\n"
    . "\t\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').disabled=true;\n"
    . "\t\t\t\t\t\t}\n"
    . "\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').selectedIndex=-1;\n"
    . "\t\t\t\t\t$checkconditionFunction(\$code);\n"
    . "\t\t\t\t\t}\n"
    . "\t\t\t\tfunction deletethis_{$ia[0]}(\$text, \$value, \$name, \$thisname)\n"
    . "\t\t\t\t\t{\n"
    . "\t\t\t\t\tvar qid='{$ia[0]}';\n"
    . "\t\t\t\t\tvar lngth=qid.length+4;\n"
    . "\t\t\t\t\tvar cutindex=\$thisname.substring(lngth, \$thisname.length);\n"
    . "\t\t\t\t\tcutindex=parseFloat(cutindex);\n"
    . "\t\t\t\t\tdocument.getElementById(\$name).value='';\n"
    . "\t\t\t\t\tdocument.getElementById(\$thisname).style.display='none';\n"
    . "\t\t\t\t\tif (cutindex > 1)\n"
    . "\t\t\t\t\t\t{\n"
    . "\t\t\t\t\t\t\$cut1name=\"cut_{$ia[0]}\"+(cutindex-1);\n"
    . "\t\t\t\t\t\t\$cut2name=\"fvalue_{$ia[0]}\"+(cutindex);\n"
    . "\t\t\t\t\t\tdocument.getElementById(\$cut1name).style.display='';\n"
    . "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n"
    . "\t\t\t\t\t\t}\n"
    . "\t\t\t\t\telse\n"
    . "\t\t\t\t\t\t{\n"
    . "\t\t\t\t\t\t\$cut2name=\"fvalue_{$ia[0]}\"+(cutindex);\n"
    . "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n"
    . "\t\t\t\t\t\t}\n"
    . "\t\t\t\t\tvar i=document.getElementById('CHOICES_{$ia[0]}').options.length;\n"
    . "\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').options[i] = new Option(\$text, \$value);\n"
    . "\t\t\t\t\tif (document.getElementById('CHOICES_{$ia[0]}').options.length > 0)\n"
    . "\t\t\t\t\t\t{\n"
    . "\t\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').disabled=false;\n"
    . "\t\t\t\t\t\t}\n"
    . "\t\t\t\t\t$checkconditionFunction('');\n"
    . "\t\t\t\t\t}\n"
    . "\t\t\t//-->\n"
    . "\t\t\t</script>\n";
    unset($answers);
    //unset($inputnames);
    unset($chosen);
    $ranklist = '';

    foreach ($ansresult->readAll() as $ansrow)
    {
        $answers[] = array($ansrow['code'], $ansrow['answer']);
    }
    $existing=0;
    for ($i=1; $i<=$anscount; $i++)
    {
        $myfname=$ia[1].$i;
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])
        {
            $existing++;
        }
    }
    for ($i=1; $i<=$max_answers; $i++)
    {
        $myfname = $ia[1].$i;
        if (!empty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            foreach ($answers as $ans)
            {
                if ($ans[0] == $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])
                {
                    $thiscode = $ans[0];
                    $thistext = $ans[1];
                }
            }
        }
        $ranklist .= "\t<tr><td class=\"position\">&nbsp;<label for='RANK_{$ia[0]}$i'>"
        ."$i:&nbsp;</label></td><td class=\"item\"><input class=\"text\" type=\"text\" name=\"RANK_{$ia[0]}$i\" id=\"RANK_{$ia[0]}$i\"";
        if (!empty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $ranklist .= " value='";
            $ranklist .= htmlspecialchars($thistext, ENT_QUOTES);
            $ranklist .= "'";
        }
        $ranklist .= " onfocus=\"this.blur()\" />\n";
        $ranklist .= "<input type=\"hidden\" name=\"$myfname\" id=\"fvalue_{$ia[0]}$i\" value='";
        $chosen[]=""; //create array
        if (!empty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $ranklist .= $thiscode;
            $chosen[]=array($thiscode, $thistext);
        }
        $ranklist .= "' />\n";
        $ranklist .= "<img src=\"$imageurl/cut.gif\" alt=\"".$clang->gT("Remove this item")."\" title=\"".$clang->gT("Remove this item")."\" ";
        if ($i != $existing)
        {
            $ranklist .= "style=\"display:none\"";
        }
        $ranklist .= " id=\"cut_{$ia[0]}$i\" onclick=\"deletethis_{$ia[0]}(document.getElementById('RANK_{$ia[0]}$i').value, document.getElementById('fvalue_{$ia[0]}$i').value, document.getElementById('RANK_{$ia[0]}$i').name, this.id)\" /><br />\n";
        $inputnames[]=$myfname;
        $ranklist .= "</td></tr>\n";
    }

    $maxselectlength=0;
    $choicelist = "<select size=\"$anscount\" name=\"CHOICES_{$ia[0]}\" ";
    if (isset($choicewidth)) {$choicelist.=$choicewidth;}

    $choicelist .= " id=\"CHOICES_{$ia[0]}\" onchange=\"if (this.options.length>0 && this.selectedIndex<0) { this.options[this.options.length-1].selected=true;}; rankthis_{$ia[0]}(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" class=\"select\">\n";

    foreach ($answers as $ans)
    {
        if (!in_array($ans, $chosen))
        {
            $choicelist .= "\t\t\t\t\t\t\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
        }
        if (strlen($ans[1]) > $maxselectlength) {$maxselectlength = strlen($ans[1]);}
    }
    $choicelist .= "</select>\n";

    $answer .= "\t<table border='0' cellspacing='0' class='rank'>\n"
    . "<tr>\n"
    . "\t<td align='left' valign='top' class='rank label'>\n"
    . "<strong>&nbsp;&nbsp;<label for='CHOICES_{$ia[0]}'>".$clang->gT("Your Choices").":</label></strong><br />\n"
    . "&nbsp;".$choicelist
    . "\t&nbsp;</td>\n";
    $maxselectlength=$maxselectlength+2;
    if ($maxselectlength > 60)
    {
        $maxselectlength=60;
    }
    $ranklist = str_replace("<input class=\"text\"", "<input size='{$maxselectlength}' class='text'", $ranklist);
    $answer .= "\t<td style=\"text-align:left; white-space:nowrap;\" class='rank output'>\n"
    . "\t<table border='0' cellspacing='1' cellpadding='0'>\n"
    . "\t<tr><td></td><td><strong>".$clang->gT("Your Ranking").":</strong></td></tr>\n";

    $answer .= $ranklist
    . "\t</table>\n"
    . "\t</td>\n"
    . "</tr>\n"
    . "<tr>\n"
    . "\t<td colspan='2' class='rank helptext'><font size='1'>\n"
    . "".$clang->gT("Click on the scissors next to each item on the right to remove the last entry in your ranked list")
    . "\t</font size='1'></td>\n"
    . "</tr>\n"
    . "\t</table>\n";

    if (trim($aQuestionAttributes["min_answers"]) != '')
    {
        $minansw=trim($aQuestionAttributes["min_answers"]);
        if(!isset($showpopups) || $showpopups == 0)
        {
            $answer .= "<div id='rankingminanswarning{$ia[0]}' style='display: none; color: red' class='errormandatory'>"
            .sprintf($clang->ngT("Please rank at least %d item for question \"%s\"","Please rank at least %d items for question \"%s\".",$minansw),$minansw, trim(str_replace(array("\n", "\r"), "", $ia[3])))."</div>";
        }
        $minanswscript = "<script type='text/javascript'>\n"
        . "  <!--\n"
        . "  oldonsubmit_{$ia[0]} = document.limesurvey.onsubmit;\n"
        . "  function ensureminansw_{$ia[0]}()\n"
        . "  {\n"
        . "     count={$anscount} - document.getElementById('CHOICES_{$ia[0]}').options.length;\n"
        . "     if (count < {$minansw} && $('#relevance{$ia[0]}').val()==1){\n";
        if(!isset($showpopups) || $showpopups == 0)
        {
            $minanswscript .= "\n
            document.getElementById('rankingminanswarning{$ia[0]}').style.display='';\n";
        } else {
            $minanswscript .="
            alert('".sprintf($clang->ngT("Please rank at least %d item for question \"%s\"","Please rank at least %d items for question \"%s\"",$minansw,'js'),$minansw, trim(javascriptEscape(str_replace(array("\n", "\r"), "",$ia[3]),true,true)))."');\n";
        }
        $minanswscript .= ""
        . "     return false;\n"
        . "   } else {\n"
        . "     if (oldonsubmit_{$ia[0]}){\n"
        . "         return oldonsubmit_{$ia[0]}();\n"
        . "     }\n"
        . "     return true;\n"
        . "     }\n"
        . "  }\n"
        . "  document.limesurvey.onsubmit = ensureminansw_{$ia[0]}\n"
        . "  -->\n"
        . "  </script>\n";
        $answer = $minanswscript . $answer;
    }

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
        $qquery = "SELECT qid FROM {{questions}} WHERE sid=".$thissurvey['sid']." AND scale_id=0 AND qid=".$qarow['qid'];
        $qresult = Yii::app()->db->createCommand($qquery)->query();     //Checked
        if ($qresult->getRowCount() > 0)
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
        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeperator= $sSeperator['seperator'];
        $numbersonly = " onkeypress='return goodchars(event,\"-0123456789$sSeperator\")'";
        $oth_checkconditionFunction = "fixnum_checkconditions";
    }
    else
    {
        $numbersonly = '';
        $oth_checkconditionFunction = "checkconditions";
    }

    // Check if the max_answers attribute is set
    //    $maxansw = 0;
    //    $callmaxanswscriptcheckbox = '';
    //    $callmaxanswscriptother = '';
    //    $maxanswscript = '';

    $exclude_all_others_auto = trim($aQuestionAttributes["exclude_all_others_auto"]);

    if ($exclude_all_others_auto=='1'){
        $autoArray['list'][]=$ia[1];
        $autoArray[$ia[1]]['parent'] = $ia[1];
    }

    //    if (((int)$aQuestionAttributes['max_answers']>0) && $exclude_all_others_auto=='0')
    //    {
    //        $maxansw=$aQuestionAttributes['max_answers'];
    //        $callmaxanswscriptcheckbox = "limitmaxansw_{$ia[0]}(this);";
    //        $callmaxanswscriptother = "onkeyup='limitmaxansw_{$ia[0]}(this)'";
    //        $maxanswscript = "\t<script type='text/javascript'>\n"
    //        . "\t<!--\n"
    //        . "function limitmaxansw_{$ia[0]}(me)\n"
    //        . "{\n"
    //        . "\tmax=$maxansw\n"
    //        . "\tcount=0;\n"
    //        . "\tif (max == 0) { return count; }\n";
    //    }
    //
    //
    //    // Check if the min_answers attribute is set
    //    $minansw=0;
    //    $minanswscript = "";
    //
    //    if ((int)$aQuestionAttributes['min_answers']>0)
    //    {
    //        $minansw=trim($aQuestionAttributes["min_answers"]);
    //        $minanswscript = "<script type='text/javascript'>\n"
    //        . "\t<!--\n"
    //        . "oldonsubmit_{$ia[0]} = document.limesurvey.onsubmit;\n"
    //        . "function ensureminansw_{$ia[0]}()\n"
    //        . "{\n"
    //        . "\tcount=0;\n"
    //        ;
    //    }

    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and parent_qid=0";
    $qresult = dbExecuteAssoc($qquery);     //Checked
    $qrow = $qresult->read(); $other = $qrow['other'];

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
        if ($exclude_all_others_auto==1){
            if ($ansrow['title']==trim($aQuestionAttributes['exclude_all_others'])){
                $autoArray[$ia[1]]['focus'] = $ia[1].trim($aQuestionAttributes['exclude_all_others']);
            }
            else{
                $autoArray[$ia[1]]['children'][] = $myfname;
                $extra_class=" excludeallothers";
            }
        }

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
        $answer .= '		<input class="checkbox" type="checkbox" name="'.$ia[1].$ansrow['title'].'" id="answer'.$ia[1].$ansrow['title'].'" value="Y"';

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
        $answer .= '		<input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="';
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
    if ($exclude_all_others_auto==1){
        $answer .= "<script type='text/javascript'>autoArray = ".ls_json_encode($autoArray).";</script>";
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
        <input class="checkbox" type="checkbox" name="'.$myfname.'cbox" alt="'.$clang->gT('Other').'" id="answer'.$myfname.'cbox"';

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && trim($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])!='')
        {
            $answer .= CHECKED;
        }
        $answer .= " onclick='cancelBubbleThis(event);if(this.checked===false){ document.getElementById(\"answer$myfname\").value=\"\"; document.getElementById(\"java$myfname\").value=\"\"; $checkconditionFunction(\"\", \"$myfname\", \"text\"); }";
        $answer .= " if(this.checked===true) { document.getElementById(\"answer$myfname\").focus(); }; LEMflagMandOther(\"$myfname\",this.checked);";
        $answer .= "' />
        <label for=\"answer$myfname\" class=\"answertext\">".$othertext."</label>
        <input class=\"text ".$kpclass."\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\"";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            if ($aQuestionAttributes['other_numbers_only']==1)
            {
                $dispVal = str_replace('.',$sSeperator,$dispVal);
            }
            $answer .= ' value="'.htmlspecialchars($dispVal,ENT_QUOTES).'"';
        }
        $answer .= " onchange='$(\"#java{$myfname}\").val(this.value);$oth_checkconditionFunction(this.value, this.name, this.type);if ($.trim($(\"#java{$myfname}\").val())!=\"\") { \$(\"#answer{$myfname}cbox\").attr(\"checked\",\"checked\"); } else { \$(\"#answer{$myfname}cbox\").attr(\"checked\",\"\"); }; LEMflagMandOther(\"$myfname\",this.checked);' $numbersonly />";
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
                $dispVal = str_replace('.',$sSeperator,$dispVal);
            }
            $answer .= ' value="'.htmlspecialchars($dispVal,ENT_QUOTES).'"';
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

    $checkotherscript = "";
    if ($other == 'Y')
    {
        // Multiple choice with 'other' is a specific case as the checkbox isn't recorded into DB
        // this means that if it is cehcked We must force the end-user to enter text in the input
        // box
        $checkotherscript = "<script type='text/javascript'>\n"
        . "\t<!--\n"
        . "oldonsubmitOther_{$ia[0]} = document.limesurvey.onsubmit;\n"
        . "function ensureOther_{$ia[0]}()\n"
        . "{\n"
        . "\tothercboxval=document.getElementById('answer".$myfname."cbox').checked;\n"
        . "\totherval=document.getElementById('answer".$myfname."').value;\n"
        . "\tif (otherval != '' || othercboxval != true) {\n"
        . "if(typeof oldonsubmitOther_{$ia[0]} == 'function') {\n"
        . "\treturn oldonsubmitOther_{$ia[0]}();\n"
        . "}\n"
        . "\t}\n"
        . "\telse {\n"
        . "alert('".sprintf($clang->gT("You've marked the \"other\" field for question \"%s\". Please also fill in the accompanying \"other comment\" field.","js"),trim(javascriptEscape($ia[3],true,true)))."');\n"
        . "return false;\n"
        . "\t}\n"
        . "}\n"
        . "document.limesurvey.onsubmit = ensureOther_{$ia[0]};\n"
        . "\t-->\n"
        . "</script>\n";
    }

    $answer = $checkotherscript . $answer;

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
        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeperator = $sSeperator['seperator'];
        $numbersonly = 'onkeypress="return goodchars(event,\'-0123456789'.$sSeperator.'\')"';
        $oth_checkconditionFunction = "fixnum_checkconditions";
    }
    else
    {
        $numbersonly = '';
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
    //    // Check if the max_answers attribute is set
    //    $maxansw=0;
    //    $callmaxanswscriptcheckbox = '';
    //    $callmaxanswscriptcheckbox2 = '';
    $callmaxanswscriptother = '';
    //    $maxanswscript = '';
    //    if (trim($aQuestionAttributes['max_answers'])!='') {
    //        $maxansw=$aQuestionAttributes['max_answers'];
    //        $callmaxanswscriptcheckbox = "limitmaxansw_{$ia[0]}(this);";
    //        $callmaxanswscriptcheckbox2= "limitmaxansw_{$ia[0]}";
    //        $callmaxanswscriptother = "onkeyup=\"limitmaxansw_{$ia[0]}(this)\"";
    //
    //        $maxanswscript = "\t<script type='text/javascript'>\n"
    //        . "\t<!--\n"
    //        . "function limitmaxansw_{$ia[0]}(me)\n"
    //        . "\t{\n"
    //        . "\tmax=$maxansw\n"
    //        . "\tcount=0;\n"
    //        . "\tif (max == 0) { return count; }\n";
    //    }
    //
    //    // Check if the min_answers attribute is set
    //    $minansw=0;
    //    $minanswscript = "";
    //    if (trim($aQuestionAttributes["min_answers"])!='')
    //    {
    //        $minansw=trim($aQuestionAttributes["min_answers"]);
    //        $minanswscript = "<script type='text/javascript'>\n"
    //        . "\t<!--\n"
    //        . "oldonsubmit_{$ia[0]} = document.limesurvey.onsubmit;\n"
    //        . "function ensureminansw_{$ia[0]}()\n"
    //        . "{\n"
    //        . "\tcount=0;\n"
    //        ;
    //    }

    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and parent_qid=0";
    $qresult = Yii::app()->db->createCommand($qquery)->query();     //Checked
    $qrow = $qresult->read(); $other = $qrow['other'];
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
        . "\t<label for=\"answer$myfname\" class=\"answertext\">\n"
        . "\t<input class=\"checkbox\" type=\"checkbox\" name=\"$myfname\" id=\"answer$myfname\" value=\"Y\"";

        /* If the question has already been ticked, check the checkbox */
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'Y')
            {
                $answer_main .= CHECKED;
            }
        }
        $answer_main .=" onclick='cancelBubbleThis(event);$checkconditionFunction(this.value, this.name, this.type);' "
        . " onchange='document.getElementById(\"answer$myfname2\").value=\"\";' />\n"
        . $ansrow['question']."</label>\n";

        //        if ($maxansw > 0) {$maxanswscript .= "\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}
        //        if ($minansw > 0) {$minanswscript .= "\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}

        $answer_main .= "<input type='hidden' name='java$myfname' id='java$myfname' value='";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $answer_main .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
        }
        $answer_main .= "' />\n";
        $fn++;
        $answer_main .= "</span>\n<span class=\"comment\">\n\t<label for='answer$myfname2' class=\"answer-comment hide \">".$clang->gT('Make a comment on your choice here:')."</label>\n"
        ."<input class='text ".$kpclass."' type='text' size='40' id='answer$myfname2' name='$myfname2' title='".$clang->gT('Make a comment on your choice here:')."' value='";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2])) {$answer_main .= htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2],ENT_QUOTES);}
        // --> START NEW FEATURE - SAVE
        $answer_main .= "'  onclick='cancelBubbleThis(event);' onchange='if (jQuery.trim($(\"#answer{$myfname2}\").val())!=\"\") { document.getElementById(\"answer{$myfname}\").checked=true;$checkconditionFunction(document.getElementById(\"answer{$myfname}\").value,\"$myfname\",\"checkbox\");}' />\n</span>\n"
        . "\t</li>\n";
        // --> END NEW FEATURE - SAVE

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
        . "\t<label for=\"answer$myfname\" class=\"answertext\">\n".$othertext."\n<input class=\"text other ".$kpclass."\" $numbersonly type=\"text\" name=\"$myfname\" id=\"answer$myfname\" title=\"".$clang->gT('Other').'" size="10"';
        $answer_main .= " onchange='$oth_checkconditionFunction(this.value, this.name, this.type)'";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])
        {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            if ($aQuestionAttributes['other_numbers_only']==1)
            {
                $dispVal = str_replace('.',$sSeperator,$dispVal);
            }
            $answer_main .= ' value="'.htmlspecialchars($dispVal,ENT_QUOTES).'"';
        }
        $fn++;
        // --> START NEW FEATURE - SAVE
        $answer_main .= "  $callmaxanswscriptother />\n\t</label>\n</span>\n"
        . "<span class=\"comment\">\n\t<label for=\"answer$myfname2\" class=\"answer-comment hide\">".$clang->gT('Make a comment on your choice here:')."\t</label>\n"
        . '
        <input class="text '.$kpclass.'" type="text" size="40" name="'.$myfname2.'" id="answer'.$myfname2.'" title="'.$clang->gT('Make a comment on your choice here:').'" value="';
        // --> END NEW FEATURE - SAVE

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2])) {$answer_main .= htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2],ENT_QUOTES);}
        // --> START NEW FEATURE - SAVE
        $answer_main .= "\"/>\n";

        //        if ($maxansw > 0)
        //        {
        //            if ($aQuestionAttributes['other_comment_mandatory']==1)
        //            {
        //                $maxanswscript .= "\tif (document.getElementById('answer".$myfname."').value != '' && document.getElementById('answer".$myfname2."').value != '') { count += 1; }\n";
        //            }
        //            else
        //            {
        //                $maxanswscript .= "\tif (document.getElementById('answer".$myfname."').value != '') { count += 1; }\n";
        //            }
        //        }
        //
        //        if ($minansw > 0)
        //        {
        //            if ($aQuestionAttributes['other_comment_mandatory']==1)
        //            {
        //                $minanswscript .= "\tif (document.getElementById('answer".$myfname."').value != '' && document.getElementById('answer".$myfname2."').value != '') { count += 1; }\n";
        //            }
        //            else
        //            {
        //                $minanswscript .= "\tif (document.getElementById('answer".$myfname."').value != '') { count += 1; }\n";
        //            }
        //        }

        $answer_main .= "</span>\n\t</li>\n";
        // --> END NEW FEATURE - SAVE

        $inputnames[]=$myfname;
        $inputnames[]=$myfname2;
    }
    $answer .= "<ul class=\"subquestions-list questions-list checkbox-text-list\">\n".$answer_main."</ul>\n";


    //    if ( $maxansw > 0 )
    //    {
    //        $maxanswscript .= "\tif (count > max)\n"
    //        . "{\n"
    //        . "alert('".sprintf($clang->gT("Please choose at most %d answers for question \"%s\"","js"), $maxansw, trim(javascriptEscape($ia[3],true,true)))."');\n"
    //        . "var commentname='answer'+me.name+'comment';\n"
    //        . "if (me.type == 'checkbox') {\n"
    //        . "\tme.checked = false;\n"
    //        . "\tvar commentname='answer'+me.name+'comment';\n"
    //        . "}\n"
    //        . "if (me.type == 'text') {\n"
    //        . "\tme.value = '';\n"
    //        . "\tif (document.getElementById(me.name + 'cbox') ){\n"
    //        . " document.getElementById(me.name + 'cbox').checked = false;\n"
    //        . "\t}\n"
    //        . "}"
    //        . "document.getElementById(commentname).value='';\n"
    //        . "return max;\n"
    //        . "}\n"
    //        . "\t}\n"
    //        . "\t//-->\n"
    //        . "\t</script>\n";
    //        $answer = $maxanswscript . $answer;
    //    }
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

    //    $checkotherscript = "";
    //    //if ($other == 'Y' && $aQuestionAttributes['other_comment_mandatory']==1) //TIBO
    //    if ($other == 'Y' && $aQuestionAttributes['other_comment_mandatory']==1) //TIBO
    //    {
    //        // Multiple choice with 'other' is a specific case as the checkbox isn't recorded into DB
    //        // this means that if it is cehcked We must force the end-user to enter text in the input
    //        // box
    //        $checkotherscript = "<script type='text/javascript'>\n"
    //        . "\t<!--\n"
    //        . "oldonsubmitOther_{$ia[0]} = document.limesurvey.onsubmit;\n"
    //        . "function ensureOther_{$ia[0]}()\n"
    //        . "{\n"
    //        . "\tothercommentval=document.getElementById('answer".$myfname2."').value;\n"
    //        . "\totherval=document.getElementById('answer".$myfname."').value;\n"
    //        . "\tif (otherval != '' && othercommentval == '') {\n"
    //        . "alert('".sprintf($clang->gT("You've marked the \"other\" field for question \"%s\". Please also fill in the accompanying \"other comment\" field.","js"),trim(javascriptEscape($ia[3],true,true)))."');\n"
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
    //
    //    $answer = $checkotherscript . $answer;

    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_file_upload($ia)
{
    global $js_header_includes, $thissurvey;

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

    $uploadbutton = "<h2><a id='upload_".$ia[1]."' class='upload' href='{$scriptloc}?sid=".Yii::app()->getConfig('surveyID')."&amp;fieldname={$ia[1]}&amp;qid={$ia[0]}&amp;preview="
    ."{$questgrppreview}&amp;show_title={$aQuestionAttributes['show_title']}&amp;show_comment={$aQuestionAttributes['show_comment']}&amp;pos=".($pos?1:0)."'>" .$clang->gT('Upload files'). "</a></h2>";

    $answer =  "<script type='text/javascript'>
    var translt = {
    title: '" . $clang->gT('Upload your files','js') . "',
    returnTxt: '" . $clang->gT('Return to survey','js') . "',
    headTitle: '" . $clang->gT('Title','js') . "',
    headComment: '" . $clang->gT('Comment','js') . "',
    headFileName: '" . $clang->gT('File name','js') . "'
    };
    </script>\n";
    /*if ($pos)
    $answer .= "<script type='text/javascript' src='{$rooturl}/scripts/modaldialog.js'></script>";
    else */
    $answer .= "<script type='text/javascript' src='".Yii::app()->getBaseUrl(true)."/scripts/modaldialog.js'></script>";
    //$js_header_includes[]= '/scripts/modaldialog.js'; //not working!

    // Modal dialog
    $answer .= $uploadbutton;

    $answer .= "<input type='hidden' id='".$ia[1]."' name='".$ia[1]."' value='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]."' />";
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

    for (i = 1, filecount = 0; i <= '.$aQuestionAttributes['max_num_of_files'].'; i++)
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
        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeperator = $sSeperator['seperator'];
        $numbersonly = 'onkeypress="return goodchars(event,\'-0123456789'.$sSeperator.'\')"';
        $extraclass .=" numberonly";
        $checkconditionFunction = "fixnum_checkconditions";
    }
    else
    {
        $numbersonly = '';
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
    $anscount = $ansresult->count()*2;
    //$answer .= "\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n";
    $fn = 1;

    $answer_main = '';

    $label_width = 0;

    if ($anscount==0)
    {
        $inputnames=array();
        $answer_main .= '	<li>'.$clang->gT('Error: This question has no answers.')."</li>\n";
    }
    else
    {
        if (trim($aQuestionAttributes['display_rows'])!='')
        {
            //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
            $drows=$aQuestionAttributes['display_rows'];

            foreach ($ansresult->readAll() as $ansrow)
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
                rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onchange="'.$checkconditionFunction.'(this.value, this.name, this.type);" '.$numbersonly.'>';

                if($label_width < strlen(trim(strip_tags($ansrow['question']))))
                {
                    $label_width = strlen(trim(strip_tags($ansrow['question'])));
                }

                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
                {
                    $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                    if ($aQuestionAttributes['numbers_only']==1)
                    {
                        $dispVal = str_replace('.',$sSeperator,$dispVal);
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
            foreach ($ansresult->readAll() as $ansrow)
            {
                $myfname = $ia[1].$ansrow['title'];
                if ($ansrow['question'] == "") {$ansrow['question'] = "&nbsp;";}

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
                        $dispVal = str_replace('.',$sSeperator,$dispVal);
                    }
                    $answer_main .= $dispVal;
                }

                // --> START NEW FEATURE - SAVE
                $answer_main .= '" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type);" '.$numbersonly.' '.$maxlength.' />'."\n".$suffix."\n\t</span>\n"
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
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_multiplenumeric($ia)
{
    global $js_header_includes, $css_header_includes, $thissurvey;

    $clang = Yii::app()->lang;
    $extraclass ="";
    $checkconditionFunction = "fixnum_checkconditions";
    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    $answer='';
    $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeperator = $sSeperator['seperator'];
    //Must turn on the "numbers only javascript"
    $numbersonly = 'onkeypress="inputField = event.srcElement ? event.srcElement : event.target || event.currentTarget; if (inputField.value.indexOf(\''.$sSeperator.'\')>0 && String.fromCharCode(getkey(event))==\''.$sSeperator.'\') return false; return goodchars(event,\'-0123456789'.$sSeperator.'\')"';
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
        $maxlength= "25";
    }

    //    //EQUALS VALUE
    //    if (trim($aQuestionAttributes['equals_num_value']) != ''){
    //        $equals_num_value = $aQuestionAttributes['equals_num_value'];
    //        $numbersonlyonblur[]='calculateValue'.$ia[1].'(3)';
    //        $calculateValue[]=3;
    //    }
    //    elseif (trim($aQuestionAttributes['num_value_equals_sgqa']) != '' && isset($_SESSION[$aQuestionAttributes['num_value_equals_sgqa']]))
    //    {
    //        $equals_num_value = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$aQuestionAttributes['num_value_equals_sgqa']];
    //        $numbersonlyonblur[]='calculateValue'.$ia[1].'(3)';
    //        $calculateValue[]=3;
    //    }
    //    else
    //    {
    //        $equals_num_value=0;
    //    }
    //
    //    //MIN VALUE
    //    if (trim($aQuestionAttributes['min_num_value']) != ''){
    //        $min_num_value = $aQuestionAttributes['min_num_value'];
    //        $numbersonlyonblur[]='calculateValue'.$ia[1].'(2)';
    //        $calculateValue[]=2;
    //    }
    //    elseif (trim($aQuestionAttributes['min_num_value_sgqa']) != '' && isset($_SESSION[$aQuestionAttributes['min_num_value_sgqa']])){
    //        $min_num_value = $_SESSION[$aQuestionAttributes['min_num_value_sgqa']];
    //        $numbersonlyonblur[]='calculateValue'.$ia[1].'(2)';
    //        $calculateValue[]=2;
    //    }
    //    else
    //    {
    //        $min_num_value=0;
    //    }
    //
    //    //MAX VALUE
    //    if (trim($aQuestionAttributes['max_num_value']) != ''){
    //        $max_num_value = $aQuestionAttributes['max_num_value'];
    //        $numbersonlyonblur[]='calculateValue'.$ia[1].'(1)';
    //        $calculateValue[]=1;
    //    }
    //    elseif (trim($aQuestionAttributes['max_num_value_sgqa']) != '' && isset($_SESSION[$aQuestionAttributes['max_num_value_sgqa']])){
    //        $max_num_value = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$aQuestionAttributes['max_num_value_sgqa']];
    //        $numbersonlyonblur[]='calculateValue'.$ia[1].'(1)';
    //        $calculateValue[]=1;
    //    }
    //    else
    //    {
    //        $max_num_value = 0;
    //    }

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

    /*if(!empty($numbersonlyonblur))
    {
    $numbersonly .= ' onblur="'.implode(';', $numbersonlyonblur).'"';
    $numbersonly_slider = implode(';', $numbersonlyonblur);
    }
    else
    {
    $numbersonly_slider = '';
    }*/
    $numbersonly_slider = '';

    if (trim($aQuestionAttributes['text_input_width'])!='')
    {
        $tiwidth=$aQuestionAttributes['text_input_width'];
        $extraclass .=" inputwidth".trim($aQuestionAttributes['text_input_width']);
    }
    else
    {
        $tiwidth=10;
    }
    if ($aQuestionAttributes['slider_layout']==1)
    {
        $slider_layout=true;
        $extraclass .=" withslider";
        $css_header_includes[]= '/scripts/jquery/css/start/jquery-ui.css';
        if (trim($aQuestionAttributes['slider_accuracy'])!='')
        {
            //$slider_divisor = 1 / $slider_accuracy['value'];
            $decimnumber = strlen($aQuestionAttributes['slider_accuracy']) - strpos($aQuestionAttributes['slider_accuracy'],'.') -1;
            $slider_divisor = pow(10,$decimnumber);
            $slider_stepping = $aQuestionAttributes['slider_accuracy'] * $slider_divisor;
            //	error_log('acc='.$slider_accuracy['value']." div=$slider_divisor stepping=$slider_stepping");
        }
        else
        {
            $slider_divisor = 1;
            $slider_stepping = 1;
        }

        if (trim($aQuestionAttributes['slider_min'])!='')
        {
            $slider_mintext = $aQuestionAttributes['slider_min'];
            $slider_min = $aQuestionAttributes['slider_min'] * $slider_divisor;
        }
        else
        {
            $slider_mintext = 0;
            $slider_min = 0;
        }
        if (trim($aQuestionAttributes['slider_max'])!='')
        {
            $slider_maxtext = $aQuestionAttributes['slider_max'];
            $slider_max = $aQuestionAttributes['slider_max'] * $slider_divisor;
        }
        else
        {
            $slider_maxtext = "100";
            $slider_max = 100 * $slider_divisor;
        }
        if (trim($aQuestionAttributes['slider_default'])!='')
        {
            $slider_default = $aQuestionAttributes['slider_default'];
        }
        else
        {
            $slider_default = '';
        }
        if ($slider_default == '' && $aQuestionAttributes['slider_middlestart']==1)
        {
            $slider_middlestart = intval(($slider_max + $slider_min)/2);
        }
        else
        {
            $slider_middlestart = '';
        }

        if (trim($aQuestionAttributes['slider_separator'])!='')
        {
            $slider_separator = $aQuestionAttributes['slider_separator'];
        }
        else
        {
            $slider_separator = '';
        }
    }
    else
    {
        $slider_layout = false;
    }
    $hidetip=$aQuestionAttributes['hide_tip'];
    if ($slider_layout === true) // auto hide tip when using sliders
    {
        $hidetip=1;
    }

    if ($aQuestionAttributes['random_order']==1)
    {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    }
    else
    {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0]  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }

    $ansresult = dbExecuteAssoc($ansquery);	//Checked
    $anscount = $ansresult->count()*2;
    //$answer .= "\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n";
    $fn = 1;

    $answer_main = '';

    if ($anscount==0)
    {
        $inputnames=array();
        $answer_main .= '	<li>'.$clang->gT('Error: This question has no answers.')."</li>\n";
    }
    else
    {
        $label_width = 0;
        foreach($ansresult->readAll() as $ansrow)
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
                $answer_and_slider_array=explode($slider_separator,$ansrow['question']);
                if (isset($answer_and_slider_array[0]))
                    $theanswer=$answer_and_slider_array[0];
                else
                    $theanswer = '';
                if (isset($answer_and_slider_array[1]))
                    $sliderleft=$answer_and_slider_array[1];
                else
                    $sliderleft = '';
                if (isset($answer_and_slider_array[2]))
                    $sliderright=$answer_and_slider_array[2];
                else
                    $sliderright = '';

                $sliderleft="<div class=\"slider_lefttext\">$sliderleft</div>";
                $sliderright="<div class=\"slider_righttext\">$sliderright</div>";
            }

            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, '', $myfname, "li","question-item answer-item text-item numeric-item".$extraclass);
            $answer_main .= "\t$htmltbody2\n";
            if ($slider_layout === false)
            {
                $answer_main .= "<label for=\"answer$myfname\">{$theanswer}</label>\n";
            }
            else
            {
                $answer_main .= "<label for=\"answer$myfname\" class=\"slider-label\">{$theanswer}</label>\n";
            }

            if($label_width < strlen(trim(strip_tags($ansrow['question']))))
            {
                $label_width = strlen(trim(strip_tags($ansrow['question'])));
            }

            if ($slider_layout === false)
            {
                $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
                $sSeperator = $sSeperator['seperator'];

                $answer_main .= "<span class=\"input\">\n\t".$prefix."\n\t<input class=\"text $kpclass\" type=\"text\" size=\"".$tiwidth.'" name="'.$myfname.'" id="answer'.$myfname.'" value="';
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
                {
                    $dispVal = str_replace('.',$sSeperator,$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]);
                    $answer_main .= $dispVal;
                }

                $answer_main .= '" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type);" '." {$numbersonly} {$maxlength} />\t{$suffix}\n</span>\n\t</li>\n";
            }
            else
            {
                if ($aQuestionAttributes['slider_showminmax']==1)
                {
                    //$slider_showmin=$slider_min;
                    $slider_showmin= "\t<div id=\"slider-left-$myfname\" class=\"slider_showmin\">$slider_mintext</div>\n";
                    $slider_showmax= "\t<div id=\"slider-right-$myfname\" class=\"slider_showmax\">$slider_maxtext</div>\n";
                }
                else
                {
                    $slider_showmin='';
                    $slider_showmax='';
                }

                $js_header_includes[] = '/scripts/jquery/jquery-ui.js';
                $js_header_includes[] = '/scripts/jquery/lime-slider.js';

                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] != '')
                {
                    $slider_startvalue = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] * $slider_divisor;
                    $displaycallout_atstart=1;
                }
                elseif ($slider_default != "")
                {
                    $slider_startvalue = $slider_default * $slider_divisor;
                    $displaycallout_atstart=1;
                }
                elseif ($slider_middlestart != '')
                {
                    $slider_startvalue = $slider_middlestart;
                    $displaycallout_atstart=0;
                }
                else
                {
                    $slider_startvalue = 'NULL';
                    $displaycallout_atstart=0;
                }
                $answer_main .= "$sliderleft<div id='container-$myfname' class='multinum-slider'>\n"
                . "\t<input type=\"text\" id=\"slider-modifiedstate-$myfname\" value=\"$displaycallout_atstart\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-param-min-$myfname\" value=\"$slider_min\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-param-max-$myfname\" value=\"$slider_max\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-param-stepping-$myfname\" value=\"$slider_stepping\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-param-divisor-$myfname\" value=\"$slider_divisor\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-param-startvalue-$myfname\" value='$slider_startvalue' style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-onchange-js-$myfname\" value=\"$numbersonly_slider\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-prefix-$myfname\" value=\"$prefix\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-suffix-$myfname\" value=\"$suffix\" style=\"display: none;\" />\n"
                . "<div id=\"slider-$myfname\" class=\"ui-slider-1\">\n"
                .  $slider_showmin
                . "<div class=\"slider_callout\" id=\"slider-callout-$myfname\"></div>\n"
                . "<div class=\"ui-slider-handle\" id=\"slider-handle-$myfname\"></div>\n"
                . $slider_showmax
                . "\t</div>"
                . "</div>$sliderright\n"
                . "<input class=\"text\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\" value=\"";
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] != '')
                {
                    $answer_main .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                }
                elseif ($slider_default != "")
                {
                    $answer_main .= $slider_default;
                }
                $answer_main .= "\"/>\n"
                . "\t</li>\n";
            }

            //			$answer .= "\t</tr>\n";

            $fn++;
            $inputnames[]=$myfname;
        }
        $question_tip = '';
        if($hidetip == 0)
        {
            $question_tip .= '<p class="tip">'.$clang->gT('Only numbers may be entered in these fields')."</p>\n";
        }
        //        if ($max_num_value)
        //        {
        //            $question_tip .= '<p id="max_num_value_'.$ia[1].'" class="tip">'.sprintf($clang->gT('Total of all entries must not exceed %d'), $max_num_value)."</p>\n";
        //        }
        //        if ($equals_num_value)
        //        {
        //            $question_tip .= '<p id="equals_num_value_'.$ia[1].'" class="tip">'.sprintf($clang->gT('Total of all entries must equal %d'),$equals_num_value)."</p>\n";
        //        }
        //        if ($min_num_value)
        //        {
        //            $question_tip .= '<p id="min_num_value_'.$ia[1].'" class="tip">'.sprintf($clang->gT('Total of all entries must be at least %s'),$min_num_value)."</p>\n";
        //        }
        //
        //          // TMSW TODO
        //        if ($max_num_value || $equals_num_value || $min_num_value)
        //        {
        //            $answer_computed = '';
        //            if ($equals_num_value)
        //            {
        //                $answer_computed .= "\t<li class='multiplenumerichelp'>\n<label for=\"remainingvalue_{$ia[1]}\">\n\t".$clang->gT('Remaining: ')."\n</label>\n<span>\n\t$prefix\n\t<input size=10 type='text' id=\"remainingvalue_{$ia[1]}\" disabled=\"disabled\" />\n\t$suffix\n</span>\n\t</li>\n";
        //            }
        //            $answer_computed .= "\t<li class='multiplenumerichelp'>\n<label for=\"totalvalue_{$ia[1]}\">\n\t".$clang->gT('Total: ')."\n</label>\n<span>\n\t$prefix\n\t<input size=10  type=\"text\" id=\"totalvalue_{$ia[1]}\" disabled=\"disabled\" />\n\t$suffix\n</span>\n\t</li>\n";
        //            $answer_main .= $answer_computed;
        //        }
        if($slider_layout){
            $answer .= "<script type='text/javascript' src='".Yii::app()->baseUrl."/scripts/jquery/lime-slider.js'></script>";
        }
        if (trim($aQuestionAttributes['equals_num_value']) != ''
        || trim($aQuestionAttributes['min_num_value']) != ''
        || trim($aQuestionAttributes['max_num_value']) != ''
        //        || trim($aQuestionAttributes['num_value_equals_sgqa']) != ''
        //        || trim($aQuestionAttributes['min_num_value_sgqa']) != ''
        //        || trim($aQuestionAttributes['max_num_value_sgqa']) != ''
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
        $answer .= $question_tip."<ul class=\"subquestions-list questions-list text-list numeric-list\">\n".$answer_main."</ul>\n";
    }
    //just added these here so its easy to change in one place
    $errorClass = 'tip problem';
    $goodClass = 'tip good';
    /* ==================================
    Style to be applied to all templates.
    .numeric-multi p.tip.error
    {
    color: #f00;
    }
    .numeric-multi p.tip.good
    {
    color: #0f0;
    }
    */
    $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeperator = $sSeperator['seperator'];
    //    if ($max_num_value || $equals_num_value || $min_num_value)
    //    { //Do value validation
    //        $answer .= '<input type="hidden" name="qattribute_answer[]" value="'.$ia[1]."\" />\n";
    //        $answer .= '<input type="hidden" name="qattribute_answer'.$ia[1]."\" />\n";
    //
    //        $answer .= "<script type='text/javascript'>\n";
    //        $answer .= "    function calculateValue".$ia[1]."(method) {\n";
    //        //Make all empty fields 0 (or else calculation won't work
    //        foreach ($inputnames as $inputname)
    //        {
    //            $answer .= "       if(document.limesurvey.answer".$inputname.".value == '') { document.limesurvey.answer".$inputname.".value = 0; }\n";
    //            $javainputnames[]="parseInt(parseFloat((document.limesurvey.answer".$inputname.".value).split(',').join('.'))*1000)";
    //        }
    //        $answer .= "       bob = eval('document.limesurvey.qattribute_answer".$ia[1]."');\n";
    //        $answer .= "       totalvalue_".$ia[1]."=(";
    //        $answer .= implode(" + ", $javainputnames);
    //        $answer .= ")/1000;\n";
    //        $answer .= "       $('#totalvalue_{$ia[1]}').val((parseFloat(totalvalue_{$ia[1]})+'').split('.').join('{$sSeperator}'));\n";
    //        $answer .= "       var ua = navigator.appVersion.indexOf('MSIE');\n";
    //        $answer .= "       var ieAtt = ua != -1 ? 'className' : 'class';\n";
    //        $answer .= "       switch(method)\n";
    //        $answer .= "       {\n";
    //        $answer .= "       case 1:\n";
    //        $answer .= "          if (totalvalue_".$ia[1]." > $max_num_value)\n";
    //        $answer .= "             {\n";
    //        $answer .= "               bob.value = '".$clang->gT("Answer is invalid. The total of all entries should not add up to more than ").$max_num_value."';\n";
    //        $answer .= "               document.getElementById('totalvalue_{$ia[1]}').setAttribute(ieAtt,'" . $errorClass . "');\n";
    //        $answer .= "               document.getElementById('max_num_value_{$ia[1]}').setAttribute(ieAtt,'" . $errorClass . "');\n";
    //        $answer .= "             }\n";
    //        $answer .= "             else\n";
    //        $answer .= "             {\n";
    //        $answer .= "               if (bob.value == '' || bob.value == '".$clang->gT("Answer is invalid. The total of all entries should not add up to more than ").$max_num_value."')\n";
    //        $answer .= "               {\n";
    //        $answer .= "                 bob.value = '';\n";
    //        //		$answer .= "                 document.getElementById('totalvalue_{$ia[1]}').style.color='black';\n";
    //        $answer .= "                 document.getElementById('totalvalue_{$ia[1]}').setAttribute(ieAtt,'" . $goodClass . "');\n";
    //        $answer .= "               }\n";
    //        //		$answer .= "               document.getElementById('max_num_value_{$ia[1]}').style.color='black';\n";
    //        $answer .= "               document.getElementById('max_num_value_{$ia[1]}').setAttribute(ieAtt,'" . $goodClass . "');\n";
    //        $answer .= "             }\n";
    //        $answer .= "          break;\n";
    //        $answer .= "       case 2:\n";
    //        $answer .= "          if (totalvalue_".$ia[1]." < $min_num_value)\n";
    //        $answer .= "             {\n";
    //        $answer .= "               bob.value = '".sprintf($clang->gT("Answer is invalid. The total of all entries should add up to at least %s.",'js'),$min_num_value)."';\n";
    //        //		$answer .= "               document.getElementById('totalvalue_".$ia[1]."').style.color='red';\n";
    //        //		$answer .= "               document.getElementById('min_num_value_".$ia[1]."').style.color='red';\n";
    //        $answer .= "               document.getElementById('totalvalue_".$ia[1]."').setAttribute(ieAtt,'" . $errorClass . "');\n";
    //        $answer .= "               document.getElementById('min_num_value_".$ia[1]."').setAttribute(ieAtt,'" . $errorClass . "');\n";
    //        $answer .= "             }\n";
    //        $answer .= "             else\n";
    //        $answer .= "             {\n";
    //        $answer .= "               if (bob.value == '' || bob.value == '".sprintf($clang->gT("Answer is invalid. The total of all entries should add up to at least %s.",'js'),$min_num_value)."')\n";
    //        $answer .= "               {\n";
    //        $answer .= "                 bob.value = '';\n";
    //        //		$answer .= "                 document.getElementById('totalvalue_".$ia[1]."').style.color='black';\n";
    //        $answer .= "                 document.getElementById('totalvalue_".$ia[1]."').setAttribute(ieAtt,'" . $goodClass . "');\n";
    //        $answer .= "               }\n";
    //        //		$answer .= "               document.getElementById('min_num_value_".$ia[1]."').style.color='black';\n";
    //        $answer .= "               document.getElementById('min_num_value_".$ia[1]."').setAttribute(ieAtt,'" . $goodClass . "');\n";
    //        $answer .= "             }\n";
    //        $answer .= "          break;\n";
    //        $answer .= "       case 3:\n";
    //        $answer .= "          remainingvalue = (parseInt(parseFloat($equals_num_value)*1000) - parseInt(parseFloat(totalvalue_".$ia[1].")*1000))/1000;\n";
    //        $answer .= "          document.getElementById('remainingvalue_".$ia[1]."').value=remainingvalue;\n";
    //        $answer .= "          if (totalvalue_".$ia[1]." == $equals_num_value)\n";
    //        $answer .= "             {\n";
    //        $answer .= "               if (bob.value == '' || bob.value == '".$clang->gT("Answer is invalid. The total of all entries should not add up to more than ").$equals_num_value."')\n";
    //        $answer .= "               {\n";
    //        $answer .= "                 bob.value = '';\n";
    //        //		$answer .= "                 document.getElementById('totalvalue_".$ia[1]."').style.color='black';\n";
    //        //		$answer .= "                 document.getElementById('equals_num_value_".$ia[1]."').style.color='black';\n";
    //        $answer .= "                 document.getElementById('totalvalue_".$ia[1]."').setAttribute(ieAtt,'" . $goodClass . "');\n";
    //        $answer .= "                 document.getElementById('equals_num_value_".$ia[1]."').setAttribute(ieAtt,'" . $goodClass . "');\n";
    //        $answer .= "               }\n";
    //        $answer .= "             }\n";
    //        $answer .= "             else\n";
    //        $answer .= "             {\n";
    //        $answer .= "             bob.value = '".$clang->gT("Answer is invalid. The total of all entries should not add up to more than ").$equals_num_value."';\n";
    //        //		$answer .= "             document.getElementById('totalvalue_".$ia[1]."').style.color='red';\n";
    //        //		$answer .= "             document.getElementById('equals_num_value_".$ia[1]."').style.color='red';\n";
    //        $answer .= "             document.getElementById('totalvalue_".$ia[1]."').setAttribute(ieAtt,'" . $errorClass . "');\n";
    //        $answer .= "             document.getElementById('equals_num_value_".$ia[1]."').setAttribute(ieAtt,'" . $errorClass . "');\n";
    //        $answer .= "             }\n";
    //        $answer .= "             break;\n";
    //        $answer .= "       }\n";
    //        $answer .= "    }\n";
    //        foreach($calculateValue as $cValue)
    //        {
    //            $answer .= "    calculateValue".$ia[1]."($cValue);\n";
    //        }
    //        $answer .= "</script>\n";
    //
    //    }

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
        $maxlength= "maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= "maxlength='20' ";
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
        $answertypeclass = " integeronly";
    }
    else
    {
        $acomma=getRadixPointData($thissurvey['surveyls_numberformat']);
        $acomma = $acomma['seperator'];

    }
    $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeperator = $sSeperator['seperator'];
    $dispVal = str_replace('.',$sSeperator,$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]);

    if ($thissurvey['nokeyboard']=='Y')
    {
        includeKeypad();
        $extraclass .=" inputkeypad";
        $answertypeclass = "num-keypad";
    }
    else
    {
        $kpclass = "";
    }
    // --> START NEW FEATURE - SAVE
    $answer = "<p class='question answer-item text-item numeric-item {$extraclass}'>"
    . " <label for='answer{$ia[1]}' class='hide label'>{$clang->gT('Answer')}</label>\n$prefix\t"
    . "<input class='text {$answertypeclass}' type=\"text\" size=\"$tiwidth\" name=\"$ia[1]\"  title=\"".$clang->gT('Only numbers may be entered in this field')."\" "
    . "id=\"answer{$ia[1]}\" value=\"{$dispVal}\" title=\"".$clang->gT('Only numbers may be entered in this field')."\" onkeypress=\"return goodchars(event,'-0123456789{$acomma}')\" onchange='$checkconditionFunction(this.value, this.name, this.type)' "
    . " {$maxlength} />\t{$suffix}\n</p>\n";
    if ($aQuestionAttributes['hide_tip']==0)
    {
        $answer .= "<p class=\"tip\">".$clang->gT('Only numbers may be entered in this field')."</p>\n";
    }

    // --> END NEW FEATURE - SAVE

    $inputnames[]=$ia[1];
    $mandatory=null;
    return array($answer, $inputnames, $mandatory);
}




// ---------------------------------------------------------------
function do_shortfreetext($ia)
{
    global $js_header_includes, $thissurvey;

    $clang = Yii::app()->lang;
    $googleMapsAPIKey = Yii::app()->getConfig("googleMapsAPIKey");
    $extraclass ="";
    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    if ($aQuestionAttributes['numbers_only']==1)
    {
        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeperator = $sSeperator['seperator'];
        $numbersonly = 'onkeypress="return goodchars(event,\'-0123456789'.$sSeperator.'\')"';
        $extraclass .=" numberonly";
        $checkconditionFunction = "fixnum_checkconditions";
    }
    else
    {
        $numbersonly = '';
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
        .'rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onchange="'.$checkconditionFunction.'(this.value, this.name, this.type);" '.$numbersonly.'>';
        // --> END NEW FEATURE - SAVE

        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) {
            $dispVal = str_replace("\\", "", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]);
            if ($aQuestionAttributes['numbers_only']==1)
            {
                $dispVal = str_replace('.',$sSeperator,$dispVal);
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

        if ($aQuestionAttributes['location_mapservice']==1 && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off")
            $js_header_includes[] = "https://maps.googleapis.com/maps/api/js?sensor=false";
        else if ($aQuestionAttributes['location_mapservice']==1)
                $js_header_includes[] = "http://maps.googleapis.com/maps/api/js?sensor=false";
            elseif ($aQuestionAttributes['location_mapservice']==2)
                $js_header_includes[] = "http://www.openlayers.org/api/OpenLayers.js";

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
            $dispVal = str_replace('.',$sSeperator,$dispVal);
        }
        $dispVal = htmlspecialchars($dispVal,ENT_QUOTES,'UTF-8');
        $answer .= " value=\"$dispVal\"";

        $answer .=" {$maxlength} onchange=\"$checkconditionFunction(this.value, this.name, this.type)\" $numbersonly />\n\t$suffix\n</p>\n";
    }

    if (trim($aQuestionAttributes['time_limit'])!='')
    {
        $js_header_includes[] = '/scripts/coookies.js';
        $answer .= return_timer_script($aQuestionAttributes, $ia, "answer".$ia[1]);
    }

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);

}

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



// ---------------------------------------------------------------
function do_longfreetext($ia)
{
    global $js_header_includes, $thissurvey;
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
    .'rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onchange="'.$checkconditionFunction.'(this.value, this.name, this.type)">';
    // --> END NEW FEATURE - SAVE

    if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) {$answer .= str_replace("\\", "", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]);}

    $answer .= "</textarea></p>\n";

    if (trim($aQuestionAttributes['time_limit'])!='')
    {
        $js_header_includes[] = '/scripts/coookies.js';
        $answer .= return_timer_script($aQuestionAttributes, $ia, "answer".$ia[1]);
    }

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_hugefreetext($ia)
{
    global $js_header_includes, $thissurvey;
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
    .'rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onchange="'.$checkconditionFunction.'(this.value, this.name, this.type)">';
    // --> END NEW FEATURE - SAVE

    if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]) {$answer .= str_replace("\\", "", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]);}
    $answer .= "</textarea>\n";
    $answer .="</p>";
    if (trim($aQuestionAttributes['time_limit']) != '')
    {
        $js_header_includes[] = '/scripts/coookies.js';
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
        if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == '')
        {
            $answer .= CHECKED;
        }
        // --> START NEW FEATURE - SAVE
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$ia[1]}\" class=\"answertext\">\n\t".$clang->gT('No answer')."\n</label>\n\t</li>\n";
        // --> END NEW FEATURE - SAVE
    }

    $answer .= "</ul>\n\n<input type=\"hidden\" name=\"java{$ia[1]}\" id=\"java{$ia[1]}\" value=\"{ ".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]."}\" />\n";
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
    . '		<input class="radio" type="radio" name="'.$ia[1].'" id="answer'.$ia[1].'F" value="F"';
    if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] == 'F')
    {
        $answer .= CHECKED;
    }
    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
    . '		<label for="answer'.$ia[1].'F" class="answertext">'.$clang->gT('Female')."</label>\n\t</li>\n";

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
    global $notanswered, $thissurvey;
    $extraclass ="";
    $clang = Yii::app()->lang;

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

    $ansquery = "SELECT question FROM {{questions}} WHERE parent_qid=".$ia[0]." AND question like '%|%'";
    $ansresult = dbExecuteAssoc($ansquery);   //Checked

    if ($ansresult->count()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;}
    // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column


    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    }
    else
    {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }

    $ansresult = dbExecuteAssoc($ansquery);     //Checked
    $anscount = $ansresult->count();

    $fn = 1;
    $answer = "\n<table class=\"question subquestion-list questions-list\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - a five point Likert scale array\">\n\n"
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
    . "\t<thead>\n<tr class=\"array1\">\n"
    . "\t<th>&nbsp;</th>\n";
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
    foreach ($ansresult->readAll() as $ansrow)
    {
        $myfname = $ia[1].$ansrow['title'];

        $answertext = dTexts__run($ansrow['question']);
        if (strpos($answertext,'|')) {$answertext=substr($answertext,0,strpos($answertext,'|'));}

        /* Check if this item has not been answered: the 'notanswered' variable must be an array,
        containing a list of unanswered questions, the current question must be in the array,
        and there must be no answer available for the item in this session. */
        if ($ia[6]=='Y' && (is_array($notanswered)) && (array_search($myfname, $notanswered) !== FALSE) && ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') ) {
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
            $answer_t_content .= "\t<td class=\"answer_cell_00$i answer-item radio-item\">\n<label for=\"answer$myfname-$i\">"
            ."\n\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-$i\" value=\"$i\" title=\"$i\"";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $i)
            {
                $answer_t_content .= CHECKED;
            }
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";
        }

        $answertext2 = dTexts__run($ansrow['question']);
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
            $answer_t_content .= "\t<td class=\"answer-item radio-item noanswer-item\">\n<label for=\"answer$myfname-\">"
            ."\n\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer').'"';
            if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
            {
                $answer_t_content .= CHECKED;
            }
            $answer_t_content .= " onclick='$checkconditionFunction(this.value, this.name, this.type)'  />\n</label>\n\t</td>\n";
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
    global $notanswered, $thissurvey;
    $extraclass ="";
    $clang = Yii::app()->lang;

    $checkconditionFunction = "checkconditions";

    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]."  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."'";
    $qresult = dbExecuteAssoc($qquery);      //Checked
    $qrow = $qresult->read(); $other = $qrow['other'];

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
    $anscount = $ansresult->count();

    $fn = 1;
    $answer = "\n<table class=\"question subquestion-list questions-list {$extraclass}\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - a ten point Likert scale array\" >\n\n"
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
        $answer .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth$\" />\n";
    }
    $answer .= "\t</colgroup>\n\n"
    . "\t<thead>\n<tr class=\"array1\">\n"
    . "\t<th>&nbsp;</th>\n";
    for ($xc=1; $xc<=10; $xc++)
    {
        $answer .= "\t<th>$xc</th>\n";
    }
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
    }
    $answer .= "</tr>\n</thead>";
    $answer_t_content = '<tbody';
    $trbc = '';
    foreach ($ansresult->readAll() as $ansrow)
    {
        $myfname = $ia[1].$ansrow['title'];
        $answertext = dTexts__run($ansrow['question']);
        /* Check if this item has not been answered: the 'notanswered' variable must be an array,
        containing a list of unanswered questions, the current question must be in the array,
        and there must be no answer available for the item in this session. */
        if ($ia[6]=='Y' && (is_array($notanswered)) && (array_search($myfname, $notanswered) !== FALSE) && ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == "") ) {
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
            $answer_t_content .= "\t<td class=\"answer_cell_00$i answer-item radio-item\">\n<label for=\"answer$myfname-$i\">\n"
            ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-$i\" value=\"$i\" title=\"$i\"";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $i)
            {
                $answer_t_content .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";
            // --> END NEW FEATURE - SAVE
        }
        if ($ia[6] != "Y" && SHOW_NO_ANSWER == 1)
        {
            $answer_t_content .= "\t<td class=\"answer-item radio-item noanswer-item\">\n<label for=\"answer$myfname-\">\n"
            ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer')."\"";
            if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
            {
                $answer_t_content .= CHECKED;
            }
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";

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
    global $notanswered, $thissurvey;
    $extraclass ="";
    $clang = Yii::app()->lang;

    $checkconditionFunction = "checkconditions";

    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."'";
    $qresult = dbExecuteAssoc($qquery);	//Checked
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
    }
    $cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

    if ($aQuestionAttributes['random_order']==1) {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
    }
    else
    {
        $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
    }
    $ansresult = dbExecuteAssoc($ansquery);	//Checked
    $anscount = $ansresult->count();
    $fn = 1;
    $answer = "\n<table class=\"question subquestions-list questions-list {$extraclass}\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - a Yes/No/uncertain Likert scale array\">\n"
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
    . "\t<th>".$clang->gT('Yes')."</th>\n"
    . "\t<th>".$clang->gT('Uncertain')."</th>\n"
    . "\t<th>".$clang->gT('No')."</th>\n";
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
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
        foreach($ansresult->readAll() as $ansrow)
        {
            $myfname = $ia[1].$ansrow['title'];
            $answertext = dTexts__run($ansrow['question']);
            /* Check if this item has not been answered: the 'notanswered' variable must be an array,
            containing a list of unanswered questions, the current question must be in the array,
            and there must be no answer available for the item in this session. */
            if ($ia[6]=='Y' && (is_array($notanswered)) && (array_search($myfname, $notanswered) !== FALSE) && ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') ) {
                $answertext = "<span class='errormandatory'>{$answertext}</span>";
            }
            $trbc = alternation($trbc , 'row');

            // Get array_filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname,"tr","$trbc answers-list radio-list");

            $answer_t_content .= $htmltbody2;

            $answer_t_content .= "\t<th class=\"answertext\">\n"
            . $hiddenfield
            . "\t\t\t\t$answertext</th>\n"
            . "\t<td class=\"answer_cell_Y answer-item radio-item\">\n<label for=\"answer$myfname-Y\">\n"
            . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-Y\" value=\"Y\" title=\"".$clang->gT('Yes').'"';
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'Y')
            {
                $answer_t_content .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n\t</label>\n\t</td>\n"
            . "\t<td class=\"answer_cell_U answer-item radio-item\">\n<label for=\"answer$myfname-U\">\n"
            . "<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-U\" value=\"U\" title=\"".$clang->gT('Uncertain')."\"";
            // --> END NEW FEATURE - SAVE

            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'U')
            {
                $answer_t_content .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n"
            . "\t<td class=\"answer_cell_N answer-item radio-item\">\n<label for=\"answer$myfname-N\">\n"
            . "<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-N\" value=\"N\" title=\"".$clang->gT('No').'"';
            // --> END NEW FEATURE - SAVE

            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'N')
            {
                $answer_t_content .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n"
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            // --> END NEW FEATURE - SAVE
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                $answer_t_content .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            }
            $answer_t_content .= "\" />\n\t</td>\n";

            if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
            {
                $answer_t_content .= "\t<td class=\"answer-item radio-item noanswer-item\">\n\t<label for=\"answer$myfname-\">\n"
                . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer')."\"";
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
    $answer .=  $answer_t_content . "\t\</tbody>\n</table>\n";
    return array($answer, $inputnames);
}

// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array_increasesamedecrease($ia)
{
    global $thissurvey;
    global $notanswered;
    $extraclass ="";
    $clang = Yii::app()->lang;

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
    $anscount = $ansresult->count();

    $fn = 1;

    $answer = "\n<table class=\"question subquestions-list questions-list {$extraclass}\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - Increase/Same/Decrease Likert scale array\">\n"
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
    . "<tr>\n"
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
    foreach($ansresult->readAll() as $ansrow)
    {
        $myfname = $ia[1].$ansrow['title'];
        $answertext = dTexts__run($ansrow['question']);
        /* Check if this item has not been answered: the 'notanswered' variable must be an array,
        containing a list of unanswered questions, the current question must be in the array,
        and there must be no answer available for the item in this session. */
        if ($ia[6]=='Y' && (is_array($notanswered)) && (array_search($myfname, $notanswered) !== FALSE) && ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == "") )
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
        . "<label for=\"answer$myfname-I\">\n"
        ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-I\" value=\"I\" title=\"".$clang->gT('Increase').'"';
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'I')
        {
            $answer_body .= CHECKED;
        }

        $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
        . "</label>\n"
        . "\t</td>\n"
        . "\t<td class=\"answer_cell_S answer-item radio-item\">\n"
        . "<label for=\"answer$myfname-S\">\n"
        . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-S\" value=\"S\" title=\"".$clang->gT('Same').'"';

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'S')
        {
            $answer_body .= CHECKED;
        }

        $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
        . "</label>\n"
        . "\t</td>\n"
        . "\t<td class=\"answer_cell_D answer-item radio-item\">\n"
        . "<label for=\"answer$myfname-D\">\n"
        . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-D\" value=\"D\" title=\"".$clang->gT('Decrease').'"';
        // --> END NEW FEATURE - SAVE
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == 'D')
        {
            $answer_body .= CHECKED;
        }

        $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
        . "</label>\n"
        . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {$answer_body .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];}
        $answer_body .= "\" />\n\t</td>\n";

        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            $answer_body .= "\t<td class=\"answer-item radio-item noanswer-item\">\n"
            . "<label for=\"answer$myfname-\">\n"
            . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer').'"';
            if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
            {
                $answer_body .= CHECKED;
            }
            $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
            . "</label>\n"
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
    global $notanswered;
    $repeatheadings = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
    $extraclass ="";
    $clang = Yii::app()->lang;

    $checkconditionFunction = "checkconditions";
    $qquery = "SELECT other FROM {{questions}} WHERE qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."'";
    $qresult = dbExecuteAssoc($qquery);     //Checked
    $qrow = $qresult->read(); $other = $qrow['other'];
    $lquery = "SELECT * FROM {{answers}} WHERE qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY sortorder, code";

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
    }
    else
    {
        $useDropdownLayout = false;
    }

    $lresult = dbExecuteAssoc($lquery);   //Checked
    if ($useDropdownLayout === false && $lresult->count() > 0)
    {
        foreach ($lresult->readAll() as $lrow)
        {
            $labelans[]=$lrow['answer'];
            $labelcode[]=$lrow['code'];
        }

        //		$cellwidth=sprintf('%02d', $cellwidth);

        $ansquery = "SELECT question FROM {{questions}} WHERE parent_qid={$ia[0]} AND question like '%|%' ";
        $ansresult = dbExecuteAssoc($ansquery);  //Checked
        if ($ansresult->count()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;}
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        }
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }
        $ansresult = dbExecuteAssoc($ansquery); //Checked
        $anscount = $ansresult->count();
        $fn=1;

        $numrows = count($labelans);
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
        $answer_head = "\t<thead>\n"
        . "<tr>\n"
        . "\t<td>&nbsp;</td>\n";
        foreach ($labelans as $ld)
        {
            $answer_head .= "\t<th>".$ld."</th>\n";
        }
        if ($right_exists) {$answer_head .= "\t<td>&nbsp;</td>\n";}
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory and we can show "no answer"
        {
            $answer_head .= "\t<th>".$clang->gT('No answer')."</th>\n";
        }
        $answer_head .= "</tr>\n\t</thead>\n\n\t\n";

        $answer = '<tbody>';
        $trbc = '';
        $inputnames=array();

        foreach($ansresult->readAll() as $ansrow)
        {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
            {
                if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                {
                    $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                    $answer .= "<tr class=\"repeat headings\">\n"
                    . "\t<td>&nbsp;</td>\n";
                    foreach ($labelans as $ld)
                    {
                        $answer .= "\t<th>".$ld."</th>\n";
                    }
                    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory and we can show "no answer"
                    {
                        $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
                    }
                    $answer .= "</tr>\n";
                }
            }
            $myfname = $ia[1].$ansrow['title'];
            $answertext = dTexts__run($ansrow['question']);
            $answertextsave=$answertext;
            if (strpos($answertext,'|'))
            {
                $answertext=substr($answertext,0, strpos($answertext,'|'));
            }
            /* Check if this item has not been answered: the 'notanswered' variable must be an array,
            containing a list of unanswered questions, the current question must be in the array,
            and there must be no answer available for the item in this session. */

            if (strpos($answertext,'|')) {$answerwidth=$answerwidth/2;}

            if ($ia[6]=='Y' && (is_array($notanswered)) && (array_search($myfname, $notanswered) !== FALSE) && ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') ) {
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
                . "<label for=\"answer$myfname-$ld\">\n"
                . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"$ld\" id=\"answer$myfname-$ld\" title=\""
                . HTMLEscape(strip_tags($labelans[$thiskey])).'"';
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $ld)
                {
                    $answer .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
                . "</label>\n"
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
                $answer .= "\t<td class=\"answer-item radio-item noanswer-item\">\n<label for=\"answer$myfname-\">\n"
                ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"\" id=\"answer$myfname-\" title=\"".$clang->gT('No answer').'"';
                if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
                {
                    $answer .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\"  />\n</label>\n\t</td>\n";
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
    elseif ($useDropdownLayout === true && $lresult->count() > 0)
    {
        foreach($lresult->readAll() as $lrow)
            $labels[]=Array('code' => $lrow['code'],
            'answer' => $lrow['answer']);
        $ansquery = "SELECT question FROM {{questions}} WHERE parent_qid={$ia[0]} AND question like '%|%' ";
        $ansresult = dbExecuteAssoc($ansquery);  //Checked
        if ($ansresult->count()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;}
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        }
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }
        $ansresult = dbExecuteAssoc($ansquery); //Checked
        $anscount = $ansresult->count();
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

        foreach ($ansresult->readAll() as $ansrow)
        {
            $myfname = $ia[1].$ansrow['title'];
            $trbc = alternation($trbc , 'row');
            $answertext=$ansrow['question'];
            $answertextsave=$answertext;
            if (strpos($answertext,'|'))
            {
                $answertext=substr($answertext,0, strpos($answertext,'|'));
            }
            /* Check if this item has not been answered: the 'notanswered' variable must be an array,
            containing a list of unanswered questions, the current question must be in the array,
            and there must be no answer available for the item in this session. */

            if (strpos($answertext,'|')) {$answerwidth=$answerwidth/2;}

            if ($ia[6]=='Y' && (is_array($notanswered)) && (array_search($myfname, $notanswered) !== FALSE) && ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') ) {
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

            if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] =='')
            {
                $answer .= "\t<option value=\"\" ".SELECTED.'>'.$clang->gT('Please choose')."...</option>\n";
            }

            foreach ($labels as $lrow)
            {
                $answer .= "\t<option value=\"".$lrow['code'].'" ';
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $lrow['code'])
                {
                    $answer .= SELECTED;
                }
                $answer .= '>'.$lrow['answer']."</option>\n";
            }
            // If not mandatory and showanswer, show no ans
            if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
            {
                $answer .= "\t<option value=\"\" ";
                if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
                {
                    $answer .= SELECTED;
                }
                $answer .= '>'.$clang->gT('No answer')."</option>\n";
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
    global $notanswered;
    $repeatheadings = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
    $extraclass ="";
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

    $checkconditionFunction = "checkconditions";
    $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeperator = $sSeperator['seperator'];

    $defaultvaluescript = "";
    $qquery = "SELECT other FROM {{questions}} WHERE qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."'";

    $qresult = Yii::app()->db->createCommand($qquery)->query();
    $qrow = $qresult->read(); $other = $qrow['other'];

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
    $numbersonly = '';

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
        $q_table_id = 'totals_'.$ia[0];
        $q_table_id_HTML = ' id="'.$q_table_id.'"';
        //	$numbersonly = 'onkeypress="return goodchars(event,\'-0123456789.\')"';
        $num_class = ' numbers-only';
        $extraclass.=" numberonly";
        switch ($aQuestionAttributes['show_totals'])
        {
            case 'R':
                $totals_class = $show_totals = 'row';
                $row_total = '<td class="total information-item">
                <label>
                <input name="[[ROW_NAME]]_total" title="[[ROW_NAME]] total" size="[[INPUT_WIDTH]]" value="" type="text" disabled="disabled" class="disabled" />
                </label>
                </td>';
                $col_head = '			<th class="total">Total</th>';
                if($show_grand == true)
                {
                    $row_head = '
                    <th class="answertext total">Grand total</th>';
                    $col_total = '
                    <td>&nbsp;</td>';
                    $grand_total = '
                    <td class="total grand information-item">
                    <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                    </td>';
                };
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
                    $col_head = '			<th class="total">Grand Total</th>';
                    $grand_total = '
                    <td class="total grand">
                    <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                    </td>';
                };
                break;
            case 'B':
                $totals_class = $show_totals = 'both';
                $row_total = '			<td class="total information-item">
                <label>
                <input name="[[ROW_NAME]]_total" title="[[ROW_NAME]] total" size="[[INPUT_WIDTH]]" value="" type="text" disabled="disabled" class="disabled" />
                </label>
                </td>';
                $col_total = '
                <td  class="total information-item">
                <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                </td>';
                $col_head = '			<th class="total">Total</th>';
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
        $numbersonly = '';
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
    if (count($lresult)> 0)
    {
        foreach($lresult->readAll() as $lrow)
        {
            $labelans[]=$lrow['question'];
            $labelcode[]=$lrow['title'];
        }
        $numrows=count($labelans);
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {$numrows++;}
        if( ($show_grand == true &&  $show_totals == 'col' ) || $show_totals == 'row' ||  $show_totals == 'both' )
        {
            ++$numrows;
        };
        $cellwidth=$columnswidth/$numrows;

        $cellwidth=sprintf('%02d', $cellwidth);

        $ansquery = "SELECT count(question) FROM {{questions}} WHERE parent_qid={$ia[0]} and scale_id=0 AND question like '%|%'";
        $ansresult = reset(dbExecuteAssoc($ansquery)->read());
        if ($ansresult>0)
        {
            $right_exists=true;
            $answerwidth=$answerwidth/2;
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
        $anscount = $ansresult->count();
        $fn=1;

        $answer_cols = "\t<colgroup class=\"col-responses\">\n"
        ."\n\t\t<col class=\"answertext\" width=\"$answerwidth%\" />\n";

        $answer_head = "\n\t<thead>\n"
        . "\t\t<tr>\n"
        . "\t\t\t<td width='$answerwidth%'>&nbsp;</td>\n";

        $odd_even = '';
        foreach ($labelans as $ld)
        {
            $answer_head .= "\t<th class=\"answertext\">".$ld."</th>\n";
            $odd_even = alternation($odd_even);
            $answer_cols .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        if ($right_exists)
        {
            $answer_head .= "\t<td>&nbsp;</td>\n";// class=\"answertextright\"
            $odd_even = alternation($odd_even);
            $answer_cols .= "<col class=\"answertextright $odd_even\" width=\"$cellwidth%\" />\n";
        }

        if( ($show_grand == true &&  $show_totals == 'col' ) || $show_totals == 'row' ||  $show_totals == 'both' )
        {
            $answer_head .= $col_head;
            $odd_even = alternation($odd_even);
            $answer_cols .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        $answer_cols .= "\t</colgroup>\n";

        $answer_head .= "</tr>\n"
        . "\t</thead>\n";

        $answer = "\n<table$q_table_id_HTML class=\"question subquestions-list questions-list{$extraclass}$num_class"."$totals_class\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an array of text responses\">\n" . $answer_cols . $answer_head;
        $answer .= "<tbody>";
        $trbc = '';
        foreach ($ansresult->readAll() as $ansrow)
        {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
            {
                if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                {
                    $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                    $answer .= "<tr class=\"repeat headings\">\n"
                    . "\t<td>&nbsp;</td>\n";
                    foreach ($labelans as $ld)
                    {
                        $answer .= "\t<th>".$ld."</th>\n";
                    }
                    $answer .= "</tr>\n";
                }
            }
            $myfname = $ia[1].$ansrow['title'];
            $answertext = dTexts__run($ansrow['question']);
            $answertextsave=$answertext;
            /* Check if this item has not been answered: the 'notanswered' variable must be an array,
            containing a list of unanswered questions, the current question must be in the array,
            and there must be no answer available for the item in this session. */
            if ($ia[6]=='Y' && is_array($notanswered))
            {
                //Go through each labelcode and check for a missing answer! If any are found, highlight this line
                $emptyresult=0;
                foreach($labelcode as $ld)
                {
                    $myfname2=$myfname.'_'.$ld;
                    if((array_search($myfname2, $notanswered) !== FALSE) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2] == '')
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
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {$answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];}
            $answer .= "\" />\n\t\t\t</th>\n";
            $thiskey=0;
            foreach ($labelcode as $ld)
            {

                $myfname2=$myfname."_$ld";
                $myfname2value = isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]) ? $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2] : "";
                $answer .= "\t<td class=\"answer_cell_00$ld answer-item text-item\">\n"
                . "\t\t\t\t<label for=\"answer{$myfname2}\">\n"
                . "\t\t\t\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" />\n"
                . "\t\t\t\t<input type=\"text\" name=\"$myfname2\" id=\"answer{$myfname2}\" class=\"".$kpclass."\" {$maxlength} title=\""
                . flattenText($labelans[$thiskey]).'" '
                . 'size="'.$inputwidth.'" '
                . ' value="'.str_replace ('"', "'", str_replace('\\', '', $myfname2value))."\" />\n";
                $inputnames[]=$myfname2;
                $answer .= "\t\t\t\t</label>\n\t\t\t</td>\n";
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
                $radix = $sSeperator;
            }
            else {
                $radix = 'X';   // to indicate that should not try to change entered values
            }
            $answer .= "\n<script type=\"text/javascript\">new multi_set('$q_table_id','$radix');</script>\n";
        }
        else
        {
            $addcheckcond = <<< EOD
<script type="text/javascript">
<!--
$(document).ready(function()
{
    $('#question{$ia[0]} :input:visible:enabled').each(function(index){
        $(this).bind('change',function(e) {
            checkconditions($(this).attr('value'), $(this).attr('name'), $(this).attr('type'));
            return true;
        })
    })
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
    global $notanswered;
    $repeatheadings = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
    $extraclass ="";
    $answertypeclass = "";
    $clang = Yii::app()->lang;

    $checkconditionFunction = "fixnum_checkconditions";
    //echo '<pre>'; print_r($_POST); echo '</pre>';
    $defaultvaluescript = '';
    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and parent_qid=0";
    $qresult = dbExecuteAssoc($qquery);
    $qrow = $qresult->read(); $other = $qrow['other'];

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
    if ($aQuestionAttributes['multiflexible_checkbox']!=0)
    {
        $minvalue=0;
        $maxvalue=1;
        $checkboxlayout=true;
        $answertypeclass .=" checkbox";
    }

    $inputboxlayout=false;
    if ($aQuestionAttributes['input_boxes']!=0 && !$checkboxlayout) // checkboxlayout have the
    {
        $inputboxlayout=true;
        $answertypeclass .=" numberonly text";
    }
    if (!$checkboxlayout && !$inputboxlayout)
    {
        $answertypeclass .=" dropdown";
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
        $kpclass = "num-keypad";
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
    if ($lresult->count() > 0)
    {
        foreach ($lresult->readAll() as $lrow)
        {
            $labelans[]=$lrow['question'];
            $labelcode[]=$lrow['title'];
        }
        $numrows=count($labelans);
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {$numrows++;}
        $cellwidth=$columnswidth/$numrows;

        $cellwidth=sprintf('%02d', $cellwidth);

        $ansquery = "SELECT question FROM {{questions}} WHERE parent_qid=".$ia[0]." AND scale_id=0 AND question like '%|%'";
        $ansresult = dbExecuteAssoc($ansquery);
        if ($ansresult->count()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;}
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

        $myheader = "\n\t<thead>\n"
        . "<tr>\n"
        . "\t<td >&nbsp;</td>\n";

        $odd_even = '';
        foreach ($labelans as $ld)
        {
            $myheader .= "\t<th>".$ld."</th>\n";
            $odd_even = alternation($odd_even);
            $mycols .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        if ($right_exists)
        {
            $myheader .= "\t<td>&nbsp;</td>";
            $odd_even = alternation($odd_even);
            $mycols .= "<col class=\"answertextright $odd_even\" width=\"$answerwidth%\" />\n";
        }
        $myheader .= "</tr>\n"
        . "\t</thead>\n";
        $mycols .= "\t</colgroup>\n";

        $trbc = '';
        $answer = "\n<table class=\"question subquestions-list questions-list {$answertypeclass}-list {$extraclass}\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an array type question with dropdown responses\">\n" . $mycols . $myheader . "\n";
        $answer .= "<tbody>";
        foreach ($ansresult as $ansrow)
        {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
            {
                if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                {
                    $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                    $answer .= "<tr class=\"repeat headings\">\n"
                    . "\t<td>&nbsp;</td>\n";
                    foreach ($labelans as $ld)
                    {
                        $answer .= "\t<th>".$ld."</th>\n";
                    }
                    $answer .= "</tr>\n\n";
                }
            }
            $myfname = $ia[1].$ansrow['title'];
            $answertext = dTexts__run($ansrow['question']);
            $answertextsave=$answertext;
            /* Check if this item has not been answered: the 'notanswered' variable must be an array,
            containing a list of unanswered questions, the current question must be in the array,
            and there must be no answer available for the item in this session. */
            if ($ia[6]=='Y' && is_array($notanswered))
            {
                //Go through each labelcode and check for a missing answer! If any are found, highlight this line
                $emptyresult=0;
                foreach($labelcode as $ld)
                {
                    $myfname2=$myfname.'_'.$ld;
                    if((array_search($myfname2, $notanswered) !== FALSE) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2] == "")
                    {
                        $emptyresult=1;
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
                    $answer .= "\t<td class=\"answer_cell_00$ld question-item answer-item {$answertypeclass}-item\">\n"
                    . "<label for=\"answer{$myfname2}\">\n"
                    . "\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" $myfname2_java_value />\n";

                    if($inputboxlayout == false) {
                        $answer .= "\t<select class=\"multiflexiselect\" name=\"$myfname2\" id=\"answer{$myfname2}\" title=\""
                        . HTMLEscape($labelans[$thiskey]).'"'
                        . " onchange=\"$checkconditionFunction(this.value, this.name, this.type)\">\n"
                        . "<option value=\"\">".$clang->gT('...')."</option>\n";

                        for($ii=$minvalue; ($reverse? $ii>=$maxvalue:$ii<=$maxvalue); $ii+=$stepvalue) {
                            $answer .= "<option value=\"$ii\"";
                            if(isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2] == $ii) {
                                $answer .= SELECTED;
                            }
                            $answer .= ">$ii</option>\n";
                        }
                        $answer .= "\t</select>\n";
                    } elseif ($inputboxlayout == true)
                    {
                        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
                        $sSeperator = $sSeperator['seperator'];
                        $answer .= "\t<input type='text' class=\"multiflexitext $kpclass\" name=\"$myfname2\" id=\"answer{$myfname2}\" {$maxlength} size=5 title=\""
                        . HTMLEscape($labelans[$thiskey]).'"'
                        . " onchange=\"$checkconditionFunction(this.value, this.name, this.type)\" onkeypress=\"return goodchars(event,'-0123456789$sSeperator')\""
                        . " value=\"";
                        if(isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]) {
                            $dispVal = str_replace('.',$sSeperator,$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]);
                            $answer .= $dispVal;
                        }
                        $answer .= "\" />\n";
                    }
                    $answer .= "</label>\n"
                    . "\t</td>\n";

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
                    //					. "<label for=\"answer{$myfname2}\">\n"
                    . "\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" value=\"$myvalue\"/>\n"
                    . "\t<input type=\"hidden\" name=\"$myfname2\" id=\"answer{$myfname2}\" value=\"$myvalue\" />\n";
                    $answer .= "\t<input type=\"checkbox\" name=\"cbox_$myfname2\" id=\"cbox_$myfname2\" $setmyvalue "
                    . " onclick=\"cancelBubbleThis(event); "
                    . " aelt=document.getElementById('answer{$myfname2}');"
                    . " jelt=document.getElementById('java{$myfname2}');"
                    . " if(this.checked) {"
                    . "  aelt.value=1;jelt.value=1;$checkconditionFunction(1,'{$myfname2}',aelt.type);"
                    . " } else {"
                    . "  aelt.value=0;jelt.value=0;$checkconditionFunction(0,'{$myfname2}',aelt.type);"
                    . " }; return true;\" "
                    //					. " onchange=\"checkconditions(this.value, this.name, this.type)\" "
                    . " />\n";
                    $inputnames[]=$myfname2;
                    //					$answer .= "</label>\n"
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
    global $notanswered;
    $clang = Yii::app()->lang;
    $extraclass = "";
    $checkconditionFunction = "checkconditions";

    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);
    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."'";
    $qresult = dbExecuteAssoc($qquery);    //Checked
    $qrow = $qresult->read(); $other = $qrow['other'];
    $lquery = "SELECT * FROM {{answers}} WHERE qid=".$ia[0]."  AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY sortorder, code";
    $lresult = dbExecuteAssoc($lquery);   //Checked
    if ($lresult->count() > 0)
    {
        foreach ($lresult->readAll() as $lrow)
        {
            $labelans[]=$lrow['answer'];
            $labelcode[]=$lrow['code'];
            $labels[]=array("answer"=>$lrow['answer'], "code"=>$lrow['code']);
        }
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
        $anscount = $ansresult->count();
        if ($anscount>0)
        {
            $fn=1;
            $cellwidth=$anscount;
            $cellwidth=round(( 50 / $cellwidth ) , 1);
            $answer = "\n<table class=\"question subquestions-list questions-list\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an array type question with a single response per column\">\n\n"
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

            while ($ansrow = $ansresult->read())
            {
                $anscode[]=$ansrow['title'];
                $answers[]=dTexts__run($ansrow['question']);
            }
            $trbc = '';
            $odd_even = '';
            for ($_i=0;$_i<count($answers);++$_i)
            {
                $ld = $answers[$_i];
                $myfname = $ia[1].$anscode[$_i];
                $trbc = alternation($trbc , 'row');
                /* Check if this item has not been answered: the 'notanswered' variable must be an array,
                containing a list of unanswered questions, the current question must be in the array,
                and there must be no answer available for the item in this session. */
                if ($ia[6]=='Y' && (is_array($notanswered)) && (array_search($myfname, $notanswered) !== FALSE) && ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == "") )
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
            foreach($ansresult->readAll() as $ansrow)
            {
                $ansrowcount++;
                $ansrowtotallength=$ansrowtotallength+strlen($ansrow['question']);
            }
            $percwidth=100 - ($cellwidth*$anscount);
            foreach($labels as $ansrow)
            {
                $answer .= "<tr>\n"
                . "\t<th class=\"arraycaptionleft\">{$ansrow['answer']}</th>\n";
                foreach ($anscode as $ld)
                {
                    //if (!isset($trbc) || $trbc == 'array1') {$trbc = 'array2';} else {$trbc = 'array1';}
                    $myfname=$ia[1].$ld;
                    $answer .= "\t<td class=\"answer_cell_00$ld answer-item radio-item\">\n"
                    . "<label for=\"answer".$myfname.'-'.$ansrow['code']."\">\n"
                    . "\t<input class=\"radio\" type=\"radio\" name=\"".$myfname.'" value="'.$ansrow['code'].'" '
                    . 'id="answer'.$myfname.'-'.$ansrow['code'].'" '
                    . 'title="'.HTMLEscape(strip_tags($ansrow['answer'])).'"';
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
                    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";
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
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array_dual($ia)
{
    global $thissurvey;
    global $notanswered;
    $repeatheadings = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
    $extraclass ="";
    $answertypeclass = ""; // Maybe not
    $clang = Yii::app()->lang;

    $checkconditionFunction = "checkconditions";

    $inputnames=array();
    $labelans1=array();
    $labelans=array();
    $qquery = "SELECT other FROM {{questions}} WHERE qid=".$ia[0]." AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."'";
    $other = reset(dbExecuteAssoc($qquery)->read());    //Checked
    $lquery =  "SELECT * FROM {{answers}} WHERE scale_id=0 AND qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY sortorder, code";
    $lquery1 = "SELECT * FROM {{answers}} WHERE scale_id=1 AND qid={$ia[0]} AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY sortorder, code";
    $aQuestionAttributes = getQuestionAttributeValues($ia[0], $ia[4]);

    if ($aQuestionAttributes['use_dropdown']==1)
    {
        $useDropdownLayout = true;
        $extraclass .=" dropdown-list";
        $answertypeclass .=" dropdown";
    }
    else
    {
        $useDropdownLayout = false;
        $extraclass .=" radio-list";
        $answertypeclass .=" radio";
    }

    if (trim($aQuestionAttributes['dualscale_headerA'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='') {
        $leftheader= $clang->gT($aQuestionAttributes['dualscale_headerA'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]);
    }
    else
    {
        $leftheader ='';
    }

    if (trim($aQuestionAttributes['dualscale_headerB'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']])!='')
    {
        $rightheader= $clang->gT($aQuestionAttributes['dualscale_headerB'][$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']]);
    }
    else
    {
        $rightheader ='';
    }

    $lresult = dbExecuteAssoc($lquery); //Checked
    if ($useDropdownLayout === false && $lresult->count() > 0)
    {
        if (trim($aQuestionAttributes['answer_width'])!='')
        {
            $answerwidth=$aQuestionAttributes['answer_width'];
        }
        else
        {
            $answerwidth=20;
        }
        $columnswidth = 100 - $answerwidth;

        foreach ($lresult->readAll() as $lrow)
        {
            $labelans[]=$lrow['answer'];
            $labelcode[]=$lrow['code'];
        }
        $lresult1 = dbExecuteAssoc($lquery1); //Checked
        if ($lresult1->count() > 0)
        {
            foreach ($lresult1->readAll() as $lrow1)
            {
                $labelans1[]=$lrow1['answer'];
                $labelcode1[]=$lrow1['code'];
            }
        }
        $numrows=count($labelans) + count($labelans1);
        if ($ia[6] != "Y" && SHOW_NO_ANSWER == 1) {$numrows++;}
        $cellwidth=$columnswidth/$numrows;

        $cellwidth=sprintf("%02d", $cellwidth);

        $ansquery = "SELECT question FROM {{questions}} WHERE parent_qid=".$ia[0]." and scale_id=0 AND question like '%|%'";
        $ansresult = dbExecuteAssoc($ansquery);   //Checked
        if ($ansresult->count()>0)
        {
            $right_exists=true;
        }
        else
        {
            $right_exists=false;
        }
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
        }
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY question_order";
        }
        $ansresult = dbExecuteAssoc($ansquery);   //Checked
        $anscount = $ansresult->count();
        $fn=1;
        // unselect second scale when using "no answer"
        $answer = "<script type='text/javascript'>\n"
        . "<!--\n"
        . "function noanswer_checkconditions(value, name, type)\n"
        . "{\n"
        . "\tvar vname;\n"
        . "\tvname = name.replace(/#.*$/,\"\");\n"
        . "\t$('input[name^=\"' + vname + '\"]').attr('checked',false);\n"
        . "\t$('input[id=\"answer' + vname + '#0-\"]').attr('checked',true);\n"
        . "\t$('input[name^=\"java' + vname + '\"]').val('');\n"
        . "\t$checkconditionFunction(value, name, type);\n"
        . "}\n"
        . "function secondlabel_checkconditions(value, name, type)\n"
        . "{\n"
        . "\tvar vname;\n"
        . "\tvname = \"answer\"+name.replace(/#1/g,\"#0-\");\n"
        . "\tif(document.getElementById(vname))\n"
        . "\t{\n"
        . "\tdocument.getElementById(vname).checked=false;\n"
        . "\t}\n"
        . "\t$checkconditionFunction(value, name, type);\n"
        . "}\n"
        . " //-->\n"
        . " </script>\n";

        // Header row and colgroups
        $mycolumns = "\t<colgroup class=\"col-responses group-1\">\n"
        ."\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";

        $myheader2 = "\n<tr class=\"array1 header_row\">\n"
        . "\t<th class=\"header_answer_text\">&nbsp;</th>\n\n";
        $odd_even = '';
        foreach ($labelans as $ld)
        {
            $myheader2 .= "\t<th>".$ld."</th>\n";
            $odd_even = alternation($odd_even);
            $mycolumns .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        $mycolumns .= "\t</colgroup>\n";

        if (count($labelans1)>0) // if second label set is used
        {
            $mycolumns .= "\t<colgroup class=\"col-responses group-2\">\n"
            . "\t<col class=\"seperator\" />\n";
            $myheader2 .= "\n\t<td class=\"header_separator\">&nbsp;</td>\n\n"; // Separator
            foreach ($labelans1 as $ld)
            {
                $myheader2 .= "\t<th>".$ld."</th>\n";
                $odd_even = alternation($odd_even);
                $mycolumns .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
            }

        }
        if ($right_exists)
        {
            $myheader2 .= "\t<td class=\"header_answer_text_right\">&nbsp;</td>\n";
            $mycolumns .= "\n\t<col class=\"answertextright\" />\n\n";
        }
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory and we can show "no answer"
        {
            $myheader2 .= "\t<td class=\"header_separator\">&nbsp;</td>\n"; // Separator
            $myheader2 .= "\t<th class=\"header_no_answer\">".$clang->gT('No answer')."</th>\n";
            $odd_even = alternation($odd_even);
            $mycolumns .= "\n\t<col class=\"seperator\" />\n\n";
            $mycolumns .= "\t<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
        }

        $mycolumns .= "\t</colgroup>\n";
        $myheader2 .= "</tr>\n";

        // build first row of header if needed
        if ($leftheader != '' || $rightheader !='')
        {
            $myheader1 = "<tr class=\"array1 groups header_row\">\n"
            . "\t<th class=\"header_answer_text\">&nbsp;</th>\n"
            . "\t<th colspan=\"".count($labelans)."\" class=\"dsheader\">$leftheader</th>\n";

            if (count($labelans1)>0)
            {
                $myheader1 .= "\t<td class=\"header_separator\">&nbsp;</td>\n" // Separator
                ."\t<th colspan=\"".count($labelans1)."\" class=\"dsheader\">$rightheader</th>\n";
            }
            if ($right_exists)
            {
                $myheader1 .= "\t<td class=\"header_answer_text_right\">&nbsp;</td>\n";
            }
            if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
            {
                $myheader1 .= "\t<td class=\"header_separator\">&nbsp;</td>\n"; // Separator
                $myheader1 .= "\t<th class=\"header_no_answer\">&nbsp;</th>\n";
            }
            $myheader1 .= "</tr>\n";
        }
        else
        {
            $myheader1 = '';
        }

        $answer .= "\n<table class=\"question subquestions-list questions-list\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - a dual array type question\">\n"
        . $mycolumns
        . "\n\t<thead>\n"
        . $myheader1
        . $myheader2
        . "\n\t</thead>\n"
        . "<tbody>\n";

        $trbc = '';
        foreach ($ansresult->readAll() as $ansrow)
        {
            // Build repeat headings if needed
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
            {
                if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                {
                    $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                    $answer .= "\n<tr  class=\"repeat headings\">\n"
                    . "\t<th class=\"header_answer_text\">&nbsp;</th>\n";
                    foreach ($labelans as $ld)
                    {
                        $answer .= "\t<th>".$ld."</th>\n";
                    }
                    if (count($labelans1)>0) // if second label set is used
                    {
                        $answer .= "<th class=\"header_separator\">&nbsp;</th>\n"; // Separator
                        foreach ($labelans1 as $ld)
                        {
                            $answer .= "\t<th>".$ld."</th>\n";
                        }
                    }
                    if ($right_exists)
                    {
                        $answer .= "\t<td class=\"header_answer_text_right\">&nbsp;</td>\n";
                    }
                    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory and we can show "no answer"
                    {
                        $answer .= "\t<td class=\"header_separator\">&nbsp;</td>\n"; // Separator
                        $answer .= "\t<th class=\"header_no_answer\">".$clang->gT('No answer')."</th>\n";
                    }
                    $answer .= "</tr>\n";
                }
            }

            $trbc = alternation($trbc , 'row');
            $answertext=dTexts__run($ansrow['question']);
            $answertextsave=$answertext;

            $dualgroup=0;
            $myfname0= $ia[1].$ansrow['title'];
            $myfname = $ia[1].$ansrow['title'].'#0';
            $myfname1 = $ia[1].$ansrow['title'].'#1'; // new multi-scale-answer
            /* Check if this item has not been answered: the 'notanswered' variable must be an array,
            containing a list of unanswered questions, the current question must be in the array,
            and there must be no answer available for the item in this session. */
            if ($ia[6]=='Y' && (is_array($notanswered)) && ((array_search($myfname, $notanswered) !== FALSE) || (array_search($myfname1, $notanswered) !== FALSE)) && (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1] == '')) )
            {
                $answertext = "<span class='errormandatory'>{$answertext}</span>";
            }

            // Get array_filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $myfname0, $trbc, $myfname,"tr","$trbc answers-list radio-list");

            $answer .= $htmltbody2;

            if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}

            array_push($inputnames,$myfname);
            $answer .= "\t<th class=\"answertext\">\n"
            . $hiddenfield
            . "$answertext\n"
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])) {$answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];}
            $answer .= "\" />\n\t</th>\n";
            $hiddenanswers='';
            $thiskey=0;

            foreach ($labelcode as $ld)
            {
                $answer .= "\t<td class=\"answer_cell_1_00$ld answer-item {$answertypeclass}-item\">\n"
                . "<label for=\"answer$myfname-$ld\">\n"
                . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"$ld\" id=\"answer$myfname-$ld\" title=\""
                . HTMLEscape(strip_tags($labelans[$thiskey])).'"';
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $ld)
                {
                    $answer .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n";
                // --> END NEW FEATURE - SAVE
                $answer .= "\n\t</td>\n";
                $thiskey++;
            }
            if (count($labelans1)>0) // if second label set is used
            {
                $dualgroup++;
                $hiddenanswers='';
                $answer .= "\t<td class=\"dual_scale_separator information-item\">&nbsp;</td>\n";		// separator
                array_push($inputnames,$myfname1);
                $hiddenanswers .= "<input type=\"hidden\" name=\"java$myfname1\" id=\"java$myfname1\" value=\"";
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1])) {$hiddenanswers .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1];}
                $hiddenanswers .= "\" />\n";
                $thiskey=0;
                foreach ($labelcode1 as $ld) // second label set
                {
                    $answer .= "\t<td class=\"answer_cell_2_00$ld  answer-item radio-item\">\n";
                    if ($hiddenanswers!='')
                    {
                        $answer .=$hiddenanswers;
                        $hiddenanswers='';
                    }
                    $answer .= "<label for=\"answer$myfname1-$ld\">\n"
                    . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname1\" value=\"$ld\" id=\"answer$myfname1-$ld\" title=\""
                    . HTMLEscape(strip_tags($labelans1[$thiskey])).'"';
                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1] == $ld)
                    {
                        $answer .= CHECKED;
                    }
                    // --> START NEW FEATURE - SAVE
                    $answer .= " onclick=\"secondlabel_checkconditions(this.value, this.name, this.type)\" />\n</label>\n";
                    // --> END NEW FEATURE - SAVE

                    $answer .= "\t</td>\n";
                    $thiskey++;
                }
            }
            if (strpos($answertextsave,'|'))
            {
                $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                $answer .= "\t<td class=\"answertextright\">$answertext</td>\n";
                $hiddenanswers = '';
            }
            elseif ($right_exists)
            {
                $answer .= "\t<td class=\"answertextright\">&nbsp;</td>\n";
            }

            if ($ia[6] != "Y" && SHOW_NO_ANSWER == 1)
            {
                $answer .= "\t<td class=\"dual_scale_separator information-item\">&nbsp;</td>\n"; // separator
                $answer .= "\t<td class=\"dual_scale_no_answer answer-item radio-item noanswer-item\">\n"
                . "<label for='answer$myfname-'>\n"
                . "\t<input class='radio' type='radio' name='$myfname' value='' id='answer$myfname-' title='".$clang->gT("No answer")."'";
                if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == "")
                {
                    $answer .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer .= " onclick=\"noanswer_checkconditions(this.value, this.name, this.type)\" />\n"
                . "</label>\n"
                . "\t</td>\n";
                // --> END NEW FEATURE - SAVE
            }

            $answer .= "</tr>\n";
            // $inputnames[]=$myfname;
            //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
            $fn++;
        }
        $answer .= "\t</tbody>\n";
        $answer .= "</table>\n";
    }
    elseif ($useDropdownLayout === true && $lresult->count() > 0)
    {

        if (trim($aQuestionAttributes['answer_width'])!='')
        {
            $answerwidth=$aQuestionAttributes['answer_width'];
        } else {
            $answerwidth=20;
        }
        $separatorwidth=(100-$answerwidth)/10;
        $columnswidth=100-$answerwidth-($separatorwidth*2);

        $answer = "";

        // Get Answers

        //question atribute random_order set?
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] and scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY ".dbRandom();
        }

        //no question attributes -> order by sortorder
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid=$ia[0] and scale_id=0 AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' ORDER BY question_order";
        }
        $ansresult = dbExecuteAssoc($ansquery);    //Checked
        $anscount = $ansresult->count();

        if ($anscount==0)
        {
            $inputnames = array();
            $answer .="\n<p class=\"error\">".$clang->gT('Error: This question has no answers.')."</p>\n";
        }
        else
        {

            //already done $lresult = dbExecuteAssoc($lquery);
            foreach ($lresult->readAll() as $lrow)
            {
                $labels0[]=Array('code' => $lrow['code'],
                'title' => $lrow['answer']);
            }
            $lresult1 = dbExecuteAssoc($lquery1);   //Checked
            foreach ($lresult1->readAll() as $lrow1)
            {
                $labels1[]=Array('code' => $lrow1['code'],
                'title' => $lrow1['answer']);
            }


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
                list ($postanswSep, $interddSep) =explode('|',$aQuestionAttributes['dropdown_separators']);
                $postanswSep = $postanswSep;
                $interddSep = $interddSep;
            }
            else {
                $postanswSep = '';
                $interddSep = '';
            }

            $colspan_1 = '';
            $colspan_2 = '';
            $suffix_cell = '';
            $answer .= "\n<table class=\"question subquestion-list questions-list dropdown-list\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an dual array type question\">\n\n"
            . "\t<col class=\"answertext\" width=\"$answerwidth%\" />\n";
            if($ddprefix != '')
            {
                $answer .= "\t<col class=\"ddprefix\" />\n";
                $colspan_1 = ' colspan="2"';
            }
            $answer .= "\t<col class=\"dsheader\" />\n";
            if($ddsuffix != '')
            {
                $answer .= "\t<col class=\"ddsuffix\" />\n";
                if(!empty($colspan_1))
                {
                    $colspan_2 = ' colspan="3"';
                }
                $suffix_cell = "\t<td>&nbsp;</td>\n"; // suffix
            }
            $answer .= "\t<col class=\"ddarrayseparator\" width=\"$separatorwidth%\" />\n";
            if($ddprefix != '')
            {
                $answer .= "\t<col class=\"ddprefix\" />\n";
            }
            $answer .= "\t<col class=\"dsheader\" />\n";
            if($ddsuffix != '')
            {
                $answer .= "\t<col class=\"ddsuffix\" />\n";
            };
            // headers
            $answer .= "\n\t<thead>\n"
            . "<tr>\n"
            . "\t<td$colspan_1>&nbsp;</td>\n" // prefix
            . "\n"
            //			. "\t<td align='center' width='$columnswidth%'><span class='dsheader'>$leftheader</span></td>\n"
            . "\t<th>$leftheader</th>\n"
            . "\n"
            . "\t<td$colspan_2>&nbsp;</td>\n" // suffix // Inter DD separator // prefix
            //			. "\t<td align='center' width='$columnswidth%'><span class='dsheader'>$rightheader</span></td>\n"
            . "\t<th>$rightheader</th>\n"
            . $suffix_cell."</tr>\n"
            . "\t</thead>\n\n";
            $answer .= "\n<tbody>\n";
            $trbc = '';
            foreach ($ansresult->readAll() as $ansrow)
            {
                $rowname = $ia[1].$ansrow['title'];
                $dualgroup=0;
                $myfname = $ia[1].$ansrow['title']."#".$dualgroup;
                $dualgroup1=1;
                $myfname1 = $ia[1].$ansrow['title']."#".$dualgroup1;

                if ($ia[6]=='Y' && (is_array($notanswered)) && ((array_search($myfname, $notanswered) !== FALSE) || (array_search($myfname1, $notanswered) !== FALSE)) && (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '') || ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1] == '')) )
                {
                    $answertext="<span class='errormandatory'>".dTexts__run($ansrow['question'])."</span>";
                }
                else
                {
                    $answertext=dTexts__run($ansrow['question']);
                }

                $trbc = alternation($trbc , 'row');

                // Get array_filter stuff
                list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $aQuestionAttributes, $thissurvey, $ansrow, $rowname, $trbc, $myfname,"tr","$trbc subquestion-list questions-list dropdown-list");

                $answer .= $htmltbody2;

                $answer .= "\t<th class=\"answertext\">\n"
                . "<label for=\"answer$rowname\">\n"
                . $hiddenfield
                . "$answertext\n"
                . "</label>\n"
                . "\t</th>\n";

                // Label0

                // prefix
                if($ddprefix != '')
                {
                    $answer .= "\t<td class=\"ddprefix information-item\">$ddprefix</td>\n";
                }
                $answer .= "\t<td class=\"answer-item dropdown-item\">\n"
                . "<select name=\"$myfname\" id=\"answer$myfname\" onchange=\"array_dual_dd_checkconditions(this.value, this.name, this.type,$dualgroup,$checkconditionFunction);\">\n";

                if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] =='')
                {
                    $answer .= "\t<option value=\"\" ".SELECTED.'>'.$clang->gT('Please choose...')."</option>\n";
                }

                foreach ($labels0 as $lrow)
                {
                    $answer .= "\t<option value=\"".$lrow['code'].'" ';
                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $lrow['code'])
                    {
                        $answer .= SELECTED;
                    }
                    $answer .= '>'.$lrow['title']."</option>\n";
                }
                // If not mandatory and showanswer, show no ans
                if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
                {
                    $answer .= "\t<option class=\"noanswer-item\" value=\"\" ";
                    if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
                    {
                        $answer .= SELECTED;
                    }
                    $answer .= '>'.$clang->gT('No answer')."</option>\n";
                }
                $answer .= "</select>\n";

                // suffix
                if($ddsuffix != '')
                {
                    $answer .= "\t<td class=\"ddsuffix information-item\">$ddsuffix</td>\n";
                }
                $answer .= "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
                {
                    $answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                }
                $answer .= "\" />\n"
                . "\t</td>\n";

                $inputnames[]=$myfname;

                $answer .= "\t<td class=\"ddarrayseparator information-item\">$interddSep</td>\n"; //Separator

                // Label1

                // prefix
                if($ddprefix != '')
                {
                    $answer .= "\t<td class='ddprefix information-item'>$ddprefix</td>\n";
                }
                //				$answer .= "\t<td align='left' width='$columnswidth%'>\n"
                $answer .= "\t<td class=\"answer-item dropdown-item\">\n"
                . "<select name=\"$myfname1\" id=\"answer$myfname1\" onchange=\"array_dual_dd_checkconditions(this.value, this.name, this.type,$dualgroup1,$checkconditionFunction);\">\n";

                if (empty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
                {
                    $answer .= "\t<option value=\"\"".SELECTED.'>'.$clang->gT('Please choose...')."</option>\n";
                }

                foreach ($labels1 as $lrow1)
                {
                    $answer .= "\t<option value=\"".$lrow1['code'].'" ';
                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1] == $lrow1['code'])
                    {
                        $answer .= SELECTED;
                    }
                    $answer .= '>'.$lrow1['title']."</option>\n";
                }
                // If not mandatory and showanswer, show no ans
                if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
                {
                    $answer .= "\t<option class=\"noanswer-item\" value='' ";
                    if (empty($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
                    {
                        $answer .= SELECTED;
                    }
                    $answer .= ">".$clang->gT('No answer')."</option>\n";
                }
                $answer .= "</select>\n";

                // suffix
                if($ddsuffix != '')
                {
                    $answer .= "\t<td class=\"ddsuffix information-item\">$ddsuffix</td>\n";
                }
                $answer .= "<input type=\"hidden\" name=\"java$myfname1\" id=\"java$myfname1\" value=\"";
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1]))
                {
                    $answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1];
                }
                $answer .= "\" />\n"
                . "\t</td>\n";
                $inputnames[]=$myfname1;

                $answer .= "</tr>\n";
            }
        } // End there are answers
        $answer .= "\t</tbody>\n";
        $answer .= "</table>\n";
    }
    else
    {
        $answer = "<p class='error'>".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames="";
    }
    return array($answer, $inputnames);
}
