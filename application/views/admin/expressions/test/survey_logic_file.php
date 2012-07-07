<?php
if (count($_GET) > 0) {
    foreach ($_GET as $key=>$val) {
        if ($key == 'sid') {
            $val = $val . '|N'; // hack to pretend this is not an assessment
        }
        $_REQUEST[$key] = $val;
    }
    $_REQUEST['LEM_PRETTY_PRINT_ALL_SYNTAX'] = 'Y';

    $surveyinfo = getSurveyInfo(sanitize_int($_REQUEST['sid']));
    if (isset($surveyinfo['assessments']) && $surveyinfo['assessments']=='Y')
    {
        $_REQUEST['assessments'] = 'Y';
    }
}

$clang = Yii::app()->lang;
Yii::app()->loadHelper('frontend');

if (empty($_REQUEST['sid']))   //  || count($_REQUEST) == 0) {
{
    $query = "select a.surveyls_survey_id as sid, a.surveyls_title as title, b.datecreated, b.assessments "
    . "from {{surveys_languagesettings}} as a join {{surveys}} as b on a.surveyls_survey_id = b.sid"
    . " where a.surveyls_language='en' order by a.surveyls_title, b.datecreated";
    $data = dbExecuteAssoc($query);
    $surveyList='';
    foreach($data->readAll() as $row) {
        $surveyList .= "<option value='" . $row['sid'] .'|' . $row['assessments'] . "'>#" . $row['sid'] . " [" . $row['datecreated'] . '] ' . flattenText($row['title']) . "</option>\n";
    }
    $url = $this->createUrl('admin/expressions/survey_logic_file');
    $form = <<< EOD
<form method='post' action='$url'>
<h3>Generate a logic file for the survey</h3>
<table border='1'>
<tr><th>Parameter</th><th>Value</th></tr>
<tr><td>Survey ID (SID)</td>
<td><select name='sid' id='sid'>
$surveyList
</select></td></tr>
<tr><td>Debug Log Level</td>
<td>
Specify which debugging features to use
<ul>
<li><input type='checkbox' name='LEM_DEBUG_TIMING' id='LEM_DEBUG_TIMING' value='Y'/>Detailed Timing</li>
<li><input type='checkbox' name='LEM_DEBUG_VALIDATION_SUMMARY' id='LEM_DEBUG_VALIDATION_SUMMARY' value='Y'/>Validation Summary</li>
<li><input type='checkbox' name='LEM_DEBUG_VALIDATION_DETAIL' id='LEM_DEBUG_VALIDATION_DETAIL' value='Y'/>Validation Detail (Validation Summary must also be checked to see detail)</li>
<li><input type='checkbox' name='LEM_PRETTY_PRINT_ALL_SYNTAX' id='LEM_PRETTY_PRINT_ALL_SYNTAX' value='Y' checked="checked"/>Pretty Print Syntax</li>
</ul></td>
</tr>
<tr><td colspan='2'><input type='submit'/></td></tr>
</table>
</form>
EOD;
    echo $form;
}
else {
    $surveyInfo = (array) explode('|', $_REQUEST['sid']);
    $surveyid = $surveyInfo[0];
    if (isset($_REQUEST['assessments']))
    {
        $assessments = ($_REQUEST['assessments'] == 'Y');
    }
    else
    {
        $assessments = ($surveyInfo[1] == 'Y');
    }
    $LEMdebugLevel = (
            ((isset($_REQUEST['LEM_DEBUG_TIMING']) && $_REQUEST['LEM_DEBUG_TIMING'] == 'Y') ? LEM_DEBUG_TIMING : 0) +
            ((isset($_REQUEST['LEM_DEBUG_VALIDATION_SUMMARY']) && $_REQUEST['LEM_DEBUG_VALIDATION_SUMMARY'] == 'Y') ? LEM_DEBUG_VALIDATION_SUMMARY : 0) +
            ((isset($_REQUEST['LEM_DEBUG_VALIDATION_DETAIL']) && $_REQUEST['LEM_DEBUG_VALIDATION_DETAIL'] == 'Y') ? LEM_DEBUG_VALIDATION_DETAIL : 0) +
            ((isset($_REQUEST['LEM_PRETTY_PRINT_ALL_SYNTAX']) && $_REQUEST['LEM_PRETTY_PRINT_ALL_SYNTAX'] == 'Y') ? LEM_PRETTY_PRINT_ALL_SYNTAX : 0)
            );

    $language = (isset($_REQUEST['lang']) ? sanitize_languagecode($_REQUEST['lang']) : NULL);
    $gid = (isset($_REQUEST['gid']) ? sanitize_int($_REQUEST['gid']) : NULL);
    $qid = (isset($_REQUEST['qid']) ? sanitize_int($_REQUEST['qid']) : NULL);

    print <<< EOD
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Logic File - Survey #$surveyid</title>
<style type="text/css">
tr.LEMgroup td
{
background-color:lightgrey;
}

tr.LEMquestion
{
background-color:#EAF2D3;
}

tr.LEManswer td
{
background-color:white;
}

.LEMerror
{
color:red;
font-weight:bold;
}

tr.LEMsubq td
{
background-color:lightyellow;
}
</style>
</head>
<body>
EOD;


    SetSurveyLanguage($surveyid, $language);

    $result = LimeExpressionManager::ShowSurveyLogicFile($surveyid, $gid, $qid,$LEMdebugLevel,$assessments);
    print $result['html'];

    print <<< EOD
</body>
</html>
EOD;
}
?>