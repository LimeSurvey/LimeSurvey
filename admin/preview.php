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
 * $Id$
 */


//Ensure script is not run directly, avoid path disclosure
$LEMdebugLevel=0;

include_once("login_check.php");
require_once(dirname(__FILE__).'/sessioncontrol.php');
require_once(dirname(__FILE__).'/../qanda.php');

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($qid)) {$qid=returnglobal('qid');}
if (empty($surveyid)) {die("No SID provided.");}
if (empty($qid)) {die("No QID provided.");}

if (!isset($_GET['lang']) || $_GET['lang'] == "")
{
    $language = GetBaseLanguageFromSurveyID($surveyid);
} else {
    $language = $_GET['lang'];
}

$_SESSION['s_lang'] = $language;
$_SESSION['fieldmap']=createFieldMap($surveyid,'full',false,$qid);
// Prefill question/answer from defaultvalues
foreach ($_SESSION['fieldmap'] as $field)
{
    if (isset($field['defaultvalue']))
    {
        $_SESSION[$field['fieldname']]=$field['defaultvalue'];
    }
}
$clang = new limesurvey_lang($language);

$thissurvey=getSurveyInfo($surveyid);
$_SESSION['dateformats'] = getDateFormatData($thissurvey['surveyls_dateformat']);

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
//7 => $qrows['other']); // ia[7] is conditionsexist not other
7 => 'N',
8 => 'N' ); // ia[8] is usedinconditions

LimeExpressionManager::StartSurvey($thissurvey['sid'], 'question', ($thissurvey['anonymized']!="N"), false,$LEMdebugLevel);
$qseq = LimeExpressionManager::GetQuestionSeq($qid);
$moveResult = LimeExpressionManager::JumpTo($qseq+1,false,true);

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
$dummy_js = '
		<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->
		<script type="text/javascript">
        /* <![CDATA[ */
	function checkconditions(value, name, type)
	{
        }
		function noop_checkconditions(value, name, type)
        {
            }
        /* ]]> */
</script>
        ';


$answer=$answers[0][1];
$help=$answers[0][2];

$question = $answers[0][0];
$question['code']=$answers[0][5];
$question['class'] = question_class($qrows['type']);
$question['essentials'] = 'id="question'.$qrows['qid'].'"';
$question['sgq']=$ia[1];

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
