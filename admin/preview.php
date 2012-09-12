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
 * $Id: preview.php 12106 2012-01-19 05:35:48Z tmswhite $
 */


//Ensure script is not run directly, avoid path disclosure
$LEMdebugLevel=0;

include_once("login_check.php");
require_once(dirname(__FILE__).'/sessioncontrol.php');

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($qid)) {$qid=returnglobal('qid');}
if (empty($surveyid)) {die("No SID provided.");}
if (empty($qid)) {die("No QID provided.");}

if (!isset($_SESSION['step'])) { $_SESSION['step'] = 0; }
if (!isset($_SESSION['prevstep'])) { $_SESSION['prevstep'] = 0; }
if (!isset($_SESSION['maxstep'])) { $_SESSION['maxstep'] = 0; }
if (!isset($_GET['lang']) || $_GET['lang'] == "")
{
    $language = GetBaseLanguageFromSurveyID($surveyid);
} else {
    $language = sanitize_languagecode($_GET['lang']);
}

$_SESSION['s_lang'] = $language;
$clang = new limesurvey_lang($language);

$thissurvey=getSurveyInfo($surveyid);
$_SESSION['dateformats'] = getDateFormatData($thissurvey['surveyls_dateformat']);
require_once(dirname(__FILE__).'/../qanda.php');

$qquery = 'SELECT * FROM '.db_table_name('questions')." WHERE sid='$surveyid' AND qid='$qid' AND language='{$language}'";
$qresult = db_execute_assoc($qquery);
$qrows = $qresult->FetchRow();
$ia = array(0 => $qid,
1 => $surveyid.'X'.$qrows['gid'].'X'.$qid,
2 => $qrows['title'],
3 => $qrows['question'],
4 => $qrows['type'],
5 => $qrows['gid'],
6 => $qrows['mandatory'],
7 => 'N',
8 => 'N' ); // ia[8] is usedinconditions

$radix=getRadixPointData($thissurvey['surveyls_numberformat']);
$radix = $radix['seperator'];
$surveyOptions = array(
    'radix'=>$radix,
    );

LimeExpressionManager::StartSurvey($thissurvey['sid'], 'question', $surveyOptions, false,$LEMdebugLevel);
$qseq = LimeExpressionManager::GetQuestionSeq($qid);
$moveResult = LimeExpressionManager::JumpTo($qseq+1,true,false,true);

$answers = retrieveAnswers($ia);

if (!$thissurvey['template'])
{
    $thistpl=sGetTemplatePath($defaulttemplate);
}
else
{
    $thistpl=sGetTemplatePath(validate_templatedir($thissurvey['template']));
}

doHeader();
$showQuestion = "$('#question$qid').show();";
$dummy_js = <<< EOD
    <script type='text/javascript'>
    <!--
    LEMradix='$radix';
    var numRegex = new RegExp('[^-' + LEMradix + '0-9]','g');
    var intRegex = new RegExp('[^-0-9]','g');
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
        $showQuestion
	}
    $(document).ready(function() {
        $showQuestion
    });
    $(document).change(function() {
        $showQuestion
    });
    $(document).bind('keydown',function(e) {
                if (e.keyCode == 9) {
                    $showQuestion
                    return true;
                }
                return true;
            });
// -->
</script>
EOD;

$answer=$answers[0][1];

//GET HELP
$hquery="SELECT help FROM {$dbprefix}questions WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."'";
$hresult=db_execute_num($hquery) or safe_die($connect->ErrorMsg());       //Checked
$help="";
while ($hrow=$hresult->FetchRow()) {$help=$hrow[0];}

$question = $answers[0][0];
$question['code']=$answers[0][5];
$question['class'] = question_class($qrows['type']);
$question['essentials'] = 'id="question'.$qrows['qid'].'"';
$question['sgq']=$ia[1];
$question['aid']='unknown';
$question['sqid']='unknown';
$question['type']= $qrows['type'];

if ($qrows['mandatory'] == 'Y')
{
    $question['man_class'] = ' mandatory';
}
else
{
    $question['man_class'] = '';
};

$content = templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
$content .='<form method="post" action="index.php" id="limesurvey" name="limesurvey" autocomplete="off">';
$content .= templatereplace(file_get_contents("$thistpl/startgroup.pstpl"));

$question_template = file_get_contents("$thistpl/question.pstpl");
if(substr_count($question_template , '{QUESTION_ESSENTIALS}') > 0 ) // the following has been added for backwards compatiblity.
{// LS 1.87 and newer templates
    $content .= "\n".templatereplace($question_template,NULL,false,$qid)."\n";
}
else
{// LS 1.86 and older templates
    $content .= '<div '.$question['essentials'].' class="'.$question['class'].$question['man_class'].'">';
    $content .= "\n".templatereplace($question_template,NULL,false,$qid)."\n";
    $content .= "\n\t</div>\n";
};

$content .= templatereplace(file_get_contents("$thistpl/endgroup.pstpl")).$dummy_js;
LimeExpressionManager::FinishProcessingGroup();
$content .= LimeExpressionManager::GetRelevanceAndTailoringJavaScript();
$content .= '<p>&nbsp;</form>';

LimeExpressionManager::FinishProcessingPage();

echo $content;

if ($LEMdebugLevel >= 1) {
    echo LimeExpressionManager::GetDebugTimingMessage();
}
if ($LEMdebugLevel >= 2) {
     echo "<table><tr><td align='left'><b>Group/Question Validation Results:</b>".$moveResult['message']."</td></tr></table>\n";
}

$content .= templatereplace(file_get_contents("$thistpl/endpage.pstpl"));

echo "</html>\n";


exit;
?>
