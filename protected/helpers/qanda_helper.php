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

use ls\models\Answer;
use ls\models\Question;

function return_timer_script(Question $question, $ia, $disable=null) {
    global $thissurvey;


    Yii::app()->getClientScript()->registerScriptFile(App()->publicUrl . '/scripts/'.'coookies.js');

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
// TMSW TODO - Can remove DB query by passing in answer list from EM
function do_array(Question $question)
{
    global $thissurvey;
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $question->bool_mandatory) ? explode("|",$aLastMoveResult['unansweredSQs']) : [];
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
    $labelans=[];
    $labelcode=[];
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
            if (!$question->bool_mandatory && $question->survey->bool_shownoanswer) //ls\models\Question is not mandatory and we can show "no answer"
            {
                $answer_head_line .= "\t<th>".gT('No answer')."</th>\n";
            }
        $answer_head = "\t<thead><tr class=\"dontread\">\n".$answer_head_line."</tr></thead>\n\t\n";

        $answer = '<tbody>';
        $trbc = '';
        $inputnames=[];
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
        if (!$question->bool_mandatory && $question->survey->bool_shownoanswer) //ls\models\Question is not mandatory
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
        $inputnames=[];

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
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $question->bool_mandatory) ? explode("|",$aLastMoveResult['unansweredSQs']) : [];
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
    $sSeparator = \ls\helpers\SurveyTranslator::getRadixPointData($thissurvey['surveyls_numberformat']);
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
    $labelans=[];
    $labelcode=[];
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
function do_array_multiflexi(Question $question, \ls\interfaces\ResponseInterface $response)
{
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $question->bool_mandatory) ? explode("|",$aLastMoveResult['unansweredSQs']) : [];
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
                $sSeparator = \ls\helpers\SurveyTranslator::getRadixPointData($question->survey->localizedNumberFormat)['separator'];
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
function do_arraycolumns(Question $question, \ls\interfaces\ResponseInterface $response)
{
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $question->bool_mandatory) ? explode("|",$aLastMoveResult['unansweredSQs']) : [];
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
function do_array_dual(Question $question, \ls\interfaces\ResponseInterface $response)
{
    return ['ok'];
    $aLastMoveResult=LimeExpressionManager::GetLastMoveResult();
    $aMandatoryViolationSubQ=($aLastMoveResult['mandViolation'] && $question->bool_mandatory) ? explode("|",$aLastMoveResult['unansweredSQs']) : [];
    $repeatheadings = Yii::app()->getConfig("repeatheadings");
    $minrepeatheadings = Yii::app()->getConfig("minrepeatheadings");
    $extraclass ="";
    $answertypeclass = ""; // Maybe not
    $caption="";// Just leave empty, are replaced after
    $inputnames=[];
    $labelans1=[];
    $labelans=[];
    

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
