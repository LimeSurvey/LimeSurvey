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
$clang = new limesurvey_lang($language);

$thissurvey=getSurveyInfo($surveyid);

$qquery = 'SELECT * FROM '.db_table_name('questions')." WHERE sid='$surveyid' AND qid='$qid' AND language='{$language}'";
$qresult = db_execute_assoc($qquery);
$qrows = $qresult->FetchRow();
$ia = array(0 => $qid, 
            1 => "FIELDNAME", 
            2 => $qrows['title'], 
            3 => $qrows['question'], 
            4 => $qrows['type'], 
            5 => $qrows['gid'],
            6 => $qrows['mandatory'], 
            //7 => $qrows['other']); // ia[7] is conditionsexist not other
            7 => 'N',
            8 => 'N' ); // ia[8] is usedinconditions
            
$answers = retrieveAnswers($ia);
$thistpl="$templaterootdir/".$thissurvey['template'];
doHeader();
$dummy_js = '
		            <!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->
		            <script type="text/javascript">
            <!--
            function checkconditions(value, name, type)
            {
            }
            //-->
		            </script>
		            <form method="post" action="index.php" id="limesurvey" name="limesurvey">
            ';



$question="<label for='$answers[0][7]'>" . $answers[0][0] . "</label>";
$answer=$answers[0][1];
$help=$answers[0][2];
$questioncode=$answers[0][5];
$q_class = question_class($qrows['type']); 
if ($qrows['mandatory'] == 'Y')
{
    $man_class = ' mandatory';
}
else
{
    $man_class = '';
}


$content = templatereplace(file_get_contents("$thistpl/startpage.pstpl"));     
$content .= templatereplace(file_get_contents("$thistpl/startgroup.pstpl")); 
$content .= '<form id="limesurvey"><div id="question'.$qrows['qid'].'" class="'.$q_class.$man_class.'">';    
$content .= templatereplace(file_get_contents("$thistpl/question.pstpl"));
$content .= '</div></form>';
$content .= templatereplace(file_get_contents("$thistpl/endgroup.pstpl")).$dummy_js;     
$content .= templatereplace(file_get_contents("$thistpl/endpage.pstpl"));     
if($qrows['mandatory'] == 'Y')
{
	$mandatory = ' mandatory';
}
else
{
	$mandatory = '';
}
$content = str_replace('{QUESTION_CLASS}' , question_class($qrows['type']) . $mandatory , $content);
echo $content;
echo "</html>\n";


exit;
?>
