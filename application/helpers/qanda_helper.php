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
* One $ia array exists for every question in the survey. The $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['insertarray']
* string is an array of $ia arrays.
*
* $question->primaryKey => question id
* $question->sgqa => fieldname
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
* This function returns an array containing the "question/answer" html display
* and a list of the question/answer fieldnames associated. It is called from
* question.php, group.php, survey.php or preview.php
*
* @param mixed $ia
* @return mixed
*/
function retrieveAnswers(Question $question)
{
//    throw new \Exception();
    bP();
    //globalise required config variables

    //QUESTION NAME

    $qtitle = $question->title;
    $inputnames = [];
    $session = App()->surveySessionManager->current;
    $response = $session->response;

    //Create the question/answer html
    $html = "";

    $number = $session->getQuestionIndex($question->primaryKey) + 1;

    $em = $question->getExpressionManager($session->response);
    $parts = $em->asSplitStringOnExpressions($question->question);
    $text = '';
    foreach($parts as $part) {
        switch ($part[2]) {
            case 'STRING':
                $text .= $part[0];
                break;
            case 'EXPRESSION':
                if ($em->RDP_Evaluate(substr($part[0], 1, -1))) {
                    $value = $em->GetResult();
                } else {

                    $value = '';
                }
                $text .= TbHtml::tag('span', [
                    'data-expression' => $em->getJavascript(substr($part[0], 1, -1))
                ], $value);
        }
    }
    $question_text = [
        // Pass through EM.
        'code' => $question->title,
        'number' => $number,
        'help' => '',
        'mandatory' => '',
        'man_message' => '',
        'class' => '',
        'man_class' => '',
        'input_error_class' => '',
        'essentials' => ''
    ];

    if ($question instanceof \ls\interfaces\iRenderable) {
        $renderedQuestion = $question->render($response, $session);
    } else {
        switch ($question->type) {
            case 'X': //BOILERPLATE QUESTION
                $values = do_boilerplate($question);
                break;
            case '5': //5 POINT CHOICE radio-buttons
                $values = do_5pointchoice($question, $response);
                break;
            case 'D': //DATE
                $values = do_date($question);
                // if a drop box style date was answered incompletely (dropbox), print an error/help message
                if (($session->getStep() != $session->getMaxStep())
                    || $session->step == $session->prevStep
                ) {
                    if (isset($_SESSION['survey_' . Yii::app()->getConfig('surveyID')]['qattribute_answer' . $question->sgqa])) {
                        $question_text['help'] = '<span class="error">' . $_SESSION['survey_' . Yii::app()->getConfig('surveyID')]['qattribute_answer' . $question->sgqa] . '</span>';
                    }
                }
                break;
            case Question::TYPE_RADIO_LIST: //LIST drop-down/radio-button list
                $values = do_list_radio($question, $response);
                if ($question->hide_tip == 0) {
                    $qtitle .= "<br />\n<span class=\"questionhelp\">"
                        . gT('Choose one of the following answers') . '</span>';
                    $question_text['help'] = gT('Choose one of the following answers');
                }
                break;
            case '!': //List - dropdown
                $values = do_list_dropdown($question, $response);
                if ($question->hide_tip == 0) {
                    $qtitle .= "<br />\n<span class=\"questionhelp\">"
                        . gT('Choose one of the following answers') . '</span>';
                    $question_text['help'] = gT('Choose one of the following answers');
                }
                break;
            case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                $values = do_listwithcomment($question, $response);
                if (count($values[1]) > 1 && $question->hide_tip == 0) {
                    $qtitle .= "<br />\n<span class=\"questionhelp\">"
                        . gT('Choose one of the following answers') . '</span>';
                    $question_text['help'] = gT('Choose one of the following answers');
                }
                break;
            case 'R': //RANKING STYLE
                $values = do_ranking($question);
                break;
            case 'M': //Multiple choice checkbox
                $values = do_multiplechoice($question);
                if (count($values[1]) > 1 && $question->hide_tip == 0) {
                    $maxansw = trim($question->max_answers);
                    $minansw = trim($question->min_answers);
                    if (!($maxansw || $minansw)) {
                        $qtitle .= "<br />\n<span class=\"questionhelp\">"
                            . gT('Check any that apply') . '</span>';
                        $question_text['help'] = gT('Check any that apply');
                    }
                }
                break;

            case 'I': //Language Question
                $values = do_language($question, $session);
                if (count($values[1]) > 1) {
                    $qtitle .= "<br />\n<span class=\"questionhelp\">"
                        . gT('Choose your language') . '</span>';
                    $question_text['help'] = gT('Choose your language');
                }
                break;
            case 'P': //Multiple choice with comments checkbox + text
                $values = do_multiplechoice_withcomments($question);
                if (count($values[1]) > 1 && $question->hide_tip == 0) {
                    $maxansw = intval($question->min_answers);
                    $minansw = intval($question->max_answers);
                    if (!($maxansw || $minansw)) {
                        $qtitle .= "<br />\n<span class=\"questionhelp\">"
                            . gT('Check any that apply') . '</span>';
                        $question_text['help'] = gT('Check any that apply');
                    }
                }
                break;
            case '|': //File Upload
                $values = do_file_upload($question, $response);
                break;
            case 'Q': //MULTIPLE SHORT TEXT
                $values = do_multipleshorttext($question, $response);
                break;
            case 'K': //MULTIPLE NUMERICAL QUESTION
                $values = do_multiplenumeric($question, $response);
                break;
            case 'N': //NUMERICAL QUESTION TYPE
                $values = do_numerical($question, $response);
                break;
            case 'S': //SHORT FREE TEXT
                $values = do_shortfreetext($question, $response);
                break;
            case 'Y': //YES/NO radio-buttons
                $values = do_yesno($question, $response);
                break;
            case 'G': //GENDER drop-down list
                $values = do_gender($question, $response);
                break;
            case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                $values = do_array_5point($question, $response);
                break;
            case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                $values = do_array_10point($question, $response);
                break;
            case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                $values = do_array_yesnouncertain($question, $response);
                break;
            case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                $values = do_array_increasesamedecrease($question, $response);
                break;
            case 'F': //ARRAY (Flexible) - Row Format
                $values = do_array($question, $response);
                break;
            case 'H': //ARRAY (Flexible) - Column Format
                $values = do_arraycolumns($question, $response);
                break;
            case ':': //ARRAY (Multi Flexi) 1 to 10
                $values = do_array_multiflexi($question, $response);
                break;
            case ';': //ARRAY (Multi Flexi) Text
                $values = do_array_multitext($question, $response);  //It's like the "5th element" movie, come to life
                break;
            case '1': //Array (Flexible Labels) dual scale
                $values = do_array_dual($question, $response);
                break;
            case '*': // Equation
                $values = do_equation($question, $response);
                break;
            default:

                throw new \Exception("Don't know how to render this." . $question->type);
        } //End Switch
    }

    if (isset($values)) {
        $html = $values[0];
    }

    if ($question->bool_mandatory)
    {
        $qtitle = '<span class="asterisk">'.gT('*').'</span>' . $qtitle;
        $question_text['mandatory'] = gT('*');
    }

    if (!isset($question->hide_tip) || $question->hide_tip==0) {
        $_vshow = true; // whether should initially be visible - TODO should also depend upon 'hidetip'?
    }
    else {
        $_vshow = false;
    }
    eP();
    return $renderedQuestion;
}

// TMSW Validation -> EM
function file_validation_message(Question $question)
{
    global $filenotvalidated;


    $qtitle = "";
    if (isset($filenotvalidated) && is_array($filenotvalidated) && $ia[4] == "|")
    {
        global $filevalidationpopup, $popup;

        foreach ($filenotvalidated as $k => $v)
        {
            if ($question->sgqa == $k || strpos($k, "_") && $question->sgqa == substr(0, strpos($k, "_") - 1));
            $qtitle .= '<br /><span class="errormandatory">'.gT($filenotvalidated[$k]).'</span><br />';
        }
    }
    return $qtitle;
}

// TMSW Validation -> EM
function mandatory_popup(QuestionValidationResult $validationResult)
{
    //POPUP WARNING
    if ($validationResult->getQuestion()->type == Question::TYPE_LONG_TEXT
        || $validationResult->getQuestion()->type == Question::TYPE_SHORT_TEXT
        || $validationResult->getQuestion()->type == Question::TYPE_HUGE_TEXT)
    {
        $popup=gT("You cannot proceed until you enter some text for one or more questions.");
    } else {
        $popup=gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.");
    }
    return $popup;
}


// TMSW Validation -> EM
function file_validation_popup($ia, $filenotvalidated = null)
{

    if ($filenotvalidated === null) { unset($filenotvalidated); }
    if (isset($filenotvalidated) && is_array($filenotvalidated))
    {
        global $filevalidationpopup, $fpopup;

        if (!isset($filevalidationpopup))
        {
            $fpopup=gT("One or more file have either exceeded the filesize/are not in the right format or the minimum number of required files have not been uploaded. You cannot proceed until these have been completed");
            $filevalidationpopup = "Y";
        }
        return $fpopup;
    }
}

function return_timer_script(Question $question, $ia, $disable=null) {
    global $thissurvey;


    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts").'coookies.js');

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

    $time_limit=$question->time_limit;

    $disable_next=trim($question->time_limit_disable_next) != '' ? $question->time_limit_disable_next : 0;
    $disable_prev=trim($question->time_limit_disable_prev) != '' ? $question->time_limit_disable_prev : 0;
    $time_limit_action=trim($question->time_limit_action) != '' ? $question->time_limit_action : 1;
    $time_limit_message_delay=trim($question->time_limit_message_delay) != '' ? $question->time_limit_message_delay*1000 : 1000;
    $time_limit_message=trim($question->time_limit_message[$session->language]) != '' ? htmlspecialchars($question->time_limit_message[$session->language], ENT_QUOTES) : gT("Your time to answer this question has expired");
    $time_limit_warning=trim($question->time_limit_warning) != '' ? $question->time_limit_warning : 0;
    $time_limit_warning_2=trim($question->time_limit_warning_2) != '' ? $question->time_limit_warning_2 : 0;
    $time_limit_countdown_message=trim($question->time_limit_countdown_message[$session->language]) != '' ? htmlspecialchars($question->time_limit_countdown_message[$session->language], ENT_QUOTES) : gT("Time remaining");
    $time_limit_warning_message=trim($question->time_limit_warning_message[$session->language]) != '' ? htmlspecialchars($question->time_limit_warning_message[$session->language], ENT_QUOTES) : gT("Your time to answer this question has nearly expired. You have {TIME} remaining.");
    $time_limit_warning_message=str_replace("{TIME}", "<div style='display: inline' id='LS_question".$question->primaryKey."_Warning'> </div>", $time_limit_warning_message);
    $time_limit_warning_display_time=trim($question->time_limit_warning_display_time) != '' ? $question->time_limit_warning_display_time+1 : 0;
    $time_limit_warning_2_message=trim($question->time_limit_warning_2_message[$session->language]) != '' ? htmlspecialchars($question->time_limit_warning_2_message[$session->language], ENT_QUOTES) : gT("Your time to answer this question has nearly expired. You have {TIME} remaining.");
    $time_limit_warning_2_message=str_replace("{TIME}", "<div style='display: inline' id='LS_question".$question->primaryKey."_Warning_2'> </div>", $time_limit_warning_2_message);
    $time_limit_warning_2_display_time=trim($question->time_limit_warning_2_display_time) != '' ? $question->time_limit_warning_2_display_time+1 : 0;
    $time_limit_message_style=trim($question->time_limit_message_style) != '' ? $question->time_limit_message_style : "position: absolute;
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
    $time_limit_warning_style=trim($question->time_limit_warning_style) != '' ? $question->time_limit_warning_style : "position: absolute;
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
    $time_limit_warning_2_style=trim($question->time_limit_warning_2_style) != '' ? $question->time_limit_warning_2_style : "position: absolute;
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
    $time_limit_timer_style=trim($question->time_limit_timer_style) != '' ? $question->time_limit_timer_style : "position: relative;
    width: 150px;
    margin-left: auto;
    margin-right: auto;
    border: 1px solid #111;
    text-align: center;
    background-color: #EEE;
    margin-bottom: 5px;
    font-size: 8pt;";
    $timersessionname="timer_question_".$question->primaryKey;
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
            $session = App()->surveySessionManager->current;
            foreach($session->getFieldArray() as $ib)
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
            if (whours > 0) dhours = whours + ' ".gT('hours').", ';
            if (wmins > 0) dmins = wmins + ' ".gT('mins').", ';
            if (wsecs > 0) dsecs = wsecs + ' ".gT('seconds')."';
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
            if (w2hours > 0) d2hours = w2hours + ' ".gT('hours').", ';
            if (w2mins > 0) d2mins = w2mins + ' ".gT('mins').", ';
            if (w2secs > 0) d2secs = w2secs + ' ".gT('seconds')."';
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
        if (hours > 0) d2hours = hours+' ".gT('hours').": ';
        if (mins > 0) d2mins = mins+' ".gT('mins').": ';
        if (secs > 0) d2secs = secs+' ".gT('seconds')."';
        if (secs < 1) d2secs = '0 ".gT('seconds')."';
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
    $output .= "<div id='question".$question->primaryKey."_timer' style='".$time_limit_message_style."'>".$time_limit_message."</div>\n\n";

    $output .= "<div id='LS_question".$question->primaryKey."_warning' style='".$time_limit_warning_style."'>".$time_limit_warning_message."</div>\n\n";
    $output .= "<div id='LS_question".$question->primaryKey."_warning_2' style='".$time_limit_warning_2_style."'>".$time_limit_warning_2_message."</div>\n\n";
    $output .= "<div id='LS_question".$question->primaryKey."_Timer' style='".$time_limit_timer_style."'></div>\n\n";
    //Call the countdown script
    $output .= "<script type='text/javascript'>
    $(document).ready(function() {
    countdown(".$question->primaryKey.", ".$time_limit.", ".$time_limit_action.", ".$time_limit_warning.", ".$time_limit_warning_2.", ".$time_limit_warning_display_time.", ".$time_limit_warning_2_display_time.", '".$disable."');
    });
    </script>\n\n";
    return $output;
}

function return_array_filter_strings(
    Question $question,
    $thissurvey,
    $rowname,
    $trbc = '',
    $valuename,
    $method = "tbody",
    $class = null
) {
    $htmltbody2 = "\n\n\t<$method id='javatbd$rowname'";
    $htmltbody2 .= ($class !== null) ? " class='$class'": "";
    if (false && !$question->isRelevant($response))
    {
        // If using exclude_all_others, then need to know whether irrelevant rows should be hidden or disabled
        if (isset($question->exclude_all_others))
        {
            $disableit=false;
            foreach(explode(';',trim($question->exclude_all_others)) as $eo)
            {
                $eorow = $question->sgqa . $eo;
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
                if (!isset($question->array_filter_style) || $question->array_filter_style == '0')
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
            if (!isset($question->array_filter_style) || $question->array_filter_style == '0')
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

// ==================================================================
// QUESTION METHODS =================================================

function do_boilerplate(Question $question)
{
    
    $answer='';

    if (trim($question->time_limit)!='')
    {
        $answer .= return_timer_script($question, $ia);
    }

    $answer .= '<input type="hidden" name="'.$question->sgqa.'" id="answer'.$question->sgqa.'" value="" />';
    $inputnames[]=$question->sgqa;

    return array($answer, $inputnames);
}

function do_equation(Question $question)
{
    
    $sEquation = (trim($question->equation)) ? $question->equation : $question->question;
    $answer='<input type="hidden" name="'.$question->sgqa.'" id="java'.$question->sgqa.'" value="';
    $answer .= htmlspecialchars(App()->surveySessionManager->current->response->{$question->sgqa},ENT_QUOTES);
    $answer .= '">';
    $answer .="<div class='em_equation hidden' style='display:none;visibility:hidden'>{$sEquation}</div>";
    $inputnames[]=$question->sgqa;

    return array($answer, $inputnames);
}


// ---------------------------------------------------------------
function do_language(Question $question, SurveySession $session)
{
    $checkconditionFunction = "checkconditions";

    $answerlangs = $question->getAnswers();
    // Get actual answer
    $sLang = $session->language;
    if(!in_array($sLang,$answerlangs))
    {
        $sLang = $session->survey->language;
    }
    $html = "\n\t<p class=\"question answer-item dropdown-item language-item\">\n"
    ."<label for='answer{$question->sgqa}' class='hide label'>".gT('Choose your language')."</label>"
    ."<select name=\"$question->sgqa\" id=\"answer$question->sgqa\" onchange=\"$checkconditionFunction(this.value, this.name, this.type);\" class=\"languagesurvey\">\n";
    foreach ($answerlangs as $subQuestion)
    {
        $html .= "\t<option value=\"{$subQuestion}\"";
        if ($sLang == $subQuestion)
        {
            $html .= SELECTED;
        }
        $aLanguage=getLanguageNameFromCode($subQuestion, true);
        $html .= '>'.$aLanguage[1]."</option>\n";
    }
    $html .= "</select>\n";
    $html .= "<input type=\"hidden\" name=\"java{$question->sgqa}\" id=\"java{$question->sgqa}\" value=\"{$sLang}\" />\n";
    $inputnames[]=$question->sgqa;

    $html .= "<script type='text/javascript'>\n"
    . "/*<![CDATA[*/\n"
    ."$('#answer{$question->sgqa}').change(function(){ "
    ."$('<input type=\"hidden\">').attr('name','lang').val($(this).val()).appendTo($('form#limesurvey'));"
    ." })\n"
    ." /*]]>*/\n"
    ."</script>\n";
    return [$html, $inputnames];
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_list_dropdown(Question $question, Response $response)
{
    $session = App()->surveySessionManager->current;
    $checkconditionFunction = "checkconditions";

    if (trim($question->other_replace_text[$session->language])!='')
    {
        $othertext=$question->other_replace_text[$session->language];
    }
    else
    {
        $othertext=gT('Other:');
    }

    if (trim($question->category_separator)!='')
    {
        $optCategorySeparator = $question->category_separator;
    }

    $answer='';

    //Time Limit Code
    if (trim($question->time_limit)!='')
    {
        $answer .= return_timer_script($question, $ia);
    }
    //End Time Limit Code

    
    //question attribute random order set?
    if ($question->random_order==1)
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE question_id={$question->primaryKey} and scale_id=0 ORDER BY ".dbRandom();
    }
    //question attribute alphasort set?
    elseif ($question->alphasort==1)
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE question_id={$question->primaryKey} and scale_id=0 ORDER BY answer";
    }
    //no question attributes -> order by sortorder
    else
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE question_id={$question->primaryKey} and scale_id=0 ORDER BY sortorder, answer";
    }

    if (false == $ansresult = Yii::app()->db->createCommand($ansquery)->query()) {
        throw new \CHttpException(500, 'Couldn\'t get answers<br />'.$ansquery.'<br />');
    }
    $ansresult= $ansresult->readAll();
    $dropdownSize = '';
    if (isset($question->dropdown_size) && $question->dropdown_size > 0)
    {
        $_height = sanitize_int($question->dropdown_size) ;
        $_maxHeight = count($ansresult);
        if ((!empty($response->{$question->sgqa})) && !$question->bool_mandatory && !$question->bool_mandatory && $question->survey->bool_shownoanswer) {
            ++$_maxHeight;  // for No Answer
        }
        if ($question->bool_other) {
            ++$_maxHeight;  // for Other
        }
        if (!$response->{$question->sgqa}) {
            ++$_maxHeight;  // for 'Please choose:'
        }

        if ($_height > $_maxHeight) {
            $_height = $_maxHeight;
        }
        $dropdownSize = ' size="'.$_height.'"';
    }

    $prefixStyle = 0;
    if (isset($question->dropdown_prefix))
    {
        $prefixStyle = sanitize_int($question->dropdown_prefix) ;
    }
    $_rowNum=0;
    $_prefix='';

    $em = $question->getExpressionManager($session->response);

    if (!isset($optCategorySeparator))
    {
        foreach ($ansresult as $subQuestion)
        {
            if ($prefixStyle == 1) {
                $_prefix = ++$_rowNum . ') ';
            }
            // Get the parts for the answer, we construct one EM expression from the whole.
            $parts = $em->asSplitStringOnExpressions(flattenText($_prefix.$subQuestion['answer']));
            $expressionParts = [];
            $text = '';
            foreach($parts as $part) {
                switch ($part[2]) {
                    case 'STRING':
                        $expressionParts[] = "'{$part[0]}'";
                        $text .= $part[0];
                        break;
                    case 'EXPRESSION':
                        if ($em->RDP_Evaluate(substr($part[0], 1, -1))) {
                            $value = $em->GetResult();
                        } else {
                            $value = '';
                        }
                        $text .= $value;
                        $expressionParts[] = substr($part[0], 1, -1);
                        $text .= $value;
                }
            }
            $answer .= TbHtml::tag('option', [
                'value' => $subQuestion['code'],
                'selected' => $response->{$question->sgqa} == $subQuestion['code'],
                'data-expression' => $em->getJavascript('join(' . implode(',', $expressionParts) . ')')
            ], $text);
        }
    }
    else
    {
        $defaultopts = [];
        $optgroups = [];
        foreach ($ansresult as $subQuestion)
        {
            // Let's sort answers in an array indexed by subcategories
            @list ($categorytext, $answertext) = explode($optCategorySeparator,$subQuestion['answer']);
            // The blank category is left at the end outside optgroups
            if ($categorytext == '')
            {
                $defaultopts[] = [
                    'code' => $subQuestion['code'],
                    'answer' => $answertext
                ];
            }
            else
            {
                $optgroups[$categorytext][] = [
                    'code' => $subQuestion['code'],
                    'answer' => $answertext
                ];
            }
        }

        foreach ($optgroups as $categoryname => $optionlistarray)
        {
            $answer .= '<optgroup class="dropdowncategory" label="'.flattenText($categoryname).'">';

            foreach ($optionlistarray as $optionarray)
            {
                if ($response->{$question->sgqa} == $optionarray['code'])
                {
                    $opt_select = SELECTED;
                }
                else
                {
                    $opt_select = '';
                }

                $answer .= '<option value="'.$optionarray['code'].'"'.$opt_select.'>'.flattenText($optionarray['answer']).'</option>';
            }

            $answer .= '</optgroup>';
        }
        $opt_select='';
        foreach ($defaultopts as $optionarray)
        {
            if ($response->{$question->sgqa} == $optionarray['code'])
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

    if (!isset($response->{$question->sgqa}))
    {
        $answer = '                    <option value=""'.SELECTED.'>'.gT('Please choose...').'</option>'."\n".$answer;
    }

    if ($question->bool_other)
    {
        if ($response->{$question->sgqa} == '-oth-')
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

    if (!empty($response->{$question->sgqa}) && !$question->bool_mandatory && $question->survey->bool_shownoanswer)
    {
        if ($prefixStyle == 1) {
            $_prefix = ++$_rowNum . ') ';
        }
        $answer .= '<option class="noanswer-item" value="">'.$_prefix.gT('No answer')."</option>\n";
    }
    $answer .= '                </select>
    <input type="hidden" name="java'.$question->sgqa.'" id="java'.$question->sgqa.'" value="'. $response->{$question->sgqa} .'" />';

    if ($question->bool_other)
    {
        $sselect_show_hide = ' showhideother(this.name, this.value);';
    }
    else
    {
        $sselect_show_hide = '';
    }
    $sselect = '
    <p class="question answer-item dropdown-item"><label for="answer'.$question->sgqa.'" class="hide label">'.gT('Please choose').'</label>
    <select name="'.$question->sgqa.'" id="answer'.$question->sgqa.'"'.$dropdownSize.' onchange="'.$checkconditionFunction.'(this.value, this.name, this.type);'.$sselect_show_hide.'">
    ';
    $answer = $sselect.$answer;

    if ($question->bool_other)
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
        $answer .= '                <input type="text" id="othertext'.$question->sgqa.'" name="'.$question->sgqa.'other" style="display:';

        $inputnames[]=$question->sgqa.'other';

        if ($response->{$question->sgqa} != '-oth-')
        {
            $answer .= 'none';
        }

        $answer .= '"';

        // --> START NEW FEATURE - SAVE
        $answer .= "  alt='".gT('Other answer')."' onchange='$checkconditionFunction(this.value, this.name, this.type);'";
        $thisfieldname="$question->sgqaother";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname])) { $answer .= " value='".htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname],ENT_QUOTES)."' ";}
        $answer .= ' />';
        $answer .= "</p>";
        // --> END NEW FEATURE - SAVE
        $inputnames[]=$question->sgqa."other";
    }
    else
    {
        $answer .= "</p>";
    }

    $inputnames[]=$question->sgqa;
    return array($answer, $inputnames);
}

function do_list_radio(Question $question, Response $response)
{
    global $dropdownthreshold;

    $session = App()->surveySessionManager->current;

    if ($session->survey->bool_nokeyboard)
    {
        includeKeypad();
        $kpclass = "text-keypad";
    }
    else
    {
        $kpclass = "";
    }

    $checkconditionFunction = "checkconditions";


    //question attribute random order set?
    if ($question->random_order==1) {
        $ansquery = "SELECT * FROM {{answers}} WHERE question_id={$question->qid} and scale_id=0 ORDER BY ".dbRandom();
    }

    //question attribute alphasort set?
    elseif ($question->alphasort==1)
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE question_id={$question->qid} and scale_id=0 ORDER BY answer";
    }

    //no question attributes -> order by sortorder
    else
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE question_id={$question->qid} and scale_id=0 ORDER BY sortorder, answer";
    }

    $ansresult = dbExecuteAssoc($ansquery)->readAll();  //Checked
    $anscount = count($ansresult);

    if (trim($question->display_columns)!='') {
        $dcols = $question->display_columns;
    }
    else
    {
        $dcols= 1;
    }

    if (trim($question->other_replace_text[$session->language])!='')
    {
        $othertext=$question->other_replace_text[$session->language];
    }
    else
    {
        $othertext=gT('Other:');
    }

    if ($question->bool_other) {$anscount++;} //Count up for the Other answer
    if (!$question->bool_mandatory && $question->survey->bool_shownoanswer) {$anscount++;} //Count up if "No answer" is showing

    $wrapper = setupColumns($dcols , $anscount,"answers-list radio-list","answer-item radio-item");
    $answer = $wrapper['whole-start'];

    //Time Limit Code
    if (trim($question->time_limit)!='')
    {
        $answer .= return_timer_script($question, $ia);
    }
    //End Time Limit Code

    // Get array_filter stuff

    $rowcounter = 0;
    $colcounter = 1;
    $trbc='';

    foreach ($ansresult as $key=>$subQuestion)
    {
        $myfname = $question->sgqa . $subQuestion['code'];
        $check_ans = '';
        if (App()->surveySessionManager->current->response->{$question->sgqa} == $subQuestion['code'])
        {
            $check_ans = CHECKED;
        }

        list($htmltbody2, $hiddenfield) = return_array_filter_strings($question, null, $myfname, $trbc, $myfname, "li",
            "answer-item radio-item");
        if(substr($wrapper['item-start'],0,4) == "\t<li")
        {
            $startitem = "\t$htmltbody2\n";
        } else {
            $startitem = $wrapper['item-start'];
        }

        $answer .= $startitem;
        $answer .= "\t$hiddenfield\n";
        $answer .='        <input class="radio" type="radio" value="'.$subQuestion['code'].'" name="'. $question->sgqa .'" id="answer'.$question->sgqa.$subQuestion['code'].'"'.$check_ans.' onclick="$(this).closest(\'ul\').find(\'input[type=text]\').val(\'\').trigger(\'change\');'.$checkconditionFunction.'(this.value, this.name, this.type)" />
        <label for="answer'.$question->sgqa.$subQuestion['code'].'" class="answertext">'.$subQuestion['answer'].'</label>
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

    if ($question->bool_other) {

        $sSeparator = getRadixPointData($session->survey->getLocalizedNumberFormat());
        $sSeparator = $sSeparator['separator'];

        if ($question->other_numbers_only==1)
        {
            $oth_checkconditionFunction = 'fixnum_checkconditions';
        }
        else
        {
            $oth_checkconditionFunction = 'checkconditions';
        }

        $check_ans = App()->surveySessionManager->current->response->{$question->sgqa} == '-oth-' ? CHECKED : '';


        $thisfieldname=$question->sgqa.'other';
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname]))
        {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$thisfieldname];
            if ($question->other_numbers_only==1)
            {
                $dispVal = str_replace('.',$sSeparator,$dispVal);
            }
            $answer_other = ' value="'.htmlspecialchars($dispVal,ENT_QUOTES).'"';
        }
        else
        {
            $answer_other = ' value=""';
        }

        list($htmltbody2, $hiddenfield)=return_array_filter_strings($question, ['sid' => $session->surveyId],
            $thisfieldname, $trbc, $myfname, "li", "answer-item radio-item other-item other");

        if(substr($wrapper['item-start-other'],0,4) == "\t<li")
        {
            $startitem = "\t$htmltbody2\n";
        } else {
            $startitem = $wrapper['item-start-other'];
        }
        $answer .= $startitem;
        $answer .= "\t$hiddenfield\n";
        $answer .= '        <input class="radio" type="radio" value="-oth-" name="'.$question->sgqa.'" id="SOTH'.$question->sgqa.'"'.$check_ans.' onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)" />
        <label for="SOTH'.$question->sgqa.'" class="answertext">'.$othertext.'</label>
        <label for="answer'.$question->sgqa.'othertext">
        <input type="text" class="text '.$kpclass.'" id="answer'.$question->sgqa.'othertext" name="'.$question->sgqa.'other" title="'.gT('Other').'"'.$answer_other.' onkeyup="if($.trim($(this).val())!=\'\'){ $(\'#SOTH'.$question->sgqa.'\').click(); }; '.$oth_checkconditionFunction.'(this.value, this.name, this.type);" />
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

    if ($question->bool_mandatory && $question->survey->bool_shownoanswer)
    {
        $answer .= $wrapper['item-start-noanswer'].'        <input class="radio" type="radio" name="'.$question->sgqa.'" id="answer'.$question->sgqa.'NANS" value=""'.$check_ans.' onclick="\'$(this).closest(\'ul\').find(\'input[type=text]\').val(\'\').trigger(\'change\');'.$checkconditionFunction.'(this.value, this.name, this.type)" />
        <label for="answer'.$question->sgqa.'NANS" class="answertext">'.gT('No answer').'</label>
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
    <input type="hidden" name="java'.$question->sgqa.'" id="java'.$question->sgqa."\" value=\"". $response->{$question->sgqa}."\" />\n";

    $inputnames[]=$question->sgqa;
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_listwithcomment(Question $question)
{

    $session = App()->surveySessionManager->current;
    $dropdownthreshold = Yii::app()->getConfig("dropdownthreshold");

    if ($session->survey->bool_nokeyboard)
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

    
    if (!isset($maxoptionsize)) {$maxoptionsize=35;}

    //question attribute random order set?
    if ($question->random_order==1) {
        $ansquery = "SELECT * FROM {{answers}} WHERE question_id = {$question->primaryKey} and scale_id=0 ORDER BY ".dbRandom();
    }
    //question attribute alphasort set?
    elseif ($question->alphasort==1)
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE question_id = $question->primaryKey and scale_id=0 ORDER BY answer";
    }
    //no question attributes -> order by sortorder
    else
    {
        $ansquery = "SELECT * FROM {{answers}} WHERE question_id = $question->primaryKey and scale_id=0 ORDER BY sortorder, answer";
    }

    $ansresult=Yii::app()->db->createCommand($ansquery)->query()->readAll();
    $anscount = count($ansresult);


    $hint_comment = gT('Please enter your comment here');
    if ($question->use_dropdown!=1 && $anscount <= $dropdownthreshold)
    {
        $answer .= '<div class="list">
        <ul class="answers-list radio-list">
        ';

        foreach ($ansresult as $subQuestion)
        {
            $check_ans = '';
            if ($session->response[$question->sgqa] == $subQuestion['code'])
            {
                $check_ans = CHECKED;
            }
            $answer .= '        <li class="answer-item radio-item">
            <input type="radio" name="'.$question->sgqa.'" id="answer'.$question->sgqa.$subQuestion['code'].'" value="'.$subQuestion['code'].'" class="radio" '.$check_ans.' onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)" />
            <label for="answer'.$question->sgqa.$subQuestion['code'].'" class="answertext">'.$subQuestion['answer'].'</label>
            </li>
            ';
        }

        if (!$question->bool_mandatory && $question->survey->bool_shownoanswer)
        {
            if (empty($session->response[$question->sgqa]))
            {
                $check_ans = CHECKED;
            }
            else
            {
                $check_ans = '';
            }
            $answer .= '        <li class="answer-item radio-item noanswer-item">
            <input class="radio" type="radio" name="'.$question->sgqa.'" id="answer'.$question->sgqa.'" value=" " onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)"'.$check_ans.' />
            <label for="answer'.$question->sgqa.'" class="answertext">'.gT('No answer').'</label>
            </li>
            ';
        }

        $fname2 = $question->sgqa.'comment';
        if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
        // --> START NEW FEATURE - SAVE
        //    --> START ORIGINAL
        //        $answer .= "\t<td valign='top'>\n"
        //                 . "<textarea class='textarea' name='$question->sgqacomment' id='answer$question->sgqacomment' rows='$tarows' cols='30'>";
        //    --> END ORIGINAL
        $answer .= '    </ul>
        </div>

        <p class="comment answer-item text-item">
        <label for="answer'.$question->sgqa.'comment">'.$hint_comment.':</label>

        <textarea class="textarea '.$kpclass.'" name="'.$question->sgqa.'comment" id="answer'.$question->sgqa.'comment" rows="'.floor($tarows).'" cols="30" >';
        // --> END NEW FEATURE - SAVE
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2])
        {
            $answer .= str_replace("\\", "", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]);
        }
        $answer .= '</textarea>
        </p>

        <input class="radio" type="hidden" name="java'.$question->sgqa.'" id="java'.$question->sgqa.'" value="'.$session->response[$question->sgqa].'" />
        ';
        $inputnames[]=$question->sgqa;
        $inputnames[]=$question->sgqa.'comment';
    }
    else //Dropdown list
    {
        $answer .= '<p class="select answer-item dropdown-item">
        <select class="select" name="'.$question->sgqa.'" id="answer'.$question->sgqa.'" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type)" >
        ';
        if (is_null($response->{$question->sgqa}))
        {
            $answer .= '<option class="noanswer-item" value=""'.SELECTED.'>'.gT('Please choose...').'</option>'."\n";
        }
        foreach ($ansresult as $subQuestion)
        {
            $check_ans = '';
            if ($response->{$question->sgqa} == $subQuestion['code'])
            {
                $check_ans = SELECTED;
            }
            $answer .= '        <option value="'.$subQuestion['code'].'"'.$check_ans.'>'.$subQuestion['answer']."</option>\n";

            if (strlen($subQuestion['answer']) > $maxoptionsize)
            {
                $maxoptionsize = strlen($subQuestion['answer']);
            }
        }
        if (!$question->bool_mandatory && $question->survey->bool_shownoanswer && !is_null($response->{$question->sgqa}))
        {
            $check_ans="";
            if (trim($response->{$question->sgqa}) == '')
            {
                $check_ans = SELECTED;
            }
            $answer .= '<option class="noanswer-item" value=""'.$check_ans.'>'.gT('No answer')."</option>\n";
        }
        $answer .= '    </select>
        </p>
        ';
        $fname2 = $question->sgqa.'comment';
        if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
        if ($tarows > 15) {$tarows=15;}
        $maxoptionsize=$maxoptionsize*0.72;
        if ($maxoptionsize < 33) {$maxoptionsize=33;}
        if ($maxoptionsize > 70) {$maxoptionsize=70;}
        $answer .= '<p class="comment answer-item text-item">
        <label for="answer'.$question->sgqa.'comment">'.$hint_comment.':</label>
        <textarea class="textarea '.$kpclass.'" name="'.$question->sgqa.'comment" id="answer'.$question->sgqa.'comment" rows="'.$tarows.'" cols="'.$maxoptionsize.'" >';
        // --> END NEW FEATURE - SAVE
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2])
        {
            $answer .= str_replace("\\", "", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]);
        }
        $answer .= '</textarea>
        <input class="radio" type="hidden" name="java'.$question->sgqa.'" id="java'.$question->sgqa.'" value="'.$response->{$question->sgqa}.'" /></p>';
        $inputnames[]=$question->sgqa;
        $inputnames[]=$question->sgqa.'comment';
    }
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_ranking(Question $question)
{
    $imageurl = Yii::app()->getConfig("imageurl");

    $checkconditionFunction = "checkconditions";
    
    $answers = $question->random_order ? array_shuffle($question->answers) : $question->answers;

    $anscount= count($answers);

    $max_answers = is_numeric($question->max_answers) ? intval($question->max_answers) : $anscount;
    $min_answers = is_numeric($question->min_answers) ? intval($question->min_answers) : $anscount;

    // Get the max number of line needed
    $iMaxLine = $max_answers < $anscount ? $max_answers : $anscount;


    $result = '';
    // First start by a ranking without javascript : just a list of select box
    // construction select box
    $result .= '<div class="ranking-answers">
    <ul class="answers-list select-list">';
    for ($i=1; $i<=$iMaxLine; $i++)
    {
        $myfname=$question->sgqa.$i;
        $result .= "\n<li class=\"select-item\">";
        $result .="<label for=\"answer{$myfname}\">";
        if($i==1){
            $result .=gT('First choice');
        }else{
            $result .=sprintf(gT('Choice of rank %s'),$i);
        }
        $result .= "</label>";
        $result .= "<select name=\"{$myfname}\" id=\"answer{$myfname}\">\n";
        if (empty(App()->surveySessionManager->current->response->{$question->sgqa})) {
            $result .= "\t<option value=\"\"".SELECTED.">".gT('Please choose...')."</option>\n";
        }
        foreach ($answers as $answer)
        {
            $thisvalue="";
            $result .="\t<option value=\"{$answer->code}\"";
                if (isset($_SESSION['survey_'.$question->sid][$myfname]) && $_SESSION['survey_'.$question->sid][$myfname] == $answer->code)
                {
                    $result .= SELECTED;
                    $thisvalue=$_SESSION['survey_'.$question->sid][$myfname];
                }
            $result .=">".flattenText($answer->answer)."</option>\n";
        }
        $result .="</select>";
        // Hidden form: maybe can be replaced with ranking.js
        $result .="<input type=\"hidden\" id=\"java{$myfname}\" disabled=\"disabled\" value=\"{$thisvalue}\"/>";
        $result .="</li>";
        $inputnames[]=$myfname;
    }
    $result .="</ul>"
        . "<div style='display:none' id='ranking-{$question->primaryKey}-maxans'>{".$max_answers."}</div>"
        . "<div style='display:none' id='ranking-{$question->primaryKey}-minans'>{".$min_answers."}</div>"
        . "<div style='display:none' id='ranking-{$question->primaryKey}-name'>".$question->sgqa."</div>"
        . "</div>";
    // The list with HTML answers
    $result .="<div style=\"display:none\">";
    foreach ($answers as $answer)
    {
        $result.="<div id=\"htmlblock-{$question->primaryKey}-{$answer->code}\">{$answer->answer}</div>";
    }
    $result .="</div>";
    $cs = App()->getClientScript();
    $cs->registerPackage('jquery-actual'); // Needed to with jq1.9 ?
    $cs->registerScriptFile(Yii::app()->getConfig('generalscripts')."ranking.js");
    $cs->registerCssFile(Yii::app()->getConfig('publicstyleurl') . "ranking.css");

    if(!empty($question->choice_title))
    {
        $choice_title = htmlspecialchars($question->choice_title, ENT_QUOTES);
    }
    else
    {
        $choice_title = gT("Your Choices",'js');
    }
    if(!empty($question->rank_title))
    {
        $rank_title = htmlspecialchars($question->rank_title, ENT_QUOTES);
    }
    else
    {
        $rank_title=gT("Your Ranking",'js');
    }
    // hide_tip is managed by css with EM
    $rank_help = gT("Double-click or drag-and-drop items in the left list to move them to the right - your highest ranking item should be on the top right, moving through to your lowest ranking item.",'js');
    
    $result .= "<script type='text/javascript'>\n"
    . "  <!--\n"
    . "var aRankingTranslations = {
             choicetitle: '{$choice_title}',
             ranktitle: '{$rank_title}',
             rankhelp: '{$rank_help}'
            };\n"
    ." doDragDropRank({$question->primaryKey},{$question->showpopups},{$question->samechoiceheight},{$question->samelistheight});\n"
    ." -->\n"
    ."</script>\n";
    return array($result, $inputnames);
}


// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_multiplechoice(Question $question)
{
    global $thissurvey;


    if ($question->survey->bool_nokeyboard)
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
    $inputnames=array();


    $checkconditionFunction = "checkconditions";

    

    if (trim($question->other_replace_text)!='')
    {
        $othertext=$question->other_replace_text;
    }
    else
    {
        $othertext=gT('Other:');
    }

    if (trim($question->display_columns)!='')
    {
        $dcols = $question->display_columns;
    }
    else
    {
        $dcols = 1;
    }

    if ($question->other_numbers_only==1)
    {
        $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator= $sSeparator['separator'];
        $oth_checkconditionFunction = "fixnum_checkconditions";
    }
    else
    {
        $oth_checkconditionFunction = "checkconditions";
    }

    if (trim($question->exclude_all_others)!='' && $question->random_order==1)
    {
        //if  exclude_all_others is set then the related answer should keep its position at all times
        //thats why we have to re-position it if it has been randomized
        $position=0;
        foreach ($question->subQuestions as $answer)
        {
            if ((trim($question->exclude_all_others) != '')  &&    ($answer['title']==trim($question->exclude_all_others)))
            {
                if ($position==$answer['question_order']-1) break; //already in the right position
                $tmp  = array_splice($ansresult, $position, 1);
                array_splice($ansresult, $answer['question_order']-1, 0, $tmp);
                break;
            }
            $position++;
        }
    }

    $anscount = count($question->subQuestions);
    if ($question->bool_other)
    {
        $anscount++; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
    }

    $wrapper = setupColumns($dcols, $anscount,"subquestions-list questions-list checkbox-list","question-item answer-item checkbox-item");

    $answer = '<input type="hidden" name="MULTI'.$question->sgqa.'" value="'.$anscount."\" />\n\n".$wrapper['whole-start'];

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
    foreach ($question->subQuestions as $subQuestion)
    {
        $myfname = $question->sgqa.$subQuestion->title;
        $extra_class="";

        $trbc='';
        /* Check for array_filter */
        list($htmltbody2, $hiddenfield)=return_array_filter_strings($question, $subQuestion, $myfname, $trbc, $myfname,
            "li", "question-item answer-item checkbox-item" . $extra_class);

        if(substr($wrapper['item-start'],0,4) == "\t<li")
        {
            $startitem = "\t$htmltbody2\n";
        } else {
            $startitem = $wrapper['item-start'];
        }

        /* Print out the checkbox */
        $answer .= $startitem;
        $answer .= "\t$hiddenfield\n";
        $answer .= '        <input class="checkbox" type="checkbox" name="'.$question->sgqa.$subQuestion->title.'" id="answer'.$question->sgqa.$subQuestion->title.'" value="Y"';

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
        .  "<label for=\"answer$question->sgqa{$subQuestion->title}\" class=\"answertext\">"
        .  $subQuestion->question
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

    if ($question->bool_other)
    {
        $myfname = $question->sgqa.'other';
        list($htmltbody2, $hiddenfield)=return_array_filter_strings($question, array("code" => "other"), $myfname,
            $trbc, $myfname, "li", "question-item answer-item checkbox-item other-item");

        if(substr($wrapper['item-start-other'],0,4) == "\t<li")
        {
            $startitem = "\t$htmltbody2\n";
        } else {
            $startitem = $wrapper['item-start-other'];
        }
        $answer .= $startitem;
        $answer .= $hiddenfield.'
        <input class="checkbox other-checkbox dontread" style="visibility:hidden" type="checkbox" name="'.$myfname.'cbox" id="answer'.$myfname.'cbox"';
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
            if ($question->other_numbers_only==1)
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
            if ($question->other_numbers_only==1)
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
    //            alert('".sprintf(gT("Please choose at most %d answers for question \"%s\"","js"), $maxansw, trim(javascriptEscape(str_replace(array("\n", "\r"), "", $ia[3]),true,true)))."');
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
    //        "\tif (count < {$minansw} && document.getElementById('display{$question->primaryKey}').value == 'on'){\n"
    //        . "alert('".sprintf(gT("Please choose at least %d answer(s) for question \"%s\"","js"),
    //        $minansw, trim(javascriptEscape(str_replace(array("\n", "\r"), "",$ia[3]),true,true)))."');\n"
    //        . "return false;\n"
    //        . "\t} else {\n"
    //        . "if (oldonsubmit_{$question->primaryKey}){\n"
    //        . "\treturn oldonsubmit_{$question->primaryKey}();\n"
    //        . "}\n"
    //        . "return true;\n"
    //        . "\t}\n"
    //        . "}\n"
    //        . "document.limesurvey.onsubmit = ensureminansw_{$question->primaryKey}\n"
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
#        . "oldonsubmitOther_{$question->primaryKey} = document.limesurvey.onsubmit;\n"
#        . "function ensureOther_{$question->primaryKey}()\n"
#        . "{\n"
#        . "\tothercboxval=document.getElementById('answer".$myfname."cbox').checked;\n"
#        . "\totherval=document.getElementById('answer".$myfname."').value;\n"
#        . "\tif (otherval != '' || othercboxval != true) {\n"
#        . "if(typeof oldonsubmitOther_{$question->primaryKey} == 'function') {\n"
#        . "\treturn oldonsubmitOther_{$question->primaryKey}();\n"
#        . "}\n"
#        . "\t}\n"
#        . "\telse {\n"
#        . "alert('".sprintf(gT("You've marked the 'Other:' field for question '%s'. Please also fill in the accompanying comment field.","js"),trim(javascriptEscape($ia[3],true,true)))."');\n"
#        . "return false;\n"
#        . "\t}\n"
#        . "}\n"
#        . "document.limesurvey.onsubmit = ensureOther_{$question->primaryKey};\n"
#        . "\t-->\n"
#        . "</script>\n";
#    }

#    $answer = $checkotherscript . $answer;

    $answer .= $postrow;
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_multiplechoice_withcomments(Question $question)
{
    global $thissurvey;


    $inputnames= array();
    if ($question->survey->bool_nokeyboard)
    {
        includeKeypad();
        $kpclass = "text-keypad";
    }
    else
    {
        $kpclass = "";
    }

    $inputnames = array();

    $checkconditionFunction = "checkconditions";

    

    if ($question->other_numbers_only==1)
    {
        $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeparator = $sSeparator['separator'];
        $oth_checkconditionFunction = "fixnum_checkconditions";
    }
    else
    {
        $oth_checkconditionFunction = "checkconditions";
    }

    if (trim($question->other_replace_text)!='')
    {
        $othertext=$question->other_replace_text;
    }
    else
    {
        $othertext=gT('Other:');
    }

    $anscount = count($question->subQuestions) * 2;

    $answer = "<input type='hidden' name='MULTI$question->sgqa' value='$anscount' />\n";
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

    foreach ($question->subQuestions as $subQuestion)
    {
        $myfname = $question->sgqa.$subQuestion->title;
        $trbc='';
        /* Check for array_filter */

        list($htmltbody2, $hiddenfield)=return_array_filter_strings($question, $subQuestion, $myfname, $trbc, $myfname,
            "li", "question-item answer-item checkbox-text-item");

        if($label_width < strlen(trim(strip_tags($subQuestion->question))))
        {
            $label_width = strlen(trim(strip_tags($subQuestion->question)));
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
        . $subQuestion->question."</label>\n";

        $answer_main .= "<input type='hidden' name='java$myfname' id='java$myfname' value='";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $answer_main .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
        }
        $answer_main .= "' />\n";
        $fn++;
        $answer_main .= "</span>\n<span class=\"comment\">\n\t<label for='answer$myfname2' class=\"answer-comment hide \">".gT('Make a comment on your choice here:')."</label>\n"
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
        $myfname = $question->sgqa.'other';
        $myfname2 = $myfname.'comment';
        $anscount = $anscount + 2;
        $answer_main .= "\t<li class=\"other question-item answer-item checkbox-text-item other-item\" id=\"javatbd$myfname\">\n<span class=\"option\">\n"
        . "\t<label for=\"answer$myfname\" class=\"answertext\">\n".$othertext."\n<input class=\"text other ".$kpclass."\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\" title=\"".gT('Other').'" size="10"';
        $answer_main .= " onkeyup='$oth_checkconditionFunction(this.value, this.name, this.type);'";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname])
        {
            $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            if ($question->other_numbers_only==1)
            {
                $dispVal = str_replace('.',$sSeparator,$dispVal);
            }
            $answer_main .= ' value="'.htmlspecialchars($dispVal,ENT_QUOTES).'"';
        }
        $fn++;
        // --> START NEW FEATURE - SAVE
        $answer_main .= " />\n\t</label>\n</span>\n"
        . "<span class=\"comment\">\n\t<label for=\"answer$myfname2\" class=\"answer-comment hide\">".gT('Make a comment on your choice here:')."\t</label>\n"
        . '<input class="text '.$kpclass.'" type="text" size="40" name="'.$myfname2.'" id="answer'.$myfname2.'"'
        . " onkeyup='$checkconditionFunction(this.value,this.name,this.type);'"
        . ' title="'.gT('Make a comment on your choice here:').'" value="';
        // --> END NEW FEATURE - SAVE

        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2])) {$answer_main .= htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2],ENT_QUOTES);}
        $answer_main .= "\"/>\n";
        $answer_main .= "</span>\n\t</li>\n";

        $inputnames[]=$myfname;
        $inputnames[]=$myfname2;
    }
    $answer .= "<ul class=\"subquestions-list questions-list checkbox-text-list\">\n".$answer_main."</ul>\n";
    if($question->commented_checkbox!="allways" && $question->commented_checkbox_auto)
    {
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."multiplechoice_withcomments.js");
#        $script= " doMultipleChoiceWithComments({$question->primaryKey},'{$question->commented_checkbox}');\n";
#        App()->getClientScript()->registerScript("doMultipleChoiceWithComments",$script,CClientScript::POS_HEAD);// Deactivate now: need to be after question, and just after
        $answer .= "<script type='text/javascript'>\n"
        . "  /*<![CDATA[*/\n"
        ." doMultipleChoiceWithComments({$question->primaryKey},'{$question->commented_checkbox}');\n"
        ." /*]]>*/\n"
        ."</script>\n";
    }
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_file_upload(Question $question, Response $response)
{
    $checkconditionFunction = "checkconditions";
    $scriptloc = Yii::app()->getController()->createUrl('uploader/index');



    $uploadbutton = "<div class='upload-button'><a id='upload_".$question->sgqa."' class='upload' ";
    $uploadbutton .= " href='#' onclick='javascript:upload_$question->sgqa();'";
    $uploadbutton .=">" .gT('Upload files'). "</a></div>";

    $answer = "<script type='text/javascript'>
        function upload_$question->sgqa() {
            var uploadurl = '{$scriptloc}?sid=".Yii::app()->getConfig('surveyID')."&fieldname={$question->sgqa}&qid={$question->primaryKey}';
            uploadurl += '&show_title={$question->show_title}';
            uploadurl += '&show_comment={$question->show_comment}';
            uploadurl += '&minfiles=' + LEMval('{$question->min_num_of_files}');
            uploadurl += '&maxfiles=' + LEMval('{$question->max_num_of_files}');
            $('#upload_$question->sgqa').attr('href',uploadurl);
        }
        var uploadLang = {
             title: '" . gT('Upload your files','js') . "',
             returnTxt: '" . gT('Return to survey','js') . "',
             headTitle: '" . gT('Title','js') . "',
             headComment: '" . gT('Comment','js') . "',
             headFileName: '" . gT('File name','js') . "',
             deleteFile : '".gt('Delete')."',
             editFile : '".gt('Edit')."'
            };
        var imageurl =  '".Yii::app()->getConfig('imageurl')."';
        var uploadurl =  '".$scriptloc."';
    </script>\n";
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."modaldialog.js");
    Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . "uploader-files.css");
    // Modal dialog
    $answer .= $uploadbutton;

    $answer .= "<input type='hidden' id='".$question->sgqa."' name='".$question->sgqa."' value='".htmlspecialchars($response->{$question->sgqa},ENT_QUOTES,'utf-8')."' />";
    $answer .= "<input type='hidden' id='".$question->sgqa."_filecount' name='".$question->sgqa."_filecount' value=";

    if (array_key_exists($question->sgqa."_filecount", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')]))
    {
        $tempval = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$question->sgqa."_filecount"];
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

    $answer .= "<div id='".$question->sgqa."_uploadedfiles'></div>";

    $answer .= '<script type="text/javascript">
    $(document).ready(function(){
    var fieldname = "'.$question->sgqa.'";
    var filecount = $("#"+fieldname+"_filecount").val();
    var json = $("#"+fieldname).val();
    var show_title = "'.$question->show_title.'";
    var show_comment = "'.$question->show_comment.'";
    displayUploadedFiles(json, filecount, fieldname, show_title, show_comment);
    });
    </script>';

    $answer .= '<script type="text/javascript">
    $(".basic_'.$question->sgqa.'").change(function() {
    var i;
    var jsonstring = "[";

    for (i = 1, filecount = 0; i <= LEMval("'.$question->max_num_of_files.'"); i++)
    {
    if ($("#'.$question->sgqa.'_"+i).val() == "")
    continue;

    filecount++;
    if (i != 1)
    jsonstring += ", ";

    if ($("#answer'.$question->sgqa.'_"+i).val() != "")
    jsonstring += "{ ';

    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['show_title']))
        $answer .= '\"title\":\""+$("#'.$question->sgqa.'_title_"+i).val()+"\",';
    else
        $answer .= '\"title\":\"\",';

    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['show_comment']))
        $answer .= '\"comment\":\""+$("#'.$question->sgqa.'_comment_"+i).val()+"\",';
    else
        $answer .= '\"comment\":\"\",';

    $answer .= '\"size\":\"\",\"name\":\"\",\"ext\":\"\"}";
    }
    jsonstring += "]";

    $("#'.$question->sgqa.'").val(jsonstring);
    $("#'.$question->sgqa.'_filecount").val(filecount);
    });
    </script>';

    $inputnames[] = $question->sgqa;
    $inputnames[] = $question->sgqa."_filecount";
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_multipleshorttext(Question $question)
{
    global $thissurvey;


    $extraclass ="";
    $answer='';
    

    if ($question->numbers_only==1)
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
    if (intval(trim($question->maximum_chars))>0)
    {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maximum_chars= intval(trim($question->maximum_chars));
        $maxlength= "maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= "";
    }
    if (trim($question->text_input_width)!='')
    {
        $tiwidth=$question->text_input_width;
        $extraclass .=" inputwidth".trim($question->text_input_width);
    }
    else
    {
        $tiwidth=20;
    }

    if (trim($question->prefix)!='') {
        $prefix=$question->prefix;
        $extraclass .=" withprefix";
    } else {
        $prefix = '';
    }

    if (trim($question->suffix)!='') {
        $suffix=$question->suffix;
        $extraclass .=" withsuffix";
    }
    else
    {
        $suffix = '';
    }

    if ($question->survey->bool_nokeyboard)
    {
        includeKeypad();
        $kpclass = "text-keypad";
        $extraclass .=" inputkeypad";
    }
    else
    {
        $kpclass = "";
    }

    //$answer .= "\t<input type='hidden' name='MULTI$question->sgqa' value='$anscount'>\n";
    $fn = 1;

    $answer_main = '';

    $label_width = 0;

    if (trim($question->display_rows)!='')
    {
        //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
        $drows=$question->display_rows;

        foreach ($question->subQuestions as $subQuestion)
        {
            $myfname = $question->sgqa.$subQuestion->title;
            if ($subQuestion->question == "")
            {
                $subQuestion->question = "&nbsp;";
            }

            //NEW: textarea instead of input=text field
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($question, $subQuestion, $myfname, '', $myfname,
                "li", "question-item answer-item text-item" . $extraclass);

            $answer_main .= "\t$htmltbody2\n"
            . "<label for=\"answer$myfname\">{$subQuestion->question}</label>\n"
            . "\t<span>\n".$prefix."\n".'
            <textarea class="textarea '.$kpclass.'" name="'.$myfname.'" id="answer'.$myfname.'"
            rows="'.$drows.'" cols="'.$tiwidth.'" '.$maxlength.' onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type);">';

            if($label_width < strlen(trim(strip_tags($subQuestion->question))))
            {
                $label_width = strlen(trim(strip_tags($subQuestion->question)));
            }

            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                if ($question->numbers_only==1)
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
        foreach ($question->subQuestions as $subQuestion)
        {
            $myfname = $question->sgqa.$subQuestion->title;
            // color code missing mandatory questions red
            if ($question->bool_mandatory && !$question->validateResponse($response)->getPassedMandatory()) {
                $subQuestion->question = "<span class='errormandatory'>{$subQuestion->question}</span>";
            }

            list($htmltbody2, $hiddenfield)=return_array_filter_strings($question, $thissurvey, $myfname, '', $myfname,
                "li", "question-item answer-item text-item" . $extraclass);
            $answer_main .= "\t$htmltbody2\n"
            . "<label for=\"answer$myfname\">{$subQuestion->question}</label>\n"
            . "\t<span>\n".$prefix."\n".'<input class="text '.$kpclass.'" type="text" size="'.$tiwidth.'" name="'.$myfname.'" id="answer'.$myfname.'" value="';

            if($label_width < strlen(trim(strip_tags($subQuestion->question))))
            {
                $label_width = strlen(trim(strip_tags($subQuestion->question)));
            }

            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                $dispVal = $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
                if ($question->numbers_only==1)
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

    $answer = "<ul class=\"subquestions-list questions-list text-list\">\n".$answer_main."</ul>\n";

    return array($answer, $inputnames);
}

// -----------------------------------------------------------------
// @todo: Can remove DB query by passing in answer list from EM
function do_multiplenumeric(Question $question)
{
    global $thissurvey;


    $extraclass ="";
    $checkconditionFunction = "fixnum_checkconditions";
    
    $html='';
    $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeparator = $sSeparator['separator'];
    //Must turn on the "numbers only javascript"
    $extraclass .=" numberonly";
    if ($question->thousands_separator == 1) {
        App()->clientScript->registerPackage('jquery-price-format');
        App()->clientScript->registerScriptFile('scripts/numerical_input.js');
        $extraclass .= " thousandsseparator";
    }

    if (intval(trim($question->maximum_chars))>0)
    {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maximum_chars= intval(trim($question->maximum_chars));
        $maxlength= "maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= " maxlength='25' ";
    }

    if (trim($question->prefix)!='') {
        $prefix=$question->prefix;
        $extraclass .=" withprefix";
    }
    else
    {
        $prefix = '';
    }

    if (trim($question->suffix)!='') {
        $suffix=$question->suffix;
        $extraclass .=" withsuffix";
    }
    else
    {
        $suffix = '';
    }

    if ($question->survey->bool_nokeyboard)
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

    if (trim($question->text_input_width)!='')
    {
        $tiwidth=$question->text_input_width;
        $extraclass .=" inputwidth".trim($question->text_input_width);
    }
    else
    {
        $tiwidth=10;
    }
    $prefixclass="numeric";
    if ($question->slider_layout==1)
    {
        $prefixclass="slider";
        $slider_layout=true;
        $extraclass .=" withslider";
        $slider_step=trim(LimeExpressionManager::ProcessString("{{$question->slider_accuracy}}", $question->primaryKey,
            array(), 1, 1));
        $slider_step =  (is_numeric($slider_step))?$slider_step:1;
        $slider_min = trim(LimeExpressionManager::ProcessString("{{$question->slider_min}}", $question->primaryKey,
            array(), 1, 1));
        $slider_mintext = $slider_min =  (is_numeric($slider_min))?$slider_min:0;
        $slider_max = trim(LimeExpressionManager::ProcessString("{{$question->slider_max}}", $question->primaryKey,
            array(), 1, 1));
        $slider_maxtext = $slider_max =  (is_numeric($slider_max))?$slider_max:100;
        $slider_default=trim(LimeExpressionManager::ProcessString("{{$question->slider_default}}",
            $question->primaryKey, array(), 1, 1));
        $slider_default =  (is_numeric($slider_default))?$slider_default:"";

        if ($slider_default == '' && $question->slider_middlestart==1)
        {
            $slider_middlestart = intval(($slider_max + $slider_min)/2);
        }
        else
        {
            $slider_middlestart = '';
        }

        $slider_separator= (trim($question->slider_separator)!='')?$question->slider_separator:"";
        $slider_reset=($question->slider_reset)?1:0;
    }
    else
    {
        $slider_layout = false;
    }
    $hidetip=$question->hide_tip;

    $fn = 1;

    $answer_main = '';

    foreach($question->subQuestions as $subQuestion)
    {
        $myfname = $question->sgqa . $subQuestion->title;
        if ($subQuestion->question == "") {$subQuestion->question = "&nbsp;";}
        if ($slider_layout === false || $slider_separator == '')
        {
            $theanswer = $subQuestion->question;
            $sliderleft='';
            $sliderright='';
        }
        else
        {
            $aAnswer=explode($slider_separator,$subQuestion->question);
            $theanswer=(isset($aAnswer[0]))?$aAnswer[0]:"";
            $sliderleft=(isset($aAnswer[1]))?$aAnswer[1]:"";
            $sliderright=(isset($aAnswer[2]))?$aAnswer[2]:"";
            $sliderleft="<div class=\"slider_lefttext\">$sliderleft</div>";
            $sliderright="<div class=\"slider_righttext\">$sliderright</div>";
        }

        // color code missing mandatory questions red
        if ($subQuestion->bool_mandatory && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] === '')
        {
            $theanswer = "<span class='errormandatory'>{$theanswer}</span>";
        }

        list($htmltbody2, $hiddenfield)=return_array_filter_strings($question, $myfname, '', $myfname,
            "li", "question-item answer-item text-item numeric-item" . $extraclass);
        $answer_main .= "\t$htmltbody2\n";
        $answer_main .= "<label for=\"answer$myfname\" class=\"{$prefixclass}-label\">{$theanswer}</label>\n";

            $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
            $sSeparator = $sSeparator['separator'];

            $answer_main .= "{$sliderleft}<span class=\"input\">\n\t".$prefix."\n\t<input class=\"text $kpclass\" type=\"number\" step=\"any\" size=\"".$tiwidth."\" name=\"".$myfname."\" id=\"answer".$myfname."\" title=\"".gT('Only numbers may be entered in this field.')."\" value=\"";
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
    if (trim($question->equals_num_value) != ''
    || trim($question->min_num_value) != ''
    || trim($question->max_num_value) != ''
    )
    {
        $qinfo = LimeExpressionManager::GetQuestionStatus($question->primaryKey);
        if (trim($question->equals_num_value) != '')
        {
            $answer_main .= "\t<li class='multiplenumerichelp help-item'>\n"
            . "<span class=\"label\">".gT('Remaining: ')."</span>\n"
            . "<span id=\"remainingvalue_{$question->primaryKey}\" class=\"dynamic_remaining\">$prefix\n"
            . "{" . $qinfo['sumRemainingEqn'] . "}\n"
            . "$suffix</span>\n"
            . "\t</li>\n";
        }

        $answer_main .= "\t<li class='multiplenumerichelp  help-item'>\n"
        . "<span class=\"label\">".gT('Total: ')."</span>\n"
        . "<span id=\"totalvalue_{$question->primaryKey}\" class=\"dynamic_sum\">$prefix\n"
        . "{" . $qinfo['sumEqn'] . "}\n"
        . "$suffix</span>\n"
        . "\t</li>\n";
    }
    $html .= "<ul class=\"subquestions-list questions-list text-list {$prefixclass}-list\">\n".$answer_main."</ul>\n";


    if($question->slider_layout==1)
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
        $slider_showminmax=($question->slider_showminmax==1)?1:0;
        //some var for slider
        $aJsLang=array(
            'reset' => gT('Reset'),
            'tip' => gT('Please click and drag the slider handles to enter your answer.'),
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
        $html .= "<script type='text/javascript'><!--\n"
                    . " doNumericSlider({$question->primaryKey},".ls_json_encode($aJsVar).");\n"
                    . " //--></script>";
    }
    $sSeparator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeparator = $sSeparator['separator'];

    return [$html, $inputnames];
}





// ---------------------------------------------------------------
function do_numerical(Question $question)
{
    $extraclass ="";
    $answertypeclass = "numeric";

    $checkconditionFunction = "fixnum_checkconditions";
    $session = App()->surveySessionManager->current;
    if (trim($question->prefix[$session->language])!='') {
        $prefix=$question->prefix[$session->language];
        $extraclass .=" withprefix";
    }
    else
    {
        $prefix = '';
    }
    if ($question->thousands_separator == 1) {
        App()->clientScript->registerPackage('jquery-price-format');
        App()->clientScript->registerScriptFile('scripts/numerical_input.js');
        $extraclass .= " thousandsseparator";
    }
    if (trim($question->suffix[$session])!='') {
        $suffix=$question->suffix[$session];
        $extraclass .=" withsuffix";
    }
    else
    {
        $suffix = '';
    }
    if (intval(trim($question->maximum_chars))>0 && intval(trim($question->maximum_chars))<20)
    {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maximum_chars= intval(trim($question->maximum_chars));
        $maxlength= " maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= " maxlength='20' ";
    }
    if (trim($question->text_input_width)!='')
    {
        $tiwidth=$question->text_input_width;
        $extraclass .=" inputwidth-".trim($question->text_input_width);
    }
    else
    {
        $tiwidth=10;
    }

    if (trim($question->num_value_int_only)==1) {
        $acomma="";
        $extraclass .=" integeronly";
        $answertypeclass .= " integeronly";
        $integeronly=1;
    } else {
        $acomma = getRadixPointData($question->survey->getLocalizedNumberFormat())['separator'];
        $integeronly=0;
    }

    $fValue= App()->surveySessionManager->current->response->{$question->sgqa};
    // Fix the display value : Value is stored as decimal in SQL then return dot and 0 after dot. Seems only for numerical question type
    if(strpos($fValue,"."))
    {
        $fValue=rtrim(rtrim($fValue,"0"),".");
    }
    $fValue = str_replace('.',$acomma,$fValue);


    if ($question->survey->bool_nokeyboard)
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
    . " <label for='answer{$question->sgqa}' class='hide label'>".gT('Your answer')."</label>\n$prefix\t"
    . "<input class='text {$answertypeclass}' type=\"text\" size=\"$tiwidth\" name=\"$question->sgqa\"  title=\"".gT('Only numbers may be entered in this field.')."\" "
    . "id=\"answer{$question->sgqa}\" value=\"{$fValue}\" onkeyup=\"{$checkconditionFunction}(this.value, this.name, this.type,'onchange',{$integeronly})\" "
    . " {$maxlength} />\t{$suffix}\n</p>\n";
    // --> END NEW FEATURE - SAVE

    $inputnames[]=$question->sgqa;
    $mandatory=null;
    return array($answer, $inputnames, $mandatory);
}





function getLatLongFromIp($sIPAddress){
    $ipInfoDbAPIKey = Yii::app()->getConfig("ipInfoDbAPIKey");
    if($ipInfoDbAPIKey)// ipinfodb.com need a key
    {
        $oXML = simplexml_load_file("http://api.ipinfodb.com/v3/ip-city/?key=$ipInfoDbAPIKey&ip=$sIPAddress&format=xml");
        if ($oXML->{'statusCode'} == "OK"){
            $lat = (float)$oXML->{'latitude'};
            $lng = (float)$oXML->{'longitude'};

            return(array($lat,$lng));
        }
        else
            return false;
    }
}





// ---------------------------------------------------------------
function do_yesno(Question $question, Response $response)
{

    $session = App()->surveySessionManager->current;
    $checkconditionFunction = "checkconditions";

    $answer = "<ul class=\"answers-list radio-list\">\n"
    . "\t<li class=\"answer-item radio-item\">\n<input class=\"radio\" type=\"radio\" name=\"{$question->sgqa}\" id=\"answer{$question->sgqa}Y\" value=\"Y\"";

    if ($response->{$question->sgqa} == 'Y')
    {
        $answer .= CHECKED;
    }
    // --> START NEW FEATURE - SAVE
    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$question->sgqa}Y\" class=\"answertext\">\n\t".gT('Yes')."\n</label>\n\t</li>\n"
    . "\t<li class=\"answer-item radio-item\">\n<input class=\"radio\" type=\"radio\" name=\"{$question->sgqa}\" id=\"answer{$question->sgqa}N\" value=\"N\"";
    // --> END NEW FEATURE - SAVE

    if ($response->{$question->sgqa} == 'N')
    {
        $answer .= CHECKED;
    }
    // --> START NEW FEATURE - SAVE
    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$question->sgqa}N\" class=\"answertext\" >\n\t".gT('No')."\n</label>\n\t</li>\n";
    // --> END NEW FEATURE - SAVE
    if (!$question->bool_mandatory && $question->survey->bool_shownoanswer)
    {
        $answer .= "\t<li class=\"answer-item radio-item noanswer-item\">\n<input class=\"radio\" type=\"radio\" name=\"{$question->sgqa}\" id=\"answer{$question->sgqa}\" value=\"\"";
        if (empty($response->{$question->sgqa}))
        {
            $answer .= CHECKED;
        }
        // --> START NEW FEATURE - SAVE
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$question->sgqa}\" class=\"answertext\">\n\t".gT('No answer')."\n</label>\n\t</li>\n";
        // --> END NEW FEATURE - SAVE
    }

    $answer .= "</ul>\n\n<input type=\"hidden\" name=\"java{$question->sgqa}\" id=\"java{$question->sgqa}\" value=\"".  $response->{$question->sgqa} ."\" />\n";
    $inputnames[]=$question->sgqa;
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_gender(Question $question, Response $response)
{


    $checkconditionFunction = "checkconditions";

    

    $answer = "<ul class=\"answers-list radio-list\">\n"
    . "\t<li class=\"answer-item radio-item\">\n"
    . '        <input class="radio" type="radio" name="'.$question->sgqa.'" id="answer'.$question->sgqa.'F" value="F"';
    if ($response->{$question->sgqa} == 'F')
    {
        $answer .= CHECKED;
    }
    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
    . '        <label for="answer'.$question->sgqa.'F" class="answertext">'.gT('Female')."</label>\n\t</li>\n";

    $answer .= "\t<li class=\"answer-item radio-item\">\n<input class=\"radio\" type=\"radio\" name=\"$question->sgqa\" id=\"answer".$question->sgqa.'M" value="M"';

    if ($response->{$question->sgqa} == 'M')
    {
        $answer .= CHECKED;
    }
    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$question->sgqa}M\" class=\"answertext\">".gT('Male')."</label>\n\t</li>\n";

    if (!$question->bool_mandatory && $question->survey->bool_shownoanswer)
    {
        $answer .= "\t<li class=\"answer-item radio-item noanswer-item\">\n<input class=\"radio\" type=\"radio\" name=\"$question->sgqa\" id=\"answer".$question->sgqa.'" value=""';
        if ($response->{$question->sgqa} == '')
        {
            $answer .= CHECKED;
        }
        // --> START NEW FEATURE - SAVE
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer$question->sgqa\" class=\"answertext\">".gT('No answer')."</label>\n\t</li>\n";
        // --> END NEW FEATURE - SAVE

    }
    $answer .= "</ul>\n\n<input type=\"hidden\" name=\"java$question->sgqa\" id=\"java$question->sgqa\" value=\"".$response->{$question->sgqa}."\" />\n";

    $inputnames[]=$question->sgqa;
    return array($answer, $inputnames);
}



// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array(Question $question)
{
    global $thissurvey;
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $question->bool_mandatory) ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $repeatheadings = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
    $extraclass ="";

    $caption="";// Just leave empty, are replaced after
    $checkconditionFunction = "checkconditions";
    $qquery = "SELECT other FROM {{questions}} WHERE qid={$question->primaryKey}";
    $other = Yii::app()->db->createCommand($qquery)->queryScalar(); //Checked

    
    if (trim($question->answer_width)!='')
    {
        $answerwidth=$question->answer_width;
    }
    else
    {
        $answerwidth=20;
    }
    $columnswidth=100-$answerwidth;

    if ($question->use_dropdown == 1)
    {
        $useDropdownLayout = true;
        $extraclass .=" dropdown-list";
        $caption=gT("An array with sub-question on each line. You have to select your answer.");
    }
    else
    {
        $useDropdownLayout = false;
        $caption=gT("An array with sub-question on each line. The answers are contained in the table header. ");
    }
    if(ctype_digit(trim($question->repeat_headings)) && trim($question->repeat_headings!=""))
    {
        $repeatheadings = intval($question->repeat_headings);
        $minrepeatheadings = 0;
    }


    $lresult= Answer::model()->findAllByAttributes(['question_id' => $question->primaryKey], [
        'order' => 'sortorder, code',
    ]);
    $labelans=array();
    $labelcode=array();
    foreach ($lresult as $lrow)
    {
        $labelans[]=$lrow->answer;
        $labelcode[]=$lrow->code;
    }
    if ($useDropdownLayout === false && count($lresult) > 0)
    {
        $sQuery = "SELECT count(qid) FROM {{questions}} WHERE parent_qid={$question->primaryKey} AND question like '%|%' ";
        $iCount = Yii::app()->db->createCommand($sQuery)->queryScalar();

        if ($iCount>0) {
            $right_exists=true;
            $answerwidth=$answerwidth/2;
        }
        else
        {
            $right_exists=false;
        }
           $fn=1;

        $numrows = count($labelans);
        if ($right_exists)
        {
            ++$numrows;
            $caption.=gT("After answers, a cell give some information. ");
        }
        if (!$question->bool_mandatory && $question->survey->bool_shownoanswer)
        {
            ++$numrows;
            $caption.=gT("The last cell are for no answer. ");
        }
        $cellwidth = round( ($columnswidth / $numrows ) , 1 );

        $answer_start = "\n<table class=\"question subquestions-list questions-list {$extraclass}\" summary=\"{$caption}\">\n";
        $answer_head_line= "\t<td>&nbsp;</td>\n";
            foreach ($labelans as $ld)
            {
                $answer_head_line .= "\t<th>".$ld."</th>\n";
            }
            if ($right_exists) {$answer_head_line .= "\t<td>&nbsp;</td>\n";}
            if (!$question->bool_mandatory && $question->survey->bool_shownoanswer) //Question is not mandatory and we can show "no answer"
            {
                $answer_head_line .= "\t<th>".gT('No answer')."</th>\n";
            }
        $answer_head = "\t<thead><tr class=\"dontread\">\n".$answer_head_line."</tr></thead>\n\t\n";

        $answer = '<tbody>';
        $trbc = '';
        $inputnames=array();
        foreach($question->subQuestions as  $subQuestion)
        {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
            {
                if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                {
                    $answer .= "</tbody>\n<tbody>";// Close actual body and open another one
                    $answer .= "<tr class=\"dontread repeat headings\">{$answer_head_line}</tr>";
                }
            }
            $myfname = $question->sgqa.$subQuestion->title;
            $answertext = $subQuestion->question;
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
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($question, $myfname, $trbc,
                $myfname, "tr", "$trbc answers-list radio-list");
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
                . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"$ld\" id=\"answer$myfname-$ld\"";
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == $ld)
                {
                    $answer .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
                . "<label class=\"hide read\" for=\"answer$myfname-$ld\">{$labelans[$thiskey]}</label>\n"
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

            if (!$question->bool_mandatory && $question->survey->bool_shownoanswer)
            {
                $answer .= "\t<td class=\"answer-item radio-item noanswer-item\">\n"
                ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"\" id=\"answer$myfname-\" ";
                if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname] == '')
                {
                    $answer .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\"  />\n"
                ."<label class=\"hide read\" for=\"answer$myfname-\">".gT('No answer')."</label>\n"
                . "\t</td>\n";
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
        if (!$question->bool_mandatory && $question->survey->bool_shownoanswer) //Question is not mandatory
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
        $sQuery = "SELECT count(question) FROM {{questions}} WHERE parent_qid={$question->primaryKey} AND question like '%|%' ";
        $iCount = Yii::app()->db->createCommand($sQuery)->queryScalar();
        if ($iCount>0) {
            $right_exists=true;
            $answerwidth=$answerwidth/2;
        } else {
            $right_exists=false;
        }
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($question->random_order==1) {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$question->primaryKey} AND language='".$session->language."' ORDER BY ".dbRandom();
        }
        else
        {
            $ansquery = "SELECT * FROM {{questions}} WHERE parent_qid={$question->primaryKey} AND language='".$session->language."' ORDER BY question_order";
        }
        $ansresult = dbExecuteAssoc($ansquery); //Checked
        $aQuestions = $ansresult->readAll();
        $anscount = count($aQuestions);
        $fn=1;

        $numrows = count($labels);
        if (!$question->bool_mandatory && $question->survey->bool_shownoanswer)
        {
            ++$numrows;
        }
        if ($right_exists)
        {
            ++$numrows;
        }
        $cellwidth = round( ($columnswidth / $numrows ) , 1 );

        $answer_start = "\n<table class=\"question subquestions-list questions-list {$extraclass}\" summary=\"$caption\" >\n";

        $answer = "\t<tbody>\n";
        $trbc = '';
        $inputnames=array();

        foreach ($aQuestions as $subQuestion)
        {
            $myfname = $question->sgqa.$subQuestion->title;
            $trbc = alternation($trbc , 'row');
            $answertext=$subQuestion->question;
            $answertextsave=$answertext;
            if (strpos($answertext,'|'))
            {
                $answertext=substr($answertext,0, strpos($answertext,'|'));
            }
            if (strpos($answertext,'|')) {$answerwidth=$answerwidth/2;}

            if ($subQuestion->bool_mandatory && in_array($myfname, $aMandatoryViolationSubQ))
            {
                $answertext = '<span class="errormandatory">'.$answertext.'</span>';
            }
            // Get array_filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($question, $subQuestion, $myfname, $trbc,
                $myfname, "tr", "$trbc question-item answer-item dropdown-item");
            $answer .= $htmltbody2;

            $answer .= "\t<th class=\"answertext\">\n<label for=\"answer{$myfname}\">{$answertext}</label>"
            . $hiddenfield
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
            {
                $answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
            }
            $answer .= "\" />\n\t</th>\n";

            $answer .= "\t<td >\n"
            . "<select name=\"$myfname\" id=\"answer$myfname\" onchange=\"$checkconditionFunction(this.value, this.name, this.type);\">\n";

            // Dropdown representation is en exception - even if mandatory or  $question->survey->bool_shownoanswer is disable a neutral option needs to be shown where the mandatory case asks actively
            if (!$question->bool_mandatory && $question->survey->bool_shownoanswer)
            {
                $sOptionText=gT('No answer');
            }
            else
            {
                $sOptionText=gT('Please choose...');
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
        $answer = "\n<p class=\"error\">".gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames='';
    }
    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array_multitext(Question $question)
{
    global $thissurvey;
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $question->bool_mandatory) ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $repeatheadings = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
    $extraclass ="";

    $caption=gT("An array of sub-question on each cell. The sub-question text are in the table header and concerns line header. ");
    if ($question->survey->bool_nokeyboard)
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
    $show_grand = null;
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

    if(ctype_digit(trim($question->repeat_headings)) && trim($question->repeat_headings!=""))
    {
        $repeatheadings = intval($question->repeat_headings);
        $minrepeatheadings = 0;
    }
    if (intval(trim($question->maximum_chars))>0)
    {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maximum_chars= intval(trim($question->maximum_chars));
        $maxlength= "maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= "";
    }
    if ($question->numbers_only==1)
    {
        $checkconditionFunction = "fixnum_checkconditions";
        if(in_array($question->show_totals,array("R","C","B")))
        {
            $q_table_id = 'totals_'.$question->primaryKey;
            $q_table_id_HTML = ' id="'.$q_table_id.'"';
        }
        $num_class = ' numbers-only';
        $extraclass.=" numberonly";
        $caption.=gT("Each answer is a number. ");
        switch ($question->show_totals)
        {
            case 'R':
                $totals_class = $show_totals = 'row';
                $row_total = '<td class="total information-item">
                <label>
                <input name="[[ROW_NAME]]_total" title="[[ROW_NAME]] total" size="[[INPUT_WIDTH]]" value="" type="text" disabled="disabled" class="disabled" />
                </label>
                </td>';
                $col_head = '            <th class="total">'.gT('Total').'</th>';
                if($show_grand == true)
                {
                    $row_head = '
                    <th class="answertext total">'.gT('Grand total').'</th>';
                    $col_total = '
                    <td>&nbsp;</td>';
                    $grand_total = '
                    <td class="total grand information-item">
                    <input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
                    </td>';
                };
                $caption.=gT("The last row shows the total for the column. ");
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
                $caption.=gT("The last column shows the total for the row. ");
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
                $col_head = '            <th class="total">'.gT('Total').'</th>';
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
                $caption.=gT("The last row shows the total for the column and the last column shows the total for the row. ");
                break;
        };
        if(!empty($totals_class))
        {
            $totals_class = ' show-totals '.$totals_class;
            if($question->location_mapheight)
            {
                $totals_class .= ' grand';
                $show_grand = true;
            };
        };
    }
    else
    {
    };
    if (trim($question->answer_width)!='')
    {
        $answerwidth=$question->answer_width;
    }
    else
    {
        $answerwidth=20;
    };
    if (trim($question->text_input_width)!='')
    {
        $inputwidth=$question->text_input_width;
        $extraclass .=" inputwidth-".trim($question->text_input_width);
    }
    else
    {
        $inputwidth = 20;
    }
    $columnswidth=100-($answerwidth*2);

    $scale1 = $question->getSubQuestions(1);
    $labelans=array();
    $labelcode=array();
    foreach($scale1 as $lrow)
    {
        $labelans[]=$lrow['question'];
        $labelcode[]=$lrow['title'];
    }
    if ($numrows=count($labelans))
    {
        if (!$question->bool_mandatory && $question->survey->bool_shownoanswer) {$numrows++;}
        if( ($show_grand == true &&  $show_totals == 'col' ) || $show_totals == 'row' ||  $show_totals == 'both' )
        {
            ++$numrows;
        };
        $cellwidth=$columnswidth/$numrows;

        $cellwidth=sprintf('%02d', $cellwidth);

        $ansresult = count($question->getSubQuestions(0));
        if ($ansresult>0)
        {
            $right_exists=true;
            $answerwidth=$answerwidth/2;
            $caption.=gT("The last cell give some information. ");
        }
        else
        {
            $right_exists=false;
        }
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
        foreach ($question->getSubQuestions(0) as $subQuestion)
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
            $myfname = $question->sgqa.$subQuestion->title;
            $answertext = $subQuestion->question;
            $answertextsave=$answertext;
            /* Check the sub Q mandatory volation */
            if ($subQuestion->bool_mandatory && !empty($aMandatoryViolationSubQ))
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
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($question, $subQuestion, $myfname, $trbc,
                $myfname, "tr", "$trbc subquestion-list questions-list");

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
                if ($question->numbers_only==1)
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
            if ($question->numbers_only==1)
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
    $('#question{$question->primaryKey} .question').delegate('input[type=text]:visible:enabled','blur keyup',function(event){
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
        $answer = "\n<p class=\"error\">".gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames='';
    }
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array_multiflexi(Question $question, Response $response)
{
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $question->bool_mandatory) ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $repeatheadings = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
    $extraclass ="";
    $answertypeclass = "";

    $caption=gT("An array of sub-question on each cell. The sub-question text are in the table header and concerns line header. ");
    $checkconditionFunction = "fixnum_checkconditions";
    //echo '<pre>'; print_r($_POST); echo '</pre>';
    $defaultvaluescript = '';


    
    if (trim($question->multiflexible_max)!='' && trim($question->multiflexible_min) ==''){
        $maxvalue=$question->multiflexible_max;
        $extraclass .=" maxvalue maxvalue-".trim($question->multiflexible_max);
        if(isset($minvalue['value']) && $minvalue['value'] == 0) {$minvalue = 0;} else {$minvalue=1;}
    }
    if (trim($question->multiflexible_min)!='' && trim($question->multiflexible_max) ==''){
        $minvalue=$question->multiflexible_min;
        $extraclass .=" minvalue minvalue-".trim($question->multiflexible_max);
        $maxvalue=$question->multiflexible_min + 10;
    }
    if (trim($question->multiflexible_min)=='' && trim($question->multiflexible_max) ==''){
        if(isset($minvalue['value']) && $minvalue['value'] == 0) {$minvalue = 0;} else {$minvalue=1;}
        $maxvalue=10;
    }
    if (trim($question->multiflexible_min) !='' && trim($question->multiflexible_max) !=''){
        if($question->multiflexible_min < $question->multiflexible_max){
            $minvalue=$question->multiflexible_min;
            $maxvalue=$question->multiflexible_max;
        }
    }

    if (trim($question->multiflexible_step)!='' && $question->multiflexible_step > 0)
    {
        $stepvalue=$question->multiflexible_step;
    }
    else
    {
        $stepvalue=1;
    }

    if($question->reverse==1)
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
    if ($question->multiflexible_checkbox!=0)
    {
        $minvalue=0;
        $maxvalue=1;
        $checkboxlayout=true;
        $answertypeclass =" checkbox";
        $caption.=gT("Check or uncheck the answer for each subquestion. ");
    }
    elseif ($question->input_boxes!=0 )
    {
        $inputboxlayout=true;
        $answertypeclass .=" numeric-item text";
        $extraclass .= " numberonly";
        $caption.=gT("Each answers are a number. ");
    }
    else
    {
        $answertypeclass =" dropdown";
        $caption.=gT("Select the answer for each subquestion. ");
    }
    if(ctype_digit(trim($question->repeat_headings)) && trim($question->repeat_headings!=""))
    {
        $repeatheadings = intval($question->repeat_headings);
        $minrepeatheadings = 0;
    }
    if (intval(trim($question->maximum_chars))>0)
    {
        // Only maxlength attribute, use textarea[maxlength] jquery selector for textarea
        $maximum_chars= intval(trim($question->maximum_chars));
        $maxlength= "maxlength='{$maximum_chars}' ";
        $extraclass .=" maxchars maxchars-".$maximum_chars;
    }
    else
    {
        $maxlength= "";
    }

    if ($question->survey->bool_nokeyboard)
    {
        includeKeypad();
        $kpclass = " num-keypad";
        $extraclass .=" inputkeypad";
    }
    else
    {
        $kpclass = "";
    }

    if (trim($question->answer_width)!='')
    {
        $answerwidth=$question->answer_width;
    }
    else
    {
        $answerwidth=20;
    }
    $columnswidth=100-($answerwidth*2);


    $rows = array_filter($question->subQuestions, function (Question $question) {
        return $question->scale_id == 0;
    });

    $numrows = (!$question->bool_mandatory && $question->survey->bool_shownoanswer) ? count($rows) + 1 : count($rows);

    $cellwidth=$columnswidth / $numrows;

    $cellwidth=sprintf('%02d', $cellwidth);

    /**
     * @todo What is this?!
     */
    $sQuery = "SELECT count(question) FROM {{questions}} WHERE parent_qid=".$question->primaryKey." AND scale_id=0 AND question like '%|%'";
    $iCount = Yii::app()->db->createCommand($sQuery)->queryScalar();
    if ($iCount>0) {
        $right_exists=true;
        $answerwidth=$answerwidth/2;
        $caption.=gT("The last cell give some information. ");
    } else {
        $right_exists=false;
    }
    // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
    $columns = array_filter($question->subQuestions, function (Question $question) {
        return $question->scale_id == 1;
    });


    $fn=1;

    $mycols = "\t<colgroup class=\"col-responses\">\n"
    . "\n\t<col class=\"answertext\" width=\"$answerwidth%\" />\n";
    $answer_head_line = "\t<td >&nbsp;</td>\n";
    $odd_even = '';
    /** @var Question $column */
    foreach ($columns as $column)
    {
        $answer_head_line .= TbHtml::tag('th', [], $column->question);
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
    $html = "\n<table class=\"question subquestions-list questions-list {$answertypeclass}-list {$extraclass}\" summary=\"{$caption}\">\n"
    . $mycols
    . $answer_head . "\n";
    $html .= "<tbody>";
    /** @var Question $row */
    foreach ($rows as $row)
    {
        if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
        {
            if ( ($anscount - $fn + 1) >= $minrepeatheadings )
            {
                $html .= "</tbody>\n<tbody>";// Close actual body and open another one
                $html .= "<tr class=\"repeat headings dontread\">\n"
                . $answer_head_line
                . "</tr>\n\n";
            }
        }
        $myfname = $question->sgqa . $row->title;
        $answertextsave = $answertext = $row->question;

        /* Check the sub Q mandatory violation */
        if ($row->bool_mandatory && !empty($aMandatoryViolationSubQ))
        {
            //Go through each labelcode and check for a missing answer! Default :If any are found, highlight this line, checkbox : if one is not found : don't highlight
            // PS : we really need a better system : event for EM !
            $emptyresult=($question->multiflexible_checkbox!=0) ? 1 : 0;
            foreach($labelcode as $ld)
            {
                $myfname2=$myfname.'_'.$ld;
                if($question->multiflexible_checkbox!=0)
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
        list($htmltbody2, $hiddenfield)=return_array_filter_strings($question, $row, $myfname, $trbc,
            $myfname, "tr", "$trbc subquestions-list questions-list {$answertypeclass}-list");

        $html .= $htmltbody2;

        if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
        $html .= "\t<th class=\"answertext\" width=\"$answerwidth%\">\n"
        . "$answertext\n"
        . $hiddenfield
        . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $html .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
        }
        $html .= "\" />\n\t</th>\n";
        $first_hidden_field = '';
        $thiskey=0;
        foreach ($columns as $column)
        {
            if ($checkboxlayout == false)
            {
                $myfname2=$myfname."_{$column->title}";

                $html .= "\t<td class=\"answer_cell_00{$column->title} question-item answer-item {$answertypeclass}-item $extraclass\">\n"
                . "<label class=\"hide read\" for=\"answer{$myfname2}\">{$column->question}</label>\n";
                $sSeparator = getRadixPointData($question->survey->localizedNumberFormat)['separator'];
                if($inputboxlayout == false) {
                    $html .= "\t<select class=\"multiflexiselect\" name=\"$myfname2\" id=\"answer{$myfname2}\""
                    . " onchange=\"$checkconditionFunction(this.value, this.name, this.type)\">\n"
                    . "<option value=\"\">".gT('...')."</option>\n";

                    for($ii=$minvalue; ($reverse? $ii>=$maxvalue:$ii<=$maxvalue); $ii+=$stepvalue) {
                        $html .= '<option value="'.str_replace('.',$sSeparator,$ii).'"';
                        if(isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]) && (string)$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2] == (string)$ii) {
                            $html .= SELECTED;
                        }
                        $html .= ">".str_replace('.',$sSeparator,$ii)."</option>\n";
                    }
                    $html .= "\t</select>\n";
                } elseif ($inputboxlayout == true)
                {
                    $html .= "\t<input type='text' class=\"multiflexitext text {$kpclass}\" name=\"$myfname2\" id=\"answer{$myfname2}\" {$maxlength} size=5 "
                    . " onkeyup=\"$checkconditionFunction(this.value, this.name, this.type)\""
                    . " value=\"";
                    if(isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]) && is_numeric($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2])) {
                        $html .= str_replace('.',$sSeparator,$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2]);
                    }
                    $html .= "\" />\n";
                }
                $html .= "\t</td>\n";

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
                $html .= "\t<td class=\"answer_cell_00$ld question-item answer-item {$answertypeclass}-item\">\n"
                . "\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" value=\"$myvalue\"/>\n"
                . "\t<input type=\"hidden\" name=\"$myfname2\" id=\"answer{$myfname2}\" value=\"$myvalue\" />\n";
                $html .= "\t<input type=\"checkbox\" class=\"checkbox {$extraclass}\" name=\"cbox_$myfname2\" id=\"cbox_$myfname2\" $setmyvalue "
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
                $html .=  "<label class=\"hide read\" for=\"cbox_{$myfname2}\">{$labelans[$thiskey]}</label>\n";
                $inputnames[]=$myfname2;
                //                    $answer .= "</label>\n"
                $html .= ""
                . "\t</td>\n";
                $thiskey++;
            }
        }
        if (strpos($answertextsave,'|'))
        {
            $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
            $html .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">$answertext</td>\n";
        }
        elseif ($right_exists)
        {
            $html .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">&nbsp;</td>\n";
        }

        $html .= "</tr>\n";
        //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
        $fn++;
    }
    $html .= "\t</tbody>\n</table>\n";

    return [$html, $inputnames];
}


// ---------------------------------------------------------------
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_arraycolumns(Question $question, Response $response)
{
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $question->bool_mandatory) ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $extraclass = "";
    $checkconditionFunction = "checkconditions";
    $caption=gT("An array with sub-question on each column. The sub-question are on table header, the answers are in each line header. ");

    
    $fn=1;
    $cellwidth = round(( 50 / count($question->subQuestions) ) , 1);
    $answer = "\n<table class=\"question subquestions-list questions-list\" summary=\"{$caption}\">\n"
    . "\t<colgroup class=\"col-responses\">\n"
    . "\t<col class=\"col-answers\" width=\"50%\" />\n";
    $odd_even = '';
    foreach($question->answers as $answerObject)
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"$odd_even question-item answers-list radio-list\" width=\"$cellwidth%\" />\n";
    }
    $answer .= "\t</colgroup>\n\n"
    . "\t<thead>\n"
    . "<tr>\n"
    . "\t<td>&nbsp;</td>\n";

    foreach ($question->subQuestions as $subQuestion)
    {
        $anscode[]=$subQuestion->title;
        $answers[]=$subQuestion->question;
    }
    $trbc = '';
    $odd_even = '';
    for ($_i=0;$_i<count($answers);++$_i)
    {
        $ld = $answers[$_i];
        $myfname = $question->sgqa.$anscode[$_i];
        $trbc = alternation($trbc , 'row');
        /* Check the Sub Q mandatory violation */
        if ($subQuestion->bool_mandatory && in_array($myfname, $aMandatoryViolationSubQ))
        {
            $ld = "<span class=\"errormandatory\">{$ld}</span>";
        }
        $odd_even = alternation($odd_even);
        $answer .= "\t<th class=\"$odd_even\">$ld</th>\n";
    }
    unset($trbc);
    $answer .= "</tr>\n\t</thead>\n\n\t<tbody>\n";
    $subQuestiontotallength=0;
    foreach($question->subQuestions as $subQuestion)
    {
        $subQuestiontotallength=$subQuestiontotallength+strlen($subQuestion->question);
    }
    $percwidth=100 - ($cellwidth * count($question->answers));
    foreach($question->subQuestions as $subQuestion)
    {
        $answer .= "<tr>\n"
        . "\t<th class=\"arraycaptionleft dontread\">{$subQuestion->question}</th>\n";
        foreach ($anscode as $ld)
        {
            //if (!isset($trbc) || $trbc == 'array1') {$trbc = 'array2';} else {$trbc = 'array1';}
            $myfname=$question->sgqa.$ld;
            $answer .= "\t<td class=\"answer_cell_00$ld answer-item radio-item\">\n"
            . "\t<input class=\"radio\" type=\"radio\" name=\"".$myfname.'" value="'.$subQuestion->title.'" '
            . 'id="answer'.$myfname.'-'.$subQuestion->title.'" ';
            if ($response->$myfname == $subQuestion->title
            || !(isset($response->$myfname) && $subQuestion->title == '')
            ) {
                $answer .= CHECKED;
            }
            $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
            . "<label class=\"hide read\" for=\"answer".$myfname.'-'.$subQuestion->title."\">{$subQuestion->question}</label>\n"
            . "\t</td>\n";
        }
        unset($trbc);
        $answer .= "</tr>\n";
        $fn++;
    }

    $answer .= "\t</tbody>\n</table>\n";
    foreach($anscode as $ld)
    {
        $myfname=$question->sgqa.$ld;
        $answer .= '<input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="';
        if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))
        {
            $answer .= $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname];
        }
        $answer .= "\" />\n";
        $inputnames[]=$myfname;
    }
    return [$answer, $inputnames];
}

// ---------------------------------------------------------------
function do_array_dual(Question $question, Response $response)
{
    return ['ok'];
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $question->bool_mandatory) ? explode("|",$aLastMoveResult['unansweredSQs']) : array();
    $repeatheadings = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
    $extraclass ="";
    $answertypeclass = ""; // Maybe not
    $caption="";// Just leave empty, are replaced after
    $inputnames=array();
    $labelans1=array();
    $labelans=array();
    

    if ($question->use_dropdown==1)
    {
        $useDropdownLayout = true;
        $extraclass .=" dropdown-list";
        $answertypeclass .=" dropdown";
        $doDualScaleFunction="doDualScaleDropDown";// javascript funtion to lauch at end of answers
        $caption=gT("An array with sub-question on each line, with 2 answers to provide on each line. You have to select the answer.");
    }
    else
    {
        $useDropdownLayout = false;
        $extraclass .=" radio-list";
        $answertypeclass .=" radio";
        $doDualScaleFunction="doDualScaleRadio";
        $caption=gT("An array with sub-question on each line, with 2 answers to provide on each line. The answers are contained in the table header. ");
    }
    if(ctype_digit(trim($question->repeat_headings)) && trim($question->repeat_headings!=""))
    {
        $repeatheadings = intval($question->repeat_headings);
        $minrepeatheadings = 0;
    }
    $leftheader = trim($question->dualscale_headerA);
    $rightheader = trim($question->dualscale_headerB);

    $answerwidth = is_numeric($question->answer_width) ? $question->answer_width : 20;
    // Label and code for input
    foreach ($question->getAnswers(0) as $lrow)
    {
        $labels0[]=Array('code' => $lrow['code'],
        'title' => $lrow['answer']);
    }
    foreach ($question->getAnswers(1) as $lrow)
    {
        $labels1[]=Array('code' => $lrow['code'],
        'title' => $lrow['answer']);
    }

    $answer = "";
    $fn=1;// Used by repeat_heading
    if ($useDropdownLayout === false)
    {
        $columnswidth = 100 - $answerwidth;
        foreach ($question->getAnswers(0) as $lrow)
        {
            $labelans0[]=$lrow['answer'];
            $labelcode0[]=$lrow['code'];
        }
        foreach ($question->getAnswers(1) as $lrow)
        {
            $labelans1[]=$lrow['answer'];
            $labelcode1[]=$lrow['code'];
        }
        $numrows = count($question->subQuestions);

        // Add needed row and fill some boolean: shownoanswer, rightexists, centerexists
        $shownoanswer=(!$question->bool_mandatory && $question->survey->bool_shownoanswer);
        if($shownoanswer) {
            $numrows++;
            $caption.=gT("The last cell are for no answer. ");
        }

        $numrows++;
        $numrows++;
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
        if($shownoanswer)
        {
            $rigthwidth= "width=\"$cellwidth%\" ";
            $mycolumns .=  "\t<col class=\"separator rigth_separator\" {$rigthwidth}/>\n";
            $answer_head_line .= "\n\t<td class=\"header_separator rigth_separator\">&nbsp;</td>\n";
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
        foreach ($question->subQuestions as $subQuestion)
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
            $answertext=$subQuestion->question;

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

            $answertextcenter=substr($answertextrigth,0, strpos($answertextrigth,'|'));
            $answertextrigth=substr($answertextrigth,strpos($answertextrigth,'|')+1);
            $myfname= $question->sgqa.$subQuestion->title;
            $myfname0 = $question->sgqa.$subQuestion->title.'#0';
            $myfid0 = $question->sgqa.$subQuestion->title.'_0';
            $myfname1 = $question->sgqa.$subQuestion->title.'#1'; // new multi-scale-answer
            $myfid1 = $question->sgqa.$subQuestion->title.'_1';
            /* Check the Sub Q mandatory violation */
            if ($subQuestion->bool_mandatory && (in_array($myfname0, $aMandatoryViolationSubQ) || in_array($myfname1, $aMandatoryViolationSubQ)))
            {
                $answertext = "<span class='errormandatory'>{$answertext}</span>";
            }
            // Get array_filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($question, $subQuestion, $myfname, $trbc,
                $myfname, "tr", "$trbc answers-list radio-list");
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
                . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname0\" value=\"$ld\" id=\"answer$myfid0-$ld\" ";
                if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0] == $ld)
                {
                    $answer .= CHECKED;
                }
                $answer .= "  />\n"
                . "<label class=\"hide read\" for=\"answer{$myfid0}-{$ld}\">$labelans0[$thiskey]</label>\n"
                . "\n\t</td>\n";
                $thiskey++;
            }
            if (count($labelans1)>0) // if second label set is used
            {
                $answer .= "\t<td class=\"dual_scale_separator information-item\">";
                if ($shownoanswer)// No answer for accessibility and no javascript (but hide hide even with no js: need reworking)
                {
                    $answer .= "\t<input class='radio jshide read' type='radio' name='$myfname0' value='' id='answer$myfid0-' ";
                    if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0] == "")
                    {
                        $answer .= CHECKED;
                    }
                    $answer .= " />\n";
                }
                $answer .=  "<label for='answer$myfid0-' class= \"hide read\">".gT("No answer")."</label>";
                $answer .= "\t{$answertextcenter}</td>\n"; // separator
                array_push($inputnames,$myfname1);
                $thiskey=0;
                foreach ($labelcode1 as $ld) // second label set
                {
                    $answer .= "\t<td class=\"answer_cell_2_00$ld  answer-item radio-item\">\n"
                    . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname1\" value=\"$ld\" id=\"answer$myfid1-$ld\" ";
                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1] == $ld)
                    {
                        $answer .= CHECKED;
                    }
                    $answer .= " />\n"
                    . "<label class=\"hide read\" for=\"answer{$myfid1}-{$ld}\">{$labelans1[$thiskey]}</label>\n"
                    . "\t</td>\n";
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
                    $answer .= "\t<input class='radio' type='radio' name='$myfname1' value='' id='answer$myfid1-' ";
                    if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1] == "")
                    {
                        $answer .= CHECKED;
                    }
                    // --> START NEW FEATURE - SAVE
                    $answer .= " />\n";
                    $answer .= "<label class='hide read' for='answer$myfid1-'>".gT("No answer")."</label>";
                }
                else
                {
                    $answer .= "\t<input class='radio' type='radio' name='$myfname0' value='' id='answer$myfid0-' ";
                    if (!isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0]) || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0] == "")
                    {
                        $answer .= CHECKED;
                    }
                    $answer .= "<label class='hide read' for='answer$myfid0-'>".gT("No answer")."<label>\n";
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
        if (trim($question->dropdown_prepostfix[$session->language])!='') {
            list ($ddprefix, $ddsuffix) =explode("|",$question->dropdown_prepostfix[$session->language]);
            $ddprefix = $ddprefix;
            $ddsuffix = $ddsuffix;
        }
        else
        {
            $ddprefix ='';
            $ddsuffix='';
        }
        if (trim($question->dropdown_separators)!='') {
            $aSeparator =explode('|',$question->dropdown_separators);
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
        foreach ($question->subQuestions as $subQuestion)
        {

            $myfname = $question->sgqa.$subQuestion->title;
            $myfname0 = $question->sgqa.$subQuestion->title."#0";
            $myfid0 = $question->sgqa.$subQuestion->title."_0";
            $myfname1 = $question->sgqa.$subQuestion->title."#1";
            $myfid1 = $question->sgqa.$subQuestion->title."_1";
            $sActualAnswer0=isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0])?$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname0]:"";
            $sActualAnswer1=isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1])?$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname1]:"";
            if ($subQuestion->bool_mandatory && (in_array($myfname0, $aMandatoryViolationSubQ) || in_array($myfname1, $aMandatoryViolationSubQ)))
            {
                $answertext="<span class='errormandatory'>".$subQuestion->question."</span>";
            }
            else
            {
                $answertext=$subQuestion->question;
            }
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($question, $subQuestion, $myfname, $trbc,
                $myfname, "tr", "$trbc subquestion-list questions-list dropdown-list");
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
                $answer .= "\t<option value=\"\" ".SELECTED.">".gT('Please choose...')."</option>\n";
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
            if ($sActualAnswer0 != '' && !$question->bool_mandatory && $question->survey->bool_shownoanswer)
            {
                $answer .= "\t<option value=\"\">".gT('No answer')."</option>\n";
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
                $answer .= "\t<option value=\"\" ".SELECTED.">".gT('Please choose...')."</option>\n";
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
            if ($sActualAnswer1 != '' && !$question->bool_mandatory && $question->survey->bool_shownoanswer)
            {
                $answer .= "\t<option value=\"\">".gT('No answer')."</option>\n";
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
      Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."dualscale.js");
    $answer .= "<script type='text/javascript'>\n"
    . "  <!--\n"
    ." {$doDualScaleFunction}({$question->primaryKey});\n"
    ." -->\n"
    ."</script>\n";
    return array($answer, $inputnames);
}
