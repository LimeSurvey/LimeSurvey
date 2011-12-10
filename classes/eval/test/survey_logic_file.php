<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Survey Logic File</title>
<style type="text/css">
tr.group td
{
color:#000000;
background-color:#EAF2D3;
}
</style>
    </head>
    <body>
        <?php
            include_once('../LimeExpressionManager.php');
            require_once('../../../classes/core/startup.php');
            require_once('../../../config-defaults.php');
            require_once('../../../common.php');
            require_once('../../../classes/core/language.php');

            $clang = new limesurvey_lang("en");

            if (count($_POST) == 0) {
                $query = "select a.surveyls_survey_id as sid, a.surveyls_title as title, b.datecreated, b.assessments "
                . "from " . db_table_name('surveys_languagesettings') . " as a join ". db_table_name('surveys') . " as b on a.surveyls_survey_id = b.sid"
                . " where a.surveyls_language='en' order by a.surveyls_title, b.datecreated";
        		$data = db_execute_assoc($query);
                $surveyList='';
                foreach($data->GetRows() as $row) {
                    $surveyList .= "<option value='" . $row['sid'] .'|' . $row['assessments'] . "'>#" . $row['sid'] . " [" . $row['datecreated'] . '] ' . FlattenText($row['title']) . "</option>\n";
                }

                $form = <<< EOD
<form method='post' action='survey_logic_file.php'>
<h3>Generate a logic file for the survey</h3>
<table border='1'>
<tr><th>Parameter</th><th>Value</th></tr>
<tr><td>Survey ID (SID)</td>
<td><select name='sid' id='sid'>
$surveyList
</select></td></tr>
<tr><td>Navigation Style</td>
<td><select name='surveyMode' id='surveyMode'>
    <option value='question'>Question (One-at-a-time)</option>
    <option value='group'>Group (Group-at-a-time)</option>
    <option value='survey' selected>Survey (All-in-one)</option>
</select></td></tr>
<tr><td>Debug Log Level</td>
<td>
Specify which debugging features to use
<ul>
<li><input type='checkbox' name='LEM_DEBUG_TIMING' id='LEM_DEBUG_TIMING' value='Y' checked/>Detailed Timing</li>
<li><input type='checkbox' name='LEM_DEBUG_VALIDATION_SUMMARY' id='LEM_DEBUG_VALIDATION_SUMMARY' value='Y'/>Validation Summary</li>
<li><input type='checkbox' name='LEM_DEBUG_VALIDATION_DETAIL' id='LEM_DEBUG_VALIDATION_DETAIL' value='Y'/>Validation Detail (Validation Summary must also be checked to see detail)</li>
<li><input type='checkbox' name='LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB' id='LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB' value='Y'/>Log Syntax Errors to Database</li>
<li><input type='checkbox' name='LEM_DEBUG_TRANSLATION_DETAIL' id='LEM_DEBUG_TRANSLATION_DETAIL' value='Y'/>Translation Detail</li>
</ul></td>
</tr>
<tr><td colspan='2'><input type='submit'/></td></tr>
</table>
</form>
EOD;
                echo $form;
            }
            else {
                $surveyInfo = explode('|',$_POST['sid']);
                $surveyid = $surveyInfo[0];
                $assessments = ($surveyInfo[1] == 'Y');
                $surveyMode = $_POST['surveyMode'];
                $LEMdebugLevel = (
                        ((isset($_POST['LEM_DEBUG_TIMING']) && $_POST['LEM_DEBUG_TIMING'] == 'Y') ? LEM_DEBUG_TIMING : 0) +
                        ((isset($_POST['LEM_DEBUG_VALIDATION_SUMMARY']) && $_POST['LEM_DEBUG_VALIDATION_SUMMARY'] == 'Y') ? LEM_DEBUG_VALIDATION_SUMMARY : 0) +
                        ((isset($_POST['LEM_DEBUG_VALIDATION_DETAIL']) && $_POST['LEM_DEBUG_VALIDATION_DETAIL'] == 'Y') ? LEM_DEBUG_VALIDATION_DETAIL : 0) +
                        ((isset($_POST['LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB']) && $_POST['LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB'] == 'Y') ? LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB : 0) +
                        ((isset($_POST['LEM_DEBUG_TRANSLATION_DETAIL']) && $_POST['LEM_DEBUG_TRANSLATION_DETAIL'] == 'Y') ? LEM_DEBUG_TRANSLATION_DETAIL : 0)
                        );

                $language = NULL;
                $gid = NULL;
                $qid = NULL;

                print <<< EOD
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Logic File - Survey #$surveyid</title>
<style type="text/css">
tr.group td
{
color:#000000;
background-color:#EAF2D3;
}
</style>
    </head>
    <body>
    <H3>Logic File for Survey #$surveyid</H3>
EOD;

                print LimeExpressionManager::ShowSurveyLogicFile($surveyid, $language, $gid, $qid,$LEMdebugLevel);

                print <<< EOD
</body>
</html>
EOD;
            }
        ?>
    </body>
</html>
